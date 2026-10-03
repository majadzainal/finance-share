<?php

namespace App\Middleware;

use App\Services\AuditTrail;

class AuthMiddleware
{
    public function handle(string $uri): void
    {
        $path = request_path($uri);
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

        if (! $this->canAccessPath($path, $_SERVER['REQUEST_METHOD'] ?? 'GET')) {
            http_response_code(403);
            exit('403 - Access denied');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (! $this->isControllerAuditedPath($path)) {
                AuditTrail::record($this->eventFromPath($path));
            }
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

        if (in_array($last, ['activate', 'deactivate', 'delete', 'restore', 'paid'], true)) {
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

    private function canAccessPath(string $path, string $method): bool
    {
        if ($method !== 'POST') {
            return true;
        }

        $role = $_SESSION['user']['role'] ?? '';

        if (preg_match('#^/.+/\d+/delete$#', $path) === 1 || preg_match('#^/income/\d+/restore$#', $path) === 1) {
            return in_array($role, ['admin', 'finance'], true);
        }

        return true;
    }

    private function isControllerAuditedPath(string $path): bool
    {
        return preg_match('#^/income/\d+/(delete|restore)$#', $path) === 1;
    }
}
