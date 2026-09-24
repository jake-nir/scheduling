<?php
declare(strict_types=1);

class Schedule
{
    public static function allForDate(string $date): array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT s.*, d.duty_name, d.start_time AS duty_start_time, d.end_time AS duty_end_time,
                    p.first_name, p.last_name, p.service_number,
                    r.rank_abbr,
                    sd.subduty_name,
                    dr.relief_name
             FROM schedules s
             LEFT JOIN duties d ON d.id = s.duty_id
             LEFT JOIN duty_subduties sd ON sd.id = s.subduty_id
             LEFT JOIN duty_reliefs dr ON dr.id = s.relief_id
             LEFT JOIN personnel p ON p.id = s.personnel_id
             LEFT JOIN ranks r ON r.id = p.rank_id
             WHERE s.schedule_date = :schedule_date
             ORDER BY s.created_at DESC'
        );
        $statement->execute([':schedule_date' => $date]);
        return $statement->fetchAll() ?: [];
    }

    public static function findById(int $id): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM schedules WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        return $statement->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'INSERT INTO schedules (schedule_date, duty_id, subduty_id, relief_id, personnel_id, source, status, created_by)
             VALUES (:schedule_date, :duty_id, :subduty_id, :relief_id, :personnel_id, :source, :status, :created_by)'
        );
        $statement->execute([
            ':schedule_date' => trim((string) ($data['schedule_date'] ?? '')),
            ':duty_id' => (int) ($data['duty_id'] ?? 0),
            ':subduty_id' => !empty($data['subduty_id']) ? (int) $data['subduty_id'] : null,
            ':relief_id' => !empty($data['relief_id']) ? (int) $data['relief_id'] : null,
            ':personnel_id' => (int) ($data['personnel_id'] ?? 0),
            ':source' => isset($data['source']) ? (string) $data['source'] : 'manual',
            ':status' => isset($data['status']) ? (string) $data['status'] : 'planned',
            ':created_by' => (int) ($data['created_by'] ?? current_user()['id'] ?? 0),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function delete(int $id): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('DELETE FROM schedules WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function cancel(int $scheduleId, string $reason, int $userId): bool
    {
        $schedule = self::findById($scheduleId);
        if (!$schedule) {
            return false;
        }

        $pdo = get_db();
        $statement = $pdo->prepare('UPDATE schedules SET status = :status WHERE id = :id');
        $didUpdate = $statement->execute([
            ':status' => 'overridden',
            ':id' => $scheduleId,
        ]);

        $override = $pdo->prepare(
            'INSERT INTO schedule_overrides (schedule_id, personnel_id, assigned_duty_id, normal_duty_id, subduty_id, relief_id, override_type, reason, confirmed_by, confirmed_at)
             VALUES (:schedule_id, :personnel_id, :assigned_duty_id, :normal_duty_id, :subduty_id, :relief_id, :override_type, :reason, :confirmed_by, NOW())'
        );
        $override->execute([
            ':schedule_id' => $scheduleId,
            ':personnel_id' => (int) ($schedule['personnel_id'] ?? 0),
            ':assigned_duty_id' => (int) ($schedule['duty_id'] ?? 0),
            ':normal_duty_id' => null,
            ':subduty_id' => !empty($schedule['subduty_id']) ? (int) $schedule['subduty_id'] : null,
            ':relief_id' => !empty($schedule['relief_id']) ? (int) $schedule['relief_id'] : null,
            ':override_type' => 'availability_override',
            ':reason' => $reason !== '' ? $reason : 'Cancelled by scheduler.',
            ':confirmed_by' => $userId,
        ]);

        AuditLog::log('schedule.cancel', 'schedule', 'Schedule cancelled with override.', $userId, current_user()['username'] ?? '', 'schedules', $scheduleId, $_SERVER['REMOTE_ADDR'] ?? null);

        return $didUpdate;
    }
}
