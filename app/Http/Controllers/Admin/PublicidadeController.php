<?php

// src/Controller/Admin/PublicidadeController.php

namespace App\Http\Controllers\Admin;

use Apps\Models\Admin\Publicidade;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class PublicidadeController
{
    private Publicidade $publicidade;
    private Environment $twig;

    public function __construct()
    {
        $this->publicidade = new Publicidade();
        $loader = new FilesystemLoader(__DIR__ . '/../../../templates');
        $this->twig = new Environment($loader);
    }

    public function listar()
    {
        $publicidades = $this->publicidade->buscarTodos();
        echo $this->twig->render('admin/publicidade/listar.twig', ['publicidades' => $publicidades, 'session' => $_SESSION]);
        unset($_SESSION['alerta']);
    }

    public function novo()
    {
        $paginas = $this->listarPaginasDisponiveis();
        echo $this->twig->render('admin/publicidade/novo.twig', ['paginas' => $paginas]);
    }

    public function salvar()
    {
        $dados = $this->coletarDados();

        if ($imagem = $this->uploadImagem()) {
            $dados['imagem'] = $imagem;
        }

        $this->publicidade->salvar($dados);
        $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Publicidade cadastrada com sucesso!'];
        header('Location: index.php?url=publicidade/listar');
    }

    public function editar()
    {
        $id = $_GET['id'] ?? null;
        $publicidade = $this->publicidade->buscarPorId($id);
        $paginas = $this->listarPaginasDisponiveis();
        echo $this->twig->render('admin/publicidade/editar.twig', ['publicidade' => $publicidade, 'paginas' => $paginas]);
    }

    public function atualizar()
    {
        $id = $_GET['id'] ?? null;
        $dados = $this->coletarDados();
        $dados['imagem'] = $_POST['imagem_atual'] ?? '';

        if ($imagem = $this->uploadImagem()) {
            $dados['imagem'] = $imagem;
        }

        $this->publicidade->atualizar($id, $dados);
        $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Publicidade atualizada com sucesso!'];
        header('Location: index.php?url=publicidade/listar');
    }

    public function excluir()
    {
        $id = $_GET['id'] ?? null;
        $this->publicidade->excluir($id);
        $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Publicidade excluída com sucesso!'];
        header('Location: index.php?url=publicidade/listar');
    }

    private function coletarDados(): array
    {
        return [
            'titulo' => $_POST['titulo'] ?? '',
            'link' => $_POST['link'] ?? '',
            'pagina' => $_POST['pagina'] ?? '',
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'imagem' => ''
        ];
    }

    private function listarPaginasDisponiveis(): array
    {
        return ['home', 'produtos', 'categorias', 'contato', 'sobre'];
    }

    private function uploadImagem(): ?string
    {
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $nome = 'publicidade_' . uniqid() . '.' . $ext;
            $pasta = $_SERVER['DOCUMENT_ROOT'] . '/laressencia/public/uploads/publicidade/';

            if (!is_dir($pasta)) {
                mkdir($pasta, 0777, true);
            }

            $caminho = $pasta . $nome;

            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
                return $nome;
            }
        }
        return null;
    }
}
