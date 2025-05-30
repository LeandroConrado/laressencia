<?php

namespace App\Core\Middleware;

class ModuleMiddleware
{
    public static function handle(string $requiredModule): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $modulos = $_SESSION['usuario_modulos'] ?? [];

        if (!in_array($requiredModule, $modulos)) {
            http_response_code(403);
            echo "Acesso negado. Você não tem permissão para acessar o módulo '{$requiredModule}'.";
            exit;
        }
    }
}
