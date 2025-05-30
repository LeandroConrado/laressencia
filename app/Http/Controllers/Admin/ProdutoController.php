<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin\Produto;
use App\Models\Admin\Categoria;
use Twig\Environment;

class ProdutoController
{
    private Produto $produtoModel;
    private Categoria $categoriaModel;
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->produtoModel = new Produto();
        $this->categoriaModel = new Categoria();
        $this->twig = $twig;
    }

    public function listar(): void
    {
        $produtos = $this->produtoModel->buscarTodos();
        echo $this->twig->render('admin/produtos/listar.twig', [
            'produtos' => $produtos,
            'session' => $_SESSION
        ]);
        unset($_SESSION['alerta']);
    }

    public function novo(): void
    {
        $categorias = $this->categoriaModel->buscarTodas();
        echo $this->twig->render('admin/produtos/novo.twig', [
            'categorias' => $categorias,
            'session' => $_SESSION
        ]);
        unset($_SESSION['alerta']);
    }

    public function salvar(): void
    {
        $midia = $this->processarMidiaUpload();

        $this->produtoModel->salvar([
            'nome' => $_POST['nome'] ?? '',
            'sku' => $_POST['sku'] ?? '',
            'ean' => $_POST['ean'] ?? '',
            'slug' => $_POST['slug'] ?? '',
            'palavras_chave' => $_POST['palavras_chave'] ?? '',
            'tags' => $_POST['tags'] ?? '',
            'descricao_curta' => $_POST['descricao_curta'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'preco' => $_POST['preco'] ?? 0,
            'preco_promocional' => $_POST['preco_promocional'] ?? 0,
            'custo' => $_POST['custo'] ?? 0,
            'data_inicio_promocao' => $_POST['data_inicio_promocao'] ?? null,
            'data_fim_promocao' => $_POST['data_fim_promocao'] ?? null,
            'quantidade_estoque' => $_POST['quantidade_estoque'] ?? 0,
            'estoque_minimo' => $_POST['estoque_minimo'] ?? 0,
            'localizacao_estoque' => $_POST['localizacao_estoque'] ?? '',
            'altura' => $_POST['altura'] ?? 0,
            'largura' => $_POST['largura'] ?? 0,
            'comprimento' => $_POST['comprimento'] ?? 0,
            'peso' => $_POST['peso'] ?? 0,
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'frete_gratis' => isset($_POST['frete_gratis']) ? 1 : 0,
            'produto_digital' => isset($_POST['produto_digital']) ? 1 : 0,
            'personalizavel' => isset($_POST['personalizavel']) ? 1 : 0,
            'exibir_home' => isset($_POST['exibir_home']) ? 1 : 0,
            'midia' => $midia['nome'],
            'tipo_midia' => $midia['tipo'],
            'categoria_id' => $_POST['categoria_id'] ?? null,
            'destaque' => isset($_POST['destaque']) ? 1 : 0
        ]);

        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => 'Produto cadastrado com sucesso!'
        ];
        header('Location: ' . base_url() . '/admin/produto/listar');
        exit;
    }

    public function editar(int $id): void
    {
        $produto = $this->produtoModel->buscarPorId($id);
        $categorias = $this->categoriaModel->buscarTodas();

        echo $this->twig->render('admin/produtos/editar.twig', [
            'produto' => $produto,
            'categorias' => $categorias,
            'session' => $_SESSION
        ]);

        unset($_SESSION['alerta']);
    }

    public function atualizar(int $id): void
    {
        $midia = $this->processarMidiaUpload(
            $_POST['midia_atual'] ?? '',
            $_POST['tipo_midia_atual'] ?? 'imagem'
        );

        $this->produtoModel->atualizar($id, [
            'nome' => $_POST['nome'],
            'sku' => $_POST['sku'],
            'ean' => $_POST['ean'],
            'slug' => $_POST['slug'],
            'palavras_chave' => $_POST['palavras_chave'],
            'tags' => $_POST['tags'],
            'descricao_curta' => $_POST['descricao_curta'],
            'descricao' => $_POST['descricao'],
            'preco' => $_POST['preco'],
            'preco_promocional' => $_POST['preco_promocional'],
            'custo' => $_POST['custo'],
            'data_inicio_promocao' => $_POST['data_inicio_promocao'],
            'data_fim_promocao' => $_POST['data_fim_promocao'],
            'quantidade_estoque' => $_POST['quantidade_estoque'],
            'estoque_minimo' => $_POST['estoque_minimo'],
            'localizacao_estoque' => $_POST['localizacao_estoque'],
            'altura' => $_POST['altura'],
            'largura' => $_POST['largura'],
            'comprimento' => $_POST['comprimento'],
            'peso' => $_POST['peso'],
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'frete_gratis' => isset($_POST['frete_gratis']) ? 1 : 0,
            'produto_digital' => isset($_POST['produto_digital']) ? 1 : 0,
            'personalizavel' => isset($_POST['personalizavel']) ? 1 : 0,
            'exibir_home' => isset($_POST['exibir_home']) ? 1 : 0,
            'midia' => $midia['nome'],
            'tipo_midia' => $midia['tipo'],
            'categoria_id' => $_POST['categoria_id'],
            'destaque' => isset($_POST['destaque']) ? 1 : 0
        ]);

        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => 'Produto atualizado com sucesso!'
        ];

        header('Location: ' . base_url() . '/admin/produto/listar');
        exit;
    }

    public function excluir(int $id): void
    {
        $produto = $this->produtoModel->buscarPorId($id);

        if (!empty($produto['midia'])) {
            $caminho = $_SERVER['DOCUMENT_ROOT'] . '/laressencia/public/uploads/produtos/' . $produto['midia'];
            if (file_exists($caminho)) {
                unlink($caminho);
            }
        }

        $this->produtoModel->excluir($id);

        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensagem' => 'Produto excluído com sucesso!'
        ];
        header('Location: ' . base_url() . '/admin/produto/listar');
        exit;
    }

    private function processarMidiaUpload(string $atual = '', string $tipoAtual = 'imagem'): array
    {
        if (!empty($_FILES['imagem']['name'])) {
            $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            $nomeArquivo = uniqid('midia_', true) . '.' . $extensao;
            $pasta = $_SERVER['DOCUMENT_ROOT'] . '/laressencia/public/uploads/produtos/';

            if (!is_dir($pasta)) {
                mkdir($pasta, 0777, true);
            }

            if (!is_writable($pasta)) {
                chmod($pasta, 0777);
            }

            $caminho = $pasta . $nomeArquivo;

            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
                return [
                    'nome' => $nomeArquivo,
                    'tipo' => in_array($extensao, ['mp4', 'webm']) ? 'video' : 'imagem'
                ];
            }
        }

        return ['nome' => $atual, 'tipo' => $tipoAtual];
    }
}
