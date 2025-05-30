<?php

namespace App\Models\Admin;

use App\Core\Database;
use PDO;

class Pedido
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::conectar();
    }

    public function buscarTodosComCliente(): array
    {
        $sql = "
            SELECT p.*, c.nome AS nome_cliente 
            FROM pedidos p 
            JOIN clientes c ON p.cliente_id = c.id
            ORDER BY p.id DESC
        ";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorIdComItens(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            "
            SELECT 
                p.*, 
                c.nome AS nome_cliente, 
                c.email,
                c.cpf,
                c.telefone,
                c.rua, 
                c.numero AS numero_endereco,
                c.bairro, 
                c.cidade, 
                c.estado, 
                c.cep
            FROM pedidos p 
            JOIN clientes c ON p.cliente_id = c.id
            WHERE p.id = ?
        "
        );
        $stmt->execute([$id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($pedido) {
            $itensStmt = $this->pdo->prepare(
                "
                SELECT 
                    i.produto_id,
                    i.quantidade,
                    i.preco_unitario AS preco,
                    pr.nome AS nome_produto
                FROM itens_pedido i
                JOIN produtos pr ON i.produto_id = pr.id
                WHERE i.pedido_id = ?
            "
            );
            $itensStmt->execute([$id]);
            $pedido['itens'] = $itensStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    
        return $pedido;
    }
    

    public function salvar(array $dados): int
    {
        $numero = 'PED' . time(); // Número gerado automaticamente
        $stmt = $this->pdo->prepare(
            "
            INSERT INTO pedidos (cliente_id, status, total, numero, criado_em) 
            VALUES (?, ?, ?, ?, NOW())
        "
        );
        $stmt->execute(
            [
            $dados['cliente_id'],
            $dados['status'],
            $dados['total'],
            $numero
            ]
        );

        $pedidoId = $this->pdo->lastInsertId();

        foreach ($dados['itens'] as $item) {
            $stmtItem = $this->pdo->prepare(
                "
                INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) 
                VALUES (?, ?, ?, ?)
            "
            );
            $stmtItem->execute(
                [
                $pedidoId,
                $item['produto_id'],
                $item['quantidade'],
                $item['preco_unitario']
                ]
            );
        }

        return $pedidoId;
    }

    public function atualizarStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function contar(): int
    {
        $sql = "SELECT COUNT(*) as total FROM pedidos";
        $stmt = $this->pdo->query($sql);
        return (int) $stmt->fetch()['total'];
    }

    public function cadastrarPedidoSite(int $clienteId, string $formaPagamento, array $frete): int
    {
        $subtotal = 0;
        foreach ($_SESSION['carrinho'] as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }

        $freteValor = $frete['valor'] ?? 0;
        $freteTipo = $frete['tipo'] ?? 'Não informado';
        $total = $subtotal + $freteValor;
        $numero = 'PED' . time();

        $stmt = $this->pdo->prepare(
            "
            INSERT INTO pedidos 
            (cliente_id, status, total, numero, forma_pagamento, frete_valor, frete_tipo, criado_em) 
            VALUES (?, 'Pendente', ?, ?, ?, ?, ?, NOW())
        "
        );
        $stmt->execute(
            [
            $clienteId,
            $total,
            $numero,
            $formaPagamento,
            $freteValor,
            $freteTipo
            ]
        );

        $pedidoId = (int) $this->pdo->lastInsertId();

        foreach ($_SESSION['carrinho'] as $item) {
            $stmtItem = $this->pdo->prepare(
                "
                INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) 
                VALUES (?, ?, ?, ?)
            "
            );
            $stmtItem->execute(
                [
                $pedidoId,
                $item['id'],
                $item['quantidade'],
                $item['preco']
                ]
            );
        }

        return $pedidoId;
    }

    public function gerarResumoWhatsapp(array $pedido): string
    {
        $resumo = "🧴 Olá, Laressência!\n\n";
        $resumo .= "🧾 Pedido nº {$pedido['numero']}\n";
        $resumo .= "👩 Cliente: {$pedido['nome_cliente']}\n";
        $resumo .= "📧 E-mail: {$pedido['email']}\n";
        $resumo .= "📱 Telefone: {$pedido['telefone']}\n";
        $resumo .= "📍 Endereço: {$pedido['rua']}, {$pedido['numero_endereco']} - {$pedido['bairro']} - {$pedido['cidade']}/{$pedido['estado']} - CEP {$pedido['cep']}\n\n";

        $resumo .= "🛍️ Itens:\n";

        foreach ($pedido['itens'] as $item) {
            $resumo .= "• {$item['nome_produto']} (x{$item['quantidade']}) — R$ " . number_format($item['preco'], 2, ',', '.') . "\n";
        }

        $resumo .= "\n";
        $resumo .= "💰 Total: R$ " . number_format($pedido['total'], 2, ',', '.') . "\n";
        $resumo .= "💳 Pagamento: {$pedido['forma_pagamento']}\n";

        if (!empty($pedido['frete_tipo'])) {
            $resumo .= "🚚 Entrega: {$pedido['frete_tipo']}\n";
        }

        $resumo .= "\n✨ Obrigado por perfumar sua casa com Laressência! 🧴";

        return urlencode($resumo);
    }

    public function somarVendas(): float
    {
        $stmt = $this->pdo->query("SELECT SUM(total) as total_vendas FROM pedidos WHERE status != 'cancelado'");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total_vendas'] ?? 0;
    }

    public function vendasPorCategoria(): array
    {
        $sql = "
                SELECT c.nome AS categoria, SUM(pi.quantidade * pi.preco_unitario) AS total_vendas
                FROM pedidos p
                JOIN itens_pedido pi ON pi.pedido_id = p.id
                JOIN produtos pr ON pr.id = pi.produto_id
                JOIN categorias c ON c.id = pr.categoria_id
                WHERE p.status != 'cancelado'
                GROUP BY c.nome
                ORDER BY total_vendas DESC
            ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mesesPedidos(): array
    {
        $stmt = $this->pdo->query(
            "
                SELECT DISTINCT DATE_FORMAT(data, '%m/%Y') as mes 
                FROM pedidos 
                WHERE status != 'cancelado'
                ORDER BY mes ASC
                LIMIT 12
            "
        );
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'mes');
    }
        

    public function pedidosPorMes(): array
    {
        $stmt = $this->pdo->query(
            "
                SELECT DATE_FORMAT(data, '%m/%Y') as mes, COUNT(*) as total 
                FROM pedidos 
                WHERE status != 'cancelado'
                GROUP BY DATE_FORMAT(data, '%m/%Y')
                ORDER BY MIN(data) ASC
                LIMIT 12
            "
        );

        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultado = [];
        foreach ($dados as $linha) {
            $resultado[] = (int) $linha['total'];
        }

        return $resultado;
    }

    public function valoresPorMes(): array
    {
        $stmt = $this->pdo->query(
            "
                SELECT 
                    DATE_FORMAT(criado_em, '%m/%Y') AS mes,
                    COUNT(*) AS quantidade,
                    SUM(total) AS valor
                FROM pedidos
                WHERE status != 'cancelado'
                GROUP BY DATE_FORMAT(criado_em, '%m/%Y')
                ORDER BY MIN(criado_em) ASC
                LIMIT 12
            "
        );
        
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'meses' => array_column($dados, 'mes'),
            'quantidades' => array_map('intval', array_column($dados, 'quantidade')),
            'valores' => array_map('floatval', array_column($dados, 'valor'))
        ];
    }

    public function buscarPorNumero(string $numero): ?array
    {
    $sql = "SELECT * FROM pedidos WHERE numero = ?";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$numero]);
    $pedido = $stmt->fetch();

    if ($pedido) {
        $pedido['itens'] = $this->buscarItensPorPedidoId($pedido['id']);
        return $pedido;
    }

    return null;
    }

    public function buscarItensPorPedidoId(int $pedidoId): array
    {
    $sql = "SELECT 
                pi.*, 
                p.nome AS nome_produto 
            FROM itens_pedido pi
            JOIN produtos p ON p.id = pi.produto_id
            WHERE pi.pedido_id = ?";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$pedidoId]);
    return $stmt->fetchAll();
    }


}
