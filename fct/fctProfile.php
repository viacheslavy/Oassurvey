<?php
function rsprofile() {
	global $accountID;
	$RBH = new Report();
	$surveyID = $_GET['sid'];
	$surveyInfo = $RBH->survey_info($surveyID);
	
	for($p=2;$p<=6;++$p) {
		$reportTable[$p] = drawProfileTable($RBH->report_profile($surveyID, "cust_" . $p), $surveyInfo["cust_" . $p . "_label"]);
	}
	
	//echo "<pre>",print_r($surveyInfo),"</pre>";
	
	assessmentTabs($surveyID, 9);
	
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
	echo "<span class='strong'>Respondent Profile</span>";
	echo "</td>";
	echo "<td>";
	echo "<div class='report-survey-name' style='font-size:14px; font-weight:normal;'># Responding: " . $surveyInfo["resp_ct"] . "</div>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	for($p=2;$p<=6;++$p) {
		echo $reportTable[$p];
	}
}
function drawProfileTable($result, $reportTitle) {
	$surveyID = $_GET['sid'];
	
	$sumSalary = 0;
	$sumHours = 0;
	foreach($result as $num => $values) {
		//$sumSalary += $values['salary'];
		$sumHours += $values['ct'];
	}
	//if($sumHours > 0) {
	//	$sumHourly = $sumSalary / $sumHours;
	//}
	$reportTable .= "<div class='report-header blue strong' style='margin-top:25px;'>" . $reportTitle . "</div>";
	$reportTable .= "<table class='report-table' style='margin-top:15px;'>";
	$reportTable .= "<tr>\n";
	$reportTable .= "<td><div class='strong italic'>Answering: " . $result[0]['count'] . "</div></td>\n";
	$reportTable .= "<td></td>\n";
	$reportTable .= "<td class='report-td-calcs blue strong'>Count</td>\n";
	$reportTable .= "<td class='report-td-calcs blue strong'>Percent</td>\n";
	$reportTable .= "</tr>\n";
	for($q=0;$q<count($result);++$q) {
		$hours[$q] = $result[$q]['ct'];
		//$salary[$q] = $result[$q]['salary'];
		//if($sumSalary > 0) {
		//	$pct[$q] = $salary[$q] / $sumSalary;
		//}
		if($sumHours > 0) {
			$pct[$q] = $hours[$q] / $sumHours;
		}
		$sumPct += $pct[$q];
		/*
		if($result[$q]['page_id']) { //if there is a subset of questions, display drill down link
			$aopen[$q] = "<a href='?rq=repuw&sid=$surveyID&pid=" . $result[$q]['page_id'] . "'>";
			$abarlink[$q] = "<a class='report-link' href='?rq=repuw&sid=$surveyID&pid=" . $result[$q]['page_id'] . "'>";
			$aclose[$q] = "</a>";
		}
		*/
		$reportTable .= "<tr>\n";
		$reportTable .= "<td>" . $result[$q]['item'] . "</td>\n";
		$reportTable .= "<td class='report-td-chart'>";
		$reportTable .= "<div class='report-div-container'>";
		$reportTable .= "<div class='report-div-slice'>&nbsp;</div>";
		$reportTable .= "<div class='report-div-slice'>&nbsp;</div>";
		$reportTable .= "<div class='report-div-slice'>&nbsp;</div>";
		$reportTable .= "<div class='report-div-slice'>&nbsp;</div>";
		$reportTable .= "<div class='report-div-slice'>&nbsp;</div>";
		if($pct[$q] > 0) { //only show bar if data present
			$reportTable .= "<div class='report-div-barchart' style='width:" . number_format($pct[$q]*100) . "%;'>" . $abarlink[$q] . "&nbsp;" . $aclose[$q] . "</div>";
		}
		$reportTable .= "</div>";
		$reportTable .= "</td>\n";
		$reportTable .= "<td class='report-td-calcs'>" . number_format($hours[$q]) . "</td>\n";
		$reportTable .= "<td class='report-td-calcs'>" . number_format($pct[$q]*100,1) . "%</td>\n";
		$reportTable .= "</tr>\n";
	} //end question loop

	
	
	$reportTable .= "<tr>\n";
	$reportTable .= "<td class='strong'>TOTAL:</td>\n";
	$reportTable .= "<td></td>\n";
	$reportTable .= "<td class='report-td-calcs strong'>" . number_format($sumHours) . "</td>\n";
	$reportTable .= "<td class='report-td-calcs strong'>" . number_format($sumPct*100) . "%</td>\n";
	$reportTable .= "</tr>\n";
	$reportTable .=  "</table>\n\n";
	return $reportTable;
}
?>
