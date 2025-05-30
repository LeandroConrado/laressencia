<?php

namespace App\Http\Controllers\Site;

use App\Core\Container;
use App\Models\Site\Contato;
use App\Models\Admin\Configuracao;

class ContatoController
{
    private $twig;

    public function __construct()
    {
        $this->twig = Container::makeTwig(); // Twig centralizado
    }

    public function index(): void
    {
        $config = (new Configuracao())->buscar();

        echo $this->twig->render('site/themes/default/contato/index.twig', [
            'config' => $config,
            'mensagem_enviada' => $_SESSION['mensagem_enviada'] ?? false
        ]);

        unset($_SESSION['mensagem_enviada']);
    }

    public function enviar(): void
    {
        $dados = [
            'nome'     => $_POST['nome'] ?? '',
            'email'    => $_POST['email'] ?? '',
            'assunto'  => $_POST['assunto'] ?? '',
            'mensagem' => $_POST['mensagem'] ?? '',
        ];

        $contato = new Contato();
        $contato->salvar($dados);

        $_SESSION['mensagem_enviada'] = true;

        header("Location: " . base_url() . "/contato");
        exit;
    }
}
