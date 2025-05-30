<?php

namespace App\Http\Controllers\Admin;

use App\Core\Container;
use App\Models\Site\Contato;

class ContatoController
{
    private $twig;
    private $contatoModel;

    public function __construct()
    {
        $this->twig = Container::makeTwig(); // Twig centralizado
        $this->contatoModel = new Contato();
    }

    public function listar(): void
    {
        $mensagens = $this->contatoModel->listar();

        echo $this->twig->render('admin/contato/listar.twig', [
            'mensagens' => $mensagens,
            'session' => $_SESSION
        ]);
        unset($_SESSION['alerta']);
    }

    public function visualizar($id): void
    {
        $mensagem = $this->contatoModel->buscarPorId($id);

        if (!$mensagem) {
            $_SESSION['alerta'] = 'Mensagem não encontrada.';
            header('Location: ' . base_url() . '/admin/contato/listar');
            exit;
        }

        echo $this->twig->render('admin/contato/visualizar.twig', [
            'mensagem' => $mensagem,
            'session' => $_SESSION
        ]);
    }

    public function excluir($id): void
    {
        $this->contatoModel->excluir($id);

        $_SESSION['alerta'] = 'Mensagem excluída com sucesso.';
        header('Location: ' . base_url() . '/admin/contato/listar');
        exit;
    }
}
