<?php

namespace App\Controller;

use App\Classes\Account;
use App\Classes\Crosstab;
use App\Classes\Report;
use App\Helper\Authentication;
use App\Model\Respondent;
use App\Model\Settings;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Interfaces\RouterInterface;
use Slim\Views\Twig;

class DefaultController extends BaseController {
    /**
     * Survey
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function settings(Request $request, Response $response, $args) {
        $survey = $request->getAttribute('survey');
        $settings = Settings::all()->where('survey_id', '=', $survey['survey_id'])->first();

        if ($request->isPost()) {
            $settings->show_splash_page = substr($request->getParam('chkShowSplashPage'), 0, 1);
            $settings->splash_page = trim($request->getParam('txtSplashPage'));
            $settings->logo_splash = trim($request->getParam('txtLogoSplash'));
            $settings->logo_survey = trim($request->getParam('txtLogoSurvey'));
            $settings->contact_email = trim($request->getParam('txtContactEmail'));
            $settings->contact_phone = trim($request->getParam('txtContactPhone'));
            $settings->show_progress_bar = substr($request->getParam('chkShowProgressBar'), 0, 1);
            $settings->begin_page = trim($request->getParam('txtBeginPage'));
            $settings->show_summary = substr($request->getParam('chkShowSummary'), 0, 1);
            $settings->end_page = trim($request->getParam('txtEndPage'));
            $settings->footer = trim($request->getParam('txtFooter'));
            $settings->weekly_hours_text = trim($request->getParam('txtWeeklyHoursText'));
            $settings->annual_legal_hours_text = trim($request->getParam('txtAnnualLegalHoursText')) ;

            $settings->save();

            $routeName = $request->getAttribute('route')->getName();
            return $response->withRedirect($this->router->pathFor($routeName, ['surveyId' => $survey['survey_id']]));
        }

        return $this->view->render($response, 'views/survey/settings.html.twig', [
            'survey' => $survey,
            'settings' => $settings,
        ]);
    }



}