<?php

use App\Classes\Account;
use App\Classes\Takesurvey;

include_once('init.php');
verifySession(60 * 60);
$accountID = getAccountID();
$function = $_GET['fct'];
if(function_exists ($function)) {
	$function();
}
function downloaddata() { //https://oassurvey.com/csv.php?sid=42&fct=test
	//important to start column index number after the end of the respondent columns. Currently 24 but may change
	$maxRespIndex = 24;
	global $accountID;
	$surveyID = $_GET['sid'];
	$isFull = $_GET['full'];
	$surveyIDHashed = hashit($surveyID,10); //for survey URLs
	verifySurvey($accountID, $surveyID);
	$DBH = new Takesurvey();
	$ABH = new Account();
	$labelArr = $ABH->get_field_labels($surveyID);
	$map = surveyMap($surveyID);
	$questionArray = $ABH->survey_questions($surveyID);
	$respArray = $ABH->survey_respondents($surveyID);
	if($isFull) { //resource heavy query. Only call on full download process
		$respAnswerArray = $DBH->question_id_answer_value_all($surveyID);
	}
	//echo "<pre>",print_r($respAnswerArray),"</pre>";
	//return;
	// loop through question map, rebuild condensed map with question ids in sequence
	$qCt = 0;
	for($m=0;$m<count($map);++$m) {
		unset($value);
		$value = $map[$m]['page_id'];
		$questions_filtered = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
		//Header Array of Survey Questions
		for($q=0;$q<count($questions_filtered);++$q) {
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
	//ob_start();
	download_send_headers("download_respondents_" . date("Y-m-d") . ".csv");
   	$df = fopen("php://output", 'w');
	for($r=-1;$r<count($respArray);$r++) {
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
			if($isFull == 1) {
				for($h=0;$h<count($headerArray);++$h) { //loop through ordered question ids
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
			if(empty($respArray[$r]['last_dt'])) {
				$csvArray[22] = null;
			} else {
				$csvArray[22] = $respArray[$r]['survey_completed'];
			}
			$csvArray[23] = "https://oassurvey.com/oas/?sv=". $surveyIDHashed . "&ac=" . $respArray[$r]['resp_access_code'];
			//begin laying in survey data columns
			if(!is_null($respArray[$r]['start_dt']) && $isFull == 1) { //loop through only if survey data is present
				unset($value);
				unset($respAnswerArray_filtered);
				//get survey answers
				$value = $respArray[$r]['resp_id'];
				$respAnswerArray_filtered = array_values(array_filter($respAnswerArray, function($ar) use ($value) { return ($ar["resp_id"] == $value); }));
				$foreachCt = $maxRespIndex;
				foreach($questionIDArray as $value) {
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
/*
function TEMPDISABLEdownloaddata() { //https://oassurvey.com/csv.php?sid=42&fct=test
	//important to start column index number after the end of the respondent columns. Currently 24 but may change
	$maxRespIndex = 24;
	global $accountID;
	$surveyID = $_GET['sid'];
	$isFull = $_GET['full'];
	$surveyIDHashed = hashit($surveyID,10); //for survey URLs
	verifySurvey($accountID, $surveyID);
	$DBH = new Takesurvey();
	$ABH = new Account();
	$map = surveyMap($surveyID);
	$questionArray = $ABH->survey_questions($surveyID);
	$respArray = $ABH->survey_respondents($surveyID);
	if($isFull) { //resource heavy query. Only call on full download process
		$respAnswerArray = $DBH->question_id_answer_value_all($surveyID);
	}
	//echo "<pre>",print_r($respAnswerArray),"</pre>";
	//return;
	// loop through question map, rebuild condensed map with question ids in sequence
	$qCt = 0;
	for($m=0;$m<count($map);++$m) {
		unset($value);
		$value = $map[$m]['page_id'];
		$questions_filtered = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
		//Header Array of Survey Questions
		for($q=0;$q<count($questions_filtered);++$q) {
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
	for($r=-1;$r<count($respArray);$r++) {
		//add column headings first on -1 line
		if($r == -1) {
			$csvArray[$r][0] = "ACCESS CODE";
			$csvArray[$r][1] = "EMAIL ADDRESS";
			$csvArray[$r][2] = "FIRST NAME";
			$csvArray[$r][3] = "LAST NAME";
			$csvArray[$r][4] = "RESP ALT";
			$csvArray[$r][5] = "LAWYER #";
			$csvArray[$r][6] = "CATEGORY";
			$csvArray[$r][7] = "JOB TITLE";
			$csvArray[$r][8] = "HOME DEPT";
			$csvArray[$r][9] = "FTE";
			$csvArray[$r][10] = "DEPT CITY";
			$csvArray[$r][11] = "CUSTOM 7";
			$csvArray[$r][12] = "CUSTOM 8";
			$csvArray[$r][13] = "CUSTOM 9";
			$csvArray[$r][14] = "CUSTOM 10";
			$csvArray[$r][15] = "CUSTOM 11";
			$csvArray[$r][16] = "CUSTOM 12";
			$csvArray[$r][17] = "COMPENSATION";
			$csvArray[$r][18] = "BENEFITS";
			$csvArray[$r][19] = "COMP-BENEFITS";
			$csvArray[$r][20] = "START DT";
			$csvArray[$r][21] = "LAST DT";
			$csvArray[$r][22] = "COMPLETED";
			$csvArray[$r][23] = "SURVEY URL";
			//add question headers
			if($isFull == 1) {
				for($h=0;$h<count($headerArray);++$h) { //loop through ordered question ids
					$csvArray[$r][$h+$maxRespIndex] = $headerArray[$h][0] . " " . $headerArray[$h][1];
				}
			}
		//continue with rest of file
		} else { //end if header
			$csvArray[$r][0] = $respArray[$r]['resp_access_code'];
			$csvArray[$r][1] = $respArray[$r]['resp_email'];
			$csvArray[$r][2] = $respArray[$r]['resp_first'];
			$csvArray[$r][3] = $respArray[$r]['resp_last'];
			$csvArray[$r][4] = $respArray[$r]['resp_alt'];
			$csvArray[$r][5] = $respArray[$r]['cust_1'];
			$csvArray[$r][6] = $respArray[$r]['cust_2'];
			$csvArray[$r][7] = $respArray[$r]['cust_3'];
			$csvArray[$r][8] = $respArray[$r]['cust_4'];
			$csvArray[$r][9] = $respArray[$r]['cust_5'];
			$csvArray[$r][10] = $respArray[$r]['cust_6'];
			$csvArray[$r][11] = null;
			$csvArray[$r][12] = null;
			$csvArray[$r][13] = null;
			$csvArray[$r][14] = null;
			$csvArray[$r][15] = null;
			$csvArray[$r][16] = null;
			$csvArray[$r][17] = null;
			$csvArray[$r][18] = null;
			$csvArray[$r][19] = null;
			$csvArray[$r][20] = $respArray[$r]['start_dt'];
			$csvArray[$r][21] = $respArray[$r]['last_dt'];
			if(empty($respArray[$r]['last_dt'])) {
				$csvArray[$r][22] = null;
			} else {
				$csvArray[$r][22] = $respArray[$r]['survey_completed'];
			}
			$csvArray[$r][23] = "https://oassurvey.com/oas/?sv=". $surveyIDHashed . "&ac=" . $respArray[$r]['resp_access_code'];
			//begin laying in survey data columns
			if(!is_null($respArray[$r]['start_dt']) && $isFull == 1) { //loop through only if survey data is present and is full download
				unset($value);
				unset($respAnswerArray_filtered);
				//get survey answers
				$value = $respArray[$r]['resp_id'];
				$respAnswerArray_filtered = array_values(array_filter($respAnswerArray, function($ar) use ($value) { return ($ar["resp_id"] == $value); }));
				$foreachCt = $maxRespIndex;
				//for($q=0;$q<count($questionIDArray);++$q) { //loop through ordered question ids
				foreach($questionIDArray as $value) {
					//unset($value);
					//$value = $questionIDArray[$q];
					unset($intersect_answer);
					$intersect_answer = array_values(array_filter($respAnswerArray_filtered, function($ar) use ($value) { return ($ar["question_id"] == $value); }));
					//$csvArray[$r][$foreachCt] = $intersect_answer[0]['answer_value'];
					$foreachCt++;
					//$csvArray[$r][$q+$maxRespIndex] = $intersect_answer[0]['answer_value'];
				} //end question id loop
			} // end if start date not null
		} // end if not header
	}// end $r loop (Respondent Array)
	
	//Generate CSV File
	download_send_headers("download_respondents_" . date("Y-m-d") . ".csv");
	echo array2csv($csvArray);
	die();
}
*/
function getQuestionCode($value, $qArray) {
	if(empty($value)) {
		return false;
	}
	$qArray_filtered = array_values(array_filter($qArray, function($ar) use ($value) { return ($ar["question_id"] == $value); }));
	return $qArray_filtered[0]['question_code'] . ".";
}

/*
function downloaddata---OLDXXXXXXXXXXXXXXXXXXXXXX() {
	global $accountID;
	$surveyID = $_GET['sid'];
	verifySurvey($accountID, $surveyID);
	$surveyIDHashed = hashit($surveyID,10); //for survey URLs
	$DBH = new Takesurvey();
	$ABH = new Account();
	
	$respArray = $ABH->survey_respondents($surveyID);
	$map = surveyMap($surveyID);
	//echo "<pre>",print_r($map),"</pre>";
	//return;
	$questionArray = $ABH->survey_questions($surveyID);
	//echo "<pre>",print_r($questionArray),"</pre>";
	//return;
	$respAnswerArray = $DBH->question_id_answer_value_all($surveyID);
	// loop through question map, rebuild condensed map with question ids in sequence
	$qCt = 0;
	for($m=0;$m<count($map);++$m) {
		unset($value);
		$value = $map[$m]['page_id'];
		$questions_filtered = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
		for($q=0;$q<count($questions_filtered);++$q) {
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
			$headerArray[$qCt][0] = $questions_filtered[$q]['question_id'];
			$headerArray[$qCt][1] = $pid[$q] . " ";
			$headerArray[$qCt][2] = $questions_filtered[$q]['question_desc'];
			++$qCt;
		} // end $q loop
	} // end $m loop
	//echo "<pre>",print_r($headerArray),"</pre>";
	//return;
	// loop through respondents
	for($i=0;$i<count($respArray);$i++) {
	//for($i=0;$i<25;$i++) {
		$csvArray[$i]['ACCESS CODE'] = $respArray[$i]['resp_access_code'];
		$csvArray[$i]['EMAIL ADDRESS'] = $respArray[$i]['resp_email'];
		$csvArray[$i]['FIRST NAME'] = $respArray[$i]['resp_first'];
		$csvArray[$i]['LAST NAME'] = $respArray[$i]['resp_last'];
		$csvArray[$i]['ALT TEXT'] = $respArray[$i]['resp_alt'];
		$csvArray[$i]['CUSTOM 1'] = $respArray[$i]['cust_1'];
		$csvArray[$i]['CUSTOM 2'] = $respArray[$i]['cust_2'];
		$csvArray[$i]['CUSTOM 3'] = $respArray[$i]['cust_3'];
		$csvArray[$i]['CUSTOM 4'] = $respArray[$i]['cust_4'];
		$csvArray[$i]['CUSTOM 5'] = $respArray[$i]['cust_5'];
		$csvArray[$i]['CUSTOM 6'] = $respArray[$i]['cust_6'];
		$csvArray[$i]['STARTED'] = $respArray[$i]['start_dt'];
		$csvArray[$i]['LAST UPDATE'] = $respArray[$i]['last_dt'];
		if(is_null($respArray[$i]['start_dt'])) {
			$csvArray[$i]['SURVEY COMPLETED'] = "";
		} else {
			$csvArray[$i]['SURVEY COMPLETED'] = $respArray[$i]['survey_completed'];
		}
		$csvArray[$i]['SURVEY_URL'] = "https://oassurvey.com/oas/?sv=". $surveyIDHashed . "&ac=" . $respArray[$i]['resp_access_code'];
		if(!is_null($respArray[$i]['start_dt']) || $i == 0) { //loop through only if response found, or if first iteration. Need first in order to lay in the column headings
			unset($value);
			unset($respAnswerArray_filtered);
			//get survey answers
			$value = $respArray[$i]['resp_id'];
			$respAnswerArray_filtered = array_values(array_filter($respAnswerArray, function($ar) use ($value) { return ($ar["resp_id"] == $value); }));
			for($q=0;$q<count($headerArray);++$q) { //loop through ordered question ids
				unset($value);
				$value = $headerArray[$q][0];
				unset($intersect_answer);
				$intersect_answer = array_values(array_filter($respAnswerArray_filtered, function($ar) use ($value) { return ($ar["question_id"] == $value); }));
				$csvArray[$i][$headerArray[$q][1] . $headerArray[$q][2]] = $intersect_answer[0]['answer_value'];
			} //end question id loop
		} // end if start date not null
	} // end $i loop
	download_send_headers("download_respondents_" . date("Y-m-d") . ".csv");
	echo array2csv($csvArray);
	die();
}
*/