<?php

namespace App\Models\Site;

use App\Core\Database;
use PDO;

class Cliente
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::conectar();
    }

    public function salvar(array $dados): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO clientes (nome, email, telefone, cpf, rua, numero_endereco, bairro, cidade, estado, cep)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $dados['nome'],
            $dados['email'],
            $dados['telefone'],
            $dados['cpf'],
            $dados['rua'],
            $dados['numero_endereco'],
            $dados['bairro'],
            $dados['cidade'],
            $dados['estado'],
            $dados['cep'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function buscarPorEmail(string $email): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorCpf(string $cpf): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE cpf = ? LIMIT 1");
        $stmt->execute([$cpf]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE clientes SET nome = ?, email = ?, telefone = ?, rua = ?, numero_endereco = ?, bairro = ?, cidade = ?, estado = ?, cep = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $dados['nome'],
            $dados['email'],
            $dados['telefone'],
            $dados['rua'],
            $dados['numero_endereco'],
            $dados['bairro'],
            $dados['cidade'],
            $dados['estado'],
            $dados['cep'],
            $id
        ]);
    }
}
