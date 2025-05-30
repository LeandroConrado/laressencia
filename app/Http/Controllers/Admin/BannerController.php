<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Banner;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class BannerController
{
    private Banner $banner;
    private Environment $twig;

    public function __construct()
    {
        $this->banner = new Banner();
        $loader = new FilesystemLoader(__DIR__ . '/../../../templates');
        $this->twig = new Environment($loader);
    }

    public function listar()
    {
        $banners = $this->banner->buscarTodos();
        echo $this->twig->render('admin/banners/listar.twig', ['banners' => $banners, 'session' => $_SESSION]);
        unset($_SESSION['alerta']);
    }

    public function novo()
    {
        echo $this->twig->render('admin/banners/novo.twig', ['session' => $_SESSION]);
        unset($_SESSION['alerta']);

        exit;
    }

    public function salvar()
    {
        $dados = $this->coletarDadosFormulario();

        if (!empty($_FILES['midia']['name'])) {
            $extensao = strtolower(pathinfo($_FILES['midia']['name'], PATHINFO_EXTENSION));
            $nomeArquivo = uniqid('banner_', true) . '.' . $extensao;
            $pasta = $_SERVER['DOCUMENT_ROOT'] . '/laressencia/public/uploads/banners/';

            if (!is_dir($pasta)) {
                mkdir($pasta, 0777, true);
            }

            $caminho = $pasta . $nomeArquivo;

            if (move_uploaded_file($_FILES['midia']['tmp_name'], $caminho)) {
                $dados['midia'] = $nomeArquivo;
                $dados['tipo_midia'] = in_array($extensao, ['mp4', 'webm']) ? 'video' : 'imagem';
            } else {
                $_SESSION['alerta'] = ['tipo' => 'error', 'mensagem' => 'Erro ao mover a mídia.'];
                header('Location: index.php?url=banners/listar');
                exit;
            }
        }

        $this->banner->salvar($dados);
        $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Banner cadastrado com sucesso!'];
        header('Location: index.php?url=banners/listar');
        exit;
    }

    public function editar()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            echo "ID não informado.";
            return;
        }

        $banner = $this->banner->buscarPorId($id);
        echo $this->twig->render('admin/banners/editar.twig', ['banner' => $banner, 'session' => $_SESSION]);
        unset($_SESSION['alerta']);
    }

    public function atualizar()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo "ID não informado.";
            return;
        }

        $dados = $this->coletarDadosFormulario();
        $dados['midia'] = $_POST['midia_atual'] ?? '';
        $dados['tipo_midia'] = $_POST['tipo_midia_atual'] ?? 'imagem';

        if (!empty($_FILES['midia']['name'])) {
            $extensao = strtolower(pathinfo($_FILES['midia']['name'], PATHINFO_EXTENSION));
            $nomeArquivo = uniqid('banner_', true) . '.' . $extensao;
            $pasta = $_SERVER['DOCUMENT_ROOT'] . '/laressencia/public/uploads/banners/';

            if (!is_dir($pasta)) {
                mkdir($pasta, 0777, true);
            }

            $caminho = $pasta . $nomeArquivo;

            if (move_uploaded_file($_FILES['midia']['tmp_name'], $caminho)) {
                $dados['midia'] = $nomeArquivo;
                $dados['tipo_midia'] = in_array($extensao, ['mp4', 'webm']) ? 'video' : 'imagem';
            } else {
                $_SESSION['alerta'] = ['tipo' => 'error', 'mensagem' => 'Erro ao mover a mídia.'];
                header('Location: index.php?url=banners/listar');
                exit;
            }
        }

        $this->banner->atualizar($id, $dados);
        $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Banner atualizado com sucesso!'];
        header('Location: index.php?url=banners/listar');
        exit;
    }

    public function excluir()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->banner->excluir($id);
            $_SESSION['alerta'] = ['tipo' => 'success', 'mensagem' => 'Banner excluído com sucesso!'];
        }

        header('Location: index.php?url=banners/listar');
        exit;
    }

    private function coletarDadosFormulario(): array
    {
        return [
            'titulo' => $_POST['titulo'] ?? '',
            'texto_botao' => $_POST['texto_botao'] ?? '',
            'cor_fundo_botao' => $_POST['cor_fundo_botao'] ?? '#007aff',
            'cor_texto_botao' => $_POST['cor_texto_botao'] ?? '#ffffff',
            'url_destino' => $_POST['url_destino'] ?? '',
            'pagina' => $_POST['pagina'] ?? 'home',
            'ordem' => (int) ($_POST['ordem'] ?? 0),
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'data_inicio' => $_POST['data_inicio'] ?? null,
            'data_fim' => $_POST['data_fim'] ?? null,
            'midia' => '',
            'tipo_midia' => 'imagem'
        ];
    }
}
