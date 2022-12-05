<?php

use Kykurniawan\Hmm\Hmm;
use Kykurniawan\Hmm\Request;
use Kykurniawan\Hmm\Response;

require_once '../vendor/autoload.php';

$app = new Hmm([
    'base_url' => 'http://127.0.0.1/hmm/public/',
    'view_path' => '../views',
]);

$app->get('/', function (Request $request, Response $response) {
    return $response->view('index');
});

$app->run();
