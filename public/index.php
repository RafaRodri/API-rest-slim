<?php
// Rotas e objetos que lidam com pedidos e objetos de resposta
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//autoload é criado pelo composer e nos permite buscar cada item de dependencia
require '../vendor/autoload.php';
require '../src/config/db.php';

// criar instancia slim (objeto principal)
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);


// customizar Rotas
require '../src/routes/customers.php';

// iniciar instancia slim
$app->run();