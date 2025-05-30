<?php

namespace App\Core\Middleware;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Verificação mais segura
        if (!isset($_SESSION['usuario_id']) || !is_numeric($_SESSION['usuario_id'])) {
            header("Location: " . base_url() . "/admin/login");
            exit;
        }
    }
}
