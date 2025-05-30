<?php

namespace App\Http\Controllers\Site;

use App\Core\Container;
use App\Models\Site\Checkout;
use App\Models\Admin\Configuracao;

class ConfirmacaoController
{
    private $twig;

    public function __construct()
    {
        $this->twig = Container::makeTwig(); // Twig centralizado
    }

    public function index($id): void
    {
        $checkout = new Checkout();
        $pedido = $checkout->buscarPorId($id);

        if (!$pedido) {
            http_response_code(404);
            echo $this->twig->render('site/themes/default/erros/404.twig');
            return;
        }

        $config = (new Configuracao())->buscar();

        echo $this->twig->render('site/themes/default/checkout/confirmacao.twig', [
            'pedido' => $pedido,
            'config' => $config
        ]);
    }
}
