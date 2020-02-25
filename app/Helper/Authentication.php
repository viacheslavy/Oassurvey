<?php

namespace App\Helper;

use App\Model\User;
use Slim\Container;
use Slim\Views\TwigExtension;
use SlimSession\Helper;

class Authentication extends TwigExtension {

    /** @var Container */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'auth';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('getUser', [$this, 'getUser']),
        ];
    }

    function getAccountID() {
        /** @var Helper $session */
        $session = $this->container->get('session');
        $accountID = $session->get('oasAcctID');
        return PseudoCrypt::unhash($accountID);
    }

    function getUser() {
        $accountId = $this->getAccountID();
        return User::find($accountId);
    }

}