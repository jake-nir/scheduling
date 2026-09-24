<?php
declare(strict_types=1);

class PersonnelController
{
    public function index(): void
    {
        require_permission('personnel.view');

        $pageTitle = 'Personnel';
        $personnel = Personnel::all();
        $viewFile = APP_ROOT . '/views/personnel/list.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function create(): void
    {
        require_permission('personnel.manage');

        $pageTitle = 'Create Personnel';
        $ranks = Rank::active();
        $viewFile = APP_ROOT . '/views/personnel/form.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function edit(): void
    {
        require_permission('personnel.manage');

        $id = (int) ($_GET['id'] ?? 0);
        $person = Personnel::findById($id);
        if (!$person) {
            flash('error', 'Personnel record not found.');
            redirect(APP_PUBLIC_URL . '/?route=personnel/index');
        }

        $pageTitle = 'Edit Personnel';
        $ranks = Rank::active();
        $viewFile = APP_ROOT . '/views/personnel/form.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }

    public function save(): void
    {
        require_permission('personnel.manage');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_PUBLIC_URL . '/?route=personnel/index');
        }

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            flash('error', 'Security token expired. Please try again.');
            redirect(APP_PUBLIC_URL . '/?route=personnel/index');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $serviceNumber = trim((string) ($_POST['service_number'] ?? ''));
        $firstName = trim((string) ($_POST['first_name'] ?? ''));
        $middleName = trim((string) ($_POST['middle_name'] ?? ''));
        $lastName = trim((string) ($_POST['last_name'] ?? ''));
        $suffix = trim((string) ($_POST['suffix'] ?? ''));
        $designation = trim((string) ($_POST['designation'] ?? ''));
        $unit = trim((string) ($_POST['unit'] ?? ''));
        $contact = trim((string) ($_POST['contact_number'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $status = in_array($_POST['status'] ?? 'active', ['active', 'inactive'], true) ? $_POST['status'] : 'active';
        $rankId = (int) ($_POST['rank_id'] ?? 0);

        if (!preg_match('/^[A-Za-z0-9-]+$/', $serviceNumber) || $serviceNumber === '' || $firstName === '' || $lastName === '' || $rankId <= 0) {
            flash('error', 'Please provide a valid service number, full name, and rank.');
            redirect(APP_PUBLIC_URL . '/?route=personnel/create');
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
            redirect(APP_PUBLIC_URL . '/?route=personnel/create');
        }

        if (($existing = Personnel::findByServiceNumber($serviceNumber)) && ($existing['id'] ?? 0) !== $id) {
            flash('error', 'That service number already exists.');
            redirect(APP_PUBLIC_URL . '/?route=personnel/create');
        }

        $payload = [
            'service_number' => $serviceNumber,
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'suffix' => $suffix,
            'designation' => $designation,
            'unit' => $unit,
            'contact_number' => $contact,
            'email' => $email,
            'status' => $status,
            'rank_id' => $rankId,
        ];

        if ($id > 0) {
            Personnel::update($id, $payload);
            flash('success', 'Personnel updated successfully.');
        } else {
            Personnel::create($payload);
            flash('success', 'Personnel created successfully.');
        }

        AuditLog::log(
            $id > 0 ? 'personnel.update' : 'personnel.create',
            'personnel',
            $id > 0 ? 'Personnel updated.' : 'Personnel created.',
            current_user()['id'] ?? null,
            current_user()['username'] ?? null,
            'personnel',
            $id > 0 ? $id : null,
            $_SERVER['REMOTE_ADDR'] ?? null
        );

        redirect(APP_PUBLIC_URL . '/?route=personnel/index');
    }

    public function deactivate(): void
    {
        require_permission('personnel.manage');

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            redirect(APP_PUBLIC_URL . '/?route=personnel/index');
        }

        Personnel::setStatus($id, 'inactive');
        AuditLog::log('personnel.deactivate', 'personnel', 'Personnel deactivated.', current_user()['id'] ?? null, current_user()['username'] ?? null, 'personnel', $id, $_SERVER['REMOTE_ADDR'] ?? null);
        flash('success', 'Personnel deactivated.');
        redirect(APP_PUBLIC_URL . '/?route=personnel/index');
    }

    public function show(): void
    {
        require_permission('personnel.view');

        $id = (int) ($_GET['id'] ?? 0);
        $person = Personnel::findById($id);
        if (!$person) {
            flash('error', 'Personnel not found.');
            redirect(APP_PUBLIC_URL . '/?route=personnel/index');
        }

        $pageTitle = 'Personnel Profile';
        $availabilities = Availability::activeForPerson($id);
        $viewFile = APP_ROOT . '/views/personnel/show.php';

        include APP_ROOT . '/views/layouts/layout.php';
    }
}
