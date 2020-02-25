<?php

namespace App\Classes;

use PDO;

class Takesurvey extends Database {
	
	public function survey_info($surveyID) {
		$this->Connect();
		$sql = 'SELECT survey_id, survey_name, survey_active FROM tblSurvey WHERE survey_id=:survey_id;';
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		$this->db = null;
		return $row;
	}
	public function single_respondent_array($surveyID, $accessCode) {
		$this->Connect();
		$sql = 'SELECT * FROM tblRespondent WHERE survey_id=:survey_id AND resp_access_code=:resp_access_code';
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':resp_access_code', $accessCode, PDO::PARAM_STR);
		$STH->execute();
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		$this->db = null;
		return $row;
	}
	public function page_id($surveyID, $pageSeq) {
		$this->Connect();
		$sql = "SELECT page_id FROM tblPage WHERE survey_id=:survey_id AND page_seq=:page_seq";
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':page_seq', $pageSeq, PDO::PARAM_INT);
		$STH->execute();
		$column = $STH->fetchColumn();
		$this->db = null;
		return $column;
	}
	public function answer_from_question_id_parent($respID, $pageID) {
		$this->Connect();
		$sql = "SELECT answer_value FROM tblAnswer WHERE resp_id=:resp_id AND question_id=(SELECT question_id_parent FROM tblPage WHERE page_id=:page_id);";
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':resp_id', $respID, PDO::PARAM_INT);
		$STH->bindValue(':page_id', $pageID, PDO::PARAM_INT);
		$STH->execute();
		$column = $STH->fetchColumn();
		$this->db = null;
		return $column;
	}
	public function total_pages($surveyID) {
		$this->Connect();
		$sql = "SELECT MAX(page_seq) AS total_pages FROM tblPage WHERE survey_id=:survey_id";
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$column = $STH->fetchColumn();
		$this->db = null;
		return $column;
	}
	public function access_code_valid($surveyID, $accessCode) {
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
	public function test_delete($respID, $deleteArray) {
			for($i=0;$i<count($deleteArray);++$i) {
				$deletePageIDs .= $deleteArray[$i] . ",";
			}
			if(empty($deletePageIDs)) {
				$deletePageIDs = 0; //set array to something so it doesn't trigger a wholesale delete
			}
			$deletePageIDs = rtrim($deletePageIDs,","); //chop off last comma
			$sql .= "SELECT an.* FROM tblAnswer an INNER JOIN tblQuestion qu ON an.question_id = qu.question_id WHERE qu.page_id IN($deletePageIDs) AND an.resp_id=:resp_id;";
			//return $sql;
			$this->Connect();
			$STH = $this->db->prepare($sql); 
			$STH->bindValue(':resp_id', $respID, PDO::PARAM_INT);
			$STH->execute();
			$result = $STH->fetchAll();
			$this->db = null;
			return $result;
	}	
	public function insert_respondent_answers_on_page($respID, $pageID, $deleteArray, $qaArray) {
		$dateTimeStamp = date("Y-m-d H:i:s");
		if(count($qaArray)>0) {
			for($d=0;$d<count($deleteArray);++$d) {
				$deletePageIDs .= $deleteArray[$d] . ",";
			}
			if(empty($deletePageIDs)) {
				$deletePageIDs = 0; //set array to something so it doesn't trigger a wholesale delete
			}
			$deletePageIDs = rtrim($deletePageIDs,","); //chop off last comma
			for($i=0;$i<count($qaArray);++$i) {
				$sqlAppend .= "(:resp_id, :question_id_$i, :answer_value_$i),";
			}
			$sqlAppend = rtrim($sqlAppend,","); //chop off last comma
			$sql .= "DELETE an.* FROM tblAnswer an INNER JOIN tblQuestion qu ON an.question_id = qu.question_id WHERE qu.page_id IN($deletePageIDs) AND an.resp_id=:resp_id;";
			$sql .= "INSERT INTO tblAnswer (resp_id, question_id, answer_value) VALUES " . $sqlAppend . ";";
			$sql .= "UPDATE tblRespondent SET last_page_id=:page_id, last_dt=:last_dt WHERE resp_id=:resp_id;";
			$this->Connect();
			$STH = $this->db->prepare($sql); 
			$STH->bindValue(':resp_id', $respID, PDO::PARAM_INT);
			$STH->bindValue(':page_id', $pageID, PDO::PARAM_INT);
			$STH->bindValue(':last_dt', $dateTimeStamp, PDO::PARAM_INT);
			// add bind loop here
			for($i=0; $i<count($qaArray); $i++) {
				$STH->bindValue(":question_id_$i", $qaArray[$i]['question_id'],PDO::PARAM_INT);
				$STH->bindValue(":answer_value_$i", $qaArray[$i]['answer_value'],PDO::PARAM_INT);
			}
			$STH->execute();
			$this->db = null;
		} //end if answer count > 0
	}
	public function parent_pages_question_desc($surveyID, $questionIDArray) {
	    $ids = '';
		for($q=0; $q<count($questionIDArray);++$q) {
			$ids .= $questionIDArray[$q] . ",";
		}
		if(!empty($questionIDArray)) {
			$ids = rtrim($ids,","); //chop off last comma
			$sql = "SELECT question_desc FROM tblQuestion WHERE survey_id=:survey_id AND question_id IN($ids);";
			$this->Connect();
			$STH = $this->db->prepare($sql);
			$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
			$STH->execute();
			$result = $STH->fetchAll(PDO::FETCH_COLUMN, 0); // FETCH_COLUMN excellent for casting one column array into single dimension
			$this->db = null;
			return $result;
		}
	}
	public function all_question_desc_for_summary($surveyID, $pageIDArray) {
	    $ids = '';
		for($q=0; $q<count($pageIDArray);++$q) {
			$ids .= $pageIDArray[$q] . ",";
		}
		if(!empty($pageIDArray)) {
			$ids = rtrim($ids,","); //chop off last comma
			$sql = "SELECT page_id, question_id, question_desc FROM tblQuestion WHERE survey_id=:survey_id AND page_id IN($ids) AND question_enabled=1 ORDER BY question_seq ASC;";
			$this->Connect();
			$STH = $this->db->prepare($sql);
			$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
			$STH->execute();
			//$result = $STH->fetchAll();
			$result = $STH->fetchAll(PDO::FETCH_ASSOC);
			$this->db = null;
			return $result;
		}
	}
	public function page_info($surveyID, $pageID) {
		$this->Connect();
		$sql = 'SELECT question_id_parent, page_type, page_desc, page_extra FROM tblPage WHERE survey_id=:survey_id AND page_id=:page_id;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':page_id', $pageID, PDO::PARAM_INT);
		$STH->execute();
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		$this->db = null;
		return $row;
	}
	public function questions_on_page($surveyID, $pageID) {
		$this->Connect();
		$sql = 'SELECT * FROM tblQuestion WHERE survey_id=:survey_id AND page_id=:page_id AND question_enabled=1 ORDER BY question_seq ASC;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':page_id', $pageID, PDO::PARAM_INT);
		$STH->execute();
		$result = $STH->fetchAll();
		$this->db = null;
		return $result;
	}
	public function respondent_answers_on_page($respID, $pageID) {
		$this->Connect();
		$sql = 'SELECT an.question_id, an.answer_value FROM tblAnswer an INNER JOIN tblQuestion qu ON an.question_id = qu.question_id WHERE qu.page_id=:page_id AND an.resp_id=:resp_id;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':resp_id', $respID, PDO::PARAM_INT);
		$STH->bindValue(':page_id', $pageID, PDO::PARAM_INT);
		$STH->execute();
		$result = $STH->fetchAll();
		$this->db = null;
		return $result;
	}
	public function question_id_answers_not_zero($respID) {
		$this->Connect();
		$sql = 'SELECT question_id FROM tblAnswer WHERE resp_id=:resp_id AND answer_value > 0;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':resp_id', $respID, PDO::PARAM_INT);
		$STH->execute();
		$result = $STH->fetchAll(PDO::FETCH_COLUMN, 0); // FETCH_COLUMN excellent for casting one column array into single dimension
		$this->db = null;
		return $result;
	}
	public function question_id_page_id_answers_not_zero($respID) {
		$this->Connect();
		$sql = 'SELECT a.question_id, q.page_id FROM tblAnswer a INNER JOIN tblQuestion q ON a.question_id = q.question_id WHERE a.resp_id=:resp_id AND a.answer_value > 0;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':resp_id', $respID, PDO::PARAM_INT);
		$STH->execute();
		$result = $STH->fetchAll();
		$this->db = null;
		return $result;
	}
	public function question_id_answer_value_not_zero($respID) {
		$this->Connect();
		$sql = 'SELECT question_id, answer_value FROM tblAnswer WHERE resp_id=:resp_id AND answer_value > 0;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':resp_id', $respID, PDO::PARAM_INT);
		$STH->execute();
		$result = $STH->fetchAll(PDO::FETCH_ASSOC);
		$this->db = null;
		return $result;
	}
	public function question_id_answer_value_all($surveyID) {
		$this->Connect();
		$sql = 'SELECT an.resp_id, an.question_id, an.answer_value FROM tblAnswer an INNER JOIN tblRespondent rs ON an.resp_id=rs.resp_id WHERE rs.survey_id=:survey_id;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		//$result = $STH->fetchAll();
		$result = $STH->fetchAll(PDO::FETCH_ASSOC);
		$this->db = null;
		return $result;
	}
	public function survey_page_map($surveyID) {
		$this->Connect();
		$sql = "SELECT page_id, question_id_parent, page_type FROM tblPage WHERE survey_id=:survey_id ORDER BY page_seq ASC;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$result = $STH->fetchAll();
		$this->db = null;
		return $result;
	}
	public function survey_question_map($surveyID) {
		$this->Connect();
		$sql = "SELECT page_id, question_id, question_enabled FROM tblQuestion WHERE survey_id=:survey_id ORDER BY question_seq ASC;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$result = $STH->fetchAll();
		$this->db = null;
		return $result;
	}
	public function mark_survey_completed($surveyID, $respID) {
		$sql = "UPDATE tblRespondent SET survey_completed=1 WHERE survey_id=:survey_id AND resp_id=:resp_id;";
		$this->Connect();
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':resp_id', $respID, PDO::PARAM_INT);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$this->db = null;
	}
	public function survey_settings($surveyID) {
		$this->Connect();
		$sql = "SELECT * FROM tblSettings WHERE survey_id=:survey_id";
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		$this->db = null;
		return $row;
	}
	public function get_begin_page($surveyID) {
		$this->Connect();
		$sql = "SELECT begin_page FROM tblSettings WHERE survey_id=:survey_id";
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$column = $STH->fetchColumn();
		$this->db = null;
		return $column;
	}
	public function get_end_page($surveyID) {
		$this->Connect();
		$sql = "SELECT end_page, show_summary FROM tblSettings WHERE survey_id=:survey_id";
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		$this->db = null;
		return $row;
	}
	public function insert_start_dt($surveyID, $respID) {
		$dateTimeStamp = date("Y-m-d H:i:s");
		$this->Connect();
		$sql .= "UPDATE tblRespondent SET start_dt='" . $dateTimeStamp . "' WHERE survey_id=:survey_id AND resp_id=:resp_id AND start_dt IS NULL;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':resp_id', $respID, PDO::PARAM_INT);
		$STH->execute();
		$this->db = null;
	}
	public function parent_page_id($surveyID, $questionID) {
		$this->Connect();
		$sql = "SELECT page_id FROM tblQuestion WHERE survey_id=:survey_id AND question_id=:question_id;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':question_id', $questionID, PDO::PARAM_INT);
		$STH->execute();
		$result = $STH->fetchColumn();
		$this->db = null;
		return $result;
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
}
?>