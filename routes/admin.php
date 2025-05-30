<?php

use App\Core\Router;


Router::get('/admin/login', [App\Http\Controllers\Admin\AuthController::class, 'login']);
Router::post('/admin/login/autenticar', [App\Http\Controllers\Admin\AuthController::class, 'autenticar']);
Router::get('/admin/logout', [App\Http\Controllers\Admin\AuthController::class, 'logout']);

Router::group(['prefix' => '/admin', 'middleware' => 'auth'], function () {
    Router::get('/', [App\Http\Controllers\Admin\RedirectController::class, 'index']);

    Router::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index']);

    Router::get('/produto/listar', [App\Http\Controllers\Admin\ProdutoController::class, 'listar'], ['role' => 'admin', 'module' => 'produtos']);
    Router::get('/produto/novo', [App\Http\Controllers\Admin\ProdutoController::class, 'novo'], ['role' => 'admin', 'module' => 'produtos']);
    Router::post('/produto/salvar', [App\Http\Controllers\Admin\ProdutoController::class, 'salvar'], ['role' => 'admin', 'module' => 'produtos']);
    Router::get('/produto/editar/{id}', [App\Http\Controllers\Admin\ProdutoController::class, 'editar'], ['role' => 'admin', 'module' => 'produtos']);
    Router::post('/produto/atualizar/{id}', [App\Http\Controllers\Admin\ProdutoController::class, 'atualizar'], ['role' => 'admin', 'module' => 'produtos']);
    Router::get('/produto/excluir/{id}', [App\Http\Controllers\Admin\ProdutoController::class, 'excluir'], ['role' => 'admin', 'module' => 'produtos']);

    Router::get('/publicidade/listar', [App\Http\Controllers\Admin\PublicidadeController::class, 'listar'], ['role' => 'editor', 'module' => 'publicidade']);
    Router::get('/publicidade/novo', [App\Http\Controllers\Admin\PublicidadeController::class, 'novo'], ['role' => 'editor', 'module' => 'publicidade']);
    Router::post('/publicidade/salvar', [App\Http\Controllers\Admin\PublicidadeController::class, 'salvar'], ['role' => 'editor', 'module' => 'publicidade']);
    Router::get('/publicidade/editar', [App\Http\Controllers\Admin\PublicidadeController::class, 'editar'], ['role' => 'editor', 'module' => 'publicidade']);
    Router::post('/publicidade/atualizar', [App\Http\Controllers\Admin\PublicidadeController::class, 'atualizar'], ['role' => 'editor', 'module' => 'publicidade']);
    Router::get('/publicidade/excluir', [App\Http\Controllers\Admin\PublicidadeController::class, 'excluir'], ['role' => 'editor', 'module' => 'publicidade']);

    Router::get('/categorias/listar', [App\Http\Controllers\Admin\CategoriaController::class, 'listar'], ['role' => 'admin', 'module' => 'categorias']);
    Router::get('/categorias/novo', [App\Http\Controllers\Admin\CategoriaController::class, 'novo'], ['role' => 'admin', 'module' => 'categorias']);
    Router::post('/categorias/salvar', [App\Http\Controllers\Admin\CategoriaController::class, 'salvar'], ['role' => 'admin', 'module' => 'categorias']);
    Router::get('/categorias/editar', [App\Http\Controllers\Admin\CategoriaController::class, 'editar'], ['role' => 'admin', 'module' => 'categorias']);
    Router::post('/categorias/atualizar', [App\Http\Controllers\Admin\CategoriaController::class, 'atualizar'], ['role' => 'admin', 'module' => 'categorias']);
    Router::get('/categorias/excluir', [App\Http\Controllers\Admin\CategoriaController::class, 'excluir'], ['role' => 'admin', 'module' => 'categorias']);

    Router::get('/usuarios/listar', [App\Http\Controllers\Admin\UsuarioController::class, 'listar'], ['role' => 'superadmin', 'module' => 'usuarios']);
    Router::get('/usuarios/novo', [App\Http\Controllers\Admin\UsuarioController::class, 'novo'], ['role' => 'superadmin', 'module' => 'usuarios']);
    Router::post('/usuarios/salvar', [App\Http\Controllers\Admin\UsuarioController::class, 'salvar'], ['role' => 'superadmin', 'module' => 'usuarios']);
    Router::get('/usuarios/editar', [App\Http\Controllers\Admin\UsuarioController::class, 'editar'], ['role' => 'superadmin', 'module' => 'usuarios']);
    Router::post('/usuarios/atualizar', [App\Http\Controllers\Admin\UsuarioController::class, 'atualizar'], ['role' => 'superadmin', 'module' => 'usuarios']);
    Router::get('/usuarios/excluir', [App\Http\Controllers\Admin\UsuarioController::class, 'excluir'], ['role' => 'superadmin', 'module' => 'usuarios']);

    Router::get('/clientes/listar', [App\Http\Controllers\Admin\ClienteController::class, 'listar'], ['role' => 'admin', 'module' => 'clientes']);
    Router::get('/clientes/novo', [App\Http\Controllers\Admin\ClienteController::class, 'novo'], ['role' => 'admin', 'module' => 'clientes']);
    Router::post('/clientes/salvar', [App\Http\Controllers\Admin\ClienteController::class, 'salvar'], ['role' => 'admin', 'module' => 'clientes']);
    Router::get('/clientes/editar', [App\Http\Controllers\Admin\ClienteController::class, 'editar'], ['role' => 'admin', 'module' => 'clientes']);
    Router::post('/clientes/atualizar', [App\Http\Controllers\Admin\ClienteController::class, 'atualizar'], ['role' => 'admin', 'module' => 'clientes']);
    Router::get('/clientes/excluir', [App\Http\Controllers\Admin\ClienteController::class, 'excluir'], ['role' => 'admin', 'module' => 'clientes']);

    Router::get('/pedidos/listar', [App\Http\Controllers\Admin\PedidoController::class, 'listar'], ['role' => 'admin', 'module' => 'pedidos']);
    Router::get('/pedidos/novo', [App\Http\Controllers\Admin\PedidoController::class, 'novo'], ['role' => 'admin', 'module' => 'pedidos']);
    Router::post('/pedidos/salvar', [App\Http\Controllers\Admin\PedidoController::class, 'salvar'], ['role' => 'admin', 'module' => 'pedidos']);
    Router::get('/pedidos/visualizar', [App\Http\Controllers\Admin\PedidoController::class, 'visualizar'], ['role' => 'admin', 'module' => 'pedidos']);
    Router::post('/pedidos/atualizar-status', [App\Http\Controllers\Admin\PedidoController::class, 'atualizarStatus'], ['role' => 'admin', 'module' => 'pedidos']);
    Router::get('/pedidos/imprimir', [App\Http\Controllers\Admin\PedidoController::class, 'imprimir'], ['role' => 'admin', 'module' => 'pedidos']);

    Router::get('/configuracoes/editar', [App\Http\Controllers\Admin\ConfiguracaoController::class, 'editar'], ['role' => 'superadmin', 'module' => 'configuracoes']);
    Router::post('/configuracoes/salvar', [App\Http\Controllers\Admin\ConfiguracaoController::class, 'salvar'], ['role' => 'superadmin', 'module' => 'configuracoes']);

    Router::get('/banners/listar', [App\Http\Controllers\Admin\BannerController::class, 'listar'], ['role' => 'editor', 'module' => 'banners']);
    Router::get('/banners/novo', [App\Http\Controllers\Admin\BannerController::class, 'novo'], ['role' => 'editor', 'module' => 'banners']);
    Router::post('/banners/salvar', [App\Http\Controllers\Admin\BannerController::class, 'salvar'], ['role' => 'editor', 'module' => 'banners']);
    Router::get('/banners/editar', [App\Http\Controllers\Admin\BannerController::class, 'editar'], ['role' => 'editor', 'module' => 'banners']);
    Router::post('/banners/atualizar', [App\Http\Controllers\Admin\BannerController::class, 'atualizar'], ['role' => 'editor', 'module' => 'banners']);
    Router::get('/banners/excluir', [App\Http\Controllers\Admin\BannerController::class, 'excluir'], ['role' => 'editor', 'module' => 'banners']);

});