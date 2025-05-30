<?php

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Quando o domínio aponta diretamente para /public
    return "{$scheme}://{$host}/" . ltrim($path, '/');
    }

}

if (!function_exists('asset')) {
    function asset(string $path = ''): string
    {
        return base_url($path);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return base_url($path);
    }
}