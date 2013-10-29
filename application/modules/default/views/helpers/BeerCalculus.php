<?
class Zend_View_Helper_BeerCalculus {
public function  beerCalculus($data=array()) {
		$view = new Zend_View();
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		$this->use_plato = false;
		if (isset($user_info->user_id) && !empty($user_info->user_id)){
			$db = Zend_Registry::get("db");
			$select = $db->select()
					->from("users_attributes")
					->where("users_attributes.user_id = ?", $user_info->user_id)
					->limit(1);
			$u_atribs= $db->fetchRow($select);
			if ($u_atribs['plato'] == 1) {
				$this->use_plato = true;
			}
		}
		$view->use_plato = $this->use_plato;
		
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
