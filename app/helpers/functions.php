<?php

if (! function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return dirname(__DIR__, 2) . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

if (! function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        return base_path('app' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (! function_exists('view_path')) {
    function view_path(string $path = ''): string
    {
        return app_path('views' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (! function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $configs = [];

        [$file, $item] = array_pad(explode('.', $key, 2), 2, null);

        if (! isset($configs[$file])) {
            $configFile = app_path("config/{$file}.php");
            $configs[$file] = is_file($configFile) ? require $configFile : [];
        }

        return $item ? ($configs[$file][$item] ?? $default) : $configs[$file];
    }
}

if (! function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('url')) {
    function url(string $path = ''): string
    {
        return rtrim((string) config('app.base_url'), '/') . '/' . ltrim($path, '/');
    }
}
