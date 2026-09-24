<?php
declare(strict_types=1);

class PodService
{
    public static function listRecent(): array
    {
        $pdo = get_db();
        $statement = $pdo->query(
            'SELECT p.*, u.full_name AS created_by_name, au.full_name AS approved_by_name
             FROM plan_of_day p
             LEFT JOIN users u ON u.id = p.created_by
             LEFT JOIN users au ON au.id = p.approved_by
             ORDER BY p.pod_date DESC, p.id DESC'
        );

        return $statement->fetchAll() ?: [];
    }

    public static function findById(int $id): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT p.*, u.full_name AS created_by_name, au.full_name AS approved_by_name
             FROM plan_of_day p
             LEFT JOIN users u ON u.id = p.created_by
             LEFT JOIN users au ON au.id = p.approved_by
             WHERE p.id = :id LIMIT 1'
        );
        $statement->execute([':id' => $id]);
        $pod = $statement->fetch();
        if (!$pod) {
            return null;
        }

        $pod['items'] = self::itemsForPod($id);
        return $pod;
    }

    public static function findLatestForDate(string $date): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT * FROM plan_of_day WHERE pod_date = :pod_date ORDER BY id DESC LIMIT 1');
        $statement->execute([':pod_date' => $date]);
        $pod = $statement->fetch();
        if (!$pod) {
            return null;
        }

        $pod['items'] = self::itemsForPod((int) $pod['id']);
        return $pod;
    }

    public static function generateDraftForDate(string $date, int $userId): array
    {
        $date = trim($date);
        if (!validate_date($date)) {
            throw new InvalidArgumentException('Invalid date supplied for POD generation.');
        }

        $existing = self::findLatestForDate($date);
        if ($existing && $existing['status'] !== 'cancelled') {
            return $existing;
        }

        $pdo = get_db();
        $referenceNumber = 'POD-' . str_replace('-', '', $date) . '-' . date('Hi');
        $insert = $pdo->prepare(
            'INSERT INTO plan_of_day (pod_date, reference_number, status, version, created_by, approved_by, approved_at)
             VALUES (:pod_date, :reference_number, :status, 1, :created_by, NULL, NULL)'
        );
        $insert->execute([
            ':pod_date' => $date,
            ':reference_number' => $referenceNumber,
            ':status' => 'draft',
            ':created_by' => $userId,
        ]);

        $podId = (int) $pdo->lastInsertId();
        $schedules = self::scheduleRowsForDate($date);
        $displayOrder = 1;

        foreach ($schedules as $schedule) {
            $itemInsert = $pdo->prepare(
                'INSERT INTO plan_of_day_items (pod_id, schedule_id, duty_id, subduty_id, relief_id, personnel_id, display_order)
                 VALUES (:pod_id, :schedule_id, :duty_id, :subduty_id, :relief_id, :personnel_id, :display_order)'
            );
            $itemInsert->execute([
                ':pod_id' => $podId,
                ':schedule_id' => !empty($schedule['id']) ? (int) $schedule['id'] : null,
                ':duty_id' => (int) ($schedule['duty_id'] ?? 0),
                ':subduty_id' => !empty($schedule['subduty_id']) ? (int) $schedule['subduty_id'] : null,
                ':relief_id' => !empty($schedule['relief_id']) ? (int) $schedule['relief_id'] : null,
                ':personnel_id' => (int) ($schedule['personnel_id'] ?? 0),
                ':display_order' => $displayOrder++,
            ]);
        }

        $pod = self::findById($podId);
        AuditLog::log('pod.create', 'pod', 'POD draft created for ' . $date, $userId, current_user()['username'] ?? null, 'plan_of_day', $podId, $_SERVER['REMOTE_ADDR'] ?? null);

        return $pod ?: [];
    }

    public static function scheduleRowsForDate(string $date): array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT s.*, d.duty_name, d.sort_order AS duty_sort_order,
                    sd.subduty_name,
                    dr.relief_name,
                    p.first_name, p.last_name, p.service_number,
                    r.rank_abbr
             FROM schedules s
             LEFT JOIN duties d ON d.id = s.duty_id
             LEFT JOIN duty_subduties sd ON sd.id = s.subduty_id
             LEFT JOIN duty_reliefs dr ON dr.id = s.relief_id
             LEFT JOIN personnel p ON p.id = s.personnel_id
             LEFT JOIN ranks r ON r.id = p.rank_id
             WHERE s.schedule_date = :schedule_date
               AND s.status != :cancelled_status
             ORDER BY d.sort_order IS NULL, d.sort_order ASC,
                      sd.sort_order IS NULL, sd.sort_order ASC,
                      dr.relief_order IS NULL, dr.relief_order ASC,
                      p.last_name ASC, p.first_name ASC'
        );
        $statement->execute([
            ':schedule_date' => $date,
            ':cancelled_status' => 'overridden',
        ]);

        return $statement->fetchAll() ?: [];
    }

    public static function itemsForPod(int $podId): array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT i.*, d.duty_name, d.duty_code, d.start_time AS duty_start_time, d.end_time AS duty_end_time,
                    sd.subduty_name,
                    dr.relief_name,
                    p.first_name, p.last_name, p.service_number,
                    r.rank_abbr
             FROM plan_of_day_items i
             LEFT JOIN duties d ON d.id = i.duty_id
             LEFT JOIN duty_subduties sd ON sd.id = i.subduty_id
             LEFT JOIN duty_reliefs dr ON dr.id = i.relief_id
             LEFT JOIN personnel p ON p.id = i.personnel_id
             LEFT JOIN ranks r ON r.id = p.rank_id
             WHERE i.pod_id = :pod_id
             ORDER BY i.display_order ASC, i.id ASC'
        );
        $statement->execute([':pod_id' => $podId]);
        return $statement->fetchAll() ?: [];
    }

    public static function findItemById(int $itemId): ?array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT i.*, d.duty_name, sd.subduty_name, dr.relief_name, p.first_name, p.last_name, p.service_number, r.rank_abbr
             FROM plan_of_day_items i
             LEFT JOIN duties d ON d.id = i.duty_id
             LEFT JOIN duty_subduties sd ON sd.id = i.subduty_id
             LEFT JOIN duty_reliefs dr ON dr.id = i.relief_id
             LEFT JOIN personnel p ON p.id = i.personnel_id
             LEFT JOIN ranks r ON r.id = p.rank_id
             WHERE i.id = :id LIMIT 1'
        );
        $statement->execute([':id' => $itemId]);
        return $statement->fetch() ?: null;
    }

    public static function updateItemAssignment(int $itemId, int $personnelId): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('UPDATE plan_of_day_items SET personnel_id = :personnel_id WHERE id = :id');
        return $statement->execute([
            ':personnel_id' => $personnelId,
            ':id' => $itemId,
        ]);
    }

    public static function finalizePod(int $podId, int $userId): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('UPDATE plan_of_day SET status = :status, approved_by = :approved_by, approved_at = NOW() WHERE id = :id');
        $updated = $statement->execute([
            ':status' => 'final',
            ':approved_by' => $userId,
            ':id' => $podId,
        ]);

        if ($updated) {
            AuditLog::log('pod.finalize', 'pod', 'POD finalized.', $userId, current_user()['username'] ?? null, 'plan_of_day', $podId, $_SERVER['REMOTE_ADDR'] ?? null);
        }

        return $updated;
    }

    public static function cancelPod(int $podId, string $reason, int $userId): bool
    {
        $pdo = get_db();
        $statement = $pdo->prepare('UPDATE plan_of_day SET status = :status WHERE id = :id');
        $updated = $statement->execute([
            ':status' => 'cancelled',
            ':id' => $podId,
        ]);

        if ($updated) {
            AuditLog::log('pod.cancel', 'pod', $reason !== '' ? $reason : 'POD cancelled.', $userId, current_user()['username'] ?? null, 'plan_of_day', $podId, $_SERVER['REMOTE_ADDR'] ?? null);
        }

        return $updated;
    }

    public static function addRevision(int $podId, int $itemId, int $oldPersonnelId, int $newPersonnelId, string $reason, int $changedBy): bool
    {
        $pod = self::findById($podId);
        if (!$pod) {
            return false;
        }

        $version = (int) ($pod['version'] ?? 1) + 1;
        $pdo = get_db();
        $updatePod = $pdo->prepare('UPDATE plan_of_day SET version = :version WHERE id = :id');
        $updatePod->execute([':version' => $version, ':id' => $podId]);

        $item = self::findItemById($itemId);
        $statement = $pdo->prepare(
            'INSERT INTO plan_of_day_revisions (pod_id, version, old_item_id, new_item_id, old_personnel_id, new_personnel_id, duty_id, reason, changed_by, changed_at)
             VALUES (:pod_id, :version, :old_item_id, :new_item_id, :old_personnel_id, :new_personnel_id, :duty_id, :reason, :changed_by, NOW())'
        );
        $executed = $statement->execute([
            ':pod_id' => $podId,
            ':version' => $version,
            ':old_item_id' => $itemId,
            ':new_item_id' => $itemId,
            ':old_personnel_id' => $oldPersonnelId,
            ':new_personnel_id' => $newPersonnelId,
            ':duty_id' => !empty($item['duty_id']) ? (int) $item['duty_id'] : null,
            ':reason' => $reason,
            ':changed_by' => $changedBy,
        ]);

        if ($executed) {
            AuditLog::log('pod.revise', 'pod', 'POD revision recorded for item ' . $itemId . '.', $changedBy, current_user()['username'] ?? null, 'plan_of_day', $podId, $_SERVER['REMOTE_ADDR'] ?? null);
        }

        return $executed;
    }

    public static function revisionsForPod(int $podId): array
    {
        $pdo = get_db();
        $statement = $pdo->prepare(
            'SELECT r.*, d.duty_name, u.full_name AS changed_by_name,
                    old_personnel.first_name AS old_first_name, old_personnel.last_name AS old_last_name,
                    new_personnel.first_name AS new_first_name, new_personnel.last_name AS new_last_name
             FROM plan_of_day_revisions r
             LEFT JOIN duties d ON d.id = r.duty_id
             LEFT JOIN users u ON u.id = r.changed_by
             LEFT JOIN personnel old_personnel ON old_personnel.id = r.old_personnel_id
             LEFT JOIN personnel new_personnel ON new_personnel.id = r.new_personnel_id
             WHERE r.pod_id = :pod_id
             ORDER BY r.version DESC, r.changed_at DESC, r.id DESC'
        );
        $statement->execute([':pod_id' => $podId]);
        return $statement->fetchAll() ?: [];
    }

    public static function personDisplayName(array $record): string
    {
        $first = trim((string) ($record['first_name'] ?? ''));
        $last = trim((string) ($record['last_name'] ?? ''));
        $rank = trim((string) ($record['rank_abbr'] ?? ''));
        $serviceNumber = trim((string) ($record['service_number'] ?? ''));

        if ($first === '' && $last === '') {
            return 'Unassigned';
        }

        $name = $first !== '' && $last !== '' ? trim($first . ' ' . $last) : ($first !== '' ? $first : $last);
        if ($rank !== '') {
            $name = $rank . ' ' . $name;
        }
        if ($serviceNumber !== '') {
            $name .= ' ' . $serviceNumber;
        }

        return $name;
    }

    public static function itemLabel(array $item): string
    {
        $duty = trim((string) ($item['duty_name'] ?? ''));
        $subDuty = trim((string) ($item['subduty_name'] ?? ''));
        $relief = trim((string) ($item['relief_name'] ?? ''));

        $label = $duty !== '' ? strtoupper($duty) : 'DUTY';
        if ($subDuty !== '') {
            $label .= ' — ' . strtoupper($subDuty);
        }
        if ($relief !== '') {
            $label .= ' — ' . strtoupper($relief);
        }

        return $label;
    }

    public static function setting(string $key, string $fallback = ''): string
    {
        $pdo = get_db();
        $statement = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = :setting_key LIMIT 1');
        $statement->execute([':setting_key' => $key]);
        $value = $statement->fetchColumn();
        return $value !== false && $value !== null ? (string) $value : $fallback;
    }
}
