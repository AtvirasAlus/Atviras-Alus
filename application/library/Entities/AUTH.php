<?php

class Entities_AUTH {

	static function dologin($user_email, $user_password, $remember_me = false) {
		$auth = Zend_Auth::getInstance();
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("users")
				->where("user_email = ?", $user_email);
		$result = $db->fetchRow($select);
		if ($result['user_enabled'] == 0){
			$select = $db->select()
					->from("users_ban_ip")
					->where("ban_ip = ?", $_SERVER['REMOTE_ADDR']);
			$res = $db->fetchAll($select);
			if (sizeof($res) == 0){
				$chost = explode(".", $_SERVER["SERVER_NAME"]);
				$chost = ".".$chost[sizeof($chost)-2].".".$chost[sizeof($chost)-1];
				$db->insert("users_ban_ip", array("ban_ip" => $_SERVER['REMOTE_ADDR'], "ban_user_id" => $result['user_id']));
				setcookie("user_btoken", md5("loremipsum"), time() + 1209600, "/", $chost);
				header('HTTP/1.0 403 Forbidden');
				return false;
				exit;
			}
		}
		$authAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'user_email', 'user_password', '? AND user_active = "1"');
		$authAdapter->setIdentity(strtolower($user_email))->setCredential($user_password);
		$result = $auth->authenticate($authAdapter);
		if ($result->isValid()) {
			$chost = explode(".", $_SERVER["SERVER_NAME"]);
			$chost = ".".$chost[sizeof($chost)-2].".".$chost[sizeof($chost)-1];
			if ($remember_me) {
				setcookie("user_email", $user_email, time() + 1209600, "/", $chost);
				setcookie("user_password", $user_password, time() + 1209600, "/", $chost);
				setcookie("remember", '1', time() + 1209600, "/", $chost);
			} else {
				setcookie("user_email", $user_email, time() + 21600, "/", $chost);
				setcookie("user_password", $user_password, time() + 21600, "/", $chost);
				setcookie("remember", '0', time() + 21600, "/", $chost);
			}

			$storage = new Zend_Auth_Storage_Session();
			$storage->write($authAdapter->getResultRowObject());
			$db->update("users", array("user_lastlogin" => new Zend_Db_Expr("NOW()")), array("user_email = '" . $user_email . "'"));
			$db->insert("users_log", array(
				"log_posted" => date("Y-m-d H:i:s"),
				"log_user_email" => $user_email,
				"log_user_ip" => $_SERVER["REMOTE_ADDR"],
				"log_user_agent" => $_SERVER["HTTP_USER_AGENT"]
			));
			return $storage->read();
		} else {

			return false;
		}
	}

}

?>
