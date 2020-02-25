<?php

namespace App\Controller;

use App\Classes\Account;
use App\Classes\Crosstab;
use App\Classes\Report;
use App\Helper\Authentication;
use App\Helper\PseudoCrypt;
use App\Model\Permission;
use App\Model\Question;
use App\Model\Respondent;
use App\Model\Settings;
use App\Model\Survey;
use App\Model\User;
use App\Repository\UserRepository;
use PHPExcel;
use PHPExcel_Worksheet_Drawing;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Interfaces\RouterInterface;
use Slim\Views\Twig;
use SlimSession\Helper;

require_once('./fct/fctFunctions.php');

class SurveyController extends BaseController {

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function surveys(Request $request, Response $response, $args) {
        $user = $this->auth->getUser();

        $surveys = Survey::all()->where('account_id', '=', $user->account_id);

        if ($request->getParam('btnNewSurvey')) {
            require_once('./fct/fctFunctions.php');
            $surveyType = substr($request->getParam('slctSurveyType'),0,1);
            $surveyIDToCopy = substr($request->getParam('slctSurveyToCopy'),0,12);
            $surveyName = filterText($request->getParam('txtSurveyName'));
            if (strlen($surveyName) == 0) {
                $surveyName = 'New Survey';
            }
            if ($surveyType == 2 && empty($surveyIDToCopy)) {
                return $response->withRedirect($this->router->pathFor('surveys'));
            } else {
                $newSurveyID = $this->account->new_survey($user->account_id, $surveyName);
                if ($surveyType == 1) { // if new survey from scratch, seed settings. If copy it will be inserted in another process
                    $this->account->seed_settings_row($newSurveyID);
                } elseif ($surveyType == 2) {
                    $this->account->copy_survey($newSurveyID, $surveyIDToCopy);
                }
                return $response->withRedirect($this->router->pathFor('surveyHome', ['surveyId' => $newSurveyID]));
            }
        }

        return $this->view->render($response, 'views/surveys.html.twig', [
            'accountId' => $user->account_id,
            'surveys' => $surveys
        ]);
    }

    /**
     * Survey->Home
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function home(Request $request, Response $response, $args) {
//        $user = $this->auth->getUser();
        $survey = $request->getAttribute('survey');
        $user = $request->getAttribute('user');
//        $survey = $this->account->single_survey($user->account_id, $request->getAttribute('surveyId'));

        // POST: activate/deactivate
        if ($request->getParam('btnActivate')) {
            $this->account->activate_deactivate($user->account_id, $survey['survey_id']);
            $routeName = $request->getAttribute('route')->getName();
            return $response->withRedirect($this->router->pathFor($routeName, ['surveyId' => $survey['survey_id']]));
        }

        // POST: edit
        if ($request->getParam('btnEditSurvey')) {
            $surveyName = filterText($request->getParam('txtSurveyName'));
            if (strlen($surveyName) == 0) {
                $surveyName = $survey['survey_name'];
            }
            $this->account->edit_survey($user->account_id, $survey['survey_id'], $surveyName);
            return $response->withRedirect($this->router->pathFor('surveyHome', ['surveyId' => $survey['survey_id']]));
        }

        // POST: delete
        if ($request->getParam('btnDeleteSurvey')) {
            $this->account->delete_survey($survey['survey_id']);
            return $response->withRedirect($this->router->pathFor('surveys'));
        }

        return $this->view->render($response, 'views/survey/home.html.twig', [
            'survey' => $survey
        ]);
    }

    private function permissionsFromRequest(Request $request, $survey) {
        $permissions = $request->getParam('permissions') ? $request->getParam('permissions') : [];
        return array_map(function($param) use ($survey) {
            return ['name' => $param, 'value' => $survey['survey_id']];
        }, array_keys($permissions));
    }

    /**
     * Survey->Users
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function users(Request $request, Response $response, $args) {
        $authenticatedUser = $request->getAttribute('user');
        $survey = $request->getAttribute('survey');

        // edit user
        if ($request->getParam('btnEditUser')) {
            // only let survey owners modify users
            if ($survey->account_id !== $authenticatedUser->account_id)
                return $response->withRedirect($this->router->pathFor('logout'));

            $user = User::find($request->getParam('account_id'));
            $permissions = [['name' => Permission::CAN_VIEW_SURVEY, 'value' => $survey['survey_id']]];
            if (!$user) {
                $user = new User($request->getParams());// User::create($request->getParams());
                $permissions = [['name' => Permission::CAN_VIEW_SURVEY, 'value' => $survey['survey_id']]];
            } else {
                $user->update($request->getParams());
            }

            $permissions = array_merge($permissions, $this->permissionsFromRequest($request, $survey));

            if ($password = $request->getParam('account_pwd'))
                $user->account_pwd = UserRepository::hashPassword($password);

            $user->save();

            $user->permissions()->delete();
            $user->permissions()->createMany($permissions);

            return $response->withRedirect($this->router->pathFor('surveyUsers', ['surveyId' => $survey->survey_id]));
        }

        // delete user
        if ($request->getParam('btnDeleteUser')) {
            // only let survey owners modify users
            if ($survey->account_id !== $authenticatedUser->account_id)
                return $response->withRedirect($this->router->pathFor('logout'));

            $user = User::find($request->getParam('account_id'));
            $user->delete();

            return $response->withRedirect($this->router->pathFor('surveyUsers', ['surveyId' => $survey->survey_id]));
        }

        $users = UserRepository::findBySurveyId($survey['survey_id']);

        return $this->view->render($response, 'views/survey/users.html.twig', [
            'survey' => $survey,
            'users' => $users,
        ]);
    }

    /**
     * Survey->Content
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function content(Request $request, Response $response, $args) {
        $user = $this->auth->getUser();
        $survey = $request->getAttribute('survey');

        $pages = $this->account->survey_pages($survey['survey_id']);
        $questions = $this->account->survey_questions($survey['survey_id']);

        $questionId = $request->getParam('questionId');
        $singleQuestion = $this->account->single_question($survey['survey_id'], $questionId);
        if (empty($singleQuestion))
            $questionId = 0;
        $branchFrom = $singleQuestion['question_desc'];
        if (strlen($branchFrom) == 0)
            $branchFrom = 'New Branch';
        $pageDesc = $branchFrom;

        if ($request->getParam('btnNewPage')) {
            $pageDesc = filterText($request->getParam('txtPageDesc'));
            if (strlen($pageDesc) == 0)
                $pageDesc = $branchFrom;
            $pageExtra = trim($request->getParam('txtPageExtra'));
            $newPageID = $this->account->insert_new_page($survey['survey_id'], $questionId, $pageDesc, $pageExtra);
            return $response->withRedirect($this->router->pathFor('surveyPage', ['surveyId' => $survey['survey_id'], 'pageId' => $newPageID]));
        }

        return $this->view->render($response, 'views/survey/content.html.twig', [
            'survey' => $survey,
            'pages' => $pages,
            'questions' => $questions,
            'branch_from' => $branchFrom,
            'page_desc' => $pageDesc,

        ]);
    }

    /**
     * Survey->Crosstab
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function crosstab(Request $request, Response $response, $args) {
        require_once('./fct/fctFunctions.php');

        $survey = $request->getAttribute('survey');
        $surveyInfo = $this->report->survey_info($survey['survey_id']);

        $pageID = @surveyMap($survey['survey_id'])[0]['page_id'];

        /**
         * branches
         */
        $crosstabBranches = $this->account->calcs($survey['survey_id'], $pageID, null, [], []);
        $filteredCrosstabBranches = array_filter($crosstabBranches, function($el) use ($request) {
            return $el['question_id'] === $request->getParam('branch');
        });
        $crosstabBranch = reset($filteredCrosstabBranches);

