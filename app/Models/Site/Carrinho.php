<?php

namespace App\Models\Site;

class Carrinho
{
    public function listarProdutos(): array
    {
        return $_SESSION['carrinho'] ?? [];
    }

    public function calcularTotal(): float
    {
        $produtos = $this->listarProdutos();
        $total = 0;

        foreach ($produtos as $produto) {
            $total += $produto['subtotal'] ?? 0;
        }

        return $total;
    }

    public function adicionar(array $produto): void
    {
        $id = $produto['id'];
        $quantidade = $produto['quantidade'] ?? 1;

        if (!isset($_SESSION['carrinho'][$id])) {
            $_SESSION['carrinho'][$id] = [
                'id' => $id,
                'nome' => $produto['nome'] ?? 'Produto',
                'preco' => $produto['preco'] ?? 0,
                'quantidade' => $quantidade,
                'imagem' => $produto['imagem'] ?? '',
                'subtotal' => ($produto['preco'] ?? 0) * $quantidade
            ];
        } else {
            $_SESSION['carrinho'][$id]['quantidade'] += $quantidade;
            $_SESSION['carrinho'][$id]['subtotal'] =
                $_SESSION['carrinho'][$id]['preco'] * $_SESSION['carrinho'][$id]['quantidade'];
        }
    }

    public function remover(int $id): void
    {
        unset($_SESSION['carrinho'][$id]);
    }

    public function limpar(): void
    {
        unset($_SESSION['carrinho']);
    }

    public function atualizar(array $dados): void
    {
        foreach ($dados['quantidade'] as $id => $qtd) {
            if (isset($_SESSION['carrinho'][$id])) {
                $_SESSION['carrinho'][$id]['quantidade'] = (int)$qtd;
                $_SESSION['carrinho'][$id]['subtotal'] =
                    $_SESSION['carrinho'][$id]['preco'] * (int)$qtd;
            }
        }
    }

    public function calcularFrete(string $cepDestino): array
    {
        $total = $this->calcularTotal();

        $frete = new Frete();
        return $frete->calcularManual($cepDestino, $total);
    }
}
