<?php

namespace App\Services;

use App\Models\AuditLog;
use Throwable;

class AuditTrail
{
    public static function record(string $event, ?array $payload = null): void
    {
        try {
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
            $user = $_SESSION['user'] ?? [];

            (new AuditLog())->create([
                'user_id' => $user['id'] ?? null,
                'user_name' => $user['name'] ?? null,
                'user_role' => $user['role'] ?? null,
                'event' => $event,
                'module' => self::moduleFromPath($path),
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
                'path' => $path,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                'request_data' => json_encode($payload ?? self::sanitizedPost(), JSON_UNESCAPED_UNICODE),
            ]);
        } catch (Throwable) {
            // Audit must not break the main workflow.
        }
    }

    public static function sanitizedPost(): array
    {
        $data = $_POST;

        foreach (['password', 'password_confirmation', 'current_password'] as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = '[hidden]';
            }
        }

        return $data;
    }

    private static function moduleFromPath(string $path): string
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        return $segments[0] ?? 'dashboard';
    }
}
