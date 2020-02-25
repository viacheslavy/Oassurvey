<?php

namespace App\Classes;

use PDO;

class Account extends Database {
	
	public function new_survey($accountID, $surveyName) {
		$this->Connect();
		$sql = "INSERT INTO tblSurvey (account_id, survey_name) VALUES (:account_id, :survey_name);";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':account_id', $accountID, PDO::PARAM_INT);
		$STH->bindValue(':survey_name', $surveyName, PDO::PARAM_STR);
		$STH->execute();
		return $this->db->lastInsertId('survey_id');
		$this->db = null;
	}
	public function seed_settings_row($surveyID) {
		$this->Connect();
		$sql = "INSERT INTO tblSettings(survey_id) VALUES(:survey_id);";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$this->db = null;
	}
	public function copy_survey($surveyIDNew, $surveyIDCopy) {
		$this->Connect();
		//COPY PAGES
		$sql = "INSERT INTO tblPage(question_id_parent, page_seq, page_type, page_desc, page_extra, page_id_original, survey_id) SELECT question_id_parent, page_seq, page_type, page_desc, page_extra, page_id, :survey_id_new FROM tblPage WHERE survey_id=:survey_id_copy;";
		//COPY QUESTIONS
		$sql .= "INSERT INTO tblQuestion(page_id, question_desc, question_desc_alt, question_extra, question_extra_alt, question_code, question_UTBMS, question_seq, question_enabled, question_id_original ,survey_id) SELECT page_id, question_desc, question_desc_alt, question_extra, question_extra_alt, question_code, question_UTBMS, question_seq, question_enabled, question_id, :survey_id_new FROM tblQuestion WHERE survey_id=:survey_id_copy;";
		
		$sql .= "INSERT INTO tblSettings(show_splash_page, splash_page, begin_page, end_page, show_summary, footer, contact_email, contact_phone, weekly_hours_text, annual_legal_hours_text, logo_splash, logo_survey, show_progress_bar, survey_id) SELECT show_splash_page, splash_page, begin_page, end_page, show_summary, footer, contact_email, contact_phone, weekly_hours_text, annual_legal_hours_text, logo_splash, logo_survey, show_progress_bar, :survey_id_new FROM tblSettings WHERE survey_id=:survey_id_copy;";
		
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id_new', $surveyIDNew, PDO::PARAM_INT);
		$STH->bindValue(':survey_id_copy', $surveyIDCopy, PDO::PARAM_INT);
		$STH->execute();
		//UPDATE PARENT IDS TO MAP QUESTIONS CORRECTLY
		$sql = "UPDATE tblPage pg INNER JOIN tblQuestion qu ON pg.question_id_parent=qu.question_id_original SET pg.question_id_parent=qu.question_id WHERE pg.survey_id=:survey_id_new AND qu.survey_id=:survey_id_new;";
		$sql .= "UPDATE tblQuestion qu INNER JOIN tblPage pg ON qu.page_id=pg.page_id_original SET qu.page_id=pg.page_id WHERE qu.survey_id=:survey_id_new AND pg.survey_id=:survey_id_new;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id_new', $surveyIDNew, PDO::PARAM_INT);
		$STH->execute();
		$this->db = null;
	}
	public function delete_survey($surveyID) {
		$this->Connect();
		$sql .= "DELETE FROM tblQuestion WHERE survey_id=:survey_id;";
		$sql .= "DELETE FROM tblPage WHERE survey_id=:survey_id;";
		$sql .= "DELETE FROM tblRespondent WHERE survey_id=:survey_id;";
		$sql .= "DELETE FROM tblSettings WHERE survey_id=:survey_id;";
		$sql .= "DELETE FROM tblSettings WHERE survey_id=:survey_id;";
		$sql .= "DELETE an FROM tblAnswer an INNER JOIN tblRespondent resp ON an.resp_id = resp.resp_id WHERE resp.survey_id=:survey_id;";
		$sql .= "DELETE FROM tblSurvey WHERE survey_id=:survey_id;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$this->db = null;
	}
	public function survey_array($accountID) {
		$this->Connect();
		$sql = "SELECT survey_id, survey_name, survey_created_dt FROM tblSurvey WHERE account_id=:account_id ORDER BY survey_created_dt DESC;";
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':account_id', $accountID, PDO::PARAM_INT);
		$STH->execute();
		$result = $STH->fetchAll();
		$this->db = null;
		return $result;
	}
	public function account_array($accountID) {
		$this->Connect();
		$sql = "SELECT account_first_name, account_last_name, account_email_address, account_usn FROM tblAccount WHERE account_id=:account_id;";
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':account_id', $accountID, PDO::PARAM_INT);
		$STH->execute();
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		$this->db = null;
		return $row;
	}
	public function user_exists($accountID, $accountUsn) {
		$this->Connect();
		$sql = "SELECT account_id FROM tblAccount WHERE account_usn = :account_usn AND account_id != :account_id;";
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':account_id', $accountID, PDO::PARAM_INT);
		$STH->bindValue(':account_usn', $accountUsn, PDO::PARAM_STR);
		$STH->execute();
		$row = $STH->fetchColumn();
		$this->db = null;
		return $row;
	}
	public function single_survey($accountID, $surveyID) {
		$this->Connect();
		$sql = 'SELECT survey_id, survey_name, survey_active, survey_created_dt, (SELECT COUNT(*) FROM tblRespondent WHERE account_id=:account_id AND survey_id=:survey_id AND last_dt IS NOT NULL) AS response_count,  (SELECT COUNT(*) FROM tblRespondent WHERE account_id=:account_id AND survey_id=:survey_id) AS respondent_count,  (SELECT COUNT(*) FROM tblRespondent WHERE account_id=:account_id AND survey_id=:survey_id AND survey_completed=1) AS complete_count FROM tblSurvey WHERE account_id=:account_id AND survey_id=:survey_id;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':account_id', $accountID, PDO::PARAM_INT);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		$this->db = null;
		return $row;
	}
	public function activate_deactivate($accountID, $surveyID) {
		$this->Connect();
		$sql = 'UPDATE tblSurvey SET survey_active = (CASE survey_active WHEN 1 THEN 0 ELSE 1 END) WHERE account_id=:account_id AND survey_id=:survey_id;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':account_id', $accountID, PDO::PARAM_INT);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$this->db = null;
	}
	public function edit_survey($accountID, $surveyID, $surveyName) {
		$this->Connect();
		$sql = 'UPDATE tblSurvey SET survey_name=:survey_name WHERE account_id=:account_id AND survey_id=:survey_id;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':account_id', $accountID, PDO::PARAM_INT);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':survey_name', $surveyName, PDO::PARAM_STR);
		$STH->execute();
		$this->db = null;
	}
	public function settings_row($surveyID) {
		$this->Connect();
		$sql = 'SELECT * FROM tblSettings WHERE survey_id=:survey_id;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		$this->db = null;
		return $row;
	}
	public function single_respondent($surveyID, $respID) {
		$this->Connect();
		$sql = 'SELECT resp_id, resp_access_code, resp_email, resp_first, resp_last, resp_alt, cust_1, cust_2, cust_3, cust_4, cust_5, cust_6 FROM tblRespondent WHERE survey_id=:survey_id AND resp_id=:resp_id;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':resp_id', $respID, PDO::PARAM_INT);
		$STH->execute();
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		$this->db = null;
		return $row;
	}
	public function single_page($surveyID, $pageID) {
		$this->Connect();
		$sql = 'SELECT pg.page_id, pg.page_type, pg.page_desc, pg.page_extra, pg.question_id_parent, qu.question_desc, qu.question_code  FROM tblPage pg LEFT JOIN tblQuestion qu ON pg.question_id_parent=qu.question_id WHERE pg.survey_id=:survey_id AND pg.page_id=:page_id;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':page_id', $pageID, PDO::PARAM_INT);
		$STH->execute();
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		$this->db = null;
		return $row;
	}
	public function single_question($surveyID, $questionID) {
		$this->Connect();
		$sql = 'SELECT question_desc, question_desc_alt, question_extra, question_extra_alt, question_code, question_enabled FROM tblQuestion WHERE survey_id=:survey_id AND question_id=:question_id;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':question_id', $questionID, PDO::PARAM_INT);
		$STH->execute();
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		$this->db = null;
		return $row;
	}
	public function survey_pages($surveyID) {
		$this->Connect();
		$sql = "SELECT page_id, question_id_parent, page_seq, page_type, page_desc FROM tblPage WHERE survey_id=:survey_id ORDER BY page_seq ASC;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$result = $STH->fetchAll();
		$this->db = null;
		return $result;
	}
	public function survey_questions($surveyID) {
		$this->Connect();
		$sql = "SELECT * FROM tblQuestion WHERE survey_id=:survey_id ORDER BY question_seq ASC;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$result = $STH->fetchAll();
		$this->db = null;
		return $result;
	}
	public function survey_questions_on_page($surveyID, $pageID) {
		$this->Connect();
		$sql = "SELECT survey_id, question_id, question_desc, question_extra, question_code, question_seq, question_enabled, question_desc_alt, question_extra_alt FROM tblQuestion WHERE survey_id=:survey_id AND page_id=:page_id ORDER BY question_seq ASC;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':page_id', $pageID, PDO::PARAM_INT);
		$STH->execute();
		$result = $STH->fetchAll();
		$this->db = null;
		return $result;
	}
	public function insert_new_page($surveyID, $questionID, $pageDesc, $pageExtra) {
		$this->Connect();
		$sql = 'INSERT INTO tblPage (page_seq, survey_id, question_id_parent, page_desc, page_extra) SELECT IFNULL(MAX(page_seq), 0) + 1, :survey_id, :question_id, :page_desc, :page_extra FROM tblPage WHERE survey_id = :survey_id AND question_id_parent = :question_id;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':question_id', $questionID, PDO::PARAM_INT);
		$STH->bindValue(':page_desc', $pageDesc, PDO::PARAM_STR);
		$STH->bindValue(':page_extra', $pageExtra, PDO::PARAM_STR);
		$STH->execute();
		return $this->db->lastInsertId('page_id');
		$this->db = null;
	}
	public function edit_page($surveyID, $pageID, $pageDesc, $pageExtra) {
		$this->Connect();
		$sql = 'UPDATE tblPage SET page_desc=:page_desc, page_extra=:page_extra WHERE survey_id=:survey_id AND page_id=:page_id;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':page_id', $pageID, PDO::PARAM_INT);
		$STH->bindValue(':page_desc', $pageDesc, PDO::PARAM_STR);
		$STH->bindValue(':page_extra', $pageExtra, PDO::PARAM_STR);
		$STH->execute();
		$this->db = null;
	}
	public function insert_new_question($surveyID, $pageID, $questionCode, $questionDesc, $questionExtra, $questionDescAlt, $questionExtraAlt) {
		$this->Connect();
		$sql = 'INSERT INTO tblQuestion (question_seq, survey_id, page_id, question_code, question_desc, question_desc_alt, question_extra, question_extra_alt) SELECT IFNULL(MAX(question_seq), 0) + 1, :survey_id, :page_id, :question_code, :question_desc, :question_desc_alt, :question_extra, :question_extra_alt FROM tblQuestion WHERE survey_id = :survey_id AND page_id = :page_id;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':page_id', $pageID, PDO::PARAM_INT);
		$STH->bindValue(':question_code', $questionCode, PDO::PARAM_STR);
		$STH->bindValue(':question_desc', $questionDesc, PDO::PARAM_STR);
		$STH->bindValue(':question_desc_alt', $questionDescAlt, PDO::PARAM_STR);
		$STH->bindValue(':question_extra', $questionExtra, PDO::PARAM_STR);
		$STH->bindValue(':question_extra_alt', $questionExtraAlt, PDO::PARAM_STR);
		$STH->execute();
		$this->db = null;
	}
	public function insert_new_respondent($surveyID, $respAccessCode, $respFirst, $respLast, $respEmail, $respAlt, $cust1, $cust2, $cust3, $cust4, $cust5, $cust6) {
		$this->Connect();
		$sql = 'SELECT resp_id FROM tblRespondent WHERE survey_id=:survey_id AND resp_access_code=:resp_access_code;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':resp_access_code', $respAccessCode, PDO::PARAM_STR);
		$STH->execute();
		$column = $STH->fetchColumn();
		if(!empty($column)) { //test if access code exists, if so return false and cease function
			$this->db = null;
			return false;
		} else {
			$sql = 'INSERT INTO tblRespondent (survey_id, resp_access_code, resp_first, resp_last, resp_email, resp_alt, cust_1, cust_2, cust_3, cust_4, cust_5, cust_6) VALUES (:survey_id, :resp_access_code, :resp_first, :resp_last, :resp_email, :resp_alt, :cust_1, :cust_2, :cust_3, :cust_4, :cust_5, :cust_6) ;';
			$STH = $this->db->prepare($sql);
			$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
			$STH->bindValue(':resp_access_code', $respAccessCode, PDO::PARAM_STR);
			$STH->bindValue(':resp_first', $respFirst, PDO::PARAM_STR);
			$STH->bindValue(':resp_last', $respLast, PDO::PARAM_STR);
			$STH->bindValue(':resp_email', $respEmail, PDO::PARAM_STR);
			$STH->bindValue(':resp_alt', $respAlt, PDO::PARAM_INT);
			$STH->bindValue(':cust_1', $cust1, PDO::PARAM_STR);
			$STH->bindValue(':cust_2', $cust2, PDO::PARAM_STR);
			$STH->bindValue(':cust_3', $cust3, PDO::PARAM_STR);
			$STH->bindValue(':cust_4', $cust4, PDO::PARAM_STR);
			$STH->bindValue(':cust_5', $cust5, PDO::PARAM_STR);
			$STH->bindValue(':cust_6', $cust6, PDO::PARAM_STR);
			$STH->execute();
			return $this->db->lastInsertId('resp_id');
			$this->db = null;
		}
	}
	public function edit_respondent($surveyID, $respID, $respAccessCode, $respFirst, $respLast, $respEmail, $respAlt, $cust1, $cust2, $cust3, $cust4, $cust5, $cust6) {
		$this->Connect();
		$sql = 'SELECT resp_id FROM tblRespondent WHERE survey_id=:survey_id AND resp_access_code=:resp_access_code AND resp_id !=:resp_id;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':resp_id', $respID, PDO::PARAM_INT);
		$STH->bindValue(':resp_access_code', $respAccessCode, PDO::PARAM_STR);
		$STH->execute();
		$column = $STH->fetchColumn();
		if(!empty($column)) { //test if access code maps to respondent id, if does not map then return false and cease function
			$this->db = null;
			return false;
		} else {
			$sql = 'UPDATE tblRespondent SET resp_access_code=:resp_access_code, resp_first=:resp_first, resp_last=:resp_last, resp_email=:resp_email, resp_alt=:resp_alt, cust_1=:cust_1, cust_2=:cust_2, cust_3=:cust_3, cust_4=:cust_4, cust_5=:cust_5, cust_6=:cust_6 WHERE survey_id=:survey_id AND resp_id=:resp_id;';
			$STH = $this->db->prepare($sql);
			$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
			$STH->bindValue(':resp_id', $respID, PDO::PARAM_INT);
			$STH->bindValue(':resp_access_code', $respAccessCode, PDO::PARAM_STR);
			$STH->bindValue(':resp_first', $respFirst, PDO::PARAM_STR);
			$STH->bindValue(':resp_last', $respLast, PDO::PARAM_STR);
			$STH->bindValue(':resp_email', $respEmail, PDO::PARAM_STR);
			$STH->bindValue(':resp_alt', $respAlt, PDO::PARAM_INT);
			$STH->bindValue(':cust_1', $cust1, PDO::PARAM_STR);
			$STH->bindValue(':cust_2', $cust2, PDO::PARAM_STR);
			$STH->bindValue(':cust_3', $cust3, PDO::PARAM_STR);
			$STH->bindValue(':cust_4', $cust4, PDO::PARAM_STR);
			$STH->bindValue(':cust_5', $cust5, PDO::PARAM_STR);
			$STH->bindValue(':cust_6', $cust6, PDO::PARAM_STR);
			$STH->execute();
			$this->db = null;
			return true;
		}
	}
	public function delete_respondent_survey($surveyID, $respID) {
		$this->Connect();
		$sql = "DELETE FROM tblAnswer WHERE resp_id=:resp_id;";
		$sql .= "UPDATE tblRespondent SET start_dt=NULL, last_dt=NULL, last_page_id=NULL, survey_completed=0 WHERE survey_id=:survey_id AND resp_id=:resp_id;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':resp_id', $respID, PDO::PARAM_INT);
		$STH->execute();
		$this->db = null;
	}
	public function delete_respondent($surveyID, $respID) {
		$this->Connect();
		$sql = "DELETE FROM tblRespondent WHERE survey_id=:survey_id AND resp_id=:resp_id;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':resp_id', $respID, PDO::PARAM_INT);
		$STH->execute();
		$this->db = null;
	}
	public function delete_all_respondents($surveyID) {
		$this->Connect();
		$sql = "DELETE FROM tblRespondent WHERE survey_id=:survey_id;";
		$sql .= "DELETE an FROM tblAnswer an INNER JOIN tblRespondent resp ON an.resp_id = resp.resp_id WHERE resp.survey_id=:survey_id;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$this->db = null;
	}
	public function reset_all_respondents($surveyID) {
		$this->Connect();
		$sql .= "UPDATE tblRespondent SET start_dt=NULL, last_dt=NULL, last_page_id=NULL, survey_completed=0 WHERE survey_id=:survey_id;";
		$sql .= "DELETE an FROM tblAnswer an INNER JOIN tblRespondent resp ON an.resp_id = resp.resp_id WHERE resp.survey_id=:survey_id;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$this->db = null;
	}
	public function edit_question($surveyID, $questionID, $questionCode, $questionDesc, $questionDescAlt, $questionExtra, $questionExtraAlt, $questionEnabled) {
		$this->Connect();
		$sql = "UPDATE tblQuestion SET question_code=:question_code, question_desc=:question_desc, question_desc_alt=:question_desc_alt, question_extra=:question_extra, question_extra_alt=:question_extra_alt, question_enabled=:question_enabled WHERE survey_id=:survey_id AND question_id=:question_id;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':question_id', $questionID, PDO::PARAM_INT);
		$STH->bindValue(':question_code', $questionCode, PDO::PARAM_STR);
		$STH->bindValue(':question_desc', $questionDesc, PDO::PARAM_STR);
		$STH->bindValue(':question_desc_alt', $questionDescAlt, PDO::PARAM_STR);
		$STH->bindValue(':question_extra', $questionExtra, PDO::PARAM_STR);
		$STH->bindValue(':question_extra_alt', $questionExtraAlt, PDO::PARAM_STR);
		$STH->bindValue(':question_enabled', $questionEnabled, PDO::PARAM_INT);
		$STH->execute();
		$this->db = null;
	}
	public function resequence_question($surveyID, $pageID, $questionID, $direction) {
		$this->Connect();
		//get sequence of question id
		$sql = "SELECT question_seq FROM tblQuestion WHERE question_id=:question_id;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':question_id', $questionID, PDO::PARAM_INT);
		$STH->execute();
		$seq = $STH->fetchColumn();
		//determine direction
		if($direction == "up") {
			$targetSeq = $seq-1;
		} else {
			$targetSeq = $seq+1;
		}
		//get target question we are swapping with
		$sql = "SELECT question_id FROM tblQuestion WHERE survey_id=:survey_id AND page_id=:page_id AND question_seq=" . $targetSeq . ";";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':page_id', $pageID, PDO::PARAM_INT);
		$STH->execute();
		$targetQID = $STH->fetchColumn();
		//if target exists then do swap
		if(!empty($targetQID)) {
			$sql = "UPDATE tblQuestion SET question_seq=" . $targetSeq . " WHERE question_id=:question_id;";
			$sql .= "UPDATE tblQuestion SET question_seq=" . $seq . " WHERE question_id=" . $targetQID;
			$STH = $this->db->prepare($sql);
			$STH->bindValue(':question_id', $questionID, PDO::PARAM_INT);
			$STH->execute();
		}
		$this->db = null;
	}
	public function survey_respondents($surveyID) {
		$this->Connect();
		$sql = "SELECT resp_id, resp_access_code, resp_email, resp_first, resp_last, resp_alt, cust_1, cust_2, cust_3, cust_4, cust_5, cust_6, start_dt, last_dt, survey_completed FROM tblRespondent WHERE survey_id=:survey_id ORDER BY resp_last ASC, resp_first ASC;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$result = $STH->fetchAll();
		//$result = $STH->fetchAll(PDO::FETCH_ASSOC);
		//$result = $STH->fetchAll(PDO::FETCH_KEY_PAIR);
		$this->db = null;
		return $result;
	}
	public function update_settings($surveyID, $showSplashPage, $txtSplashPage, $txtBeginPage, $txtEndPage, $showSummary, $txtFooter, $txtContactEmail, $txtContactPhone, $txtWeeklyHoursText, $txtAnnualLegalHoursText, $txtLogoSplash, $txtLogoSurvey, $showProgressBar) {
		$this->Connect();
		$sql = "UPDATE tblSettings SET show_splash_page=:show_splash_page, splash_page=:splash_page, begin_page=:begin_page, end_page=:end_page, show_summary=:show_summary, footer=:footer, contact_email=:contact_email, contact_phone=:contact_phone, weekly_hours_text=:weekly_hours_text, annual_legal_hours_text=:annual_legal_hours_text, logo_splash=:logo_splash, logo_survey=:logo_survey, show_progress_bar=:show_progress_bar WHERE survey_id=:survey_id;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':show_splash_page', $showSplashPage, PDO::PARAM_INT);
		$STH->bindValue(':splash_page', $txtSplashPage, PDO::PARAM_STR);
		$STH->bindValue(':begin_page', $txtBeginPage, PDO::PARAM_STR);
		$STH->bindValue(':end_page', $txtEndPage, PDO::PARAM_STR);
		$STH->bindValue(':show_summary', $showSummary, PDO::PARAM_INT);
		$STH->bindValue(':footer', $txtFooter, PDO::PARAM_STR);
		$STH->bindValue(':contact_email', $txtContactEmail, PDO::PARAM_STR);
		$STH->bindValue(':contact_phone', $txtContactPhone, PDO::PARAM_STR);
		$STH->bindValue(':weekly_hours_text', $txtWeeklyHoursText, PDO::PARAM_STR);
		$STH->bindValue(':annual_legal_hours_text', $txtAnnualLegalHoursText, PDO::PARAM_STR);
		$STH->bindValue(':logo_splash', $txtLogoSplash, PDO::PARAM_STR);
		$STH->bindValue(':logo_survey', $txtLogoSurvey, PDO::PARAM_STR);
		$STH->bindValue(':show_progress_bar', $showProgressBar, PDO::PARAM_INT);
		$STH->execute();
		$this->db = null;
	}
	public function delete_question_and_dependents($surveyID, $pageID, $questionID, $deletePage) {
	    // TODO: not sure if the following commenting out affects anything else
        // but it's needed to delete a root page
		if(empty($surveyID) || empty($pageID)) { // || empty($questionID)) {
			return false;
		}
		$this->Connect();

		//FIND DEPENDENT PAGES
		$map = surveyMap($surveyID);
		if ($questionID) {
            $pageIDArray = '';
            for ($a = 0; $a < count($map); ++$a) {
                if ($map[$a]['question_id_parent'] == $questionID ||
                    $map[$a]['question_id_parent_2'] == $questionID ||
                    $map[$a]['question_id_parent_3'] == $questionID ||
                    $map[$a]['question_id_parent_4'] == $questionID ||
                    $map[$a]['question_id_parent_5'] == $questionID) {
                    $pageIDArray .= $map[$a]['page_id'] . ","; //set in delete array
                }
            }
            //DELETE DEPENDENTS PAGE AND QUESTIONS
            if (!empty($pageIDArray)) {
                $pageIDArray = rtrim($pageIDArray, ","); //chop off last comma
                $sql = "DELETE FROM tblQuestion WHERE survey_id=:survey_id AND page_id IN(" . $pageIDArray . ");";
                $sql .= "DELETE FROM tblPage WHERE survey_id=:survey_id AND page_id IN(" . $pageIDArray . ");";
                $STH = $this->db->prepare($sql);
                $STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
                $STH->execute();
                unset($sql);
            }
        }
		if($deletePage == false) { //we don't want to delete the spawning parent question if deleting the branching page
			//DELETE PARENT QUESTION
			$sql = "DELETE FROM tblQuestion WHERE survey_id=:survey_id AND question_id=:question_id;";
			$STH = $this->db->prepare($sql);
			$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
			$STH->bindValue(':question_id', $questionID, PDO::PARAM_INT);
			$STH->execute();
			unset($sql);
			
			//RE SEQUENCE AFTER DELETE
			$sql = "SET @a:=0; UPDATE tblQuestion SET question_seq=@a:=@a+1 WHERE survey_id=:survey_id AND page_id=:page_id ORDER BY question_seq ASC;";
			$STH = $this->db->prepare($sql);
			$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
			$STH->bindValue(':page_id', $pageID, PDO::PARAM_INT);
			$STH->execute();
		} else {
			//DELETE PAGE
			$sql = "DELETE FROM tblPage WHERE survey_id=:survey_id AND page_id=:page_id;";
			$STH = $this->db->prepare($sql);
			$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
			$STH->bindValue(':page_id', $pageID, PDO::PARAM_INT);
			$STH->execute();
			unset($sql);
			
			//RE SEQUENCE AFTER DELETE
			$sql = "SET @a:=0; UPDATE tblPage SET page_seq=@a:=@a+1 WHERE survey_id=:survey_id AND question_id_parent=:question_id ORDER BY page_seq ASC;";
			$STH = $this->db->prepare($sql);
			$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
			$STH->bindValue(':question_id', $questionID, PDO::PARAM_INT);
			$STH->execute();
		}
		
		$this->db = null;
	}
	public function get_resp_id($surveyID, $accessCode) {
		$this->Connect();
		$sql = 'SELECT resp_id FROM tblRespondent WHERE survey_id=:survey_id AND resp_access_code=:resp_access_code';
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':resp_access_code', $accessCode, PDO::PARAM_STR);
		$STH->execute();
		$respID = $STH->fetchColumn();
		$this->db = null;
		return $respID;
	}
	public function update_account($accountID, $accountUsn, $accountFirstName, $accountLastName, $accountEmailAddress, $hashedPwd) {
		$this->Connect();
		$sql = "UPDATE tblAccount SET account_usn=:account_usn, account_first_name=:account_first_name, account_last_name=:account_last_name, account_email_address=:account_email_address WHERE account_id=:account_id;";
		if(!empty($hashedPwd)) {
			$sql .= "UPDATE tblAccount SET account_pwd=:account_pwd WHERE account_id=:account_id;";
		}
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':account_id', $accountID, PDO::PARAM_INT);
		$STH->bindValue(':account_usn', $accountUsn, PDO::PARAM_STR);
		$STH->bindValue(':account_first_name', $accountFirstName, PDO::PARAM_STR);
		$STH->bindValue(':account_last_name', $accountLastName, PDO::PARAM_STR);
		$STH->bindValue(':account_email_address', $accountEmailAddress, PDO::PARAM_STR);
		if(!empty($hashedPwd)) {
			$STH->bindValue(':account_pwd', $hashedPwd, PDO::PARAM_STR);
		}
		$STH->execute();
		$this->db = null;
	}
	public function calcs($surveyID, $pageID, $questionID, $group, $filters) {
		$this->Connect();

		$start = "SELECT ";

		if(sizeof($filters) > 1) {
			$start .= "CONCAT(";
		}

		for($k = 0; $k < sizeof($filters); $k++) {
			$start .= substr($filters[$k], 0, strpos($filters[$k], "="));
			//last filter
			if($k == (sizeof($filters)-1)) {
				if(sizeof($filters) > 1) {
					$start .= ")";
				}
				$start .= " as filters, ";
			} else {
				$start .= ", \",\", ";
			}
		}

		if(sizeof($group) > 1) {
			$start .= "CONCAT(";
		}

		for($k = 0; $k < sizeof($group); $k++) {
			$start .= $group[$k];
			//last group
			if($k == (sizeof($group)-1)) {
				if(sizeof($group) > 1) {
					$start .= ")";
				}
				$start .= " as groups, ";
			} else {
				$start .= ", \",\", ";
			}
		}
/*
		$start .= "q.question_desc, q.question_id, 
				count(a.resp_id) as count";
*/
		//Dan - added parent page id of the question - used to create link to the next chart
		$start .= "q.question_desc, q.question_id, q.page_id, 
				count(a.resp_id) as count";
				
		$tempPageID = $pageID;
		$parentQuestionID = 1;
		$i = 0;
		$sqlMultiplier = "";
		while($parentQuestionID != 0) {
			$i++;
			$preSql = "SELECT p.page_id, p.question_id_parent, q.page_id as temp FROM `tblPage` p LEFT JOIN `tblQuestion` q ON p.question_id_parent = q.question_id WHERE p.page_id =:page_id";
			$STH = $this->db->prepare($preSql);
			$STH->bindValue(':page_id', $tempPageID, PDO::PARAM_INT);
			$STH->execute();
			$result = $STH->fetchAll(PDO::FETCH_ASSOC);

			$tempPageID = @$result[0]['temp'];
			$parentQuestionID = @$result[0]['question_id_parent'];

			if($parentQuestionID > 0) {
				$sql[$i] = "SELECT answer_value 
	                             FROM   tblAnswer a".$i." 
	                             WHERE  question_id = ".$parentQuestionID." 
	                                    AND a".$i.".resp_id = a.resp_id";
	        }
		}

		$currentPct = "a.answer_value";


		$end =	" FROM   `tblQuestion` q 
			       JOIN `tblAnswer` a 
			         ON a.question_id = q.question_id 
			       JOIN `tblRespondent` r 
			         ON a.resp_id = r.resp_id 
			WHERE  q.survey_id =:survey_id
			       AND q.page_id =:page_id 
				   AND r.survey_completed=1
				   ";

				   /*
		//Dan - added left join to tblPage pg to get the parent page ID of the question
		$end =	" FROM   `tblQuestion` q 
			       JOIN `tblAnswer` a 
			         ON a.question_id = q.question_id 
			       JOIN `tblRespondent` r 
			         ON a.resp_id = r.resp_id 
				   LEFT JOIN `tblPage` pg 
				   	 ON pg.question_id_parent = q.question_id 
			WHERE  q.survey_id =:survey_id
			       AND q.page_id =:page_id 
				   AND r.survey_completed=1
				   ";
				   */

		if(isset($questionID) && ($questionID > 0)) {
			$end .= " AND q.question_id = " . $questionID;
		}

		if(sizeof($filters) > 0) {
			$end .= " AND ";
		}
		for($k = 0; $k < sizeof($filters); $k++) {
			$end .= $filters[$k];
			if($k < (sizeof($filters)-1)) {
				$end .= " AND ";
			}
		}

		if(sizeof($group) > 0) {
			$end .= " GROUP BY q.question_desc, ";
				for($k = 0; $k < sizeof($group); $k++) {
				$end .= $group[$k];
				if($k < (sizeof($group)-1)) {
					$end .= ",";
				}
			}
		} else {
			$end .= " GROUP BY a.question_id, q.question_desc ";
		}

		//Select etc.
		$sqlFull = $start;

		//hours calculation
			$sqlFull .= ", sum(((".$currentPct."))";

			for($j=1; $j<$i; $j++) {
				$sqlFull .= "*((".$sql[$j].")/100)";
			}

			$sqlFull .= ") as hours";

		//compensation calculation

			//Get total hours per respondent
			$sqTot = "SELECT SUM(answer_value) 
						FROM tblAnswer aa 
						JOIN tblQuestion qq ON aa.question_id = qq.question_id 
						JOIN tblPage pp ON qq.page_id = pp.page_id 
						WHERE  question_id_parent = 0 AND aa.resp_id = a.resp_id
						";

			//multiplier of all categories except top layer
			if($i == 1) {
				$sqlFull .= ", sum((".$currentPct.")";
				$sqlFull .= "/(".$sqTot.")";
			} else {
				$sqlFull .= ", sum(((".$currentPct.")/100)";
			}

			for($j=1; $j<$i; $j++) {
				if($j == ($i-1)) {
					$sqlFull .= "*((".$sql[$j].")/(".$sqTot."))";
				} else {
					$sqlFull .= "*((".$sql[$j].")/100)";
				}
			}

			$sqlFull .= "*r.resp_total_compensation";

			$sqlFull .= ") as salary";

		//From, joins, where, group
		$sqlFull .= $end;
		
		//Dan - added order by salary DESC
		//$sqlFull .= " ORDER BY q.question_seq ASC;";
		$sqlFull .= " ORDER BY salary DESC;";
		
		//return $sqlFull;
		
		$STH = $this->db->prepare($sqlFull);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':page_id', $pageID, PDO::PARAM_INT);

		//echo '<br>'.$sqlFull;

		$STH->execute();
		$result = $STH->fetchAll(PDO::FETCH_ASSOC);
		$this->db = null;
		return $result;
	}
	public function survey_filters($surveyID, $cust) {
		$this->Connect();
		$sql = "SELECT DISTINCT(cust_".$cust.") as filter, 'cust_".$cust."' as type FROM `tblRespondent` WHERE `survey_id` =:survey_id 
				ORDER by filter asc";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$result = $STH->fetchAll();
		$this->db = null;
		return $result;
	}
	public function survey_custs($surveyID) {
		$this->Connect();
		$sql = "SELECT `cust_1_label`, `cust_2_label`, `cust_3_label`, `cust_4_label`, `cust_5_label`, `cust_6_label` FROM `tblSurvey` WHERE `survey_id` =:survey_id";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$result = $STH->fetchAll();
		$this->db = null;
		return $result;
	}
	public function get_field_labels($surveyID) {
		$this->Connect();
		$sql = "SELECT cust_1_label, cust_2_label, cust_3_label, cust_4_label, cust_5_label, cust_6_label FROM tblSurvey WHERE survey_id=:survey_id;";
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		$this->db = null;
		return $row;
	}
	public function update_field_labels($surveyID, $cust1, $cust2, $cust3, $cust4, $cust5, $cust6) {
		$this->Connect();
		$sql = "UPDATE tblSurvey SET cust_1_label=:cust_1_label, cust_2_label=:cust_2_label, cust_3_label=:cust_3_label, cust_4_label=:cust_4_label, cust_5_label=:cust_5_label, cust_6_label=:cust_6_label WHERE survey_id=:survey_id;";
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':cust_1_label', $cust1, PDO::PARAM_STR);
		$STH->bindValue(':cust_2_label', $cust2, PDO::PARAM_STR);
		$STH->bindValue(':cust_3_label', $cust3, PDO::PARAM_STR);
		$STH->bindValue(':cust_4_label', $cust4, PDO::PARAM_STR);
		$STH->bindValue(':cust_5_label', $cust5, PDO::PARAM_STR);
		$STH->bindValue(':cust_6_label', $cust6, PDO::PARAM_STR);
		$STH->execute();
		$this->db = null;
	}
	public function parent_page_id($question_id) {
		$this->Connect();
		$sql = "SELECT page_id FROM `tblPage` WHERE question_id_parent =:question_id ORDER BY page_type LIMIT 1;";
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':question_id', $question_id, PDO::PARAM_INT);
		$STH->execute();
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		$this->db = null;
		return $row['page_id'];
	}
}
?>
