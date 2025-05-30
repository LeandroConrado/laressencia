<?php

namespace App\Http\Controllers\Site;

use App\Core\Container;
use App\Models\Admin\Configuracao;

class SiteController
{
    private $twig;

    public function __construct()
    {
        $this->twig = Container::makeTwig(); // Twig centralizado
    }

    public function pagina404(): void
    {
        http_response_code(404);
        $config = (new Configuracao())->buscar();

        echo $this->twig->render('site/themes/default/paginas/404.twig', [
            'config' => $config
        ]);
    }

    public function manutencao(): void
    {
        http_response_code(503);
        $config = (new Configuracao())->buscar();

        echo $this->twig->render('site/themes/default/paginas/manutencao.twig', [
            'config' => $config
        ]);
    }

    public function termos(): void
    {
        $config = (new Configuracao())->buscar();

        echo $this->twig->render('site/themes/default/paginas/termos.twig', [
            'config' => $config
        ]);
    }
}
