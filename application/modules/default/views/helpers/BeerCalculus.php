<?
class Zend_View_Helper_BeerCalculus {
public function  beerCalculus($data=array()) {
		$view = new Zend_View();
		
		$view->addScriptPath(APPLICATION_PATH."/modules/default/views/helpers/");
		$db = Zend_Registry::get("db");
		$select=$db->select()
			->from("beer_malt")
			->order("malt_name");
			$view->malts=$db->fetchAll($select);
		$select=$db->select()
			->from("beer_styles")
			->order("style_name");
			$view->styles=$db->fetchAll($select);
		$select=$db->select()
			->from("beer_hops")
			->order("hop_name");
			$view->hops=$db->fetchAll($select);
		
		$select=$db->select()
			->from("beer_yeasts")
			->order("yeast_name");
			$view->yeasts=$db->fetchAll($select);
			$view->data=$data;
			$out=$view->render("calculus.phtml");
		return 	$out;
		
	}
		
}
?>
