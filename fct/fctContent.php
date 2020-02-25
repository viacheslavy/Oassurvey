<?php
function csteditwarning() {
	$body = "
<div class='modal-body'>
<p class='blue strong'>This survey is currently active and/or has responses. Making edits to an active survey with responses is strongly discouraged. If edits are absolutely necessary, please ensure changes are minor, such as spelling corrections.</p><p class='strong blue'>Restructuring the survey, adding, deleting, or significantly rewording questions may cause the existing survey data to become skewed and possibly unusable. Please proceed with caution.</p><p class='blue strong'>It is further recommended to deactivate the survey while  performing edits.</p>

</div>
<div class='modal-footer'>
<button type='button' class='btn btn-primary' data-dismiss='modal'>Understood</button>
</div>
			  ";
	showModal("Edit Warning", $body);
}
function cstnewpage() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$questionID = $_GET['qid'];
	$DBH = new Account();
	$singleQuestion = $DBH->single_question($surveyID, $questionID);
	if(empty($singleQuestion)){ $questionID=0; };
	$branchFrom = $singleQuestion['question_desc'];
	if(strlen($branchFrom)==0){
		$branchFrom = "New Branch";
	}
	$pageDesc = $branchFrom;
	$pageExtra = "<p>Of the time you devote to <strong>[SURVEY POSITION]</strong>, indicate the percentage dedicated to these category of activities. (Your responses must total 100%)</p>"; //default text
	if(isset($_POST['btnNewPage'])) {
		$pageDesc = filterText($_POST['txtPageDesc']);
		if(strlen($pageDesc)==0) {
			$pageDesc = $branchFrom;
		}
		$pageExtra = trim($_POST['txtPageExtra']);
		$newPageID = $DBH->insert_new_page($surveyID, $questionID, $pageDesc, $pageExtra);
		header("Location:?rq=editpage&sid=$surveyID&pid=$newPageID");
		exit();
	}
	$body = "
<form method='post'>
<div class='modal-body'>

<div class='form-group'>
<label for='pageDesc'>Page Title:</label>
<input type='textbox' id='pageDesc' name='txtPageDesc' class='form-control' maxlength='255' placeholder='Page Description' value='$pageDesc' />
</div>

<div class='form-group'>
<label for='pageExtra'>Page Description:</label>
<textarea id='pageExtra' class='form-control' name='txtPageExtra' style='height:200px !important;'>$pageExtra</textarea>
</div>

</div>
<div class='modal-footer'>
<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
<input type='submit' class='btn btn-primary' name='btnNewPage' value='Create Page' />
</div>
</form>
			  ";
	showModal("Add New Page <span class='glyphicon glyphicon-arrow-right'></span> Branching From $branchFrom", $body);
}
function csteditpage() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$pageID = $_GET['pid'];
	$DBH = new Account();
	$singlePage = $DBH->single_page($surveyID, $pageID);
	$pageDesc = $singlePage['page_desc'];
	$pageExtra = $singlePage['page_extra'];
	if(empty($singleQuestion)){ $questionID=0; };
	$branchFrom = $singleQuestion['question_desc'];
	if(strlen($branchFrom)==0){
		$branchFrom = $singlePage['page_desc'];
	}
	$pageDesc = $branchFrom;
	if(isset($_POST['btnEditPage'])) {
		$pageDesc = filterText($_POST['txtPageDesc']);
		if(strlen($pageDesc)==0) {
			$pageDesc = $branchFrom;
		}
		$pageExtra = trim($_POST['txtPageExtra']);
		$DBH->edit_page($surveyID, $pageID, $pageDesc, $pageExtra);
		header("Location:?rq=editpage&sid=$surveyID&pid=$pageID");
		exit();
	}
	$body = "
<form method='post'>
<div class='modal-body'>

<div class='form-group'>
<label for='pageDesc'>Page Title:</label>
<input type='textbox' id='pageDesc' name='txtPageDesc' class='form-control' maxlength='255' placeholder='Page Description' value='$pageDesc' />
</div>

<div class='form-group'>
<label for='pageExtra'>Page Description:</label>
<textarea id='pageExtra' class='form-control' name='txtPageExtra' style='height:200px !important;'>$pageExtra</textarea>
</div>

</div>
<div class='modal-footer'>
<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
<input type='submit' class='btn btn-primary' name='btnEditPage' value='Save Changes' />
</div>
</form>
			  ";
	showModal("Edit Page", $body);
}
function cstnewitem() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$pageID = $_GET['pid'];
	$DBH = new Account();
	if(isset($_POST['btnNewItem'])) {
		$questionCode = filterText($_POST['txtQuestionCode']);
		$questionDesc = filterText($_POST['txtQuestionDesc']);
		if(strlen($questionDesc)==0) {
			$questionDesc = "Placeholder";
		}
		$questionExtra = filterText($_POST['txtQuestionExtra']);
		$questionDescAlt = filterText($_POST['txtQuestionDescAlt']);
		$questionExtraAlt = filterText($_POST['txtQuestionExtraAlt']);
		$DBH->insert_new_question($surveyID, $pageID, $questionCode, $questionDesc, $questionExtra, $questionDescAlt, $questionExtraAlt);
		header("Location:?rq=editpage&sid=$surveyID&pid=$pageID");
		exit();
	}
	$body = "
