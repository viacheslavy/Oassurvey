<?php

namespace App\Classes;

use PDO;

class Report extends Database {
	
	public function survey_info($surveyID) {
		$this->Connect();
			$sql = 'SELECT sv.survey_name, sv.cust_1_label, sv.cust_2_label, sv.cust_3_label, sv.cust_4_label, sv.cust_5_label, sv.cust_6_label, st.logo_survey, (SELECT COUNT(resp_id) FROM tblRespondent WHERE survey_id=:survey_id AND last_dt IS NOT NULL) AS resp_ct FROM tblSurvey sv INNER JOIN tblSettings st ON sv.survey_id=st.survey_id WHERE sv.survey_id=:survey_id;';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$arr = $STH->fetch(PDO::FETCH_ASSOC);
		$this->db = null;
		return $arr;
	}
	public function resp_by_custs($surveyID, $cust1, $cust2, $cust3, $cust4, $cust5, $cust6) {
		$this->Connect();
			$sql = 'SELECT COUNT(resp_id) as resp_ct FROM tblRespondent WHERE survey_id=:survey_id AND last_dt IS NOT NULL';
			if(isset($cust1) && $cust1 != NULL)
				$sql .= ' AND cust_1 = :cust_1';
			if(isset($cust2) && $cust2 != NULL)
				$sql .= ' AND cust_2 = :cust_2';
			if(isset($cust3) && $cust3 != NULL)
				$sql .= ' AND cust_3 = :cust_3';
			if(isset($cust4) && $cust4 != NULL)
				$sql .= ' AND cust_4 = :cust_4';
			if(isset($cust5) && $cust5 != NULL)
				$sql .= ' AND cust_5 = :cust_5';
			if(isset($cust6) && $cust6 != NULL)
				$sql .= ' AND cust_6 = :cust_6';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
			if(isset($cust1) && $cust1 != NULL)
				$STH->bindValue(':cust_1', $cust1, PDO::PARAM_STR);
			if(isset($cust2) && $cust2 != NULL)
				$STH->bindValue(':cust_2', $cust2, PDO::PARAM_STR);
			if(isset($cust3) && $cust3 != NULL)
				$STH->bindValue(':cust_3', $cust3, PDO::PARAM_STR);
			if(isset($cust4) && $cust4 != NULL)
				$STH->bindValue(':cust_4', $cust4, PDO::PARAM_STR);
			if(isset($cust5) && $cust5 != NULL)
				$STH->bindValue(':cust_5', $cust5, PDO::PARAM_STR);
			if(isset($cust6) && $cust6 != NULL)
				$STH->bindValue(':cust_6', $cust6, PDO::PARAM_STR);
		$STH->execute();
		$resp_ct = $STH->fetchColumn();
		$this->db = null;
		return $resp_ct;
	}
	public function report_profile($surveyID, $col) {
		$this->Connect();
		$sql = "SELECT $col AS item, COUNT(resp_id) AS ct FROM tblRespondent WHERE survey_id=:survey_id AND survey_completed=1 GROUP BY $col ORDER BY ct DESC, $col ASC;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$arr = $STH->fetchAll(PDO::FETCH_ASSOC);
		$this->db = null;
		return $arr;
	}
	public function get_all_individuals($surveyID, $isComplete) {
	    $sqlIsComplete = '';
		if ($isComplete) {
			$sqlIsComplete = "AND survey_completed=1";
		}
		$this->Connect();
		$sql = "SELECT resp_id, resp_first, resp_last, cust_3 AS custom FROM tblRespondent WHERE survey_id=:survey_id AND last_dt IS NOT NULL " . $sqlIsComplete . " ORDER BY resp_last, resp_first;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->execute();
		$arr = $STH->fetchAll(PDO::FETCH_ASSOC);
		$this->db = null;
		return $arr;
	}
	public function get_individual($respID) {
		$this->Connect();
		$sql = "SELECT * from tblRespondent WHERE resp_id=:resp_id;";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':resp_id', $respID, PDO::PARAM_INT);
		$STH->execute();
		$row = $STH->fetch(PDO::FETCH_ASSOC);
		$this->db = null;
		return $row;
	}
	public function report_page($surveyID, $pageID, $hoursPage, $pageArr) {
		$this->Connect();
		if($pageID == $hoursPage) { //if getting results for hours page
			$sql = 'SELECT sv.question_id, sv.question_desc, pg.page_id FROM tblQuestion sv LEFT JOIN tblPage pg ON pg.question_id_parent=sv.question_id WHERE sv.survey_id=:survey_id AND sv.page_id=:page_id AND pg.page_type !=2 ORDER BY sv.question_seq ASC;';
		} else { // all others branching from hours page
			//for($p=0;$p<count($pageArr);++$p) {
			//	if($p==0) { //the first iteration is the hours
			//		$pageIDHours = $pageArr[$p]["question_id"];
			//		//$calc .= "SUM(SELECT an.answer_value FROM 
			//	}
			//}
			$sql = 'SELECT sv.question_id, sv.question_desc, (SELECT pg.page_id FROM tblPage pg WHERE pg.question_id_parent = sv.question_id) AS page_id FROM tblQuestion sv WHERE sv.survey_id=:survey_id AND sv.page_id=:page_id ORDER BY sv.question_seq ASC;';
		}
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
		$STH->bindValue(':page_id', $pageID, PDO::PARAM_INT);
		$STH->execute();
		$arr = $STH->fetchAll(PDO::FETCH_ASSOC);
		$this->db = null;
		return $arr;
	}
	public function parent_pages($surveyID, $questionIDArray) {
	    $ids = '';
		for($q=0; $q<count($questionIDArray);++$q) {
			$ids .= $questionIDArray[$q] . ",";
		}
		if(!empty($questionIDArray)) {
			$ids = rtrim($ids,","); //chop off last comma
			$sql = "SELECT qu.question_desc, qu.question_id, pg.page_id FROM tblQuestion qu INNER JOIN tblPage pg ON qu.question_id=pg.question_id_parent WHERE qu.survey_id=:survey_id AND pg.page_type !=2 AND qu.question_id IN($ids);";
			$this->Connect();
			$STH = $this->db->prepare($sql);
			$STH->bindValue(':survey_id', $surveyID, PDO::PARAM_INT);
			$STH->execute();
			$result = $STH->fetchAll(PDO::FETCH_ASSOC);
			$this->db = null;
			return $result;
		}
	}
}
?>
