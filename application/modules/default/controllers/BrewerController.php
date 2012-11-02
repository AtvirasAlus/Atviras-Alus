<?php

class BrewerController extends Zend_Controller_Action {

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
	public function favoritesAction(){
		$db = Zend_Registry::get('db');
		$storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $storage->read();
		$u = $this->view->user_info;
		if (isset($u->user_id) && !empty($u->user_id)){
			$this->view->pleaselogin = false;
			$me = $u->user_id;
			$select = $db->select()
					->from("beer_recipes_favorites")
					->join("beer_recipes", "beer_recipes_favorites.recipe_id=beer_recipes.recipe_id", array("recipe_name"))
					->join("beer_styles", "beer_recipes.recipe_style=beer_styles.style_id", array("style_name", "style_id"))
					->where("beer_recipes_favorites.user_id = ?", $me)
					->where("beer_recipes.brewer_id = ?", $me)
					->order("beer_recipes_favorites.favorite_date DESC");
			$result = $db->fetchAll($select);
			$this->view->myfavorites = $result;
			$select = $db->select()
					->from("beer_recipes_favorites")
					->join("beer_recipes", "beer_recipes_favorites.recipe_id=beer_recipes.recipe_id", array("recipe_name"))
					->join("beer_styles", "beer_recipes.recipe_style=beer_styles.style_id", array("style_name", "style_id"))
					->join("users", "beer_recipes.brewer_id=users.user_id", array("user_name as brewer_name", "user_id as brewer_id"))
					->where("beer_recipes_favorites.user_id = ?", $me)
					->where("beer_recipes.brewer_id != ?", $me)
					->order("beer_recipes_favorites.favorite_date DESC");
			$result = $db->fetchAll($select);
			$this->view->favorites = $result;
		} else {
			$this->view->pleaselogin = true;
		}
		
	}

	public function infoAction() {

		$db = Zend_Registry::get('db');
		$this->view->user_info = array("total_sessions" => 0, "total_brewed" => 0, "total_recipes" => 0, "user_lastlogin" => 0, "user_created" => 0, "user_name" => '');
		if ($this->_getParam('brewer') > 0) {
			$brewer = $this->_getParam('brewer');
			$select = $db->select()
					->from("users")
					->joinLeft("users_attributes", "users_attributes.user_id=users.user_id")
					->where("users.user_active = ?", '1')
					->where("users.user_id = ?", $brewer);
			$this->view->user_info = $db->fetchRow($select);
			$this->view->user_info['user_id'] = $brewer;
			if ($this->view->user_info) {
				$select = $db->select()
						->from("beer_recipes", array("count" => "count(*)"))
						->where("beer_recipes.recipe_publish = ?", '1')
						->where("beer_recipes.brewer_id= ?", $brewer);
				if ($row = $db->fetchRow($select)) {
					$this->view->user_info["total_recipes"] = $row["count"];
				}
				$select = $db->select()
						->from("beer_brew_sessions", array("count" => "count(*)", "total" => "COALESCE(sum(session_size),0)"))
						->where("session_brewer =?", $brewer);
				if ($row = $db->fetchRow($select)) {
					$this->view->user_info["total_brewed"] = $row["total"];
					$this->view->user_info["total_sessions"] = $row["count"];
				}
				$select = $db->select()
						->from("beer_recipes")
						->where("beer_recipes.recipe_publish = ?", '1')
						->where("beer_recipes.brewer_id= ?", $brewer);
				$this->view->user_info["recipes"] = array();
				if ($rows = $db->fetchAll($select)) {
					$this->view->user_info["recipes"] = $rows;
				}
				$select = $db->select()
						->from("beer_recipes_comments", array("comment_text", "comment_date"))
						->join("beer_recipes", "comment_recipe=recipe_id", array("recipe_name", "recipe_id"))
						->where("comment_brewer = ?", $brewer)
						->order("comment_date desc")
						->limit(10);
				$this->view->user_info["recipes_comments"] = array();
				if ($rows = $db->fetchAll($select)) {
					$this->view->user_info["recipes_comments"] = $rows;
				}
				$select = $db->select()
						->from("beer_recipes_tags", array("weight" => "count(tag_text)", "tag_text" => "tag_text"))
						->where("tag_owner = ?", $brewer)
						->group("tag_text")
						->order("weight DESC")
						->limit(100);

				$this->view->user_info["tags"] = array();
				if ($rows = $db->fetchAll($select)) {
					$this->view->user_info["tags"] = $rows;
				}
				$select = $db->select()
						->from("beer_brew_sessions", array("session_caskingdate", "session_secondarydate", "session_primarydate", "session_recipe", "session_fg", "session_og", "session_size", "session_brewer", "session_name", "session_id", 'session_comments' => 'LEFT( session_comments, LOCATE( " ", session_comments, 30 ) )'))
						->join("users", "beer_brew_sessions.session_brewer = users.user_id")
						->joinLeft("beer_recipes", "beer_brew_sessions.session_recipe=beer_recipes.recipe_id", array("recipe_id", "recipe_name", "recipe_publish"))
						->joinLeft("users AS recu", "beer_recipes.brewer_id=recu.user_id", array("user_id AS recu_id", "user_name AS recu_name"))
						->where("beer_brew_sessions.session_brewer =?", $brewer)
						->order("session_primarydate DESC");
				$this->view->user_info['sessions'] = array();
				$this->view->user_info['sessions_size'] = 0;
				if ($rows = $db->fetchAll($select)) {
					$this->view->user_info["sessions_size"] = sizeof($rows);
					$this->view->user_info["sessions"] = array_slice($rows, 0, 10);
				}
			}
		}
	}

