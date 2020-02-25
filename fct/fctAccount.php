<?php
function signout() {
	logoutUser();
}
function assessmentTabs($surveyID, $activeTab) {
	global $accountID;
	verifySurvey($accountID, $surveyID);
	for($t=1;$t<=10;++$t) {
		if($activeTab == $t) {
			$tab[$t] = " active";
		} else {
			$tab[$t] = "";
		}
	}
	?>
<ul class="nav nav-tabs">
  <li class="nav-item<? echo $tab[1]; ?>">
	<a class="nav-link" href="?rq=assmtopen&sid=<? echo $surveyID; ?>">Home</a>
  </li>
  <li class="nav-item<? echo $tab[2]; ?>">
	<a class="nav-link" href="?rq=content&sid=<? echo $surveyID; ?>">Content</a>
  </li>
  <li class="nav-item<? echo $tab[3]; ?>">
	<a class="nav-link" href="?rq=settings&sid=<? echo $surveyID; ?>">Settings</a>
  </li>
  <!--
  <li class="nav-item<? //echo $tab[4]; ?>">
	<a class="nav-link" href="#">Appearance</a>
  </li>
  -->
  <li class="nav-item<? echo $tab[5]; ?>">
	<a class="nav-link" href="?rq=respondents&sid=<? echo $surveyID; ?>">Respondents</a>
  </li>
  <li class="nav-item<? echo $tab[6]; ?>">
	<a class="nav-link" href="#">Invitations</a>
  </li>
  <li class="nav-item<? echo $tab[7]; ?>">
	<a class="nav-link" href="?rq=reports&sid=<? echo $surveyID; ?>">X</a>
  </li>
  <li class="nav-item<? echo $tab[9]; ?>">
	<a class="nav-link" href="?rq=rsprofile&sid=<? echo $surveyID; ?>">Profile</a>
  </li>
  <li class="nav-item<? echo $tab[8]; ?>">
	<a class="nav-link" href="?rq=repuw&sid=<? echo $surveyID; ?>">Report</a>
  </li>
  <li class="nav-item<? echo $tab[10]; ?>">
	<a class="nav-link" href="?rq=repind&sid=<? echo $surveyID; ?>">Individual</a>
  </li>
</ul>
<div class="spacer">&nbsp;</div>
<?php
}

function cstnewsurvey() {
	global $accountID;
	$DBH = new Account();
	$surveyArray = $DBH->survey_array($accountID);
	$surveyCopyOptions = "<option value=''></option>";
	for($s=0;$s<count($surveyArray);++$s) {
		$surveyCopyOptions .= "<option value='" . $surveyArray[$s]['survey_id'] . "'>" . $surveyArray[$s]['survey_name'] . "</option>";
	}
	if(isset($_POST['btnNewSurvey'])) {
		$surveyType = substr($_POST['slctSurveyType'],0,1);
		$surveyIDToCopy = substr($_POST['slctSurveyToCopy'],0,12);
		$surveyName = filterText($_POST['txtSurveyName']);
		if(strlen($surveyName)==0) {
			$surveyName = "New Survey";
		}
		if($surveyType == 2 && empty($surveyIDToCopy)) {
			header("Location:?rq=assmt");
			exit();
		} else {
			$newSurveyID = $DBH->new_survey($accountID, $surveyName);
			if($surveyType == 1) { //if new  survey from scratch, seed settings. If copy it will be inserted in another process
				$DBH->seed_settings_row($newSurveyID);
			} elseif($surveyType == 2) {
				$DBH->copy_survey($newSurveyID, $surveyIDToCopy);
			}
			header("Location:?rq=assmtopen&sid=$newSurveyID");
			exit();
		}
	}
	$body = "
<form method='post'>
<div class='modal-body'>

<div class='form-group'>
<label for='surveyType'>Create Options:</label>
<select class='form-control' id='surveyType' name='slctSurveyType'>
<option value='1'>Create New Survey From Scratch</option>
<option value='2'>Copy Existing Survey</option>
</select>
</div>

<div class='form-group' id='surveyCopyDiv'>
<label for='surveyCopy'>Select Survey To Copy:</label>
<select class='form-control' id='surveyCopy' name='slctSurveyToCopy'>
" . $surveyCopyOptions . "
</select>
</div>

<div class='form-group'>
<label for='surveyName'>Survey Name:</label>
<input type='textbox' id='surveyName' name='txtSurveyName' class='form-control' maxlength='255' placeholder='Survey Name' value='$surveyName' />
</div>

</div>
<div class='modal-footer'>
<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
<input type='submit' class='btn btn-primary' name='btnNewSurvey' value='Create Survey' />
</div>
</form>
			  ";
	showModal("New Survey", $body);
?>
<script>
$(document).ready(function(){
	$("#surveyCopyDiv").hide();
	$("#surveyType").change(function () {
		var thisval = $(this).val();  //get option id
		if(thisval == 2) {
			$("#surveyCopyDiv").slideDown(200);
		} else {
			$( "#surveyCopyDiv" ).slideUp(200);
		}
	});
	$("#surveyCopy").change(function () {
		var thistext = $("#surveyCopy option:selected").text();  //get exam name
		if(thistext.length > 0) { 
			$("#surveyName").val("Copy of "+thistext);
		} else {
			$("#surveyName").val("");
		}
	});
});
</script>
<?php
}

