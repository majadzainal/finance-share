<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $uri, array|callable $action): void
    {
        $this->add('GET', $uri, $action);
    }

    public function post(string $uri, array|callable $action): void
    {
        $this->add('POST', $uri, $action);
    }

    public function add(string $method, string $uri, array|callable $action): void
    {
        $this->routes[strtoupper($method)][] = [
            'uri' => $this->normalizeUri($uri),
            'action' => $action,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $uri = $this->normalizeUri(parse_url($uri, PHP_URL_PATH) ?: '/');
        $route = $this->match($method, $uri);

        if (! $route) {
            http_response_code(404);
            echo '404 - Page not found';
            return;
        }

        $action = $route['action'];
        $params = $route['params'];

        if (is_callable($action)) {
            echo $action(...$params);
            return;
        }

        [$controller, $methodName] = $action;
        $controller = new $controller();
        echo $controller->{$methodName}(...$params);
    }

    private function normalizeUri(string $uri): string
    {
        $uri = '/' . trim($uri, '/');
        return $uri === '/' ? '/' : rtrim($uri, '/');
    }

    private function match(string $method, string $uri): ?array
    {
        foreach ($this->routes[$method] ?? [] as $route) {
            $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([^/]+)', $route['uri']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                return [
                    'action' => $route['action'],
                    'params' => $matches,
                ];
            }
        }

        return null;
    }
}
