<?php

session_start();

require 'vendor/autoload.php';

$app = new Slim\App([
    'settings' => [
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => true,
        'db' => [
            'driver' => 'mysql',
            'host' => 'db.oassurvey.com',
            'database' => 'oassurve_mysqoasdb',
            'username' => 'oassurve_dangre',
            'password' => 'P84*((!43GbVvk)^',
//            'host' => 'localhost',
//            'database' => 'ofpartner',
//            'username' => 'root',
//            'password' => '123',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]
    ],
]);

$container = $app->getContainer();

unset($app->getContainer()['errorHandler']);
unset($app->getContainer()['phpErrorHandler']);

$app->add(new \Slim\Middleware\Session([
    'name' => 'oas',
    'autorefresh' => true,
    'lifetime' => '1 hour',
]));

include 'dependencies.php';

// fire off the eloquent capsule so models work
$container->get('db');

include './routes/routes.php';

$app->run();