        /**
         * groups
         */
        $crosstabGroups = [];
        if ($branch = $request->getParam('branch')) {
            $subPageID = $this->account->parent_page_id($branch);
            $crosstabGroups = $this->account->calcs($survey['survey_id'], $subPageID, null, [], []);
        }
        $filteredCrosstabGroups = array_filter($crosstabGroups, function($el) use ($request) {
            return $el['question_id'] === $request->getParam('group');
        });
        $crosstabGroup = reset($filteredCrosstabGroups);

        /**
         * categories
         */
        $crosstabCategories = [];
        if ($group = $request->getParam('group')) {
            $subPageID = $this->account->parent_page_id($group);
            $crosstabCategories = $this->account->calcs($survey['survey_id'], $subPageID, null, [], []);
        }
        $filteredCrosstabCategories = array_filter($crosstabCategories, function($el) use ($request) {
            return $el['question_id'] === $request->getParam('category');
        });
        $crosstabCategory = reset($filteredCrosstabCategories);

        /**
         * tasks
         */
        $crosstabTasks = [];
        if ($category = $request->getParam('category')) {
            $subPageID = $this->account->parent_page_id($category);
            $crosstabTasks = $this->account->calcs($survey['survey_id'], $subPageID, null, [], []);
        }
        $filteredCrosstabTasks = array_filter($crosstabTasks, function($el) use ($request) {
            return $el['question_id'] === $request->getParam('task');
        });
        $crosstabTask = reset($filteredCrosstabTasks);

        /**
         * processes
         */

        /**
         * generate data
         */
        $crosstabDatatable = $this->crosstab->getData($survey['survey_id'], $request->getParam('total', 'cost'), $request->getParam('by'), $request->getParam('branch'), $request->getParam('group'), $request->getParam('category'));

        /**
         * title
         */
        $crosstabTitle = '';
        if ($request->getParam('category')) {
            foreach ($crosstabCategories as $q) {
                if ($q['question_id'] === $request->getParam('category')) {
                    $crosstabTitle = $q['question_desc'];
                    break;
                }
            }
        } else if ($request->getParam('group')) {
            foreach ($crosstabGroups as $q) {
                if ($q['question_id'] === $request->getParam('group')){
                    $crosstabTitle = $q['question_desc'];
                    break;
                }
            }
        } else if($request->getparam('branch')) {
            foreach ($crosstabBranches as $q) {
                if ($q['question_id'] === $request->getParam('branch')) {
                    $crosstabTitle = $q['question_desc'];
                    break;
                }
            }
        }

        $total = $request->getParam('total', 'cost');
        $by = $request->getParam('by', 'group');

