<?php

use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;

if (!function_exists('route')) {
    function route(string $name, array $params = []): string
    {
        return Router::url($name, $params);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : 'http://localhost/meditrust';
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url($path);
    }
}

if (!function_exists('view')) {
    function view(string $view, array $data = [], ?string $layout = 'layouts/main'): Response
    {
        return View::render($view, $data, $layout);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $to): Response
    {
        return Response::redirect($to);
    }
}

if (!function_exists('back')) {
    function back(): Response
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? route('home');
        return Response::redirect($referer);
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return Session::user();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Session::csrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        $old = Session::get('old', []);
        return $old[$key] ?? $default;
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null): mixed
    {
        return Session::flash($key, $message);
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('displayStars')) {
    function displayStars(float|int $rating): string
    {
        $full = (int)floor($rating);
        $half = ($rating - $full) >= 0.5 ? 1 : 0;
        $empty = 5 - $full - $half;
        return str_repeat('⭐', $full) . ($half ? '⭐' : '') . str_repeat('☆', $empty);
    }
}
