<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class PedidoPdfService
{
    public function gerarPdf(array $pedido): string
    {
        $html = '
        <h2 style="color: green;">✅ Pedido Realizado com Sucesso!</h2>
        <p><strong>Número do Pedido:</strong> ' . $pedido['numero'] . '</p>
        <p><strong>Data:</strong> ' . date('d/m/Y H:i', strtotime($pedido['criado_em'])) . '</p>
        <hr>
        <h3>Dados do Cliente:</h3>
        <p><strong>Nome:</strong> ' . $pedido['nome_cliente'] . '</p>
        <p><strong>E-mail:</strong> ' . $pedido['email'] . '</p>
        <p><strong>CPF:</strong> ' . $pedido['cpf'] . '</p>
        <p><strong>Telefone:</strong> ' . $pedido['telefone'] . '</p>

        <h3>Endereço de Entrega:</h3>
        <p>' . $pedido['rua'] . ', ' . $pedido['numero_endereco'] . ' - ' . $pedido['bairro'] . '</p>
        <p>' . $pedido['cidade'] . '/' . $pedido['estado'] . ' - CEP: ' . $pedido['cep'] . '</p>';

        if (!empty($pedido['frete_valor'])) {
            $html .= '<p><strong>Frete:</strong> R$ ' . number_format($pedido['frete_valor'], 2, ',', '.') . '</p>';
        }
        if (!empty($pedido['frete_tipo'])) {
            $html .= '<p><strong>Tipo de Entrega:</strong> ' . $pedido['frete_tipo'] . '</p>';
        }

        $html .= '
        <p><strong>Forma de Pagamento:</strong> ' . $pedido['forma_pagamento'] . '</p>
        <p><strong>Status:</strong> ' . $pedido['status'] . '</p>
        <hr>
        <h3>Itens do Pedido:</h3>
        <table width="100%" border="1" cellspacing="0" cellpadding="6">
            <thead>
                <tr style="background-color: #eee;">
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>';

        $total = 0;
        foreach ($pedido['itens'] as $item) {
            $subtotal = $item['quantidade'] * $item['preco'];
            $total += $subtotal;

            $html .= '
                <tr>
                    <td>' . $item['nome_produto'] . '</td>
                    <td>' . $item['quantidade'] . '</td>
                    <td>R$ ' . number_format($item['preco'], 2, ',', '.') . '</td>
                    <td>R$ ' . number_format($subtotal, 2, ',', '.') . '</td>
                </tr>';
        }

        if (!empty($pedido['frete_valor'])) {
            $total += $pedido['frete_valor'];
        }

        $html .= '
            </tbody>
        </table>
        <p style="margin-top: 15px; font-size: 16px;"><strong>Total:</strong> R$ ' . number_format($total, 2, ',', '.') . '</p>';

        // Geração do PDF
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $pdf = new Dompdf($options);
        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        // Caminho final do arquivo
        $pasta = dirname(__DIR__, 2) . '/public/uploads/pedidos';
        $arquivo = $pasta . '/pedido_' . $pedido['numero'] . '.pdf';

        // Cria o diretório, se não existir
        if (!is_dir($pasta)) {
            mkdir($pasta, 0775, true);
        }

        // Salva o PDF
        file_put_contents($arquivo, $pdf->output());

        // Retorna caminho público
        return '/uploads/pedidos/pedido_' . $pedido['numero'] . '.pdf';
    }
}
