<?php
declare(strict_types=1);

$menuItems = [
    ['label' => 'Dashboard', 'route' => 'dashboard/index', 'permission' => 'dashboard.view'],
    ['label' => 'Calendar', 'route' => 'calendar/index', 'permission' => 'calendar.view'],
    ['label' => 'Personnel', 'route' => 'personnel/index', 'permission' => 'personnel.view'],
    ['label' => 'Availability', 'route' => 'availability/index', 'permission' => 'availability.view'],
    ['label' => 'Duty History', 'route' => 'dutyhistory/index', 'permission' => 'dutyhistory.view'],
    ['label' => 'Scheduling', 'route' => 'schedule/index', 'permission' => 'schedule.manage'],
    ['label' => 'Rotation', 'route' => 'rotation/index', 'permission' => 'rotation.view'],
    ['label' => 'POD', 'route' => 'pod/index', 'permission' => 'pod.manage'],
    ['label' => 'Reports', 'route' => 'reports/index', 'permission' => 'reports.view'],
    ['label' => 'Duty Config', 'route' => 'duties/index', 'permission' => 'dutyconfig.manage'],
    ['label' => 'Users', 'route' => 'users/index', 'permission' => 'users.manage'],
    ['label' => 'Roles', 'route' => 'roles/index', 'permission' => 'users.manage'],
];
?>
<aside class="sidebar no-print">
    <div class="brand-wrap">
        <div class="brand-mark">D</div>
        <div>
            <div class="brand-name">Duty Scheduling</div>
            <div class="brand-subtitle">Admin</div>
        </div>
    </div>

    <nav class="nav-menu">
        <?php foreach ($menuItems as $item): ?>
            <?php if (user_has_permission($item['permission'])): ?>
                <a class="nav-item <?= (($_GET['route'] ?? '') === $item['route']) ? 'active' : ''; ?>" href="<?= e(APP_PUBLIC_URL . '/?route=' . $item['route']); ?>"><?= e($item['label']); ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
</aside>
