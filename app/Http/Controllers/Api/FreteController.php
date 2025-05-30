<?php

namespace App\Http\Controllers\Api;

use App\Services\MelhorEnvioService;
use Exception;

class FreteController
{
    public function calcular($produtoId, $quantidade, $cep): void
    {
        try {
            $produtoId = (int)$produtoId;
            $quantidade = (int)$quantidade;

            // ✅ Quando produtoId = 0, usamos o carrinho
            if ($produtoId === 0) {
                session_start();
                $carrinho = $_SESSION['carrinho'] ?? [];

                if (empty($carrinho)) {
                    echo json_encode([
                        'status' => 'erro',
                        'mensagem' => 'Carrinho vazio ou não encontrado.'
                    ]);
                    return;
                }

                $fretes = (new MelhorEnvioService())->calcularFreteCarrinho($carrinho, $cep);

            } else {
                // ✅ Produto individual
                $fretes = (new MelhorEnvioService())->calcularFrete($produtoId, $quantidade, $cep);
            }

            echo json_encode(['status' => 'ok', 'fretes' => $fretes]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }
}