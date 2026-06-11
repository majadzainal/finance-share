<?php

namespace App\Middleware;

use App\Services\AuditTrail;

class AuthMiddleware
{
    public function handle(string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $publicPaths = ['/login'];

        if (in_array($path, $publicPaths, true)) {
            return;
        }

        if (! isset($_SESSION['user'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        if ($this->isAdminPath($path) && ($_SESSION['user']['role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('403 - Access denied');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            AuditTrail::record($this->eventFromPath($path));
        }
    }

    private function isAdminPath(string $path): bool
    {
        foreach (['/users', '/roles', '/audit-logs'] as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }

    private function eventFromPath(string $path): string
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));
        $last = end($segments) ?: '';

        if (in_array($last, ['activate', 'deactivate', 'delete', 'paid'], true)) {
            return $last;
        }

        if (($segments[0] ?? '') === 'closing' && $last === 'finalize') {
            return 'finalize';
        }

        if (count($segments) >= 2 && is_numeric($segments[1])) {
            return 'update';
        }

        return 'store';
    }
}