<form method='post'>
<div class='modal-body'>
<div class='form-group'>
<label for='questionCode'>Item Code:</label>
<input type='textbox' id='questionCode' name='txtQuestionCode' class='form-control' maxlength='40' placeholder='Item Code' value='$questionCode' style='width:200px;' />
</div>

<div class='form-group'>
<label for='questionDesc'>Item Name:</label>
<input type='textbox' id='questionDesc' name='txtQuestionDesc' class='form-control' maxlength='1000' placeholder='Item Name' value='$questionDesc' />
</div>

<div class='form-group'>
<label for='questionExtra'>Item Description:</label>
<input type='textbox' id='questionExtra' name='txtQuestionExtra' class='form-control' maxlength='1500' placeholder='Item Description' value='$questionExtra' />
</div>

<div class='form-group'>
<label for='questionDescAlt'>Alternate Item Name (optional):</label>
<input type='textbox' id='questionDescAlt' name='txtQuestionDescAlt' class='form-control' maxlength='1000' placeholder='Alternate Item Name (optional)' value='$questionDescAlt' />
</div>

<div class='form-group'>
<label for='questionExtraAlt'>Alternate Item Description (optional):</label>
<input type='textbox' id='questionExtraAlt' name='txtQuestionExtraAlt' class='form-control' maxlength='1500' placeholder='Alternate Item Description (optional)' value='$questionExtraAlt' />
</div>

</div>
<div class='modal-footer'>
<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
<input type='submit' class='btn btn-primary' name='btnNewItem' value='Add Item' />
</div>
</form>
			  ";
	showModal("Add New Item", $body);
}
function cstedititem() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$request = $_GET['rq'];
	$pageID = $_GET['pid'];
	$questionID = $_GET['qid'];
	
	$DBH = new Account();
	$questionArray = $DBH->single_question($surveyID, $questionID);
	$questionDesc = $questionArray['question_desc'];
	$questionDescAlt = $questionArray['question_desc_alt'];
	$questionExtra = $questionArray['question_extra'];
	$questionExtraAlt = $questionArray['question_extra_alt'];
	$questionCode = $questionArray['question_code'];
	$questionEnabled = $questionArray['question_enabled'];
	if(count($questionArray) == 0) { //ahouls be something there
		echo "Invalid Selection.";
		exit();
	}
	if(isset($_POST['btnEditItem'])) {
		$questionCode = filterText($_POST['txtQuestionCode']);
		$questionDesc = filterText($_POST['txtQuestionDesc']);
		if(strlen($questionDesc)==0) {
			$questionDesc = $questionArray['question_desc'];
		}
		$questionExtra = filterText($_POST['txtQuestionExtra']);
		$questionDescAlt = filterText($_POST['txtQuestionDescAlt']);
		$questionExtraAlt = filterText($_POST['txtQuestionExtraAlt']);
		$questionEnabled = substr($_POST['chkQuestionEnabled'],0,1);
		$DBH->edit_question($surveyID, $questionID, $questionCode, $questionDesc, $questionDescAlt, $questionExtra, $questionExtraAlt, $questionEnabled);
		header("Location:?rq=$request&sid=$surveyID&pid=$pageID");
		exit();
	}
	if($questionEnabled == 1) {
		$qeChecked = " checked='checked'";
	}
	$body = "
<form method='post'>
<div class='modal-body'>

<div class='checkbox'>
<label><input type='checkbox' name='chkQuestionEnabled' value='1'" . $qeChecked . "> <strong>Visible On Survey</strong></label>
</div>

<div class='form-group'>
<label for='questionCode'>Item Code:</label>
<input type='textbox' id='questionCode' name='txtQuestionCode' class='form-control' maxlength='40' placeholder='Item Code' value='$questionCode' style='width:200px;' />
</div>

<div class='form-group'>
<label for='questionDesc'>Item Name:</label>
<input type='textbox' id='questionDesc' name='txtQuestionDesc' class='form-control' maxlength='1000' placeholder='Item Name' value='$questionDesc' />
</div>

<div class='form-group'>
<label for='questionExtra'>Item Description:</label>
<input type='textbox' id='questionExtra' name='txtQuestionExtra' class='form-control' maxlength='1500' placeholder='Item Description' value='$questionExtra' />
</div>

<div class='form-group'>
<label for='questionDescAlt'>Alternate Item Name (optional):</label>
<input type='textbox' id='questionDescAlt' name='txtQuestionDescAlt' class='form-control' maxlength='1000' placeholder='Alternate Item Name (optional)' value='$questionDescAlt' />
</div>

<div class='form-group'>
<label for='questionExtraAlt'>Alternate Item Description (optional):</label>
<input type='textbox' id='questionExtraAlt' name='txtQuestionExtraAlt' class='form-control' maxlength='1500' placeholder='Alternate Item Description (optional)' value='$questionExtraAlt' />
</div>