	public function sessionsAction() {

		$db = Zend_Registry::get('db');
		$this->view->user_info = array("total_sessions" => 0, "total_brewed" => 0, "total_recipes" => 0, "user_lastlogin" => 0, "user_created" => 0, "user_name" => '');
		if ($this->_getParam('brewer') > 0) {
			$brewer = $this->_getParam('brewer');
			$select = $db->select()
					->from("users")
					->where("users.user_active = ?", '1')
					->where("users.user_id = ?", $brewer);
			$this->view->user_info = $db->fetchRow($select);
			if ($this->view->user_info) {
				$select = $db->select()
						->from("beer_brew_sessions", array("session_caskingdate", "session_secondarydate", "session_primarydate", "session_recipe", "session_fg", "session_og", "session_size", "session_brewer", "session_name", "session_id", 'session_comments' => 'LEFT( session_comments, LOCATE( " ", session_comments, 30 ) )'))
						->join("users", "beer_brew_sessions.session_brewer = users.user_id")
						->joinLeft("beer_recipes", "beer_brew_sessions.session_recipe=beer_recipes.recipe_id", array("recipe_id", "recipe_name", "recipe_publish"))
						->joinLeft("users AS recu", "beer_recipes.brewer_id=recu.user_id", array("user_id AS recu_id", "user_name AS recu_name"))
						->where("beer_brew_sessions.session_brewer =?", $brewer)
						->order("session_primarydate DESC");
				$this->view->user_info['sessions'] = array();
				$this->view->user_info['sessions_size'] = 0;
				if ($rows = $db->fetchAll($select)) {
					$this->view->user_info["sessions_size"] = sizeof($rows);
					$this->view->user_info["sessions"] = $rows;
				}
			}
		}
	}

