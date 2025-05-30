<?php

namespace App\Services;

use App\Models\Site\Produto;
use Exception;

class MelhorEnvioService
{
    private string $token;
    private string $url;

    public function __construct()
    {
        $this->token = $_ENV['MELHOR_ENVIO_TOKEN'] ?? '';
        $this->url = $_ENV['MELHOR_ENVIO_URL'] ?? 'https://sandbox.melhorenvio.com.br';
    }

    public function calcularFrete(int $produtoId, int $quantidade, string $cepDestino): array
    {
        $produtoModel = new Produto();
        $produto = $produtoModel->buscarPorId($produtoId);

        if (!$produto) {
            throw new Exception('Produto não informado');
        }

        $peso = (float)$produto['peso'] ?: 1;
        $altura = (float)$produto['altura'] ?: 1;
        $largura = (float)$produto['largura'] ?: 1;
        $comprimento = (float)$produto['comprimento'] ?: 16;
        $seguro = (float)$produto['preco'];

        $query = http_build_query([
            "from" => ["postal_code" => $_ENV['CEP_ORIGEM']],
            "to" => ["postal_code" => $cepDestino],
            "products" => [[
                "weight" => $peso,
                "width" => $largura,
                "height" => $altura,
                "length" => $comprimento,
                "quantity" => $quantidade
            ]],
            "options" => ["insurance_value" => $seguro]
        ]);

        $endpoint = $this->url . '/api/v2/me/shipment/calculate?' . $query;

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error || $httpCode >= 400) {
            throw new Exception("Erro na consulta de frete. Código HTTP: $httpCode. Resposta: $response");
        }

        return json_decode($response, true);
    }

    public function calcularFreteCarrinho(array $itens, string $cepDestino): array
    {
        $produtoModel = new \App\Models\Site\Produto();
        $products = [];

        foreach ($itens as $item) {
            $produto = $produtoModel->buscarPorId($item['id']);
            if (!$produto) continue;

            $products[] = [
                "weight" => (float)$produto['peso'] ?: 1,
                "width" => (float)$produto['largura'] ?: 1,
                "height" => (float)$produto['altura'] ?: 1,
                "length" => (float)$produto['comprimento'] ?: 16,
                "quantity" => (int)$item['quantidade'] ?: 1,
            ];
        }

        if (empty($products)) {
            throw new \Exception("Nenhum produto válido encontrado no carrinho.");
        }

        $query = http_build_query([
            "from" => ["postal_code" => $_ENV['CEP_ORIGEM']],
            "to" => ["postal_code" => $cepDestino],
            "products" => $products,
            "options" => ["insurance_value" => 0]
        ]);

        $endpoint = $this->url . '/api/v2/me/shipment/calculate?' . $query;

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'Accept: application/json'
            ]
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error || $httpCode >= 400) {
            throw new \Exception("Erro na consulta de frete. Código HTTP: $httpCode. Resposta: $response");
        }

        return json_decode($response, true);
    }
}
