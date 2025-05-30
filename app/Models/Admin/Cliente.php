<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;

class Cliente
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::conectar();
    }

    public function buscarTodos(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM clientes ORDER BY nome ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cadastrar(array $dados): int
    {
        $requiredFields = ['nome', 'email', 'cpf', 'telefone', 'cep', 'estado', 'cidade', 'bairro', 'rua', 'numero'];
        foreach ($requiredFields as $field) {
            if (!isset($dados[$field]) || empty(trim($dados[$field]))) {
                throw new \Exception("Campo obrigatório '$field' está faltando ou vazio.");
            }
        }

        $stmt = $this->pdo->prepare(
            "
            INSERT INTO clientes (nome, email, cpf, telefone, cep, estado, cidade, bairro, rua, numero)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        "
        );
        $stmt->execute(
            [
            $dados['nome'],
            $dados['email'],
            $dados['cpf'],
            $dados['telefone'],
            $dados['cep'],
            $dados['estado'],
            $dados['cidade'],
            $dados['bairro'],
            $dados['rua'],
            $dados['numero']
            ]
        );

        return (int) $this->pdo->lastInsertId();
    }

    public function salvar(array $dados): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO clientes (nome, email, telefone, rua, numero, bairro, cidade, estado, cep) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute(
            [
            $dados['nome'],
            $dados['email'],
            $dados['telefone'],
            $dados['rua'],
            $dados['numero'],
            $dados['bairro'],
            $dados['cidade'],
            $dados['estado'],
            $dados['cep']
            ]
        );
    }

    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->pdo->prepare("UPDATE clientes SET nome = ?, email = ?, telefone = ?, rua = ?, numero = ?, bairro = ?, cidade = ?, estado = ?, cep = ? WHERE id = ?");
        return $stmt->execute(
            [
            $dados['nome'],
            $dados['email'],
            $dados['telefone'],
            $dados['rua'],
            $dados['numero'],
            $dados['bairro'],
            $dados['cidade'],
            $dados['estado'],
            $dados['cep'],
            $id
            ]
        );
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM clientes WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function buscarPorEmail(string $email): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
