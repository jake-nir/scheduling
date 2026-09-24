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
        'personnel/index' => ['PersonnelController', 'index'],
        'personnel/create' => ['PersonnelController', 'create'],
        'personnel/edit' => ['PersonnelController', 'edit'],
        'personnel/save' => ['PersonnelController', 'save'],
        'personnel/deactivate' => ['PersonnelController', 'deactivate'],
        'personnel/show' => ['PersonnelController', 'show'],
        'availability/index' => ['AvailabilityController', 'index'],
        'availability/create' => ['AvailabilityController', 'create'],
        'availability/edit' => ['AvailabilityController', 'edit'],
        'availability/save' => ['AvailabilityController', 'save'],
        'availability/update' => ['AvailabilityController', 'update'],
        'availability/delete' => ['AvailabilityController', 'delete'],
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
        'calendar/index' => ['CalendarController', 'index'],
        'calendar/daily' => ['CalendarController', 'daily'],
        'calendar/weekly' => ['CalendarController', 'weekly'],
        'calendar/monthly' => ['CalendarController', 'monthly'],
        'calendar/rotation' => ['CalendarController', 'rotation'],
        'rotation/index' => ['RotationController', 'index'],
        'dutyhistory/index' => ['DutyHistoryController', 'index'],
        'dutyhistory/person' => ['DutyHistoryController', 'person'],
        'reports/index' => ['ReportController', 'index'],
        'reports/roster' => ['ReportController', 'roster'],
        'reports/duty-history' => ['ReportController', 'dutyHistory'],
        'reports/pow-rotation' => ['ReportController', 'powRotation'],
        'reports/sentinel-rotation' => ['ReportController', 'sentinelRotation'],
        'reports/availability' => ['ReportController', 'availability'],
        'reports/personnel-status' => ['ReportController', 'personnelStatus'],
        'reports/pod' => ['ReportController', 'pod'],
        'pod/index' => ['PodController', 'index'],
        'pod/create' => ['PodController', 'create'],
        'pod/view' => ['PodController', 'view'],
        'pod/preview' => ['PodController', 'preview'],
        'pod/print' => ['PodController', 'print'],
        'pod/finalize' => ['PodController', 'finalize'],
        'pod/revise' => ['PodController', 'revise'],
        'pod/cancel' => ['PodController', 'cancel'],
        'pod/revisions' => ['PodController', 'revisions'],
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
