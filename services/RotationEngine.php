<?php
declare(strict_types=1);

class RotationEngine
{
    public static function dutyGroupForDuty(int $dutyId): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT * FROM duty_rotation_groups WHERE duty_id = :duty_id AND is_active = 1 ORDER BY id ASC LIMIT 1'
        );
        $statement->execute([':duty_id' => $dutyId]);
        $group = $statement->fetch();

        return $group ?: null;
    }

    public static function recommendedPersonnelForDuty(int $dutyId, string $date, ?int $subDutyId = null, ?int $reliefId = null, int $depth = 0): ?array
    {
        if ($depth > 10) {
            return null;
        }

        $group = self::dutyGroupForDuty($dutyId);
        if (!$group) {
            return null;
        }

        $cycle = max(1, (int) ($group['current_cycle'] ?? 1));
        $members = RotationMember::allForGroup((int) $group['id']);
        $currentCandidates = [];

        foreach ($members as $member) {
            $personnelId = (int) ($member['personnel_id'] ?? 0);
            $personnel = Personnel::findById($personnelId);
            if (!$personnel || ($personnel['status'] ?? 'inactive') !== 'active') {
                continue;
            }

            $latest = self::latestAssignmentForMember((int) $group['id'], $personnelId);
            $isPending = true;
            if ($latest && (int) ($latest['cycle_number'] ?? 0) === $cycle && ($latest['status'] ?? '') === 'completed') {
                $isPending = false;
            }

            if ($isPending && !self::isPersonnelAvailableForDuty($personnelId, $date)) {
                continue;
            }

            $evaluation = ConflictDetector::evaluate([
                'personnel_id' => $personnelId,
                'duty_id' => $dutyId,
                'schedule_date' => $date,
                'subduty_id' => $subDutyId,
                'relief_id' => $reliefId,
            ]);

            if ($evaluation['status'] === 'BLOCKED') {
                continue;
            }

            $currentCandidates[] = [
                'personnel_id' => $personnelId,
                'sequence' => (int) ($member['sequence'] ?? 0),
                'name' => Personnel::displayName($personnel),
                'status' => $evaluation['status'],
                'issues' => $evaluation['issues'],
            ];
        }

        if (empty($currentCandidates)) {
            $nextCycle = (int) ($group['current_cycle'] ?? 1) + 1;
            $pdo = get_db();
            $statement = $pdo->prepare('UPDATE duty_rotation_groups SET current_cycle = :current_cycle WHERE id = :id');
            $statement->execute([':current_cycle' => $nextCycle, ':id' => (int) $group['id']]);
            return self::recommendedPersonnelForDuty($dutyId, $date, $subDutyId, $reliefId, $depth + 1);
        }

        usort($currentCandidates, static fn (array $left, array $right): int => $left['sequence'] <=> $right['sequence']);
        return $currentCandidates[0];
    }

    public static function latestAssignmentForMember(int $groupId, int $personnelId): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT * FROM rotation_assignments
             WHERE rotation_group_id = :rotation_group_id
               AND personnel_id = :personnel_id
             ORDER BY created_at DESC, id DESC
             LIMIT 1'
        );
        $statement->execute([
            ':rotation_group_id' => $groupId,
            ':personnel_id' => $personnelId,
        ]);

        $row = $statement->fetch();
        return $row ?: null;
    }

    public static function isPersonnelAvailableForDuty(int $personnelId, string $date): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT id FROM personnel_availability
             WHERE personnel_id = :personnel_id
               AND status = :status
               AND start_date <= :start_date
               AND (end_date IS NULL OR end_date >= :end_date)'
        );
        $statement->execute([
            ':personnel_id' => $personnelId,
            ':status' => 'Leave',
            ':start_date' => $date,
            ':end_date' => $date,
        ]);

        return $statement->fetchColumn() === false;
    }

    public static function recommendationFeed(int $dutyId, string $date, ?int $subDutyId = null, ?int $reliefId = null): array
    {
        $feed = [
            'recommended' => [],
            'alternates' => [],
            'unavailable' => [],
            'warnings' => [],
            'manual_enabled' => true,
        ];

        $duty = Duty::findById($dutyId);
        if (!$duty) {
            $feed['warnings'][] = 'Duty is not available.';
            return $feed;
        }

        $group = self::dutyGroupForDuty($dutyId);
        $preferred = self::recommendedPersonnelForDuty($dutyId, $date, $subDutyId, $reliefId);

        foreach (Personnel::all() as $person) {
            $personnelId = (int) ($person['id'] ?? 0);
            $evaluation = ConflictDetector::evaluate([
                'personnel_id' => $personnelId,
                'duty_id' => $dutyId,
                'schedule_date' => $date,
                'subduty_id' => $subDutyId,
                'relief_id' => $reliefId,
            ]);

            $entry = [
                'personnel_id' => $personnelId,
                'name' => Personnel::displayName($person),
                'rank' => $person['rank_abbr'] ?? '',
                'status' => $evaluation['status'],
                'issues' => $evaluation['issues'],
            ];

            if ($preferred && (int) $preferred['personnel_id'] === $personnelId) {
                $feed['recommended'][] = array_merge($entry, ['reason' => 'Primary rotation candidate']);
                continue;
            }

            if ($evaluation['status'] === 'RECOMMENDED') {
                $feed['alternates'][] = $entry;
            } elseif ($evaluation['status'] === 'WARNING') {
                $feed['warnings'][] = $entry;
            } elseif ($evaluation['status'] === 'BLOCKED') {
                $feed['unavailable'][] = $entry;
            } else {
                $feed['alternates'][] = $entry;
            }
        }

        if (!empty($preferred) && empty($feed['recommended'])) {
            $feed['recommended'][] = [
                'personnel_id' => (int) $preferred['personnel_id'],
                'name' => $preferred['name'],
                'rank' => '',
                'status' => 'RECOMMENDED',
                'issues' => ['Primary rotation candidate'],
                'reason' => 'Primary rotation candidate',
            ];
        }

        if ($group !== null && !empty($feed['recommended'])) {
            $feed['warnings'][] = [
                'personnel_id' => (int) ($feed['recommended'][0]['personnel_id'] ?? 0),
                'message' => 'Current rotation cycle: ' . (int) ($group['current_cycle'] ?? 1),
            ];
        }

        return $feed;
    }

    public static function dashboard(int $dutyId): array
    {
        $group = self::dutyGroupForDuty($dutyId);
        if (!$group) {
            return [
                'duty_id' => $dutyId,
                'cycle' => 1,
                'members' => [],
                'next_recommendation' => null,
            ];
        }

        $members = RotationMember::allForGroup((int) $group['id']);
        $rows = [];
        foreach ($members as $member) {
            $latest = self::latestAssignmentForMember((int) $group['id'], (int) $member['personnel_id']);
            $status = 'Pending';
            if ($latest && (int) ($latest['cycle_number'] ?? 0) === (int) ($group['current_cycle'] ?? 1) && ($latest['status'] ?? '') === 'completed') {
                $status = 'Completed';
            } elseif ($latest && ($latest['status'] ?? '') === 'unavailable') {
                $status = 'Unavailable';
            } elseif ($latest && ($latest['status'] ?? '') === 'skipped') {
                $status = 'Skipped';
            }

            $rows[] = [
                'sequence' => (int) ($member['sequence'] ?? 0),
                'personnel_id' => (int) ($member['personnel_id'] ?? 0),
                'name' => $member['first_name'] . ' ' . $member['last_name'],
                'rank' => $member['rank_abbr'] ?? '',
                'status' => $status,
            ];
        }

        return [
            'duty_id' => $dutyId,
            'cycle' => (int) ($group['current_cycle'] ?? 1),
            'members' => $rows,
            'next_recommendation' => self::recommendedPersonnelForDuty($dutyId, date('Y-m-d')),
        ];
    }
}
