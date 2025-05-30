<?php

namespace App\Models\Site;

use App\Core\Database;
use PDO;

class Produto
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::conectar();
    }

    // Buscar destaques paginados
    public function buscarDestaquesPaginado(int $limite = 4, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM produtos
            WHERE destaque = 1
            ORDER BY id DESC
            LIMIT :limite OFFSET :offset
        ");
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->limparMidias($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Contar total de produtos em destaque
    public function contarDestaques(): int
    {
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as total 
            FROM produtos
            WHERE destaque = 1
        ");
        return (int) $stmt->fetch()['total'];
    }

    // Buscar produto por ID
    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            $produto['midia'] = ltrim($produto['midia'], '/');
        }

        return $produto;
    }

    // Buscar todos os produtos
    public function buscarTodos(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM produtos ORDER BY id DESC");
        return $this->limparMidias($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Buscar todos os produtos ativos
    public function buscarTodosAtivos(): array
    {
        $sql = "SELECT * FROM produtos WHERE ativo = 1 ORDER BY nome ASC";
        $stmt = $this->pdo->query($sql);
        return $this->limparMidias($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Buscar produtos por categoria
    public function buscarPorCategoria(int $categoriaId): array
    {
        $sql = "SELECT * FROM produtos WHERE categoria_id = :categoria_id AND ativo = 1 ORDER BY nome ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':categoria_id', $categoriaId, PDO::PARAM_INT);
        $stmt->execute();

        return $this->limparMidias($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Buscar produto por slug
    public function buscarPorSlug(string $slug): ?array
    {
        $sql = "SELECT * FROM produtos WHERE slug = :slug";
        $stmt = $this->pdo->prepare($sql); // corrigido aqui
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            $produto['midia'] = ltrim($produto['midia'], '/');
        }

        return $produto ?: null;
    }

    // Utilitário interno para limpar barras de midia
private function limparMidias(array $produtos): array
{
    foreach ($produtos as &$produto) {
        if (isset($produto['midia'])) {
            $produto['midia'] = ltrim($produto['midia'], '/');
        }
    }
    return $produtos;
}
}
