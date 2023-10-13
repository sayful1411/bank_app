<?php

class Router
{
    private static array $list = [];

    public static function get(string $page, array $controllerMethod): void // GET route
    {
        static::$list[] = [
            'page' => $page,
            'method' => 'GET',
            'controller' => $controllerMethod[0], // Controller class name
            'controllerMethod' => $controllerMethod[1], // Controller method name
        ];
    }

    public static function post(string $page, array $controllerMethod): void // POST route
    {
        static::$list[] = [
            'page' => $page,
            'method' => 'POST',
            'controller' => $controllerMethod[0], // Controller class name
            'controllerMethod' => $controllerMethod[1], // Controller method name
        ];
    }

    public static function run()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $page = trim($_SERVER['REQUEST_URI'], '/');
        $page = explode("?",$page)[0];
// echo $page; die();
        foreach (self::$list as $item) {
            if ($item['page'] === $page && $item['method'] === $method) {
                $controllerName = $item['controller'];
                $controllerMethod = $item['controllerMethod'];

                if ($controllerName && $controllerMethod) {
                    $controllerInstance =$controllerName;
                    $controllerInstance::$controllerMethod();
                    return;
                }

                die('Controller or method not found');
            }
        }

        die('Route not found');
    }
}

