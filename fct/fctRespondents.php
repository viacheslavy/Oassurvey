<?php
function cstnewrespondent() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$DBH = new Account();
	$respAccessCode = generateRandomString(10);
	//$singleQuestion = $DBH->single_question($surveyID, $questionID);
	$pageDesc = $branchFrom;
	$labelArr = $DBH->get_field_labels($surveyID);
	if(isset($_POST['btnNewRespondent'])) {
		$respAccessCode = filterText($_POST['txtRespAccessCode']);
		if(empty($respAccessCode)) { // auto access code if blank
			$respAccessCode = generateRandomString(10);
		}
		$respFirst = filterText($_POST['txtRespFirst']);
		$respLast = filterText($_POST['txtRespLast']);
		$respEmail = filterText($_POST['txtRespEmail']);
		$respAlt = substr($_POST['chkRespAlt'],0,1);
		$cust1 = filterText($_POST['txtCust1']);
		$cust2 = filterText($_POST['txtCust2']);
		$cust3 = filterText($_POST['txtCust3']);
		$cust4 = filterText($_POST['txtCust4']);
		$cust5 = filterText($_POST['txtCust5']);
		$cust6 = filterText($_POST['txtCust6']);
		$respInsert = $DBH->insert_new_respondent($surveyID, $respAccessCode, $respFirst, $respLast, $respEmail, $respAlt, $cust1, $cust2, $cust3, $cust4, $cust5, $cust6);
		if($respInsert == false) {
			 $acHasError = " has-error";
			 $acErrorMsg = "This Access Code is assigned to another person. Please try another.";
		} else {
			header("Location:?rq=respondents&sid=$surveyID");
			exit();
		}
	}
	if($respAlt == 1) {
		$reChecked = " checked='checked'";
	}
	$body = "
			<form method='post'>
				<div class='modal-body'>
				<div id='personScroll'>
					<div class='form-group" . $acHasError . "'>
					<label class='control-label' for='txtRespAccessCode'>Access Code:</label>
					<input type='textbox' id='txtRespAccessCode' name='txtRespAccessCode' class='form-control txtbox' maxlength='20' placeholder='Access Code' value='$respAccessCode' aria-describedby='acError' />
					<span id='acError' class='help-block'>" . $acErrorMsg . "</span>
					</div>
				
					<div class='form-group'>
					<label for='txtRespFirst'>First Name:</label>
					<input type='textbox' id='txtRespFirst' name='txtRespFirst' class='form-control txtbox' maxlength='255' placeholder='First Name' value='$respFirst' />
					</div>
				
					<div class='form-group'>
					<label for='txtRespLast'>Last Name:</label>
					<input type='textbox' id='txtRespLast' name='txtRespLast' class='form-control txtbox' maxlength='255' placeholder='Last Name' value='$respLast' />
					</div>
					
					<div class='form-group'>
					<label for='txtRespEmail'>Email Address:</label>
					<input type='textbox' id='txtRespEmail' name='txtRespEmail' class='form-control txtbox' maxlength='255' placeholder='Email Address' value='$respEmail' />
					</div>
					
					<div class='checkbox'>
					<label><input type='checkbox' name='chkRespAlt' value='1'" . $reChecked . "> <strong>Receives Alternate Text</strong></label>
					</div>
					
					<div class='form-group'>
					<label for='txtCust1'>" . $labelArr["cust_1_label"] . ":</label>
					<input type='textbox' id='txtCust1' name='txtCust1' class='form-control txtbox' maxlength='255' placeholder='Custom 1' value='$cust1' />
					</div>
					
					<div class='form-group'>
					<label for='txtCust2'>" . $labelArr["cust_2_label"] . ":</label>
					<input type='textbox' id='txtCust2' name='txtCust2' class='form-control txtbox' maxlength='255' placeholder='Custom 2' value='$cust2' />
					</div>
					
					<div class='form-group'>
					<label for='txtCust3'>" . $labelArr["cust_3_label"] . ":</label>
					<input type='textbox' id='txtCust3' name='txtCust3' class='form-control txtbox' maxlength='255' placeholder='Custom 3' value='$cust3' />
					</div>
					
					<div class='form-group'>
					<label for='txtCust4'>" . $labelArr["cust_4_label"] . ":</label>
					<input type='textbox' id='txtCust4' name='txtCust4' class='form-control txtbox' maxlength='255' placeholder='Custom 4' value='$cust4' />
					</div>
					
					<div class='form-group'>
					<label for='txtCust5'>" . $labelArr["cust_5_label"] . ":</label>
					<input type='textbox' id='txtCust5' name='txtCust5' class='form-control txtbox' maxlength='255' placeholder='Custom 5' value='$cust5' />
					</div>
					
					<div class='form-group'>
					<label for='txtCust6'>" . $labelArr["cust_6_label"] . ":</label>
					<input type='textbox' id='txtCust6' name='txtCust6' class='form-control txtbox' maxlength='255' placeholder='Custom 6' value='$cust6' />
					</div>

				</div><!-- end person scroll -->
				</div><!-- end modal body -->
				<div class='modal-footer'>
					<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
					<input type='submit' id='btnNewResp' class='btn btn-primary' name='btnNewRespondent' value='Add New Respondent' />
				</div>
			</form>
			  ";
	showModal("<span class='glyphicon glyphicon-user'></span>&nbsp; Add New Respondent", $body);
