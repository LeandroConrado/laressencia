<?php

namespace App\Http\Controllers\Site;

use App\Models\Site\Checkout;

class DownloadController
{
    public function pedidoPdf($numero): void
    {
        $checkout = new Checkout();
        $pedido = $checkout->buscarPorNumero($numero);

        if (!$pedido) {
            http_response_code(404);
            echo "Pedido não encontrado.";
            return;
        }

        // Geração do PDF (exemplo usando Dompdf ou biblioteca similar)
        $html = "<h1>Resumo do Pedido {$numero}</h1>";
        $html .= "<p>Cliente: {$pedido['cliente_nome']}</p>";
        $html .= "<p>Total: R$ {$pedido['total']}</p>";

        // Exemplo simples: salvar como HTML e forçar download (simulando PDF)
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=pedido_{$numero}.html");
        echo $html;
    }
}
