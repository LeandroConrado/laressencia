<?php
// Display all errors directly in browser (for testing only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Basic environment information
echo "<h1>Checkout Diagnostics</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Current directory: " . __DIR__ . "</p>";

// Test autoloading
echo "<h2>Testing Autoloader</h2>";
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "<p style='color: green;'>✓ Autoloader loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Autoloader error: " . $e->getMessage() . "</p>";
    die("Cannot proceed without autoloader");
}

// Test environment loading
echo "<h2>Testing Environment Configuration</h2>";
try {
    // Try loading Dotenv directly
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    echo "<p style='color: green;'>✓ Environment file loaded successfully</p>";
    
    // Check DB variables
    if (!empty($_ENV['DB_HOST']) && !empty($_ENV['DB_NAME'])) {
        echo "<p style='color: green;'>✓ Database environment variables found</p>";
        echo "<p>Host: " . $_ENV['DB_HOST'] . "</p>";
        echo "<p>Database: " . $_ENV['DB_NAME'] . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Database environment variables not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Environment loading error: " . $e->getMessage() . "</p>";
}

// Test Database connection directly
echo "<h2>Testing Database Connection</h2>";
try {
    // Try connecting using PDO directly
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'laressencia';
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASS'] ?? '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ Direct PDO connection successful</p>";
    
    // Test basic query
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>Tables in database:</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Check specifically for pedidos and itens_pedido tables
    $requiredTables = ['pedidos', 'itens_pedido', 'clientes'];
    $missingTables = array_diff($requiredTables, $tables);
    
    if (empty($missingTables)) {
        echo "<p style='color: green;'>✓ All required tables exist</p>";
    } else {
        echo "<p style='color: red;'>✗ Missing required tables: " . implode(', ', $missingTables) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection error: " . $e->getMessage() . "</p>";
}

// Test Checkout model directly
echo "<h2>Testing Checkout Model</h2>";
try {
    // Try to instantiate the Checkout class
    $checkoutClass = class_exists('App\\Models\\Site\\Checkout');
    echo $checkoutClass 
        ? "<p style='color: green;'>✓ Checkout class exists</p>" 
        : "<p style='color: red;'>✗ Checkout class not found</p>";

    if ($checkoutClass) {
        $checkout = new App\Models\Site\Checkout();
        echo "<p style='color: green;'>✓ Checkout model instantiated successfully</p>";
        
        // Check if the criarPedido method exists
        $method = method_exists($checkout, 'criarPedido');
        echo $method 
            ? "<p style='color: green;'>✓ criarPedido method exists</p>" 
            : "<p style='color: red;'>✗ criarPedido method not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Checkout model error: " . $e->getMessage() . "</p>";
}

// Test Controller directly
echo "<h2>Testing CheckoutController</h2>";
try {
    // Try to instantiate the CheckoutController class
    $controllerClass = class_exists('App\\Http\\Controllers\\Site\\CheckoutController');
    echo $controllerClass 
        ? "<p style='color: green;'>✓ CheckoutController class exists</p>" 
        : "<p style='color: red;'>✗ CheckoutController class not found</p>";

    if ($controllerClass) {
        $controller = new App\Http\Controllers\Site\CheckoutController();
        echo "<p style='color: green;'>✓ CheckoutController instantiated successfully</p>";
        
        // Check if the finalizarPedido method exists
        $method = method_exists($controller, 'finalizarPedido');
        echo $method 
            ? "<p style='color: green;'>✓ finalizarPedido method exists</p>" 
            : "<p style='color: red;'>✗ finalizarPedido method not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ CheckoutController error: " . $e->getMessage() . "</p>";
}