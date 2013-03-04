<?php

class RecipesController extends Zend_Controller_Action {

	function init() {
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		$this->use_plato = false;
		$this->show_beta = false;
		$this->uid = 0;
		$this->ugroup = 'brewer';
		if (isset($user_info->user_id) && !empty($user_info->user_id)){
			$this->uid = $user_info->user_id;
			$this->ugroup = $user_info->user_type;
			$db = Zend_Registry::get("db");
			$select = $db->select()
					->from("users_attributes")
					->where("users_attributes.user_id = ?", $user_info->user_id)
					->limit(1);
			$u_atribs= $db->fetchRow($select);
			if ($u_atribs['beta_tester'] == 1) {
				$this->show_beta = true;
			}
			if ($u_atribs['plato'] == 1) {
				$this->use_plato = true;
			}
		}
		$this->view->use_plato = $this->use_plato;
		$this->view->uid = $this->uid;
	}
	public function specialAction(){
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("beer_recipes")
				->join("beer_styles", "beer_styles.style_id = beer_recipes.recipe_style")
				->join("users", "users.user_id = beer_recipes.brewer_id")
				->where("recipe_special = ?", 1)
				->order("recipe_special_date DESC");
		$result = $db->fetchAll($select);
		$this->view->items = $result;
	}
	
