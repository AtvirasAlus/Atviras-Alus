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
					"MAX(recipe_abv) as max_abv", "MIN(recipe_abv) as min_abv",
					"COUNT(recipe_id) as kiekis"
				))
				->where("recipe_publish = '1'");
		$result = $db->fetchRow($select);
		$this->view->params = $result;
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
		$db = $this->db;
		$select = $db->select()
				->from("beer_recipes", array("COUNT(recipe_id) as kiekis"))
				->where("recipe_ibu >= '".$ibu_min."'")
				->where("recipe_ibu <= '".$ibu_max."'")
				->where("recipe_ebc >= '".$ebc_min."'")
				->where("recipe_ebc <= '".$ebc_max."'")
				->where("recipe_abv >= '".$abv_min."'")
				->where("recipe_abv <= '".$abv_max."'")
				->where("recipe_publish = '1'")
				;
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

		$select = $db->select();
		$select->from("beer_recipes")
				->join("users", "users.user_id=beer_recipes.brewer_id", array("user_name"))
				->joinLeft("beer_styles", "beer_styles.style_id=beer_recipes.recipe_style", array("style_name"))
				->where("beer_recipes.recipe_ibu >= '".$ibu_min."'")
				->where("beer_recipes.recipe_ibu <= '".$ibu_max."'")
				->where("beer_recipes.recipe_ebc >= '".$ebc_min."'")
				->where("beer_recipes.recipe_ebc <= '".$ebc_max."'")
				->where("beer_recipes.recipe_abv >= '".$abv_min."'")
				->where("beer_recipes.recipe_abv <= '".$abv_max."'")
				->where("beer_recipes.recipe_publish = '1'")
				->group("beer_recipes.recipe_id")
				->order("beer_recipes.recipe_name");
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));
		$this->view->content->setItemCountPerPage(15);
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