<?php

use App\Core\Router;

// Rotas principais do site
Router::post('/enviar-contato', [App\Http\Controllers\Site\ContatoController::class, 'enviar']);
Router::get('/contato', [App\Http\Controllers\Site\ContatoController::class, 'index']);
Router::get('/404', [App\Http\Controllers\Site\SiteController::class, 'pagina404']);
Router::get('/manutencao', [App\Http\Controllers\Site\SiteController::class, 'manutencao']);
Router::get('/termos', [App\Http\Controllers\Site\SiteController::class, 'termos']);
Router::get('/', [App\Http\Controllers\Site\HomeController::class, 'index']);
Router::get('/home', [App\Http\Controllers\Site\HomeController::class, 'index']);
Router::get('/produtos', [App\Http\Controllers\Site\ProdutoController::class, 'listar']);
Router::get('/produto/{id}', [App\Http\Controllers\Site\ProdutoController::class, 'ver']);
Router::get('/categoria', [App\Http\Controllers\Site\CategoriaController::class, 'listar']);
Router::get('/categoria/{id}', [App\Http\Controllers\Site\CategoriaController::class, 'ver']);
Router::get('/confirmacao/{id}', [App\Http\Controllers\Site\CheckoutController::class, 'confirmacao']);

// Rotas do carrinho de compras
Router::post('/adicionar-carrinho/{id}', [App\Http\Controllers\Site\CarrinhoController::class, 'adicionar']);
Router::post('/comprar-agora', [App\Http\Controllers\Site\CarrinhoController::class, 'comprarAgora']);
Router::get('/carrinho', [App\Http\Controllers\Site\CarrinhoController::class, 'visualizar']);
Router::get('/remover-carrinho/{id}', [App\Http\Controllers\Site\CarrinhoController::class, 'remover']);
Router::post('/atualizar-carrinho', [App\Http\Controllers\Site\CarrinhoController::class, 'atualizar']);

// Rotas de checkout e finalização de pedido
Router::get('/checkout', [App\Http\Controllers\Site\CheckoutController::class, 'index']);
Router::get('/validar-cliente', [App\Http\Controllers\Site\CheckoutController::class, 'validarCliente']);
Router::post('/finalizar-pedido', [App\Http\Controllers\Site\CheckoutController::class, 'finalizarPedido']);
Router::get('/baixar-pdf/{numero}', [App\Http\Controllers\Site\CheckoutController::class, 'baixarPdf']);

// Rotas de frete
Router::get('/calcular-frete/{cep}', [App\Http\Controllers\Site\CarrinhoController::class, 'calcularFrete']);
Router::get('/selecionar-frete', [App\Http\Controllers\Site\CarrinhoController::class, 'selecionarFrete']);

// Páginas informativas
Router::get('/sobre', [App\Http\Controllers\Site\SobreController::class, 'index']);
Router::get('/politicas', [App\Http\Controllers\Site\PoliticasController::class, 'index']);
Router::get('/faq', [App\Http\Controllers\Site\FaqController::class, 'index']);