<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Services\AuditTrail;

class AuthController extends Controller
{
    public function showLogin(): string
    {
        if (isset($_SESSION['user'])) {
            $this->redirect('/dashboard');
        }

        return $this->view('auth.login', [
            'title' => 'Login',
            'error' => $_GET['error'] ?? null,
        ]);
    }

    public function login(): string
    {
        $username = trim($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $user = (new User())->findActiveByUsername($username);

        if (! $user || ! password_verify($password, $user['password'])) {
            AuditTrail::record('login_failed', ['username' => $username]);

            return $this->view('auth.login', [
                'title' => 'Login',
                'error' => 'Username atau password salah.',
            ]);
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];

        AuditTrail::record('login_success', ['username' => $username]);
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        AuditTrail::record('logout', []);
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        $this->redirect('/login');
    }

    private function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}
