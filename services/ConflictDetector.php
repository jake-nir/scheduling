<?php
declare(strict_types=1);

class ConflictDetector
{
    public static function evaluate(array $assignment): array
    {
        $personnelId = (int) ($assignment['personnel_id'] ?? 0);
        $dutyId = (int) ($assignment['duty_id'] ?? 0);
        $subDutyId = !empty($assignment['subduty_id']) ? (int) $assignment['subduty_id'] : null;
        $date = trim((string) ($assignment['schedule_date'] ?? ''));

        if ($date === '' || !validate_date($date)) {
            return ['status' => 'BLOCKED', 'issues' => ['Invalid schedule date.'], 'severity' => 'blocked'];
        }

        if ($personnelId <= 0) {
            return ['status' => 'BLOCKED', 'issues' => ['Personnel is required.'], 'severity' => 'blocked'];
        }

        if ($dutyId <= 0) {
            return ['status' => 'BLOCKED', 'issues' => ['Duty is required.'], 'severity' => 'blocked'];
        }

        $personnel = Personnel::findById($personnelId);
        if (!$personnel) {
            return ['status' => 'BLOCKED', 'issues' => ['Personnel record not found.'], 'severity' => 'blocked'];
        }

        $duty = Duty::findById($dutyId);
        if (!$duty || (int) ($duty['is_active'] ?? 0) !== 1) {
            return ['status' => 'BLOCKED', 'issues' => ['Duty is not active or not configured.'], 'severity' => 'blocked'];
        }

        $status = 'RECOMMENDED';
        $issues = [];

        if (($personnel['status'] ?? 'inactive') !== 'active') {
            $status = 'BLOCKED';
            $issues[] = 'Personnel is inactive.';
        }

        if ($subDutyId !== null) {
            $subDuty = SubDuty::findById($subDutyId);
            if (!$subDuty || (int) ($subDuty['is_active'] ?? 0) !== 1 || (int) ($subDuty['duty_id'] ?? 0) !== $dutyId) {
                $status = 'BLOCKED';
                $issues[] = 'Selected sub-duty is invalid for this duty.';
            }
        }

        if (!empty($assignment['relief_id'])) {
            $reliefId = (int) $assignment['relief_id'];
            $relief = Relief::findById($reliefId);
            if (!$relief || (int) ($relief['is_active'] ?? 0) !== 1) {
                $status = 'BLOCKED';
                $issues[] = 'Selected relief is inactive or invalid.';
            }
            if ($subDutyId !== null && (int) ($relief['subduty_id'] ?? 0) !== $subDutyId) {
                $status = 'BLOCKED';
                $issues[] = 'Relief does not belong to the selected sub-duty.';
            }
        }

        if (self::hasAvailabilityBlock($personnelId, $date)) {
            $status = 'BLOCKED';
            $issues[] = 'Personnel is on Leave for that date.';
        }

        if (self::hasSameDayConflict($personnelId, $date, $assignment)) {
            $status = 'BLOCKED';
            $issues[] = 'Personnel already has a schedule on the same date that overlaps.';
        }

        if (!self::isRankEligibleForDuty($personnelId, $dutyId, $subDutyId)) {
            $status = 'BLOCKED';
            $issues[] = 'Personnel rank is not eligible for this duty.';
        }

        $warningIssues = [];
        $normalDuty = self::normalPrimaryDutyForPersonnel($personnelId, $dutyId);
        if ($normalDuty !== null && (int) $normalDuty['id'] !== $dutyId) {
            $warningIssues[] = 'Duty differs from the person\'s normal primary duty assignment.';
        }

        $rotationBypass = self::rotationBypassWarning($personnelId, $dutyId, $date, $assignment);
        if ($rotationBypass !== null) {
            $warningIssues[] = $rotationBypass;
        }

        $sentinelRepeat = self::sentinelRepeatWarning($personnelId, $subDutyId, $date, $assignment);
        if ($sentinelRepeat !== null) {
            $warningIssues[] = $sentinelRepeat;
        }

        $restGapWarning = self::restGapWarning($personnelId, $date, $dutyId);
        if ($restGapWarning !== null) {
            $warningIssues[] = $restGapWarning;
        }

        $recentDutyWarning = self::recentDutyWarning($personnelId, $dutyId, $date);
        if ($recentDutyWarning !== null) {
            $warningIssues[] = $recentDutyWarning;
        }

        if (!empty($warningIssues)) {
            if ($status === 'RECOMMENDED') {
                $status = 'WARNING';
            }
            $issues = array_merge($issues, $warningIssues);
        }

        if ($status === 'RECOMMENDED') {
            $issues[] = 'Primary eligibility and availability are acceptable.';
        }

        return [
            'status' => $status,
            'issues' => array_values(array_unique($issues)),
            'severity' => strtolower($status),
            'normal_duty' => $normalDuty,
        ];
    }

