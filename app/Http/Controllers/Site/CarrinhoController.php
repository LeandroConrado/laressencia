<?php

namespace App\Http\Controllers\Site;

use App\Core\Container;
use App\Models\Site\Carrinho;
use App\Models\Admin\Configuracao;
use App\Models\Site\Produto;

class CarrinhoController
{
    private $twig;

    public function __construct()
    {
        $this->twig = Container::makeTwig(); // Twig centralizado
    }

    public function visualizar(): void
    {
        $carrinho = new Carrinho();
        $produtos = $carrinho->listarProdutos();
        $total = $carrinho->calcularTotal();
        $config = (new Configuracao())->buscar();

        echo $this->twig->render('site/themes/default/carrinho/visualizar.twig', [
            'produtos' => $produtos,
            'total' => $total,
            'config' => $config
        ]);
    }

    public function adicionar($id): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $quantidade = $_POST['quantidade'] ?? 1;

        $produto = (new Produto())->buscarPorId($id);

        if (!$produto) {
            header("Location: " . rtrim(base_url(), '/') . '/produtos');
            exit;
        }

        $carrinho = new Carrinho();
        $carrinho->adicionar([
            'id' => $id,
            'quantidade' => (int)$quantidade,
            'nome' => $produto['nome'],
            'preco' => $produto['preco'],
            'imagem' => $produto['midia'] ?? ''
        ]);

        header("Location: " . rtrim(base_url(), '/') . '/carrinho');
        exit;
    }

    public function remover($id): void
    {
        $carrinho = new Carrinho();
        $carrinho->remover($id);
        header("Location: " . rtrim(base_url(), '/') . '/carrinho');
        exit;
    }

    public function atualizar(): void
    {
        $carrinho = new Carrinho();
        $carrinho->atualizar($_POST);
        header("Location: " . rtrim(base_url(), '/') . '/carrinho');
        exit;
    }

    public function comprarAgora(): void
    {
        $idProduto = $_POST['id'] ?? null;
        $quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 1;
        $freteSelecionado = $_POST['frete'] ?? null;

        if ($idProduto) {
            $produto = (new Produto())->buscarPorId($idProduto);

            if (!$produto) {
                header("Location: " . rtrim(base_url(), '/') . '/produtos');
                exit;
            }

            $carrinho = new Carrinho();
            $carrinho->limpar();

            $item = [
                'id' => $idProduto,
                'quantidade' => max(1, $quantidade),
                'nome' => $produto['nome'],
                'preco' => $produto['preco'],
                'imagem' => $produto['midia'] ?? '',
                'subtotal' => $produto['preco'] * $quantidade
            ];

            if ($freteSelecionado) {
                list($nome, $valor, $prazo) = explode(':', $freteSelecionado);
                $item['frete_nome'] = $nome;
                $item['frete_valor'] = (float) $valor;
                $item['frete_prazo'] = $prazo;
            }

            $carrinho->adicionar($item);
        }

        header("Location: " . rtrim(base_url(), '/') . '/checkout');
        exit;
    }

    public function calcularFrete($cep): void
    {
        $carrinho = new Carrinho();
        $frete = $carrinho->calcularFrete($cep);
        echo json_encode(['frete' => $frete]);
    }

    public function selecionarFrete(): void
    {
        $freteSelecionado = $_GET['valor'] ?? 0;
        $_SESSION['frete'] = $freteSelecionado;
        echo json_encode(['ok' => true]);
    }
}