</div>
<div class='modal-footer'>
<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
<input type='submit' class='btn btn-primary' name='btnEditItem' value='Save Edits' />
</div>
</form>
			  ";
	showModal("<span class='glyphicon glyphicon-pencil'></span> Edit Item", $body);
}
function cstdeleteitem() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$request = $_GET['rq'];
	$pageID = $_GET['pid'];
	$questionID = $_GET['qid'];
	
	$DBH = new Account();
	$questionArray = $DBH->single_question($surveyID, $questionID);
	$questionDesc = $questionArray['question_desc'];
	if(isset($_POST['btnDeleteItem'])) {
		$DBH->delete_question_and_dependents($surveyID, $pageID, $questionID, false);
		header("Location:?rq=editpage&sid=$surveyID&pid=$pageID");
		exit();
	}
	$body = "
<form method='post'>
<div class='modal-body'>
<p><span class='redalert strong'>WARNING: </span> You are about to <strong>permanently delete $questionDesc</strong>. In addition, <strong>all survey content that branches from $questionDesc will be permanently deleted</strong>. Are you sure you wish to continue?</p>

</div>
<div class='modal-footer'>
<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
<input type='submit' class='btn btn-danger' name='btnDeleteItem' value='Permanently Delete' />
</div>
</form>
			  ";
	showModal("<span class='glyphicon glyphicon-trash'></span> Delete $questionDesc", $body);
}
function cstdeletepage() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$request = $_GET['rq'];
	$pageID = $_GET['pid'];

	$DBH = new Account();
	$pageArray = $DBH->single_page($surveyID, $pageID);
	$pageDesc = $pageArray['question_desc'];
	$questionIDParent = $pageArray['question_id_parent'];
	if(isset($_POST['btnDeletePage'])) {
		$DBH->delete_question_and_dependents($surveyID, $pageID, $questionIDParent, true);
		header("Location:?rq=content&sid=$surveyID");
		exit();
	}
	$body = "
<form method='post'>
<div class='modal-body'>
<p><span class='redalert strong'>WARNING: </span> You are about to <strong>permanently delete all survey content that branches from $pageDesc.</strong> Are you sure you wish to continue?</p>

