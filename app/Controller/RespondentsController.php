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
use Respect\Validation\Rules\Uploaded;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use Slim\Interfaces\RouterInterface;
use Slim\Views\Twig;
use SlimSession\Helper;

require_once('./fct/fctFunctions.php');

class RespondentsController extends BaseController {

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function get(Request $request, Response $response, $args) {
        $survey = $request->getAttribute('survey');
        $respondents = Respondent::all()->where('survey_id', '=', $survey['survey_id'])->sortBy('resp_last')->sortBy('resp_first');

        $respondedCount = $respondents->where('last_dt', '<>', null)->count();
        $completedCount = $respondents->where('survey_completed', '=', 1)->count();

        $labels = $this->account->get_field_labels($survey['survey_id']);

        // reset respondent
        if ($request->getParam('btnResetRespondent')) {
            $singleRespondent = $this->account->single_respondent($survey['survey_id'], $request->getParam('respondentId'));
            if (!$singleRespondent)
                return $response->withRedirect($this->router->pathFor('logout'));

            $this->account->delete_respondent_survey($survey['survey_id'], $request->getParam('respondentId'));

            return $response->withRedirect($this->router->pathFor('surveyRespondents', ['surveyId' => $survey['survey_id']]));
        }

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
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function generateAccessCode(Request $request, Response $response, $args) {
        $code = generateRandomString(10);
        return $response->withJson(['code' => $code]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function resetAll(Request $request, Response $response, $args) {
        $survey = $request->getAttribute('survey');
        $this->account->reset_all_respondents($survey['survey_id']);
        return $response->withRedirect($this->router->pathFor('surveyRespondents', ['surveyId' => $survey['survey_id']]));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function deleteAll(Request $request, Response $response, $args) {
        $survey = $request->getAttribute('survey');
        $this->account->delete_all_respondents($survey['survey_id']);
        return $response->withRedirect($this->router->pathFor('surveyRespondents', ['surveyId' => $survey['survey_id']]));
    }


    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function upload(Request $request, Response $response, $args) {
        $survey = $request->getAttribute('survey');

        $dryRun = $request->getParam('btnUploadCSV') === 'Check For Errors';
        $csv = [];

        // check there are no errors
        $files = $request->getUploadedFiles();
        if ($files['csv']->getError() == 0) {
            /** @var UploadedFile $file */
            $file = $files['csv'];
            $name = $file->getClientFilename();
            $path_parts = pathinfo($name);
            $ext = $path_parts['extension'];
            $type = $file->getClientMediaType();
            $tmpName = $file->file;
            // check the file is a csv
            if ($ext === 'csv') {
                if (($handle = fopen($tmpName, 'r')) !== false) {
                    // necessary if a large csv file
                    set_time_limit(60);
                    $row = 0;
                    while (($data = fgetcsv($handle, ',')) !== false) {
                        // number of fields in the csv
                        $col_count = count($data);
                        // get the values from the csv
                        for ($i = 0; $i <= 10; ++$i) { //represents the column range. Expand as needed
                            $csv[$row][$i] = $data[$i];
                        }
                        // inc the row
                        $row++;
                    }
                    fclose($handle);
                }
                $countPeople = count($csv) - 1;
                $csvUploadMessage = $dryRun
                    ? '<div class="app-text"><span style="font-weight:bold; color:green;">Error Check Complete! </span><br />' . $countPeople . ' record(s) were found in the file you selected.</div>'
                    : '<div class="app-text"><span style="font-weight:bold; color:green;">Upload Complete! </span><br />' . $countPeople . ' record(s) were found in the uploaded file. <a class="btn btn-default" href="' . $this->router->pathFor('surveyRespondents', ['surveyId' => $survey['survey_id']]) . '">Finished</a></div>';
            } else {
                $csvUploadMessage = '<div class="app-text">' . $name . ' is not a valid CSV file. Please select another file.</div>';
            }
        } else {
            $csvUploadMessage = '<div class="app-text">No file was selected. Please click the Browse button to search for the CSV file on your computer, then click Upload List.</div>';
        } // end if 'error' == 0

        $displayCSVOutput = '<div id="personScroll" style="background-color:#EEE; padding:7px; border: 1px solid #555;">' . $csvUploadMessage;

        $errorCountAccessCode = 0;
        $errorCountEmail = 0;
        $errorCountDuplicate = 0;
        $countUniqueInserts = 0;
        $countDupesInFile = 0;
        $errorAccessCode = '';
        $errorEmail = '';

        if (count($csv) > 1) {
            for ($r = 1;$r < count($csv); ++$r) { //start at 1 to skip column headings which are on $r->0
                $rowNum = $r + 1;
                for ($c = 0; $c <= 10; ++$c) { //iterate through columns in active row. currently limited to 10
                    if ($c == 0) { //if access code
                        //if (in_array($csv[$r][$c], $csv)) { // if access code is a file duplicate. need to figure out correct method here
                        // 	++$countDupesInFile;
                        //}
                        if (containsSpecialCharacters($csv[$r][$c]) || strlen($csv[$r][$c]) > 40) { //if special characters found or access code too long then log error
                            $errorAccessCode = $errorAccessCode . '<div>Row ' . $rowNum . ': ' . $csv[$r][$c] . '</div>';
                            ++$errorCountAccessCode;
                        }
                        if (containsSpecialCharacters($csv[$r][$c]) || empty($csv[$r][$c]) || strlen($csv[$r][$c]) > 40) { //if blank or invalid then generate 10 character random access code
                            $csv[$r][$c] = generateRandomString(10);
                        }
                    } // end if access code
                    if ($c == 1) { //if email column
                        if (!empty($csv[$r][$c]) && isValidEmail($csv[$r][$c]) == false) { // if email was inputted but is invalid
                            $errorEmail = $errorEmail . '<div>Row ' . $rowNum . ': ' . $csv[$r][$c] . '</div>';
                            ++$errorCountEmail;
                            $csv[$r][$c] = null; //set email to null if not a valid address
                        }
                    }
                    if ($c == 4) { //if receives alt text
                        if ($csv[$r][$c] != 1) { //looking for boolean 1 or 0. if not 1 then default to 0
                            $csv[$r][$c] = 0;
                        }
                    }
                } // end column loop
                $respIDFound = getRespID($survey['survey_id'], $csv[$r][0]);
                if (!empty($respIDFound)) {
                    if (!$dryRun) {
                        $this->account->edit_respondent($survey['survey_id'], $respIDFound, $csv[$r][0], $csv[$r][2], $csv[$r][3], $csv[$r][1], $csv[$r][4], $csv[$r][5], $csv[$r][6], $csv[$r][7], $csv[$r][8], $csv[$r][9], $csv[$r][10]);
                    }
                    ++$errorCountDuplicate;
                } else {
                    if (!$dryRun) {
                        $this->account->insert_new_respondent($survey['survey_id'], $csv[$r][0], $csv[$r][2], $csv[$r][3], $csv[$r][1], $csv[$r][4], $csv[$r][5], $csv[$r][6], $csv[$r][7], $csv[$r][8], $csv[$r][9], $csv[$r][10]);
                    }
                    ++$countUniqueInserts;
                }
            } // end row loop
        } // end if count($csv) > 1
        $tense = $dryRun ? 'will be' : 'were';
        $errorDetailsDuplicate = $errorCountDuplicate > 0
            ? '<div class="app-text">Based on the access codes, ' . $errorCountDuplicate . ' record(s) were found to exist in the exam already or were duplicates in your CSV file. These <span style="font-weight:bold; color:red;">' . $tense . ' overwritten</span> with the most recent uploaded information. ' . $countUniqueInserts . ' unique record(s) ' . $tense . ' uploaded to the exam.</div>'
            : null;

        $errorDupesInFile = $countDupesInFile > 0
            ? '<div class="app-text">' . $countDupesInFile . ' duplicate access code(s) were found in your CSV File. Each access code should be unique. Any duplicate records will be overwritten with the final one in your list.</div>'
            : null;

        $errorDetailsAccessCode = $errorCountAccessCode > 0
            ? '<div class="app-text">' . $errorCountAccessCode . ' record(s) contained an invalid access code. These ' . $tense . ' replaced by a system-generated access code. The invalid access codes are listed below.</div>'
            : null;
        $errorAccessCode = $errorCountAccessCode > 0
            ? '<div style="font-weight:bold;">Invalid Access Codes:</div>' . $errorAccessCode
            : null;

        $errorDetailsEmail = $errorCountEmail > 0
            ? '<div class="app-text">' . $errorCountEmail . ' record(s) contained an invalid email address. Invalid email addresses ' . $tense . ' removed from the upload. The invalid email addresses are listed below.</div>'
            : null;
        $errorEmail = $errorCountEmail > 0
            ? '<div style="font-weight:bold; margin-top:10px;">Invalid Email Addresses:</div>' . $errorEmail
            : null;

        $displayCSVOutput = $displayCSVOutput . $errorDetailsDuplicate . $errorDupesInFile . $errorDetailsAccessCode . $errorDetailsEmail . $errorAccessCode . $errorEmail  . '</div>';

        echo $displayCSVOutput;
        exit;
    }
}