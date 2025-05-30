<?php

namespace App\Http\Controllers\Site;

use App\Core\Container;
use App\Models\Admin\Configuracao;

class PoliticasController
{
    private $twig;

    public function __construct()
    {
        $this->twig = Container::makeTwig(); // Container centralizado
    }

    public function index(): void
    {
        $config = (new Configuracao())->buscar();

        echo $this->twig->render('site/themes/default/politicas/index.twig', [
            'config' => $config
        ]);
    }
}