        // if downloading pdf
        if ($request->getAttribute('route')->getName() === 'surveyCrosstabExcel') {
//            \PHPExcel_Settings::setZipClass(\PHPExcel_Settings::PCLZIP);

            $excel = new PHPExcel();

            $excel->getProperties()->setCreator('OAS')
                ->setTitle('Title')
                ->setSubject('Subject')
                ->setDescription('Description');

            $sheet = $excel->setActiveSheetIndex(0);

            $sheet->getProtection()->setSheet(true);
            $sheet->getProtection()->setObjects(true);
            $sheet->getProtection()->setPassword('password123');

            $sheet->freezePane('D1');

            $sheet->getColumnDimensionByColumn(0)->setAutoSize(true);
            $sheet->getColumnDimensionByColumn(1)->setAutoSize(true);
            foreach (range(1, 1000) as $col) {
                $sheet->getColumnDimensionByColumn($col)->setWidth(15);
            }
            $sheet->getStyle('A1:LZ100')->getAlignment()->setIndent(1);

            $first = reset($crosstabDatatable['body']);

            $row = 2;
            $drawing = new PHPExcel_Worksheet_Drawing();
            $drawing->setPath('.' . $surveyInfo['logo_survey']);
            $drawing->setCoordinates('B2');
            $drawing->setHeight(40);
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
            $sheet->getRowDimension($row)->setRowHeight(35);

            $row++;

//            $sheet->setCellValueByColumnAndRow(0, $row, 'Date');
//            $sheet->getStyleByColumnAndRow(0, $row)->getAlignment()->setHorizontal('right');
            $sheet->setCellValueByColumnAndRow(1, $row, \PHPExcel_Shared_Date::PHPToExcel(time()));
            $sheet->getStyleByColumnAndRow(1, $row)->getNumberFormat()->setFormatCode('mm/dd/YYYY h:mm:ss');
            $sheet->getStyleByColumnAndRow(1, $row)->getAlignment()->setHorizontal('left');
            $row++;
            $row++;


            $sheet->setCellValueByColumnAndRow(1, $row, $crosstabTitle);
            $sheet->setCellValueByColumnAndRow(2, $row - 1, 'Total');
            $sheet->getStyleByColumnAndRow(2, $row - 1)->getAlignment()->setHorizontal('right');
            $totalParticipants = array_reduce($first['participants'], function($carry, $val) {
                return $carry + $val;
            }, 0);
            $sheet->setCellValueByColumnAndRow(2, $row, $totalParticipants);

            $col = 3;

            foreach ($first['data'] as $key => $val) {
                $sheet->setCellValueByColumnAndRow($col, $row - 1, $key);
                $sheet->getStyleByColumnAndRow($col, $row - 1)->getAlignment()->setWrapText(true);
                $sheet->getStyleByColumnAndRow($col, $row - 1)->getAlignment()->setHorizontal('right');
                $sheet->setCellValueByColumnAndRow($col, $row, $first['participants'][$key]);
                $col++;
            }

            $row++;

            foreach ($crosstabDatatable['body'] as $key => $val) {
                $sheet->getRowDimension($row)->setRowHeight(25);

                $value = $request->getParam('total', 'cost') === 'cost'
                    ? number_format(round($val['total'] / 1000))
                    : number_format(round($val['total_hours'] / 1000));
                $sheet->setCellValueByColumnAndRow(1, $row, $key);
                $sheet->getStyleByColumnAndRow(1, $row)->getAlignment()->setVertical('center');
                $sheet->setCellValueByColumnAndRow(2, $row, $value);
                $sheet->getStyleByColumnAndRow(2, $row)->getAlignment()->setHorizontal('right');
                $col = 3;
                foreach ($val['data'] as $rowData) {
                    $percentage = $request->getParam('total', 'cost') === 'cost'
                        ? $this->crosstab->calculatePercent($crosstabDatatable['head']['scale']['min'], $crosstabDatatable['head']['scale']['max'], $rowData['value'])
                        : $this->crosstab->calculatePercent($crosstabDatatable['head']['hour']['min'], $crosstabDatatable['head']['hour']['max'], $rowData['hour']);
                    $color = $this->crosstab->generateColor($percentage);
                    $value = $request->getParam('total', 'cost') === 'cost'
                        ? $rowData['value'] / 1000
                        : $rowData['hour'] / 1000;
                    $sheet->setCellValueByColumnAndRow($col, $row, number_format(round($value)));
                    $colors = explode(',', $color);
                    $hex = sprintf("%02x%02x%02x", $colors[0], $colors[1], $colors[2]);
                    $sheet->getStyleByColumnAndRow($col, $row)->getFill()->setFillType('solid')->getStartColor()->setRGB($hex);
                    $sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal('right');
                    $sheet->getStyleByColumnAndRow($col, $row)->getAlignment()->setVertical('center');
                    $sheet->getStyleByColumnAndRow($col, $row)->applyFromArray([
                        'borders' => [
                            'allborders' => [
                                'style'=> 'thin',
                                'color' => ['rgb' => 'dddddd']
                            ]
                        ]
                    ]);
                    $col++;
                }
                $row++;
            }

            $row++;
            $row++;

            $sheet->setCellValueByColumnAndRow(0, $row, 'Branch');
            $sheet->getStyleByColumnAndRow(0, $row)->getAlignment()->setHorizontal('right');
            @$sheet->setCellValueByColumnAndRow(1, $row, $crosstabBranch['question_desc']);
            $row++;
            if ($crosstabGroup) {
                $sheet->setCellValueByColumnAndRow(0, $row, 'Group');
                $sheet->getStyleByColumnAndRow(0, $row)->getAlignment()->setHorizontal('right');
                @$sheet->setCellValueByColumnAndRow(1, $row, $crosstabGroup['question_desc']);
                $row++;
            }
            if ($crosstabCategory) {
                $sheet->setCellValueByColumnAndRow(0, $row, 'Category');
                $sheet->getStyleByColumnAndRow(0, $row)->getAlignment()->setHorizontal('right');
                @$sheet->setCellValueByColumnAndRow(1, $row, $crosstabCategory['question_desc']);
                $row++;
            }
            if ($crosstabTask) {
                $sheet->setCellValueByColumnAndRow(0, $row, 'Task');
                $sheet->getStyleByColumnAndRow(0, $row)->getAlignment()->setHorizontal('right');
                @$sheet->setCellValueByColumnAndRow(1, $row, $crosstabTask['question_desc']);
                $row++;
            }
            $sheet->setCellValueByColumnAndRow(1, $row, $total === 'cost' ? 'Total Cost' : 'Total Hours');
            $row++;
            $sheet->setCellValueByColumnAndRow(1, $row, 'By ' . ucfirst($by));

            $row++;
            $row++;
            $sheet->setCellValueByColumnAndRow(1, $row, $surveyInfo['copyright']);


            header('Content-type: text/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="file.xlsx"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            $writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            $writer->save('php://output');
            exit;
        }

        return $this->view->render($response, 'views/survey/crosstab.html.twig', [
            'survey' => $survey,
            'survey_info' => $surveyInfo,
            'crosstabBranches' => $crosstabBranches,
            'crosstabGroups' => $crosstabGroups,
            'crosstabCategories' => $crosstabCategories,
            'crosstabTasks' => $crosstabTasks,
            'crosstabDatatable' => $crosstabDatatable,
            'crosstabTitle' => $crosstabTitle,
        ]);
    }

