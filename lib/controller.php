<?php
foreach (glob('./autoload/lib/*.php') as $file) {
    include($file);
}

require_once './lib/utils.php';
require_once './lib/web.php';
require_once __DIR__ . './../app/controller.php';

$current_dir = str_replace('\\', '/', getcwd());
$url = explode('/', $_SERVER['REQUEST_URI']);
$root = $_SERVER['DOCUMENT_ROOT'];
$allow_methods = [
    'GET',
    'POST',
    'PUT',
    'PATCH',
    'DELETE'
];

if (isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
    if (!in_array($method, $allow_methods)) {
        $method = $_SERVER['REQUEST_METHOD'];
    }
} else {
    $method = $_SERVER['REQUEST_METHOD'];
}
$method = strtolower($method);
$except_url = explode('/', str_replace($root, '', $current_dir));
$is_cli_server = php_sapi_name() == 'cli-server';

clear_empty($url);
clear_empty($except_url);
clear_empty($url);
$url = implode('/', $url);
$url = explode('?', $url)[0];
$url = explode('/', $url);
clear_empty($url);
$url_count = count($url);
$url = implode('/', $url);

$contains_page = false;
foreach (Route::$routes[$method] ?? [] as $route) {
    if ($url_count == $route->len && preg_match($route->pattern, $url, $matches)) {
        $params = [];
        if (count($route->params)) {
            $params = array_combine($route->params, array_slice($matches, -count($route->params)));
        }

        $values = [];
        if (count($route->params)) {
            $values = array_map(function ($value) {
                return '"' . $value . '"';
            }, array_slice($matches, -count($route->params)));
        }
        $value = implode(',', $values);

        try {
            $functionText = "{$route->function}({$value});";
            eval($functionText);
        } catch (\TypeError $th) {
            if ($value !== "") $value = ', ' . $value;
            $functionText = "{$route->function}(\$GLOBALS['request'] {$value});";
            eval($functionText);
        }

        $contains_page = true;
        break;
    }
}

if (!$contains_page) {
    if (file_exists($url) && filetype($url) == 'file') {
        echo file_get_contents($url);
    } else {
        die('Page not found.');
    }
}
