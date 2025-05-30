<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Usuario;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class UsuarioController
{
    private Usuario $usuario;
    private Environment $twig;

    public function __construct()
    {
        $this->usuario = new Usuario();
        $loader = new FilesystemLoader(__DIR__ . '/../../../templates');
        $this->twig = new Environment($loader);
    }

    public function listar(): void
    {
        $usuarios = $this->usuario->buscarTodos();
        echo $this->twig->render(
            'admin/usuarios/listar.twig', [
            'usuarios' => $usuarios,
            'session'  => $_SESSION
            ]
        );
        unset($_SESSION['alerta']);
    }

    public function novo(): void
    {
        echo $this->twig->render('admin/usuarios/novo.twig');
    }

    public function salvar(): void
    {
        $dados = [
            'nome'  => $_POST['nome'] ?? '',
            'email' => $_POST['email'] ?? '',
            'senha' => password_hash($_POST['senha'] ?? '', PASSWORD_DEFAULT),
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'foto'  => ''
        ];

        if (!empty($_FILES['foto']['name'])) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $nomeArquivo = uniqid('foto_', true) . '.' . $ext;
            $pasta = $_SERVER['DOCUMENT_ROOT'] . '/laressencia/public/uploads/usuarios/';

            if (!is_dir($pasta)) {
                mkdir($pasta, 0777, true);
            }

            $caminho = $pasta . $nomeArquivo;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho)) {
                $dados['foto'] = 'uploads/usuarios/' . $nomeArquivo;
            }
        }

        $this->usuario->salvar($dados);

        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => 'Usuário cadastrado com sucesso!'
        ];

        header('Location: index.php?url=usuarios/listar');
        exit;
    }

    public function editar(): void
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "ID não informado.";
            return;
        }

        $usuario = $this->usuario->buscarPorId($id);
        if (!$usuario) {
            echo "Usuário não encontrado.";
            return;
        }

        echo $this->twig->render(
            'admin/usuarios/editar.twig', [
            'usuario' => $usuario,
            'session' => $_SESSION
            ]
        );
        unset($_SESSION['alerta']);
    }

    public function atualizar(): void
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "ID não informado.";
            return;
        }

        $dados = [
            'nome'  => $_POST['nome'] ?? '',
            'email' => $_POST['email'] ?? '',
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'foto'  => $_POST['foto_atual'] ?? ''
        ];

        if (!empty($_POST['senha'])) {
            $dados['senha'] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        }

        if (!empty($_FILES['foto']['name'])) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $nomeArquivo = uniqid('foto_', true) . '.' . $ext;
            $pasta = $_SERVER['DOCUMENT_ROOT'] . '/laressencia/public/uploads/usuarios/';

            if (!is_dir($pasta)) {
                mkdir($pasta, 0777, true);
            }

            $caminho = $pasta . $nomeArquivo;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho)) {
                $dados['foto'] = 'uploads/usuarios/' . $nomeArquivo;
            }
        }

        $this->usuario->atualizar($id, $dados);

        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => 'Usuário atualizado com sucesso!'
        ];

        header('Location: index.php?url=usuarios/listar');
        exit;
    }

    public function excluir(): void
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "ID não informado.";
            return;
        }

        $this->usuario->excluir($id);

        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => 'Usuário excluído com sucesso!'
        ];

        header('Location: index.php?url=usuarios/listar');
        exit;
    }
}
