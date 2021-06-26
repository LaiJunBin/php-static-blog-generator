<?php

class Route
{
    static $routes = [];
    static $route_table = [];

    private static $prefix = '';

    public function __construct($params = [])
    {
        foreach ($params as $key => $value) {
            $this->$key = $value;
        }
    }

    static function get($url, $action)
    {
        return self::process("get", $url, $action);
    }

    static function post($url, $action)
    {
        return self::process("post", $url, $action);
    }

    static function put($url, $action)
    {
        return self::process("put", $url, $action);
    }

    static function patch($url, $action)
    {
        return self::process("patch", $url, $action);
    }

    static function delete($url, $action)
    {
        return self::process("delete", $url, $action);
    }

    static function process($method, $url, $action)
    {
        if (!array_key_exists($method, self::$routes)) {
            self::$routes[$method] = [];
            self::$route_table[$method] = [];
        }
        $function = $action;

        $url = self::$prefix . '/' . $url;
        $url = explode('/', $url);
        clear_empty($url);
        $url = implode('/', $url);

        array_push(self::$route_table[$method], [
            'method' => $method,
            'url' => '/' . $url,
            'action' => $action,
            'name' => ''
        ]);

        preg_match_all("/{(.[^}]*)}/", $url, $params);
        $pattern = preg_replace("/{.[^}]*}/", "(.*)", $url);
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = str_replace('?', '\?', $pattern);
        $pattern_uri = $pattern;
        $pattern = "/(?={$pattern}\?)^{$pattern}\?.*|^{$pattern}$/";

        $url = explode('/', $url);
        clear_empty($url);
        $url_count = count($url);

        $route = new Route([
            'method' => $method,
            'function' => $function,
            'pattern' => $pattern,
            'pattern_uri' => $pattern_uri,
            'len' => $url_count,
            'params' => $params[1]
        ]);
        array_push(self::$routes[$method], $route);
        return $route;
    }

    static function hasUri($url, $method = 'get')
    {
        $isCorrect = false;
        $url = explode('?', $url)[0];
        $url = explode('/', $url);
        clear_empty($url);
        $url_count = count($url);
        $url = implode('/', $url);
        foreach (static::$routes[$method] ?? [] as $route) {
            if ($url_count == $route->len && preg_match($route->pattern, $url)) {
                $isCorrect = true;
                break;
            }
        }
        return $isCorrect;
    }
}
