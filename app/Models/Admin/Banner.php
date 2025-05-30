<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;

class Banner
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
            SELECT *
            FROM banners
            ORDER BY ordem ASC, id DESC
        "
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarAtivosPorPagina(string $pagina): array
    {
        $stmt = $this->pdo->prepare(
            "
            SELECT *
            FROM banners
            WHERE ativo = 1
              AND pagina = :pagina
              AND (data_inicio IS NULL OR data_inicio <= CURDATE())
              AND (data_fim IS NULL OR data_fim >= CURDATE())
            ORDER BY ordem ASC, id DESC
        "
        );
        $stmt->execute(['pagina' => $pagina]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function salvar(array $dados): bool
    {
        $stmt = $this->pdo->prepare(
            "
            INSERT INTO banners 
            (titulo, texto_botao, cor_fundo_botao, cor_texto_botao, url_destino, pagina, ordem, ativo, data_inicio, data_fim, tipo_midia, midia)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        "
        );
        return $stmt->execute(
            [
            $dados['titulo'],
            $dados['texto_botao'],
            $dados['cor_fundo_botao'],
            $dados['cor_texto_botao'],
            $dados['url_destino'],
            $dados['pagina'],
            $dados['ordem'],
            $dados['ativo'],
            $dados['data_inicio'],
            $dados['data_fim'],
            $dados['tipo_midia'],
            $dados['midia']
            ]
        );
    }

    public function atualizar($id, array $dados): bool
    {
        $stmt = $this->pdo->prepare(
            "
            UPDATE banners
            SET titulo = ?, texto_botao = ?, cor_fundo_botao = ?, cor_texto_botao = ?, url_destino = ?, pagina = ?, ordem = ?, ativo = ?, data_inicio = ?, data_fim = ?, tipo_midia = ?, midia = ?
            WHERE id = ?
        "
        );
        return $stmt->execute(
            [
            $dados['titulo'],
            $dados['texto_botao'],
            $dados['cor_fundo_botao'],
            $dados['cor_texto_botao'],
            $dados['url_destino'],
            $dados['pagina'],
            $dados['ordem'],
            $dados['ativo'],
            $dados['data_inicio'],
            $dados['data_fim'],
            $dados['tipo_midia'],
            $dados['midia'],
            $id
            ]
        );
    }

    public function excluir($id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM banners WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
