<?php

use App\Classes\Takesurvey;

global $respID;
global $surveyID;
global $pageID;
global $surveyIDUnhashed;
global $accessCode;
global $respFirst;
global $respLast;
global $weeklyHoursText;
global $annualLegalHoursText;
global $lastPageIDSubmitted;
global $beginButton;
global $showProgressBar;

function renderPage() {
	//FUTURE DEVEOPMENT NOTE:
	//create tblSurveyMap and write the current map to database instead of rendering each page load
	//should improve performance and integrity
	global $respID;
	global $surveyID;
	global $pageID;
	global $surveyIDUnhashed;
	global $accessCode;
	global $respFirst;
	global $respLast;

	//FINISH LATER BUTTON CLICKED
    if(isset($_POST['btnFinishLater'])) {
	    showModal("Finish Survey Later", "<div class='modal-body'>Your responses are saved. When ready, please return to the survey through the e-mail invitation you received.</div><div class='modal-footer'><button type='button' class='btn btn-default' data-dismiss='modal'>Back To Survey</button><a class='btn btn-primary' href='?sv=" . $surveyIDUnhashed . "&ac=" . $accessCode . "'>Exit Survey</a></div>");
    }
	if($pageID == "A") {
		inactivePage();
		exit();
	}
	if($pageID == "E") {
		endPage();
		return;
	}
	if($pageID == "B") {
		beginPage();
		return;
	}
	$posArray = surveyPosition($respID, $surveyID, $pageID);
	if($posArray == false) {
		beginPage();
		return;
	}
	$surveyPosition = $posArray[0];
	echo "<div class='row' style='margin-bottom:15px;'>\n";
	//echo "<div class='col-sm-5'><p class='sm'>Respondent Name: <strong>" . $respFirst . " " . $respLast . "</strong></p></div>\n";
	echo "<div class='col-sm-12'>" . progressBar($posArray[1], $posArray[2]) . "</div>\n";
	echo "</div>\n";
	//testdelete($respID, $surveyID, $pageID, true);
	$DBH = new Takesurvey();
	$pageInfo = $DBH->page_info($surveyID, $pageID);
	$questionIDParent = $pageInfo['question_id_parent'];
	$pageDesc = $pageInfo['page_desc'];
	$pageExtra = $pageInfo['page_extra'];
	$pageExtra = str_replace("[SURVEY POSITION]",$posArray[3],$pageExtra); //swap variable for answer
	$pageExtra = str_replace("[PARENT LINK]","?sv=" . $surveyIDUnhashed . "&ac=" . $accessCode . "&rq=" . findParentPage($surveyID, $questionIDParent),$pageExtra); //swap variable for answer
	switch ($pageInfo['page_type']) {
		case 0: //Legal and Support annual hours calculation
			pageType0($pageDesc, $pageExtra, $surveyPosition);
			break;
		case 1: //main summation page type
			pageType1($pageDesc, $pageExtra, $surveyPosition, $questionIDParent);
			break;
		case 2:
			pageType2($pageDesc, $pageExtra, $surveyPosition);
			break;
	}
	?>
<section class="main-section buttons" style="margin-top:0px;">
    <div class="row">
    	<div class="col-sm-2 text-left">
<?php
			if($pageInfo['page_type']==1) {
			echo "<a class='btn btn-sm btn-default' href='?sv=" . $surveyIDUnhashed . "&ac=" . $accessCode . "&rq=" . findParentPage($surveyID, $questionIDParent) . "&res=true'  data-toggle='tooltip' data-placement='top' title='If the above activities do not apply to you, go back and enter ZERO for " . $posArray[3] . ".' style='color:#bd3245; font-size:14px; border:1px solid #e6c6cb;'><span class='glyphicon glyphicon-remove-sign'></span> These Don't Apply</a>";
			}
?>
        </div>
        <div class="col-sm-8">
        	<input type="submit" class="btn btn-primary preload" id="idBtnSave" name="btnSave" value="Continue" />
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="submit" class="btn btn-default preload" name="btnFinishLater" value="Save & Exit" />
    	</div>
    	<div class="col-sm-2">
        	&nbsp;
        </div>
    </div>
</section><!-- end main-section buttons -->
<?php
}
function pageType2($pageDesc, $pageExtra, $surveyPosition) {
	global $respID;
	global $surveyID;
	global $pageID;
	global $surveyIDUnhashed;
	global $accessCode;
	global $respFirst;
	global $respLast;
	$DBH = new Takesurvey();
	$answer = $DBH->answer_from_question_id_parent($respID, $pageID);
	//SAVE BUTTON IS CLICKED
	if(isset($_POST['btnSave']) || isset($_POST['btnOverwrite'])) {
		$nextPageID = getNextPage($respID, $surveyID, $pageID, true, false);
		header("Location:?sv=" . $surveyIDUnhashed . "&ac=" . $accessCode . "&rq=" . $nextPageID);
		exit();
	}
	$pageExtra = str_replace("[ANSWER VALUE]",$answer,$pageExtra); //swap variable for answer
	$disableResume = " title='Review Next Page'";
////////////DISPLAY CONTENT////////////////////
////////////DISPLAY CONTENT////////////////////
	echo '<table border="0" width="100%"><tr><td valign="center" width="100">';
	echo '<button type="submit" tabindex="-1" name="btnBack" data-toggle="tooltip" data-placement="top" title="Review Previous Page" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-chevron-left"></span></button>&nbsp;';
	echo '<button type="submit" tabindex="-1" name="btnNext"  data-toggle="tooltip" data-placement="top"' . $disableResume . ' class="btn btn-sm btn-default"><span class="glyphicon glyphicon-chevron-right"></span></button>';
	echo '</td><td valign="center">';
	echo '</td></tr></table>';
	echo '<div>&nbsp;</div>';
	echo "<p class='surveyQuestion'>" . $pageDesc . "</p>\n";
	echo $pageExtra;
}
function pageType0($pageDesc, $pageExtra, $surveyPosition) {
	global $respID;
	global $surveyID;
	global $pageID;
	global $surveyIDUnhashed;
	global $accessCode;
	global $respFirst;
	global $respLast;
	global $weeklyHoursText;
	global $annualLegalHoursText;
	$weeksPerYear = 52; //hard coded but should eventually be database value
	$DBH = new Takesurvey();
	$respAnswerArray = $DBH->respondent_answers_on_page($respID, $pageID);
	//echo "<pre>",print_r($respAnswerArray),"</pre>";
	$questionArray = $DBH->questions_on_page($surveyID, $pageID);
	$goToCurrentPage = isset($_GET['res']) ? $_GET['res'] : 0;
	$weeklyHours = '';
	$errorClass = '';
	//SAVE BUTTON IS CLICKED
	if(isset($_POST['btnSave']) || isset($_POST['btnOverwrite'])) {
		$weeklyHours = substr(trim($_POST['hours_weekly']),0,5);
		$annualLegalHours = substr(trim($_POST['hours_annual_legal']),0,7);
		if(!is_numeric($weeklyHours) || $weeklyHours < 1){
			showModalSimple("Invalid Weekly Hours", "<p class='surveyQuestion'>Your weekly hours should be a number greater than zero.</p>");
		} elseif(!is_numeric($annualLegalHours) || $annualLegalHours > $weeklyHours * $weeksPerYear) {
			showModalSimple("Invalid Annual Practice Hours", "<p class='surveyQuestion'>Your annual practice hours should be numeric and of a value equal to or less your total annual hours. Please enter zero if not applicable.</p>");
		//} elseif(count($respAnswerArray) > 0 && !isset($_POST['btnOverwrite'])) {
			//showModal("<span class='glyphicon glyphicon-warning-sign'>&nbsp;</span> Overwrite Warning", "<div class='modal-body'>You are about to re-submit this page. Doing so may reset portions of the survey you previously answered. Are you sure you wish to proceed?</div><div class='modal-footer'><input class='btn btn-primary' type='submit' name='btnOverwrite' data-toggle='tooltip' data-placement='bottom' value='Re-submit Page' />&nbsp;&nbsp;<input class='btn btn-default' type='submit' name='btnResume' value='Cancel & Go To Where I Left Off' /></div>");
		} else {
			$dbTotalHours = round($weeklyHours,0) * $weeksPerYear;
			$dbLegalHours = round($annualLegalHours,0);
			$dbSupportHours = $dbTotalHours - $dbLegalHours;
			$qaArray[0]['question_id'] = $questionArray[0]['question_id'];
			$qaArray[0]['answer_value'] = $dbLegalHours;
			$qaArray[1]['question_id'] = $questionArray[1]['question_id'];
			$qaArray[1]['answer_value'] = $dbSupportHours;
			//echo "<pre>", print_r($qaArray),"</pre>";
			$deleteArray = deleteArray($surveyID, $pageID, $qaArray); //get array of page ids to be deleted from tblAnswer, including current page
			$DBH->insert_respondent_answers_on_page($respID, $pageID, $deleteArray, $qaArray);
			$nextPageID = getNextPage($respID, $surveyID, $pageID, true, $goToCurrentPage);
			header("Location:?sv=" . $surveyIDUnhashed . "&ac=" . $accessCode . "&rq=" . $nextPageID);
			exit();
		}
	} else {
		if(count($respAnswerArray)>0) { //display previous answers on page if any
			$dbTotalHours = ($respAnswerArray[0]['answer_value'] + $respAnswerArray[1]['answer_value']);
			$weeklyHours = $dbTotalHours / $weeksPerYear; //convert back to weekly hours for display
			$annualLegalHours = $respAnswerArray[1]['answer_value'];
		}
	} // ENd if save button clicked
	if(count($respAnswerArray) == 0) { //new page, disable resume
		$disableResume = " disabled='disabled' title='This is the most current page'";
	} else {
		$disableResume = " title='Review Next Page'";
	}
////////////DISPLAY CONTENT////////////////////
////////////DISPLAY CONTENT////////////////////
	echo '<table border="0" width="100%"><tr><td valign="center" width="100">';
	echo '<input type="submit" class="btn btn-link" name="btnSave" value="" style="padding:0px !important;" />'; //hidden save button if enter is hit, this button
	echo '<button type="submit" tabindex="-1" name="btnBack" data-toggle="tooltip" data-placement="top" title="Review Previous Page" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-chevron-left"></span></button>&nbsp;';
	echo '<button type="submit" tabindex="-1" name="btnNext"  data-toggle="tooltip" data-placement="top"' . $disableResume . ' class="btn btn-sm btn-default"><span class="glyphicon glyphicon-chevron-right"></span></button>';
	echo '</td><td valign="center">';
	echo $pageDesc;
	echo '</td></tr></table>';
	echo "<table class='sumtable' style='margin-top:15px;'>\n";
	echo "<tr class=''><td colspan='2'>\n";
	echo "<div class='pageExtra'>" . $pageExtra . "</div>\n";
	echo '</td></tr>';

	//WEEKLY HOURS
	echo "<tr class='trHours'>\n";
	echo "<td class='tdHours'>\n";
	echo $weeklyHoursText;
	echo "</td>\n";
	echo "<td class='tdHours2'>\n";
	echo "<input type='textbox' class='hoursbox" . @$errorClass[0] . "' id='id_weekly_hours' name='hours_weekly' maxlength='5' value='" . $weeklyHours . "' />\n";
	echo "</td>\n";
	echo "</tr>\n";

	//TOTAL ANNUAL HOURS
	echo "<tr class=''><td colspan='2'>\n";
	echo "<div class='divAT'>Based on your average WEEKLY hours entered above, your total ANNUAL hours = <span class='annualTotal'>" . @$dbTotalHours ."</span>";
	echo '</td></tr>';

	//ANNUAL LEGAL HOURS
	echo "<tr class='trHours'>\n";
	echo "<td class='tdHours'>\n";
	echo $annualLegalHoursText;
	echo "</td>\n";
	echo "<td class='tdHours2'>\n";
	echo "<input type='textbox' class='hoursbox" . @$errorClass[1] . "' id='id_annual_legal_hours' name='hours_annual_legal' maxlength='7' value='" . @$annualLegalHours . "' />\n";
	echo "</td>\n";
	echo "</tr>\n";

	//ANNUAL LEGAL HOURS
	echo "<tr class=''><td colspan='2'>\n";
	echo "<div class='divAT' id='hoursSplit' style='display:none;'>";
	echo "<div>Your Annual Practice Hours = <span id='annualLegalHours'></span></div>";
	echo "<div>Your Annual Administrative/Support hours = <span id='annualSupportHours'></span></div>";
	echo "</div>";
	echo "</td></tr>";
	echo "</table>\n";
?>
<script>
$(document).ready(function(){
	if($("#id_weekly_hours").val().length > 0) { //if hours textbox has something in it, show hours
		showHours();
	}
	$("#id_weekly_hours").keyup(function(){
		var weeklyhrs = $(this).val();
		var annualhrs = weeklyhrs * <?php echo $weeksPerYear; ?>;
		$(".annualTotal").text(addCommas(annualhrs));
	});
	$("#id_annual_legal_hours").keyup(function(){
		showHours();
	});
	function showHours() {
		$("#hoursSplit").show();
		var legalhrs = $("#id_annual_legal_hours").val();
		var weeklyhrs = $("#id_weekly_hours").val();
		var annualhrs = weeklyhrs * <?php echo $weeksPerYear; ?>;
		var supporthrs = annualhrs - legalhrs;
		$("#annualLegalHours").text(addCommas(legalhrs));
		$("#annualSupportHours").text(addCommas(supporthrs));
	}
	function addCommas(nStr) {
		nStr += '';
		var x = nStr.split('.');
		var x1 = x[0];
		var x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/;
		while (rgx.test(x1)) {
			x1 = x1.replace(rgx, '$1' + ',' + '$2');
		}
		return x1 + x2;
	}
});
</script>
<?php
}
function pageType1($pageDesc, $pageExtra, $surveyPosition, $questionIDParent) {
	global $respID;
	global $respAlt;
	global $surveyID;
	global $pageID;
	global $surveyIDUnhashed;
	global $accessCode;
	$DBH = new Takesurvey();
	$respAnswerArray = $DBH->respondent_answers_on_page($respID, $pageID);
	$questionArray = $DBH->questions_on_page($surveyID, $pageID);
	$goToCurrentPage = isset($_GET['res']) ? $_GET['res'] : null;
	$errorClass = [];
	//SAVE BUTTON IS CLICKED
	if(isset($_POST['btnSave']) || isset($_POST['btnOverwrite'])) {
		$totalSum = 0;
		$errorCt = 0;
		$qaCt = 0;
		for($q=0;$q<count($questionArray);++$q) {
			$answerValue[$q] = $_POST['sum_' . $questionArray[$q]['question_id']];
			?><script>//alert('<?php //echo $answerValue[$q]; ?>');</script><?php
			//test if number is valid between 0-100. Blanks acceptable
			if((is_numeric($answerValue[$q]) && ($answerValue[$q] < 0 || $answerValue[$q] > 100)) || (strlen($answerValue[$q]) > 0 && !is_numeric($answerValue[$q]))) {
				$errorClass[$q] = " sumboxAlert";
				$errorCt++;
			} else {
				if($answerValue[$q] == 0) { //remove zeros for display, although we'll recapture zeros when submitting to db
					$answerValue[$q] = "";
				}
				$respAnswer[$q] = $answerValue[$q]; //cast to new variable for blank to zero handler for database array only
				if(strlen($answerValue[$q]) == 0) {
					$respAnswer[$q] = 0;
				} else {
					$answerValue[$q] = round($respAnswer[$q],0); // round the valid numbers over zero
				}
				$qaArray[$qaCt]['question_id'] = $questionArray[$q]['question_id'];
				$qaArray[$qaCt]['answer_value'] = $respAnswer[$q];
				$qaCt++;
				$totalSum += $answerValue[$q];
			}
		} //end question array loop
		if($errorCt > 0) {
			showModalSimple("Invalid Entry", "<p class='surveyQuestion'>There was a problem with your entries (highlighted in red).</p><p class='surveyQuestion'>Please enter whole numbers only between 1 and 100 with no decimals, percent signs, or extra characters.</p>");
		} elseif($totalSum != 100) {
			showModalSimple("Invalid Sum", "<p class='surveyQuestion'>Your entries currently add up to " . $totalSum . "%</p><p class='surveyQuestion'>Please ensure your entries add up to 100% before continuing.</p>");
		//} elseif(count($respAnswerArray) > 0 && !isset($_POST['btnOverwrite'])) {
			//showModal("<span class='glyphicon glyphicon-warning-sign'>&nbsp;</span> Overwrite Warning", "<div class='modal-body'>You are about to re-submit this page. Doing so may reset portions of the survey you previously answered. Are you sure you wish to proceed?</div><div class='modal-footer'><input class='btn btn-primary' type='submit' name='btnOverwrite' data-toggle='tooltip' data-placement='bottom' value='Re-submit Page' />&nbsp;&nbsp;<input class='btn btn-default' type='submit' name='btnResume' value='Cancel & Go To Where I Left Off' /></div>");
		} else {
			//testDeleteArray($surveyID, $pageID, $qaArray);
			$deleteArray = deleteArray($surveyID, $pageID, $qaArray); //get array of page ids to be deleted from tblAnswer, including current page
			$DBH->insert_respondent_answers_on_page($respID, $pageID, $deleteArray, $qaArray);
			$nextPageID = getNextPage($respID, $surveyID, $pageID, true, $goToCurrentPage);
			header("Location:?sv=" . $surveyIDUnhashed . "&ac=" . $accessCode . "&rq=" . $nextPageID);
			exit();
		}
	} else { // if save button not clicked, loop through question array to grab stored values if any
		$totalSum = 0;
		for($q=0;$q<count($questionArray);++$q) {
			unset($value);
			$value = $questionArray[$q]['question_id'];
			$foundAnswer[$q] = array_values(array_filter($respAnswerArray, function($ar) use ($value) { return ($ar["question_id"] == $value); }));
			//echo "<pre>",print_r($foundAnswer[$q]),"</pre>";
			@$answerValue[$q] = $foundAnswer[$q][0]['answer_value'];
			$totalSum += $answerValue[$q];
			if($answerValue[$q] == 0) {
				$answerValue[$q] = "";
			}
		}
	} //End If Save Button clicked
    $buttonResume = '';
	if(count($respAnswerArray) == 0) { //new page, disable resume
		$disableResume = " disabled='disabled' title='This is the most current page'";
	} else {
		$disableResume = " title='Review Next Page'";
		$buttonResume = "<input type='submit' name='btnResume' class='btn btn-sm btn-default' value='Resume Where I Left Off' style='margin-left:20px;' />";
	}
	echo "<div id='flashSum'></div>\n";

	/*//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		echo '<table border="0" width="100%"><tr>';
		echo '<td width="100"><input type="submit" class="btn btn-link" name="btnSave" value="" style="padding:0px !important;" /></td>'; //hidden save button if enter is hit, this button submits first';
		echo '<td><span class="PIS">Position In Survey:</span></td>';
		echo '</tr><tr><td valign="center" width="100">';
		echo '<div style="white-space:nowrap;">';
        echo '<button type="submit" tabindex="-1" name="btnBack" data-toggle="tooltip" data-placement="top" title="Review Previous Page" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-chevron-left"></span></button>&nbsp;';
        echo '<button type="submit" tabindex="-1" name="btnNext" data-toggle="tooltip" data-placement="top"' . $disableResume . ' class="btn btn-sm btn-default"><span class="glyphicon glyphicon-chevron-right"></span></button>';
		echo '</div>';
		echo '</td><td valign="center">';
		echo $surveyPosition;
		echo '</td></tr></table>';
	*//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	echo "<div class='row'>\n";
	echo "<div class='col-sm-12' style='white-space:nowrap;'>\n";
	//hidden save button if enter is hit, this button submits first';
	echo '<input type="submit" class="btn btn-link" name="btnSave" value="" style="padding:0px !important;" />';
	echo '<button type="submit" tabindex="-1" name="btnBack" data-toggle="tooltip" data-placement="top" title="Review Previous Page" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-chevron-left"></span></button>&nbsp;';
	echo '<button type="submit" tabindex="-1" name="btnNext" data-toggle="tooltip" data-placement="top"' . $disableResume . ' class="btn btn-sm btn-default"><span class="glyphicon glyphicon-chevron-right"></span></button>';
	echo $buttonResume;
	echo "</div>\n"; //end col
	echo "</div>\n"; // end row

	echo "<div class='row' style='margin-top:10px;'>\n";
	echo "<div class='col-sm-12'>\n";
	echo "<span class='PIS'>Position In Survey:</span>\n";
	echo "</div>\n";  // end col
	echo "</div>\n"; // end row

	echo "<div class='row' style='margin-top:10px;'>\n";
	echo "<div class='col-sm-12'>\n";
	echo $surveyPosition;
	echo "</div>\n";  // end col
	echo "</div>\n"; // end row
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	echo "<table class='sumtable' style='margin-top:15px;'>\n";
	echo "<tr class=''><td colspan='2'>\n";
	echo "<div class='pageExtra'>" . $pageExtra . "</div>\n";
	echo '</td></tr>';
	for($q=0;$q<count($questionArray);++$q) {
		//show alternate display text if applicable and available
		if($respAlt == true && !empty($questionArray[$q]['question_desc_alt'])) {
			$questionDescDisplay[$q] = $questionArray[$q]['question_desc_alt'];
		} else {
			$questionDescDisplay[$q] = $questionArray[$q]['question_desc'];
		}
		if($respAlt == true && !empty($questionArray[$q]['question_extra_alt'])) {
			$questionExtraDisplay[$q] = $questionArray[$q]['question_extra_alt'];
		} else {
			$questionExtraDisplay[$q] = $questionArray[$q]['question_extra'];
		}
		echo "<tr class='trdesc trStatic' id='id_" . $questionArray[$q]['question_id'] . "_tr'>\n";
		echo "<td class='qdesc'><div class='qwrapper'><span class='qindicator glyphicon glyphicon-arrow-right' id='id_" . $questionArray[$q]['question_id'] . "_qi'></span>" . $questionDescDisplay[$q] . "</div></td>\n";
		echo "<td class='qsum' rowspan='2'>\n";
		echo "<input type='textbox' class='sumbox" . @$errorClass[$q] . "' id='id_" . $questionArray[$q]['question_id'] . "' name='sum_" . $questionArray[$q]['question_id'] . "' maxlength='3' value='" . $answerValue[$q] . "' /><span class='pct'>%</span>\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr id='id_" . $questionArray[$q]['question_id'] . "_tr_extra'>\n";
		echo "<td class='qextra'><span class='more'>" . $questionExtraDisplay[$q] . "</span></td>\n";
		//echo "<td class='qextrasum'></td>\n";
		echo "</tr>\n";

		//echo "<tr><td colspan='2'><div style='height:10px;'>&nbsp;</div></td></tr>\n"; //spacer row
	} // end question loop
	//echo "<tr class='trdesc'><td class='qdesc'><br /><a class='btn-link' href='?sv=" . $surveyIDUnhashed . "&ac=" . $accessCode . "&rq=" . findParentPage($surveyID, $questionIDParent) . "'  data-toggle='tooltip' data-placement='bottom' title='If the above activities do not apply to you, go back and enter 0% for this group.' style='font-size:14px; font-weight:bold; color:#bb3b3b !important;' tabindex='-1'><span class='glyphicon glyphicon-remove-sign'></span> If none of these apply to you, click to go back and make edits.</a></td><td class='qsum'><span class='sm' style='padding-right:30px; text-decoration:underline;'>TOTAL</span><br /><span id='sumTotal'>" . $totalSum . "%</span</td></tr>\n";
	echo "<tr class='trdesc'><td class='qdesc'></td><td class='qsum'><span class='sm' style='padding-right:30px; text-decoration:underline;'>TOTAL</span><br /><span id='sumTotal'>" . $totalSum . "%</span</td></tr>\n";
	echo "</table>\n";
	?>
<script>
$(document).ready(function(){
	$("#flashSum").hide();
	$(".sumbox").eq(0).focus();
	$('.sumbox').each(function () { //loop through all sumboxes on page load
		var sumid = $(this).attr('id');
		if($(this).val() > 0) {
			$("#"+sumid+"_tr").removeClass("trStatic"); //highlight row if input captured
			$("#"+sumid+"_tr").addClass("sumHighlight"); //highlight row if input captured
			$("#"+sumid+"_tr_extra").addClass("sumHighlight"); //highlight row if input captured
		}
	});
	$(".sumbox").keyup(function(){
		//console.log( "Handler for .keypress() called." );
		$('#flashSum').fadeIn('fast');
		var sum = 0;
		$(".sumbox").each(function(){
			sum += +$(this).val();
		});
		//console.log(sum);
		if(isNaN(sum)) {
			$("#sumTotal").text("???");
			$("#flashSum").text("???");
		} else {
			$("#sumTotal").text(sum+"%");
			$("#flashSum").text(sum+"%");
			$("#sumTotal").toggleClass("redalert", sum > 100);
			$("#flashSum").toggleClass("redalert", sum > 100);
			$("#flashSum").toggleClass("greenalert", sum == 100);
		}
		timer = setTimeout(function(){
		  $("#flashSum").fadeOut('slow');
		}, 1000);
	});
	$(".sumbox").focusin(function(){
		var thisid = $(this).attr("id");
		this.select(); //highlight current text
		$(".qindicator").hide(); //hide all other arrow indicators
		$("#"+thisid+"_qi").show(); //show current arrow indicator
	});
	$(".sumbox").focusout(function(){
		$("#flashSum").fadeOut('fast');
		var sumid = $(this).attr('id');
		$("#"+sumid+"_tr").toggleClass("sumHighlight", $(this).val() > 0); //highlight row if input captured
		$("#"+sumid+"_tr_extra").toggleClass("sumHighlight", $(this).val() > 0); //highlight row if input captured
		if($(this).val() > 0) {
			$("#"+sumid+"_tr").removeClass("trStatic"); //highlight row if input captured
			$("#"+sumid+"_tr").addClass("sumHighlight"); //highlight row if input captured
		}
		$(this).toggleClass("sumboxAlert", isNaN($(this).val()) || $(this).val() < 0 || $(this).val() > 100); //flag non numeric or numbers out of range
	});
 //FUNCTIONS FOR COLLAPSING AND EXPANDING DESCRIPTION CONTAINERS
    var showChar = 100;  // How many characters are shown by default
    var ellipsestext = "...";
    var moretext = "Show more >";
    var lesstext = "Show less";


    $('.more').each(function() {
        var content = $(this).html();

        if(content.length > showChar) {

            var c = content.substr(0, showChar);
            var h = content.substr(showChar, content.length - showChar);

            var html = c + '<span class="moreellipses">' + ellipsestext+ '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink" tabindex="-1">' + moretext + '</a></span>';
 			$(this).html(html);
        }

    });
    $(".morelink").click(function(){
        if($(this).hasClass("less")) {
            $(this).removeClass("less");
            $(this).html(moretext);
        } else {
            $(this).addClass("less");
            $(this).html(lesstext);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
    });
	//USE ARROWS AS TAB KEYS
	$('.sumbox').keydown(function(e) {
		//alert(e.keyCode);
		if (e.keyCode==40) {
			navigate(e.target, 1);
		}
		if (e.keyCode==38) {
			navigate(e.target, -1);
		}
	});
	function navigate(origin, sens) {
		var inputs = $('#formsrv').find('input:enabled');
		var index = inputs.index(origin);
		index += sens;
		if (index < 0) {
			index = inputs.length - 1;
		}
		if (index > inputs.length - 1) {
			index = 0;
		}
		inputs.eq(index).focus();
	}
});
</script>
<?php
}
function getNextPage($respID, $surveyID, $pageID, $goingForward, $goToCurrentPage) {
	//if $goToCurrentPage is TRUE, will go to the most current point in the survey
	$DBH = new Takesurvey();
	$respAnswerArray = $DBH->question_id_page_id_answers_not_zero($respID);
	for($i=0;$i<count($respAnswerArray);++$i) { //break up array into two one-dimensional arrays for in_array functionality
		$respAnswerQuestionIDArray[$i] = $respAnswerArray[$i]['question_id'];
		$respAnswerPageIDArray[$i] = $respAnswerArray[$i]['page_id'];
	}
	$map = surveyMap($surveyID);
	//default to first page if no data collected
	if(count($respAnswerQuestionIDArray)==0 || is_null($pageID)) {
		return $map[0]['page_id'];
	}
	if($goingForward == false) { //flip the array around if heading backward
		$falloffPage = $map[0]['page_id']; // go to first level page if back naving and no more options in the array
		if($pageID == $falloffPage) {
			$falloffPage = "B";
		}
		$map = array_reverse($map);

	} else {
		$falloffPage = "E";
	}
	$placeMarkerSet = false; //placemarker sets the capo at the current page, so we will only focus on all pages forward of the current page
	 //if advancing to next page in survey
		for($a=0; $a<count($map);++$a) {
			//begin searching now that we're beyond the place marker
			if($placeMarkerSet == true) {
				//if the answers submitted thus far matches the next page id in line, set as next page
				if(in_array($map[$a]['question_id_parent'], $respAnswerQuestionIDArray)) {
					//if skipping to most current page ($goToCurrentPage is true) then skip to first relevant page that is not a text page
					if($goToCurrentPage == false || ($map[$a]['page_type'] != 2 && !in_array($map[$a]['page_id'], $respAnswerPageIDArray))) {
						return $map[$a]['page_id'];
						break;
					}
				}
			}
			if($map[$a]['page_id'] == $pageID) { //place marker found! set capo
				$placeMarkerSet = true;
			}
		}
		return $falloffPage; //if no more branches found, go to end page
}
function inactivePage() {

?>
<p class="surveyQuestion">The survey is current inactive.</p>
<?php
}
function beginPage() {
	global $respID;
	global $surveyID;
	global $lastPageIDSubmitted;
	global $beginButton;
	global $respFirst;
	global $respLast;
	$DBH = new Takesurvey();
	$beginPage = $DBH->get_begin_page($surveyID);
	$beginPage = str_replace("[RESPONDENT NAME]",$respFirst . " " . $respLast,$beginPage); //swap variable for answer
	echo $beginPage;
//RESET BUTTON CLICKED
if(isset($_POST['btnResetWarning'])) {
	showModal("Permanently Delete Answers", "<div class='modal-body'><span class='redalert'>WARNING: </span> You are about to permanently delete all answers you previously submitted. Once you click 'Confirm' this action cannot be undone. Are you sure you wish to continue?</div><div class='modal-footer'><button type='button' class='btn btn-default' data-dismiss='modal'>Cancel</button><input class='btn btn-danger' type='submit' value='Confirm Delete Answers' name='btnReset' /></div>");
}
	?>
<section class="main-section buttons">
<div class="container">
<input type="submit" class="btn btn-primary preload" name="btnResume" value="<?php echo $beginButton; ?>" />
</div>
<div class="container" style='margin-top:30px;'>
<?php
	if(!empty($lastPageIDSubmitted)) {
		echo "<input type='submit' class='btn-link strong' name='btnStartFromBeginning' value='Start From The Beginning' />";
		//echo "<input type='submit' class='btn-link strong' name='btnResetWarning' value='Delete My Answers & Start From Beginning'  data-toggle='tooltip' data-placement='bottom' title='WARNING: Will result in loss of survey data.' />";
	}
?>
</div>
</section><!-- end main-section buttons -->
<?php
}
function endPage() {
	global $respID;
	global $surveyID;
	$DBH = new Takesurvey();
	$DBH->mark_survey_completed($surveyID, $respID);
	$endPageArray = $DBH->get_end_page($surveyID);
	$endPage = $endPageArray['end_page'];
	$showSummary = $endPageArray['show_summary'];
	echo $endPage;
	if($showSummary == true) {
		echo '<section class="main-section buttons" style="margin-top:25px;">';
		echo '<div class="container">';
		echo '<a href="#" class="btn btn-primary" id="btnPrintSummary"> Print Summary</a>';
		echo '</div>';
		echo '</section>';
		summary($respID, $surveyID);
	}
    ?>
<script>
$(document).ready(function(){
	$("#btnPrintSummary").click(function(){
		$("#divSummary").show();
		window.print();
	});
});
</script>
<?php
}
function surveyPosition($respID, $surveyID, $pageID) {
	$DBH = new Takesurvey();
	$map = surveyMap($surveyID);
	$respAnswerQuestionIDArray = $DBH->question_id_answers_not_zero($respID);
	$questionIDArray = [];
	for($a=0; $a<count($map);++$a) {
		if($map[$a]['page_id'] == $pageID) { //place marker found! set capo
			// should the respondent be on this page? This will test the parent question id of the current page
			//this tells us the respondent shouldn't be on this page. This only somewhat works because it allows users to manipulate querystring and jump ahead in the survey if the page is acceptable from a previous entry. Modify to ensure this can't happen, and the respondent follows the proper page sequence.
			if(!in_array($map[$a]['question_id_parent'], $respAnswerQuestionIDArray)) {
				if(	$map[$a]['question_id_parent'] == 0) {
					return true;
				} else {
					return false;
				}
			}
			$questionIDArray[0] = $map[$a]['question_id_parent_5'];
			$questionIDArray[1] = $map[$a]['question_id_parent_4'];
			$questionIDArray[2] = $map[$a]['question_id_parent_3'];
			$questionIDArray[3] = $map[$a]['question_id_parent_2'];
			$questionIDArray[4] = $map[$a]['question_id_parent'];
			$returnArray[1] = $a; //current page count for progress bar
			break;
		}
	}
	$ar = $DBH->parent_pages_question_desc($surveyID, $questionIDArray);
	if(count($ar)>0) {
        $position = '';
        for($h=0;$h<count($ar);++$h) {
			$position .= "<span class='PISitem'>" . $ar[$h] . "</span>";
			if($h<count($ar)-1) {
				$position .= "<span class='glyphicon glyphicon-chevron-right' style='color:#2479a5; padding:0px 12px;'></span>";
			}
		}
		$returnArray[3] = end($ar); //get current position
		$returnArray[0] = $position;
		$returnArray[2] = count($map); //total page count for progress bar
		return $returnArray;
	}
}
function summary($respID, $surveyID) {
	$DBH = new Takesurvey();
	$respAnswerArray = $DBH->question_id_answer_value_not_zero($respID);
	$map = surveyMap($surveyID);
	$sumCt = 0;
	for($q=0;$q<count($respAnswerArray);++$q){
		$respAnswerQuestionIDArray[$q] = $respAnswerArray[$q]['question_id']; //make question id array one dimensional for in_array
	}
	for($a=0; $a<count($map);++$a) {
		if((in_array($map[$a]['question_id_parent'], $respAnswerQuestionIDArray) || $map[$a]['question_id_parent'] == 0) && $map[$a]['page_type'] !=2) {
			$summary[$sumCt]['page_id'] = $map[$a]['page_id'];
			$summary[$sumCt]['page_type'] = $map[$a]['page_type'];
			$summary[$sumCt]['question_id_parent'] = $map[$a]['question_id_parent'];
			$summary[$sumCt]['level'] = $map[$a]['level'];
			$pageIDArray[$sumCt] = $map[$a]['page_id']; //create single dimension of page IDs to get question descs for summary
			$sumCt++;
		}
	}
	echo "<div id='divSummary'>";
	echo "<table class='tblSummary'>";
	$questionDescArray = $DBH->all_question_desc_for_summary($surveyID, $pageIDArray);
	//echo "<pre>",print_r($questionDescArray),"</pre>";
	for($s=0;$s<count($summary);++$s) {
		unset($value);
		unset($pageHeading);
		$value = $summary[$s]['question_id_parent'];
		$pageHeading = array_values(array_filter($questionDescArray, function($ar) use ($value) { return ($ar["question_id"] == $value); }));
		if($summary[$s]['page_type'] == 0) {
			$colHeader = "HOURS";
			$char = "";
			$pageDesc = "Branch";
		} else {
			$colHeader = "PCT";
			$char = "%";
			$pageDesc = $pageHeading[0]['question_desc'];
		}
		unset($value);
		unset($questions_filtered);
		$value = $summary[$s]['page_id'];
		$questions_filtered = array_values(array_filter($questionDescArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
		echo "<tr><td class='summaryHeader'>$pageDesc</td><td class='summaryHeader text-right'>$colHeader</td></tr>";
		for($q=0;$q<count($questions_filtered);++$q) {
			echo "<tr>";
			unset($value);
			unset($get_answer);
			$value = $questions_filtered[$q]['question_id'];
			$get_answer = array_values(array_filter($respAnswerArray, function($ar) use ($value) { return ($ar["question_id"] == $value); }));
			$get_answer = $get_answer[0]['answer_value'];
			if(empty($get_answer)) {
				$get_answer = 0;
			}
			unset($fontWeight);
			if($get_answer==true) {
				$fontWeight=" strong";
			}
			echo "<td class='" . $fontWeight . "' style='padding-left: " . ($summary[$s]['level']-2)*20 . "px;'>" . $questions_filtered[$q]['question_desc'] . "</td>";
			echo "<td class='text-right" . $fontWeight . "'>" . $get_answer . "$char</td></tr>";
		}
	}//end summary (page) loop
	echo "</table>";
	echo "</div>";
}
function deleteArray($surveyID, $pageID, $qaArray) {
	//We are being surgical about what gets deleted. We used to delete everything from current page forward. NOW Focusing on the current page only, whatever question is entered as zero, we are flagging any spawn of that zero question and deleting from the answer database. Additionally, any answers previously stored on the current page are also getting deleted to make room for the new answers.
	for($x=0;$x<count($qaArray);++$x) {
		if($qaArray[$x]['answer_value']==0) { //if zero answer then add to the delete array
			$deleteTheseIDSpawns[] = $qaArray[$x]['question_id'];
		}
	}
	//echo "<pre>",print_r($deleteArray),"</pre>";
	//return;
	$DBH = new Takesurvey();
	$map = surveyMap($surveyID);
	//echo "<table border=1 width=500><tr><th>seq</th><th>question_id</th></tr>";
	//for($q=0;$q<count($deleteArray);++$q){
	//	echo "<tr><td>" . $q . "</td><td>" . $deleteArray[$q] . "</td></tr>";
	//}
	//echo "</table><br><Br>";
	//$placeMarkerSet = false; //placemarker sets the capo at the current page, so we will only focus on all pages forward of the current page
	//echo "<table border=1 width=500>";
	//echo "<tr><th>page_id</th><th>qidp1</th><th>qidp2</th><th>qidp3</th><th>qidp4</th><th>qidp5</th><th>level</th><th>marker</th></tr>";
	for($a=0; $a<count($map);++$a) {
		//if($map[$a]['page_id'] == $pageID) { //place marker found! set capo
		//	$placeMarkerSet = true;
		//}
		//echo "<tr>";
		//echo "<td>" . $map[$a]['page_id'] . "</td>";
		//echo "<td>" . $map[$a]['question_id_parent'] . "</td>";
		//echo "<td>" . $map[$a]['question_id_parent_2'] . "</td>";
		//echo "<td>" . $map[$a]['question_id_parent_3'] . "</td>";
		//echo "<td>" . $map[$a]['question_id_parent_4'] . "</td>";
		//echo "<td>" . $map[$a]['question_id_parent_5'] . "</td>";
		//echo "<td>" . $map[$a]['level'] . "</td><td>";
		//if($placeMarkerSet == true) { //begin searching now that we're beyond the place marker
			//if the answers submitted thus far matches the next page id in line, set as next page

		// KILL THE SPAWN!!
		if(	in_array($map[$a]['question_id_parent'], $deleteTheseIDSpawns) ||
			in_array($map[$a]['question_id_parent_2'], $deleteTheseIDSpawns) ||
			in_array($map[$a]['question_id_parent_3'], $deleteTheseIDSpawns) ||
			in_array($map[$a]['question_id_parent_4'], $deleteTheseIDSpawns) ||
			in_array($map[$a]['question_id_parent_5'], $deleteTheseIDSpawns) ||
			$map[$a]['page_id'] == $pageID) {
			//echo "<div style='background:pink'>delete</div>";
			$pageIDArray[] = $map[$a]['page_id']; //set in delete array
		}
		//}
		//echo "</td></tr>";
	}
	//echo "</table>";
	return $pageIDArray;
}
function deleteArrayOLDXXXXXXXXXXXXXX($surveyID, $pageID, $respID) {
	$map = surveyMap($surveyID);
	$placeMarkerSet = false; //placemarker sets the capo at the current page, so we will only focus on all pages forward of the current page
	for($a=0; $a<count($map);++$a) {
		if($map[$a]['page_id'] == $pageID) { //place marker found! set capo
			$placeMarkerSet = true;
		}
		if($placeMarkerSet == true) { //flag ALL pages from current page forward for deletion
			$pageIDArray[] = $map[$a]['page_id'];
		}
	}
	return $pageIDArray;
}
function findParentPage($surveyID, $questionIDParent) {
	$DBH = new Takesurvey();
	$parentPageID = $DBH->parent_page_id($surveyID, $questionIDParent);
	return $parentPageID;
}























function testmap($respID, $surveyID, $pageID, $goingForward) {
	$DBH = new Takesurvey();
	$respAnswerQuestionIDArray = $DBH->question_id_answers_not_zero($respID);
	$map = surveyMap($surveyID);
	if($goingForward == false) { //flip the array around if heading backward
		$falloffPage = $map[0]['page_id']; // go to first level page if back naving and no more options in the array
		if($pageID == $falloffPage) {
			$falloffPage = "B";
		}
		$map = array_reverse($map);

	} else {
		$falloffPage = "E";
	}
	echo "<table border=1 width=500><tr><th>seq</th><th>question_id</th></tr>";
	for($q=0;$q<count($respAnswerQuestionIDArray);++$q){
		echo "<tr><td>" . $q . "</td><td>" . $respAnswerQuestionIDArray[$q] . "</td></tr>";
	}
	echo "</table><br><Br>";
	$placeMarkerSet = false; //placemarker sets the capo at the current page, so we will only focus on all pages forward of the current page
	 //if advancing to next page in survey
	 echo "<table border=1 width=500>";
	 echo "<tr><th>page_id</th><th>qidp1</th><th>qidp2</th><th>qidp3</th><th>qidp4</th><th>qidp5</th><th>level</th><th>marker</th></tr>";
	for($a=0; $a<count($map);++$a) {
		echo "<tr>";
		echo "<td>" . $map[$a]['page_id'] . "</td>";
		echo "<td>" . $map[$a]['question_id_parent'] . "</td>";
		echo "<td>" . $map[$a]['question_id_parent_2'] . "</td>";
		echo "<td>" . $map[$a]['question_id_parent_3'] . "</td>";
		echo "<td>" . $map[$a]['question_id_parent_4'] . "</td>";
		echo "<td>" . $map[$a]['question_id_parent_5'] . "</td>";
		echo "<td>" . $map[$a]['level'] . "</td><td>";
		if($map[$a]['page_id'] == $pageID) { //place marker found! set capo
			echo "<div style='background:yellow'>current</div>";
		}
		if($placeMarkerSet == true) { //begin searching now that we're beyond the place marker
			//if the answers submitted thus far matches the next page id in line, set as next page
			if(in_array($map[$a]['question_id_parent'], $respAnswerQuestionIDArray)) {
				echo "<div style='background:lime'>true</div>";
			}
		}
		echo "</td></tr>";
		if($map[$a]['page_id'] == $pageID) { //place marker found! set capo
			$placeMarkerSet = true;
		}
	}
	echo "</table>";
}