<?php
declare(strict_types=1);

class Scheduler
{
    public static function evaluate(array $assignment): array
    {
        $result = ConflictDetector::evaluate($assignment);
        $result['can_proceed'] = !in_array($result['status'], ['BLOCKED'], true);
        return $result;
    }

    public static function recommend(array $assignment): array
    {
        $date = trim((string) ($assignment['schedule_date'] ?? date('Y-m-d')));
        $dutyId = (int) ($assignment['duty_id'] ?? 0);
        $subDutyId = !empty($assignment['subduty_id']) ? (int) $assignment['subduty_id'] : null;
        $reliefId = !empty($assignment['relief_id']) ? (int) $assignment['relief_id'] : null;

        if ($date === '' || !validate_date($date) || $dutyId <= 0) {
            return [
                'recommended' => [],
                'alternates' => [],
                'unavailable' => [],
                'warnings' => ['Invalid date or duty selection.'],
                'manual_enabled' => true,
            ];
        }

        $feed = RotationEngine::recommendationFeed($dutyId, $date, $subDutyId, $reliefId);
        $selected = (int) ($assignment['personnel_id'] ?? 0);
        $recommendedPersonId = (int) ($feed['recommended'][0]['personnel_id'] ?? 0);

        if ($selected > 0 && $recommendedPersonId > 0 && $selected !== $recommendedPersonId) {
            $feed['warnings'][] = [
                'personnel_id' => $selected,
                'message' => 'Selected personnel is not the current rotation recommendation.',
            ];
        }

        return $feed;
    }

    public static function manualAssign(array $assignment, array $options = []): int
    {
        $personnelId = (int) ($assignment['personnel_id'] ?? 0);
        $dutyId = (int) ($assignment['duty_id'] ?? 0);
        $date = trim((string) ($assignment['schedule_date'] ?? ''));

        if ($personnelId <= 0 || $dutyId <= 0 || $date === '') {
            throw new InvalidArgumentException('Personnel, duty, and date are required.');
        }

        $result = self::evaluate($assignment);
        $confirmed = (bool) ($options['confirmed'] ?? false);
        $reason = trim((string) ($options['reason'] ?? ''));
        $overrideType = trim((string) ($options['override_type'] ?? ''));

        if ($result['status'] === 'BLOCKED') {
            throw new RuntimeException(implode('; ', $result['issues']));
        }

        if ($result['status'] === 'WARNING' && !$confirmed) {
            throw new RuntimeException('Warning requires confirmation before assignment can proceed.');
        }

        $scheduleId = Schedule::create([
            'schedule_date' => $date,
            'duty_id' => $dutyId,
            'subduty_id' => $assignment['subduty_id'] ?? null,
            'relief_id' => $assignment['relief_id'] ?? null,
            'personnel_id' => $personnelId,
            'start_time' => $assignment['start_time'] ?? null,
            'end_time' => $assignment['end_time'] ?? null,
            'source' => 'manual',
            'status' => $result['status'] === 'WARNING' ? 'overridden' : 'confirmed',
            'created_by' => (int) ($options['created_by'] ?? current_user()['id'] ?? 0),
        ]);

        $rotationGroup = RotationEngine::dutyGroupForDuty($dutyId);
        if ($rotationGroup) {
            $pdo = get_db();
            $cycle = (int) ($rotationGroup['current_cycle'] ?? 1);
            $statement = $pdo->prepare(
                'INSERT INTO rotation_assignments (rotation_group_id, personnel_id, schedule_id, cycle_number, status, completed_date, notes)
                 VALUES (:rotation_group_id, :personnel_id, :schedule_id, :cycle_number, :status, :completed_date, :notes)'
            );
            $statement->execute([
                ':rotation_group_id' => (int) $rotationGroup['id'],
                ':personnel_id' => $personnelId,
                ':schedule_id' => $scheduleId,
                ':cycle_number' => $cycle,
                ':status' => 'completed',
                ':completed_date' => $date,
                ':notes' => $result['status'] === 'WARNING' ? 'Manual override approved.' : 'Assigned from rotation scheduler.',
            ]);

            $pdo->prepare('UPDATE duty_rotation_groups SET current_position = :current_position WHERE id = :id')->execute([
                ':current_position' => self::nextSequencePosition((int) $rotationGroup['id'], $personnelId),
                ':id' => (int) $rotationGroup['id'],
            ]);
        }

        if ($result['status'] === 'WARNING') {
            $overrideType = $overrideType !== '' ? $overrideType : self::defaultOverrideType($assignment, $result);
            self::saveOverride($scheduleId, $personnelId, $dutyId, $assignment, $overrideType, $reason, (int) ($options['created_by'] ?? current_user()['id'] ?? 0));
        }

        AuditLog::log(
            'schedule.assign',
            'schedule',
            'Manual assignment saved.',
            (int) ($options['created_by'] ?? current_user()['id'] ?? 0),
            (string) (current_user()['username'] ?? ''),
            'schedules',
            $scheduleId,
            $_SERVER['REMOTE_ADDR'] ?? null
        );

        return $scheduleId;
    }

    public static function saveOverride(int $scheduleId, int $personnelId, int $assignedDutyId, array $assignment, string $overrideType, string $reason, int $confirmedBy): void
    {
        $schedule = Schedule::findById($scheduleId);
        $normalDuty = ConflictDetector::normalPrimaryDutyForPersonnel($personnelId, $assignedDutyId);
        $pdo = get_db();
        $statement = $pdo->prepare(
            'INSERT INTO schedule_overrides (schedule_id, personnel_id, assigned_duty_id, normal_duty_id, subduty_id, relief_id, override_type, reason, confirmed_by, confirmed_at)
             VALUES (:schedule_id, :personnel_id, :assigned_duty_id, :normal_duty_id, :subduty_id, :relief_id, :override_type, :reason, :confirmed_by, NOW())'
        );

        $statement->execute([
            ':schedule_id' => $scheduleId,
            ':personnel_id' => $personnelId,
            ':assigned_duty_id' => $assignedDutyId,
            ':normal_duty_id' => $normalDuty['id'] ?? null,
            ':subduty_id' => !empty($assignment['subduty_id']) ? (int) $assignment['subduty_id'] : null,
            ':relief_id' => !empty($assignment['relief_id']) ? (int) $assignment['relief_id'] : null,
            ':override_type' => $overrideType,
            ':reason' => $reason !== '' ? $reason : 'Manual override approved after warning.',
            ':confirmed_by' => $confirmedBy,
        ]);

        if ($schedule) {
            $pdo->prepare('UPDATE schedules SET status = :status WHERE id = :id')->execute([
                ':status' => 'overridden',
                ':id' => $scheduleId,
            ]);
        }
    }

    public static function defaultOverrideType(array $assignment, array $result): string
    {
        if (!empty($assignment['subduty_id'])) {
            return 'sentinel_repeat';
        }

        return 'rank_substitution';
    }

    private static function nextSequencePosition(int $groupId, int $personnelId): int
    {
        $members = RotationMember::allForGroup($groupId);
        foreach ($members as $member) {
            if ((int) ($member['personnel_id'] ?? 0) === $personnelId) {
                return (int) ($member['sequence'] ?? 0);
            }
        }

        return 0;
    }
}
