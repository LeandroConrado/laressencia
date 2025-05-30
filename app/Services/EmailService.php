<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    public function enviarResumoPedido(array $pedido, string $caminhoPdf)
    {
        $mail = new PHPMailer(true);

        try {
            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) $_ENV['MAIL_PORT'];

            // Remetente e destinatários
            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress('leocomsistemaweb@gmail.com');
            $mail->addAddress($pedido['email']);

            // Corpo do e-mail
            $mail->isHTML(true);
            $mail->Subject = 'Resumo do Pedido ' . $pedido['numero'];

            $linkPdf = base_url() . ltrim($caminhoPdf, '/');

            $body = "
                🧴 <strong>Resumo do Pedido:</strong><br><br>
                🧾 <strong>Pedido nº:</strong> {$pedido['numero']}<br>
                👩 <strong>Cliente:</strong> {$pedido['nome_cliente']}<br>
                📧 <strong>E-mail:</strong> {$pedido['email']}<br>
                📱 <strong>Telefone:</strong> {$pedido['telefone']}<br>
                📍 <strong>Endereço:</strong> {$pedido['rua']}, {$pedido['numero_endereco']} - {$pedido['bairro']} - {$pedido['cidade']}/{$pedido['estado']} - CEP: {$pedido['cep']}<br><br>
                🛍️ <strong>Itens:</strong><br>";

            foreach ($pedido['itens'] as $item) {
                $body .= "• {$item['nome_produto']} (x{$item['quantidade']}) – R$ " . number_format($item['preco'], 2, ',', '.') . "<br>";
            }

            $body .= "
                <br>
                💰 <strong>Total:</strong> R$ " . number_format($pedido['total'], 2, ',', '.') . "<br>
                💳 <strong>Forma de Pagamento:</strong> {$pedido['forma_pagamento']}<br>";

            if (!empty($pedido['frete_tipo'])) {
                $body .= "🚚 <strong>Tipo de Entrega:</strong> {$pedido['frete_tipo']}<br>";
            }

            $body .= "
                <br>📎 <a href='{$linkPdf}' target='_blank'>Clique aqui para baixar o PDF do Pedido</a><br><br>
                ✨ Obrigado por perfumar seu lar com Laressência! 🧴
            ";

            $mail->Body = $body;

            // Anexo do PDF
            $mail->addAttachment(dirname(__DIR__, 2) . '/public' . $caminhoPdf);

            $mail->send();
        } catch (Exception $e) {
            error_log('Erro ao enviar e-mail: ' . $e->getMessage());
        }
    }

    public function confirmacao(int $id): void
    {
    $pedido = $this->pedido->buscarPorIdComItens($id);

    if (!$pedido) {
        http_response_code(404);
        echo 'Pedido não encontrado.';
        return;
    }

    echo $this->twig->render('site/themes/default/confirmacao.twig', [
        'pedido' => $pedido
    ]);
}

}