function assmt() {
	global $accountID;
	$DBH = new Account();
	$surveyArray = $DBH->survey_array($accountID);

	$response = '';
	$response .= "<h4>Surveys $accountID</h4>";
	$response .= "<div class='row'>\n";
	$response .= "<div class='col-sm-12'>\n";
	$response .= "<a class='btn btn-primary btn-sm' href='?rq=assmt&cst=newsurvey'><span class='glyphicon glyphicon-plus'></span> Create Survey Or Make Copy</a>";
	$response .= "</div>\n"; //end col
	$response .= "</div><br />\n"; //end row
	$response .= "<div class='well'>\n";
	for($i=0;$i<count($surveyArray);++$i) {
		$response .= "<div class='surveyList' style='margin-bottom:5px;'><a class='strong' href='?rq=assmtopen&sid=" . $surveyArray[$i]['survey_id'] . "'>" . $surveyArray[$i]['survey_name'] . "</a></div>\n";
	}
	$response .= "</div>\n"; //end well

    return $response;
}

function myacct() {
	global $accountID;
	$DBH = new Account();
	$accountArray = $DBH->account_array($accountID);
	$accountUsn = $accountArray['account_usn'];
	$accountFirstName = $accountArray['account_first_name'];
	$accountLastName = $accountArray['account_last_name'];
	$accountEmailAddress = $accountArray['account_email_address'];
	if(isset($_POST['btnSaveMyAccount'])) {
		$accountUsn = substr(trim($_POST['txtAccountUsn']),0,20);
		if(strlen($accountUsn) < 6 || preg_match('/\s/',$accountUsn)) {
			$error .= "<p class='blue strong'>User Name must be between 6 and 20 characters with no spaces.</p>";
		}
		$usnExists = $DBH->user_exists($accountID, $accountUsn);
		if($usnExists) {
			$error .= "<p class='blue strong'>This User Name is not available. Please try another.</p>";
		}
		$accountFirstName = substr(trim($_POST['txtAccountFirstName']),0,255);
		if(strlen($accountFirstName) == 0) {
			$accountFirstName = $accountArray['account_first_name'];
		}
		$accountLastName = substr(trim($_POST['txtAccountLastName']),0,255);
		if(strlen($accountLastName) == 0) {
			$accountLastName = $accountArray['account_last_name'];
		}
		$accountEmailAddress = substr(trim($_POST['txtAccountEmailAddress']),0,255);
		if(strlen($accountEmailAddress) == 0) {
			$accountEmailAddress = $accountArray['account_email_address'];
		}
		if(isValidEmail($accountEmailAddress) == false) {
			$error .= "<p class='blue strong'>The e-mail address is not valid as entered.</p>";
		}
		$accountPwd = substr(trim($_POST['txtAccountPwd']),0,20);
		$accountPwdRepeat = substr(trim($_POST['txtAccountPwdRepeat']),0,20);
		if(strlen($accountPwd) > 0) {
			if(strlen($accountPwd) < 6 || preg_match('/\s/',$accountPwd)) {
				$error .= "<p class='blue strong'>Password must be between 6 and 20 characters with no spaces.</p>";
			}elseif($accountPwd != $accountPwdRepeat) {
				$error .= "<p class='blue strong'>The password fields do not match.</p>";
			} else {
				$DBS = new Signin();
				$hashedPwd = $DBS->hash_password($accountPwd);
				$passwordSuccess = "<p class='blue strong'>These changes also included a password change. Please make note of your new password now.</p>";
			}
		}
		if(!empty($error)) {
			$body = "<div class='modal-body'>" . $error . "</div><div class='modal-footer'><button type='button' class='btn btn-primary' data-dismiss='modal'>Okay</button></div>";
			showModal("My Account Errors", $body);
		} else {
			$DBH->update_account($accountID, $accountUsn, $accountFirstName, $accountLastName, $accountEmailAddress, $hashedPwd);
			$body = "<div class='modal-body'><p class='blue strong'>Success! The changes you made have been saved." . $passwordSuccess . "</p></div><div class='modal-footer'><button type='button' class='btn btn-primary' data-dismiss='modal'>Okay</button></div>";
			showModal("My Account Changes Made", $body);
		}
	}
	
	echo "<h4>My Account</h4>";
	echo "<div class='well'>\n";
	echo "	<form method='post'  autocomplete='off'>
			<p style='color:#AAA;'>CONTACT INFO</p>
			<div class='form-group'>
			<label for='accountFirstName'>First Name:</label>
			<input type='textbox' id='accountFirstName' name='txtAccountFirstName' class='form-control' maxlength='255' placeholder='First Name' value='$accountFirstName' />
			</div>
			
			<div class='form-group'>
			<label for='accountLastName'>Last Name:</label>
			<input type='textbox' id='accountLastName' name='txtAccountLastName' class='form-control' maxlength='255' placeholder='Last Name' value='$accountLastName' />
			</div>
			
			<div class='form-group'>
			<label for='accountEmailAddress'>E-mail Address:</label>
			<input type='textbox' id='accountEmailAddress' name='txtAccountEmailAddress' class='form-control' maxlength='255' placeholder='E-mail Address' value='$accountEmailAddress' />
			</div>
			<br /><p style='color:#AAA;'>SIGN IN CREDENTIALS</p>
			<div class='form-group'>
			<label for='accountUsn'>User Name / Login ID:</label>
			<input type='textbox' id='accountUsn' name='txtAccountUsn' class='form-control' maxlength='20' placeholder='User Name' value='$accountUsn' />
			</div>
			
			<div class='form-group'>
			<label for='accountPwd'>New Password:</label>
			<input type='password' id='accountPwd' name='txtAccountPwd' class='form-control' maxlength='20' placeholder='Leave blank to keep old password' />
			</div>
			
			<div class='form-group'>
			<label for='accountPwdRepeat'>Repeat New Password:</label>
			<input type='password' id='accountPwdRepeat' name='txtAccountPwdRepeat' class='form-control' maxlength='20' placeholder='Repeat password above' />
			</div>
			
			<div class='form-group'>
			<input type='submit' class='btn btn-primary' name='btnSaveMyAccount' value='Save Changes' />
			</div>
			</form>
			";
	echo "</div>\n"; //end well
}
?>