?>
<script>
$(document).ready(function() {
     submitEnable();
     $(".txtbox").keyup(function(){
		submitEnable();
     });
	 function submitEnable() {
        if($('#txtRespFirst').val() != '' && $('#txtRespLast').val() != '') {
           $('input[type="submit"]#btnNewResp').prop('disabled', false);
        } else {
			$('input[type="submit"]#btnNewResp').prop('disabled', true);
		}
	 }
 });
</script>
<?php
}
function cstuploadrespondents() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$DBH = new Account();
	if(isset($_POST['btnUploadCSV'])) {
		$dryRun = false;
		$csv = array();
		if($_POST['btnUploadCSV'] == "Check For Errors") {
			$dryRun = true;
		}
		// check there are no errors
		if($_FILES['csv']['error'] == 0){
			$name = $_FILES['csv']['name'];
			$path_parts = pathinfo($name);
			$ext = $path_parts['extension'];
			$type = $_FILES['csv']['type'];
			$tmpName = $_FILES['csv']['tmp_name'];
			// check the file is a csv
			if($ext === 'csv'){
				if(($handle = fopen($tmpName, 'r')) !== FALSE) {
					// necessary if a large csv file
					set_time_limit(60);
					$row = 0;
					while(($data = fgetcsv($handle, ',')) !== FALSE) {
						// number of fields in the csv
						$col_count = count($data);
						// get the values from the csv
						for($i = 0; $i <= 10; ++$i) { //represents the column range. Expand as needed
							$csv[$row][$i] = $data[$i];
						}
						// inc the row
						$row++;
					}
					fclose($handle);
				}
			$countPeople = count($csv)-1;
			if($dryRun == true) {
				$csvUploadMessage = "<div class='app-text'><span style='font-weight:bold; color:green;'>Error Check Complete! </span><br />" . $countPeople . " record(s) were found in the file you selected.</div>";
			}
			else {
				$csvUploadMessage = "<div class='app-text'><span style='font-weight:bold; color:green;'>Upload Complete! </span><br />" . $countPeople . " record(s) were found in the uploaded file. <a class='btn btn-default' href='?rq=respondents&sid=$surveyID'>Finished</a></div>";
			}
			}
			else {
				$csvUploadMessage = "<div class='app-text'>" . $name . " is not a valid CSV file. Please select another file.</div>";
			} // end if $ext == 'csv'
		}
		else {
			$csvUploadMessage = "<div class='app-text'>No file was selected. Please click the Browse button to search for the CSV file on your computer, then click Upload List.</div>";
		} // end if 'error' == 0
		$displayCSVOutput = "<div id='personScroll' style='background-color:#EEE; padding:7px; border: 1px solid #555;'>" . $csvUploadMessage;
	} // end if isset btnUploadCSV
	if(count($csv) > 1) {
		$errorCountAccessCode = 0;
		$errorCountEmail = 0;
		$errorCountDuplicate = 0;
		$countUniqueInserts = 0;
		$countDupesInFile = 0;
		for($r = 1;$r < count($csv); ++$r) { //start at 1 to skip column headings which are on $r->0
			$rowNum = $r + 1;
			//for($c = 0; $c <= count($csv[$r]); ++$c) {
			for($c = 0; $c <= 10; ++$c) { //iterate through columns in active row. currently liited to 10
				if($c == 0) { //if access code
					//if (in_array($csv[$r][$c], $csv)) { // if access code is a file duplicate. need to figure out correct method here
					// 	++$countDupesInFile;
					//}
					if(containsSpecialCharacters($csv[$r][$c]) || strlen($csv[$r][$c]) > 40) { //if special characters found or access code too long then log error
						$errorAccessCode = $errorAccessCode . "<div>Row " . $rowNum . ": " . $csv[$r][$c] . "</div>";
						++$errorCountAccessCode;
					}
					if(containsSpecialCharacters($csv[$r][$c]) || empty($csv[$r][$c]) || strlen($csv[$r][$c]) > 40) { //if blank or invalid then generate 10 character random access code
						$csv[$r][$c] = generateRandomString(10);
					}
				} // end if access code
				if($c == 1) { //if email column
					if(!empty($csv[$r][$c]) && isValidEmail($csv[$r][$c]) == false) { //if email was inputted but is invalid
						$errorEmail = $errorEmail . "<div>Row " . $rowNum . ": " . $csv[$r][$c] . "</div>";
						++$errorCountEmail;
						$csv[$r][$c] = NULL; //set email to null if not a valid address
					}
				}
				if($c == 4) { //if receives alt text
					if($csv[$r][$c] != 1) { //looking for boolean 1 or 0. if not 1 then default to 0
						$csv[$r][$c] = 0;
					}
				}
			} // end column loop
			$respIDFound = getRespID($surveyID, $csv[$r][0]);
			if(!empty($respIDFound)) {
				if($dryRun == false) {
					$DBH->edit_respondent($surveyID, $respIDFound, $csv[$r][0], $csv[$r][2], $csv[$r][3], $csv[$r][1], $csv[$r][4], $csv[$r][5], $csv[$r][6], $csv[$r][7], $csv[$r][8], $csv[$r][9], $csv[$r][10]);
				}
				++$errorCountDuplicate;
			}
			else {
				if($dryRun == false) {
					$DBH->insert_new_respondent($surveyID, $csv[$r][0], $csv[$r][2], $csv[$r][3], $csv[$r][1], $csv[$r][4], $csv[$r][5], $csv[$r][6], $csv[$r][7], $csv[$r][8], $csv[$r][9], $csv[$r][10]);
				}
				++$countUniqueInserts;
			}
		} // end row loop
	} // end if count($csv) > 1
	if($dryRun == true) {
		$tense = "will be";
	}
	else {
		$tense = "were";
	}
	if($errorCountDuplicate > 0) {
		$errorDetailsDuplicate = "<div class='app-text'>Based on the access codes, " . $errorCountDuplicate . " record(s) were found to exist in the exam already or were duplicates in your CSV file. These <span style='font-weight:bold; color:red;'>$tense overwritten</span> with the most recent uploaded information. " . $countUniqueInserts . " unique record(s) $tense uploaded to the exam.</div>";
	}
	if($countDupesInFile > 0) {
		$errorDupesInFile = "<div class='app-text'>" . $countDupesInFile . " duplicate access code(s) were found in your CSV File. Each access code should be unique. Any duplicate records will be overwritten with the final one in your list.</div>";
	}
	if($errorCountAccessCode > 0) {
		$errorDetailsAccessCode = "<div class='app-text'>" . $errorCountAccessCode . " record(s) contained an invalid access code. These $tense replaced by a system-generated access code. The invalid access codes are listed below.</div>";
		$errorAccessCode = "<div style='font-weight:bold;'>Invalid Access Codes:</div>" . $errorAccessCode;
	}
	if($errorCountEmail > 0) {
		$errorDetailsEmail = "<div class='app-text'>" . $errorCountEmail . " record(s) contained an invalid email address. Invalid email addresses $tense removed from the upload. The invalid email addresses are listed below.</div>";
		$errorEmail = "<div style='font-weight:bold; margin-top:10px;'>Invalid Email Addresses:</div>" . $errorEmail;
	}
	$displayCSVOutput = $displayCSVOutput . $errorDetailsDuplicate . $errorDupesInFile . $errorDetailsAccessCode . $errorDetailsEmail . $errorAccessCode . $errorEmail  . "</div>";
	$body = "
				<div class='modal-body'>
				
					<p>In this area you can upload an entire list of respondents at once. Click the <strong>Browse</strong> button below to search for the CSV (Comma Separated Values) file on your computer.</p><p>Click the <strong>Check For Errors</strong> button before uploading your file. This will help identify any issues in your file before uploading the entire list.</p>
					
					<p>Need a CSV file? <a href='/downloads/respondent-template.csv' target='_blank'>Click here to download</a> and fill in the template. Once filled in, save the CSV file to your computer and continue with the upload process.</p>
				
				</div>
				<div class='modal-footer'>
            		<form method='post' enctype='multipart/form-data'>
           			 <input class='btn btn-default' type='file' name='csv' value='' accept='.csv, .CSV' />
					<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
					<input type='submit' id='btnUploadResp' class='btn btn-warning' name='btnUploadCSV' value='Check For Errors' />
					<input type='submit' id='btnUploadResp' class='btn btn-primary' name='btnUploadCSV' value='Upload' />
				</div>
			</form>
			  <div class='modal-body'>" . $displayCSVOutput . "</div>";
	showModal("<span class='glyphicon glyphicon-upload'></span>&nbsp; Upload CSV Respondent List", $body);
