<?php
declare(strict_types=1);

class PodController
{
    public function index(): void
    {
        require_permission('pod.manage');

        $pageTitle = 'Plan of the Day';
        $date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
        $pods = PodService::listRecent();
        $viewFile = APP_ROOT . '/views/pod/list.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function create(): void
    {
        require_permission('pod.manage');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_verify($_POST['csrf_token'] ?? null)) {
                flash('error', 'Security token expired. Please try again.');
                redirect(APP_PUBLIC_URL . '/?route=pod/index');
            }

            $date = trim((string) ($_POST['pod_date'] ?? date('Y-m-d')));
            try {
                $pod = PodService::generateDraftForDate($date, current_user()['id'] ?? 0);
                if (!empty($pod['id'])) {
                    flash('success', 'POD draft created successfully.');
                    redirect(APP_PUBLIC_URL . '/?route=pod/view&id=' . (int) $pod['id']);
                }
            } catch (Throwable $exception) {
                flash('error', $exception->getMessage());
                redirect(APP_PUBLIC_URL . '/?route=pod/index&date=' . urlencode($date));
            }
        }

        $pageTitle = 'Create POD';
        $viewFile = APP_ROOT . '/views/pod/create.php';
        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function view(): void
    {
        require_permission('pod.manage');

        $podId = (int) ($_GET['id'] ?? 0);
        $pod = PodService::findById($podId);
        if (!$pod) {
            flash('error', 'POD not found.');
            redirect(APP_PUBLIC_URL . '/?route=pod/index');
        }

        $pageTitle = 'POD Details';
        $viewFile = APP_ROOT . '/views/pod/view.php';
        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function preview(): void
    {
        require_permission('pod.manage');

        $podId = (int) ($_GET['id'] ?? 0);
        $pod = PodService::findById($podId);
        if (!$pod) {
            flash('error', 'POD not found.');
            redirect(APP_PUBLIC_URL . '/?route=pod/index');
        }

        $pageTitle = 'POD Preview';
        $viewFile = APP_ROOT . '/views/pod/preview.php';
        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function print(): void
    {
        require_permission('pod.manage');

        $podId = (int) ($_GET['id'] ?? 0);
        $pod = PodService::findById($podId);
        if (!$pod) {
            flash('error', 'POD not found.');
            redirect(APP_PUBLIC_URL . '/?route=pod/index');
        }

        $pageTitle = 'Print POD';
        $viewFile = APP_ROOT . '/views/pod/print.php';
        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function finalize(): void
    {
        require_permission('pod.manage');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_PUBLIC_URL . '/?route=pod/index');
        }

        $podId = (int) ($_POST['pod_id'] ?? 0);
        if ($podId <= 0) {
            flash('error', 'POD not found.');
            redirect(APP_PUBLIC_URL . '/?route=pod/index');
        }

        $userId = current_user()['id'] ?? 0;
        if (!PodService::finalizePod($podId, $userId)) {
            flash('error', 'Unable to finalize this POD.');
            redirect(APP_PUBLIC_URL . '/?route=pod/view&id=' . $podId);
        }

        flash('success', 'POD finalized successfully.');
        redirect(APP_PUBLIC_URL . '/?route=pod/view&id=' . $podId);
    }

    public function revise(): void
    {
        require_permission('pod.manage');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_PUBLIC_URL . '/?route=pod/index');
        }

        $podId = (int) ($_POST['pod_id'] ?? 0);
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $newPersonnelId = (int) ($_POST['personnel_id'] ?? 0);
        $reason = trim((string) ($_POST['reason'] ?? ''));

        $pod = PodService::findById($podId);
        if (!$pod || !$itemId || !$newPersonnelId) {
            flash('error', 'Unable to update this POD item.');
            redirect(APP_PUBLIC_URL . '/?route=pod/view&id=' . $podId);
        }

        $item = PodService::findItemById($itemId);
        if (!$item) {
            flash('error', 'Item not found.');
            redirect(APP_PUBLIC_URL . '/?route=pod/view&id=' . $podId);
        }

        $newUserId = current_user()['id'] ?? 0;
        if ((string) ($pod['status'] ?? '') === 'final') {
            if ($reason === '') {
                flash('error', 'A revision reason is required before updating a finalized POD.');
                redirect(APP_PUBLIC_URL . '/?route=pod/view&id=' . $podId);
            }

            if (!PodService::addRevision($podId, $itemId, (int) $item['personnel_id'], $newPersonnelId, $reason, $newUserId)) {
                flash('error', 'Unable to store the revision history.');
                redirect(APP_PUBLIC_URL . '/?route=pod/view&id=' . $podId);
            }
        }

        if (!PodService::updateItemAssignment($itemId, $newPersonnelId)) {
            flash('error', 'Unable to update the POD item.');
            redirect(APP_PUBLIC_URL . '/?route=pod/view&id=' . $podId);
        }

        if ((string) ($pod['status'] ?? '') === 'final') {
            flash('success', 'Finalized POD updated and revision recorded.');
        } else {
            flash('success', 'POD draft updated.');
        }

        redirect(APP_PUBLIC_URL . '/?route=pod/view&id=' . $podId);
    }

    public function cancel(): void
    {
        require_permission('pod.manage');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_PUBLIC_URL . '/?route=pod/index');
        }

        $podId = (int) ($_POST['pod_id'] ?? 0);
        $reason = trim((string) ($_POST['reason'] ?? ''));
        $userId = current_user()['id'] ?? 0;

        if ($podId <= 0) {
            flash('error', 'POD not found.');
            redirect(APP_PUBLIC_URL . '/?route=pod/index');
        }

        if (!PodService::cancelPod($podId, $reason, $userId)) {
            flash('error', 'Unable to cancel this POD.');
            redirect(APP_PUBLIC_URL . '/?route=pod/view&id=' . $podId);
        }

        flash('success', 'POD cancelled.');
        redirect(APP_PUBLIC_URL . '/?route=pod/index');
    }

    public function revisions(): void
    {
        require_permission('pod.revisions');

        $podId = (int) ($_GET['id'] ?? 0);
        $pod = PodService::findById($podId);
        if (!$pod) {
            flash('error', 'POD not found.');
            redirect(APP_PUBLIC_URL . '/?route=pod/index');
        }

        $revisions = PodService::revisionsForPod($podId);
        $pageTitle = 'POD Revisions';
        $viewFile = APP_ROOT . '/views/pod/revisions.php';
        include APP_ROOT . '/views/layouts/layout.php';
    }
}
