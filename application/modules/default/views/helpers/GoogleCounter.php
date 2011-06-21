<?
class Zend_View_Helper_GoogleCounter {
public function  googleCounter() {
			//$storage = new Zend_Auth_Storage_Session(); 
			//$storage->read()
			define('ga_email','simonas.gutautas@gmail.com');
			define('ga_password','sg035if');
			define('ga_profile_id','42076848');
			$view = new Zend_View();
			$view->addScriptPath(APPLICATION_PATH."/modules/default/views/helpers/");
			$view->ga = new Entities_Gapi(ga_email,ga_password);
			$view->ga->getAuthToken();
			$view->ga->requestReportData(ga_profile_id,array('browser','browserVersion'),array('pageviews','visits'));
			$out=$view->render("counter.phtml");
		return 	$out;
		
	}
		
}
?>
