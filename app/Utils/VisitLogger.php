<?php

namespace App\Utils;

use Leandroconrado\Laressencia\Core\Database;
use PDO;

class VisitLogger
{
    public static function registrar(): void
    {
        try {
            $pdo = Database::conectar();

            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $url = $_SERVER['REQUEST_URI'] ?? '';
            $agente = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $data = date('Y-m-d H:i:s');

            $stmt = $pdo->prepare(
                "
                INSERT INTO visitas (ip, url, agente, criado_em, data_acesso, pagina) 
                VALUES (?, ?, ?, ?, ?, ?)
            "
            );
            $stmt->execute(
                [
                $ip,
                $url,
                $agente,
                $data,
                $data,
                $url // ou um slug mais limpo se quiser
                ]
            );
        } catch (\Exception $e) {
            // Para debug local, remova em produção
            error_log('Erro ao registrar visita: ' . $e->getMessage());
        }
    }
}
