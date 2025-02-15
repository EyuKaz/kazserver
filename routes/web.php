<?php
use MyPHPServer\Server\{Request, Response, Router};

/** @var Router $router */
$router->addRoute('GET', '/', function (Request $request) {
    return (new Response())
        ->setBody(file_get_contents(__DIR__ . '/../public/index.html'));
});

$router->addRoute('GET', '/hello', function (Request $request) {
    $name = $request->getQueryParam('name') ?? 'World';
    return (new Response())
        ->setBody("<h1>Hello, {$name}!</h1>");
});

$router->addRoute('POST', '/submit', function (Request $request) {
    $data = $request->getBodyParam('data') ?? 'No data received';
    return (new Response())
        ->setBody("<h1>Received: {$data}</h1>");
});