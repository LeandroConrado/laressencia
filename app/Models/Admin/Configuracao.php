<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;

class Configuracao
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::conectar();
    }

    public function listarTodas(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM configuracoes ORDER BY chave ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obterValor(string $chave): string|null
    {
        $stmt = $this->pdo->prepare("SELECT valor FROM configuracoes WHERE chave = ?");
        $stmt->execute([$chave]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['valor'] ?? null;
    }

    public function atualizar(string $chave, string $valor): bool
    {
        $stmt = $this->pdo->prepare("UPDATE configuracoes SET valor = ? WHERE chave = ?");
        return $stmt->execute([$valor, $chave]);
    }

    public function buscar(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM configuracoes");
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $config = [];
        foreach ($resultados as $linha) {
            $config[$linha['chave']] = $linha['valor'];
        }

        return $config;
    }
}