	public function checkuniquenameAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$recipe_id = $this->_getParam('recipe_id');
		$recipe_name = trim($this->_getParam('recipe_name'));
		if ($recipe_name == ""){
			echo "0";
			exit;
		} else {
			$db = Zend_Registry::get('db');
			$select = $db->select()
					->from("beer_recipes")
					->where("recipe_name = ?", $recipe_name);
			if ($recipe_id != 0){
				$select->where("recipe_id != ?", $recipe_id);
			}
			$result = $db->fetchAll($select);
			echo sizeof($result);
			exit;
		}
	}

	public function showemptyrecipesonAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$back = $_SERVER['HTTP_REFERER'];
		setcookie("show_empty_recipes", 1, time()+3600*24*360);
		header("location: ".$back);
	}

	public function showemptyrecipesoffAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$back = $_SERVER['HTTP_REFERER'];
		setcookie("show_empty_recipes", 0, time()+3600*24*360);
		header("location: ".$back);
	}

	public function indexAction() {
		$ord = $this->_getParam('recipe_order');
		if (empty($ord)) {
			if (isset($_COOKIE['recipe_sort']) && !empty($_COOKIE['recipe_sort'])){
				$ord = $_COOKIE['recipe_sort'];
			} else {
				$ord = "posted";
				setcookie("recipe_sort", $ord, time() + 60*60*24*356);
			}
		} else {
			setcookie("recipe_sort", $ord, time() + 60*60*24*356);
		}
		$this->view->order = $ord;
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("beer_recipes")
				->join("users", "users.user_id = beer_recipes.brewer_id", array("user_name"))
				->joinLeft("beer_styles", "beer_recipes.recipe_style = beer_styles.style_id", array("style_name"))
				->where("beer_recipes.recipe_publish = ?", '1');
		switch($ord){
			case "posted":
				$select->order("beer_recipes.recipe_created DESC");
			break;
			case "title":
				$select->order("TRIM(beer_recipes.recipe_name) ASC");
			break;
			case "sessions":
				$select->order("beer_recipes.recipe_total_sessions DESC");
			break;
			case "lastsession":
				$select->order("beer_recipes.recipe_last_session DESC");
			break;
			case "likes":
				$select->order("beer_recipes.recipe_total_likes DESC");
			break;
			case "awards":
				$select->order("beer_recipes.recipe_total_awards DESC");
				$select->order("beer_recipes.recipe_total_awards_weight DESC");
			break;
			case "comments":
				$select->order("beer_recipes.recipe_total_comments DESC");
			break;
			case "views":
				$select->order("beer_recipes.recipe_viewed DESC");
			break;
			default:
				$select->order("beer_recipes.recipe_created DESC");
			break;
				
		}
		$select_new = clone $select;
		if (!isset($_COOKIE['show_empty_recipes']) || $_COOKIE['show_empty_recipes'] != "1"){
			$select->where("recipe_total_sessions > 0");
		}
		$select_new->where("recipe_total_sessions = 0");
		$result = $db->fetchAll($select_new);
		$this->view->hidden_recipes = sizeof($result);
		//$this->view->recipes=$db->fetchAll($select);
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);


		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));

		$this->view->content->setItemCountPerPage(21);
		$select = $db->select()
				->from("beer_recipes_tags", array("weight" => "count(tag_text)", "tag_text" => "tag_text"))
				->group("tag_text")
				->order("weight DESC")
				->limit(100);
		$this->view->tags = array();
		if ($rows = $db->fetchAll($select)) {
			$this->view->tags = $rows;
		}
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

	public function stylesAction() {
		$ord = $this->_getParam('recipe_order');
		if (empty($ord)) {
			if (isset($_COOKIE['recipe_sort']) && !empty($_COOKIE['recipe_sort'])){
				$ord = $_COOKIE['recipe_sort'];
			} else {
				$ord = "posted";
				setcookie("recipe_sort", $ord, time() + 60*60*24*356);
			}
		} else {
			setcookie("recipe_sort", $ord, time() + 60*60*24*356);
		}
		$this->view->order = $ord;
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("beer_recipes")
				->join("users", "users.user_id = beer_recipes.brewer_id", array("user_name"))
				->joinLeft("beer_styles", "beer_recipes.recipe_style = beer_styles.style_id", array("style_name"));
		$select->where("beer_recipes.recipe_publish = ?", '1');
		$select->where("beer_recipes.recipe_style= ?", $this->_getParam('style'));
		switch($ord){
			case "posted":
				$select->order("beer_recipes.recipe_created DESC");
			break;
			case "title":
				$select->order("TRIM(beer_recipes.recipe_name) ASC");
			break;
			case "sessions":
				$select->order("beer_recipes.recipe_total_sessions DESC");
			break;
			case "lastsession":
				$select->order("beer_recipes.recipe_last_session DESC");
			break;
			case "likes":
				$select->order("beer_recipes.recipe_total_likes DESC");
			break;
			case "awards":
				$select->order("beer_recipes.recipe_total_awards DESC");
				$select->order("beer_recipes.recipe_total_awards_weight DESC");
			break;
			case "comments":
				$select->order("beer_recipes.recipe_total_comments DESC");
			break;
			case "views":
				$select->order("beer_recipes.recipe_viewed DESC");
			break;
			default:
				$select->order("beer_recipes.recipe_created DESC");
			break;
				
		}
		$select_new = clone $select;
		if (!isset($_COOKIE['show_empty_recipes']) || $_COOKIE['show_empty_recipes'] != "1"){
			$select->where("recipe_total_sessions > 0");
		}
		$select_new->where("recipe_total_sessions = 0");
		$result = $db->fetchAll($select_new);
		$this->view->hidden_recipes = sizeof($result);
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));
		$this->view->content->setItemCountPerPage(15);
		$select = $db->select()
				->from("beer_styles")
				->where("beer_styles.style_id= ?", $this->_getParam('style'));
		$this->view->beer_style = $db->fetchRow($select);
		$select = $db->select()
				->from("beer_awards")
				->order("posted DESC");
		$result = $db->FetchAll($select);
		$aw = array();
		foreach ($result as $key=>$val){
			$aw[$val['recipe_id']][] = $val;
		}
		$this->view->awards = $aw;
		$this->_helper->viewRenderer("index");
	}

	public function searchAction() {
		$db = Zend_Registry::get("db");
		$params = explode("|", $this->getRequest()->getParam("params"));
		$mask = array("type" => "recipe_type", "style" => "recipe_style", "medals" => "medals", "name" => "recipe_name", "hops" => "hop_name", "malts" => "malt_name", "yeasts" => "yeast_name", "brewer" => "user_name", "tags" => "tag_text", "mine" => 'mine');
		$select = $db->select()
				->from("beer_styles", array("style_name", "style_id", "style_cat"))
				->joinLeft("VIEW_public_recipes", "VIEW_public_recipes.recipe_style=beer_styles.style_id", array("count" => "count(VIEW_public_recipes.recipe_id)"))
				->group("beer_styles.style_id")
				->order("style_name");
		$this->view->beer_styles = $db->fetchAll($select);
		$filter = array();
		for ($i = 0; $i < count($params); $i++) {
			$c = explode(":", $params[$i]);
			if (isset($mask[$c[0]])) {
				if (isset($c[1])) {
					$filter[$mask[$c[0]]] = $c[1];
				}
			}
		}

		$select = $db->select();
		$select->from("beer_recipes")
				->join("users", "users.user_id=beer_recipes.brewer_id", array("user_name"))
				->joinLeft("beer_styles", "beer_styles.style_id=beer_recipes.recipe_style", array("style_name"));
		if (!empty($filter)) {
			if (isset($filter["hop_name"])) {
				$select->joinLeft("beer_recipes_hops", "beer_recipes_hops.recipe_id=beer_recipes.recipe_id");
			}
		}
		if (!empty($filter)) {
			if (isset($filter["tag_text"])) {
				$select->joinLeft("beer_recipes_tags", "beer_recipes_tags.tag_recipe_id=beer_recipes.recipe_id");
			}
		}
		if (!empty($filter)) {
			if (isset($filter["malt_name"])) {
				$select->joinLeft("beer_recipes_malt", "beer_recipes_malt.recipe_id=beer_recipes.recipe_id");
			}
		}
		if (!empty($filter)) {
			if (isset($filter["yeast_name"])) {
				$select->joinLeft("beer_recipes_yeast", "beer_recipes_yeast.recipe_id=beer_recipes.recipe_id");
			}
		}


		if (!empty($filter)) {
			if (isset($filter["recipe_type"])) {
				$select->where("recipe_type = ?", $filter["recipe_type"]);
			} else {
				$filter["recipe_type"] = 0;
			}
			if (isset($filter["recipe_style"])) {
				$select->where("recipe_style = ?", intval($filter["recipe_style"]));
			} else {
				$filter["recipe_style"] = 0;
			}
			if (isset($filter["recipe_name"])) {
				$select->where("recipe_name LIKE '%" . $filter["recipe_name"] . "%'");
			} else {
				$filter["recipe_name"] = "";
			}
			if (isset($filter["medals"]) && $filter["medals"] == "1") {
				$select->where("recipe_total_awards > 0");
			} else {
				$filter["medals"] = "";
			}
			if (isset($filter["mine"]) && $filter["mine"] == "1" && $this->uid != 0) {
				$select->where("brewer_id = ?", $this->uid);
				$filter["user_name"] = "";
			} else {
				$filter["mine"] = "0";
				if (isset($filter["user_name"])) {
					$select->where("user_name LIKE '%" . $filter["user_name"] . "%'");
				} else {
					$filter["user_name"] = "";
				}
			}

			if (isset($filter["hop_name"])) {
				$hops = explode(",", $filter["hop_name"]);
				for ($i = 0; $i < count($hops); $i++) {
					if (strlen(trim($hops[$i])) > 0) {
						$select->where("hop_name LIKE '%" . trim($hops[$i]) . "%'");
					}
				}
			} else {
				$filter["hop_name"] = "";
			}
			if (isset($filter["tag_text"])) {
				$tags = explode(",", $filter["tag_text"]);
				for ($i = 0; $i < count($tags); $i++) {
					if (strlen(trim($tags[$i])) > 0) {
						$select->where("tag_text LIKE '%" . addslashes(trim($tags[$i])) . "%'");
					}
				}
			} else {
				$filter["tag_text"] = "";
			}
			if (isset($filter["malt_name"])) {
				$malt = explode(",", $filter["malt_name"]);
				for ($i = 0; $i < count($malt); $i++) {
					if (strlen(trim($malt[$i])) > 0) {
						$select->where("malt_name LIKE '%" . trim($malt[$i]) . "%'");
					}
				}
			} else {
				$filter["malt_name"] = "";
			}
			if (isset($filter["yeast_name"])) {
				$yeast = explode(",", $filter["yeast_name"]);
				for ($i = 0; $i < count($yeast); $i++) {
					if (strlen(trim($yeast[$i])) > 0) {
						$select->where("yeast_name LIKE '%" . trim($yeast[$i]) . "%'");
					}
				}
			} else {
				$filter["yeast_name"] = "";
			}
			if (isset($filter["mine"]) && $filter["mine"] == "1" && $this->uid != 0) {
				//$select->where("recipe_publish = ?", '1');
			} else {
				$select->where("recipe_publish = ?", '1');
			}
			$select->group("beer_recipes.recipe_id");
			$select->order("beer_recipes.recipe_name");
			$select_new = clone $select;
			if (!isset($_COOKIE['show_empty_recipes']) || $_COOKIE['show_empty_recipes'] != "1"){
				$select->where("recipe_total_sessions > 0");
			}
			$select_new->where("recipe_total_sessions = 0");
			$result = $db->fetchAll($select_new);
			$this->view->hidden_recipes = sizeof($result);
			$adapter = new Zend_Paginator_Adapter_DbSelect($select);
			$this->view->content = new Zend_Paginator($adapter);
			$this->view->content->setCurrentPageNumber($this->_getParam('page'));
			$this->view->content->setItemCountPerPage(15);
			//print $select->__toString();
			
			$select = $db->select()
					->from("beer_awards")
					->order("posted DESC");
			$result = $db->FetchAll($select);
			$aw = array();
			foreach ($result as $key=>$val){
				$aw[$val['recipe_id']][] = $val;
			}
			$this->view->awards = $aw;
		} else {
			$filter = array("recipe_style" => 0, "recipe_type" => 0, "recipe_name" => "", "hop_name" => "", "yeast_name" => "", "malt_name" => "", "user_name" => "", "tag_text" => "", "medals" => "");
		}
		$this->view->filter_values = $filter;
	}

	public function modTagsAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$storage = new Zend_Auth_Storage_Session();
		$u = $storage->read();
		$tags = isset($_POST['tags']) ? $_POST['tags'] : '';
		$db = Zend_Registry::get("db");
		if ($u->user_id) {
			$tags = isset($_POST['tags']) ? $_POST['tags'] : '';
			$db->delete("beer_recipes_tags", "tag_recipe_id = " . $_POST['recipe_id'] . ' and tag_owner = ' . $u->user_id);
			$tags_array = explode(",", $tags);
			foreach ($tags_array as $tag) {
				$db->insert("beer_recipes_tags", array("tag_recipe_id" => $_POST['recipe_id'], "tag_owner" => $u->user_id, "tag_text" => $tag));
			}
			print Zend_Json::encode(array("tags" => $tags));
		} else {
			print Zend_Json::encode(array("result" => 1));
		}
	}

	public function printLabelAction() {
		$this->_helper->layout->setLayout('empty');
		$recipe_id = isset($_GET['recipe_id']) ? $_GET['recipe_id'] : 0;
		$db = Zend_Registry::get("db");
		$select = $db->select();
		$select->from("beer_recipes")
				->join("users", "users.user_id=beer_recipes.brewer_id", array("user_name"))
				->joinLeft("beer_styles", "beer_styles.style_id=beer_recipes.recipe_style", array("style_name"))
				->where("recipe_id = ?", $recipe_id);
		if (!$this->view->recipe = $db->fetchRow($select)) {
			$this->view->recipe = array("recipe_id" => 0, "style_name" => "", "recipe_name" => "", "recipe_ibu" => "", "recipe_ebc" => "", "recipe_abv" => "");
		};
	}

	public function cloudAction() {
		//  $this->_helper->layout->setLayout('empty');
		$db = Zend_Registry::get("db");
		$select = $db->select();
		$select->from("beer_recipes")
				->where('recipe_publish =?', '1');
		if (isset($_GET['id'])) {
			$select->where("brewer_id=?", $_GET['id']);
		}
		$select->limit(25);
		$this->view->words = $db->fetchAll($select);
	}

	public function viewAction() {
		$storage = new Zend_Auth_Storage_Session();

		$ruid = explode("-", $this->_getParam('recipe'));
		$recipe_id = $ruid[0];
		$this->view->data = array();
		if ($recipe_id > 0) {
			$db = Zend_Registry::get("db");
			
			$select = $db->select()
					->from("beer_recipes", array("recipe_id", "recipe_publish", "recipe_created", "brewer_id", "recipe_viewed"))
					->join("users", "users.user_id = beer_recipes.brewer_id", array("user_name"))
					->where("recipe_id = ?", $recipe_id);
			$rcp = $db->fetchRow($select);
			if ($this->uid != $rcp['brewer_id']){
				$update = $db->update("beer_recipes", array(
						'recipe_viewed' => $rcp['recipe_viewed'] + 1
					), "recipe_id = '".$rcp['recipe_id']."'");
			}
			$doshow = true;
			if ($rcp['recipe_publish'] == 0 && $rcp['brewer_id'] != $this->uid && $this->ugroup != "admin"){
				$doshow = false;
				if (isset($_GET['auth_key']) && !empty($_GET['auth_key'])){
					$auth_key = $_GET['auth_key'];
					$hash = md5($rcp['recipe_id'].$rcp['recipe_created']);
					if ($hash == $auth_key){
						$doshow = true;
					}
				}
			}
			if ($doshow === true){
				$recipe = new Entities_Recipe($recipe_id);
				$this->view->data["recipe"] = $recipe->getProperties();
				$this->view->data["malt"] = $recipe->getMalts();
				$this->view->data["hops"] = $recipe->getHops();
				$this->view->data["yeast"] = $recipe->getYeasts();
				$this->view->user_info = $storage->read();
				$select = $db->select();
				$select->from("beer_brew_sessions", array("count" => "count(*)"))
						->where("session_recipe =?", $recipe_id);
				$this->view->data["brew_session"] = $db->fetchRow($select);
				$this->view->tags = "";
				$select = $db->select();
				$select->from("beer_recipes_tags", array("tags" => "group_concat(tag_text)"))
						->where("tag_recipe_id =?", $recipe_id);

				if ($tags = $db->fetchRow($select)) {
					$this->view->tags = $tags['tags'];
				}

				$user_id = 0;
				if ($this->view->user_info) {
					$user_id = $this->view->user_info->user_id;
				}
				$this->view->recipe_votes = array("total" => $this->getVotes($recipe_id), "user_vote" => $this->getUserVotes($recipe_id, $user_id));
				$select = $db->select()
						->from("beer_awards")
						->order("posted DESC")
						->where("recipe_id = ?", $recipe_id);
				$result = $db->FetchAll($select);
				$aw = array();
				foreach ($result as $key=>$val){
					$aw[$val['recipe_id']][] = $val;
				}
				$this->view->awards = $aw;

				$select = $db->select()
						->from("beer_images", array("*", "DATE_FORMAT(posted, '%Y-%m-%d') as postedf"))
						->join("users", "users.user_id=beer_images.user_id", "user_name")
						->where("recipe_id = ?", array($recipe_id))
						->order("posted ASC");
				$images = $db->FetchAll($select);
				$this->view->images = $images;
			} else {
				$this->view->rcp = $rcp;
				$this->_helper->viewRenderer('private');
			}
		}
	}

	public function favoritesAction() {
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from('cache_fav_recipes', array("votes"))
				->join("beer_recipes", "beer_recipes.recipe_id = cache_fav_recipes.recipe_id")
				->join("beer_styles", "beer_styles.style_id = beer_recipes.recipe_style", array("style_name"))
				->join("users", "users.user_id=cache_fav_recipes.brewer_id", array("user_name", "user_email"))
				->order("total DESC");
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));
		$this->view->content->setItemCountPerPage(100);
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
	
	public function deleteimageAction(){
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$image_id = $this->_getParam('image_id');
		$db = Zend_Registry::get('db');
		$storage = new Zend_Auth_Storage_Session();
		$u = $storage->read();
		$select = $db->select()
				->from("beer_images")
				->where("id = ?", array($image_id));
		$result = $db->FetchRow($select);
		if (isset($u->user_id) && !empty($u->user_id)){
			if ($result['user_id'] == $u->user_id){
				$db->delete("beer_images", "id = '".$image_id."'");
				unlink($_SERVER['DOCUMENT_ROOT'] . "/recipe_images/".$result['recipe_id']."/".$result['file_name'].".jpg");
				unlink($_SERVER['DOCUMENT_ROOT'] . "/recipe_images/".$result['recipe_id']."/t_".$result['file_name'].".jpg");
			} else {
				$select = $db->select()
						->from("beer_recipes")
						->where("recipe_id = ?", $result['recipe_id']);
				$result2 = $db->FetchRow($select);
				if ($result2['brewer_id'] == $u->user_id){
					$db->delete("beer_images", "id = '".$image_id."'");
					unlink($_SERVER['DOCUMENT_ROOT'] . "/recipe_images/".$result['recipe_id']."/".$result['file_name'].".jpg");
					unlink($_SERVER['DOCUMENT_ROOT'] . "/recipe_images/".$result['recipe_id']."/t_".$result['file_name'].".jpg");
				}
			}
		}
		$this->_redirect("/alus/receptas/".$result["recipe_id"]);
	}
	
	private function getAvailableName($rid){
		$rand = md5(mktime().rand(0, 1000000));
		if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/recipe_images/".$rid."/".$rand.".jpg")){
			return $this->getAvailableName($rid);
		} else {
			return $rand;
		}
	}

	public function uploadimageAction(){
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$image_id = $this->_getParam('image_id');
		$db = Zend_Registry::get('db');
		$storage = new Zend_Auth_Storage_Session();
		$u = $storage->read();
		$post = $this->getRequest()->getPost();
		if (isset($u->user_id) && $u->user_id != 0){
			if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/recipe_images/".$post['recipe_id'])){
				mkdir($_SERVER['DOCUMENT_ROOT'] . "/recipe_images/".$post['recipe_id']);
			}
			$allowedExts = array("jpg", "jpeg", "gif", "png");
			$file = $_FILES['recipe_image'];
			if ($file["name"] != ""){
				$newname = $this->getAvailableName($post['recipe_id']);
				$fullFilePath = $_SERVER['DOCUMENT_ROOT'] . "/recipe_images/".$post['recipe_id']."/".$newname.".jpg";
				$t_fullFilePath = $_SERVER['DOCUMENT_ROOT'] . "/recipe_images/".$post['recipe_id']."/t_".$newname.".jpg";
				$extension = end(explode(".", $file["name"]));
				$extension = strtolower($extension);
				if ((($file["type"] != "image/gif") && ($file["type"] != "image/jpeg") && ($file["type"] != "image/png")) || !in_array($extension, $allowedExts)){
				} else {
					switch(strtolower($file['type'])){
						case 'image/jpeg':
							$image = imagecreatefromjpeg($file['tmp_name']);
							break;
						case 'image/png':
							$image = imagecreatefrompng($file['tmp_name']);
							break;
						case 'image/gif':
							$image = imagecreatefromgif($file['tmp_name']);
							break;
						default:
							exit('Unsupported type: '.$file['type']);
					}
					$max_width = 1024;
					$max_height = 1024;
					$old_width  = imagesx($image);
					$old_height = imagesy($image);
					$scale      = min($max_width/$old_width, $max_height/$old_height);
					$new_width  = ceil($scale*$old_width);
					$new_height = ceil($scale*$old_height);
					$newf = imagecreatetruecolor($new_width, $new_height);
					imagecopyresampled($newf, $image, 0, 0, 0, 0, $new_width, $new_height, $old_width, $old_height);
					imagejpeg($newf, $fullFilePath);
					
					$canvas_w = 138;
					$canvas_h = 104;
					$width  = imagesx($image);
					$height = imagesy($image);
					$original_overcanvas_w = $width/$canvas_w;
					$original_overcanvas_h = $height/$canvas_h;
					$dst_w = round($width/min($original_overcanvas_w,$original_overcanvas_h),0);
					$dst_h = round($height/min($original_overcanvas_w,$original_overcanvas_h),0);
					$new = imagecreatetruecolor($canvas_w, $canvas_h);
					$background = imagecolorallocate($new, 255, 255, 255);
					imagefill($new, 0, 0, $background);
					imagecopyresampled($new, $image, ($canvas_w-$dst_w)/2, ($canvas_h-$dst_h)/2, 0, 0, $dst_w, $dst_h, $width, $height);
					imagejpeg($new, $t_fullFilePath);
					
					$insert = $db->insert("beer_images", array(
						"user_id" => $u->user_id,
						"recipe_id" => $post['recipe_id'],
						"file_name" => $newname,
						"posted" => date("Y-m-d H:i:s"),
						"title" => ""
					));
				}
			}
		}
		$this->_redirect("/alus/receptas/".$post["recipe_id"]);
	}
	
	public function galleryAction() {
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("beer_images", array("*", "DATE_FORMAT(posted, '%Y-%m-%d') as postedf"))
				->join("users", "users.user_id = beer_images.user_id", array("user_name"))
				->join("beer_recipes", "beer_recipes.recipe_id=beer_images.recipe_id", array("recipe_name"))
				->order("beer_images.posted DESC");
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));
		$this->view->content->setItemCountPerPage(50);
	}

	public function getVotes($recipe_id = 0) {
		$db = Zend_Registry::get('db');
		$select = $db->select();
		$select->from("beer_recipes_favorites", array("count" => "count(*)"))
				->where("recipe_id =?", $recipe_id);
		$fv = $db->fetchRow($select);
		return $fv["count"];
	}

	public function getUserVotes($recipe_id = 0, $user_id = 0) {
		$db = Zend_Registry::get('db');
		$select = $db->select();
		$select->from("beer_recipes_favorites", array("count" => "count(*)"))
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
				$db->delete("beer_recipes_favorites", "recipe_id = " . $_POST['id'] . ' and user_id = ' . $u->user_id);
				switch ($_POST['action']) {

					case "vote_up":
						$db->insert("beer_recipes_favorites", array("recipe_id" => $_POST['id'], "user_id" => $u->user_id));

						break;
				}
                                Entities_Events::trigger("vote_recipe", array("recipe_id" => $_POST['id']));
				print Zend_Json::encode(array("status" => 0, "data" => array("votes" => $this->getVotes($_POST['id']))));
			}
		} else {
			print Zend_Json::encode(array("status" => 1, "errors" => array(array("message" => "Neregistruotas naudotojas", "type" => "authentication"))));
		}
	}

	public function publishAction() {
		if (isset($_POST)) {
			$this->_helper->layout->setLayout('empty');
			$this->_helper->viewRenderer->setNoRender(true);
			$storage = new Zend_Auth_Storage_Session();
			$u = $storage->read();
			if (isset($u->user_name)) {
				$db = Zend_Registry::get('db');
				if (strlen($_POST["recipe_id"]) > 0) {
					$select = $db->select();
					$select->from("beer_recipes", array("recipe_id","recipe_published","recipe_publish"))
							->where("brewer_id = ?", $u->user_id)
							->where("recipe_id = ?", $_POST["recipe_id"]);
					$r = $db->fetchRow($select);
                                        
					if (isset($r)) {
                                            // patch sql update  `beer_recipes`  set recipe_published=recipe_created where recipe_publish ='1' and recipe_published ='0000-00-00 00:00:00'
                                            if ($r["recipe_published"]=='0000-00-00 00:00:00' && $r["recipe_publish"]=='0') {
                                                $db->update("beer_recipes", array("recipe_published"=>date('Y-m-d H:i:s', time()),"recipe_publish" => $_POST['publish']), "recipe_id = " . $r['recipe_id']);
                                            }else{
						$db->update("beer_recipes", array("recipe_publish" => $_POST['publish']), "recipe_id = " . $r['recipe_id']);
                                            }

						print Zend_Json::encode(array("status" => 0));
						return;
					} else {
						
					}
				}print Zend_Json::encode(array("status" => 1, "errors" => array(array("message" => "Receptas nerastas", "type" => "system"))));
			} else {
				print Zend_Json::encode(array("status" => 1, "errors" => array(array("message" => "Neregistruotas naudotojas", "type" => "authentication"))));
			}
		}
	}

	public function deleteAction() {
		if (isset($_POST)) {
			$this->_helper->layout->setLayout('empty');
			$this->_helper->viewRenderer->setNoRender(true);
			$storage = new Zend_Auth_Storage_Session();
			$u = $storage->read();
			if (isset($u->user_name)) {
				$db = Zend_Registry::get('db');
				if (strlen($_POST["recipe_id"]) > 0) {
					$select = $db->select();
					$select->from("beer_recipes", array("recipe_id"))
							->where("brewer_id = ?", $u->user_id)
							->where("recipe_id = ?", $_POST["recipe_id"]);
					$r = $db->fetchRow($select);
					if (isset($r)) {
						$db->delete("beer_recipes", "recipe_id = " . $r['recipe_id']);
						$db->delete("beer_recipes_malt", "recipe_id = " . $r['recipe_id']);
						$db->delete("beer_recipes_hops", "recipe_id = " . $r['recipe_id']);
						$db->delete("beer_recipes_yeast", "recipe_id = " . $r['recipe_id']);
						$db->delete("beer_recipes_comments", "comment_recipe = " . $r['recipe_id']);
						$db->delete("beer_recipes_favorites", "recipe_id = " . $r['recipe_id']);
						$db->delete("cache_fav_recipes", "recipe_id = " . $r['recipe_id']);
						print Zend_Json::encode(array("status" => 0));
						return;
					} else {
						
					}
				}print Zend_Json::encode(array("status" => 1, "errors" => array(array("message" => "Receptas nerastas", "type" => "system"))));
			} else {
				print Zend_Json::encode(array("status" => 1, "errors" => array(array("message" => "Neregistruotas naudotojas", "type" => "authentication"))));
			}
		}
	}

	public function saveAction() {
		if (isset($_POST)) {
			$this->_helper->layout->setLayout('empty');
			$this->_helper->viewRenderer->setNoRender(true);
			$storage = new Zend_Auth_Storage_Session();
			$u = $storage->read();

			if (isset($u->user_name)) {
				$db = Zend_Registry::get('db');

				$fields_recipe = array('recipe_batch' => 'bash_size', 'recipe_boiltime' => 'boil_time', 'recipe_efficiency' => 'efficiency', 'recipe_attenuation' => 'attenuation', 'recipe_name' => 'beer_name', 'recipe_style' => 'style_id', 'recipe_comments' => 'comments', 'recipe_sg' => 'recipe_sg', 'recipe_fg' => 'recipe_fg', 'recipe_ebc' => 'recipe_ebc', 'recipe_abv' => 'recipe_abv', 'recipe_ibu' => 'recipe_ibu');
				$ins = array();
				$recipe_update = false;
				foreach ($fields_recipe as $key => $value) {
					$ins[$key] = $_POST[$value];
				}
				$recipe_type = "grain";
				if (isset($_POST["malt_list"])) {
					for ($i = 0; $i < count($_POST["malt_list"]); $i++) {
						if ($_POST["malt_type"][$i] == "extract") {
							$recipe_type = "partial";
						}
					}
				}
				$ins["recipe_type"] = $recipe_type;
				if (strlen($_POST["recipe_id"]) > 0) {
					$select = $db->select();
					$select->from("beer_recipes", array("recipe_id"))
							->where("brewer_id = ?", $u->user_id)
							->where("recipe_id = ?", $_POST["recipe_id"]);
					$r = $db->fetchAll($select);
					if (count($r) > 0) {
						
					} else {
						$_POST["recipe_id"] = "";
					}
				}
				if (strlen($_POST["recipe_id"]) > 0 && !isset($_POST["duplicate"])) {

					$ins["recipe_modified"] = new Zend_Db_Expr('CURRENT_TIMESTAMP');
					if ($db->update("beer_recipes", $ins, "recipe_id = " . $_POST["recipe_id"])) {
						$recipe_id = $_POST["recipe_id"];
						$recipe_update = true;
					}
				} else {
					$ins["brewer_id"] = $u->user_id;
					if ($db->insert("beer_recipes", $ins)) {
						;
						$recipe_id = $db->lastInsertId();
					}
				}

				$fields_malt = array("malt_id" => "malt_id", "malt_type" => "malt_type", "malt_name" => 'malt_list', "malt_extract" => "malt_extract", "malt_ebc" => "malt_color", "malt_weight" => "malt_weight");
				$fields_hops = array("hop_id" => "hop_id", "hop_time" => "hop_time", "hop_name" => 'hop_list', "hop_alpha" => "hop_alpha", "hop_weight" => "hop_weight");
				$fields_yeast = array("yeast_id" => "yeast_id", "yeast_name" => 'yeast_list', "yeast_weight" => "yeast_weight");

				if ($recipe_id) {
					if ($recipe_update) {// istrinti
						$db->delete("beer_recipes_malt", "recipe_id = " . $recipe_id);
						$db->delete("beer_recipes_hops", "recipe_id = " . $recipe_id);
						$db->delete("beer_recipes_yeast", "recipe_id = " . $recipe_id);
					}
					if (isset($_POST["malt_list"])) {
						for ($i = 0; $i < count($_POST["malt_list"]); $i++) {
							$ins = array();
							foreach ($fields_malt as $key => $value) {
								$ins[$key] = $_POST[$value][$i];
							}
							$ins["recipe_id"] = $recipe_id;
							$db->insert("beer_recipes_malt", $ins);
						}
					}
					if (isset($_POST["hop_list"])) {
						for ($i = 0; $i < count($_POST["hop_list"]); $i++) {
							$ins = array();
							foreach ($fields_hops as $key => $value) {
								$ins[$key] = $_POST[$value][$i];
							}
							$ins["recipe_id"] = $recipe_id;
							$db->insert("beer_recipes_hops", $ins);
						}
					}
					if (isset($_POST["yeast_list"])) {
						for ($i = 0; $i < count($_POST["yeast_list"]); $i++) {
							$ins = array();
							foreach ($fields_yeast as $key => $value) {
								$ins[$key] = $_POST[$value][$i];
							}
							$ins["recipe_id"] = $recipe_id;
							$db->insert("beer_recipes_yeast", $ins);
						}
					}
				}
				print Zend_Json::encode(array("status" => 0, "data" => array("recipe_id" => $recipe_id)));

				//  print $db->lastInsertId().$_POST['recipe_sg'];
			} else {
				print Zend_Json::encode(array("status" => 1, "errors" => array(array("message" => "Neregistruotas naudotojas", "type" => "authentication"))));
			}
		}
	}

	public function randomAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get('db');
		$select = $db->select();
		$select->from("beer_recipes", array("recipe_id"))
				->where("recipe_publish =?", '1')
				->order("Rand()")
				->limit(1);
		if ($random = $db->fetchRow($select)) {
			$this->_redirect("/recipes/view/" . $random["recipe_id"]);
		}
	}

	function findAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender();
		$db = Zend_Registry::get('db');
		if (isset($_GET['tags'])) {
			$select = $db->select()
					->from("beer_recipes_tags", array("tag_text" => "distinct(tag_text)"))
					->where("tag_text like '%" . $_GET["term"] . "%'");
			$u = $db->fetchAll($select);
			for ($i = 0; $i < count($u); $i++) {
				$u[$i] = $u[$i]["tag_text"];
			}
		}
		if (isset($_GET['hops'])) {
			$select = $db->select()
					->from("beer_recipes_hops", array("hop_name" => "distinct(hop_name)"))
					->where("hop_name like '%" . $_GET["term"] . "%'");
			//->where("user_enabled = '1'");;
			//->where("user_active = '1'");
			$u = $db->fetchAll($select);
			for ($i = 0; $i < count($u); $i++) {
				$u[$i] = $u[$i]["hop_name"];
			}
		}
		if (isset($_GET['malts'])) {
			$select = $db->select()
					->from("beer_recipes_malt", array("malt_name" => "distinct(malt_name)"))
					->where("malt_name like '%" . $_GET["term"] . "%'");
			//->where("user_enabled = '1'");;
			//->where("user_active = '1'");
			$u = $db->fetchAll($select);
			for ($i = 0; $i < count($u); $i++) {
				$u[$i] = $u[$i]["malt_name"];
			}
		}
		if (isset($_GET['yeasts'])) {
			$select = $db->select()
					->from("beer_recipes_yeast", array("yeast_name" => "distinct(yeast_name)"))
					->where("yeast_name like '%" . $_GET["term"] . "%'");
			//->where("user_enabled = '1'");;
			//->where("user_active = '1'");
			$u = $db->fetchAll($select);
			for ($i = 0; $i < count($u); $i++) {
				$u[$i] = $u[$i]["yeast_name"];
			}
		}
		print Zend_Json::encode($u);
	}

}