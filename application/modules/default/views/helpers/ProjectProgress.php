<?
class Zend_View_Helper_ProjectProgress extends Zend_View_Helper_Abstract{
public function  projectProgress() {
		
		
		$this->view->addScriptPath(APPLICATION_PATH."/modules/default/views/helpers/");
		$db = Zend_Registry::get("db");
		$select=$db->select()
		->from("beer_styles_description",array("count"=>"count(*)"))
		->where("style_ingredients !=''");
		$this->view->total_styles_translated=$db->fetchRow($select);
		$select=$db->select()
		->from("beer_styles",array("count"=>"count(*)"));
		
		$this->view->total_styles=$db->fetchRow($select);
		
			
			$out=$this->view->render("progress.phtml");
		return 	$out;
		
	}
		
}
?>
