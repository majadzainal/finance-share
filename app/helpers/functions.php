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
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Auto-detect base URL dynamically if HTTP_HOST is present, or fallback to config
        $baseUrl = rtrim((string) config('app.base_url', ''), '/');
        if (isset($_SERVER['HTTP_HOST'])) {
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];

            $configPath = parse_url($baseUrl, PHP_URL_PATH) ?: '';
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

            $subPath = '';
            if ($scriptDir !== '/' && $scriptDir !== '.' && $scriptDir !== '') {
                $subPath = rtrim($scriptDir, '/');
                if (str_ends_with($subPath, '/public') && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/public')) {
                    $subPath = substr($subPath, 0, -7);
                }
            } elseif ($configPath !== '' && $configPath !== '/') {
                $subPath = rtrim($configPath, '/');
            }

            $baseUrl = $scheme . '://' . $host . $subPath;
        }

        $basePath = parse_url($baseUrl, PHP_URL_PATH);
        if ($basePath && $basePath !== '/' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (! function_exists('request_path')) {
    function request_path(?string $uri = null): string
    {
        $uri = $uri ?? $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $baseUrl = (string) config('app.base_url', '');
        $basePath = parse_url($baseUrl, PHP_URL_PATH);

        if ($basePath && $basePath !== '/' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        // Also check SCRIPT_NAME directory prefix if present
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptDir !== '/' && $scriptDir !== '.' && $scriptDir !== '') {
            $subPath = rtrim($scriptDir, '/');
            if (str_ends_with($subPath, '/public') && !str_contains($path, '/public')) {
                $subPath = substr($subPath, 0, -7);
            }
            if ($subPath !== '' && $subPath !== '/' && str_starts_with($path, $subPath)) {
                $path = substr($path, strlen($subPath));
            }
        }

        $path = '/' . ltrim($path, '/');
        return $path === '' ? '/' : $path;
    }
}

if (! function_exists('parse_money')) {
    function parse_money(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        $str = trim((string) $value);
        $str = preg_replace('/[^0-9.,-]/', '', $str);
        if ($str === '' || $str === '-') {
            return 0.0;
        }

        if (str_contains($str, '.') && str_contains($str, ',')) {
            if (strrpos($str, ',') > strrpos($str, '.')) {
                // Indonesian format: 1.000.000,50
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                // US format: 1,000,000.50
                $str = str_replace(',', '', $str);
            }
        } elseif (str_contains($str, '.')) {
            // If multiple dots (1.500.000) or ends with .000 (500.000)
            if (substr_count($str, '.') > 1 || preg_match('/\.\d{3}$/', $str)) {
                $str = str_replace('.', '', $str);
            }
        } elseif (str_contains($str, ',')) {
            // If multiple commas (1,500,000) or ends with ,000 (500,000)
            if (substr_count($str, ',') > 1 || preg_match('/,\d{3}$/', $str)) {
                $str = str_replace(',', '', $str);
            } else {
                $str = str_replace(',', '.', $str);
            }
        }

        return (float) $str;
    }
}
