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
            'source' => 'manual',
            'status' => $result['status'] === 'WARNING' ? 'overridden' : 'confirmed',
            'created_by' => (int) ($options['created_by'] ?? current_user()['id'] ?? 0),
        ]);

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
}
