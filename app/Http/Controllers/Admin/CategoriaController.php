<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Categoria;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class CategoriaController
{
    private Categoria $categoria;
    private Environment $twig;

    public function __construct()
    {
        $this->categoria = new Categoria();
        $loader = new FilesystemLoader(__DIR__ . '/../../../templates');
        $this->twig = new Environment($loader);
    }

    public function listar(): void
    {
        $categorias = $this->categoria->buscarTodas();
        echo $this->twig->render(
            'admin/categorias/listar.twig', [
            'categorias' => $categorias,
            'session' => $_SESSION
            ]
        );
        unset($_SESSION['alerta']);
    }

    public function novo(): void
    {
        echo $this->twig->render('admin/categorias/novo.twig');
    }

    public function salvar(): void
    {
        $dados = [
            'nome' => $_POST['nome'] ?? '',
            'imagem' => '',
            'destaque' => isset($_POST['destaque']) ? 1 : 0
        ];

        if (!empty($_FILES['imagem']['name'])) {
            $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            $nomeArquivo = uniqid('categoria_', true) . '.' . $ext;
            $pasta = dirname(__DIR__, 3) . '/public/uploads/categorias/';

            if (!is_dir($pasta)) {
                mkdir($pasta, 0777, true);
            }

            $caminho = $pasta . $nomeArquivo;

            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
                $dados['imagem'] = 'uploads/categorias/' . $nomeArquivo;
            }
        }

        $this->categoria->salvar($dados);

        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => 'Categoria cadastrada com sucesso!'
        ];

        header('Location: index.php?url=categorias/listar');
        exit;
    }

    public function editar(): void
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            echo "ID não informado.";
            return;
        }

        $categoria = $this->categoria->buscarPorId($id);

        if (!$categoria) {
            echo "Categoria não encontrada.";
            return;
        }

        echo $this->twig->render(
            'admin/categorias/editar.twig', [
            'categoria' => $categoria,
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

        $categoriaExistente = $this->categoria->buscarPorId($id);
        $imagemAtual = $categoriaExistente['imagem'] ?? '';

        $dados = [
            'nome' => $_POST['nome'] ?? '',
            'imagem' => $imagemAtual,
            'destaque' => isset($_POST['destaque']) ? 1 : 0
        ];

        if (!empty($_FILES['imagem']['name'])) {
            $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            $nomeArquivo = uniqid('categoria_', true) . '.' . $ext;
            $pasta = dirname(__DIR__, 3) . '/public/uploads/categorias/';

            if (!is_dir($pasta)) {
                mkdir($pasta, 0777, true);
            }

            $caminho = $pasta . $nomeArquivo;

            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
                $dados['imagem'] = 'uploads/categorias/' . $nomeArquivo;
            }
        }

        $this->categoria->atualizar($id, $dados);

        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => 'Categoria atualizada com sucesso!'
        ];

        header('Location: index.php?url=categorias/listar');
        exit;
    }

    public function excluir(): void
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            echo "ID não informado.";
            return;
        }

        $this->categoria->excluir($id);

        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => 'Categoria excluída com sucesso!'
        ];

        header('Location: index.php?url=categorias/listar');
        exit;
    }
}
