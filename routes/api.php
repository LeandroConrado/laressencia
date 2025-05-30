<?php

use App\Http\Controllers\Api\FreteController;
use App\Core\Router;

Router::get('/api/frete/{produtoId}/{quantidade}/{cep}', [FreteController::class, 'calcular']);


