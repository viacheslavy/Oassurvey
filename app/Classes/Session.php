<?php

namespace App\Classes;

class Session {
	public static $_sessionStarted = false;

	public static function start() {
		if (self::$_sessionStarted == false) {
			session_start();
			self::$_sessionStarted = true;
		}
	}
	public static function set($key, $value) {
		$_SESSION[$key] = $value;
		$_SESSION['LAST_ACTIVITY'] = time();
	}
	public static function get($key) {
		if(isset($_SESSION[$key]))
			return $_SESSION[$key];
		else
			return false;
	}
	public static function verifySession($secondsToTimeout) {
	
	if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $secondsToTimeout)) {
		self::destroy();
		}
		else {
			$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
		}
	}
	public static function display() {
		echo '<pre>';
		print_r($_SESSION);
		echo '</pre>';
	}
	public static function destroy() {
		if(self::$_sessionStarted == true) {
			session_unset();
			session_destroy();
		}
	}
}