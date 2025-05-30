<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;

class Estatistica
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::conectar();
    }

    public function totalProdutos(): int
    {
        return $this->pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
    }

    public function totalPedidos(): int
    {
        return $this->pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
    }

    public function totalClientes(): int
    {
        return $this->pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    }

    public function valorTotalVendas(): float
    {
        return $this->pdo->query("SELECT SUM(total) FROM pedidos")->fetchColumn() ?? 0;
    }

    public function vendasPorCategoria(): array
    {
        $sql = "
            SELECT c.nome AS categoria, COUNT(p.id) AS total
            FROM produtos p
            JOIN categorias c ON c.id = p.categoria_id
            GROUP BY c.nome
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function usuariosOnline(): int
    {
        // Exemplo fictício — ajuste conforme sua lógica
        return rand(1, 20);
    }

    public function visitasTotais(): int
    {
        // Exemplo fictício — ajuste conforme seu sistema de analytics
        return 1000 + rand(0, 500);
    }
}
