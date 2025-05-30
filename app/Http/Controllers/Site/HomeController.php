<?php

namespace App\Http\Controllers\Site;

use App\Models\Site\Produto;
use App\Models\Site\Categoria;
use App\Models\Site\Banner;
use App\Models\Admin\Configuracao;
use App\Core\Container;

class HomeController
{
    private $twig;

    public function __construct()
    {
        $this->twig = Container::makeTwig(); // Container centralizado
    }

    public function index(): void
    {
        $produtoModel = new Produto();
        $categoriaModel = new Categoria();
        $bannerModel = new Banner();
        $configModel = new Configuracao();

        $paginaAtual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
        $limitePorPagina = 4;
        $offset = ($paginaAtual - 1) * $limitePorPagina;

        $produtosDestaque = $produtoModel->buscarDestaquesPaginado($limitePorPagina, $offset);
        $totalDestaques = $produtoModel->contarDestaques();
        $categorias = $categoriaModel->buscarTodas();
        $banners = $bannerModel->buscarTodosAtivos();
        $config = $configModel->buscar();

        echo $this->twig->render('site/themes/default/home.twig', [
            'destaques'     => $produtosDestaque,
            'categorias'    => $categorias,
            'banners'       => $banners,
            'paginaAtual'   => $paginaAtual,
            'totalPaginas'  => ceil($totalDestaques / $limitePorPagina),
            'config'        => $config
        ]);
    }
}
