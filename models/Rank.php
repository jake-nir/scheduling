<?php
declare(strict_types=1);

class Rank
{
    public static function all(): array
    {
        $pdo = get_db();
        $statement = $pdo->query('SELECT * FROM ranks ORDER BY rank_order DESC, id ASC');
        return $statement->fetchAll() ?: [];
    }

    public static function active(): array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM ranks WHERE is_active = 1 ORDER BY rank_order DESC, id ASC');
        $statement->execute();
        return $statement->fetchAll() ?: [];
    }

    public static function findById(int $id): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM ranks WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $rank = $statement->fetch();
        return $rank ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'INSERT INTO ranks (rank_name, rank_abbr, rank_order, is_active) VALUES (:rank_name, :rank_abbr, :rank_order, :is_active)'
        );
        $statement->execute([
            ':rank_name' => trim((string) ($data['rank_name'] ?? '')),
            ':rank_abbr' => trim((string) ($data['rank_abbr'] ?? '')),
            ':rank_order' => (int) ($data['rank_order'] ?? 0),
            ':is_active' => (int) (($data['is_active'] ?? 1) === 1),
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'UPDATE ranks SET rank_name = :rank_name, rank_abbr = :rank_abbr, rank_order = :rank_order, is_active = :is_active WHERE id = :id'
        );
        return $statement->execute([
            ':rank_name' => trim((string) ($data['rank_name'] ?? '')),
            ':rank_abbr' => trim((string) ($data['rank_abbr'] ?? '')),
            ':rank_order' => (int) ($data['rank_order'] ?? 0),
            ':is_active' => (int) (($data['is_active'] ?? 1) === 1),
            ':id' => $id,
        ]);
    }

    public static function reorder(int $id, int $newOrder): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('UPDATE ranks SET rank_order = :rank_order WHERE id = :id');
        return $statement->execute([':rank_order' => $newOrder, ':id' => $id]);
    }

    public static function delete(int $id): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('DELETE FROM ranks WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function displayName(?array $rank, string $firstName, string $middleName, string $lastName): string
    {
        $abbr = $rank['rank_abbr'] ?? '';
        $middle = trim($middleName);
        $middleInitial = $middle !== '' ? $middle[0] . '.' : '';
        return trim(sprintf('%s %s %s %s', $abbr, $firstName, $middleInitial, $lastName));
    }
}
