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

$routeResolver = resolve_route($route);
if ($routeResolver === null) {
    http_response_code(404);
    include APP_ROOT . '/views/errors/404.php';
    exit;
}

[$controllerName, $actionName] = $routeResolver;

if ($route !== 'auth/login' && $route !== 'auth/logout' && !is_logged_in()) {
    redirect(APP_PUBLIC_URL . '/?route=auth/login');
}

if (in_array($route, ['auth/login', 'auth/logout'], true)) {
    require_once APP_ROOT . '/controllers/' . $controllerName . '.php';
    $controller = new $controllerName();
    $controller->{$actionName}();
    exit;
}

require_permission('dashboard.view');

require_once APP_ROOT . '/controllers/' . $controllerName . '.php';
$controller = new $controllerName();
if (!method_exists($controller, $actionName)) {
    http_response_code(404);
    include APP_ROOT . '/views/errors/404.php';
    exit;
}

$controller->{$actionName}();
