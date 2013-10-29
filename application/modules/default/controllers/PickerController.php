<?php
class PickerController extends Zend_Controller_Action {
	public function init() {
		$this->storage = new Zend_Auth_Storage_Session();
		$this->user_info = $this->storage->read();
		$this->db = Zend_Registry::get("db");
	}

	public function indexAction() {
		$db = $this->db;
		$select = $db->select()
				->from("beer_recipes", array(
					"MAX(recipe_ibu) as max_ibu", "MIN(recipe_ibu) as min_ibu", 
					"MAX(recipe_ebc) as max_ebc", "MIN(recipe_ebc) as min_ebc", 
					"MAX(recipe_abv) as max_abv", "MIN(recipe_abv) as min_abv"
				))
				->where("recipe_ebc <= '100'")
				->where("recipe_ibu <= '100'")
				->where("recipe_abv <= '20'")
				->where("recipe_publish = '1'");
		$result = $db->fetchRow($select);
		$this->view->params = $result;
		
		$sel_vals = $result;
		$sel_vals['type_val'] = "all";
		$sel_vals['style_val'] = "all";
		if ($ibu_min = $this->getRequest()->getParam('ibu_min')) $sel_vals['min_ibu'] = $ibu_min;
		if ($ibu_max = $this->getRequest()->getParam('ibu_max')) $sel_vals['max_ibu'] = $ibu_max;
		if ($abv_min = $this->getRequest()->getParam('abv_min')) $sel_vals['min_abv'] = $abv_min;
		if ($abv_max = $this->getRequest()->getParam('abv_max')) $sel_vals['max_abv'] = $abv_max;
		if ($ebc_min = $this->getRequest()->getParam('ebc_min')) $sel_vals['min_ebc'] = $ebc_min;
		if ($ebc_max = $this->getRequest()->getParam('ebc_max')) $sel_vals['max_ebc'] = $ebc_max;
		if ($style_val = $this->getRequest()->getParam('style_val')) $sel_vals['style_val'] = $style_val;
		if ($type_val = $this->getRequest()->getParam('type_val')) $sel_vals['type_val'] = $type_val;
		$this->view->sel_vals = $sel_vals;
		
		$select = $db->select()
				->from("beer_yeast_cats");
		$result = $db->fetchAll($select);
		$this->view->styles = $result;
	}
	public function previewAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$ibu_min = $this->getRequest()->getParam('ibu_min');
		$ibu_max = $this->getRequest()->getParam('ibu_max');
		$ebc_min = $this->getRequest()->getParam('ebc_min');
		$ebc_max = $this->getRequest()->getParam('ebc_max');
		$abv_min = $this->getRequest()->getParam('abv_min');
		$abv_max = $this->getRequest()->getParam('abv_max');
		$style_val = $this->getRequest()->getParam('style_val');
		$type_val = $this->getRequest()->getParam('type_val');
		$db = $this->db;
		$select = $db->select()
				->from("beer_recipes", array("COUNT(recipe_id) as kiekis"))
				->where("recipe_ibu >= '".$ibu_min."'")
				->where("recipe_ibu <= '".$ibu_max."'")
				->where("recipe_ebc >= '".$ebc_min."'")
				->where("recipe_ebc <= '".$ebc_max."'")
				->where("recipe_abv >= '".$abv_min."'")
				->where("recipe_abv <= '".$abv_max."'")
				->where("recipe_ebc <= '100'")
				->where("recipe_ibu <= '100'")
				->where("recipe_abv <= '20'")
				->where("recipe_publish = '1'")
				;
		switch($style_val){
			case "all":
				break;
			case "0":
				$select->join("beer_styles", "beer_styles.style_id=beer_recipes.recipe_style");
				$select->where("beer_styles.style_cat = 0");
				break;
			default:
				$select->join("beer_styles", "beer_styles.style_id=beer_recipes.recipe_style");
				$select->join("beer_cats", "beer_cats.cat_id=beer_styles.style_cat");
				$select->where("beer_cats.yeast_cat = '".$style_val."'");
				break;
		}
		switch($type_val){
			case "allgrain":
				$select->where("recipe_type = 'grain'");
				break;
			case "extract":
				$select->where("recipe_type = 'partial'");
				break;
		}
		$result = $db->fetchRow($select);
		echo $result['kiekis'];
	}
	
	public function resultsAction() {
		$db = Zend_Registry::get("db");
		$ibu_min = $this->getRequest()->getParam('ibu_min');
		$ibu_max = $this->getRequest()->getParam('ibu_max');
		$ebc_min = $this->getRequest()->getParam('ebc_min');
		$ebc_max = $this->getRequest()->getParam('ebc_max');
		$abv_min = $this->getRequest()->getParam('abv_min');
		$abv_max = $this->getRequest()->getParam('abv_max');
		$style_val = $this->getRequest()->getParam('style_val');
		$type_val = $this->getRequest()->getParam('type_val');
		
		$this->view->back_url = "/paieska/parametrai/".$ibu_min."/".$ibu_max."/".$ebc_min."/".$ebc_max."/".$abv_min."/".$abv_max."/".$style_val."/".$type_val;

		$select = $db->select();
		$select->from("beer_recipes")
				->join("users", "users.user_id=beer_recipes.brewer_id", array("user_name"))
				->where("beer_recipes.recipe_ibu >= '".$ibu_min."'")
				->where("beer_recipes.recipe_ibu <= '".$ibu_max."'")
				->where("beer_recipes.recipe_ebc >= '".$ebc_min."'")
				->where("beer_recipes.recipe_ebc <= '".$ebc_max."'")
				->where("beer_recipes.recipe_abv >= '".$abv_min."'")
				->where("beer_recipes.recipe_abv <= '".$abv_max."'")
				->where("beer_recipes.recipe_publish = '1'")
				->where("recipe_ebc <= '100'")
				->where("recipe_ibu <= '100'")
				->where("recipe_abv <= '20'")
				->group("beer_recipes.recipe_id")
				->order("beer_recipes.recipe_name");
		switch($style_val){
			case "all":
				$select->joinLeft("beer_styles", "beer_styles.style_id=beer_recipes.recipe_style", array("style_name"));
				break;
			case "0":
				$select->join("beer_styles", "beer_styles.style_id=beer_recipes.recipe_style");
				$select->where("beer_styles.style_cat = 0");
				break;
			default:
				$select->join("beer_styles", "beer_styles.style_id=beer_recipes.recipe_style");
				$select->join("beer_cats", "beer_cats.cat_id=beer_styles.style_cat");
				$select->where("beer_cats.yeast_cat = '".$style_val."'");
				break;
		}
		switch($type_val){
			case "allgrain":
				$select->where("recipe_type = 'grain'");
				break;
			case "extract":
				$select->where("recipe_type = 'partial'");
				break;
		}
		$select_new = clone $select;
		if (!isset($_COOKIE['show_empty_recipes']) || $_COOKIE['show_empty_recipes'] != "1"){
			$select->where("recipe_total_sessions > 0");
		}
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));
		$this->view->content->setItemCountPerPage(15);
		$select_new->where("recipe_total_sessions = 0");
		$result = $db->fetchAll($select_new);
		$this->view->hidden_recipes = sizeof($result);
		$select = $db->select()
				->from("beer_awards")
				->order("posted DESC");
		$result = $db->FetchAll($select);
		$aw = array();
		foreach ($result as $key=>$val){
			$aw[$val['recipe_id']][] = $val;
		}
		$this->view->awards = $aw;
	}
}