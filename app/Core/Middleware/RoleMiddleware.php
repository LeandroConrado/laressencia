<?php

namespace App\Core\Middleware;

class RoleMiddleware
{
    public static function handle(string $requiredRole): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userRole = $_SESSION['usuario_tipo'] ?? null;

        if ($userRole !== $requiredRole) {
            http_response_code(403);
            echo "Acesso negado. Permissão insuficiente para acessar esta área.";
            exit;
        }
    }
}