?>
<script>
$(document).ready(function() {
     submitEnable();
     $(".txtbox").keyup(function(){
		submitEnable();
     });
	 function submitEnable() {
        if($('#txtRespFirst').val() != '' && $('#txtRespLast').val() != '') {
           $('input[type="submit"]#btnNewResp').prop('disabled', false);
        } else {
			$('input[type="submit"]#btnNewResp').prop('disabled', true);
		}
	 }
 });
</script>
<?php
}
function csteditrespondent() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$respID = $_GET['rid'];
	$DBH = new Account();
	$labelArr = $DBH->get_field_labels($surveyID);
	$singleRespondent = $DBH->single_respondent($surveyID, $respID);
	if($singleRespondent == false) {
		logoutUser();
	}
	$respAccessCode = $singleRespondent['resp_access_code'];
	$respFirst = $singleRespondent['resp_first'];
	$respLast = $singleRespondent['resp_last'];
	$respEmail = $singleRespondent['resp_email'];
	$respAlt = $singleRespondent['resp_alt'];
	$cust1 = $singleRespondent['cust_1'];
	$cust2 = $singleRespondent['cust_2'];
	$cust3 = $singleRespondent['cust_3'];
	$cust4 = $singleRespondent['cust_4'];
	$cust5 = $singleRespondent['cust_5'];
	$cust6 = $singleRespondent['cust_6'];
	if(isset($_POST['btnEditRespondent'])) {
		$respAccessCode = filterText($_POST['txtRespAccessCode']);
		if(empty($respAccessCode)) { 
			$respAccessCode = $singleRespondent['resp_access_code'];
		}
		$respFirst = filterText($_POST['txtRespFirst']);
		$respLast = filterText($_POST['txtRespLast']);
		$respEmail = filterText($_POST['txtRespEmail']);
		$respAlt = substr($_POST['chkRespAlt'],0,1);
		$cust1 = filterText($_POST['txtCust1']);
		$cust2 = filterText($_POST['txtCust2']);
		$cust3 = filterText($_POST['txtCust3']);
		$cust4 = filterText($_POST['txtCust4']);
		$cust5 = filterText($_POST['txtCust5']);
		$cust6 = filterText($_POST['txtCust6']);
		echo "<script>alert('$cust1 - $cust2 - $cust3 - $cust4 - $cust5 - $cust6');</script>";
		$respEdit = $DBH->edit_respondent($surveyID, $respID, $respAccessCode, $respFirst, $respLast, $respEmail, $respAlt, $cust1, $cust2, $cust3, $cust4, $cust5, $cust6);
		if($respEdit == false) {
			 $acHasError = " has-error";
			 $acErrorMsg = "This Access Code is assigned to another person. Please try another.";
		} else {
			header("Location:?rq=respondents&sid=$surveyID");
			exit();
		}
	}
	if($respAlt == 1) {
		$reChecked = " checked='checked'";
	}
	$body = "
			<form method='post'>
				<div class='modal-body'>
				<div id='personScroll'>
					<div class='form-group" . $acHasError . "'>
					<label class='control-label' for='txtRespAccessCode'>Access Code:</label>
					<input type='textbox' id='txtRespAccessCode' name='txtRespAccessCode' class='form-control txtbox' maxlength='20' placeholder='Access Code' value='$respAccessCode' aria-describedby='acError' />
					<span id='acError' class='help-block'>" . $acErrorMsg . "</span>
					</div>
				
					<div class='form-group'>
					<label for='txtRespFirst'>First Name:</label>
					<input type='textbox' id='txtRespFirst' name='txtRespFirst' class='form-control txtbox' maxlength='255' placeholder='First Name' value='$respFirst' />
					</div>
				
					<div class='form-group'>
					<label for='txtRespLast'>Last Name:</label>
					<input type='textbox' id='txtRespLast' name='txtRespLast' class='form-control txtbox' maxlength='255' placeholder='Last Name' value='$respLast' />
					</div>
					
					<div class='form-group'>
					<label for='txtRespEmail'>Email Address:</label>
					<input type='textbox' id='txtRespEmail' name='txtRespEmail' class='form-control txtbox' maxlength='255' placeholder='Email Address' value='$respEmail' />
					</div>
					
					<div class='checkbox'>
					<label><input type='checkbox' name='chkRespAlt' value='1'" . $reChecked . "> <strong>Receives Alternate Text</strong></label>
					</div>
					
					<div class='form-group'>
					<label for='txtCust1'>" . $labelArr["cust_1_label"] . ":</label>
					<input type='textbox' id='txtCust1' name='txtCust1' class='form-control txtbox' maxlength='255' placeholder='Custom 1' value='$cust1' />
					</div>
					
					<div class='form-group'>
					<label for='txtCust2'>" . $labelArr["cust_2_label"] . ":</label>
					<input type='textbox' id='txtCust2' name='txtCust2' class='form-control txtbox' maxlength='255' placeholder='Custom 2' value='$cust2' />
					</div>
					
					<div class='form-group'>
					<label for='txtCust3'>" . $labelArr["cust_3_label"] . ":</label>
					<input type='textbox' id='txtCust3' name='txtCust3' class='form-control txtbox' maxlength='255' placeholder='Custom 3' value='$cust3' />
					</div>
					
					<div class='form-group'>
					<label for='txtCust4'>" . $labelArr["cust_4_label"] . ":</label>
					<input type='textbox' id='txtCust4' name='txtCust4' class='form-control txtbox' maxlength='255' placeholder='Custom 4' value='$cust4' />
					</div>
					
					<div class='form-group'>
					<label for='txtCust5'>" . $labelArr["cust_5_label"] . ":</label>
					<input type='textbox' id='txtCust5' name='txtCust5' class='form-control txtbox' maxlength='255' placeholder='Custom 5' value='$cust5' />
					</div>
					
					<div class='form-group'>
					<label for='txtCust6'>" . $labelArr["cust_6_label"] . ":</label>
					<input type='textbox' id='txtCust6' name='txtCust6' class='form-control txtbox' maxlength='255' placeholder='Custom 6' value='$cust6' />
					</div>

				
				</div><!--end modal body -->
				</div><!--end scrolling div -->
				<div class='modal-footer'>
					<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
					<input type='submit' id='btnNewResp' class='btn btn-primary' name='btnEditRespondent' value='Save Changes' />
				</div>
			</form>
			  ";
	showModal("<span class='glyphicon glyphicon-user'></span>&nbsp; Edit Respondent", $body);