    /**
     * Survey->Individual
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function individual(Request $request, Response $response, $args) {
        $survey = $request->getAttribute('survey');
        $surveyInfo = $this->report->survey_info($survey['survey_id']);
        $respondents = $this->report->get_all_individuals($survey['survey_id'], 0);

        // if form submitted
        $respondent = null;
        $indTable = '';
        if ($request->isPost()) {
            require_once('fct/fctIndividual.php');
            $respondent = $this->report->get_individual($request->getParam('respondentId'), $survey['survey_id']);
            $indTable = getIndividual($request->getParam('respondentId'), $survey['survey_id'], $respondent['resp_total_compensation']);
        }

        return $this->view->render($response, 'views/survey/individual.html.twig', [
            'survey' => $survey,
            'survey_info' => $surveyInfo,
            'respondents' => $respondents,
            'respondent' => $respondent,
//            'questionDescArray' => $questionDescArray,
            'indTable' => $indTable,


        ]);
    }

    private function saveQuestion(Request $request, $page) {
        $survey = $request->getAttribute('survey');
        $question = Question::where('survey_id', $survey['survey_id'])->where('question_id', $request->getParam('questionId'))->first();

        if (!$question) {
            $question = new Question();
            $question->survey_id = $survey['survey_id'];
            $question->page_id = $page['page_id'];
        }

        $question->question_code = filterText($request->getParam('txtQuestionCode'));
        $question->question_desc = filterText($request->getParam('txtQuestionDesc'));
        $question->question_extra = filterText($request->getParam('txtQuestionExtra'));
        $question->question_desc_alt = filterText($request->getParam('txtQuestionDescAlt'));
        $question->question_extra_alt = filterText($request->getParam('txtQuestionExtraAlt'));
        $question->question_enabled = substr($request->getParam('chkQuestionEnabled'),0,1);

        $question->save();
    }

    /**
     * Survey->Page
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function page(Request $request, Response $response, $args) {
        $survey = $request->getAttribute('survey');
        $page = $this->account->single_page($survey['survey_id'], $args['pageId']);

        if (!$page)
            return $response->withRedirect($this->router->pathFor('logout'));

        if ($request->getParam('btnDeleteItem')) {
            $this->account->delete_question_and_dependents($survey['survey_id'], $page['page_id'], $request->getParam('questionId'), false);
            return $response->withRedirect($this->router->pathFor('surveyPage', ['surveyId' => $survey['survey_id'], 'pageId' => $page['page_id']]));
        }

        if ($request->getParam('btnSaveItem')) {
            $this->saveQuestion($request, $page);
            return $response->withRedirect($this->router->pathFor('surveyPage', ['surveyId' => $survey['survey_id'], 'pageId' => $page['page_id']]));
        }

        if ($request->getParam('btnEditPage')) {
            $questionId = $request->getParam('questionId');
            $singleQuestion = $this->account->single_question($survey['survey_id'], $questionId);
            $branchFrom = $singleQuestion['question_desc'];
            if (strlen($branchFrom) == 0)
                $branchFrom = 'New Branch';

            $pageDesc = filterText($request->getParam('txtPageDesc'));
            if (strlen($pageDesc) == 0) {
                $pageDesc = $branchFrom;
            }
            $pageExtra = trim($request->getParam('txtPageExtra'));
            $this->account->edit_page($survey['survey_id'], $page['page_id'], $pageDesc, $pageExtra);

            $routeName = $request->getAttribute('route')->getName();
            return $response->withRedirect($this->router->pathFor($routeName, ['surveyId' => $survey['survey_id'], 'pageId' => $page['page_id']]));
        }

        if ($request->getParam('btnDeletePage')) {
            $this->account->delete_question_and_dependents($survey['survey_id'], $page['page_id'], $page['question_id_parent'], true);
            return $response->withRedirect($this->router->pathFor('surveyContent', ['surveyId' => $survey['survey_id']]));
        }

        if ($reseqQID = $request->getParam('btnQuestionUp')) {
            $this->account->resequence_question($survey['survey_id'], $page['page_id'], $reseqQID, 'up');
            $routeName = $request->getAttribute('route')->getName();
            return $response->withRedirect($this->router->pathFor($routeName, ['surveyId' => $survey['survey_id'], 'pageId' => $page['page_id']]));
        }

        if ($reseqQID = $request->getParam('btnQuestionDown')) {
            $this->account->resequence_question($survey['survey_id'], $page['page_id'], $reseqQID, 'down');
            $routeName = $request->getAttribute('route')->getName();
            return $response->withRedirect($this->router->pathFor($routeName, ['surveyId' => $survey['survey_id'], 'pageId' => $page['page_id']]));
        }

        $questions = $this->account->survey_questions_on_page($survey['survey_id'], $page['page_id']);

        return $this->view->render($response, 'views/survey/page.html.twig', [
            'survey' => $survey,
            'page' => $page,
            'questions' => $questions,
        ]);
    }

    /**
     * Survey->Question
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function question(Request $request, Response $response, $args) {
        $survey = $request->getAttribute('survey');
        $page = $this->account->single_page($survey['survey_id'], $args['pageId']);
        $question = Question::where('survey_id', $survey['survey_id'])->where('question_id', $request->getAttribute('questionId'))->first();

        if ($request->getParam('btnSaveItem')) {
            if (!$question) {
                $question = new Question();
                $question->survey_id = $survey['survey_id'];
                $question->page_id = $page['page_id'];
            }

            $question->question_code = filterText($request->getParam('txtQuestionCode'));
            $question->question_desc = filterText($request->getParam('txtQuestionDesc'));
            $question->question_extra = filterText($request->getParam('txtQuestionExtra'));
            $question->question_desc_alt = filterText($request->getParam('txtQuestionDescAlt'));
            $question->question_extra_alt = filterText($request->getParam('txtQuestionExtraAlt'));
            $question->question_enabled = substr($request->getParam('chkQuestionEnabled'),0,1);

            $question->save();

            return $response->withRedirect($this->router->pathFor('surveyPage', ['surveyId' => $survey['survey_id'], 'pageId' => $page['page_id']]));
        }

        $questions = $this->account->survey_questions_on_page($survey['survey_id'], $page['page_id']);

        return $this->view->render($response, 'views/survey/question.html.twig', [
            'survey' => $survey,
            'page' => $page,
            'question' => $question,
            'questions' => $questions,
        ]);
    }

    /**
     * Survey->Question
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function addQuestion(Request $request, Response $response, $args) {
        $survey = $request->getAttribute('survey');
        $page = $this->account->single_page($survey['survey_id'], $args['pageId']);
        $question = Question::where('survey_id', $survey['survey_id'])->where('question_id', $request->getAttribute('questionId'))->first();

        if ($request->getParam('btnSaveItem')) {
            if (!$question) {
                $question = new Question();
                $question->survey_id = $survey['survey_id'];
                $question->page_id = $page['page_id'];
            }

            $question->question_code = filterText($request->getParam('txtQuestionCode'));
            $question->question_desc = filterText($request->getParam('txtQuestionDesc'));
            $question->question_extra = filterText($request->getParam('txtQuestionExtra'));
            $question->question_desc_alt = filterText($request->getParam('txtQuestionDescAlt'));
            $question->question_extra_alt = filterText($request->getParam('txtQuestionExtraAlt'));
            $question->question_enabled = substr($request->getParam('chkQuestionEnabled'),0,1);

            $question->save();

            return $response->withRedirect($this->router->pathFor('surveyPage', ['surveyId' => $survey['survey_id'], 'pageId' => $page['page_id']]));
        }

        $questions = $this->account->survey_questions_on_page($survey['survey_id'], $page['page_id']);

        return $this->view->render($response, 'views/survey/add-question.html.twig', [
            'survey' => $survey,
            'page' => $page,
            'question' => $question,
            'questions' => $questions,
        ]);
    }

    /**
     * Survey->Profile
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function profile(Request $request, Response $response, $args) {
        $survey = $request->getAttribute('survey');
        $surveyInfo = $this->report->survey_info($survey['survey_id']);

        $customInfo = [];
        foreach ([2, 3, 4, 5, 6] as $p) {
            $customInfo[$p] = $this->report->report_profile($survey['survey_id'], "cust_$p");
        }

        return $this->view->render($response, 'views/survey/profile.html.twig', [
            'survey' => $survey,
            'survey_info' => $surveyInfo,
            'custom_info' => $customInfo,
        ]);
    }

    /**
     * Survey->Report
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function report(Request $request, Response $response, $args) {
        require_once('./fct/fctFunctions.php');

        $survey = $request->getAttribute('survey');
        $surveyInfo = $this->report->survey_info($survey['survey_id']);

        $pageID = $request->getParam('pid');
        $map = surveyMap($survey['survey_id']);
        $hoursPageID = @$map[0]['page_id'];
        $hoursTitle = 'Legal & Support';
        $isHoursPage = false;

        $filters = [];
        for ($i = 1; $i <= 6; $i++) {
            if ($custom = $request->getParam("cust$i"))
                $filters[] = $custom;
        }

        if (empty($pageID) || $pageID == $hoursPageID) {
            $pageID = $hoursPageID; //start from the beginning of the survey if no page marker found
            $isHoursPage = true;
        }
        unset($value);
        $value = $pageID; //get question id of parent question
        //filter down survey map to just the current page and get all parent elements of current page. allows up to 5 levels
        $map_filtered = array_values(array_filter($map, function($ar) use ($value) { return ($ar["page_id"] == $value); }));

        //echo "Map Filtered: <pre>", print_r($map_filtered),"</pre>";
        $questionIDArray[0] = @$map_filtered[0]['question_id_parent_5'];
        $questionIDArray[1] = @$map_filtered[0]['question_id_parent_4'];
        $questionIDArray[2] = @$map_filtered[0]['question_id_parent_3'];
        $questionIDArray[3] = @$map_filtered[0]['question_id_parent_2'];
        $questionIDArray[4] = @$map_filtered[0]['question_id_parent'];
        //$pages gets details of parent elements and current page. Includes question, question id, and page id

        $pages = $this->report->parent_pages($survey['survey_id'], $questionIDArray);

        $currentPage = end($pages);
        $reportTitle = $currentPage['question_desc'];
        if ($isHoursPage) {
            $reportTitle = $hoursTitle;
        }

        //get data from calcs
        // GROUP BY clauses
        $group = [];

        //hit calcs
        $results = $this->account->calcs($survey['survey_id'], $pageID, NULL, $group, $filters);
        // TODO: put this in view somehow
        foreach ($results as &$result) {
            //get page_id where question_id is the question_id_parent
            $result['page_id'] = $this->account->parent_page_id($result['question_id']);
        }

        $employeeFilters = $this->account->survey_filters($survey['survey_id'], 1);
        $groupFilters = $this->account->survey_filters($survey['survey_id'], 2);
        $titleFilters = $this->account->survey_filters($survey['survey_id'], 3);
        $departmentFilters = $this->account->survey_filters($survey['survey_id'], 4);
        $categoryFilters = $this->account->survey_filters($survey['survey_id'], 5);
        $cityFilters = $this->account->survey_filters($survey['survey_id'], 6);
        
        $query = $_GET;
        unset($query['cust1'], $query['cust2'], $query['cust3'], $query['cust4'], $query['cust5'], $query['cust6']);

        return $this->view->render($response, 'views/survey/report.html.twig', [
            'survey' => $survey,
            'survey_info' => $surveyInfo,
            'employee_filters' => $employeeFilters,
            'group_filters' => $groupFilters,
            'title_filters' => $titleFilters,
            'department_filters' => $departmentFilters,
            'category_filters' => $categoryFilters,
            'city_filters' => $cityFilters,
            'pages' => $pages,
            'results' => $results,
            'title' => $reportTitle,
            'hours_title' => $hoursTitle,
            'filters' => $filters,

            'query_result' => http_build_query($query)
        ]);
    }

    /**
     * Survey->Reports
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function reports(Request $request, Response $response, $args) {
        $survey = $request->getAttribute('survey');

        return $this->view->render($response, 'views/survey/reports.html.twig', [
            'survey' => $survey,
        ]);
    }

    /**
     * Survey->Respondents
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function respondents(Request $request, Response $response, $args) {
        $survey = $request->getAttribute('survey');
        $respondents = Respondent::all()->where('survey_id', '=', $survey['survey_id'])->sortBy('resp_last')->sortBy('resp_first');

        $respondedCount = $respondents->where('last_dt', '<>', null)->count();
        $completedCount = $respondents->where('survey_completed', '=', 1)->count();

        $labels = $this->account->get_field_labels($survey['survey_id']);

        // delete respondent
        if ($request->getParam('btnDeleteRespondent')) {
            $respondent = $this->account->single_respondent($survey['survey_id'], $request->getParam('respondentId'));

            $this->account->delete_respondent_survey($survey['survey_id'], $respondent['resp_id']);
            $this->account->delete_respondent($survey['survey_id'], $respondent['resp_id']);

            return $response->withRedirect($this->router->pathFor('surveyRespondents', ['surveyId' => $survey['survey_id']]));
        }

        // edit respondent
        if (isset($_POST['btnEditRespondent'])) {
            $respondent = Respondent::where('resp_id', $request->getParam('respondentId'))->first();

            // check for duplicate access code
            if (Respondent::where('resp_access_code', '=', $request->getParam('txtRespAccessCode'))->where('resp_id', '<>', $request->getParam('respondentId'))->first())
                return $response->withJson(['status' => 'error', 'errors' => ['resp-access-code-error' => null]]);

            if (!$respondent) {
                $respondent = new Respondent();
                $respondent->survey_id = $survey['survey_id'];
            }
            $respondent->resp_access_code = filterText($request->getParam('txtRespAccessCode'));
            $respondent->resp_first = filterText($request->getParam('txtRespFirst'));
            $respondent->resp_last = filterText($request->getParam('txtRespLast'));
            $respondent->resp_email = filterText($request->getParam('txtRespEmail'));
            $respondent->resp_alt = substr($request->getParam('chkRespAlt'),0,1);
            $respondent->cust_1 = filterText($request->getParam('txtCust1'));
            $respondent->cust_2 = filterText($request->getParam('txtCust2'));
            $respondent->cust_3 = filterText($request->getParam('txtCust3'));
            $respondent->cust_4 = filterText($request->getParam('txtCust4'));
            $respondent->cust_5 = filterText($request->getParam('txtCust5'));
            $respondent->cust_6 = filterText($request->getParam('txtCust6'));

            $respondent->save();

            return $response->withJson([
                'status' => 'success',
                'url' => $this->router->pathFor('surveyRespondents', ['surveyId' => $survey['survey_id']]),
            ]);

//            if ($respEdit == false) {
//                $acHasError = " has-error";
//                $acErrorMsg = "This Access Code is assigned to another person. Please try another.";
//            } else {
//                return $response->withRedirect($this->router->pathFor('surveyRespondents', ['surveyId' => $survey['survey_id']]));
//            }
        }

        if (isset($_POST['btnSaveLabels'])) {
            $cust1 = filterText($request->getParam('txtCust1'));
            $cust2 = filterText($request->getParam('txtCust2'));
            $cust3 = filterText($request->getParam('txtCust3'));
            $cust4 = filterText($request->getParam('txtCust4'));
            $cust5 = filterText($request->getParam('txtCust5'));
            $cust6 = filterText($request->getParam('txtCust6'));
            $this->account->update_field_labels($survey['survey_id'], $cust1, $cust2, $cust3, $cust4, $cust5, $cust6);
            return $response->withRedirect($this->router->pathFor('surveyRespondents', ['surveyId' => $survey['survey_id']]));
        }


        return $this->view->render($response, 'views/survey/respondents.html.twig', [
            'survey' => $survey,
            'respondents' => $respondents,
            'responded_count' => $respondedCount,
            'completed_count' => $completedCount,
            'labels' => $labels,
            'survey_hash' => PseudoCrypt::hash($survey['survey_id'], 10),
        ]);
    }

    /**
     * Survey->Settings
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
            $settings->annual_legal_hours_text = trim($request->getParam('txtAnnualLegalHoursText'));
            $settings->copyright = trim($request->getParam('copyright'));

            $settings->save();

            $routeName = $request->getAttribute('route')->getName();
            return $response->withRedirect($this->router->pathFor($routeName, ['surveyId' => $survey['survey_id']]));
        }

        return $this->view->render($response, 'views/survey/settings.html.twig', [
            'survey' => $survey,
            'settings' => $settings,
        ]);
    }


    /**
     * Survey
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function survey(Request $request, Response $response, $args) {
        $survey = $request->getAttribute('survey');
        $settings = Settings::all()->where('survey_id', '=', $survey['survey_id'])->first();

        require_once('oas2.php');
//        exit;
//
//        return $this->view->render($response, 'views/survey/settings.html.twig', [
//            'survey' => $survey,
//            'settings' => $settings,
//        ]);
    }


    /**
     * Survey->Content-Export
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function contentDetailed(Request $request, Response $response, $args) {
        $survey = $request->getAttribute('survey');
        $surveyID = $survey['survey_id'];

        if (!$survey)
            return $response->withRedirect($this->router->pathFor('logout'));

        $pageArray = $this->account->survey_pages($survey['survey_id']);
        $questionArray = $this->account->survey_questions($survey['survey_id']);

        $table = '';
        $table .= "<h4 class='title'>Detailed Content Of " . $survey['survey_name'] . "</h4>";
        $table .= "<a class='btn btn-primary btn-sm' href='" . $this->router->pathFor('surveyContent', ['surveyId' => $surveyID]) . "'><span class='glyphicon glyphicon-arrow-left'></span> Go Back</a>\n";
        $table .= "<br /><br />";
        $table .= "<div class='well'>\n";
        $table .= "<table class='table table-striped' id='tblDetails'>\n";
        $table .= "<tr>\n";
        $table .= "<th width='75'></td>\n";
        $table .= "<th>#</td>\n";
        $table .= "<th>QID</td>\n";
        $table .= "<th>PID</td>\n";
        $table .= "<th>Code</td>\n";
        $table .= "<th>Item</td>\n";
        $table .= "<th>Definition</td>\n";
        $table .= "<th>Alt Item</td>\n";
        $table .= "<th>Alt Definition</td>\n";
        $table .= "<th>UTBMS</td>\n";
        $table .= "</tr>\n";
        $questionSeq = 1;
        //LEVEL ONE #######################################################################################################
        unset($value);
        $value = 0;
        $pageArray_1 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));
        for($p1=0;$p1<count($pageArray_1);++$p1) {
            unset($value);
            $value = $pageArray_1[$p1]['page_id'];
            $questionArray_1 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
            for($q1=0;$q1<count($questionArray_1);++$q1) {
                unset($value);
                $value = $questionArray_1[$q1]['question_id']; //get question id of parent question
                $pageArray_2 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));
                // START LEVEL 2
                $table .= "<tr>\n";

                $table .= "<td><a href='?rq=export&sid=$surveyID&qid=" . $questionArray_1[$q1]['question_id'] . "&cst=edititem'><span class='glyphicon glyphicon-pencil'></span> </a><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_1[$q1]['question_enabled'] . "'></span></td>\n";
                $table .= "<td>" . $questionSeq++ . "</td>\n";
                $table .= "<td>" . $questionArray_1[$q1]['question_id'] . "</td>\n";
                $table .= "<td>" . $pageArray_1[$p1]['page_id'] . "</td>\n";
                $table .= "<td>" . $questionArray_1[$q1]['question_code'] . "</td>\n";
                $table .= "<td>" . $questionArray_1[$q1]['question_desc'] . "</td>\n";
                $table .= "<td>" . $questionArray_1[$q1]['question_extra'] . "</td>\n";
                $table .= "<td>" . $questionArray_1[$q1]['question_desc_alt'] . "</td>\n";
                $table .= "<td>" . $questionArray_1[$q1]['question_extra_alt'] . "</td>\n";
                $table .= "<td>" . $questionArray_1[$q1]['question_UTBMS'] . "</td>\n";
                $table .= "</tr>\n";
                for($p2=0;$p2<count($pageArray_2);++$p2) {
                    unset($value);
                    $value = $pageArray_2[$p2]['page_id'];
                    $questionArray_2 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
                    for($q2=0;$q2<count($questionArray_2);++$q2) {
                        unset($value);
                        $value = $questionArray_2[$q2]['question_id']; //get question id of parent question
                        $pageArray_3 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));
                        // START LEVEL 3
                        $table .= "<tr><td colspan='9' style='background-color:#666;'></tr>\n";
                        $table .= "<tr>\n";

                        $table .= "<td><a href='?rq=export&sid=$surveyID&qid=" . $questionArray_2[$q2]['question_id'] . "&cst=edititem'><span class='glyphicon glyphicon-pencil'></span></a><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_2[$q2]['question_enabled'] . "'></span></td>\n";
                        $table .= "<td>" . $questionSeq++ . "</td>\n";
                        $table .= "<td>" . $questionArray_2[$q2]['question_id'] . "</td>\n";
                        $table .= "<td>" . $pageArray_2[$p2]['page_id'] . "</td>\n";
                        $table .= "<td>" . $questionArray_2[$q2]['question_code'] . "</td>\n";
                        $table .= "<td>" . $questionArray_2[$q2]['question_desc'] . "</td>\n";
                        $table .= "<td>" . $questionArray_2[$q2]['question_extra'] . "</td>\n";
                        $table .= "<td>" . $questionArray_2[$q2]['question_desc_alt'] . "</td>\n";
                        $table .= "<td>" . $questionArray_2[$q2]['question_extra_alt'] . "</td>\n";
                        $table .= "<td>" . $questionArray_2[$q2]['question_UTBMS'] . "</td>\n";
                        $table .= "</tr>\n";
                        for($p3=0;$p3<count($pageArray_3);++$p3) {
                            unset($value);
                            $value = $pageArray_3[$p3]['page_id'];
                            $questionArray_3 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
                            for($q3=0;$q3<count($questionArray_3);++$q3) {
                                unset($value);
                                $value = $questionArray_3[$q3]['question_id']; //get question id of parent question
                                $pageArray_4 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));
                                // START LEVEL 4
                                $table .= "<tr>\n";

                                $table .= "<td><a href='?rq=export&sid=$surveyID&qid=" . $questionArray_3[$q3]['question_id'] . "&cst=edititem'><span class='glyphicon glyphicon-pencil'></span></a><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_3[$q3]['question_enabled'] . "'></span></td>\n";
                                $table .= "<td>" . $questionSeq++ . "</td>\n";
                                $table .= "<td>" . $questionArray_3[$q3]['question_id'] . "</td>\n";
                                $table .= "<td>" . $pageArray_3[$p3]['page_id'] . "</td>\n";
                                $table .= "<td>" . $questionArray_2[$q2]['question_code'] . "." . $questionArray_3[$q3]['question_code'] . "</td>\n";
                                $table .= "<td>" . $questionArray_3[$q3]['question_desc'] . "</td>\n";
                                $table .= "<td>" . $questionArray_3[$q3]['question_extra'] . "</td>\n";
                                $table .= "<td>" . $questionArray_3[$q3]['question_desc_alt'] . "</td>\n";
                                $table .= "<td>" . $questionArray_3[$q3]['question_extra_alt'] . "</td>\n";
                                $table .= "<td>" . $questionArray_3[$q3]['question_UTBMS'] . "</td>\n";
                                $table .= "</tr>\n";
                                for($p4=0;$p4<count($pageArray_4);++$p4) {
                                    unset($value);
                                    $value = $pageArray_4[$p4]['page_id'];
                                    $questionArray_4 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
                                    for($q4=0;$q4<count($questionArray_4);++$q4) {
                                        unset($value);
                                        $value = $questionArray_4[$q4]['question_id']; //get question id of parent question
                                        $pageArray_5 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));
                                        // START LEVEL 5
                                        $table .= "<tr>\n";

                                        $table .= "<td><a href='?rq=export&sid=$surveyID&qid=" . $questionArray_4[$q4]['question_id'] . "&cst=edititem'><span class='glyphicon glyphicon-pencil'></span></a><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_4[$q4]['question_enabled'] . "'></span></td>\n";
                                        $table .= "<td>" . $questionSeq++ . "</td>\n";
                                        $table .= "<td>" . $questionArray_4[$q4]['question_id'] . "</td>\n";
                                        $table .= "<td>" . $pageArray_4[$p4]['page_id'] . "</td>\n";
                                        $table .= "<td>" . $questionArray_2[$q2]['question_code'] . "." . $questionArray_3[$q3]['question_code'] . "." . $questionArray_4[$q4]['question_code'] . "</td>\n";
                                        $table .= "<td>" . $questionArray_4[$q4]['question_desc'] . "</td>\n";
                                        $table .= "<td>" . $questionArray_4[$q4]['question_extra'] . "</td>\n";
                                        $table .= "<td>" . $questionArray_4[$q4]['question_desc_alt'] . "</td>\n";
                                        $table .= "<td>" . $questionArray_4[$q4]['question_extra_alt'] . "</td>\n";
                                        $table .= "<td>" . $questionArray_4[$q4]['question_UTBMS'] . "</td>\n";
                                        $table .= "</tr>\n";
                                        for($p5=0;$p5<count($pageArray_5);++$p5) {
                                            unset($value);
                                            $value = $pageArray_5[$p5]['page_id'];
                                            $questionArray_5 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
                                            for($q5=0;$q5<count($questionArray_5);++$q5) {
                                                unset($value);
                                                $value = $questionArray_5[$q5]['question_id']; //get question id of parent question
                                                $pageArray_6 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));
                                                // START LEVEL 6
                                                $table .= "<tr>\n";
                                                $table .= "<td><a href='?rq=export&sid=$surveyID&qid=" . $questionArray_5[$q5]['question_id'] . "&cst=edititem'><span class='glyphicon glyphicon-pencil'></span></a><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_5[$q5]['question_enabled'] . "'></span></td>\n";
                                                $table .= "<td>" . $questionSeq++ . "</td>\n";
                                                $table .= "<td>" . $questionArray_5[$q5]['question_id'] . "</td>\n";
                                                $table .= "<td>" . $pageArray_5[$p5]['page_id'] . "</td>\n";
                                                $table .= "<td>" . $questionArray_2[$q2]['question_code'] . "." . $questionArray_3[$q3]['question_code'] . "." . $questionArray_4[$q4]['question_code'] . "." . $questionArray_5[$q5]['question_code'] . "</td>\n";
                                                $table .= "<td>" . $questionArray_5[$q5]['question_desc'] . "</td>\n";
                                                $table .= "<td>" . $questionArray_5[$q5]['question_extra'] . "</td>\n";
                                                $table .= "<td>" . $questionArray_5[$q5]['question_desc_alt'] . "</td>\n";
                                                $table .= "<td>" . $questionArray_5[$q5]['question_extra_alt'] . "</td>\n";
                                                $table .= "<td>" . $questionArray_5[$q5]['question_UTBMS'] . "</td>\n";
                                                $table .= "</tr>\n";
                                                for($p6=0;$p6<count($pageArray_6);++$p6) {
                                                    unset($value);
                                                    $value = $pageArray_6[$p6]['page_id'];
                                                    $questionArray_6 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
                                                    for($q6=0;$q6<count($questionArray_6);++$q6) { //last level, only questions, no children pages
                                                        $table .= "<tr>\n";

                                                        $table .= "<td><a href='?rq=export&sid=$surveyID&qid=" . $questionArray_6[$q6]['question_id'] . "&cst=edititem'><span class='glyphicon glyphicon-pencil'></span></a><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_6[$q6]['question_enabled'] . "'></span></td>\n";
                                                        $table .= "<td>" . $questionSeq++ . "</td>\n";
                                                        $table .= "<td>" . $questionArray_6[$q6]['question_id'] . "</td>\n";
                                                        $table .= "<td>" . $pageArray_6[$p6]['page_id'] . "</td>\n";
                                                        $table .= "<td>" . $questionArray_2[$q2]['question_code'] . "." . $questionArray_3[$q3]['question_code'] . "." . $questionArray_4[$q4]['question_code'] . "." . $questionArray_5[$q5]['question_code'] . "." . $questionArray_6[$q6]['question_code'] . "</td>\n";
                                                        $table .= "<td>" . $questionArray_6[$q6]['question_desc'] . "</td>\n";
                                                        $table .= "<td>" . $questionArray_6[$q6]['question_extra'] . "</td>\n";
                                                        $table .= "<td>" . $questionArray_6[$q6]['question_desc_alt'] . "</td>\n";
                                                        $table .= "<td>" . $questionArray_6[$q6]['question_extra_alt'] . "</td>\n";
                                                        $table .= "<td>" . $questionArray_6[$q6]['question_UTBMS'] . "</td>\n";
                                                        $table .= "</tr>\n";
                                                    } // end q6 loop
                                                }// end p6 loop
                                            } // end q5 loop
                                        }// end p5 loop
                                    } // end q4 loop
                                }// end p4 loop
                            } // end q3 loop
                        } // end p3 loop
                    }// end q2 loop
                } // end p2 loop
            }// end q1 loop
        }// end p1 loop
        $table .= "</table>\n";
        $table .= "</div>";

        return $this->view->render($response, 'views/survey/content-detailed.html.twig', [
            'survey' => $survey,
            'table' => $table,
        ]);
    }

}