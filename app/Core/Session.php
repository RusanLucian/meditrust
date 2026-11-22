<?php

namespace App\Core;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
            self::$started = true;
        } elseif (session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
        }

        if (!isset($_SESSION['_flash_next'])) {
            $_SESSION['_flash_next'] = [];
        }
        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, ?string $message = null): mixed
    {
        self::start();
        if ($message !== null) {
            $_SESSION['_flash_next'][$key] = $message;
            return null;
        }

        if (isset($_SESSION['_flash'][$key])) {
            $val = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $val;
        }

        if (isset($_SESSION['_flash_next'][$key])) {
            $val = $_SESSION['_flash_next'][$key];
            unset($_SESSION['_flash_next'][$key]);
            return $val;
        }

        return null;
    }

    public static function ageFlash(): void
    {
        self::start();
        $_SESSION['_flash'] = $_SESSION['_flash_next'] ?? [];
        $_SESSION['_flash_next'] = [];
    }

    public static function csrfToken(): string
    {
        self::start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf(?string $token): bool
    {
        self::start();
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function login(array $user): void
    {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'] ?? '';
        $_SESSION['user_email'] = $user['email'] ?? '';
        $_SESSION['user_type'] = $user['user_type'] ?? 'patient';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public static function logout(): void
    {
        self::start();
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_type']);
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }
    }

    public static function user(): ?array
    {
        self::start();
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'user_type' => $_SESSION['user_type'] ?? '',
        ];
    }

    public static function isAdmin(): bool
    {
        return (self::get('user_type') === 'admin');
    }

    public static function isDoctor(): bool
    {
        return (self::get('user_type') === 'doctor');
    }

    public static function isPatient(): bool
    {
        return (self::get('user_type') === 'patient');
    }
}
