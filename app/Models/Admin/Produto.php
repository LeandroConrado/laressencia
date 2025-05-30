<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;

class Produto
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::conectar();
    }

    public function buscarTodos(): array
    {
        $stmt = $this->pdo->query(
            "
        SELECT p.*, c.nome AS nome_categoria
        FROM produtos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        ORDER BY p.id DESC
"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function salvar(array $dados): bool
    {
        $stmt = $this->pdo->prepare(
            "
        INSERT INTO produtos (nome, descricao, preco, estoque, midia, tipo_midia, categoria_id, destaque)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    "
        );
        return $stmt->execute(
            [
            $dados['nome'],
            $dados['descricao'],
            $dados['preco'],
            $dados['estoque'],
            $dados['midia'] ?? '',           // Evita erro se não enviado
            $dados['tipo_midia'] ?? 'imagem',
            $dados['categoria_id'],
            $dados['destaque'] ?? 0          // Checkbox pode não vir setado
            ]
        );
    }

    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->pdo->prepare(
            "
        UPDATE produtos
        SET nome = ?, descricao = ?, preco = ?, estoque = ?, midia = ?, tipo_midia = ?, categoria_id = ?, destaque = ?
        WHERE id = ?
    "
        );
        return $stmt->execute(
            [
            $dados['nome'],
            $dados['descricao'],
            $dados['preco'],
            $dados['estoque'],
            $dados['midia'] ?? '',
            $dados['tipo_midia'] ?? 'imagem',
            $dados['categoria_id'],
            $dados['destaque'] ?? 0,
            $id
            ]
        );
    }


    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM produtos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function contar(): int
    {
        $sql = "SELECT COUNT(*) as total FROM produtos";
        $stmt = $this->pdo->query($sql);
        return (int) $stmt->fetch()['total'];
    }

}
