<?php

use App\Helper\PseudoCrypt;
use App\Repository\UserRepository;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;

$app->get('/', function(Request $request, Response $response, $args) {

    return $this->view->render($response, 'views/home.html.twig', [

    ]);
})->setName('home');

$app->map(['GET', 'POST'], '/oas', 'App\Controller\SurveyController:survey')->setName('survey');

$app->map(['GET', 'POST'],'/login', 'App\Controller\AuthController:login')->setName('login');
$app->get('/logout', 'App\Controller\AuthController:logout')->setName('logout');

$app->group('/admin', function() use ($app) {

    $app->map(['GET', 'POST'], '/surveys', 'App\Controller\SurveyController:surveys')->setName('surveys');


    $userValidators = [
        'firstName' => v::noWhitespace()->length(3, 20),
        'lastName' => v::noWhitespace()->length(3, 20),
        'email' => v::email(),
        'username' => v::call(function($username) use ($app) {
            $user = $app->getContainer()->get('auth')->getUser();
            return UserRepository::usernameExists($user->account_id, $username);
        }, v::falseVal()),
        ];
    if (array_key_exists('accountPassword', $_REQUEST) && $_REQUEST['accountPassword'])
        $userValidators['accountPassword'] = v::noWhitespace()->length(4,20)->identical($_REQUEST['passwordRepeat']);

    $app->map(['GET', 'POST'], '/account', function(Request $request, Response $response, $args) {
        $user = $this->get('auth')->getUser();

        if ($request->isPost()) {
            $username = $request->getParam('username');
            $firstName = $request->getParam('firstName');
            $lastName = $request->getParam('lastName');
            $email = $request->getParam('email');
            $password = $request->getParam('accountPassword');

            $user->account_usn = $username;
            $user->account_first_name = $firstName;
            $user->account_last_name = $lastName;
            $user->account_email_address = $email;

            if (!$request->getAttribute('has_errors')) {
                if ($password) {
                    $user->account_pwd = UserRepository::hashPassword($password);
                }

                if ($user->save()) {
                    $this->flash->addMessage('message', 'Success! The changes you made have been saved.');
                    if ($password) {
                        $this->flash->addMessage('message',
                            'These changes also included a password change. Please make note of your new password now.');
                    }
                    return $response->withStatus(302)->withHeader('Location', $this->router->pathFor('account'));
                }
            }

            foreach ($request->getAttribute('errors') as $key => $val) {
                $this->flash->addMessage('error', $val[0]);
            }
        }

        return $this->view->render($response, 'views/account.html.twig', [
            'user' => $user
        ]);
    })->setName('account')->add(new \DavidePastore\Slim\Validation\Validation($userValidators, function($message) {
        $messages = [
            '{{name}} must be identical as {{compareTo}}' => 'Passwords do not match',
            '{{name}} is not considered as "False"' => 'Username is already taken',
        ];
        if (array_key_exists($message, $messages))
            return $messages[$message];
        return $message;
    }));


    // ==================================== SURVEY =====================================================================
    $app->group('/survey/{surveyId}', function() use ($app) {

        $app->map(['GET', 'POST'], '/home',                       'App\Controller\SurveyController:home')->setName('surveyHome');
        $app->map(['GET', 'POST'], '/users[/{userId}]',           'App\Controller\SurveyController:users')->setName('surveyUsers');
        $app->map(['GET', 'POST'], '/content',                    'App\Controller\SurveyController:content')->setName('surveyContent');
        $app->map(['GET'],         '/content/detailed',           'App\Controller\SurveyController:contentDetailed')->setName('surveyContentDetailed');
        $app->map(['GET', 'POST'], '/settings',                   'App\Controller\SurveyController:settings')->setName('surveySettings');
        $app->map(['GET', 'POST'], '/respondents',                'App\Controller\RespondentsController:get')->setName('surveyRespondents');
        $app->map(['GET'],         '/respondents/generate',       'App\Controller\RespondentsController:generateAccessCode')->setName('surveyRespondentsGenerateAccessCode');
        $app->map(['POST'],        '/respondents/upload',         'App\Controller\RespondentsController:upload')->setName('surveyRespondentsUpload');
        $app->map(['GET'],         '/respondents/csv',            'App\Controller\CSVController:download')->setName('surveyRespondentsCSV');
        $app->map(['POST'],        '/respondents/reset',          'App\Controller\RespondentsController:resetAll')->setName('surveyRespondentsResetAll');
        $app->map(['POST'],        '/respondents/delete',         'App\Controller\RespondentsController:deleteAll')->setName('surveyRespondentsDeleteAll');
        $app->map(['GET', 'POST'], '/reports',                    'App\Controller\SurveyController:reports')->setName('surveyReports');
        $app->map(['GET'],         '/profile',                    'App\Controller\SurveyController:profile')->setName('surveyProfile');
        $app->map(['GET', 'POST'], '/report',                     'App\Controller\SurveyController:report')->setName('surveyReport');
        $app->map(['GET'],         '/crosstab',                   'App\Controller\SurveyController:crosstab')->setName('surveyCrosstab');
        $app->map(['GET'],         '/crosstab/excel',             'App\Controller\SurveyController:crosstab')->setName('surveyCrosstabExcel');
        $app->map(['GET', 'POST'], '/individual',                 'App\Controller\SurveyController:individual')->setName('surveyIndividual');
        $app->map(['GET', 'POST'], '/page/{pageId}',              'App\Controller\SurveyController:page')->setName('surveyPage');
        $app->map(['GET', 'POST'], '/page/{pageId}/question/[{questionId}]','App\Controller\SurveyController:question')->setName('surveyQuestion');

    })->add(new \App\Middleware\CheckSurveyOwnershipMiddleware($app->getContainer()));




})->add(new App\Middleware\AuthenticationMiddleware($app->getContainer()));