<?php

namespace App\Http\Controllers\Site;

use App\Core\Container;
use App\Models\Admin\Configuracao;
use App\Models\Site\Carrinho;
use App\Models\Site\Checkout;
use App\Models\Site\Cliente;

class CheckoutController
{
    private $twig;

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->twig = Container::makeTwig();
    }

    public function index(): void
    {
        $configModel = new Configuracao();
        $config = $configModel->buscar();

        $carrinho = new Carrinho();
        $produtos = $carrinho->listarProdutos();
        
        // Verificar se o carrinho está vazio
        if (empty($produtos)) {
            $_SESSION['error'] = "Seu carrinho está vazio. Adicione produtos antes de finalizar a compra.";
            header("Location: " . base_url() . "/carrinho");
            exit;
        }
        
        $subtotal = $carrinho->calcularTotal();
        $frete = $_SESSION['frete'] ?? 0;
        $totalGeral = $subtotal + $frete;

        echo $this->twig->render('site/themes/default/checkout/index.twig', [
            'config' => $config,
            'produtos' => $produtos,
            'subtotal' => $subtotal,
            'frete' => $frete,
            'total_geral' => $totalGeral
        ]);
    }

    public function validarCliente(): void
    {
        header('Content-Type: application/json');
        $email = $_GET['email'] ?? '';
        $clienteModel = new Cliente();
        $cliente = $clienteModel->buscarPorEmail($email);
        echo json_encode(['status' => 'ok', 'existe' => $cliente ? true : false]);
    }

 

    public function finalizarPedido(): void
{
    try {
        // Log checkout initiation
        error_log("Checkout initiated - " . date('Y-m-d H:i:s'));
        
        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Invalid request method in checkout");
            $_SESSION['error'] = "Método não permitido";
            header("Location: " . base_url() . "/checkout");
            exit;
        }
        
        // Database connection check
        try {
            $connection = \App\Core\Database::conectar();
        } catch (\PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            $_SESSION['error'] = "Erro no processamento. Por favor, tente novamente.";
            header("Location: " . base_url() . "/checkout");
            exit;
        }
        
        $clienteModel = new Cliente();

        $email = $_POST['email'] ?? '';
        if (empty($email)) {
            error_log("Email not provided");
            $_SESSION['error'] = "O email é obrigatório";
            header("Location: " . base_url() . "/checkout");
            exit;
        }

        error_log("Searching for customer with email: " . $email);
        $cliente = $clienteModel->buscarPorEmail($email);

        if (!$cliente) {
            error_log("Customer not found, validating registration data");
            
            $camposObrigatorios = ['nome', 'cpf', 'telefone', 'cep', 'rua', 'numero', 'bairro', 'cidade', 'estado'];
            foreach ($camposObrigatorios as $campo) {
                if (empty($_POST[$campo])) {
                    error_log("Required field missing: " . $campo);
                    $_SESSION['error'] = "Preencha todos os dados de cadastro";
                    header("Location: " . base_url() . "/checkout");
                    exit;
                }
            }

            $novoCliente = [
                'nome' => $_POST['nome'],
                'email' => $_POST['email'],
                'telefone' => $_POST['telefone'],
                'cpf' => $_POST['cpf'],
                'rua' => $_POST['rua'],
                'numero_endereco' => $_POST['numero'],
                'bairro' => $_POST['bairro'],
                'cidade' => $_POST['cidade'],
                'estado' => $_POST['estado'],
                'cep' => $_POST['cep']
            ];
            
            error_log("Saving new customer");
            $clienteId = $clienteModel->salvar($novoCliente);
            
            if (!$clienteId) {
                error_log("Failed to save customer");
                $_SESSION['error'] = "Erro ao processar dados do cliente";
                header("Location: " . base_url() . "/checkout");
                exit;
            }
            
            error_log("New customer created with ID: " . $clienteId);
        } else {
            $clienteId = $cliente['id'];
            error_log("Existing customer found with ID: " . $clienteId);
        }

        // Verify cart
        $carrinho = new Carrinho();
        $produtos = $carrinho->listarProdutos();
        
        if (empty($produtos)) {
            error_log("Empty cart during checkout");
            $_SESSION['error'] = "Seu carrinho está vazio";
            header("Location: " . base_url() . "/carrinho");
            exit;
        }

        $freteSelecionado = $_POST['frete_selecionado'] ?? '';
        $formaPagamento = $_POST['forma_pagamento'] ?? '';
        
        if (empty($formaPagamento)) {
            error_log("Payment method not selected");
            $_SESSION['error'] = "Selecione uma forma de pagamento";
            header("Location: " . base_url() . "/checkout");
            exit;
        }

        $pedido = [
            'cliente_id' => $clienteId,
            'frete_selecionado' => $freteSelecionado,
            'forma_pagamento' => $formaPagamento,
            'produtos' => $produtos,
            'subtotal' => $carrinho->calcularTotal(),
            'frete' => $_SESSION['frete'] ?? 0,
            'total' => ($carrinho->calcularTotal() + ($_SESSION['frete'] ?? 0))
        ];

        error_log("Creating order with data: " . json_encode($pedido));
        $checkout = new Checkout();
        $pedidoId = $checkout->criarPedido($pedido);
        
        if (!$pedidoId) {
            error_log("Failed to create order");
            $_SESSION['error'] = "Erro ao processar seu pedido";
            header("Location: " . base_url() . "/checkout");
            exit;
        }
        
        error_log("Order created successfully with ID: " . $pedidoId);

        // Clear cart after successful checkout
        $carrinho->limpar();
        unset($_SESSION['frete']);
        
        header("Location: " . base_url() . "/confirmacao/{$pedidoId}");
        exit;
    } catch (\Exception $e) {
        error_log("Exception in checkout process: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        $_SESSION['error'] = "Ocorreu um erro ao finalizar seu pedido. Por favor, tente novamente.";
        header("Location: " . base_url() . "/checkout");
        exit;
    }
}


    public function confirmacao($id): void
    {
        try {
            $checkout = new Checkout();
            $pedido = $checkout->buscarPorId($id);
            
            if (!$pedido) {
                error_log("Pedido não encontrado: $id");
                header("Location: " . base_url() . "/404");
                exit;
            }

            $configModel = new Configuracao();
            $config = $configModel->buscar();

            echo $this->twig->render('site/themes/default/checkout/confirmacao.twig', [
                'pedido' => $pedido,
                'config' => $config
            ]);
        } catch (\Exception $e) {
            error_log("Erro na página de confirmação: " . $e->getMessage());
            $_SESSION['error'] = "Erro ao exibir confirmação do pedido";
            header("Location: " . base_url() . "/");
            exit;
        }
    }

    public function baixarPdf($numero): void
    {
        try {
            echo "PDF gerado para o pedido {$numero}";
        } catch (\Exception $e) {
            error_log("Erro ao gerar PDF: " . $e->getMessage());
            $_SESSION['error'] = "Não foi possível gerar o PDF deste pedido";
            header("Location: " . base_url() . "/");
            exit;
        }
    }
}