	public function listAction() {
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("users", array("user_name", "user_id", "user_email"))
				->joinLeft("VIEW_public_recipes", "VIEW_public_recipes.brewer_id=users.user_id", array("count" => "count(VIEW_public_recipes.recipe_id)"))
				->where("users.user_active = ?", '1')
				->group("users.user_id")
				->order("user_lastlogin DESC")
				->order("count DESC")
				->order("recipe_created DESC")
				->order("user_name ASC");
		$search = $this->_getParam("brewer_search");
		$this->view->search = "";
		if (isset($search) && !empty ($search)){
			$this->view->search = $search;
			$select->where("users.user_name LIKE '%".$search."%'");
		}
		$result = $db->FetchAll($select);
		if (sizeof($result) == 1){
			header("location: /brewers/".$result[0]['user_id']);
		}
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));
		$this->view->content->setItemCountPerPage(40);
	}

	public function profileAction() {
		$storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $storage->read();
		$u = $this->view->user_info;
		$this->view->changePasswordForm = new Form_Profile();

		$this->view->userAttributesForm = new Form_Attributes();
		$this->view->errors = array();

		if ($this->getRequest()->isPost()) {
			$action = "";

			if (isset($_POST['action'])) {

				$action = $_POST['action'];
				if ($action == "attributes") {
					$form = $this->view->userAttributesForm;
					$form_valid = $form->isValid($_POST);
				} else if ($action == "groups") {
					$form_valid = true;
				}
			} else {
				$action = "psw";
				$form = $this->view->changePasswordForm;
				$form_valid = $form->isValid($_POST);
			}
			if ($form_valid) {

				switch ($action) {
					case "psw":


						if ($this->getRequest()->getPost('user_password') == $this->getRequest()->getPost('user_password_repeat')) {
							if ($this->updatePassword($u->user_id, $this->getRequest()->getPost('user_password_old'), $this->getRequest()->getPost('user_password'))) {
								$this->view->success = "Slaptažodis pakeistas";
							} else {
								$this->view->errors[] = array("type" => "system", "message" => "Neteisingas slaptažodis");
							}
						} else {
							$this->view->errors[] = array("type" => "system", "message" => "Slaptažodžiai nesutampa");
						}
						break;
					case "groups":
						$user = new Entities_User($u->user_id);
						if (isset($_POST['group'])) {
							$user->updateGroups($_POST['group']);
						} else {
							$user->updateGroups($u->user_id, array());
						}
						break;
					case "attributes":
						$location = $_POST['user_location'];
						if ($_POST['use_other_location'] == '1') {
							$location = $_POST['user_other_location'];
						}

						$user = new Entities_User($u->user_id);
						if ($user->updateAttributes(array('user_location' => $location, 'user_about' => $_POST['user_about'], 'user_mail_comments' => $_POST['user_mail_comments'], 'beta_tester' => $_POST['beta_tester']))) {
							$this->_redirect("/brewer/profile");
						} else {
							$this->view->errors[] = array("type" => "system", "message" => "Išsaugoti informacijos nepavyko");
						}
						break;
				}
			} else {
				$err_codes = new Entities_FormErrors();
				foreach ($form->getErrors() as $key => $error) {
					if (count($error) > 0) {
						$this->view->errors[] = array("type" => "form", "message" => $form->getElement($key)->getLabel() . " - " . $err_codes->getError($error[0]));
					}
				}
			}
		}
		$user = new Entities_User($u->user_id);

		$this->view->user_attributes = $user->getAttributes();
		$this->view->user_groups = $user->getGroups();
	}

	private function updatePassword($user_id, $old_password, $new_password) {

		$db = Zend_Registry::get('db');
		$where = array();
		$where[] = $db->quoteInto('user_id = ?', $user_id);
		$where[] = $db->quoteInto('user_password = ?', md5($old_password));
		return $db->update("users", array('user_password' => md5($new_password)), $where);
	}

	public function recipesAction() {
		$storage = new Zend_Auth_Storage_Session();
		$u = $storage->read();
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("beer_awards")
				->order("posted DESC");
		$result = $db->FetchAll($select);
		$aw = array();
		foreach ($result as $key=>$val){
			$aw[$val['recipe_id']][] = $val;
		}
		$this->view->awards = $aw;
		if ($this->_getParam('brewer') > 0) {
			$brewer = $this->_getParam('brewer');
			//$this->_helper->viewRenderer->render("../recipes/index");
			$this->_helper->viewRenderer("public");
			$select = $db->select()
					->from("beer_recipes", array("count" => "count(*)"))
					->where("beer_recipes.recipe_publish = ?", '1')
					->where("beer_recipes.brewer_id= ?", $brewer);
			$total = $db->fetchRow($select);
		} else {
			if (isset($u->user_id)) {
				$brewer = $u->user_id;
				$select = $db->select()
						->from("beer_recipes", array("count" => "count(*)"))
						->where("beer_recipes.brewer_id= ?", $brewer);
				$total = $db->fetchRow($select);
			}
		}
		$select = $db->select()
				->from("users")
				->where("user_id = ?", $brewer);
		$brewer = $db->fetchRow($select);
		if (isset($brewer)) {
			$select = $db->select()
					->from("beer_recipes")
					->join("users", "users.user_id = beer_recipes.brewer_id", array("user_name"))
					->joinLeft("beer_styles", "beer_recipes.recipe_style = beer_styles.style_id", array("style_name"));
			if ($this->_getParam('brewer') > 0) {
				$select->where("beer_recipes.recipe_publish = ?", '1');
			}
			$select->where("beer_recipes.brewer_id= ?", $brewer["user_id"]);
			$select->order("beer_recipes.recipe_created DESC");

			//$this->view->recipes=$db->fetchAll($select);
			$adapter = new Zend_Paginator_Adapter_DbSelect($select);
			$this->view->content = new Zend_Paginator($adapter);
			$this->view->content->setCurrentPageNumber($this->_getParam('page'));
			$this->view->content->setItemCountPerPage(15);
			$this->view->brewer = $brewer;
			$this->view->brewer["total"] = $total["count"];
		}
	}

}