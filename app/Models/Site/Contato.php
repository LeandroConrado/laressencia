<?php

namespace App\Models\Site;

use App\Core\Database;
use PDO;

class Contato
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::conectar();
    }

    public function salvar(array $dados): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO mensagens_contato (nome, email, assunto, mensagem, data_envio)
            VALUES (?, ?, ?, ?, NOW())
        ");

        return $stmt->execute([
            $dados['nome'],
            $dados['email'],
            $dados['assunto'],
            $dados['mensagem']
        ]);
    }

    public function listar(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM mensagens_contato ORDER BY data_envio DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM mensagens_contato WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM mensagens_contato WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
