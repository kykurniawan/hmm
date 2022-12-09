<?php

use Kykurniawan\Hmm\Controller;
use Kykurniawan\Hmm\Hmm;
use Kykurniawan\Hmm\Response;

require_once '../vendor/autoload.php';

// Example controller class
class ExampleController extends Controller
{
    public function example(Response $response)
    {
        return $response->view('example');
    }
}

// Example invokable controller class
class ExampleInvokableController extends Controller
{
    public function __invoke(Response $response)
    {
        return $response->view('example');
    }
}

// Create Hmm instance
$hmm = new Hmm([
    Hmm::CONF_BASE_URL => 'http://127.0.0.1/hmm/example/',
    Hmm::CONF_VIEW_PATH => '../views',
    Hmm::CONF_PUBLIC_PATH => './',
]);

// With closure
$hmm->get('closure', function (Response $response) {
    return $response->view('example');
});

// With controller method
$hmm->get('controller-method', [ExampleController::class, 'example']);

// With invokable controller
$hmm->get('invokable-controller', ExampleInvokableController::class);

// Redirecting
$hmm->get('redirect', function (Response $response) use ($hmm) {
    return $response->redirect('redirect/result');
});

$hmm->get('redirect/result', function () {
    return 'You are redirected here';
});

// Run the magic
$hmm->run();
