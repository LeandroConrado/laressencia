<?php

namespace App\Core;

class Auth
{
    /**
     * Verifica se o usuário está logado.
     * Se não estiver, redireciona para a tela de login.
     */
    public static function verificaLogin(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . base_url() . '/admin/login');
            exit;
        }
    }

    /**
     * Retorna os dados do usuário logado.
     * Se não estiver logado, retorna null.
     */
    public static function usuario(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!empty($_SESSION['usuario_id'])) {
            return [
                'id'    => $_SESSION['usuario_id'],
                'nome'  => $_SESSION['usuario_nome'],
                'email' => $_SESSION['usuario_email'],
                'foto'  => $_SESSION['usuario_foto'] ?? null,
            ];
        }

        return null;
    }

    /**
     * Realiza logout e destrói a sessão.
     */
    public static function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_unset();
        session_destroy();

        header('Location: ' . base_url() . '/admin/login');
        exit;
    }
}
