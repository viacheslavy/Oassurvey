<?php

namespace App\Classes;

use PDO;
use PDOException;

class Database {
	protected $db;
	protected function Connect() {
		$db_host = "db.oassurvey.com";
		$db_name = "oassurve_mysqoasdb";
		$db_user = "oassurve_dangre";
		$db_pass = "P84*((!43GbVvk)^";
//		$db_host = 'localhost';
//		$db_name = 'ofpartner';
//		$db_user = 'root';
//		$db_pass = '123';
//        $db_host = 'ofpartner.db.11768123.hostedresource.com';
//        $db_name = 'ofpartner';
//        $db_user = 'ofpartner';
//        $db_pass = 'Rw820123!';
		try {
			$this->db = new PDO("mysql:host=" . $db_host . ";dbname=" . $db_name, $db_user, $db_pass);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		} 
		catch(PDOException $e) {
			die($e);
		}
	}
}
?>
