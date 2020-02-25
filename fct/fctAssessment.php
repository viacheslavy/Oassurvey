<?php
function cstdeletesurvey() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$DBH = new Account();
	$singleSurvey = $DBH->single_survey($accountID, $surveyID);
	$surveyName = $singleSurvey['survey_name'];
	if(isset($_POST['btnDeleteSurvey'])) {
		$DBH->delete_survey($surveyID);
		header("Location:?rq=assmt");
		exit();
	}
	$body = "
<form method='post'>
<div class='modal-body'>

<p class='redalert strong'>WARNING: You are about to permanently delete $surveyName. In addition, all survey data will be permanently deleted. Are you absolutely sure you wish to continue?</p>

</div>
<div class='modal-footer'>
<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
<input type='submit' class='btn btn-danger' name='btnDeleteSurvey' value='Permanently Delete Survey' />
</div>
</form>
			  ";
	showModal("<span class='glyphicon glyphicon-trash'></span> Delete Survey", $body);
}
function csteditsurveyname() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$DBH = new Account();
	$singleSurvey = $DBH->single_survey($accountID, $surveyID);
	$surveyName = $singleSurvey['survey_name'];
	if(isset($_POST['btnEditSurvey'])) {
		$surveyName = filterText($_POST['txtSurveyName']);
		if(strlen($surveyName)==0) {
			$surveyName = $singleSurvey['survey_name'];
		}
		$DBH->edit_survey($accountID, $surveyID, $surveyName);
		header("Location:?rq=assmtopen&sid=$surveyID");
		exit();
	}
	$body = "
<form method='post'>
<div class='modal-body'>

<div class='form-group'>
<label for='pageDesc'>Survey Name:</label>
<input type='textbox' id='surveyName' name='txtSurveyName' class='form-control' maxlength='255' placeholder='Survey Name' value='$surveyName' />
</div>

</div>
<div class='modal-footer'>
<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
<a class='btn btn-danger' href='?rq=assmtopen&sid=" . $surveyID . "&cst=deletesurvey'>Delete Survey</a>
<input type='submit' class='btn btn-primary' name='btnEditSurvey' value='Save Changes' />
</div>
</form>
			  ";
	showModal("Edit Survey", $body);
}
function assmtopen() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$DBH = new Account();
	$singleSurvey = $DBH->single_survey($accountID, $surveyID);
	if(empty($singleSurvey)){ signout(); };
	$surveyName = $singleSurvey['survey_name'];
	$surveyActive = $singleSurvey['survey_active'];
	$responseCount = $singleSurvey['response_count'];
	$respondentCount = $singleSurvey['respondent_count'];
	$completeCount = $singleSurvey['complete_count'];
	if($respondentCount == 0) {
		$responsePct = "0%";
	} else {
		$responsePct = round($responseCount / $respondentCount * 100,0) . "%";
	}
	$responseRate = $responseCount . " of " . $respondentCount . " (" . $responsePct . ")";
	
	if($responseCount == 0) {
		$completePct = "0%";
	} else {
		$completePct = round($completeCount / $responseCount * 100,0) . "%";
	}
	$completionRate = $completeCount . " of " . $responseCount . " (" . $completePct . ")";
	if($surveyActive) {
		$CTAD = "Click To Deactivate";
		$BTN = "primary";
		$activeDesc = "Survey Is Currently <span style='font-weight:bold; color:green; font-size:16px;'>Active</span>";
	} else {
		$CTAD = "Click To Activate";
		$BTN = "primary";
		$activeDesc = "Survey Is Currently <span style='font-weight:bold; color:red; font-size:16px;'>Inactive</span>";
	}
	if(isset($_POST['btnActivate'])) {
		$DBH->activate_deactivate($accountID, $surveyID);
		header("Location:?rq=assmtopen&sid=$surveyID");
		exit();
	}
	assessmentTabs($surveyID, 1);
	echo "<h4>Working on: " . $singleSurvey['survey_name'] . "</h4>";
	echo "<form method='post'>";
    echo "<div class='well' style='margin-top:20px;'>\n";
	
	echo "<div class='row'>\n";
	echo "<div class='col-sm-3'>\n";
	echo "<p class='blue'>" . $activeDesc . ":</p>\n";
	echo "</div>\n";//end col 3
	echo "<div class='col-sm-9'>\n";
	echo "<button type='submit' name='btnActivate' class='btn btn-" . $BTN . " btn-sm' style='padding:0px 5px; font-weight:normal;'>" . $CTAD . "</button>";
	echo "</div>\n";//end col 9
	echo "</div>\n";//end row
	
	echo "<div class='row'>\n";
	echo "<div class='col-sm-3'>\n";
	echo "<p class='blue'>Survey Name:</p>\n";
	echo "</div>\n";//end col 3
	echo "<div class='col-sm-9'>\n";
	echo "<a class='strong' href='?rq=assmtopen&sid=$surveyID&cst=editsurveyname'>" . $surveyName . "</a>\n";
	echo "</div>\n";//end col 9
	echo "</div>\n";//end row
	
	echo "<div class='row'>\n";
	echo "<div class='col-sm-3'>\n";
	echo "<p class='blue'>Response Rate:</p>\n";
	echo "</div>\n";//end col 3
	echo "<div class='col-sm-9'>\n";
	echo "<p class='blue strong'>" . $responseRate . "</p>\n";
	echo "</div>\n";//end col 9
	echo "</div>\n";//end row
	
	echo "<div class='row'>\n";
	echo "<div class='col-sm-3'>\n";
	echo "<p class='blue'>Completion Rate:</p>\n";
	echo "</div>\n";//end col 3
	echo "<div class='col-sm-9'>\n";
	echo "<p class='blue strong'>" . $completionRate . "</p>\n";
	echo "</div>\n";//end col 9
	echo "</div>\n";//end row

	
	echo "</div>\n";//end well
	echo "</form>";
}