</div>
<div class='modal-footer'>
<button type='button' class='btn btn-secondary' data-dismiss='modal'>Cancel</button>
<input type='submit' class='btn btn-danger' name='btnDeletePage' value='Permanently Delete' />
</div>
</form>
			  ";
	showModal("<span class='glyphicon glyphicon-trash'></span> Delete $pageDesc", $body);
}
function content() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$DBH = new Account();
	$singleSurvey = $DBH->single_survey($accountID, $surveyID);
	if(empty($singleSurvey)){ signout(); };
	$surveyName = $singleSurvey['survey_name'];
	$surveyActive = $singleSurvey['survey_active'];
	$responseCount = $singleSurvey['response_count'];
	if(($surveyActive || $responseCount) && empty($_GET['cst'])) {
		header("Location:?rq=content&sid=$surveyID&cst=editwarning");
		exit();
	}
	$pageArray = $DBH->survey_pages($surveyID);
	$questionArray = $DBH->survey_questions($surveyID);
	assessmentTabs($surveyID, 2);
	echo "<h4>Content Of " . $singleSurvey['survey_name'] . "</h4>";
	echo "<a class='btn btn-primary btn-sm' href='?rq=content&sid=$surveyID&cst=newpage'><span class='glyphicon glyphicon-plus'></span> Add First Level Branch</a>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo "<span class='btn btn-default btn-sm' id='rollup'>Collapse All</span> ";
	echo " <span class='btn btn-default btn-sm' id='rolldown'>Expand All</span> ";
	echo " <a class='btn btn-default btn-sm' href='?rq=export&sid=$surveyID'><span class='glyphicon glyphicon-export'></span> Detailed</a> ";
	//echo "<pre>", print_r($questionArray_1), "</pre>";
    echo "<div class='well' style='margin-top:20px;'>\n";
	echo "<ul class='nav nav-list'>\n";
	//LEVEL ONE #######################################################################################################
	unset($value);
	//$value = $questionArray_1[$q1]['question_id'];
	$value = 0;
	$pageArray_1 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));
	for($p1=0;$p1<count($pageArray_1);++$p1) {
		echo "<li><label class='tree-toggler'><span class='oastree glyphicon glyphicon-chevron-down'></span>" . $pageArray_1[$p1]['page_desc'] . "<div class='oasedit'><a title='Edit' class='btn btn-success btn-xs' href='?rq=editpage&sid=$surveyID&pid=" . $pageArray_1[$p1]['page_id'] . "'><span class='glyphicon glyphicon-pencil'></span> Edit</a></div></label>\n"; //Show Page Name on Branch level
		echo "<ul class='nav nav-list tree'>\n";
		unset($value);
		$value = $pageArray_1[$p1]['page_id'];
		$questionArray_1 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
		for($q1=0;$q1<count($questionArray_1);++$q1) {
			unset($value);
			$value = $questionArray_1[$q1]['question_id']; //get question id of parent question
			$pageArray_2 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));
			// START LEVEL 2
			if(count($pageArray_2) == 0) { // if children are to follow, start new tree level
				echo "<li class='liQ'><label class='oasnotree'><span class='oascode'>" . $questionArray_1[$q1]['question_code'] . "</span>" . $questionArray_1[$q1]['question_desc'] . "<div class='oasedit'><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_1[$q1]['question_enabled'] . "'></span><a title='New Branch From This Item' class='btn btn-primary btn-xs' href='?rq=content&cst=newpage&sid=$surveyID&qid=" . $questionArray_1[$q1]['question_id'] . "'><span class='glyphicon glyphicon-plus'></span> Add</a></div></label></li>\n";
			}
			for($p2=0;$p2<count($pageArray_2);++$p2) {
					echo "<li class='liQ'><label class='tree-toggler'><span class='oastree glyphicon glyphicon-chevron-down'></span><span class='oascode'>" . $questionArray_1[$q1]['question_code'] . "</span>" . $questionArray_1[$q1]['question_desc'] . "<div class='oasedit'><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_1[$q1]['question_enabled'] . "'></span><a title='Edit' class='btn btn-success btn-xs' href='?rq=editpage&sid=$surveyID&pid=" . $pageArray_2[$p2]['page_id'] . "'><span class='glyphicon glyphicon-pencil'></span> Edit</a></div></label>\n";
					echo "<ul class='nav nav-list tree'>\n";
				unset($value);
				$value = $pageArray_2[$p2]['page_id'];
				$questionArray_2 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
				for($q2=0;$q2<count($questionArray_2);++$q2) {
					unset($value);
					$value = $questionArray_2[$q2]['question_id']; //get question id of parent question
					$pageArray_3 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));			
					// START LEVEL 3
					if(count($pageArray_3) == 0) { // if children are to follow, start new tree level
						echo "<li class='liQ'><label class='oasnotree'><span class='oascode'>" . $questionArray_1[$q1]['question_code'] . $questionArray_2[$q2]['question_code'] . "</span>" . $questionArray_2[$q2]['question_desc'] . "<div class='oasedit'><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_2[$q2]['question_enabled'] . "'></span><a title='New Branch From This Item' class='btn btn-primary btn-xs' href='?rq=content&cst=newpage&sid=$surveyID&qid=" . $questionArray_2[$q2]['question_id'] . "'><span class='glyphicon glyphicon-plus'></span> Add</a></div></label></li>\n";
					}
					for($p3=0;$p3<count($pageArray_3);++$p3) {
							echo "<li class='liQ'><label class='tree-toggler'><span class='oastree glyphicon glyphicon-chevron-down'></span><span class='oascode'>" . $questionArray_1[$q1]['question_code'] . $questionArray_2[$q2]['question_code'] . "</span>" . $questionArray_2[$q2]['question_desc'] . "<div class='oasedit'><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_2[$q2]['question_enabled'] . "'></span><a title='Edit' class='btn btn-success btn-xs' href='?rq=editpage&sid=$surveyID&pid=" . $pageArray_3[$p3]['page_id'] . "'><span class='glyphicon glyphicon-pencil'></span> Edit</a></div></label>\n";
							echo "<ul class='nav nav-list tree'>\n";					
						unset($value);
						$value = $pageArray_3[$p3]['page_id'];
						$questionArray_3 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
						for($q3=0;$q3<count($questionArray_3);++$q3) {
							unset($value);
							$value = $questionArray_3[$q3]['question_id']; //get question id of parent question
							$pageArray_4 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));							
							// START LEVEL 4
							if(count($pageArray_4) == 0) { // if children are to follow, start new tree level
								echo "<li class='liQ'><label class='oasnotree'><span class='oascode'>" . $questionArray_1[$q1]['question_code'] . $questionArray_2[$q2]['question_code'] . "." . $questionArray_3[$q3]['question_code'] . "</span>" . $questionArray_3[$q3]['question_desc'] . "<div class='oasedit'><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_3[$q3]['question_enabled'] . "'></span><a title='New Branch From This Item' class='btn btn-primary btn-xs' href='?rq=content&cst=newpage&sid=$surveyID&qid=" . $questionArray_3[$q3]['question_id'] . "'><span class='glyphicon glyphicon-plus'></span> Add</a></div></label></li>\n";
							}
							for($p4=0;$p4<count($pageArray_4);++$p4) {
								echo "<li class='liQ'><label class='tree-toggler'><span class='oastree glyphicon glyphicon-chevron-down'></span><span class='oascode'>" . $questionArray_1[$q1]['question_code'] . $questionArray_2[$q2]['question_code'] . "." . $questionArray_3[$q3]['question_code'] . "</span>" . $questionArray_3[$q3]['question_desc'] . "<div class='oasedit'><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_3[$q3]['question_enabled'] . "'></span><a title='Edit' class='btn btn-success btn-xs' href='?rq=editpage&sid=$surveyID&pid=" . $pageArray_4[$p4]['page_id'] . "'><span class='glyphicon glyphicon-pencil'></span> Edit</a></div></label>\n";
								echo "<ul class='nav nav-list tree'>\n";					
								unset($value);
								$value = $pageArray_4[$p4]['page_id'];
								$questionArray_4 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
								for($q4=0;$q4<count($questionArray_4);++$q4) {
									unset($value);
									$value = $questionArray_4[$q4]['question_id']; //get question id of parent question
									$pageArray_5 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));									
									// START LEVEL 5
									if(count($pageArray_5) == 0) { // if children are to follow, start new tree level
										echo "<li class='liQ'><label class='oasnotree'><span class='oascode'>" . $questionArray_1[$q1]['question_code'] . $questionArray_2[$q2]['question_code'] . "." . $questionArray_3[$q3]['question_code'] . "." . $questionArray_4[$q4]['question_code'] . "</span>" . $questionArray_4[$q4]['question_desc'] . "<div class='oasedit'><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_4[$q4]['question_enabled'] . "'></span><a title='New Branch From This Item' class='btn btn-primary btn-xs' href='?rq=content&cst=newpage&sid=$surveyID&qid=" . $questionArray_4[$q4]['question_id'] . "'><span class='glyphicon glyphicon-plus'></span> Add</a></div></label></li>\n";
									}
									for($p5=0;$p5<count($pageArray_5);++$p5) {
											echo "<li class='liQ'><label class='tree-toggler'><span class='oastree glyphicon glyphicon-chevron-down'></span><span class='oascode'>" . $questionArray_1[$q1]['question_code'] . $questionArray_2[$q2]['question_code'] . "." . $questionArray_3[$q3]['question_code'] . "." . $questionArray_4[$q4]['question_code'] . "</span>" . $questionArray_4[$q4]['question_desc'] . "<div class='oasedit'><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_4[$q4]['question_enabled'] . "'></span><a title='Edit' class='btn btn-success btn-xs' href='?rq=editpage&sid=$surveyID&pid=" . $pageArray_5[$p5]['page_id'] . "'><span class='glyphicon glyphicon-pencil'></span> Edit</a></div></label>\n";
											echo "<ul class='nav nav-list tree'>\n";					
										unset($value);
										$value = $pageArray_5[$p5]['page_id'];
										$questionArray_5 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
										for($q5=0;$q5<count($questionArray_5);++$q5) {
											unset($value);
											$value = $questionArray_5[$q5]['question_id']; //get question id of parent question
											$pageArray_6 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));											
											// START LEVEL 6
											if(count($pageArray_6) == 0) { // if children are to follow, start new tree level
												echo "<li class='liQ'><label class='oasnotree'><span class='oascode'>" . $questionArray_1[$q1]['question_code'] . $questionArray_2[$q2]['question_code'] . "." . $questionArray_3[$q3]['question_code'] . "." . $questionArray_4[$q4]['question_code'] . "." . $questionArray_5[$q5]['question_code'] . "</span>" . $questionArray_5[$q5]['question_desc'] . "<div class='oasedit'><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_5[$q5]['question_enabled'] . "'></span><a title='New Branch From This Item' class='btn btn-primary btn-xs' href='?rq=content&cst=newpage&sid=$surveyID&qid=" . $questionArray_5[$q5]['question_id'] . "'><span class='glyphicon glyphicon-plus'></span> Add</a></div></label></li>\n";
											}
											for($p6=0;$p6<count($pageArray_6);++$p6) {
													echo "<li class='liQ'><label class='tree-toggler'><span class='oastree glyphicon glyphicon-chevron-down'></span><span class='oascode'>" . $questionArray_1[$q1]['question_code'] . $questionArray_2[$q2]['question_code'] . "." . $questionArray_3[$q3]['question_code'] . "." . $questionArray_4[$q4]['question_code'] . "." . $questionArray_5[$q5]['question_code'] . "</span>" . $questionArray_5[$q5]['question_desc'] . "<div class='oasedit'><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_5[$q5]['question_enabled'] . "'></span><a title='Edit' class='btn btn-success btn-xs' href='?rq=editpage&sid=$surveyID&pid=" . $pageArray_6[$p6]['page_id'] . "'><span class='glyphicon glyphicon-pencil'></span> Edit</a></div></label>\n";
													echo "<ul class='nav nav-list tree'>\n";					
												unset($value);
												$value = $pageArray_6[$p6]['page_id'];
												$questionArray_6 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
												for($q6=0;$q6<count($questionArray_6);++$q6) { //last level, only questions, no children pages
													echo "<li class='liQ'><label class='oasnotree'><span class='oascode'>" . $questionArray_1[$q1]['question_code'] . $questionArray_2[$q2]['question_code'] . "." . $questionArray_3[$q3]['question_code'] . "." . $questionArray_4[$q4]['question_code'] . "." . $questionArray_5[$q5]['question_code'] . "." . $questionArray_6[$q6]['question_code'] . "</span>" . $questionArray_6[$q6]['question_desc'] . "<div class='oasedit'><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_6[$q6]['question_enabled'] . "'></span></div></label></li>\n";
												} // end q6 loop
												echo "</ul>\n";
												echo "</li>\n";
											}// end p6 loop											
										} // end q5 loop
										echo "</ul>\n";
										echo "</li>\n";
									}// end p5 loop									
								} // end q4 loop
								echo "</ul>\n";
								echo "</li>\n";
							}// end p4 loop							
						} // end q3 loop
						echo "</ul>\n";
						echo "</li>\n";
					} // end p3 loop
				}// end q2 loop
				echo "</ul>\n";
				echo "</li>\n";
			} // end p2 loop
		}// end q1 loop
		echo "</ul>\n";
		echo "</li>\n";
	}// end p1 loop
	echo "</ul>\n";
	echo "</div>\n";//end well
}
function editpage() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$pageID = $_GET['pid'];
	$DBH = new Account();
	$singlePage = $DBH->single_page($surveyID, $pageID);
	if(empty($singlePage)){ signout(); };
	if(!empty($_POST['btnQuestionUp'])) {
		$reseqQID =  $_POST['btnQuestionUp'];
		$DBH->resequence_question($surveyID, $pageID, $reseqQID, "up");
		header("Location:?rq=editpage&sid=$surveyID&pid=$pageID");
		exit();
	}
	if(!empty($_POST['btnQuestionDown'])) {
		$reseqQID =  $_POST['btnQuestionDown'];
		$DBH->resequence_question($surveyID, $pageID, $reseqQID, "down");
		header("Location:?rq=editpage&sid=$surveyID&pid=$pageID");
		exit();
	}
	$questionArray = $DBH->survey_questions_on_page($surveyID, $pageID);
	//echo "<pre>", print_r($singlePage), "</pre>";
	assessmentTabs($surveyID, 2);
	echo "<h4>Content Branching From: &nbsp;" . $singlePage['question_desc'] . " (" . $singlePage['question_code'] . ")</h4>\n";
	echo "<a class='btn btn-primary btn-sm' href='?rq=editpage&sid=$surveyID&pid=$pageID&cst=newitem'><span class='glyphicon glyphicon-plus'></span> Add Item</a> ";
	echo "&nbsp;&nbsp; <a class='btn btn-primary btn-sm' href='?rq=editpage&sid=$surveyID&pid=$pageID&cst=editpage'><span class='glyphicon glyphicon-pencil'></span> Edit Page Text</a> ";
	echo "&nbsp;&nbsp; <a class='btn btn-primary btn-sm' href='?rq=editpage&sid=$surveyID&pid=$pageID&cst=deletepage'><span class='glyphicon glyphicon-trash'></span> Delete Page</a> ";
	echo "&nbsp;&nbsp; <a class='btn btn-primary btn-sm' href='?rq=content&sid=$surveyID'><span class='glyphicon glyphicon-arrow-left'></span> Go Back</a>\n";
	echo "<form method='post'>\n";
	echo "<div class='well' style='margin-top:20px;'>\n";
	for($q=0;$q<count($questionArray);++$q) {
		echo "<div class='row questionBox'>\n";


		echo "<div class='col-lg-2'>\n";
		
		echo "<a title='Edit' class='btn btn-sm btn-default editButton' href='?rq=editpage&sid=$surveyID&pid=$pageID&qid=" . $questionArray[$q]['question_id'] . "&cst=edititem'><span class='glyphicon glyphicon-pencil'></span></a>";
		echo "<a title='Delete' class='btn btn-sm btn-default editButton' href='?rq=editpage&sid=$surveyID&pid=$pageID&qid=" . $questionArray[$q]['question_id'] . "&cst=deleteitem'><span class='glyphicon glyphicon-trash'></span></a>";
		echo "&nbsp;&nbsp;";
		echo "<button title='Move Up' type='submit' class='btn btn-sm btn-default editButton' name='btnQuestionUp' value='" . $questionArray[$q]['question_id'] . "'><span class='glyphicon glyphicon-arrow-up'></span></button>";
		echo "<button title='Move Down' type='submit' class='btn btn-sm btn-default editButton' name='btnQuestionDown' value='" . $questionArray[$q]['question_id'] . "'><span class='glyphicon glyphicon-arrow-down'></span></button>";
		echo "<span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray[$q]['question_enabled'] . "'></span>\n";
		
		echo "</div>\n";
		
		echo "<div class='col-lg-1'>\n";
		echo "<div>" . $questionArray[$q]['question_code'] . "</div>\n";
		echo "</div>\n";
		
		echo "<div class='col-lg-3'>\n";
		echo "<div>" . $questionArray[$q]['question_desc'] . "</div>\n";
		echo "</div>\n";
		
		echo "<div class='col-lg-6'>\n";
		echo "<div>" . $questionArray[$q]['question_extra'] . "</div>\n";
		echo "</div>\n";
		
		echo "</div>\n"; //end row
	}
	echo "</div>\n";//end well
	echo "</form>\n";
}

