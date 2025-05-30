<?php

namespace App\Http\Controllers\Site;

use App\Core\Container;
use App\Models\Admin\Configuracao;

class FaqController
{
    private $twig;

    public function __construct()
    {
        $this->twig = Container::makeTwig(); // Usando container unificado
    }

    public function index(): void
    {
        $config = (new Configuracao())->buscar();

        echo $this->twig->render('site/themes/default/faq/index.twig', [
            'config' => $config
        ]);
    }
}
