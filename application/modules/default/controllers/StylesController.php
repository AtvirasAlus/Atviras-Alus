<?php

class StylesController extends Zend_Controller_Action {

	function init() {
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		$this->show_beta = false;
		if (isset($user_info->user_id) && !empty($user_info->user_id)){
			$db = Zend_Registry::get("db");
			$select = $db->select()
					->from("users_attributes")
					->where("users_attributes.user_id = ?", $user_info->user_id)
					->limit(1);
			$u_atribs= $db->fetchRow($select);
			if ($u_atribs['beta_tester'] == 1) {
				$this->show_beta = true;
			}
		}
		$this->_helper->layout()->setLayout('layoutnew');
	}

	public function stylesAction() {
		$db = Zend_Registry::get("db");
		$style = explode("-", $this->_getParam('style'));
		$this->view->current_style = array();
		$select = $db->select()
				->from("beer_recipes")
				->join("users", "users.user_id = beer_recipes.brewer_id", array("user_name"))
				->joinLeft("beer_styles", "beer_recipes.recipe_style = beer_styles.style_id", array("style_name"));
		$select->where("beer_recipes.recipe_publish = ?", '1');
		$select->where("beer_recipes.recipe_style= ?", $style[0]);
		$select->order("beer_recipes.recipe_created DESC");
		$select->limit(9);
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));
		$this->view->content->setItemCountPerPage(9);
		//$this->_helper->viewRenderer("index");
		if ($style[0]) {
			$select = $db->select()
					->from("beer_styles")
					->joinLeft("beer_styles_description", "beer_styles_description.style_id=beer_styles.style_id", array("style_aroma", "style_mouthfeel", "style_appearance", "style_ingredients", "style_flavor", "style_history"))
					->where("beer_styles.style_id= ?", $style[0]);
			$this->view->current_style = $db->fetchRow($select);
		}
	}

	public function indexAction() {
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("beer_styles", array("style_name", "style_id", "style_cat"))
				->joinLeft("VIEW_public_recipes", "VIEW_public_recipes.recipe_style=beer_styles.style_id", array("count" => "count(VIEW_public_recipes.recipe_id)"))
				->joinLeft("beer_cats", "beer_styles.style_cat=beer_cats.cat_id", array("cat_name", "yeast_cat"))
				->joinLeft("beer_yeast_cats", "beer_cats.yeast_cat=beer_yeast_cats.yeast_cat_id", array("yeast_cat_name"))
				->group("beer_styles.style_id")
				->order("yeast_cat")
				->order("style_cat")
				->order("style_name");
		$this->view->beer_styles = $db->fetchAll($select);
		if (isset($_GET["style_id"])) {
			$select = $db->select()
					->from("beer_styles", array("style_name", "style_id"))
					->joinLeft("beer_styles_description", "beer_styles_description.style_id=beer_styles.style_id", array("style_aroma", "style_mouthfeel", "style_appearance", "style_ingredients", "style_flavor", "style_history"))
					->where("beer_styles.style_id= ?", ($_GET["style_id"]));
			$this->view->current_style = $db->fetchRow($select);
		}
	}

}