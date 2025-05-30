<?php

namespace App\Http\Controllers\Site;

use App\Core\Container;
use App\Models\Site\Categoria;
use App\Models\Admin\Configuracao;

class CategoriaController
{
    private $twig;

    public function __construct()
    {
        $this->twig = Container::makeTwig(); // Usa o container centralizado
    }

    public function listar(): void
    {
        $categoriaModel = new Categoria();
        $categorias = $categoriaModel->buscarTodas();

        $configModel = new Configuracao();
        $config = $configModel->buscar();

        echo $this->twig->render('site/themes/default/categorias/listar.twig', [
            'categorias' => $categorias,
            'config' => $config
        ]);
    }

    public function ver($id): void
    {
        $categoriaModel = new Categoria();
        $categoria = $categoriaModel->buscarPorId($id);
        $produtos = $categoriaModel->buscarProdutos($id);

        $configModel = new Configuracao();
        $config = $configModel->buscar();

        echo $this->twig->render('site/themes/default/categorias/ver.twig', [
            'categoria' => $categoria,
            'produtos'  => $produtos,
            'config'    => $config
        ]);
    }
}
