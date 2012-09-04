<?php

class FoodController extends Zend_Controller_Action {

	public function init() {
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
		
	}

	public function indexAction() {
		
	}
	
	public function itemAction(){
		$db = Zend_Registry::get('db');
		$storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $storage->read();
		$me = -1;
		if (isset($this->view->user_info->user_id) && !empty($this->view->user_info->user_id)) {
			$me = $this->view->user_info->user_id;
		}
		$item_id = $this->getRequest()->getParam("item");
		if (empty($item_id)){
			$item_id = 0;
		} else {
			$item_id = explode("-", $item_id);
			$item_id = $item_id[0];
		}
		$this->view->item_id = $item_id;
		
		$select = $db->select()
				->from("food_items", array("*", "DATE_FORMAT(posted, '%Y-%m-%d') as postedf", "DATE_FORMAT(modified, '%Y-%m-%d') as modifiedf"))
				->join("users", "users.user_id = food_items.author_id", array("user_name"))
				->join("food_categories AS c1", "c1.id = food_items.cat_id", array("title as cat_title"))
				->join("food_categories AS c2", "c2.id = food_items.parent_cat_id", array("title as parent_cat_title"))
				->where("food_items.id = ?", array($item_id));
		$item = $db->FetchRow($select);
		$item['thumb'] = false;
		if ($item['image1'] != ""){
			$item['thumb'] = "image1";
		} else {
			if ($item['image2'] != ""){
				$item['thumb'] = "image2";
			} else {
				if ($item['image2'] != ""){
					$item['thumb'] = "image2";
				}
			}
		}
		$this->view->item = $item;
		
		$select = $db->select()
				->from("food_ingridients")
				->where("recipe_id = ?", array($item_id));
		$ingridients = $db->FetchAll($select);
		$this->view->ingridients = $ingridients;
		
		$this->view->recipe_votes = array("total" => $this->getVotes($item_id), "user_vote" => $this->getUserVotes($item_id, $me));
		
		$select = $db->select()
				->from("food_styles")
				->join("beer_styles", "beer_styles.style_id = food_styles.style_id", "style_name")
				->where("recipe_id = ?", array($item_id))
				->order("style_name ASC");
		$styles = $db->FetchAll($select);
		$this->view->styles= $styles;
		
		$arr = array();
		foreach($styles as $st){
			$arr[] = $st['style_id'];
		}
		if (sizeof($arr) > 0){
			$this->view->recipes = array();
			$select = $db->select()
					->from("cache_fav_recipes")
					->where("recipe_style IN (".implode(",", $arr).")")
					->order("total DESC")
					->limit(6);
			$frecipes = $db->FetchAll($select);
			foreach($frecipes as $recipe){
				$select = $db->select()
						->from("beer_recipes")
						->join("users", "users.user_id = beer_recipes.brewer_id", array("user_name"))
						->joinLeft("beer_styles", "beer_recipes.recipe_style = beer_styles.style_id", array("style_name"));
				$select->where("beer_recipes.recipe_id = ?", $recipe['recipe_id']);
				$rec = $db->FetchRow($select);
				$this->view->recipes[] = $rec;
			}
		}		
	}

	public function getVotes($recipe_id = 0) {
		$db = Zend_Registry::get('db');
		$select = $db->select();
		$select->from("food_favorites", array("count" => "count(*)"))
				->where("recipe_id =?", $recipe_id);
		$fv = $db->fetchRow($select);
		return $fv["count"];
	}

	public function getUserVotes($recipe_id = 0, $user_id = 0) {
		$db = Zend_Registry::get('db');
		$select = $db->select();
		$select->from("food_favorites", array("count" => "count(*)"))
				->where("recipe_id =?", $recipe_id)
				->where("user_id =?", $user_id);
		$fv = $db->fetchRow($select);
		return $fv["count"];
	}

	public function voteAction() {
		$db = Zend_Registry::get('db');
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$storage = new Zend_Auth_Storage_Session();
		$u = $storage->read();
		if (isset($u->user_name)) {
			if (isset($_POST)) {
				$db->delete("food_favorites", "recipe_id = " . $_POST['id'] . ' and user_id = ' . $u->user_id);
				switch ($_POST['action']) {
					case "vote_up":
						$db->insert("food_favorites", array("recipe_id" => $_POST['id'], "user_id" => $u->user_id));
						break;
				}
				Entities_Events::trigger("vote_food", array("recipe_id" => $_POST['id']));
				print Zend_Json::encode(array("status" => 0, "data" => array("votes" => $this->getVotes($_POST['id']))));
			}
		} else {
			print Zend_Json::encode(array("status" => 1, "errors" => array(array("message" => "Neregistruotas nautotojas", "type" => "authentication"))));
		}
	}
	
	public function listAction() {
		$db = Zend_Registry::get('db');
		$storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $storage->read();
		$me = -1;
		if (isset($this->view->user_info->user_id) && !empty($this->view->user_info->user_id)) {
			$me = $this->view->user_info->user_id;
		}
		$category = $this->getRequest()->getParam("category");
		if (empty($category)){
			$category = 0;
		} else {
			$category = explode("-", $category);
			$category = $category[0];
		}
		$this->view->cat_id = $category;
		if ($category != 0){
			$select = $db->select()
					->from("food_categories")
					->where("id = ?", array($category));
			$current = $db->FetchRow($select);
			$this->view->current = $current;
			if ($current['parent_id'] != 0){
				$select = $db->select()
						->from("food_categories")
						->where("id = ?", array($current['parent_id']));
				$parent = $db->FetchRow($select);
				$this->view->parent = $parent;
			}
		}
		$select = $db->select()
				->from("food_categories")
				->where("parent_id = ?", array($category))
				->order("title ASC");
		$sublist = $db->FetchAll($select);
		$this->view->subcategories = $sublist;
		
		$select = $db->select()
				->from("food_items", array("*", "DATE_FORMAT(posted, '%Y-%m-%d') as postedf"))
				->join("users", "food_items.author_id = users.user_id", array("user_name"))
				->where("food_items.cat_id = ?", array($category))
				->order("food_items.posted DESC");
		$items = $db->FetchAll($select);
		$this->view->items = $items;
		
	}

}