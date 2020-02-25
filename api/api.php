<?php

include_once("../init.php");
verifySession(60 * 60);
$accountID = getAccountID();
global $accountID;
$DBH = new Account();

$action = $_POST['action'];

if(!isset($accountID)) {
	$arr = array();
	$arr = array(
		'success' => false,
		'data' => '',
		'error' => '[401] Unauthorized'
	);

	$jsn = json_encode($arr);
	print($jsn);
	die();
}

if($action == "calcs") {
	$arr = array();

	$surveyID = $_POST['sid'];
	$pageID = $_POST['pid'];
	$questionID = $_POST['qid'];

	if(!isset($surveyID) || !isset($pageID) || !isset($questionID)) {
		$arr = array();
		$arr = array(
			'success' => false,
			'data' => '',
			'error' => '[401] Missing variable'
		);

		$jsn = json_encode($arr);
		print($jsn);
		die();
	}
	
	// GROUP BY clauses
	if(isset($_POST['group']) && ($_POST['group'] != 'undefined')) {
		$group = [''.$_POST['group'].''];
	} else {
		$group = [];
	}

	//WHERE clauses
	if(isset($_POST['filters']) && ($_POST['filters'] != 'undefined')) {
		$filters = [''.$_POST['filters'].''];
	} else {
		$filters = [];
	}

	//hit calcs
	//TODO: return error messages
	$result = $DBH->calcs($surveyID, $pageID, $questionID, $group, $filters);

	$arr = array(
		'success' => true,
		'data' => $result,
		'error' => ''
	);

	$jsn = json_encode($arr);
	print($jsn);

} else if($action == 'survey_questions_on_page') {
	$arr = array();

	$surveyID = $_POST['sid'];
	$pageID = $_POST['pid'];

	if(!isset($surveyID) || !isset($pageID)) {
		$arr = array();
		$arr = array(
			'success' => false,
			'data' => '',
			'error' => '[401] Missing variable'
		);

		$jsn = json_encode($arr);
		print($jsn);
		die();
	}

	$result = $DBH->survey_questions_on_page($surveyID, $pageID);

	$arr = array(
		'success' => true,
		'data' => $result,
		'error' => ''
	);

	$jsn = json_encode($arr);
	print($jsn);

} else {
	$arr = array(
		'success' => false,
		'data' => '',
		'error' => '[403] Access denied'
	);

	$jsn = json_encode($arr);
	print($jsn);
}

?>