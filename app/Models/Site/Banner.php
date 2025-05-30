<?php

namespace App\Models\Site;

use App\Core\Database;
use PDO;

class Banner
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::conectar();
    }

    public function buscarTodosAtivos(): array
    {
        $dataHoje = date('Y-m-d');

        $sql = "SELECT * FROM banners 
                WHERE ativo = 1 
                AND (data_inicio IS NULL OR data_inicio <= :hoje)
                AND (data_fim IS NULL OR data_fim >= :hoje)
                ORDER BY ordem ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':hoje', $dataHoje);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
