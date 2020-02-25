<?php

namespace App\Controller;

use App\Classes\Account;
use App\Classes\Crosstab;
use App\Classes\Report;
use App\Classes\Session;
use App\Classes\Takesurvey;
use App\Helper\Authentication;
use App\Model\Respondent;
use App\Model\Settings;
use Psr\Container\ContainerInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Interfaces\RouterInterface;
use Slim\Views\Twig;

class BaseController {

    /** @var  ContainerInterface */
    protected $container;

    /** @var  RouterInterface */
    protected $router;

    /** @var  Twig */
    protected $view;

    /** @var  Account */
    protected $account;

    /** @var  Report */
    protected $report;

    /** @var  Authentication */
    protected $auth;

    /** @var  Messages */
    protected $flash;

    /** @var  Crosstab */
    protected $crosstab;

    /** @var  Takesurvey */
    protected $takesurvey;

    /** @var  Session */
    protected $session;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;

        $this->router = $this->container->get('router');
        $this->view = $this->container->get('view');
        $this->account = $this->container->get('account');
        $this->report = $this->container->get('report');
        $this->auth = $this->container->get('auth');
        $this->flash = $this->container->get('flash');
        $this->crosstab = $this->container->get('crosstab');
        $this->takesurvey = $this->container->get('takesurvey');
        $this->session = $this->container->get('session');
    }
}