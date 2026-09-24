<?php
declare(strict_types=1);

class RotationMember
{
    public static function allForGroup(int $groupId): array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT m.*, p.first_name, p.last_name, r.rank_abbr
             FROM duty_rotation_members m
             LEFT JOIN personnel p ON p.id = m.personnel_id
             LEFT JOIN ranks r ON r.id = p.rank_id
             WHERE m.rotation_group_id = :rotation_group_id
             ORDER BY m.sequence ASC, m.id ASC'
        );
        $statement->execute([':rotation_group_id' => $groupId]);
        return $statement->fetchAll() ?: [];
    }

    public static function create(int $groupId, int $personnelId, int $sequence): int
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'INSERT INTO duty_rotation_members (rotation_group_id, personnel_id, sequence, is_active)
             VALUES (:rotation_group_id, :personnel_id, :sequence, :is_active)'
        );
        $statement->execute([
            ':rotation_group_id' => $groupId,
            ':personnel_id' => $personnelId,
            ':sequence' => $sequence,
            ':is_active' => 1,
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function sync(int $groupId, array $personnelIds): void
    {
        $existing = self::allForGroup($groupId);
        $existingMap = [];
        foreach ($existing as $row) {
            $existingMap[(int) $row['personnel_id']] = (int) $row['id'];
        }

        foreach ($personnelIds as $index => $personnelId) {
            $personnelId = (int) $personnelId;
            if ($personnelId <= 0) {
                continue;
            }

            if (isset($existingMap[$personnelId])) {
                $pdo = get_db();
                $statement = $pdo->prepare('UPDATE duty_rotation_members SET sequence = :sequence WHERE id = :id');
                $statement->execute([':sequence' => $index + 1, ':id' => $existingMap[$personnelId]]);
                unset($existingMap[$personnelId]);
                continue;
            }

            self::create($groupId, $personnelId, $index + 1);
        }

        if (!empty($existingMap)) {
            $pdo = get_db();
            $statement = $pdo->prepare('DELETE FROM duty_rotation_members WHERE id = :id');
            foreach ($existingMap as $id) {
                $statement->execute([':id' => $id]);
            }
        }
    }
}
