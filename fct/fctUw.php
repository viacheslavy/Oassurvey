<?php
function profile() {
	global $accountID;
	$RBH = new Report();
	$surveyID = $_GET['sid'];
	$surveyInfo = $RBH->survey_info($surveyID);
	$reportProfile = $RBH->report_profile($surveyID, "cust_6");
	
	echo "<pre>",print_r($reportProfile),"</pre>";
	
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
	echo "";
	echo "</td>";
	echo "<td>";
	echo "<div class='report-survey-name' style='font-size:14px; font-weight:normal;'># Responding: " . $surveyInfo["resp_ct"] . "</div>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
}

function repuw() {
	global $accountID;
	$RBH = new Report();
	$surveyID = $_GET['sid'];
	$pageID = $_GET['pid'];
	$surveyInfo = $RBH->survey_info($surveyID);
	//echo "Survey Info: <pre>", print_r($surveyInfo),"</pre>";
	//get map of entire survey
	$map = surveyMap($surveyID);
	//echo "<pre>", print_r($map),"</pre>";
	//return;
	$hoursPageID = $map[0]['page_id'];
	$hoursTitle = "Legal & Support";
	
	$filters = [];
	for($i=1;$i<7;$i++) {
		if(isset($_GET['cust'.$i])) {
			array_push($filters, $_GET['cust'.$i]);
		}
	}

	$currCust = $_GET['cust'];
	$crumbStart = "<span class='report-crumb'><a class='acrumb' href='?rq=repuw&sid=$surveyID&pid=" . $hoursPageID . "&filter=" . $currFilters ."&cust=".$currCust."'>" . $hoursTitle . "</a></span>";

	if(empty($pageID) || $pageID == $hoursPageID) {
		$pageID = $hoursPageID; //start from the beginning of the survey if no page marker found
		$isHoursPage = true;
	}
	unset($value);
	$value = $pageID; //get question id of parent question
	//filter down survey map to just the current page and get all parent elements of current page. allows up to 5 levels
	$map_filtered = array_values(array_filter($map, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
	//echo "Map Filtered: <pre>", print_r($map_filtered),"</pre>";
	$questionIDArray[0] = $map_filtered[0]['question_id_parent_5'];
	$questionIDArray[1] = $map_filtered[0]['question_id_parent_4'];
	$questionIDArray[2] = $map_filtered[0]['question_id_parent_3'];
	$questionIDArray[3] = $map_filtered[0]['question_id_parent_2'];
	$questionIDArray[4] = $map_filtered[0]['question_id_parent'];
	//$pageArr gets details of parent elements and current page. Includes question, question id, and page id
	$pageArr = $RBH->parent_pages($surveyID, $questionIDArray);
	//echo "Parent Pages: <pre>", print_r($pageArr),"</pre>";
	$currentPage = end($pageArr);
	$reportTitle = $currentPage["question_desc"];
	if($isHoursPage) {
		$reportTitle = $hoursTitle;
	}

	//get data from calcs
	$DBH = new Account();
	// GROUP BY clauses
	$group = [];
	
	/*if($_GET['filter'] == '') {
		$filters = [];
	} else {
		//WHERE clauses
		$filters = [''.$_GET['filter'].''];
	}*/

	//hit calcs
	$result = $DBH->calcs($surveyID, $pageID, NULL, $group, $filters);
	$sumSalaryTotal = array_sum(array_column($result,'salary')); //get total overall cost for current page
	$hasBranches = array_sum(array_column($result,'page_id')); //used for logic in showing distribtution. Ignore distribution if false
	
	for($i=0;$i<count($result);++$i) { //get sub page results to display under main page
		if($hasBranches == false) {
			break; //stop distribution if there are no branches
		}
		unset($subresult);

		//get page_id where question_id is the question_id_parent
		$result[$i]['page_id'] = $DBH->parent_page_id($result[$i]['question_id']);

		if($result[$i]['page_id']) {
			$subresult = $DBH->calcs($surveyID, $result[$i]['page_id'], NULL, $group, $filters);
		} else {
			//if there are no sub pages for question, then just repeat the overall info for the distribution table
			$subresult[0]['question_desc'] = $result[$i]['question_desc'];
			$subresult[0]['question_id'] = $result[$i]['question_id'];
			$subresult[0]['page_id'] = $result[$i]['page_id'];
			$subresult[0]['count'] = $result[$i]['count'];
			$subresult[0]['hours'] = $result[$i]['hours'];
			$subresult[0]['salary'] = $result[$i]['salary'];
		}
		if(array_sum(array_column($subresult,'salary'))) { //only show distribution if there is salary on the page
			$subTables .= drawSubTable($subresult, $result[$i]['question_desc'], $result[$i]['page_id'], $i+1, $sumSalaryTotal);
		}
	}
	
	$reportTable = drawTable($result, $reportTitle);
	//echo "Matt's Query, Calculated Results: <pre>", print_r($result),"</pre>";
	//echo "<pre>", print_r($result), "</pre>";

	for($p=0;$p<count($pageArr);++$p) {
		$currFilters = $_GET['filter'];
		$currCust = $_GET['cust'];
		$crumbtrail .= "<div class='report-crumb'><img src='/images/arrow-right.png' width='25' /></div><div class='report-crumb'><a  class='acrumb' href='?rq=repuw&sid=$surveyID&pid=" . $pageArr[$p]['page_id'] . "&filter=" . $currFilters . "&cust=".$currCust."'>" . $pageArr[$p]['question_desc'] . "</a></div>";
	}
	assessmentTabs($surveyID, 8);

	/*
	// FILTERS begin
	*/

		$saveCust1 = $_GET['cust1'];
		$saveCust2 = $_GET['cust2'];
		$saveCust3 = $_GET['cust3'];
		$saveCust4 = $_GET['cust4'];
		$saveCust5 = $_GET['cust5'];
		$saveCust6 = $_GET['cust6'];

		unset($_GET['cust1'],$_GET['cust2'],$_GET['cust3'],$_GET['cust4'],$_GET['cust5'],$_GET['cust6']);

		$query = $_GET;
		// rebuild url
		$query_result = http_build_query($query);
		// new link
		$url = $_SERVER['PHP_SELF'] . '?' . $query_result;

		echo "<br><br>";

		$custLabels = $DBH->survey_custs($surveyID);

		$saveCust1e = explode('"',$saveCust1)[1];
		$saveCust2e = explode('"',$saveCust2)[1];
		$saveCust3e = explode('"',$saveCust3)[1];
		$saveCust4e = explode('"',$saveCust4)[1];
		$saveCust5e = explode('"',$saveCust5)[1];
		$saveCust6e = explode('"',$saveCust6)[1];

		$numRespondingWithFilters = $RBH->resp_by_custs($surveyID, $saveCust1e, $saveCust2e, $saveCust3e, $saveCust4e, $saveCust5e, $saveCust6e);

		echo "<!-- Begin Filter Options -->";
		echo "<div class='row'>";
		echo "  <div class='col-sm-12'>";
		echo "  <!--<span class='btn btn-link strong' id='show-filters'>+ Show Report Filter Options</span><br /><br />-->";
		echo "  </div>";
		echo "</div> <!-- End Row -->";
		echo "<div id='filter-panel'>";
		echo "  <div class='row form-inline'>";
		echo "    <div class='well'>";
		echo "    <p class='blue' style='font-size:18px; font-weight:300;'>I want to see how the following personnel are utilized:</p>";
		/*echo "<select style='width: 16%;' id='ddFilter1' onchange='window.location = \"".$url."\"+$(\"#ddFilter1\").val()+$(\"#ddFilter2\").val()+$(\"#ddFilter3\").val()+$(\"#ddFilter4\").val()+$(\"#ddFilter5\").val()+$(\"#ddFilter6\").val();' class='form-control' name='ddFilter'>";
		
			$filters = $DBH->survey_filters($_GET['sid'],1);

			echo "<option value=''> - By ".$custLabels[0]['cust_1_label']." - </option>";
    		echo "<option value=''>None</option>";

    		foreach($filters as $row) {
    			if($row['filter'] != '') {
    				$test = $row['type'].' = "'.$row['filter'] . '"';
    				if($saveCust1 == $test) {
    					$selected = 'selected';
    				} else {
    					$selected = '';
    				}
    				$urlFilter = urlencode($row['type']." = \"".$row['filter']);
    				echo"<option ".$selected." value='&cust1=".$urlFilter."\"'>".$row['filter']."</option>";
    			}
    		}

		echo "    </select>&nbsp;";*/
		echo "<select style='width: 19%;' id='ddFilter2' onchange='window.location = \"".$url."\"+$(\"#ddFilter1\").val()+$(\"#ddFilter2\").val()+$(\"#ddFilter3\").val()+$(\"#ddFilter4\").val()+$(\"#ddFilter5\").val()+$(\"#ddFilter6\").val();' class='form-control' name='ddFilter'>";

		$filters = $DBH->survey_filters($_GET['sid'],2);

			echo "<option value=''> - By ".$custLabels[0]['cust_2_label']." - </option>";
    		echo "<option value=''>None</option>";

    		foreach($filters as $row) {
    			if($row['filter'] != '') {
    				$test = $row['type'].' = "'.$row['filter'] . '"';
    				if($saveCust2 == $test) {
    					$selected = 'selected';
    				} else {
    					$selected = '';
    				}
    				$urlFilter = urlencode($row['type']." = \"".$row['filter']);
    				echo"<option ".$selected." value='&cust2=".$urlFilter."\"'>".$row['filter']."</option>";
    			}
    		}

		echo "    </select>&nbsp;";
		echo "<select style='width: 19%;' id='ddFilter3' onchange='window.location = \"".$url."\"+$(\"#ddFilter1\").val()+$(\"#ddFilter2\").val()+$(\"#ddFilter3\").val()+$(\"#ddFilter4\").val()+$(\"#ddFilter5\").val()+$(\"#ddFilter6\").val();' class='form-control' name='ddFilter'>";
		
		$filters = $DBH->survey_filters($_GET['sid'],3);

			echo "<option value=''> - By ".$custLabels[0]['cust_3_label']." - </option>";
    		echo "<option value=''>None</option>";

    		foreach($filters as $row) {
    			if($row['filter'] != '') {
    				$test = $row['type'].' = "'.$row['filter'] . '"';
    				if($saveCust3 == $test) {
    					$selected = 'selected';
    				} else {
    					$selected = '';
    				}
    				$urlFilter = urlencode($row['type']." = \"".$row['filter']);
    				echo"<option ".$selected." value='&cust3=".$urlFilter."\"'>".$row['filter']."</option>";
    			}
    		}

		echo "    </select>&nbsp;";
		echo "<select style='width: 19%;' id='ddFilter4' onchange='window.location = \"".$url."\"+$(\"#ddFilter1\").val()+$(\"#ddFilter2\").val()+$(\"#ddFilter3\").val()+$(\"#ddFilter4\").val()+$(\"#ddFilter5\").val()+$(\"#ddFilter6\").val();' class='form-control' name='ddFilter'>";

		$filters = $DBH->survey_filters($_GET['sid'],4);

			echo "<option value=''> - By ".$custLabels[0]['cust_4_label']." - </option>";
    		echo "<option value=''>None</option>";

    		foreach($filters as $row) {
    			if($row['filter'] != '') {
    				$test = $row['type'].' = "'.$row['filter'] . '"';
    				if($saveCust4 == $test) {
    					$selected = 'selected';
    				} else {
    					$selected = '';
    				}
    				$urlFilter = urlencode($row['type']." = \"".$row['filter']);
    				echo"<option ".$selected." value='&cust4=".$urlFilter."\"'>".$row['filter']."</option>";
    			}
    		}

		echo "    </select>&nbsp;";
		echo "<select style='width: 19%;' id='ddFilter5' onchange='window.location = \"".$url."\"+$(\"#ddFilter1\").val()+$(\"#ddFilter2\").val()+$(\"#ddFilter3\").val()+$(\"#ddFilter4\").val()+$(\"#ddFilter5\").val()+$(\"#ddFilter6\").val();' class='form-control' name='ddFilter'>";

		$filters = $DBH->survey_filters($_GET['sid'],5);

			echo "<option value=''> - By ".$custLabels[0]['cust_5_label']." - </option>";
    		echo "<option value=''>None</option>";

    		foreach($filters as $row) {
    			if($row['filter'] != '') {
    				$test = $row['type'].' = "'.$row['filter'] . '"';
    				if($saveCust5 == $test) {
    					$selected = 'selected';
    				} else {
    					$selected = '';
    				}
    				$urlFilter = urlencode($row['type']." = \"".$row['filter']);
    				echo"<option ".$selected." value='&cust5=".$urlFilter."\"'>".$row['filter']."</option>";
    			}
    		}

		echo "    </select>&nbsp;";
		echo "<select style='width: 19%;' id='ddFilter6' onchange='window.location = \"".$url."\"+$(\"#ddFilter1\").val()+$(\"#ddFilter2\").val()+$(\"#ddFilter3\").val()+$(\"#ddFilter4\").val()+$(\"#ddFilter5\").val()+$(\"#ddFilter6\").val();' class='form-control' name='ddFilter'>";

		$filters = $DBH->survey_filters($_GET['sid'],6);

			echo "<option value=''> - By ".$custLabels[0]['cust_6_label']." - </option>";
    		echo "<option value=''>None</option>";

    		foreach($filters as $row) {
    			if($row['filter'] != '') {
    				$test = $row['type'].' = "'.$row['filter'] . '"';
    				if($saveCust6 == $test) {
    					$selected = 'selected';
    				} else {
    					$selected = '';
    				}
    				$urlFilter = urlencode($row['type']." = \"".$row['filter']);
    				echo"<option ".$selected." value='&cust6=".$urlFilter."\"'>".$row['filter']."</option>";
    			}
    		}

		echo "    </select>";
		echo "    <div style='margin:10px 5px 0px 0px;'>";
		echo "    <span id='filter-reset' onclick='window.location = \"".$url."\"' class='btn btn-link strong'>Reset All Filters</span>";
		//echo "    <span style='padding-left:15px;'># Responding in Filters ".$numRespondingWithFilters."</span>";
		echo "    </div>";
		echo "    </div>";
		echo "  </div>";
		echo "</div> <!-- End Filter Panel -->";
		echo "<div class='spacer'>&nbsp;</div>";
		echo "<!-- End Filter Options -->";

	/*
	// FILTERS end
	*/

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
	echo "";
	echo "</td>";
	echo "<td>";
	echo "<div class='report-survey-name' style='font-size:14px; font-weight:normal;'># Responding: " . $numRespondingWithFilters . "</div>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";

	echo "<div id='crumbcontainer'>\n";
	echo $crumbStart;
	echo $crumbtrail . "</div><div style='clear:both; margin-bottom:15px;'></div>\n";
	echo $reportTable;
	if(!empty($subTables)) {
		echo "<div style='font-size:20px; color:#999; margin:10px 0px; padding-top:20px; border-top:1px solid #DDD; font-style:italic; font-weight:300;'>Cost Distribution of " . $reportTitle . " ($" . number_format($sumSalaryTotal) . "):</div>\n";
		echo $subTables;
	}
	//echo "<pre>", print_r($subresult), "</pre>";
}

function drawTable($result, $reportTitle) {
	$surveyID = $_GET['sid'];
	
	$sumSalary = 0;
	$sumHours = 0;
	foreach($result as $num => $values) {
		$sumSalary += $values['salary'];
		$sumHours += $values['hours'];
	}
	if($sumHours > 0) {
		$sumHourly = $sumSalary / $sumHours;
	}
	$reportTable .= "<div class='report-header blue strong'>" . $reportTitle . "</div>";
	$reportTable .= "<table class='report-table' style='margin-top:15px;'>";
	$reportTable .= "<tr>\n";
	$reportTable .= "<td><div class='strong italic'>Answering: " . $result[0]['count'] . "</div></td>\n";
	$reportTable .= "<td></td>\n";
	$reportTable .= "<td class='report-td-calcs blue strong'>Pct</td>\n";
	$reportTable .= "<td class='report-td-calcs blue strong'>Hours</td>\n";
	$reportTable .= "<td class='report-td-calcs blue strong'>Cost to Firm</td>\n";
	$reportTable .= "<td class='report-td-calcs blue strong'>Hourly</td>\n";
	$reportTable .= "</tr>\n";
	$currFilters = $_GET['filter'];
	$currCust = $_GET['cust'];
	for($q=0;$q<count($result);++$q) {
		$hours[$q] = $result[$q]['hours'];
		$salary[$q] = $result[$q]['salary'];
		if($sumSalary > 0) {
			$pct[$q] = $salary[$q] / $sumSalary;
		}
		if($hours[$q] > 0) {
			$hourly[$q] = $salary[$q] / $hours[$q];
		}
		$sumPct += $pct[$q];
		if($result[$q]['page_id']) { //if there is a subset of questions, display drill down link
			$aopen[$q] = "<a href='?rq=repuw&sid=$surveyID&pid=" . $result[$q]['page_id'] . "&filter=" . $currFilters ."&cust=".$currCust."'>";
			$abarlink[$q] = "<a class='report-link' href='?rq=repuw&sid=$surveyID&pid=" . $result[$q]['page_id'] . "&filter=" . $currFilters . "&cust=".$currCust."'>";
			$aclose[$q] = "</a>";
		}
		$reportTable .= "<tr>\n";
		$reportTable .= "<td>" . $aopen[$q] . $result[$q]['question_desc'] . $aclose[$q] . "</td>\n";
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
		$reportTable .= "<td class='report-td-calcs'>" . number_format($pct[$q]*100,1) . "%</td>\n";
		$reportTable .= "<td class='report-td-calcs'>" . number_format($hours[$q]) . "</td>\n";
		$reportTable .= "<td class='report-td-calcs'>$" . number_format($salary[$q]) . "</td>\n";
		$reportTable .= "<td class='report-td-calcs'>$" . number_format($hourly[$q]) . "</td>\n";
		$reportTable .= "</tr>\n";
	} //end question loop

	
	
	$reportTable .= "<tr>\n";
	$reportTable .= "<td class='strong'>TOTAL:</td>\n";
	$reportTable .= "<td></td>\n";
	$reportTable .= "<td class='report-td-calcs strong'>" . number_format($sumPct*100) . "%</td>\n";
	$reportTable .= "<td class='report-td-calcs strong'>" . number_format($sumHours) . "</td>\n";
	$reportTable .= "<td class='report-td-calcs strong'>$" . number_format($sumSalary) . "</td>\n";
	$reportTable .= "<td class='report-td-calcs strong'>$" . number_format($sumHourly) . "</td>\n";
	$reportTable .= "</tr>\n";
	$reportTable .=  "</table>\n\n";
	return $reportTable;
}




function drawSubTable($result, $reportTitle, $pageID, $loopNo, $sumSalaryTotal) {
	$surveyID = $_GET['sid'];
	
	$sumSalary = 0;
	$sumHours = 0;
	foreach($result as $num => $values) {
		$sumSalary += $values['salary'];
		$sumHours += $values['hours'];
	}
	if($sumHours > 0) {
		$sumHourly = $sumSalary / $sumHours;
	}
	if($sumSalaryTotal > 0) {
		$sumPct = $sumSalary / $sumSalaryTotal;
	}
	if (($loopNo % 2) == 1) { //if odd starting with zero, then start new row. Allows two charts per row
		$reportTable .= "<div class='row'>\n";
	}
	$reportTable .= "<div class='col-lg-6' style='margin-bottom:30px;'>\n";
	$currFilters = $_GET['filter'];
	$currCust = $_GET['cust'];
	$reportTable .= "<div class='report-header blue strong' style='font-size:12px;'><a class='white' href='?rq=repuw&sid=$surveyID&pid=" . $pageID . "&filter=" . $currFilters . "&cust=".$currCust."'>" . $reportTitle . "</a></div>";
	$reportTable .= "<table class='report-sub-table' style='margin-top:15px;'>";
	$reportTable .= "<tr>\n";
	$reportTable .= "<td><div class='strong italic'>Answering: " . $result[0]['count'] . "</div></td>\n";
	$reportTable .= "<td></td>\n";
	$reportTable .= "<td class='report-td-calcs blue strong'>Pct</td>\n";
	//$reportTable .= "<td class='report-td-calcs blue strong'>Hours</td>\n";
	$reportTable .= "<td class='report-td-calcs blue strong'>Cost</td>\n";
	//$reportTable .= "<td class='report-td-calcs blue strong'>Hourly</td>\n";
	$reportTable .= "</tr>\n";
	if(count($result) > 1) { //if just summarizing, no need to show details
		for($q=0;$q<count($result);++$q) {
			$hours[$q] = $result[$q]['hours'];
			$salary[$q] = $result[$q]['salary'];
			if($sumSalaryTotal > 0) { //percentage is percent of total
				$pct[$q] = $salary[$q] / $sumSalaryTotal;
			}
			//$sumPct += $pct[$q];
			if($result[$q]['page_id']) { //if there is a subset of questions, display drill down link
				$currFilters = $_GET['filter'];
				$currCust = $_GET['cust'];
				$aopen[$q] = "<a href='?rq=repuw&sid=$surveyID&pid=" . $result[$q]['page_id'] . "&filter=".$currFilters."&cust=".$currCust."'>";
				$abarlink[$q] = "<a class='report-link' href='?rq=repuw&sid=$surveyID&pid=" . $result[$q]['page_id'] . "&filter=".$currFilters."&cust=".$currCust."'>";
				$aclose[$q] = "</a>";
			}
			$reportTable .= "<tr>\n";
			$reportTable .= "<td><div class='sub-desc'>" . $aopen[$q] . $result[$q]['question_desc'] . $aclose[$q] . "</div></td>\n";
			$reportTable .= "<td class='report-td-chart'>";
			$reportTable .= "<div class='report-div-container' style='height:25px;'>";
			$reportTable .= "<div class='report-div-slice' style='height:25px;'>&nbsp;</div>";
			$reportTable .= "<div class='report-div-slice' style='height:25px;'>&nbsp;</div>";
			$reportTable .= "<div class='report-div-slice' style='height:25px;'>&nbsp;</div>";
			$reportTable .= "<div class='report-div-slice' style='height:25px;'>&nbsp;</div>";
			$reportTable .= "<div class='report-div-slice' style='height:25px;'>&nbsp;</div>";
			if($pct[$q] > 0) { //only show bar if data present
				$reportTable .= "<div class='report-div-barchart' style='width:" . number_format($pct[$q]*100) . "%; height:16px;'>" . $abarlink[$q] . "&nbsp;" . $aclose[$q] . "</div>";
			}
			$reportTable .= "</div>";
			$reportTable .= "</td>\n";
			$reportTable .= "<td class='report-td-calcs'>" . number_format($pct[$q]*100,1) . "%</td>\n";
			//$reportTable .= "<td class='report-td-calcs'>" . number_format($hours[$q]) . "</td>\n";
			$reportTable .= "<td class='report-td-calcs'>$" . number_format($salary[$q]) . "</td>\n";
			//$reportTable .= "<td class='report-td-calcs'>$" . number_format($hourly[$q]) . "</td>\n";
			$reportTable .= "</tr>\n";
		} //end question loop
	} //end if more than one row
	$reportTable .= "<tr>\n";
	$reportTable .= "<td class='strong'>TOTAL:</td>\n";
		$reportTable .= "<td class='report-td-chart'>";
		$reportTable .= "<div class='report-div-container' style='height:25px;'>";
		$reportTable .= "<div class='report-div-slice' style='height:25px;'>&nbsp;</div>";
		$reportTable .= "<div class='report-div-slice' style='height:25px;'>&nbsp;</div>";
		$reportTable .= "<div class='report-div-slice' style='height:25px;'>&nbsp;</div>";
		$reportTable .= "<div class='report-div-slice' style='height:25px;'>&nbsp;</div>";
		$reportTable .= "<div class='report-div-slice' style='height:25px;'>&nbsp;</div>";
		if($sumPct > 0) { //only show bar if data present
			$reportTable .= "<div class='report-div-barchart bartotal' style='width:" . number_format($sumPct*100) . "%; height:16px;'>&nbsp;</div>";
		}
		$reportTable .= "</div>";
		$reportTable .= "</td>\n";
	$reportTable .= "<td class='report-td-calcs strong'>" . number_format($sumPct*100, 1) . "%</td>\n";
	//$reportTable .= "<td class='report-td-calcs strong'>" . number_format($sumHours) . "</td>\n";
	$reportTable .= "<td class='report-td-calcs strong'>$" . number_format($sumSalary) . "</td>\n";
	//$reportTable .= "<td class='report-td-calcs strong'>$" . number_format($sumHourly) . "</td>\n";
	$reportTable .= "</tr>\n";
	$reportTable .=  "</table>\n\n";
	$reportTable .=  "</div><!--end column-->\n\n";
	if (($loopNo % 2) == 0) { //if odd starting with zero, then start new row. Allows two charts per row. close row here
		$reportTable .= "</div><!--end row-->\n";
	}
	return $reportTable;
}




































/*
function repuwXXX() {
	global $accountID;
	$RBH = new Report();
	$surveyID = $_GET['sid'];
	$pageID = $_GET['pid'];
	//get map of entire survey
	$map = surveyMap($surveyID);
	//echo "<pre>", print_r($map),"</pre>";
	//return;
	$hoursPageID = $map[0]['page_id'];
	$hoursTitle = "Legal & Support";
	$crumbStart = "<span class='report-crumb'><a class='acrumb' href='?rq=repuw&sid=$surveyID&pid=" . $hoursPageID . "'>" . $hoursTitle . "</a></span>";
	if(empty($pageID) || $pageID == $hoursPageID) {
		$pageID = $hoursPageID; //start from the beginning of the survey if no page marker found
		$isHoursPage = true;
	}
	unset($value);
	$value = $pageID; //get question id of parent question
	//filter down survey map to just the current page and get all parent elements of current page. allows up to 5 levels
	$map_filtered = array_values(array_filter($map, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
	//echo "Map Filtered: <pre>", print_r($map_filtered),"</pre>";
	$questionIDArray[0] = $map_filtered[0]['question_id_parent_5'];
	$questionIDArray[1] = $map_filtered[0]['question_id_parent_4'];
	$questionIDArray[2] = $map_filtered[0]['question_id_parent_3'];
	$questionIDArray[3] = $map_filtered[0]['question_id_parent_2'];
	$questionIDArray[4] = $map_filtered[0]['question_id_parent'];
	//$pageArr gets details of parent elements and current page. Includes question, question id, and page id
	$pageArr = $RBH->parent_pages($surveyID, $questionIDArray);
	//echo "Parent Pages: <pre>", print_r($pageArr),"</pre>";
	$currentPage = end($pageArr);
	$reportTitle = $currentPage["question_desc"];
	if($isHoursPage) {
		$reportTitle = $hoursTitle;
	}

	//get data from calcs
	$DBH = new Account();
	// GROUP BY clauses
	$group = [];
	
	//WHERE clauses
	$filters = [];

	//hit calcs
	$result = $DBH->calcs($surveyID, $pageID, NULL, $group, $filters);
	
	//echo "Matt's Query, Calculated Results: <pre>", print_r($result),"</pre>";

	//echo "<pre>", print_r($result), "</pre>";
	
	$sumSalary = 0;
	$sumHours = 0;
	foreach($result as $num => $values) {
		$sumSalary += $values['salary'];
		$sumHours += $values['hours'];
	}
	if($sumHours > 0) {
		$sumHourly = $sumSalary / $sumHours;
	}

	for($p=0;$p<count($pageArr);++$p) {
		$crumbtrail .= "<div class='report-crumb'><img src='/images/arrow-right.png' width='25' /></div><div class='report-crumb'><a  class='acrumb' href='?rq=repuw&sid=$surveyID&pid=" . $pageArr[$p]['page_id'] . "'>" . $pageArr[$p]['question_desc'] . "</a></div>";
	}
	$questionsOnPage = $RBH->report_page($surveyID, $pageID, $hoursPageID, $pageArr);
	//echo "Page Array: <pre>", print_r($currentPage),"</pre>";
	//echo "Questions on page: <pre>", print_r($questionsOnPage),"</pre>";
	for($q=0;$q<count($questionsOnPage);++$q) {
		unset($value);
		unset($result_filtered);
		$value = $questionsOnPage[$q]['question_id']; //get question id as filter value
		$result_filtered = array_values(array_filter($result, function($ar) use ($value) { return ($ar["question_id"] == $value); }));
		//echo "<pre>", print_r($result_filtered),"</pre>";
		$hours[$q] = $result_filtered[0]['hours'];
		$salary[$q] = $result_filtered[0]['salary'];
		if($sumSalary > 0) {
			$pct[$q] = $salary[$q] / $sumSalary;
		}
		if($hours[$q] > 0) {
			$hourly[$q] = $salary[$q] / $hours[$q];
		}
		$sumPct += $pct[$q];
		if($questionsOnPage[$q]['page_id']) { //if there is a subset of questions, display drill down link
			$aopen[$q] = "<a href='?rq=repuw&sid=$surveyID&pid=" . $questionsOnPage[$q]['page_id'] . "'>";
			$abarlink[$q] = "<a class='report-link' href='?rq=repuw&sid=$surveyID&pid=" . $questionsOnPage[$q]['page_id'] . "'>";
			$aclose[$q] = "</a>";
		}
		$reportTable .= "<tr>\n";
		$reportTable .= "<td>" . $aopen[$q] . $questionsOnPage[$q]['question_desc'] . $aclose[$q] . "</td>\n";
		$reportTable .= "<td class='report-td-chart'>";
		$reportTable .= "<div class='report-div-container'>";
		$reportTable .= "<div class='report-div-slice'>&nbsp;</div>";
		$reportTable .= "<div class='report-div-slice'>&nbsp;</div>";
		$reportTable .= "<div class='report-div-slice'>&nbsp;</div>";
		$reportTable .= "<div class='report-div-slice'>&nbsp;</div>";
		$reportTable .= "<div class='report-div-slice'>&nbsp;</div>";
		//$reportTable .= "<div class='report-div-barchart' style='width:" . rand(1,50) . "%;'>" . $abarlink[$q] . "&nbsp;" . $aclose[$q] . "</div>";
		if($pct[$q] > 0) { //only show bar if data present
			$reportTable .= "<div class='report-div-barchart' style='width:" . number_format($pct[$q]*100) . "%;'>" . $abarlink[$q] . "&nbsp;" . $aclose[$q] . "</div>";
		}
		$reportTable .= "</div>";
		$reportTable .= "</td>\n";
		$reportTable .= "<td class='report-td-calcs'>" . number_format($pct[$q]*100,1) . "%</td>\n";
		$reportTable .= "<td class='report-td-calcs'>" . number_format($hours[$q]) . "</td>\n";
		$reportTable .= "<td class='report-td-calcs'>$" . number_format($salary[$q]) . "</td>\n";
		$reportTable .= "<td class='report-td-calcs'>$" . number_format($hourly[$q]) . "</td>\n";
		$reportTable .= "</tr>\n";
	} //end question loop

	
	
	$reportTable .= "<tr>\n";
	$reportTable .= "<td class='strong'>TOTAL:</td>\n";
	$reportTable .= "<td></td>\n";
	$reportTable .= "<td class='report-td-calcs strong'>" . number_format($sumPct*100) . "%</td>\n";
	$reportTable .= "<td class='report-td-calcs strong'>" . number_format($sumHours) . "</td>\n";
	$reportTable .= "<td class='report-td-calcs strong'>$" . number_format($sumSalary) . "</td>\n";
	$reportTable .= "<td class='report-td-calcs strong'>$" . number_format($sumHourly) . "</td>\n";
	$reportTable .= "</tr>\n";
	
	assessmentTabs($surveyID, 8);
	echo "<div id='crumbcontainer'>";
	echo $crumbStart;
	echo $crumbtrail . "</div><div style='clear:both; margin-bottom:15px;'></div>";
	echo "<div class='report-header blue strong'>" . $reportTitle . "</div>";
	echo "<table class='report-table' style='margin-top:15px;'>";
	echo "<tr>\n";
	echo "<td><div class='strong italic'>Answering: " . $result[0]['count'] . "</div></td>\n";
	echo "<td></td>\n";
	echo "<td class='report-td-calcs blue strong'>Pct</td>\n";
	echo "<td class='report-td-calcs blue strong'>Hours</td>\n";
	echo "<td class='report-td-calcs blue strong'>Cost to Firm</td>\n";
	echo "<td class='report-td-calcs blue strong'>Hourly</td>\n";
	echo "</tr>\n";
	echo $reportTable;
	echo "</table>";
}
*/
?>