    public static function isRankEligibleForDuty(int $personnelId, int $dutyId, ?int $subDutyId = null): bool
    {
        $personnel = Personnel::findById($personnelId);
        if (!$personnel) {
            return false;
        }

        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT 1 FROM rank_duty_eligibility
             WHERE rank_id = :rank_id
               AND duty_id = :duty_id
               AND is_active = 1
               AND (:subduty_id IS NULL OR subduty_id IS NULL OR subduty_id = :requested_subduty_id)
             LIMIT 1'
        );
        $statement->execute([
            ':rank_id' => (int) ($personnel['rank_id'] ?? 0),
            ':duty_id' => $dutyId,
            ':subduty_id' => $subDutyId,
            ':requested_subduty_id' => $subDutyId,
        ]);

        return (bool) $statement->fetchColumn();
    }

    public static function hasAvailabilityBlock(int $personnelId, string $date): bool
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

        return (bool) $statement->fetchColumn();
    }

    public static function hasSameDayConflict(int $personnelId, string $date, array $assignment): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT s.id, s.start_time AS assignment_start_time, s.end_time AS assignment_end_time,
                    d.start_time AS duty_start_time, d.end_time AS duty_end_time
             FROM schedules s
             LEFT JOIN duties d ON d.id = s.duty_id
             WHERE s.personnel_id = :personnel_id
               AND s.schedule_date = :schedule_date
               AND s.status != :status'
        );
        $statement->execute([
            ':personnel_id' => $personnelId,
            ':schedule_date' => $date,
            ':status' => 'overridden',
        ]);

        $rows = $statement->fetchAll() ?: [];
        $newStart = trim((string) ($assignment['start_time'] ?? '')) !== '' ? (string) $assignment['start_time'] : null;
        $newEnd = trim((string) ($assignment['end_time'] ?? '')) !== '' ? (string) $assignment['end_time'] : null;

        if ($newStart === null && $newEnd === null) {
            foreach ($rows as $row) {
                $existingStart = $row['assignment_start_time'] ?? null;
                $existingEnd = $row['assignment_end_time'] ?? null;
                if ($existingStart === null && $existingEnd === null) {
                    $existingStart = $row['duty_start_time'] ?? null;
                    $existingEnd = $row['duty_end_time'] ?? null;
                }
                if ($existingStart === null && $existingEnd === null) {
                    return true;
                }
            }
            return !empty($rows);
        }

        foreach ($rows as $row) {
            $existingStart = $row['assignment_start_time'] ?? null;
            $existingEnd = $row['assignment_end_time'] ?? null;
            if ($existingStart === null && $existingEnd === null) {
                $existingStart = $row['duty_start_time'] ?? null;
                $existingEnd = $row['duty_end_time'] ?? null;
            }

            if ($existingStart === null && $existingEnd === null) {
                return true;
            }

            $existingStartTs = $existingStart !== null ? strtotime($date . ' ' . $existingStart) : null;
            $existingEndTs = $existingEnd !== null ? strtotime($date . ' ' . $existingEnd) : null;
            $newStartTs = $newStart !== null ? strtotime($date . ' ' . $newStart) : null;
            $newEndTs = $newEnd !== null ? strtotime($date . ' ' . $newEnd) : null;

            if ($newStartTs === null || $newEndTs === null) {
                return true;
            }

            if ($existingStartTs === null || $existingEndTs === null) {
                return true;
            }

            if ($newStartTs < $existingEndTs && $newEndTs > $existingStartTs) {
                return true;
            }
        }

        return false;
    }

    public static function normalPrimaryDutyForPersonnel(int $personnelId, int $dutyId): ?array
    {
        $personnel = Personnel::findById($personnelId);
        if (!$personnel) {
            return null;
        }

        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT e.duty_id, d.duty_name, d.duty_code
             FROM rank_duty_eligibility e
             LEFT JOIN duties d ON d.id = e.duty_id
             WHERE e.rank_id = :rank_id AND e.is_active = 1 AND e.priority = 1 AND d.is_active = 1
             ORDER BY e.id ASC
             LIMIT 1'
        );
        $statement->execute([':rank_id' => (int) ($personnel['rank_id'] ?? 0)]);
        $row = $statement->fetch();

        if (!$row) {
            return null;
        }

        $row['id'] = (int) $row['duty_id'];
        return $row;
    }

    public static function rotationBypassWarning(int $personnelId, int $dutyId, string $date, array $assignment): ?string
    {
        $group = RotationEngine::dutyGroupForDuty($dutyId);
        if (!$group) {
            return null;
        }

        $recommendedPersonId = !empty($assignment['recommended_personnel_id']) ? (int) $assignment['recommended_personnel_id'] : 0;
        if ($recommendedPersonId > 0 && $recommendedPersonId !== $personnelId) {
            return 'Selected member bypasses the current rotation recommendation.';
        }

        return null;
    }

    public static function sentinelRepeatWarning(int $personnelId, ?int $subDutyId, string $date, array $assignment): ?string
    {
        if ($subDutyId === null || $subDutyId <= 0) {
            return null;
        }

        $reliefId = !empty($assignment['relief_id']) ? (int) $assignment['relief_id'] : 0;
        if ($reliefId <= 0) {
            return null;
        }

        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT s.id
             FROM schedules s
             WHERE s.personnel_id = :personnel_id
               AND s.subduty_id = :subduty_id
               AND s.relief_id = :relief_id
               AND s.schedule_date < :date
             ORDER BY s.schedule_date DESC, s.id DESC
             LIMIT 1'
        );
        $statement->execute([
            ':personnel_id' => $personnelId,
            ':subduty_id' => $subDutyId,
            ':relief_id' => $reliefId,
            ':date' => $date,
        ]);

        if ($statement->fetchColumn()) {
            return 'This sentinel relief repeats a recent assignment within the same cycle.';
        }

        return null;
    }

    public static function restGapWarning(int $personnelId, string $date, int $dutyId): ?string
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT s.schedule_date, d.end_time
             FROM schedules s
             LEFT JOIN duties d ON d.id = s.duty_id
             WHERE s.personnel_id = :personnel_id
               AND s.schedule_date < :date
             ORDER BY s.schedule_date DESC, s.id DESC
             LIMIT 1'
        );
        $statement->execute([
            ':personnel_id' => $personnelId,
            ':date' => $date,
        ]);
        $row = $statement->fetch();

        if (!$row) {
            return null;
        }

        $setting = (int) ($_SESSION['settings']['rest_gap_hours'] ?? 12);
        $newDuty = Duty::findById($dutyId);
        if (!$newDuty || !isset($newDuty['start_time'])) {
            return null;
        }

        $lastDutyEnd = $row['end_time'] ?? '23:59:00';
        $hoursDiff = round((strtotime($date . ' ' . $newDuty['start_time']) - strtotime($row['schedule_date'] . ' ' . $lastDutyEnd)) / 3600, 2);

        if ($hoursDiff < $setting) {
            return 'Back-to-back duty is too close for the required rest gap.';
        }

        return null;
    }

    public static function recentDutyWarning(int $personnelId, int $dutyId, string $date): ?string
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT COUNT(*)
             FROM schedules s
             WHERE s.personnel_id = :personnel_id
               AND s.duty_id = :duty_id
               AND s.schedule_date >= DATE_SUB(:date, INTERVAL 30 DAY)'
        );
        $statement->execute([
            ':personnel_id' => $personnelId,
            ':duty_id' => $dutyId,
            ':date' => $date,
        ]);

        if ((int) $statement->fetchColumn() > 0) {
            return 'Personnel has recently performed this duty within the frequency window.';
        }

        return null;
    }
}
