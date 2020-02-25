<?php

namespace App\Helper;

use App\Model\User;
use Slim\Container;
use Slim\Views\TwigExtension;
use SlimSession\Helper;

class Route extends TwigExtension {

    /** @var Container */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'router';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('routeName', [$this, 'routeName']),
        ];
    }

    function routeName() {
        dd($this->container->get('request')->getUri());
        return $this->container->get('request')->getUri();
    }

}