<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;

class Categoria
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::conectar();
    }

    public function buscarTodas(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function salvar(array $dados): bool
    {
        $stmt = $this->pdo->prepare(
            "
            INSERT INTO categorias (nome, imagem, destaque) 
            VALUES (?, ?, ?)
        "
        );
        return $stmt->execute(
            [
            $dados['nome'],
            $dados['imagem'] ?? '',
            $dados['destaque'] ?? 0
            ]
        );
    }

    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->pdo->prepare(
            "
            UPDATE categorias 
            SET nome = ?, imagem = ?, destaque = ? 
            WHERE id = ?
        "
        );
        return $stmt->execute(
            [
            $dados['nome'],
            $dados['imagem'] ?? '',
            $dados['destaque'] ?? 0,
            $id
            ]
        );
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM categorias WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function contar(): int
    {
        $sql = "SELECT COUNT(*) as total FROM categorias";
        $stmt = $this->pdo->query($sql);
        return (int) $stmt->fetch()['total'];
    }
}
