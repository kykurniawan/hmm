<?php

use Kykurniawan\Hmm\Constants;
use Kykurniawan\Hmm\Helpers\Migration;
use Kykurniawan\Hmm\Hmm;
use Kykurniawan\Hmm\Response;

require_once '../vendor/autoload.php';

// Create Hmm instance
$app = new Hmm([
    Constants::CONF_BASE_URL => 'http://127.0.0.1/hmm/example/',
    Constants::CONF_VIEW_PATH => '../views',
    Constants::CONF_PUBLIC_PATH => './',
    Constants::CONF_MIGRATION_PATH => '../migrations',
]);


$app->get('/migrate', function (Response $response) {
    Migration::migrate();
});

$app->get('/reset', function (Response $response) {
    Migration::reset();
});

$app->run();
