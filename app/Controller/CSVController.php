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

class CSVController extends BaseController {

    private function getQuestionCode($value, $qArray) {
        if (empty($value)) {
            return false;
        }
        $qArray_filtered = array_values(array_filter($qArray, function($ar) use ($value) { return ($ar['question_id'] == $value); }));
        return $qArray_filtered[0]['question_code'] . '.';
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|ResponseInterface
     */
    public function download(Request $request, Response $response, $args) {
        $survey = $request->getAttribute('survey');

        //important to start column index number after the end of the respondent columns. Currently 24 but may change
        $maxRespIndex = 24;

        $isFull = $request->getParam('full');
        $surveyIDHashed = PseudoCrypt::hash($survey['survey_id'],10); //for survey URLs
        $labelArr = $this->account->get_field_labels($survey['survey_id']);
        $map = surveyMap($survey['survey_id']);
        $questionArray = $this->account->survey_questions($survey['survey_id']);
        $respArray = $this->account->survey_respondents($survey['survey_id']);

        //resource heavy query. Only call on full download process
        $respAnswerArray = $isFull
            ? $this->takesurvey->question_id_answer_value_all($survey['survey_id'])
            : null;

        // loop through question map, rebuild condensed map with question ids in sequence
        $qCt = 0;
        $headerArray = [];
        for ($m=0; $m<count($map); ++$m) {
            unset($value);
            $value = $map[$m]['page_id'];
            $questions_filtered = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar['page_id'] == $value); }));
            //Header Array of Survey Questions
            for ($q=0; $q < count($questions_filtered); ++$q) {
                //get question codes of all parent ids in order to string together the complete question code
                $pid1[$q] = getQuestionCode($map[$m]['question_id_parent'], $questionArray);
                $pid2[$q] = getQuestionCode($map[$m]['question_id_parent_2'], $questionArray);
                $pid3[$q] = getQuestionCode($map[$m]['question_id_parent_3'], $questionArray);
                $pid4[$q] = getQuestionCode($map[$m]['question_id_parent_4'], $questionArray);
                $pid5[$q] = getQuestionCode($map[$m]['question_id_parent_5'], $questionArray);
                $pid[$q] = "[" . $pid5[$q] . $pid4[$q] . $pid3[$q] . $pid2[$q] . $pid1[$q] . $questions_filtered[$q]['question_code'] . "]";
                $pid[$q] = str_replace("[.","[",$pid[$q]);
                $pid[$q] = str_replace(".]","]",$pid[$q]);
                $pid[$q] = str_replace("[]","",$pid[$q]);
                $headerArray[$qCt][0] = $pid[$q]; //question code group
                $headerArray[$qCt][1] = $questions_filtered[$q]['question_desc']; //question text
                $questionIDArray[$qCt] = $questions_filtered[$q]['question_id']; //compact 1D question id array just for question answers. should speed up process
                ++$qCt;
            } // end $q loop
        } // end $m loop
        /////////////////////////////////////////////////////////////////
        // RESPONDENT ARRAY LOOP - start at -1 to include column headings
        /////////////////////////////////////////////////////////////////
        download_send_headers("download_respondents_" . date("Y-m-d") . ".csv");
        $df = fopen("php://output", 'w');
        for ($r =- 1; $r < count($respArray); $r++) {
            //add column headings first on -1 line
            unset($csvArray);
            if($r == -1) {
                $csvArray[0] = "ACCESS CODE";
                $csvArray[1] = "EMAIL ADDRESS";
                $csvArray[2] = "FIRST NAME";
                $csvArray[3] = "LAST NAME";
                $csvArray[4] = "RESP ALT";
                $csvArray[5] = $labelArr["cust_1_label"];
                $csvArray[6] = $labelArr["cust_2_label"];
                $csvArray[7] = $labelArr["cust_3_label"];
                $csvArray[8] = $labelArr["cust_4_label"];
                $csvArray[9] = $labelArr["cust_5_label"];
                $csvArray[10] = $labelArr["cust_6_label"];
                $csvArray[11] = "CUSTOM 7";
                $csvArray[12] = "CUSTOM 8";
                $csvArray[13] = "CUSTOM 9";
                $csvArray[14] = "CUSTOM 10";
                $csvArray[15] = "CUSTOM 11";
                $csvArray[16] = "CUSTOM 12";
                $csvArray[17] = "COMPENSATION";
                $csvArray[18] = "BENEFITS";
                $csvArray[19] = "COMP-BENEFITS";
                $csvArray[20] = "START DT";
                $csvArray[21] = "LAST DT";
                $csvArray[22] = "COMPLETED";
                $csvArray[23] = "SURVEY URL";
                //add question headers
                if ($isFull == 1) {
                    for ($h=0; $h < count($headerArray); ++$h) { //loop through ordered question ids
                       $csvArray[$h+$maxRespIndex] = $headerArray[$h][0] . " " . $headerArray[$h][1];
                    }
                }
                //continue with rest of file
            } else { //end if header
                $csvArray[0] = $respArray[$r]['resp_access_code'];
                $csvArray[1] = $respArray[$r]['resp_email'];
                $csvArray[2] = $respArray[$r]['resp_first'];
                $csvArray[3] = $respArray[$r]['resp_last'];
                $csvArray[4] = $respArray[$r]['resp_alt'];
                $csvArray[5] = $respArray[$r]['cust_1'];
                $csvArray[6] = $respArray[$r]['cust_2'];
                $csvArray[7] = $respArray[$r]['cust_3'];
                $csvArray[8] = $respArray[$r]['cust_4'];
                $csvArray[9] = $respArray[$r]['cust_5'];
                $csvArray[10] = $respArray[$r]['cust_6'];
                $csvArray[11] = null;
                $csvArray[12] = null;
                $csvArray[13] = null;
                $csvArray[14] = null;
                $csvArray[15] = null;
                $csvArray[16] = null;
                $csvArray[17] = null;
                $csvArray[18] = null;
                $csvArray[19] = null;
                $csvArray[20] = $respArray[$r]['start_dt'];
                $csvArray[21] = $respArray[$r]['last_dt'];
                $csvArray[22] = empty($respArray[$r]['last_dt'])
                    ? null
                    : $respArray[$r]['survey_completed'];

                $csvArray[23] = "https://oassurvey.com/oas/?sv=" . $surveyIDHashed . "&ac=" . $respArray[$r]['resp_access_code'];
                //begin laying in survey data columns
                if (!is_null($respArray[$r]['start_dt']) && $isFull == 1) { //loop through only if survey data is present
                    unset($value);
                    unset($respAnswerArray_filtered);
                    //get survey answers
                    $value = $respArray[$r]['resp_id'];
                    $respAnswerArray_filtered = array_values(array_filter($respAnswerArray, function($ar) use ($value) { return ($ar["resp_id"] == $value); }));
                    $foreachCt = $maxRespIndex;
                    foreach ($questionIDArray as $value) {
                        unset($intersect_answer);
                        $intersect_answer = array_values(array_filter($respAnswerArray_filtered, function($ar) use ($value) { return ($ar["question_id"] == $value); }));
                        $csvArray[$foreachCt] = $intersect_answer[0]['answer_value'];
                        $foreachCt++;
                    } //end question id loop
                } // end if start date not null
            } // end if not header
            fputcsv($df, $csvArray);
        }// end $r loop (Respondent Array)
        fclose($df);
    }
}