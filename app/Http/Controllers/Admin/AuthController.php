<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Usuario;
use Twig\Environment;

class AuthController
{
    private Environment $twig;
    private Usuario $usuarioModel;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $this->usuarioModel = new Usuario();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function login(): void
    {
        echo $this->twig->render('admin/login.twig', [
            'erro' => $_SESSION['erro_login'] ?? null
        ]);

        unset($_SESSION['erro_login']);
    }

    public function autenticar(): void
    {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $usuario = $this->usuarioModel->buscarPorEmail($email);

    if (!$usuario || !password_verify($senha, $usuario['senha'])) {
        $_SESSION['erro_login'] = 'E-mail ou senha inválidos.';
        header('Location: ' . base_url() . '/admin/login');
        exit;
    }

    // Dados básicos
    $_SESSION['usuario_id']    = $usuario['id'];
    $_SESSION['usuario_nome']  = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_foto']  = $usuario['foto'];

    // Tipo de usuário
    $_SESSION['usuario_tipo'] = $usuario['tipo'] ?? 'admin'; // exemplo: admin, editor, superadmin

    // Módulos permitidos (poderia vir do banco)
    $_SESSION['usuario_modulos'] = explode(',', $usuario['modulos'] ?? 'produtos,clientes,pedidos');

    header("Location: " . base_url() . "/admin/dashboard");
    exit;
    }


    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_unset();
        session_destroy();

        header("Location: " . base_url() . "/admin/login");
        exit;
    }
}
