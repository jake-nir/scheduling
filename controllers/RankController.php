<?php
declare(strict_types=1);

class RankController
{
    public function index(): void
    {
        require_permission('personnel.manage');

        $pageTitle = 'Ranks';
        $ranks = Rank::all();
        $viewFile = APP_ROOT . '/views/ranks/list.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function create(): void
    {
        require_permission('personnel.manage');

        $pageTitle = 'Create Rank';
        $viewFile = APP_ROOT . '/views/ranks/form.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function edit(): void
    {
        require_permission('personnel.manage');

        $id = (int) ($_GET['id'] ?? 0);
        $rank = Rank::findById($id);
        if (!$rank) {
            flash('error', 'Rank not found.');
            redirect(APP_PUBLIC_URL . '/?route=ranks/index');
        }

        $pageTitle = 'Edit Rank';
        $viewFile = APP_ROOT . '/views/ranks/form.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function save(): void
    {
        require_permission('personnel.manage');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_PUBLIC_URL . '/?route=ranks/index');
        }

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Security token expired. Please try again.');
            redirect(APP_PUBLIC_URL . '/?route=ranks/index');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $rankName = trim((string) ($_POST['rank_name'] ?? ''));
        $rankAbbr = trim((string) ($_POST['rank_abbr'] ?? ''));
        $rankOrder = (int) ($_POST['rank_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($rankName === '' || $rankAbbr === '') {
            flash('error', 'Rank name and abbreviation are required.');
            redirect(APP_PUBLIC_URL . '/?route=ranks/create');
        }

        $payload = [
            'rank_name' => $rankName,
            'rank_abbr' => $rankAbbr,
            'rank_order' => $rankOrder,
            'is_active' => $isActive,
        ];

        if ($id > 0) {
            Rank::update($id, $payload);
            flash('success', 'Rank updated successfully.');
            AuditLog::log('rank.update', 'ranks', 'Rank updated.', current_user()['id'] ?? null, current_user()['username'] ?? null, 'ranks', $id, $_SERVER['REMOTE_ADDR'] ?? null);
        } else {
            Rank::create($payload);
            flash('success', 'Rank created successfully.');
            AuditLog::log('rank.create', 'ranks', 'Rank created.', current_user()['id'] ?? null, current_user()['username'] ?? null, 'ranks', null, $_SERVER['REMOTE_ADDR'] ?? null);
        }

        redirect(APP_PUBLIC_URL . '/?route=ranks/index');
    }

    public function reorder(): void
    {
        require_permission('personnel.manage');

        $id = (int) ($_POST['id'] ?? 0);
        $rankOrder = (int) ($_POST['rank_order'] ?? 0);
        if ($id > 0) {
            Rank::reorder($id, $rankOrder);
            AuditLog::log('rank.reorder', 'ranks', 'Rank reordered.', current_user()['id'] ?? null, current_user()['username'] ?? null, 'ranks', $id, $_SERVER['REMOTE_ADDR'] ?? null);
        }

        redirect(APP_PUBLIC_URL . '/?route=ranks/index');
    }
}