?>
<script>
$(document).ready(function() {
     submitEnable();
     $(".txtbox").keyup(function(){
		submitEnable();
     });
	 function submitEnable() {
        if($('#txtRespFirst').val() != '' && $('#txtRespLast').val() != '') {
           $('input[type="submit"]#btnNewResp').prop('disabled', false);
        } else {
			$('input[type="submit"]#btnNewResp').prop('disabled', true);
		}
	 }
 });
</script>
<?php
}
function cstlabels() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$DBH = new Account();
	$labelArr = $DBH->get_field_labels($surveyID);
	$cust1 = $labelArr["cust_1_label"];
	$cust2 = $labelArr["cust_2_label"];
	$cust3 = $labelArr["cust_3_label"];
	$cust4 = $labelArr["cust_4_label"];
	$cust5 = $labelArr["cust_5_label"];
	$cust6 = $labelArr["cust_6_label"];
	if(isset($_POST['btnSaveLabels'])) {
		$cust1 = filterText($_POST['txtCust1']);
		$cust2 = filterText($_POST['txtCust2']);
		$cust3 = filterText($_POST['txtCust3']);
		$cust4 = filterText($_POST['txtCust4']);
		$cust5 = filterText($_POST['txtCust5']);
		$cust6 = filterText($_POST['txtCust6']);
		$DBH->update_field_labels($surveyID, $cust1, $cust2, $cust3, $cust4, $cust5, $cust6);
		header("Location:?rq=respondents&sid=$surveyID");
		exit();
	}
	$body = "
			<form method='post'>
				<div class='modal-body'>
				<p>Customize up to 6 respondent field labels in the textboxes below:</p>
					<table style='width:100%;'>
						<tr>
							<td>Custom 1:</td>
							<td>
								<div class='form-group'><input required type='textbox' id='txtCust1' name='txtCust1' class='form-control txtbox' maxlength='120' placeholder='Custom 1' value='$cust1' /></div>
							</td>
						</tr>
						<tr>
							<td>Custom 2:</td>
							<td>
								<div class='form-group'><input required type='textbox' id='txtCust2' name='txtCust2' class='form-control txtbox' maxlength='120' placeholder='Custom 2' value='$cust2' /></div>
							</td>
						</tr>
						<tr>
							<td>Custom 3:</td>
							<td>
								<div class='form-group'><input required type='textbox' id='txtCust3' name='txtCust3' class='form-control txtbox' maxlength='120' placeholder='Custom 3' value='$cust3' /></div>
							</td>
						</tr>
						<tr>
							<td>Custom 4:</td>
							<td>
								<div class='form-group'><input required type='textbox' id='txtCust4' name='txtCust4' class='form-control txtbox' maxlength='120' placeholder='Custom 4' value='$cust4' /></div>
							</td>
						</tr>
						<tr>
							<td>Custom 5:</td>
							<td>
								<div class='form-group'><input required type='textbox' id='txtCust5' name='txtCust5' class='form-control txtbox' maxlength='120' placeholder='Custom 5' value='$cust5' /></div>
							</td>
						</tr>
						<tr>
							<td>Custom 6:</td>
							<td>
								<div class='form-group'><input required type='textbox' id='txtCust6' name='txtCust6' class='form-control txtbox' maxlength='120' placeholder='Custom 6' value='$cust6' /></div>
							</td>
						</tr>
					</table>
				</div><!-- end modal body -->
				<div class='modal-footer'>
					<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
					<input type='submit' id='btnNewResp' class='btn btn-primary' name='btnSaveLabels' value='Save Labels' />
				</div>
			</form>
			  ";
	showModal("<span class='glyphicon glyphicon-tag'></span> Customize Field Labels", $body);
