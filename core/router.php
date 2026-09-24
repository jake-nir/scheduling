<?php
declare(strict_types=1);

function route_map(): array
{
    return [
        'auth/login' => ['AuthController', 'login'],
        'auth/logout' => ['AuthController', 'logout'],
        'dashboard/index' => ['DashboardController', 'index'],
        'users/index' => ['UserController', 'index'],
        'users/create' => ['UserController', 'create'],
        'users/edit' => ['UserController', 'edit'],
        'users/save' => ['UserController', 'save'],
        'users/deactivate' => ['UserController', 'deactivate'],
        'users/reset-password' => ['UserController', 'resetPassword'],
        'roles/index' => ['UserController', 'roles'],
        'roles/create' => ['UserController', 'roleCreate'],
        'roles/edit' => ['UserController', 'roleEdit'],
        'roles/save' => ['UserController', 'saveRole'],
        'duties/index' => ['DutyController', 'index'],
        'duties/create' => ['DutyController', 'create'],
        'duties/edit' => ['DutyController', 'edit'],
        'duties/save' => ['DutyController', 'save'],
        'duties/delete' => ['DutyController', 'delete'],
        'subduties/index' => ['SubDutyController', 'index'],
        'subduties/save' => ['SubDutyController', 'save'],
        'subduties/delete' => ['SubDutyController', 'delete'],
        'reliefs/index' => ['ReliefController', 'index'],
        'reliefs/save' => ['ReliefController', 'save'],
        'reliefs/delete' => ['ReliefController', 'delete'],
        'eligibility/index' => ['EligibilityController', 'index'],
        'eligibility/save' => ['EligibilityController', 'save'],
        'rotation-groups/index' => ['RotationGroupController', 'index'],
        'rotation-groups/save' => ['RotationGroupController', 'save'],
        'schedule/index' => ['ScheduleController', 'index'],
        'schedule/create' => ['ScheduleController', 'create'],
        'schedule/store' => ['ScheduleController', 'store'],
        'schedule/cancel' => ['ScheduleController', 'cancel'],
        'schedule/conflicts' => ['ScheduleController', 'conflicts'],
    ];
}

function resolve_route(string $route): ?array
{
    $normalized = trim($route, "/ ");
    if ($normalized === '') {
        $normalized = 'auth/login';
    }

    return route_map()[$normalized] ?? null;
}
