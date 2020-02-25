<?php
function settings() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$DBH = new Account();
	$surveyArray = $DBH->single_survey($accountID, $surveyID);
	$settingsRow = $DBH->settings_row($surveyID);
	
	$showSplashPage = $settingsRow['show_splash_page'];
	$showProgressBar = $settingsRow['show_progress_bar'];
	$txtSplashPage = $settingsRow['splash_page'];
	$txtBeginPage = $settingsRow['begin_page'];
	$txtEndPage = $settingsRow['end_page'];
	$showSummary = $settingsRow['show_summary'];
	$txtLogoSplash = $settingsRow['logo_splash'];
	$txtLogoSurvey = $settingsRow['logo_survey'];
	$txtFooter = $settingsRow['footer'];
	$txtContactEmail = $settingsRow['contact_email'];
	$txtContactPhone = $settingsRow['contact_phone'];
	$txtWeeklyHoursText = $settingsRow['weekly_hours_text'];
	$txtAnnualLegalHoursText = $settingsRow['annual_legal_hours_text'];
	
	if(isset($_POST['btnSettings'])) {
		$showSplashPage = substr($_POST['chkShowSplashPage'],0,1);
		$showSummary = substr($_POST['chkShowSummary'],0,1);
		$showProgressBar = substr($_POST['chkShowProgressBar'],0,1);
		$txtSplashPage = trim($_POST['txtSplashPage']);
		$txtLogoSplash = trim($_POST['txtLogoSplash']);
		$txtLogoSurvey = trim($_POST['txtLogoSurvey']);
		$txtBeginPage = trim($_POST['txtBeginPage']);
		$txtEndPage = trim($_POST['txtEndPage']);
		$txtFooter = trim($_POST['txtFooter']);
		$txtContactEmail = trim($_POST['txtContactEmail']);
		$txtContactPhone = trim($_POST['txtContactPhone']);
		$txtWeeklyHoursText = trim($_POST['txtWeeklyHoursText']);
		$txtAnnualLegalHoursText = trim($_POST['txtAnnualLegalHoursText']);
		$DBH->update_settings($surveyID, $showSplashPage, $txtSplashPage, $txtBeginPage, $txtEndPage, $showSummary, $txtFooter, $txtContactEmail, $txtContactPhone, $txtWeeklyHoursText, $txtAnnualLegalHoursText, $txtLogoSplash, $txtLogoSurvey, $showProgressBar);
	}
	if($showSplashPage == 1) {
		$splChecked = " checked='checked'";
	}
	if($showSummary == 1) {
		$smChecked = " checked='checked'";
	}
	if($showProgressBar == 1) {
		$pbChecked = " checked='checked'";
	}
	assessmentTabs($surveyID, 3);
	echo "<h4>" . $surveyArray['survey_name'] . " Settings</h4>";
	//echo "<pre>", print_r($respArray), "</pre>";
	echo "<form method='post'>";
    echo "<div class='well' style='margin-top:20px;'>\n";
	
	echo "<div class='row'>\n";
	echo "<div class='col-sm-3'>\n";
	echo "<p class='blue strong'>Splash Page:</p>\n";
	echo "<label class='blue normal'><input type='checkbox' name='chkShowSplashPage' value='1'" . $splChecked . "> Enable Spash Page</label>";
	echo "</div>\n";//end col 3
	echo "<div class='col-sm-9'>\n";
	echo "<div class='form-group'>\n";
	echo "<textarea id='splashPage' class='form-control' name='txtSplashPage' style='height:150px !important;'>" . $txtSplashPage . "</textarea>\n";
	echo "</div>\n";//end form group
	echo "</div>\n";//end col 9
	echo "</div>\n";//end row
	
	echo "<div class='row'>\n";
	echo "<div class='col-sm-3'>\n";
	echo "<p class='blue strong'>Splash Page Logo:</p>\n";
	echo "</div>\n";//end col 3
	echo "<div class='col-sm-9'>\n";
	echo "<div class='form-group'>\n";
	echo "<input type='textbox' id='logoSplash' name='txtLogoSplash' class='form-control' maxlength='500' placeholder='Enter URL of Logo' value='$txtLogoSplash' />\n";
	echo "</div>\n";//end form group
	echo "</div>\n";//end col 9
	echo "</div>\n";//end row
	
	echo "<div class='row'>\n";
	echo "<div class='col-sm-3'>\n";
	echo "<p class='blue strong'>Survey Pages Logo:</p>\n";
	echo "</div>\n";//end col 3
	echo "<div class='col-sm-9'>\n";
	echo "<div class='form-group'>\n";
	echo "<input type='textbox' id='logoSurvey' name='txtLogoSurvey' class='form-control' maxlength='500' placeholder='Enter URL of Logo' value='$txtLogoSurvey' />\n";
	echo "</div>\n";//end form group
	echo "</div>\n";//end col 9
	echo "</div>\n";//end row
	
	echo "<div class='row'>\n";
	echo "<div class='col-sm-3'>\n";
	echo "<p class='blue strong'>Contact E-mail:</p>\n";
	echo "</div>\n";//end col 3
	echo "<div class='col-sm-9'>\n";
	echo "<div class='form-group'>\n";
	echo "<input type='textbox' id='contactEmail' name='txtContactEmail' class='form-control' maxlength='80' placeholder='Contact Email' value='" . $txtContactEmail . "' />\n";
	echo "</div>\n";//end form group
	echo "</div>\n";//end col 9
	echo "</div>\n";//end row
	
	echo "<div class='row'>\n";
	echo "<div class='col-sm-3'>\n";
	echo "<p class='blue strong'>Contact Phone:</p>\n";
	echo "</div>\n";//end col 3
	echo "<div class='col-sm-9'>\n";
	echo "<div class='form-group'>\n";
	echo "<input type='textbox' id='contactPhone' name='txtContactPhone' class='form-control' maxlength='80' placeholder='Contact Phone' value='$txtContactPhone' />\n";
	echo "</div>\n";//end form group
	echo "</div>\n";//end col 9
	echo "</div>\n";//end row
	
	echo "<div class='row'>\n";
	echo "<div class='col-sm-3'>\n";
	echo "<p class='blue strong'>Begin Page:</p>\n";
	echo "<label class='blue normal'><input type='checkbox' name='chkShowProgressBar' value='1'" . $pbChecked . "> Enable Progress Bar</label>";
	echo "</div>\n";//end col 3
	echo "<div class='col-sm-9'>\n";
	echo "<div class='form-group'>\n";
	echo "<textarea id='beginPage' class='form-control' name='txtBeginPage' style='height:150px !important;'>$txtBeginPage</textarea>\n";
	echo "</div>\n";//end form group
	echo "</div>\n";//end col 9
	echo "</div>\n";//end row
	
	echo "<div class='row'>\n";
	echo "<div class='col-sm-3'>\n";
	echo "<p class='blue strong'>End Page:</p>\n";
	echo "<label class='blue normal'><input type='checkbox' name='chkShowSummary' value='1'" . $smChecked . "> Enable Print Summary</label>";
	echo "</div>\n";//end col 3
	echo "<div class='col-sm-9'>\n";
	echo "<div class='form-group'>\n";
	echo "<textarea id='endPage' class='form-control' name='txtEndPage' style='height:150px !important;'>$txtEndPage</textarea>\n";
	echo "</div>\n";//end form group
	echo "</div>\n";//end col 9
	echo "</div>\n";//end row
		
	echo "<div class='row'>\n";
	echo "<div class='col-sm-3'>\n";
	echo "<p class='blue strong'>Footer:</p>\n";
	echo "</div>\n";//end col 3
	echo "<div class='col-sm-9'>\n";
	echo "<div class='form-group'>\n";
	echo "<textarea id='footer' class='form-control' name='txtFooter' style='height:150px !important;'>$txtFooter</textarea>\n";
	echo "</div>\n";//end form group
	echo "</div>\n";//end col 9
	echo "</div>\n";//end row
	
	echo "<div class='row'>\n";
	echo "<div class='col-sm-3'>\n";
	echo "<p class='blue strong'>Weekly Hours Prompt:</p>\n";
	echo "</div>\n";//end col 3
	echo "<div class='col-sm-9'>\n";
	echo "<div class='form-group'>\n";
	echo "<textarea id='weeklyHours' class='form-control' name='txtWeeklyHoursText' style='height:150px !important;'>$txtWeeklyHoursText</textarea>\n";
	echo "</div>\n";//end form group
	echo "</div>\n";//end col 9
	echo "</div>\n";//end row
	
	echo "<div class='row'>\n";
	echo "<div class='col-sm-3'>\n";
	echo "<p class='blue strong'>Annual Legal Hours Prompt:</p>\n";
	echo "</div>\n";//end col 3
	echo "<div class='col-sm-9'>\n";
	echo "<div class='form-group'>\n";
	echo "<textarea id='annualLegalHours' class='form-control' name='txtAnnualLegalHoursText' style='height:150px !important;'>$txtAnnualLegalHoursText</textarea>\n";
	echo "</div>\n";//end form group
	echo "</div>\n";//end col 9
	echo "</div>\n";//end row

	echo "<div class='row'>\n";
	echo "<div class='col-sm-12 text-right'>\n";
	echo "<input type='submit' name='btnSettings' class='btn btn-primary' value='Save Settings' />\n";
	echo "</div>\n";//end col 12
	echo "</div>\n";//end row
	
	echo "</div>\n";//end well
	echo "</form>";
}

?>