function export() {
	global $accountID;
	$surveyID = $_GET['sid'];
	$DBH = new Account();
	$singleSurvey = $DBH->single_survey($accountID, $surveyID);
	if(empty($singleSurvey)){ signout(); };
	$pageArray = $DBH->survey_pages($surveyID);
	$questionArray = $DBH->survey_questions($surveyID);
	assessmentTabs($surveyID, 2);
	echo "<h4>Detailed Content Of " . $singleSurvey['survey_name'] . "</h4>";
	echo "<a class='btn btn-primary btn-sm' href='?rq=content&sid=$surveyID'><span class='glyphicon glyphicon-arrow-left'></span> Go Back</a>\n";
	echo "<br /><br />";
	echo "<div class='well'>\n";
    echo "<table class='table table-striped' id='tblDetails'>\n";
	echo "<tr>\n";
	echo "<th width='75'></td>\n";
	echo "<th>#</td>\n";
	echo "<th>QID</td>\n";
	echo "<th>PID</td>\n";
	echo "<th>Code</td>\n";
	echo "<th>Item</td>\n";
	echo "<th>Definition</td>\n";
	echo "<th>Alt Item</td>\n";
	echo "<th>Alt Definition</td>\n";
	echo "<th>UTBMS</td>\n";
	echo "</tr>\n";
	$questionSeq = 1;
	//LEVEL ONE #######################################################################################################
	unset($value);
	$value = 0;
	$pageArray_1 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));
	for($p1=0;$p1<count($pageArray_1);++$p1) {
		unset($value);
		$value = $pageArray_1[$p1]['page_id'];
		$questionArray_1 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
		for($q1=0;$q1<count($questionArray_1);++$q1) {
			unset($value);
			$value = $questionArray_1[$q1]['question_id']; //get question id of parent question
			$pageArray_2 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));
			// START LEVEL 2
				echo "<tr>\n";
				
				echo "<td><a href='?rq=export&sid=$surveyID&qid=" . $questionArray_1[$q1]['question_id'] . "&cst=edititem'><span class='glyphicon glyphicon-pencil'></span> </a><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_1[$q1]['question_enabled'] . "'></span></td>\n";
				echo "<td>" . $questionSeq++ . "</td>\n";
				echo "<td>" . $questionArray_1[$q1]['question_id'] . "</td>\n";
				echo "<td>" . $pageArray_1[$p1]['page_id'] . "</td>\n";
				echo "<td>" . $questionArray_1[$q1]['question_code'] . "</td>\n";
				echo "<td>" . $questionArray_1[$q1]['question_desc'] . "</td>\n";
				echo "<td>" . $questionArray_1[$q1]['question_extra'] . "</td>\n";
				echo "<td>" . $questionArray_1[$q1]['question_desc_alt'] . "</td>\n";
				echo "<td>" . $questionArray_1[$q1]['question_extra_alt'] . "</td>\n";
				echo "<td>" . $questionArray_1[$q1]['question_UTBMS'] . "</td>\n";
				echo "</tr>\n";
			for($p2=0;$p2<count($pageArray_2);++$p2) {
				unset($value);
				$value = $pageArray_2[$p2]['page_id'];
				$questionArray_2 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
				for($q2=0;$q2<count($questionArray_2);++$q2) {
					unset($value);
					$value = $questionArray_2[$q2]['question_id']; //get question id of parent question
					$pageArray_3 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));			
					// START LEVEL 3
					echo "<tr><td colspan='9' style='background-color:#666;'></tr>\n";
					echo "<tr>\n";
					
					echo "<td><a href='?rq=export&sid=$surveyID&qid=" . $questionArray_2[$q2]['question_id'] . "&cst=edititem'><span class='glyphicon glyphicon-pencil'></span></a><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_2[$q2]['question_enabled'] . "'></span></td>\n";
					echo "<td>" . $questionSeq++ . "</td>\n";
					echo "<td>" . $questionArray_2[$q2]['question_id'] . "</td>\n";
					echo "<td>" . $pageArray_2[$p2]['page_id'] . "</td>\n";
					echo "<td>" . $questionArray_2[$q2]['question_code'] . "</td>\n";
					echo "<td>" . $questionArray_2[$q2]['question_desc'] . "</td>\n";
					echo "<td>" . $questionArray_2[$q2]['question_extra'] . "</td>\n";
					echo "<td>" . $questionArray_2[$q2]['question_desc_alt'] . "</td>\n";
					echo "<td>" . $questionArray_2[$q2]['question_extra_alt'] . "</td>\n";
					echo "<td>" . $questionArray_2[$q2]['question_UTBMS'] . "</td>\n";
					echo "</tr>\n";
					for($p3=0;$p3<count($pageArray_3);++$p3) {				
						unset($value);
						$value = $pageArray_3[$p3]['page_id'];
						$questionArray_3 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
						for($q3=0;$q3<count($questionArray_3);++$q3) {
							unset($value);
							$value = $questionArray_3[$q3]['question_id']; //get question id of parent question
							$pageArray_4 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));							
							// START LEVEL 4
							echo "<tr>\n";
							
							echo "<td><a href='?rq=export&sid=$surveyID&qid=" . $questionArray_3[$q3]['question_id'] . "&cst=edititem'><span class='glyphicon glyphicon-pencil'></span></a><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_3[$q3]['question_enabled'] . "'></span></td>\n";
							echo "<td>" . $questionSeq++ . "</td>\n";
							echo "<td>" . $questionArray_3[$q3]['question_id'] . "</td>\n";
							echo "<td>" . $pageArray_3[$p3]['page_id'] . "</td>\n";
							echo "<td>" . $questionArray_2[$q2]['question_code'] . "." . $questionArray_3[$q3]['question_code'] . "</td>\n";
							echo "<td>" . $questionArray_3[$q3]['question_desc'] . "</td>\n";
							echo "<td>" . $questionArray_3[$q3]['question_extra'] . "</td>\n";
							echo "<td>" . $questionArray_3[$q3]['question_desc_alt'] . "</td>\n";
							echo "<td>" . $questionArray_3[$q3]['question_extra_alt'] . "</td>\n";
							echo "<td>" . $questionArray_3[$q3]['question_UTBMS'] . "</td>\n";
							echo "</tr>\n";
							for($p4=0;$p4<count($pageArray_4);++$p4) {				
								unset($value);
								$value = $pageArray_4[$p4]['page_id'];
								$questionArray_4 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
								for($q4=0;$q4<count($questionArray_4);++$q4) {
									unset($value);
									$value = $questionArray_4[$q4]['question_id']; //get question id of parent question
									$pageArray_5 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));									
									// START LEVEL 5
									echo "<tr>\n";
									
									echo "<td><a href='?rq=export&sid=$surveyID&qid=" . $questionArray_4[$q4]['question_id'] . "&cst=edititem'><span class='glyphicon glyphicon-pencil'></span></a><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_4[$q4]['question_enabled'] . "'></span></td>\n";
									echo "<td>" . $questionSeq++ . "</td>\n";
									echo "<td>" . $questionArray_4[$q4]['question_id'] . "</td>\n";
									echo "<td>" . $pageArray_4[$p4]['page_id'] . "</td>\n";
									echo "<td>" . $questionArray_2[$q2]['question_code'] . "." . $questionArray_3[$q3]['question_code'] . "." . $questionArray_4[$q4]['question_code'] . "</td>\n";
									echo "<td>" . $questionArray_4[$q4]['question_desc'] . "</td>\n";
									echo "<td>" . $questionArray_4[$q4]['question_extra'] . "</td>\n";
									echo "<td>" . $questionArray_4[$q4]['question_desc_alt'] . "</td>\n";
									echo "<td>" . $questionArray_4[$q4]['question_extra_alt'] . "</td>\n";
									echo "<td>" . $questionArray_4[$q4]['question_UTBMS'] . "</td>\n";
									echo "</tr>\n";
									for($p5=0;$p5<count($pageArray_5);++$p5) {					
										unset($value);
										$value = $pageArray_5[$p5]['page_id'];
										$questionArray_5 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
										for($q5=0;$q5<count($questionArray_5);++$q5) {
											unset($value);
											$value = $questionArray_5[$q5]['question_id']; //get question id of parent question
											$pageArray_6 = array_values(array_filter($pageArray, function($ar) use ($value) { return ($ar["question_id_parent"] == $value); }));											
											// START LEVEL 6
											echo "<tr>\n";
											echo "<td><a href='?rq=export&sid=$surveyID&qid=" . $questionArray_5[$q5]['question_id'] . "&cst=edititem'><span class='glyphicon glyphicon-pencil'></span></a><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_5[$q5]['question_enabled'] . "'></span></td>\n";
											echo "<td>" . $questionSeq++ . "</td>\n";
											echo "<td>" . $questionArray_5[$q5]['question_id'] . "</td>\n";
											echo "<td>" . $pageArray_5[$p5]['page_id'] . "</td>\n";
											echo "<td>" . $questionArray_2[$q2]['question_code'] . "." . $questionArray_3[$q3]['question_code'] . "." . $questionArray_4[$q4]['question_code'] . "." . $questionArray_5[$q5]['question_code'] . "</td>\n";
											echo "<td>" . $questionArray_5[$q5]['question_desc'] . "</td>\n";
											echo "<td>" . $questionArray_5[$q5]['question_extra'] . "</td>\n";
											echo "<td>" . $questionArray_5[$q5]['question_desc_alt'] . "</td>\n";
											echo "<td>" . $questionArray_5[$q5]['question_extra_alt'] . "</td>\n";
											echo "<td>" . $questionArray_5[$q5]['question_UTBMS'] . "</td>\n";
											echo "</tr>\n";
											for($p6=0;$p6<count($pageArray_6);++$p6) {				
												unset($value);
												$value = $pageArray_6[$p6]['page_id'];
												$questionArray_6 = array_values(array_filter($questionArray, function($ar) use ($value) { return ($ar["page_id"] == $value); }));
												for($q6=0;$q6<count($questionArray_6);++$q6) { //last level, only questions, no children pages
													echo "<tr>\n";
													
													echo "<td><a href='?rq=export&sid=$surveyID&qid=" . $questionArray_6[$q6]['question_id'] . "&cst=edititem'><span class='glyphicon glyphicon-pencil'></span></a><span title='This item will be hidden on the survey. Subsequently, all items that branch from this will be hidden as well.' class='glyphicon glyphicon-ban-circle visible_" . $questionArray_6[$q6]['question_enabled'] . "'></span></td>\n";
													echo "<td>" . $questionSeq++ . "</td>\n";
													echo "<td>" . $questionArray_6[$q6]['question_id'] . "</td>\n";
													echo "<td>" . $pageArray_6[$p6]['page_id'] . "</td>\n";
													echo "<td>" . $questionArray_2[$q2]['question_code'] . "." . $questionArray_3[$q3]['question_code'] . "." . $questionArray_4[$q4]['question_code'] . "." . $questionArray_5[$q5]['question_code'] . "." . $questionArray_6[$q6]['question_code'] . "</td>\n";
													echo "<td>" . $questionArray_6[$q6]['question_desc'] . "</td>\n";
													echo "<td>" . $questionArray_6[$q6]['question_extra'] . "</td>\n";
													echo "<td>" . $questionArray_6[$q6]['question_desc_alt'] . "</td>\n";
													echo "<td>" . $questionArray_6[$q6]['question_extra_alt'] . "</td>\n";
													echo "<td>" . $questionArray_6[$q6]['question_UTBMS'] . "</td>\n";
													echo "</tr>\n";
												} // end q6 loop
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
	echo "</table>\n";
	echo "</div>";
}
?>