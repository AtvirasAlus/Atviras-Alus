<?php
class ApiController extends Zend_Controller_Action {
	public function init() {
	}

	public function loginAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$email = trim($this->_getParam('email'));
		$password = trim($this->_getParam('password'));
		$response = array();
		$response['status'] = "0";
		if (!empty($email) && !empty($password)){
			$select = $db->select()
					->from("users")
					->where("user_email = ?", $email)
					->where("user_password = ?", md5($password))
					->limit(1);
			$result = $db->FetchAll($select);
			if (sizeof($result) > 0){
				$response['status'] = 1;
				$response['user_id'] = $result[0]['user_id'];
				$response['token'] = $result[0]['api_token'];
			}
		}
		if (isset($_GET['testmode'])){
			echo "<pre>";print_r($response);exit;
		} else {
			echo $_GET['callback'] . '(' . json_encode($response) . ")";
		}
	}

	public function validateAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$token = trim($this->_getParam('token'));
		$response = array();
		$response['status'] = "0";
		if (!empty($token)){
			$select = $db->select()
					->from("users")
					->where("api_token = ?", $token)
					->limit(1);
			$result = $db->FetchAll($select);
			if (sizeof($result) > 0){
				$response['status'] = 1;
				$response['user_id'] = $result[0]['user_id'];
				$response['token'] = $result[0]['api_token'];
			}
		}
		if (isset($_GET['testmode'])){
			echo "<pre>";print_r($response);exit;
		} else {
			echo $_GET['callback'] . '(' . json_encode($response) . ")";
		}
	}

	public function getuserinfoAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$token = trim($this->_getParam('token'));
		$response = array();
		$response['status'] = "0";
		if (!empty($token)){
			$select = $db->select()
					->from("users")
					->where("api_token = ?", $token)
					->limit(1);
			$result = $db->FetchAll($select);
			if (sizeof($result) > 0){
				$response['status'] = 1;
				$response['userinfo'] = $result[0];
			}
		}
		if (isset($_GET['testmode'])){
			echo "<pre>";print_r($response);exit;
		} else {
			echo $_GET['callback'] . '(' . json_encode($response) . ")";
		}
	}
	
	public function getuserrecipesAction(){
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$response = array();
		$response['status'] = "0";
		$db = Zend_Registry::get("db");
		$token = trim($this->_getParam('token'));
		$uid = $this->getuserid($token);
		if ($uid === false){
			$response['status'] = 999;
			echo json_encode($response);
			exit;
		}
		$select = $db->select()
				->from("beer_recipes")
				->where("brewer_id = ?", $uid)
				->join("beer_styles", "beer_styles.style_id = beer_recipes.recipe_style", array("style_name"))
				->join("users", "users.user_id = beer_recipes.brewer_id", array("user_name"))
				->order("recipe_name ASC");
		$result = $db->FetchAll($select);
		$response['status'] = "1";
		$response['count'] = sizeof($result);
		$response['items'] = $result;
		if (isset($_GET['testmode'])){
			echo "<pre>";print_r($response);exit;
		} else {
			echo $_GET['callback'] . '(' . json_encode($response) . ")";
		}
	}
	
	private function getuserid($token){
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("users")
				->where("api_token = ?", $token);
		$result = $db->FetchRow($select);
		if ($result === false){
			return false;
		} else {
			return $result['user_id'];
		}
	}

	public function getuserrecipeAction(){
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$response = array();
		$response['status'] = "0";
		$db = Zend_Registry::get("db");
		$recipe = trim($this->_getParam('recipe'));
		$token = trim($this->_getParam('token'));
		$uid = $this->getuserid($token);
		if ($uid === false){
			$response['status'] = 999;
			echo $_GET['callback'] . '(' . json_encode($response) . ")";
			exit;
		}
		if (!empty($recipe)){
			$select = $db->select()
					->from("beer_recipes")
					->where("brewer_id = ?", $uid)
					->where("recipe_id = ?", $recipe)
					->join("beer_styles", "beer_styles.style_id = beer_recipes.recipe_style", array("style_name"))
					->join("users", "users.user_id = beer_recipes.brewer_id", array("user_name"))
					->order("recipe_id ASC");
			$result = $db->FetchRow($select);
			if ($result !== false){
				$response['status'] = "1";
				$response['item'] = $result;
				$select = $db->select()
						->from("beer_awards")
						->where("recipe_id = ?", $recipe)
						->order("icon ASC");
				$response['awards'] = $db->FetchAll($select);
				$select = $db->select()
						->from("beer_brew_sessions")
						->join("users", "users.user_id=beer_brew_sessions.session_brewer", array("user_name", "user_email"))
						->where("session_recipe = ?", $recipe)
						->order("session_primarydate DESC");
				$response['sessions'] = $db->FetchAll($select);
				$select = $db->select()
						->from("beer_images")
						->join("users", "users.user_id=beer_images.user_id", array("user_name", "user_email"))
						->where("recipe_id = ?", $recipe)
						->order("posted DESC");
				$response['images'] = $db->FetchAll($select);
				$select = $db->select()
						->from("beer_recipes_comments")
						->join("users", "users.user_id=beer_recipes_comments.comment_brewer", array("user_name", "user_email"))
						->where("comment_recipe = ?", $recipe)
						->order("comment_date ASC");
				$response['comments'] = $db->FetchAll($select);
				$select = $db->select()
						->from("beer_recipes_favorites")
						->join("users", "users.user_id=beer_recipes_favorites.user_id", array("user_name", "user_email"))
						->where("recipe_id = ?", $recipe)
						->order("favorite_date ASC");
				$response['favorites'] = $db->FetchAll($select);
				$select = $db->select()
						->from("beer_recipes_hops")
						->where("recipe_id = ?", $recipe)
						->order("hop_time DESC")
						->order("hop_name ASC");
				$response['hops'] = $db->FetchAll($select);
				$select = $db->select()
						->from("beer_recipes_malt")
						->where("recipe_id = ?", $recipe)
						->order("malt_weight DESC")
						->order("malt_name ASC");
				$response['malts'] = $db->FetchAll($select);
				$select = $db->select()
						->from("beer_recipes_yeast")
						->where("recipe_id = ?", $recipe)
						->order("yeast_weight DESC")
						->order("yeast_name ASC");
				$response['yeasts'] = $db->FetchAll($select);
			}
		}
		if (isset($_GET['testmode'])){
			echo "<pre>";print_r($response);exit;
		} else {
			echo $_GET['callback'] . '(' . json_encode($response) . ")";
		}
	}

	// API 2.0
	
	public function getalluserrecipesAction(){
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$response = array();
		$response['status'] = "0";
		$db = Zend_Registry::get("db");
		$token = trim($this->_getParam('token'));
		$uid = $this->getuserid($token);
		if ($uid === false){
			$response['status'] = 999;
			echo json_encode($response);
			exit;
		}
		$response['status'] = "1";
		$select = $db->select()
				->from("beer_recipes")
				->where("brewer_id = ?", $uid)
				->join("beer_styles", "beer_styles.style_id = beer_recipes.recipe_style", array("style_name"))
				->join("users", "users.user_id = beer_recipes.brewer_id", array("user_name"))
				->order("recipe_name ASC")
				->limit(1);
		$result = $db->FetchAll($select);
		$response['recipes_count'] = sizeof($result);
		$response['recipes'] = $result;
		$rids = array();
		foreach($result as $key=>$val){
			$rids[] = $val['recipe_id'];
		}
		$rsize = sizeof($rids);
		$rids = implode(", ", $rids);
		if ($rsize > 0){
			$select = $db->select()
					->from("beer_awards")
					->where("recipe_id IN (".$rids.")");
			$result = $db->FetchAll($select);
			$response['awards_count'] = sizeof($result);
			$response['awards'] = $result;
			$select = $db->select()
					->from("beer_recipes_hops")
					->where("recipe_id IN (".$rids.")");
			$result = $db->FetchAll($select);
			$response['hops_count'] = sizeof($result);
			$response['hops'] = $result;
			$select = $db->select()
					->from("beer_recipes_malt")
					->where("recipe_id IN (".$rids.")");
			$result = $db->FetchAll($select);
			$response['malts_count'] = sizeof($result);
			$response['malts'] = $result;
			$select = $db->select()
					->from("beer_recipes_yeast")
					->where("recipe_id IN (".$rids.")");
			$result = $db->FetchAll($select);
			$response['yeasts_count'] = sizeof($result);
			$response['yeasts'] = $result;
		}
		if (isset($_GET['testmode'])){
			echo "<pre>";print_r($response);exit;
		} else {
			echo $_GET['callback'] . '(' . json_encode($response) . ")";
		}
	}
	
	public function getalluserfavoriterecipesAction(){
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$response = array();
		$response['status'] = "0";
		$db = Zend_Registry::get("db");
		$token = trim($this->_getParam('token'));
		$uid = $this->getuserid($token);
		if ($uid === false){
			$response['status'] = 999;
			echo json_encode($response);
			exit;
		}
		$response['status'] = "1";
		$select = $db->select()
				->from("beer_recipes_favorites", array())
				->join("beer_recipes", "beer_recipes.recipe_id=beer_recipes_favorites.recipe_id")
				->where("beer_recipes_favorites.user_id = ?", $uid)
				->join("beer_styles", "beer_styles.style_id = beer_recipes.recipe_style", array("style_name"))
				->join("users", "users.user_id = beer_recipes.brewer_id", array("user_name"))
				->order("beer_recipes.recipe_name ASC");
		$result = $db->FetchAll($select);
		$response['recipes_count'] = sizeof($result);
		$response['recipes'] = $result;
		$rids = array();
		foreach($result as $key=>$val){
			$rids[] = $val['recipe_id'];
		}
		$rsize = sizeof($rids);
		$rids = implode(", ", $rids);
		if ($rsize > 0){
			$select = $db->select()
					->from("beer_awards")
					->where("recipe_id IN (".$rids.")");
			$result = $db->FetchAll($select);
			$response['awards_count'] = sizeof($result);
			$response['awards'] = $result;
			$select = $db->select()
					->from("beer_recipes_hops")
					->where("recipe_id IN (".$rids.")");
			$result = $db->FetchAll($select);
			$response['hops_count'] = sizeof($result);
			$response['hops'] = $result;
			$select = $db->select()
					->from("beer_recipes_malt")
					->where("recipe_id IN (".$rids.")");
			$result = $db->FetchAll($select);
			$response['malts_count'] = sizeof($result);
			$response['malts'] = $result;
			$select = $db->select()
					->from("beer_recipes_yeast")
					->where("recipe_id IN (".$rids.")");
			$result = $db->FetchAll($select);
			$response['yeasts_count'] = sizeof($result);
			$response['yeasts'] = $result;
		}
		if (isset($_GET['testmode'])){
			echo "<pre>";print_r($response);exit;
		} else {
			echo $_GET['callback'] . '(' . json_encode($response) . ")";
		}
	}
	public function getalluserrecipecommentsAction(){
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$response = array();
		$response['status'] = "0";
		$db = Zend_Registry::get("db");
		$token = trim($this->_getParam('token'));
		$uid = $this->getuserid($token);
		if ($uid === false){
			$response['status'] = 999;
			echo json_encode($response);
			exit;
		}
		$response['status'] = "1";
		$select = $db->select()
				->from("beer_recipes_favorites", array("recipe_id"))
				->where("beer_recipes_favorites.user_id = ?", $uid);
		$result = $db->FetchAll($select);
		$rids = array();
		foreach($result as $key=>$val){
			$rids[] = $val['recipe_id'];
		}
		$select = $db->select()
				->from("beer_recipes", array("recipe_id"))
				->where("brewer_id = ?", $uid);
		$result = $db->FetchAll($select);
		foreach($result as $key=>$val){
			$rids[] = $val['recipe_id'];
		}
		$rids = array_unique($rids);
		$response['items_size'] = 0;
		$response['items'] = array();
		if (sizeof($rids) > 0){
			$rids = implode(", ", $rids);
			$select = $db->select()
					->from("beer_recipes_comments")
					->join("users", "beer_recipes_comments.comment_brewer=users.user_id", array("user_name", "user_email"))
					->where("beer_recipes_comments.comment_recipe IN (".$rids.")");
			$result = $db->FetchAll($select);
			$response['items_size'] = sizeof($result);
			$response['items'] = $result;
		}
		if (isset($_GET['testmode'])){
			echo "<pre>";print_r($response);exit;
		} else {
			echo $_GET['callback'] . '(' . json_encode($response) . ")";
		}
	}
	public function getallusersessionsAction(){
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$response = array();
		$response['status'] = "0";
		$db = Zend_Registry::get("db");
		$token = trim($this->_getParam('token'));
		$uid = $this->getuserid($token);
		if ($uid === false){
			$response['status'] = 999;
			echo json_encode($response);
			exit;
		}
		$response['status'] = "1";
		$select = $db->select()
				->from("beer_brew_sessions")
				->join("beer_recipes", "beer_brew_sessions.session_recipe=beer_recipes.recipe_id", array("recipe_name"))
				->where("beer_brew_sessions.session_brewer = ?", $uid);
		$result = $db->FetchAll($select);
		$response['items_size'] = sizeof($result);
		$response['items'] = $result;
		if (isset($_GET['testmode'])){
			echo "<pre>";print_r($response);exit;
		} else {
			echo $_GET['callback'] . '(' . json_encode($response) . ")";
		}
	}
	public function getalluserstorageAction(){
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$response = array();
		$response['status'] = "0";
		$db = Zend_Registry::get("db");
		$token = trim($this->_getParam('token'));
		$uid = $this->getuserid($token);
		if ($uid === false){
			$response['status'] = 999;
			echo json_encode($response);
			exit;
		}
		$response['status'] = "1";
		$select = $db->select()
				->from("storage_hops")
				->where("user_id = ?", $uid)
				->order("hop_name");
		$result = $db->FetchAll($select);
		$response['hops_size'] = sizeof($result);
		$response['hops'] = $result;
		$select = $db->select()
				->from("storage_malt")
				->where("user_id = ?", $uid)
				->order("malt_name");
		$result = $db->FetchAll($select);
		$response['malt_size'] = sizeof($result);
		$response['malt'] = $result;
		$select = $db->select()
				->from("storage_other")
				->where("user_id = ?", $uid)
				->order("other_name");
		$result = $db->FetchAll($select);
		$response['other_size'] = sizeof($result);
		$response['other'] = $result;
		$select = $db->select()
				->from("storage_yeast")
				->where("user_id = ?", $uid)
				->order("yeast_name");
		$result = $db->FetchAll($select);
		$response['yeast_size'] = sizeof($result);
		$response['yeast'] = $result;
		if (isset($_GET['testmode'])){
			echo "<pre>";print_r($response);exit;
		} else {
			echo $_GET['callback'] . '(' . json_encode($response) . ")";
		}
	}
}