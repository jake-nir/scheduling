<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/init.php';

if (!isset($_GET['route'])) {
    redirect(APP_PUBLIC_URL . '/?route=' . ($_APP_CONFIG['default_route'] ?? 'auth/login'));
}

$route = trim((string) ($_GET['route'] ?? ''), " /\t\n\r");
if ($route === '') {
    $route = $_APP_CONFIG['default_route'] ?? 'auth/login';
}

$GLOBALS['__debug_request'] = [
    'route' => $route,
    'controller' => null,
    'method' => null,
    'controller_file' => null,
    'controller_exists' => null,
    'method_exists' => null,
];

$routeResolver = resolve_route($route);

if ($routeResolver === null) {
    error_log(sprintf('[duty-scheduling] 404: route "%s" is not registered in core/router.php', $route));
    http_response_code(404);
    include APP_ROOT . '/views/errors/404.php';
    exit;
}

[$controllerName, $actionName] = $routeResolver;

$controllerFile = APP_ROOT . '/controllers/' . $controllerName . '.php';
$GLOBALS['__debug_request']['controller'] = $controllerName;
$GLOBALS['__debug_request']['method'] = $actionName;
$GLOBALS['__debug_request']['controller_file'] = $controllerFile;
$GLOBALS['__debug_request']['controller_exists'] = is_file($controllerFile);

if (app_env() === 'local') {
    error_log(sprintf(
        '[duty-scheduling] route=%s | controller=%s | method=%s | file=%s | exists=%s',
        $route,
        $controllerName,
        $actionName,
        $controllerFile,
        $GLOBALS['__debug_request']['controller_exists'] ? 'YES' : 'NO'
    ));
}

if ($route !== 'auth/login' && $route !== 'auth/logout' && !is_logged_in()) {
    redirect(APP_PUBLIC_URL . '/?route=auth/login');
}

if (in_array($route, ['auth/login', 'auth/logout'], true)) {
    dispatch_controller($controllerName, $actionName);
    exit;
}

require_permission('dashboard.view');

dispatch_controller($controllerName, $actionName);
