<?php

namespace App\Models\Site;

use App\Models\Admin\Configuracao;

class Frete
{
    private string $token;
    private string $cepOrigem;

    public function __construct()
    {
        $config = (new Configuracao())->buscar();
        $this->token = $config['melhor_envio_token'] ?? '';
        $this->cepOrigem = $config['cep_origem'] ?? '';
    }

    public function calcularViaMelhorEnvio(string $cepDestino, float $peso, float $comprimento, float $largura, float $altura): array
    {
        $payload = json_encode([
            'from' => ['postal_code' => $this->cepOrigem],
            'to' => ['postal_code' => $cepDestino],
            'package' => [
                'weight' => $peso,
                'width' => $largura,
                'height' => $altura,
                'length' => $comprimento
            ],
            'services' => ['1', '2'], // Exemplo: 1 = PAC, 2 = SEDEX
        ]);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://www.melhorenvio.com.br/api/v2/me/shipping/calculate",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer {$this->token}"
            ],
        ]);

        $response = curl_exec($curl);
        $erro = curl_error($curl);
        curl_close($curl);

        if ($erro) {
            return [['servico' => 'Erro', 'valor' => 0, 'prazo' => $erro]];
        }

        $resultados = json_decode($response, true);

        $fretes = [];
        foreach ($resultados as $opcao) {
            $fretes[] = [
                'servico' => $opcao['name'],
                'valor' => floatval($opcao['price']),
                'prazo' => $opcao['delivery_time'] . ' dias úteis'
            ];
        }

        return $fretes;
    }

    public function selecionar(array $opcao): array
    {
        return [
            'frete_tipo'  => $opcao['servico'],
            'frete_valor' => $opcao['valor'],
            'prazo'       => $opcao['prazo'],
        ];
    }

    public function getCepOrigem(): string
    {
        return $this->cepOrigem;
    }
}
