<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database;
use App\Models\Admin\Produto;
use App\Models\Admin\Pedido;
use App\Models\Admin\Categoria;
use App\Models\Admin\Usuario;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use PDO;

class DashboardController
{
    private Environment $twig;
    private PDO $pdo;

    public function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../../../templates');
        $this->twig = new Environment($loader);
        $this->pdo = Database::conectar();
    }

    public function index(): void
    {
        verificar_login(); // protege o acesso como middleware

        $produtoModel = new Produto();
        $pedidoModel = new Pedido();
        $categoriaModel = new Categoria();
        $usuarioModel = new Usuario();

        $total_vendas = $pedidoModel->somarVendas();
        $total_produtos = $produtoModel->contar();
        $total_pedidos = $pedidoModel->contar();
        $total_usuarios = $usuarioModel->contar();
        $total_categorias = $categoriaModel->contar();
        $valores_por_mes = $pedidoModel->valoresPorMes();

        $total_visitas = $this->getTotalVisitas();
        $usuarios_online = $this->getUsuariosOnline();

        $meses = $pedidoModel->mesesPedidos();
        $pedidos_por_mes = $pedidoModel->pedidosPorMes();

        echo $this->twig->render(
            'admin/dashboard/dashboard.twig',
            [
                'total_produtos' => $total_produtos,
                'total_pedidos' => $total_pedidos,
                'total_usuarios' => $total_usuarios,
                'total_categorias' => $total_categorias,
                'total_vendas' => $total_vendas,
                'total_visitas' => $total_visitas,
                'usuarios_online' => $usuarios_online,
                'meses' => $meses,
                'valores_por_mes' => $valores_por_mes,
                'pedidos_por_mes' => $pedidos_por_mes,
                'session' => session_user() // <- SEM warning
            ]
        );
    }

    private function getTotalVisitas(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM visitas");
        return (int) $stmt->fetchColumn();
    }

    private function getUsuariosOnline(): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(DISTINCT ip) FROM visitas WHERE data_acesso > (NOW() - INTERVAL 5 MINUTE)");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
