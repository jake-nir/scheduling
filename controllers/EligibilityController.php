<?php
declare(strict_types=1);

class EligibilityController
{
    public function index(): void
    {
        require_permission('dutyconfig.manage');

        $dutyId = (int) ($_GET['duty_id'] ?? 0);
        $duty = $dutyId > 0 ? Duty::findById($dutyId) : null;
        if (!$duty) {
            $duty = Duty::active()[0] ?? null;
            if (!$duty) {
                flash('error', 'No duty exists yet.');
                redirect(APP_PUBLIC_URL . '/?route=duties/index');
            }
            $dutyId = (int) $duty['id'];
        }

        $pageTitle = 'Eligibility Matrix: ' . ($duty['duty_name'] ?? 'Duty');
        $ranks = Rank::all();
        $eligibilityRows = Eligibility::allForDuty($dutyId);
        $subDuties = SubDuty::allForDuty($dutyId);
        $viewFile = APP_ROOT . '/views/duties/eligibility.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function save(): void
    {
        require_permission('dutyconfig.manage');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_PUBLIC_URL . '/?route=duties/index');
        }

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Security token expired. Please try again.');
            redirect(APP_PUBLIC_URL . '/?route=duties/index');
        }

        $dutyId = (int) ($_POST['duty_id'] ?? 0);
        $rows = $_POST['eligibility'] ?? [];
        $saved = 0;

        foreach ($rows as $rankId => $entry) {
            $rankId = (int) $rankId;
            $priority = isset($entry['priority']) ? (int) $entry['priority'] : 0;
            $eligibility = in_array(($entry['eligibility'] ?? 'Primary'), ['Primary', 'Alternate'], true) ? (string) $entry['eligibility'] : 'Primary';
            $subDutyId = isset($entry['subduty_id']) && $entry['subduty_id'] !== '' ? (int) $entry['subduty_id'] : null;

            if ($rankId <= 0 || $priority <= 0) {
                continue;
            }

            $recordId = (int) ($entry['id'] ?? 0);
            if ($recordId > 0) {
                Eligibility::update($recordId, [
                    'rank_id' => $rankId,
                    'duty_id' => $dutyId,
                    'subduty_id' => $subDutyId,
                    'priority' => $priority,
                    'eligibility' => $eligibility,
                    'is_active' => 1,
                ]);
            } else {
                Eligibility::create([
                    'rank_id' => $rankId,
                    'duty_id' => $dutyId,
                    'subduty_id' => $subDutyId,
                    'priority' => $priority,
                    'eligibility' => $eligibility,
                    'is_active' => 1,
                ]);
            }

            $saved++;
        }

        flash('success', $saved > 0 ? 'Eligibility saved.' : 'No eligibility rows were updated.');
        redirect(APP_PUBLIC_URL . '/?route=eligibility/index&duty_id=' . $dutyId);
    }
}
