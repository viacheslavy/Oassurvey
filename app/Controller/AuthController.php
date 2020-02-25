<?php

namespace App\Controller;

use App\Helper\PseudoCrypt;
use App\Model\User;
use App\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class AuthController extends BaseController
{

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function login(Request $request, Response $response, $args)
    {
        $username = $request->getParam('username');
        $password = $request->getParam('password');

        if ($username) {
            if ($user = User::byUsernameAndPassword($username, $password)->first()) {
                $hashAccountID = PseudoCrypt::hash($user->account_id, 8);
                $this->session->set('oasAcctID', $hashAccountID);

                // user technically logged in
                $user->last_login = new \Carbon\Carbon();
                $user->save();

                // check if we're an admin or a user given permission for a specific survey
                if (!$user->isAdmin() && ($permission = $user->hasPermission(\App\Model\Permission::CAN_VIEW_SURVEY))) {
                    // redirect directly to the survey page
                    // if we're not an admin, redirect to first permissioned tab
                    foreach ($this->router->getRoutes() as $route) {
                        // make sure route is part of /admin and /survey groups
                        $groupPatterns = array_map(function($group) { return $group->getPattern(); }, $route->getGroups());
                        if (in_array('/admin', $groupPatterns) && in_array('/survey/{surveyId}', $groupPatterns)) {
                            // this is a survey route
                            // whichever route we have permission for first, send us there
                            if ($permission = $user->hasPermission($route->getName()))
                                return $response->withRedirect($this->router->pathFor($route->getName(), ['surveyId' => $permission->value]));
                        }
                    }

//                return $response->withRedirect($this->get('router')->pathFor('surveyHome', ['surveyId' => $permission->value]));
                } else {
                    // make sure we're an admin
                    if ($user->isAdmin())
                        return $response->withRedirect($this->router->pathFor('account'));
                }
            }

            $this->flash->addMessage('login_failed', true);
            return $response->withRedirect($this->router->pathFor('login'));
        }

        return $this->view->render($response, 'views/login.html.twig');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function logout(Request $request, Response $response, $args)
    {
        $this->session->delete('oasAcctID');
        $this->flash->addMessage('logged_out', true);
        return $response->withRedirect($this->router->pathFor('login'));
    }
}
