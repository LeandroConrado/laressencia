<?php

namespace App\Models\Admin;

use PDO;
use Exception;

class Publicidade
{
    private PDO $conexao;

    public function __construct()
    {
        $this->conexao = \Leandroconrado\Laressencia\Core\Database::conectar();
    }

    public function buscarTodos(): array
    {
        $sql = "SELECT * FROM publicidades ORDER BY id DESC";
        $stmt = $this->conexao->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT * FROM publicidades WHERE id = :id LIMIT 1";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    public function salvar(array $dados): void
    {
        $sql = "INSERT INTO publicidades (titulo, imagem, link, pagina, ativo, criado_em)
                VALUES (:titulo, :imagem, :link, :pagina, :ativo, NOW())";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(':titulo', $dados['titulo']);
        $stmt->bindValue(':imagem', $dados['imagem']);
        $stmt->bindValue(':link', $dados['link']);
        $stmt->bindValue(':pagina', $dados['pagina']);
        $stmt->bindValue(':ativo', $dados['ativo'], PDO::PARAM_INT);
        $stmt->execute();
    }

    public function atualizar(int $id, array $dados): void
    {
        $sql = "UPDATE publicidades SET 
                    titulo = :titulo,
                    imagem = :imagem,
                    link = :link,
                    pagina = :pagina,
                    ativo = :ativo
                WHERE id = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(':titulo', $dados['titulo']);
        $stmt->bindValue(':imagem', $dados['imagem']);
        $stmt->bindValue(':link', $dados['link']);
        $stmt->bindValue(':pagina', $dados['pagina']);
        $stmt->bindValue(':ativo', $dados['ativo'], PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function excluir(int $id): void
    {
        $sql = "DELETE FROM publicidades WHERE id = :id";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