?>
<script>
$(document).ready(function() {

 });
</script>
<?php
}
function cstresetrespondent() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$respID = $_GET['rid'];
	$DBH = new Account();
	$singleRespondent = $DBH->single_respondent($surveyID, $respID);
	if($singleRespondent == false) {
		logoutUser();
	}
	$respFirst = $singleRespondent['resp_first'];
	$respLast = $singleRespondent['resp_last'];
	if(isset($_POST['btnResetRespondent'])) {
		$DBH->delete_respondent_survey($surveyID, $respID);
		header("Location:?rq=respondents&sid=$surveyID");
		exit();
	}
	if($respAlt == 1) {
		$reChecked = " checked='checked'";
	}
	$body = "
			<form method='post'>
				<div class='modal-body'>
				<p class='surveyQuestion'><span style='color:red;'><strong>Caution:</strong></span> This action will permanently delete the survey data of <strong> " . $respFirst . " " . $respLast . ".</strong> Are you sure you wish to continue?</p>

				
				</div>
				<div class='modal-footer'>
					<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
					<input type='submit' id='btnNewResp' class='btn btn-warning' name='btnResetRespondent' value='Reset Survey' />
				</div>
			</form>
			  ";
	showModal("<span class='glyphicon glyphicon-user'></span>&nbsp; Confirm Reset Survey", $body);
}
function cstdeleterespondent() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$respID = $_GET['rid'];
	$DBH = new Account();
	$singleRespondent = $DBH->single_respondent($surveyID, $respID);
	if($singleRespondent == false) {
		logoutUser();
	}
	$respFirst = $singleRespondent['resp_first'];
	$respLast = $singleRespondent['resp_last'];
	if(isset($_POST['btnDeleteRespondent'])) {
		$DBH->delete_respondent_survey($surveyID, $respID);
		$DBH->delete_respondent($surveyID, $respID);
		header("Location:?rq=respondents&sid=$surveyID");
		exit();
	}
	if($respAlt == 1) {
		$reChecked = " checked='checked'";
	}
	$body = "
			<form method='post'>
				<div class='modal-body'>
				<p class='surveyQuestion'><span style='color:red;'><strong>WARNING:</strong></span> This action will permanently delete respondent <strong> $respFirst $respLast </strong> from the survey, and will <strong>permanently delete any survey data</strong> $respFirst $respLast provided. Are you sure you wish to continue?</p>

				
				</div>
				<div class='modal-footer'>
					<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
					<input type='submit' id='btnNewResp' class='btn btn-danger' name='btnDeleteRespondent' value='Permanently Delete' />
				</div>
			</form>
			  ";
	showModal("<span class='glyphicon glyphicon-user'></span>&nbsp; Confirm Delete $respFirst $respLast", $body);
}
function cstresetall() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$DBH = new Account();
	if(isset($_POST['btnDeleteAll'])) {
		$DBH->reset_all_respondents($surveyID);
		header("Location:?rq=respondents&sid=$surveyID");
		exit();
	}
	$body = "
			<form method='post'>
				<div class='modal-body'>
				<p class='surveyQuestion'><span style='color:red;'><strong>WARNING:</strong></span> This action will reset all respondents and <strong>PERMANENTLY DELETE ALL SURVEY DATA</strong> from this survey. Are you sure you wish to continue?</p>

				
				</div>
				<div class='modal-footer'>
					<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
					<input type='submit' id='btnNewResp' class='btn btn-warning' name='btnDeleteAll' value='Reset All Respondents' />
				</div>
			</form>
			  ";
	showModal("<span class='glyphicon glyphicon-refresh'></span>&nbsp; Confirm Reset All Respondents", $body);
}
function cstdeleteall() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$DBH = new Account();
	if(isset($_POST['btnDeleteAll'])) {
		$DBH->delete_all_respondents($surveyID);
		header("Location:?rq=respondents&sid=$surveyID");
		exit();
	}
	$body = "
			<form method='post'>
				<div class='modal-body'>
				<p class='surveyQuestion'><span style='color:red;'><strong>WARNING:</strong></span> This action will <strong>PERMANENTLY DELETE ALL RESPONDENTS</strong> along with <strong>ALL SURVEY DATA</strong> from the survey. Are you sure you wish to continue?</p>

				
				</div>
				<div class='modal-footer'>
					<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
					<input type='submit' id='btnNewResp' class='btn btn-danger' name='btnDeleteAll' value='Permanently Delete' />
				</div>
			</form>
			  ";
	showModal("<span class='glyphicon glyphicon-trash'></span>&nbsp; Confirm Delete All Respondents", $body);
}
function respondents() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$DBH = new Account();
	$surveyArray = $DBH->single_survey($accountID, $surveyID);
	$respArray = $DBH->survey_respondents($surveyID);
	$startCt = array_filter($respArray, function($ar) { return (!is_null($ar["last_dt"])); });
	$completeCt = array_filter($respArray, function($ar) { return ($ar["survey_completed"]) == 1; });
	assessmentTabs($surveyID, 5);
	echo "<h4>" . $surveyArray['survey_name'] . ":&nbsp;&nbsp;&nbsp;&nbsp;" . count($respArray) . " Respondents&nbsp;&nbsp;&nbsp;" . count($startCt) . " Responded&nbsp;&nbsp;&nbsp;" . count($completeCt) . " Completed</h4>";
	echo "<a class='btn btn-primary btn-sm' href='?rq=respondents&sid=$surveyID&cst=newrespondent'><span class='glyphicon glyphicon-user'></span> New Respondent</a>";
	echo "&nbsp;&nbsp;";
	echo "<a class='btn btn-primary btn-sm' href='?rq=respondents&sid=$surveyID&cst=uploadrespondents'><span class='glyphicon glyphicon-upload'></span> Upload Respondents</a>";
	echo "&nbsp;&nbsp;";
	echo "<a class='btn btn-primary btn-sm' href='/csv.php?sid=$surveyID&fct=downloaddata&full=0' target='_blank'><span class='glyphicon glyphicon-download'></span> Download Respondents</a>";
	echo "&nbsp;&nbsp;";
	echo "<a class='btn btn-primary btn-sm' href='/csv.php?sid=$surveyID&fct=downloaddata&full=1' target='_blank'><span class='glyphicon glyphicon-download'></span> Download All Data</a>";
	echo "&nbsp;&nbsp;";
	echo "<a class='btn btn-primary btn-sm' href='?rq=respondents&sid=$surveyID&cst=labels'><span class='glyphicon glyphicon-tag'></span> Field Labels</a>";
	echo "&nbsp;&nbsp;";
	echo "<a class='btn btn-warning btn-sm' href='?rq=respondents&sid=$surveyID&cst=resetall'><span class='glyphicon glyphicon-refresh'></span> Reset All</a>";
	echo "&nbsp;&nbsp;";
	echo "<a class='btn btn-danger btn-sm' href='?rq=respondents&sid=$surveyID&cst=deleteall'><span class='glyphicon glyphicon-trash'></span> Delete All</a>";
	//echo "<pre>", print_r($respArray), "</pre>";
    echo "<div class='well' style='margin-top:20px;'>\n";
    echo "<table class='table table-striped' id='tblRespondent'>\n";
	echo "<tr>\n";
	echo "<th>Edit</th>\n";
	echo "<th>Access Code</th>\n";
	echo "<th>Name</th>\n";
	echo "<th>Email</th>\n";
	//echo "<th>Survey URL</th>\n";
	echo "<th>Last Update</th>\n";
	echo "<th></th>\n";
	echo "</tr>\n";
	for($r=0;$r<count($respArray);++$r) {
		$URL[$r] = "https://" . baseURL() . "/oas/?sv=" . hashit($surveyID,10) . "&ac=" . $respArray[$r]['resp_access_code'];
		echo "<tr>\n";
		echo "<td style='white-space:nowrap;'>\n";
		echo "<a href='?rq=respondents&sid=$surveyID&rid=" . $respArray[$r]['resp_id'] . "&cst=editrespondent' title='Edit Respondent' style='font-size:16px; margin-right:8px;'><span class='glyphicon glyphicon-pencil'></span> </a>\n";
		unset($resetDisable);
		echo "<a href='?rq=respondents&sid=$surveyID&rid=" . $respArray[$r]['resp_id'] . "&cst=resetrespondent' title='Reset Survey' style='font-size:16px; margin-right:8px;'><span class='glyphicon glyphicon-refresh'></span> </a>\n";
		echo "<a href='?rq=respondents&sid=$surveyID&rid=" . $respArray[$r]['resp_id'] . "&cst=deleterespondent' title='Delete Respondent' style='font-size:16px; margin-right:8px;'><span class='glyphicon glyphicon-trash'></span> </a>\n";
		echo "<a href='" . $URL[$r] . "' target='_blank' title='Survey URL' style='font-size:16px;'><span class='glyphicon glyphicon-link'></span> </a>\n";
		echo "</td>\n";
		echo "<td>" . $respArray[$r]['resp_access_code'] . "</td>\n";
		echo "<td>" . $respArray[$r]['resp_last'] . ", " . $respArray[$r]['resp_first'] . "</td>\n";
		echo "<td>" . $respArray[$r]['resp_email'] . "</td>\n";
		//echo "<td><a href='" . $URL[$r] . "' target='_blank'>" . $URL[$r] . "</a></td>\n";
		echo "<td>" . $respArray[$r]['last_dt'] . "</td>\n";
		echo "<td align='right'>\n";
		if($respArray[$r]['survey_completed'] == true) {
			echo " <span title='Respondent completed survey.' style='color:green; font-size:18px;' class='glyphicon glyphicon-check'></span>";
		}
		echo "</td>\n";
		echo "</tr>\n";
	}
	echo "</table>\n";
	echo "</div>\n";//end well
}

?>