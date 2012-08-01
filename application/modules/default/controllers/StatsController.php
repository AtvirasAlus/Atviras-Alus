<?php

class StatsController extends Zend_Controller_Action {

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

	public function indexAction() {
		$db = Zend_Registry::get('db');
		$select = $db->select();
		$id = isset($_GET['id']) ? $_GET['id'] : "";
		switch ($id) {

			case "sessions":
				$select->from("VIEW_sessions_stats", array("day" => "DATE(CONCAT(year,'.',month,'.1'))", "total" => "COALESCE(liters_total)", "avg" => "(liters_total/brewer_count)", "count" => "brewer_count"))
						->group("day");

				$this->view->sessions_count = Zend_Json::encode($db->fetchAll($select));

				break;
			case "users":
				$select->from("users", array("total" => "count(user_id)", "day" => "DATE(Concat(year(user_created),'.',month(user_created),'.1'))"))
						->group("day");
				$this->view->user_count = Zend_Json::encode($db->fetchAll($select));
				break;
			case "locations":
				$select = $db->select()
						->from("users", array("count" => "count(users.user_id)"))
						->joinLeft("users_attributes", "users_attributes.user_id=users.user_id", array("user_location"))
						->where("users.user_active = ?", '1')
						->group("user_location");

				$this->view->location_count = Zend_Json::encode($db->fetchAll($select));
				$this->_helper->viewRenderer("barchart");

				break;
			case "cats":
				$select->from("VIEW_public_recipes", array("total" => "count(recipe_id)"))
						->joinLeft("beer_styles", "VIEW_public_recipes.recipe_style=beer_styles.style_id", array())
						->joinLeft("beer_cats", "beer_cats.cat_id=beer_styles.style_cat", array("cat_name"))
						->where("VIEW_public_recipes.recipe_style > 0")
						->group("beer_styles.style_cat")
						->order("total DESC");


				$this->view->cats = Zend_Json::encode($db->fetchAll($select));
				$this->_helper->viewRenderer("piechart");
				break;
			case "styles":
				$select->from("beer_recipes", array("total" => "count(recipe_id)"))
						->joinLeft("beer_styles", "beer_recipes.recipe_style=beer_styles.style_id", array("style_name"))
						->where("beer_recipes.recipe_publish = 1")
						->where("beer_recipes.recipe_style > 0")
						->group("style_id")
						->order("total DESC")
						->limit(30);
				$this->view->styles = Zend_Json::encode($db->fetchAll($select));
				$this->_helper->viewRenderer("piechart");
				break;
			case "abv":
				$selectA = $db->select()
						->from('VIEW_public_recipes', array('count' => 'count(recipe_abv)', 'label' => "CONCAT('0 - 3,5%')"))
						->where('recipe_abv >= 0')
						->where('recipe_abv <= 3.5');

				$selectB = $db->select()
						->from('VIEW_public_recipes', array('count' => 'count(recipe_abv)', 'label' => "CONCAT('3,6- 4,6%')"))
						->where('recipe_abv >= 3.6')
						->where('recipe_abv <= 4.6');
				$selectC = $db->select()
						->from('VIEW_public_recipes', array('count' => 'count(recipe_abv)', 'label' => "CONCAT('4,7- 5,7%')"))
						->where('recipe_abv >= 4.7')
						->where('recipe_abv <= 5.7');
				$selectD = $db->select()
						->from('VIEW_public_recipes', array('count' => 'count(recipe_abv)', 'label' => "CONCAT('5,8 - 6,9%')"))
						->where('recipe_abv >= 5.8')
						->where('recipe_abv <= 6.9');
				$selectE = $db->select()
						->from('VIEW_public_recipes', array('count' => 'count(recipe_abv)', 'label' => "CONCAT('7,0- 9,0%')"))
						->where('recipe_abv >= 7')
						->where('recipe_abv <= 9');
				$selectF = $db->select()
						->from('VIEW_public_recipes', array('count' => 'count(recipe_abv)', 'label' => "CONCAT('> 9,0%')"))
						->where('recipe_abv >9');


				$select = $db->select()
						->union(array($selectA, $selectB, $selectC, $selectD, $selectE, $selectF));
				$this->view->abv = Zend_Json::encode($db->fetchAll($select));

				$this->_helper->viewRenderer("piechart");
				break;
			default:
				$select->from("beer_recipes", array("total" => "count(recipe_id)", "day" => "DATE(Concat(year(recipe_created),'.',month(recipe_created),'.1'))"))
						->group("day");
				$this->view->recipes_count = Zend_Json::encode($db->fetchAll($select));
				break;
		}
	}

	public function sessionsAction() {
		$db = Zend_Registry::get('db');
		$select = $db->select();
		/* $select->from("@days",array("day"=>"DATE"))
		  ->joinLeft("beer_brew_sessions","`@days`.DATE=beer_brew_sessions.session_primarydate",array("total"=>"COALESCE(sum(session_size),0)","avg"=>"COALESCE(sum(session_size)/count(distinct(session_brewer)),0)","count"=>"count(distinct(session_brewer))"))
		  ->where("`@days`.DATE >= ?",new Zend_Db_Expr("DATE(NOW())-(31*6)"))
		  ->where("`@days`.DATE <= ?",new Zend_Db_Expr("NOW()"))
		  ->group("day");

		  //$this->view->sessions_count= Zend_Json::encode($db->fetchAll($select)); */
		$select->from("VIEW_sessions_stats", array("day" => "DATE(CONCAT(year,'.',month,'.1'))", "total" => "COALESCE(liters_total)", "avg" => "(liters_total/brewer_count)", "count" => "brewer_count"))
				->group("day");
		$this->view->sessions_count = Zend_Json::encode($db->fetchAll($select));
	}

	public function mysessionsAction() {
		$storage = new Zend_Auth_Storage_Session();
		$this->user = $storage->read();
		$user_id = false;
		if (isset($this->user->user_id)) {
			$user_id = $this->user->user_id;
		}
		if (isset($_GET['uid'])) {
			$user_id = $_GET['uid'];
		}
		if ($user_id) {

			$db = Zend_Registry::get('db');
			$select = $db->select();

			$select->from("beer_brew_sessions")
					->joinLeft("beer_recipes", "beer_brew_sessions.session_recipe =beer_recipes.recipe_id", array("recipe_name"))
					->where("session_brewer =?", $user_id)
					->group("session_primarydate");
			$this->view->sessions_count = Zend_Json::encode($db->fetchAll($select));
		}
	}

}