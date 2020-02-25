<?php

$app->getContainer()['view'] = function($c) {

    $view = new \Slim\Views\Twig('templates', [
        'debug' => true,
    //        'cache' => 'cache'
    ]);
    $view->addExtension(new Twig_Extension_Debug());
    $view->addExtension(new \Knlv\Slim\Views\TwigMessages(new \Slim\Flash\Messages()));
    $view->addExtension(new \App\Helper\Authentication($c));
    $view->getEnvironment()->addGlobal('current_uri', $c->get('request')->getUri());
    $view->getEnvironment()->addFilter(new Twig_SimpleFilter('sumProperty', function($array, $property) {
        if (!$array)
            return [];

        return array_reduce($array, function($carry, $el) use ($property) {
            return $carry + $el[$property];
        }, 0);
    }));
    $view->getEnvironment()->addFilter(new Twig_SimpleFilter('wherePropertyEquals', function($array, $property, $value) {
        return array_values(array_filter($array, function($el) use ($property, $value) { return $el[$property] == $value; }));
    }));
    $view->getEnvironment()->addGlobal('request', $c->get('request'));
    $view->getEnvironment()->addGlobal('crosstab', $c->get('crosstab'));
    $view->getEnvironment()->addGlobal('account', $c->get('account'));
//    $view->getEnvironment()->addGlobal('auth', $c->get('auth'));
    $view->getEnvironment()->addFilter(new Twig_SimpleFilter('http_build_query', 'http_build_query'));
    $view->getEnvironment()->addFilter(new Twig_SimpleFilter('urlencode', 'urlencode'));
    $view->getEnvironment()->addFilter(new Twig_SimpleFilter('array_column', 'array_column'));
    $view->getEnvironment()->addFilter(new Twig_SimpleFilter('array_sum', 'array_sum'));


    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));

    return $view;
};

$app->getContainer()['db'] = function($container) {

    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container['settings']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$app->getContainer()['session'] = function($container) {
    return new \SlimSession\Helper;
};

$app->getContainer()['auth'] = function($container) {
    return new \App\Helper\Authentication($container);
};

$app->getContainer()['flash'] = function() {
    return new \Slim\Flash\Messages();
};

$app->getContainer()['SurveyRepository'] = function($c) {
    return new \App\Repository\SurveyRepository($c);
};

$app->getContainer()['account'] = function($c) {
    return new \App\Classes\Account();
};

$app->getContainer()['report'] = function($c) {
    return new \App\Classes\Report();
};

$app->getContainer()['crosstab'] = function($c) {
    return new \App\Classes\Crosstab();
};

$app->getContainer()['takesurvey'] = function($c) {
    return new \App\Classes\Takesurvey();
};