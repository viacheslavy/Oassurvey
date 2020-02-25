<?php

require 'vendor/autoload.php';

use App\Classes\Account;
use App\Classes\Takesurvey;
use App\Classes\Session;
use App\Helper\PseudoCrypt;

$timeZoneDesc = "America/New_York";
date_default_timezone_set($timeZoneDesc); //eventually make the encapsulated time zone user defined
function surveyInfo($surveyID) {
	$DBH = new Takesurvey();
	return $DBH->survey_info($surveyID);
}
function verifySurvey($accountID, $surveyID) {
	$DBH = new Account();
	$verify = $DBH->single_survey($accountID, $surveyID);
	if($verify == false) {
		logoutUser();
	}
}
function surveySettings($surveyID) {
	$DBH = new Takesurvey();
	return $DBH->survey_settings($surveyID);
}
function singleRespondentArray($surveyID, $accessCode) {
	$DBH = new Takesurvey();
	return $DBH->single_respondent_array($surveyID, $accessCode);
}
function deleteRespondentSurvey($surveyID, $respID) {
	$DBH = new Takesurvey();
	return $DBH->delete_respondent_survey($surveyID, $respID);
}
function insertStartDT($surveyID, $respID) {
	$DBH = new Takesurvey();
	$DBH->insert_start_dt($surveyID, $respID);
}
function pageArray($surveyID, $pageSeq) {
	$DBH = new Takesurvey();
	$arr['page_id'] = $DBH->page_id($surveyID, $pageSeq);
	$arr['total_pages'] = $DBH->total_pages($surveyID);
	return $arr;
}
function filterText($value) {
	$filteredText = trim(filter_var($value,FILTER_SANITIZE_SPECIAL_CHARS));
	return $filteredText;
}
function filterMC($value) {
	$filteredText = substr($value,0,20);
	return $filteredText;
}
function filterSum($value) {
	$filteredText = round(substr($value,0,3),0);
	return $filteredText;
}
function containsSpecialCharacters($str) {
	// _-.@ not searched. 
	return preg_match('/[^0-9a-z-_@.]/i', $str);
}
function isValidEmail($strEmailAddress) {
	return filter_var($strEmailAddress, FILTER_VALIDATE_EMAIL);
}
function getRespID($surveyID, $respAccessCode) {
	$DBH = new Account();
	return $DBH->get_resp_id($surveyID, $respAccessCode);
}
function showModal($header, $body) {
?>
    <div class="modal fade" id="modalid" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title"><?php echo $header; ?></h5>
              </div>
              	<?php echo $body; ?>
            </div><!-- /.modal-content -->
        </div>
    </div>
<script>
$(document).ready(function(){
	$('#modalid').modal('show');
});
</script>
<?php
}
function showModalSimple($header, $message) {
?>
    <div class="modal fade" id="modalsimple" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title"><?php echo $header; ?></h5>
              </div>
              <div class='modal-body text-center'>
              <?php echo $message; ?>
              </div>
                <div class='modal-footer'>
                <button type='button' class='btn btn-primary' data-dismiss='modal'>OK</button>
                </div>
            </div><!-- /.modal-content -->
        </div>
    </div>
<script>
$(document).ready(function(){
	$('#modalsimple').modal('show');
});
</script>
<?php
}
function baseURL(){
	return $_SERVER['SERVER_NAME'];
  //return sprintf(
    //"%s://%s%s",
    //isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
	//$_SERVER['SERVER_NAME'],
    //$_SERVER['REQUEST_URI'] //for querystring
  //);
}
function generateRandomString($length)
{     
    $chars = '1A2BC3DE43G5HI6JK7LM8NO9PQ0RPT7VWXYZ';
    $result = '';
    for ($p = 0; $p < $length; $p++){
        $result .= ($p%2) ? $chars[mt_rand(19, 35)] : $chars[mt_rand(0, 18)];
    }
    return $result;
}
function hashit($val, $char) { #pass in an incremental ID, and number of characters in hash key
	return PseudoCrypt::hash($val, $char);
}
function unhashit($val) { #pass in the hashed value to decrypt to incremental ID
	return PseudoCrypt::unhash($val);
}
function progressBar($currentPage, $totalPages) {
	global $showProgressBar;
	if(empty($totalPages) || $showProgressBar == false) {
		return;
	}
	$pct = round($currentPage / $totalPages * 100,0);
echo "<div class='container'><!-- progress bar container -->\n";
echo "<div class='progress'>\n";
echo "<div class='progress-bar progress-bar-primary progress-bar-striped active' role='progressbar' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100' style='width:" . $pct . "%; white-space:nowrap; font-size:14px;'>\n";
echo "<strong>Progress: $pct%</strong>";
echo "</div>\n</div>\n</div><!-- end progress bar container -->\n\n\n";
}
function verifySession($secondsToTimeout) {
	Session::start();
	Session::verifySession($secondsToTimeout);
	$accountID = Session::get('oasAcctID');
	if($accountID == false) {
		header('Location: /signin/');
		exit();
	}
}
function array2csv(array &$array)
{
   if (count($array) == 0) {
     return null;
   }
   ob_start();
   $df = fopen("php://output", 'w');
   foreach ($array as $row) {
      fputcsv($df, $row);
   }
   fclose($df);
   return ob_get_clean();
}
function array2csvOLDXXXXXXXXXXXXXXXXXXXXX(array &$array)
{
   if (count($array) == 0) {
     return null;
   }
   ob_start();
   $df = fopen("php://output", 'w');
   fputcsv($df, array_keys(reset($array))); //column headings first
   foreach ($array as $row) {
      fputcsv($df, $row); //loop through array to lay in data rows
   }
   fclose($df);
   return ob_get_clean();
}
function download_send_headers($filename) { //for CSV downloads
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download  
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}
function getAccountID() {
	Session::start();
	$accountID = Session::get('oasAcctID');
	$accountID = unhashit($accountID);
	return $accountID;
}
function loginUser($accountID){
	Session::start();
	$hashAccountID = hashit($accountID, 8);
	Session::set('oasAcctID', $hashAccountID);
	//$DBH = new Signin();
	//$DBH->last_login_dt($accountID);
	header('Location:/acct/');
	exit();
}
function logoutUser(){
	Session::start();
	Session::destroy();
	header('Location:/signin/');
	exit();
}
function surveyMap($surveyID) {
	//function builds a map of the survey in order of which page is to be displayed
	$DBH = new Takesurvey();
	$pageArray = $DBH->survey_page_map($surveyID);
	$questionArray = $DBH->survey_question_map($surveyID);
	$mapCt = 0;
	//LEVEL ONE #######################################################################################################
	unset($value);
	$value = 0;
	$pageArray_1 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));
	$map = [];
	for($p1=0;$p1<count($pageArray_1);++$p1) {
		$map[$mapCt]['page_id'] = $pageArray_1[$p1]['page_id'];
		$map[$mapCt]['page_type'] = $pageArray_1[$p1]['page_type'];
		$map[$mapCt]['question_id_parent'] = 0;
		$map[$mapCt]['level'] = 1;
		$map[$mapCt]['question_id_parent_2'] = 0;
		$map[$mapCt]['question_id_parent_3'] = 0;
		$map[$mapCt]['question_id_parent_4'] = 0;
		$map[$mapCt]['question_id_parent_5'] = 0;
		$mapCt++;
		unset($value);
		$value = $pageArray_1[$p1]['page_id'];
		$questionArray_1 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
		for($q1=0;$q1<count($questionArray_1);++$q1) {
			//LEVEL TWO #######################################################################################################
			unset($value);
			$value = $questionArray_1[$q1]['question_id']; //get question id of parent question
			$pageArray_2 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));
			for($p2=0;$p2<count($pageArray_2);++$p2) {
				$map[$mapCt]['page_id'] = $pageArray_2[$p2]['page_id'];
				$map[$mapCt]['page_type'] = $pageArray_2[$p2]['page_type'];
				$map[$mapCt]['question_id_parent'] = $questionArray_1[$q1]['question_id'];
				$map[$mapCt]['level'] = 2;
				$map[$mapCt]['question_id_parent_2'] = 0;
				$map[$mapCt]['question_id_parent_3'] = 0;
				$map[$mapCt]['question_id_parent_4'] = 0;
				$map[$mapCt]['question_id_parent_5'] = 0;
				$mapCt++;
				unset($value);
				$value = $pageArray_2[$p2]['page_id'];
				$questionArray_2 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
				for($q2=0;$q2<count($questionArray_2);++$q2) {
					//LEVEL THREE #######################################################################################################
					unset($value);
					$value = $questionArray_2[$q2]['question_id']; //get question id of parent question
					$pageArray_3 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));			
					for($p3=0;$p3<count($pageArray_3);++$p3) {
						$map[$mapCt]['page_id'] = $pageArray_3[$p3]['page_id'];
						$map[$mapCt]['page_type'] = $pageArray_3[$p3]['page_type'];
						$map[$mapCt]['question_id_parent'] = $questionArray_2[$q2]['question_id'];
						$map[$mapCt]['level'] = 3;
						$map[$mapCt]['question_id_parent_2'] = $questionArray_1[$q1]['question_id'];
						$map[$mapCt]['question_id_parent_3'] = 0;
						$map[$mapCt]['question_id_parent_4'] = 0;
						$map[$mapCt]['question_id_parent_5'] = 0;
						$mapCt++;			
						unset($value);
						$value = $pageArray_3[$p3]['page_id'];
						$questionArray_3 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
						for($q3=0;$q3<count($questionArray_3);++$q3) {
							//LEVEL FOUR #######################################################################################################
							unset($value);
							$value = $questionArray_3[$q3]['question_id']; //get question id of parent question
							$pageArray_4 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));							
							for($p4=0;$p4<count($pageArray_4);++$p4) {
								$map[$mapCt]['page_id'] = $pageArray_4[$p4]['page_id'];
								$map[$mapCt]['page_type'] = $pageArray_4[$p4]['page_type'];
								$map[$mapCt]['question_id_parent'] = $questionArray_3[$q3]['question_id'];
								$map[$mapCt]['level'] = 4;
								$map[$mapCt]['question_id_parent_2'] = $questionArray_2[$q2]['question_id'];
								$map[$mapCt]['question_id_parent_3'] = $questionArray_1[$q1]['question_id'];
								$map[$mapCt]['question_id_parent_4'] = 0;
								$map[$mapCt]['question_id_parent_5'] = 0;
								$mapCt++;				
								unset($value);
								$value = $pageArray_4[$p4]['page_id'];
								$questionArray_4 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
								for($q4=0;$q4<count($questionArray_4);++$q4) {
									//LEVEL FIVE #######################################################################################################
									unset($value);
									$value = $questionArray_4[$q4]['question_id']; //get question id of parent question
									$pageArray_5 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));									
									for($p5=0;$p5<count($pageArray_5);++$p5) {
										$map[$mapCt]['page_id'] = $pageArray_5[$p5]['page_id'];
										$map[$mapCt]['page_type'] = $pageArray_5[$p5]['page_type'];
										$map[$mapCt]['question_id_parent'] = $questionArray_4[$q4]['question_id'];
										$map[$mapCt]['level'] = 5;
										$map[$mapCt]['question_id_parent_2'] = $questionArray_3[$q3]['question_id'];
										$map[$mapCt]['question_id_parent_3'] = $questionArray_2[$q2]['question_id'];
										$map[$mapCt]['question_id_parent_4'] = $questionArray_1[$q1]['question_id'];
										$map[$mapCt]['question_id_parent_5'] = 0;
										$mapCt++;
										unset($value);
										$value = $pageArray_5[$p5]['page_id'];
										$questionArray_5 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
										for($q5=0;$q5<count($questionArray_5);++$q5) {
											//LEVEL SIX #######################################################################################################
											unset($value);
											$value = $questionArray_5[$q5]['question_id']; //get question id of parent question
											$pageArray_6 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));
											for($p6=0;$p6<count($pageArray_6);++$p6) {
												$map[$mapCt]['page_id'] = $pageArray_6[$p6]['page_id'];
												$map[$mapCt]['page_type'] = $pageArray_6[$p6]['page_type'];
												$map[$mapCt]['question_id_parent'] = $questionArray_5[$q5]['question_id'];
												$map[$mapCt]['level'] = 6;
												$map[$mapCt]['question_id_parent_2'] = $questionArray_4[$q4]['question_id'];
												$map[$mapCt]['question_id_parent_3'] = $questionArray_3[$q3]['question_id'];
												$map[$mapCt]['question_id_parent_4'] = $questionArray_2[$q2]['question_id'];
												$map[$mapCt]['question_id_parent_5'] = $questionArray_1[$q1]['question_id'];
												$mapCt++;
												unset($value);
												$value = $pageArray_6[$p6]['page_id'];
												$questionArray_6 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
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
	return $map;
}