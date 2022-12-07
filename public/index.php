<?php

use Kykurniawan\Hmm\Hmm;
use Kykurniawan\Hmm\Request;
use Kykurniawan\Hmm\Response;

require_once '../vendor/autoload.php';

$hmm = new Hmm([
    Hmm::CONF_BASE_URL => 'http://127.0.0.1/hmm/public/',
    Hmm::CONF_VIEW_PATH => '../views',
    Hmm::CONF_PUBLIC_PATH => './',
]);

$hmm->get('/', function (Request $request, Response $response) {
    return $response->view('index');
});

$hmm->get('redirect', function (Request $request, Response $response) {
    return $response->redirect($request->hmm()->config(Hmm::CONF_BASE_URL));
});

$hmm->run();
