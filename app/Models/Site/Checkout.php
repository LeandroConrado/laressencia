<?php
// Apenas uma implementação de referência para você verificar seu próprio arquivo

namespace App\Models\Site;

class Checkout
{

    
public function criarPedido(array $dados): int
{
    $connection = \App\Core\Database::conectar();
    
    try {
        // Start transaction
        $connection->beginTransaction();
        error_log("Transaction started for order creation");
        
        // Insert order record
        $stmt = $connection->prepare("INSERT INTO pedidos (
            cliente_id, 
            forma_pagamento, 
            frete_selecionado, 
            valor_subtotal, 
            valor_frete, 
            valor_total, 
            status, 
            data_criacao
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $status = 'pendente';
        $stmt->execute([
            $dados['cliente_id'],
            $dados['forma_pagamento'],
            $dados['frete_selecionado'],
            $dados['subtotal'],
            $dados['frete'],
            $dados['total'],
            $status
        ]);
        
        $pedidoId = $connection->lastInsertId();
        error_log("Order record created with ID: " . $pedidoId);
        
        // Insert order items
        if (!empty($dados['produtos'])) {
            $stmtItem = $connection->prepare("INSERT INTO pedido_itens (
                pedido_id, 
                produto_id, 
                quantidade, 
                valor_unitario, 
                valor_total
            ) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($dados['produtos'] as $produto) {
                $stmtItem->execute([
                    $pedidoId,
                    $produto['id'],
                    $produto['quantidade'],
                    $produto['preco'],
                    $produto['subtotal']
                ]);
                error_log("Added item to order: Product ID " . $produto['id']);
            }
        }
        
        // Commit transaction
        $connection->commit();
        error_log("Transaction committed successfully");
        
        return $pedidoId;
    } catch (\Exception $e) {
        // Rollback transaction on error
        if ($connection->inTransaction()) {
            $connection->rollBack();
            error_log("Transaction rolled back due to error");
        }
        
        error_log("Order creation error: " . $e->getMessage());
        return 0;
    }
}
    
    public function buscarPorId(int $id): array
    {
        try {
            // Implementação para buscar o pedido no banco
            $db = \App\Core\Database::getConnection();
            
            $stmt = $db->prepare("
                SELECT p.*, c.nome as cliente_nome, c.email as cliente_email 
                FROM pedidos p
                JOIN clientes c ON p.cliente_id = c.id
                WHERE p.id = ?
            ");
            
            $stmt->execute([$id]);
            $pedido = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$pedido) {
                return [];
            }
            
            // Buscar os itens do pedido
            $stmtItens = $db->prepare("
                SELECT pi.*, pr.nome as produto_nome 
                FROM pedido_itens pi
                JOIN produtos pr ON pi.produto_id = pr.id
                WHERE pi.pedido_id = ?
            ");
            
            $stmtItens->execute([$id]);
            $pedido['itens'] = $stmtItens->fetchAll(\PDO::FETCH_ASSOC);
            
            return $pedido;
        } catch (\Exception $e) {
            error_log("Erro ao buscar pedido: " . $e->getMessage());
            return [];
        }
    }
}