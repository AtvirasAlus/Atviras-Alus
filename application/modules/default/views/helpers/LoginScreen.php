<?
class Zend_View_Helper_LoginScreen {
function  loginScreen() {
		$storage = new Zend_Auth_Storage_Session();
		$view = new Zend_View();	
		$view->user_data = $storage->read();
		$view->addScriptPath(APPLICATION_PATH."/modules/default/views/helpers/");
		return $view->render("login.phtml");
	}

}
?>
