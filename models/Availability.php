<?php
declare(strict_types=1);

class Availability
{
    public const EXCEPTION_STATUSES = [
        'Leave',
        'Schooling',
        'Sick',
        'Official Assignment',
        'Training',
        'Temporarily Unavailable',
        'Other',
    ];

    public static function isExceptionStatus(string $status): bool
    {
        return in_array(trim($status), self::EXCEPTION_STATUSES, true);
    }

    public static function allForPersonnel(int $personnelId): array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT * FROM personnel_availability WHERE personnel_id = :personnel_id ORDER BY start_date DESC, id DESC'
        );
        $statement->execute([':personnel_id' => $personnelId]);
        return $statement->fetchAll() ?: [];
    }

    public static function create(array $data): int
    {
        $status = trim((string) ($data['status'] ?? ''));
        if (!self::isExceptionStatus($status)) {
            throw new InvalidArgumentException('A valid unavailability type is required.');
        }

        $pdo = get_db();
        $statement = $pdo->prepare(
            'INSERT INTO personnel_availability (personnel_id, status, start_date, end_date, reason, remarks, created_by)
             VALUES (:personnel_id, :status, :start_date, :end_date, :reason, :remarks, :created_by)'
        );
        $statement->execute([
            ':personnel_id' => (int) ($data['personnel_id'] ?? 0),
            ':status' => $status,
            ':start_date' => trim((string) ($data['start_date'] ?? '')),
            ':end_date' => trim((string) ($data['end_date'] ?? '')) !== '' ? trim((string) $data['end_date']): null,
            ':reason' => trim((string) ($data['reason'] ?? '')),
            ':remarks' => trim((string) ($data['remarks'] ?? '')),
            ':created_by' => (int) (($data['created_by'] ?? current_user()['id'] ?? 0)),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function findById(int $id): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM personnel_availability WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        return $statement->fetch() ?: null;
    }

    public static function update(int $id, array $data): bool
    {
        $status = trim((string) ($data['status'] ?? ''));
        if (!self::isExceptionStatus($status)) {
            throw new InvalidArgumentException('A valid unavailability type is required.');
        }

        $pdo = get_db();
        $statement = $pdo->prepare(
            'UPDATE personnel_availability SET personnel_id = :personnel_id, status = :status, start_date = :start_date, end_date = :end_date, reason = :reason, remarks = :remarks WHERE id = :id'
        );
        return $statement->execute([
            ':personnel_id' => (int) ($data['personnel_id'] ?? 0),
            ':status' => $status,
            ':start_date' => trim((string) ($data['start_date'] ?? '')),
            ':end_date' => trim((string) ($data['end_date'] ?? '')) !== '' ? trim((string) $data['end_date']) : null,
            ':reason' => trim((string) ($data['reason'] ?? '')),
            ':remarks' => trim((string) ($data['remarks'] ?? '')),
            ':id' => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('DELETE FROM personnel_availability WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function overlapsForPerson(int $personnelId, string $startDate, string $endDate, ?int $excludeId = null): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT id FROM personnel_availability
             WHERE personnel_id = :personnel_id
               AND id != COALESCE(:exclude_id, -1)
               AND start_date <= :end_date
               AND (end_date IS NULL OR end_date >= :start_date)'
        );
        $statement->execute([
            ':personnel_id' => $personnelId,
            ':exclude_id' => $excludeId,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
        ]);
        return (bool) $statement->fetch();
    }

    public static function activeForPerson(int $personnelId): array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT * FROM personnel_availability
             WHERE personnel_id = :personnel_id
               AND (end_date IS NULL OR end_date >= CURDATE())
             ORDER BY start_date ASC'
        );
        $statement->execute([':personnel_id' => $personnelId]);
        return $statement->fetchAll() ?: [];
    }

    /**
     * Single source of truth for the "available by default" model:
     * personnel are available unless an unavailability exception covers the date.
     * Returns the covering exception record (status, start_date, end_date, reason, remarks)
     * or null when the personnel is available on that date.
     */
    public static function unavailableOnDate(int $personnelId, string $date): ?array
    {
        if (!validate_date($date)) {
            return null;
        }

        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT * FROM personnel_availability
             WHERE personnel_id = :personnel_id
               AND start_date <= :start_date
               AND (end_date IS NULL OR end_date >= :end_date)
             ORDER BY start_date DESC, id DESC
             LIMIT 1'
        );
        $statement->execute([
            ':personnel_id' => $personnelId,
            ':start_date' => $date,
            ':end_date' => $date,
        ]);

        $row = $statement->fetch();
        if ($row === false) {
            return null;
        }

        $row['available'] = false;
        return $row;
    }

    public static function isUnavailableOnDate(int $personnelId, string $date): bool
    {
        return self::unavailableOnDate($personnelId, $date) !== null;
    }
}