<?php

use App\Classes\Report;
use App\Classes\Takesurvey;

require_once('fctFunctions.php');

function repind() {
	global $accountID;
	$RBH = new Report();
	$surveyID = $_GET['sid'];
	
	//echo "<pre>", print_r(surveyMap($surveyID)), "</pre>";
	
	$surveyInfo = $RBH->survey_info($surveyID);
	$respArray = $RBH->get_all_individuals($surveyID, 0);
	//if form subbmitted
	if(!empty($_POST['slctResp'])) {
		$respInfo = $RBH->get_individual($_POST['slctResp'], $surveyID);
		$indTable = getIndividual($_POST['slctResp'], $surveyID, $respInfo["resp_total_compensation"]);
		//echo "<pre>",print_r($respInfo),"</pre>";
		//build
		$respInfoTable .= "<div style='margin-bottom:25px;'><table width='100%'>";
		
		$respInfoTable .= "<tr>";
		$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:left;'>Employee Name:</td>";
		$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:right; font-weight:bold;'>" . $respInfo["resp_first"] . " " . $respInfo["resp_last"] . "</td>";
		$respInfoTable .= "</tr>";
		
		$respInfoTable .= "<tr>";
		$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:left;'>Survey Date:</td>";
		$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:right; font-weight:bold;'>" . $respInfo["last_dt"] . "</td>";
		$respInfoTable .= "</tr>";
		
		$respInfoTable .= "<tr>";
		$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:left;'>Access Code:</td>";
		$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:right; font-weight:bold;'>" . $respInfo["resp_access_code"] . "</td>";
		$respInfoTable .= "</tr>";
		
		$respInfoTable .= "<tr>";
		$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:left;'>E-mail Address:</td>";
		$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:right; font-weight:bold;'>" . $respInfo["resp_email"] . "</td>";
		$respInfoTable .= "</tr>";
		
		//custom fields
		for($c=1;$c<12;++$c) {
			if(!empty($surveyInfo["cust_" . $c . "_label"])) { //get only defined labels
				$respInfoTable .= "<tr>";
				$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:left;'>" . $surveyInfo["cust_" . $c . "_label"] . ":</td>";
				$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:right; font-weight:bold;'>" . $respInfo["cust_" . $c] . "</td>";
				$respInfoTable .= "</tr>";
			}
		}
		
		$respInfoTable .= "<tr>";
		$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:left;'>Compensation:</td>";
		$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:right; font-weight:bold;'>$" . number_format($respInfo["resp_compensation"],0) . "</td>";
		$respInfoTable .= "</tr>";
		
		$respInfoTable .= "<tr>";
		$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:left;'>Benefit %</td>";
		$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:right; font-weight:bold;'>" . number_format($respInfo["resp_benefit_pct"]*100,2) . "%</td>";
		$respInfoTable .= "</tr>";
		
		$respInfoTable .= "<tr>";
		$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:left;'>Total Compensation:</td>";
		$respInfoTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:right; font-weight:bold;'>$" . number_format($respInfo["resp_total_compensation"],0) . "</td>";
		$respInfoTable .= "</tr>";
		
		$respInfoTable .= "</table></div>";
	} //end if $post submit
	//echo "<pre>",print_r($surveyInfo),"</pre>";
	
	//build drop down selection of respondents
	$respSelect .= "<select class='form-control' name='slctResp' id='idSlctResp'>";
	$respSelect .= "<option value=''> - SELECT INDIVIDUAL - </option>";
	for($r=0;$r<count($respArray);++$r) {
		if($_POST['slctResp'] == $respArray[$r]['resp_id']) {
			$isSelected = " selected ";
		} else {
			$isSelected = "";
		}
		$respSelect .= "<option value='" . $respArray[$r]['resp_id'] . "'" . $isSelected . ">" . $respArray[$r]['resp_last'] . ", " . $respArray[$r]['resp_first'] . " (" . $respArray[$r]['custom'] . ")</option>\n";
	}
	$respSelect .= "</select>";
	//get navigation bar
	assessmentTabs($surveyID, 10);
	
	echo "<form name='frmInd' method='post'>";
	echo "<div class='row' style='margin-bottom:25px;'>";
	echo "<div class='col-sm-3'><span class='blue largetext'>Select a respondent:</span></div>\n";
	echo "<div class='col-sm-9'>" . $respSelect . "</div>\n";
	echo "</div>"; //end row
	echo "</form>";
	echo "<table class='report-mainheader'>";
	echo "<tr>";
	echo "<td>";
	echo "<img src='" . $surveyInfo["logo_survey"] . "' width='130' />";
	echo "</td>";
	echo "<td>";
	echo "<div class='report-survey-name'>" . $surveyInfo["survey_name"] . "</div>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>";
	echo "<span class='strong'>Individual Report</span>";
	echo "</td>";
	echo "<td>";
	echo "<div class='report-survey-name' style='font-size:14px;'></div>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo $respInfoTable;
	echo $indTable;
	?>
	<script>
    $(document).ready(function() {
      $('#idSlctResp').on('change', function() {
         document.forms['frmInd'].submit();
      });
    });
    </script>
    <?php
}
function getIndividual($respID, $surveyID, $totalCompensation) {
	$DBH = new Takesurvey();
	$respAnswerArray = $DBH->question_id_answer_value_not_zero($respID);
	//echo "Resp Answer Array: <pre>",print_r($respAnswerArray),"</pre>";
	$map = surveyMap($surveyID);
	$sumCt = 0;
	for($q=0;$q<count($respAnswerArray);++$q){
		$respAnswerQuestionIDArray[$q] = $respAnswerArray[$q]['question_id']; //make question id array one dimensional for in_array
	}
	for($a=0; $a<count($map);++$a) {
		//rebuild map, reduced down to just relevant pages that respondent provided answers to. Each record in summary represents a single page
		if((in_array($map[$a]['question_id_parent'], $respAnswerQuestionIDArray) || $map[$a]['question_id_parent'] == 0) && $map[$a]['page_type'] !=2) {
			$summary[$sumCt]['page_id'] = $map[$a]['page_id'];
			$summary[$sumCt]['page_type'] = $map[$a]['page_type'];
			$summary[$sumCt]['question_id_parent'] = $map[$a]['question_id_parent'];
			$summary[$sumCt]['level'] = $map[$a]['level'];
			$summary[$sumCt]['question_id_parent_2'] = $map[$a]['question_id_parent_2'];
			$summary[$sumCt]['question_id_parent_3'] = $map[$a]['question_id_parent_3'];
			$summary[$sumCt]['question_id_parent_4'] = $map[$a]['question_id_parent_4'];
			$summary[$sumCt]['question_id_parent_5'] = $map[$a]['question_id_parent_5'];
			$pageIDArray[$sumCt] = $map[$a]['page_id']; //create single dimension of page IDs to get question descs for summary
			$sumCt++;
		}
	}
	//echo "Page ID Array:<pre>",print_r($pageIDArray),"</pre>";
	//echo "Summary Array:<pre>",print_r($summary),"</pre>";
	$questionDescArray = $DBH->all_question_desc_for_summary($surveyID, $pageIDArray);
	//echo "<pre>",print_r($questionDescArray),"</pre>";

	$indTable = "<div>";
	$indTable .= "<table style='width:100%;'>";
		
		
		//get question ids of legal and support from page_id top of summary array
		unset($value);
		$value = $summary[0]["page_id"];
		$toplevelArr = array_values(array_filter($questionDescArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
		$qidLegal = $toplevelArr[0]["question_id"];
		$qidSupport = $toplevelArr[1]["question_id"];
		//echo "<pre>",$qidLegal,"-", $qidSupport, "</pre>";

		
		
		//get Legal Hours
		unset($value);
		$value = $qidLegal;
		$respAnswer_legal = array_values(array_filter($respAnswerArray, function($ar) use ($value) { return ($ar["question_id"] == $value); }));
		$hoursLegal = count($respAnswer_legal) ? $respAnswer_legal[0]["answer_value"] : 0;
		if (empty($hoursLegal)) {
			$hoursLegal = 0;
		}
		
		//get Support Hours
		unset($value);
		$value = $qidSupport;
		$respAnswer_support = array_values(array_filter($respAnswerArray, function($ar) use ($value) { return ($ar["question_id"] == $value); }));
		$hoursSupport = $respAnswer_support[0]["answer_value"];
		if(empty($hoursSupport)) {
			$hoursSupport = 0;
		}
		$hoursTotal = $hoursLegal + $hoursSupport;
		if($hoursTotal >0) {
			$pctLegal = $hoursLegal / $hoursTotal;
			$pctSupport = $hoursSupport / $hoursTotal;
			//$salaryLegal = $totalCompensation * $pctLegal;
			//$salarySupport = $totalCompensation * $pctSupport;
			$percent[$qidLegal] = $pctLegal;
			$percent[$qidSupport] = $pctSupport;
			$hours[$qidLegal] = $hoursLegal;
			$hours[$qidSupport] = $hoursSupport;
			$salary[$qidLegal] = $totalCompensation * $pctLegal; //the salary for legal and support is a simple array $salary with the question_id as the key
			$salary[$qidSupport] = $totalCompensation * $pctSupport;
		}
		
		//echo $salaryLegal . " - " . $salarySupport;
		

	for($s=0;$s<count($summary);++$s) {
		unset($value);
		unset($pageHeading);
		$value = $summary[$s]['question_id_parent'];
		$pageHeading = array_values(array_filter($questionDescArray, function($ar) use ($value) { return ($ar["question_id"] == $value); }));
		if($summary[$s]['page_type'] == 0) {
			$colHeader = "Hours";
			$char = "";
			$pageDesc = "Branch";
		} else {
			$colHeader = "Pct";
			$char = "%";
			$pageDesc = $pageHeading[0]['question_desc'];
		}
		unset($value);
		unset($questions_filtered);
		$value = $summary[$s]['page_id'];
		$questions_filtered = array_values(array_filter($questionDescArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
		$indTable .= "<tr style='background:#E1E1E1; color:#369;'><td style='padding-left: " . ($summary[$s]['level']-3)*40 . "px;'>$pageDesc</td><td style='text-align:right; width:70px;'>Percent</td><td style='text-align:right; width:70px;'>Hours</td><td style='text-align:right; width:100px;'>Cost To Firm</td></tr>";
		//echo "<pre>", print_r($questions_filtered),"</pre>";

		//$pqid creates a small array of all answers from parent questions for this page, based on the question_id_parent. Will use the parent answers to multiply against the current answer to come up with the hours and salary for the question
		unset($pqid);
		for($pq=1;$pq<=5;++$pq) {
			unset($value);
			if($pq > 1) {
				$pqappend = "_" . $pq;
			} else {
				$pqappend = "";
			}
			$value = $summary[$s]['question_id_parent' . $pqappend];
			unset($parentAnswers_filtered);
			$parentAnswers_filtered = array_values(array_filter($respAnswerArray, function($ar) use ($value) { return ($ar["question_id"] == $value); }));
			//echo "<pre>",print_r($parentAnswers_filtered),"</pre>";
			if(!empty($parentAnswers_filtered[0]['answer_value'])) {
				if($parentAnswers_filtered[0]['question_id'] == $qidLegal) {
					$pqid[] = $pctLegal;
				} elseif($parentAnswers_filtered[0]['question_id'] == $qidSupport) {
					$pqid[] = $pctSupport;
				} else {
					$pqid[] = $parentAnswers_filtered[0]['answer_value'] / 100;
				}
			}
		}

		for($q=0;$q<count($questions_filtered);++$q) { //loop through all questions on this particular page
			unset($value);
			unset($respAnswerArray_filtered);
			unset($get_percent);
			unset($get_hours);
			unset($get_salary);
			unset($multiplier);
			unset($level);
			$value = $questions_filtered[$q]['question_id'];
			$respAnswerArray_filtered = array_values(array_filter($respAnswerArray, function($ar) use ($value) { return ($ar["question_id"] == $value); }));
			$get_percent = count($respAnswerArray_filtered) ? $respAnswerArray_filtered[0]['answer_value'] : 0; //gets raw percentage (or hours if first level) respondent entered into survey
			if(empty($get_percent)) {
				$get_hours = 0;
				$get_salary = 0;
				$get_percent = 0;
			} else {
				$get_percent = $get_percent / 100;
				$level = $summary[$s]['level'];

				if($level==1 && $summary[$s]['page_type'] == 0) {
					$get_percent = $percent[$questions_filtered[$q]["question_id"]];
					$get_hours = $hours[$questions_filtered[$q]["question_id"]];
					$get_salary = $salary[$questions_filtered[$q]["question_id"]];
				} else {
					//echo $questions_filtered[$q]['question_desc'], "<pre>",print_r($pqid),"</pre>";
					$get_salary = $totalCompensation;
					$get_hours = $hoursTotal;
					for($pq=0;$pq<count($pqid);++$pq) {
						$get_salary = $get_salary * $pqid[$pq];
						$get_hours = $get_hours * $pqid[$pq];
					}
						$get_hours = $get_hours * $get_percent;
						$get_salary = $get_salary * $get_percent;
				}
			}
			$indTable .= "<tr>\n";
			$indTable .= "<td style='border-bottom:1px solid #CCCCCC; padding-left: " . ($summary[$s]['level']-2)*40 . "px;'>" . $questions_filtered[$q]['question_desc'] . "</td>\n";
			$indTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:right;'>" . number_format($get_percent * 100,0) . "%</td>\n";
			$indTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:right;'>" . number_format($get_hours,0) . "</td>\n";
			$indTable .= "<td style='border-bottom:1px solid #CCCCCC; text-align:right;'>$" . number_format($get_salary,0) . "</td>\n";
			$indTable .= "</tr>\n";
		}//end questions_filtered loop
	}//end summary (page) loop
	$indTable .= "</table>";
	$indTable .= "</div>";
	return $indTable;
}
?>
