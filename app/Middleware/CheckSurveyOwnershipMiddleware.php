<?php

namespace App\Middleware;

use App\Helper\PseudoCrypt;
use App\Model\Survey;
use App\Model\User;
use Slim\Container;
use Slim\Http\Response;
use SlimSession\Helper;

class CheckSurveyOwnershipMiddleware
{
    /** @var Container */
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param Response $response
     * @param callable $next
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        /** @var User $user */
        $user = $this->container->get('auth')->getUser();
        $surveyId = $request->getAttribute('route')->getArgument('surveyId');

        $survey = Survey::where(['account_id' => $user->account_id, 'survey_id' => $surveyId])->first();

        // check if we have permission for this survey
        if (!$survey && !$user->canViewSurvey($surveyId))
            return $response->withRedirect($this->container->get('router')->pathFor('login'));

        // if not admin, check for permission for specific tab
        $route = $request->getAttribute('route');
        if (!$user->isAdmin() && !$user->hasPermissionWithValue($route->getName(), $surveyId))
            return $response->withRedirect($this->container->get('router')->pathFor('login'));

        // we have permission so if we're not owner then grab it
        $survey = Survey::where('survey_id', '=', $surveyId)->first();

        // Attach the 'survey' to args
        $request = $request->withAttribute('survey', $survey);

        return $next($request, $response);
    }

}