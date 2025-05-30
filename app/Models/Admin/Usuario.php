<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;

class Usuario
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::conectar();
    }

    public function buscarTodos(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM usuarios ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorEmail(string $email): array|false
    {
        $stmt = $this->pdo->prepare("SELECT id, nome, email, senha, foto, tipo, modulos FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function salvar(array $dados): bool
    {
        $stmt = $this->pdo->prepare(
            "
            INSERT INTO usuarios (nome, email, senha, ativo, foto) 
            VALUES (?, ?, ?, ?, ?)
        "
        );
        return $stmt->execute(
            [
            $dados['nome'],
            $dados['email'],
            $dados['senha'],
            $dados['ativo'],
            $dados['foto']
            ]
        );
    }

    public function atualizar(int $id, array $dados): bool
    {
        $campos = [
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'ativo' => $dados['ativo'],
            'foto' => $dados['foto']
        ];

        $sql = "UPDATE usuarios SET nome = :nome, email = :email, ativo = :ativo, foto = :foto";

        if (!empty($dados['senha'])) {
            $campos['senha'] = $dados['senha'];
            $sql .= ", senha = :senha";
        }

        $sql .= " WHERE id = :id";
        $campos['id'] = $id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($campos);
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function contar(): int
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios";
        $stmt = $this->pdo->query($sql);
        return (int) $stmt->fetch()['total'];
    }
}
