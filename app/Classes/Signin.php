<?php
include_once("clsDatabase.php");
Class Signin extends Database {
	private $salt = "Zo4rU5Z1YyKJAASY0PT6EUg7BBYdlEhPaNLuxAwU8oas1ElzHv0Ri7EM6iraktpx5w";

	public function insertNewAccount($strFirstName, $strLastName, $strEmailAddress, $strUserName, $strPassword){
		$this->Connect();
		$sql = "INSERT INTO tblAccount (account_first_name, account_last_name, account_email_address, account_usn, account_pwd) value (:account_first_name, :account_last_name, :account_email_address, :account_usn, :account_pwd)";
		$STH = $this->db->prepare($sql); 
		$STH->bindValue(':account_first_name', $strFirstName, PDO::PARAM_STR);
		$STH->bindValue(':account_last_name', $strLastName, PDO::PARAM_STR);
		$STH->bindValue(':account_email_address', $strEmailAddress, PDO::PARAM_STR);
		$STH->bindValue(':account_usn', $strUserName, PDO::PARAM_STR);
		$STH->bindValue(':account_pwd', hash("sha512", $strPassword . $this->salt), PDO::PARAM_STR);
		$STH->execute();
		$this->db = null;
	}
	public function duplicateUserName($strUserName) {
		$this->Connect();
		$sql = 'SELECT account_id FROM tblAccount WHERE user_name = :user_name';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':user_name', $strUserName, PDO::PARAM_STR);
		$STH->execute();
		if ($STH->rowCount()==1) {
			return true;
		}
		$this->db = null;
	}
	public function hash_password($strPassword) {
		return hash("sha512", $strPassword . $this->salt);
	}
	public function duplicateEmailAddress($strEmailAddress) {
		$this->Connect();
		$sql = 'SELECT account_id FROM tblAccount WHERE email_address = :email_address';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':email_address', $strEmailAddress, PDO::PARAM_STR);
		$STH->execute();
		if ($STH->rowCount()==1) {
			return true;
		}
		$this->db = null;
	}

    /**
     * @param $userName
     * @param $strPassword
     * @return mixed
     */
    public function verifyAccount($userName, $strPassword) {
		$this->Connect();
		$encryptedPassword = $this->hash_password($strPassword);
		$sql = 'SELECT account_id FROM tblAccount WHERE account_usn = :account_usn AND account_pwd = :account_pwd';
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':account_usn', $userName, PDO::PARAM_STR);
		$STH->bindValue(':account_pwd', $encryptedPassword, PDO::PARAM_STR);
		$STH->execute();
		$actid = $STH->fetchColumn();
		error_log("usn - $userName", "3", "/tmp/test.log");
		error_log("encrypted pw - $encryptedPassword", "3", "/tmp/test.log");
		/*if ($actid == false) {
			$sql = 'SELECT account_id FROM tblAccount WHERE email_address = :email_address AND password = :password';
			$STH = $this->db->prepare($sql);
			$STH->bindValue(':email_address', $strLogin, PDO::PARAM_STR);
			$STH->bindValue(':password', $encryptedPassword, PDO::PARAM_STR);
			$STH->execute();
			if ($STH->rowCount()==1) {
				$actid = $STH->fetchColumn();
			}
		}*/
		$this->db = null;
		return $actid;
	}
	public function last_login_dt($accountID){
		$this->Connect();
		$sql = "UPDATE tblAccount SET account_last_update_dt=:last_login_dt WHERE account_id=:account_id";
		$STH = $this->db->prepare($sql);
		$STH->bindValue(':account_id', $accountID, PDO::PARAM_INT);
		$STH->bindValue(':last_login_dt', date("Y-m-d H:i:s"), PDO::PARAM_STR);
		$STH->execute();
		$this->db = null;
	}
}
?>