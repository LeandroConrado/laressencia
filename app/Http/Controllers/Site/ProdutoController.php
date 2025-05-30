<?php

namespace App\Http\Controllers\Site;

use App\Core\Container;
use App\Models\Site\Produto;
use App\Models\Admin\Configuracao;

class ProdutoController
{
    private $twig;

    public function __construct()
    {
        $this->twig = Container::makeTwig();
    }

    public function listar(): void
    {
        $produtoModel = new Produto();
        $produtos = $produtoModel->buscarTodos();
        $configModel = new Configuracao();
        $config = $configModel->buscar();

        echo $this->twig->render('site/themes/default/produtos/listar.twig', [
            'produtos' => $produtos,
            'config' => $config
        ]);
    }

    public function ver($id): void
    {
        $produtoModel = new Produto();
        $produto = $produtoModel->buscarPorId($id);
        $configModel = new Configuracao();
        $config = $configModel->buscar();

        echo $this->twig->render('site/themes/default/produtos/ver.twig', [
            'produto' => $produto,
            'config' => $config
        ]);
    }
}
