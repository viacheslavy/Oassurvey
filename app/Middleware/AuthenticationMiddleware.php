<?php

namespace App\Middleware;

use App\Helper\PseudoCrypt;
use App\Model\User;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use SlimSession\Helper;

class AuthenticationMiddleware
{
    /** @var Container */
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param Request
     * @param Response $response
     * @param callable $next
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $session = new Helper;

        if (!$session->exists('oasAcctID'))
            return $response->withStatus(302)->withHeader('Location', $this->container->get('router')->pathFor('login'));

        $accountID = $session->get('oasAcctID');
        $accountID = PseudoCrypt::unhash($accountID);

        $user = User::where('account_id', '=', $accountID)->first();

        $request = $request->withAttribute('user', $user);
        $this->container->view->getEnvironment()->addGlobal('user', $user);

        $response = $next($request, $response);

        return $response;
    }

}