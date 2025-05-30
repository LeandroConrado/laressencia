<?php
// Display all errors (for testing only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Start session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Load environment variables
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Test PDO connection
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'laressencia';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? '';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create a test order directly with PDO
        $pdo->beginTransaction();
        
        // Insert a test client if email doesn't exist
        $email = $_POST['email'] ?? 'test@example.com';
        
        $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email = ?");
        $stmt->execute([$email]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente) {
            $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, telefone, cpf, cep, rua, numero_endereco, bairro, cidade, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['nome'] ?? 'Test User',
                $email,
                $_POST['telefone'] ?? '(00) 00000-0000',
                $_POST['cpf'] ?? '000.000.000-00',
                $_POST['cep'] ?? '00000-000',
                $_POST['rua'] ?? 'Test Street',
                $_POST['numero'] ?? '123',
                $_POST['bairro'] ?? 'Test District',
                $_POST['cidade'] ?? 'Test City',
                $_POST['estado'] ?? 'TS'
            ]);
            
            $clienteId = $pdo->lastInsertId();
        } else {
            $clienteId = $cliente['id'];
        }
        
        // Insert test order
        $stmt = $pdo->prepare("INSERT INTO pedidos (cliente_id, forma_pagamento, frete_selecionado, valor_subtotal, valor_frete, valor_total, status, data_criacao) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $clienteId,
            $_POST['forma_pagamento'] ?? 'Pix',
            $_POST['frete_selecionado'] ?? 'PAC',
            100.00, // Sample subtotal
            15.00,  // Sample shipping
            115.00, // Sample total
            'pendente'
        ]);
        
        $pedidoId = $pdo->lastInsertId();
        
        // Insert test order item
        $stmt = $pdo->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, valor_unitario, valor_total) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $pedidoId,
            1, // Sample product ID
            1, // Sample quantity
            100.00, // Sample unit price
            100.00 // Sample total
        ]);
        
        $pdo->commit();
        
        // Success message
        echo "<div style='color: green; padding: 20px; background: #e8f5e9; margin: 20px 0; border-radius: 5px;'>
                <h2>Pedido criado com sucesso!</h2>
                <p>ID do Pedido: $pedidoId</p>
                <p>Cliente ID: $clienteId</p>
              </div>";
        
    } catch (Exception $e) {
        // Rollback transaction
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // Display error
        echo "<div style='color: red; padding: 20px; background: #ffebee; margin: 20px 0; border-radius: 5px;'>
                <h2>Erro ao criar pedido</h2>
                <p>Mensagem: " . $e->getMessage() . "</p>
                <p>Arquivo: " . $e->getFile() . " na linha " . $e->getLine() . "</p>
              </div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout Simplificado</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer; }
        .error { color: red; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Checkout Simplificado</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <form method="post" action="">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>
        </div>
        
        <div class="form-group">
            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone" required>
        </div>
        
        <div class="form-group">
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" required>
        </div>
        
        <div class="form-group">
            <label for="cep">CEP:</label>
            <input type="text" id="cep" name="cep" required>
        </div>
        
        <div class="form-group">
            <label for="rua">Rua:</label>
            <input type="text" id="rua" name="rua" required>
        </div>
        
        <div class="form-group">
            <label for="numero">Número:</label>
            <input type="text" id="numero" name="numero" required>
        </div>
        
        <div class="form-group">
            <label for="bairro">Bairro:</label>
            <input type="text" id="bairro" name="bairro" required>
        </div>
        
        <div class="form-group">
            <label for="cidade">Cidade:</label>
            <input type="text" id="cidade" name="cidade" required>
        </div>
        
        <div class="form-group">
            <label for="estado">Estado:</label>
            <input type="text" id="estado" name="estado" required>
        </div>
        
        <div class="form-group">
            <label for="forma_pagamento">Forma de Pagamento:</label>
            <select id="forma_pagamento" name="forma_pagamento" required>
                <option value="Pix">Pix</option>
                <option value="Cartão de Crédito">Cartão de Crédito</option>
                <option value="Boleto">Boleto</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="frete_selecionado">Frete:</label>
            <select id="frete_selecionado" name="frete_selecionado" required>
                <option value="PAC">PAC - R$ 15,00</option>
                <option value="SEDEX">SEDEX - R$ 25,00</option>
            </select>
        </div>
        
        <button type="submit">Finalizar Pedido</button>
    </form>
</body>
</html>