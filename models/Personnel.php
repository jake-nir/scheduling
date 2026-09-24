<?php
declare(strict_types=1);

class Personnel
{
    public static function all(): array
    {
        $pdo = get_db();
        $statement = $pdo->query(
            'SELECT p.*, r.rank_abbr, r.rank_name
             FROM personnel p
             LEFT JOIN ranks r ON r.id = p.rank_id
             ORDER BY p.last_name ASC, p.first_name ASC'
        );
        return $statement->fetchAll() ?: [];
    }

    public static function findById(int $id): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT p.*, r.rank_abbr, r.rank_name
             FROM personnel p
             LEFT JOIN ranks r ON r.id = p.rank_id
             WHERE p.id = :id
             LIMIT 1'
        );
        $statement->execute([':id' => $id]);
        $person = $statement->fetch();
        return $person ?: null;
    }

    public static function findByServiceNumber(string $serviceNumber): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM personnel WHERE service_number = :service_number LIMIT 1');
        $statement->execute([':service_number' => trim($serviceNumber)]);
        return $statement->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'INSERT INTO personnel (service_number, first_name, middle_name, last_name, suffix, designation, unit, contact_number, email, status, rank_id)
             VALUES (:service_number, :first_name, :middle_name, :last_name, :suffix, :designation, :unit, :contact_number, :email, :status, :rank_id)'
        );
        $statement->execute([
            ':service_number' => trim((string) ($data['service_number'] ?? '')),
            ':first_name' => trim((string) ($data['first_name'] ?? '')),
            ':middle_name' => trim((string) ($data['middle_name'] ?? '')),
            ':last_name' => trim((string) ($data['last_name'] ?? '')),
            ':suffix' => trim((string) ($data['suffix'] ?? '')),
            ':designation' => trim((string) ($data['designation'] ?? '')),
            ':unit' => trim((string) ($data['unit'] ?? '')),
            ':contact_number' => trim((string) ($data['contact_number'] ?? '')),
            ':email' => trim((string) ($data['email'] ?? '')),
            ':status' => isset($data['status']) ? (string) $data['status'] : 'active',
            ':rank_id' => (int) ($data['rank_id'] ?? 0),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'UPDATE personnel SET service_number = :service_number, first_name = :first_name, middle_name = :middle_name, last_name = :last_name, suffix = :suffix, designation = :designation, unit = :unit, contact_number = :contact_number, email = :email, status = :status, rank_id = :rank_id WHERE id = :id'
        );

        return $statement->execute([
            ':service_number' => trim((string) ($data['service_number'] ?? '')),
            ':first_name' => trim((string) ($data['first_name'] ?? '')),
            ':middle_name' => trim((string) ($data['middle_name'] ?? '')),
            ':last_name' => trim((string) ($data['last_name'] ?? '')),
            ':suffix' => trim((string) ($data['suffix'] ?? '')),
            ':designation' => trim((string) ($data['designation'] ?? '')),
            ':unit' => trim((string) ($data['unit'] ?? '')),
            ':contact_number' => trim((string) ($data['contact_number'] ?? '')),
            ':email' => trim((string) ($data['email'] ?? '')),
            ':status' => isset($data['status']) ? (string) $data['status'] : 'active',
            ':rank_id' => (int) ($data['rank_id'] ?? 0),
            ':id' => $id,
        ]);
    }

    public static function setStatus(int $id, string $status): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('UPDATE personnel SET status = :status WHERE id = :id');
        return $statement->execute([':status' => $status, ':id' => $id]);
    }

    public static function delete(int $id): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('DELETE FROM personnel WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function displayName(array $person): string
    {
        $rankAbbr = $person['rank_abbr'] ?? '';
        $middleName = trim((string) ($person['middle_name'] ?? ''));
        $middleInitial = $middleName !== '' ? $middleName[0] . '.' : '';
        $suffix = trim((string) ($person['suffix'] ?? ''));
        $suffixText = $suffix !== '' ? ' ' . $suffix : '';
        return trim(sprintf('%s %s %s %s%s', $rankAbbr, $person['first_name'] ?? '', $middleInitial, $person['last_name'] ?? '', $suffixText));
    }
}
