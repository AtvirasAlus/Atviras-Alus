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

	public function deleteAction() {
		$db = Zend_Registry::get('db');
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$storage = new Zend_Auth_Storage_Session();
		$u = $storage->read();
		if (isset($u->user_name)) {
			$select = $db->select()
					->from("food_items")
					->where("id = ?", array($_POST['recipe_id']));
			$result = $db->FetchRow($select);
			if (isset($_POST) && $result['author_id'] == $u->user_id) {
				$db->delete("food_items", "id = " . $_POST['recipe_id'] . ' and author_id = ' . $u->user_id);
				$db->delete("food_comments", "food_id = " . $_POST['recipe_id']);
				$db->delete("food_favorites", "recipe_id = " . $_POST['recipe_id']);
				$db->delete("food_ingridients", "recipe_id = " . $_POST['recipe_id']);
				$db->delete("food_styles", "recipe_id = " . $_POST['recipe_id']);
				$this->rrmdir($_SERVER['DOCUMENT_ROOT'] . "/food/" . $_POST['recipe_id']);
				
				print Zend_Json::encode(array("status" => 0));
			}
		} else {
			print Zend_Json::encode(array("status" => 1, "errors" => array(array("message" => "Neregistruotas nautotojas", "type" => "authentication"))));
		}
	}
	
	public function rrmdir($dir) {
		foreach(glob($dir . '/*') as $file) {
			if(is_dir($file))
				rrmdir($file);
			else
				unlink($file);
		}
		rmdir($dir);
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

	public function myAction() {
		$db = Zend_Registry::get('db');
		$storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $storage->read();
		$me = -1;
		if (isset($this->view->user_info->user_id) && !empty($this->view->user_info->user_id)) {
			$me = $this->view->user_info->user_id;
		}
		if ($me == -1) $this->_redirect('/maistas');
		$select = $db->select()
				->from("food_items", array("*", "DATE_FORMAT(posted, '%Y-%m-%d') as postedf", "DATE_FORMAT(modified, '%Y-%m-%d') as modifiedf"))
				->join("users", "food_items.author_id = users.user_id", array("user_name"))
				->where("food_items.author_id = ?", array($me))
				->order("food_items.posted DESC");
		$items = $db->FetchAll($select);
		$this->view->items = $items;
		
	}
	
	public function newAction() {
		$db = Zend_Registry::get('db');
		$storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $storage->read();
		$me = -1;
		if (isset($this->view->user_info->user_id) && !empty($this->view->user_info->user_id)) {
			$me = $this->view->user_info->user_id;
		}
		if ($me == -1) $this->_redirect('/maistas');
		
		if (isset($_POST['rec_submit'])){
			$post = $this->getRequest()->getPost();
			$this->view->postdata = $post;
			$errors = array();
			if (!isset($post['rec_title']) || empty($post['rec_title'])){
				$errors[] = "Įveskite recepto pavadinimą";
			}
			if (!isset($post['rec_cat']) || empty($post['rec_cat'])){
				$errors[] = "Pasirinkite recepto kategoriją";
			}
			if (!isset($post['rec_description']) || empty($post['rec_description'])){
				$errors[] = "Aprašykite gaminimo eigą";
			}
			if (!isset($post['rec_ing_name']) && sizeof($post['rec_ing_name']) == 0){
				$errors[] = "Sukurkite bent vieną ingridientą";
			} else {
				foreach($post['rec_ing_name'] as $k=>$v){
					if (empty($v)){
						$errors[] = "Įveskite ingridiento pavadinimą";
						break;
					}
				}
				foreach($post['rec_ing_amount'] as $k=>$v){
					if (empty($v)){
						$errors[] = "Įveskite ingridiento kiekįą";
						break;
					}
				}
			}
			$allowedExts = array("jpg", "jpeg", "gif", "png");
			foreach($_FILES as $key=>$file){
				if ($file["name"] != ""){
					$extension = end(explode(".", $file["name"]));
					if ((($file["type"] != "image/gif") && ($file["type"] != "image/jpeg") && ($file["type"] != "image/png")) || !in_array($extension, $allowedExts)){
						$errors[] = "Neleistinas failas. Galima įkelti tik PNG, GIF ir JPG failus";
						break;
					}
				}
			}
			if (sizeof($errors)>0){
				$this->view->errors = implode("\n", $errors);
			} else {
				$select = $db->select()
						->from("food_categories")
						->where("id = ?", array($post['rec_cat']));
				$cat = $db->FetchRow($select);
				$db->insert("food_items", array(
					"cat_id" =>  (int)$post['rec_cat'],
					"parent_cat_id" =>  $cat['parent_id'],
					"title" =>  trim(strip_tags($post['rec_title'])),
					"description" => strip_tags($post['rec_description'], "<a><b><i>"),
					"author_id" =>  $me,
					"posted" => date("Y-m-d H:i:s"),
					"modified" => "0000-00-00 00:00:00",
				));
				$last_id = $db->lastInsertId();
				foreach($post['rec_ing_name'] as $key=>$val){
					$db->insert("food_ingridients", array(
						"recipe_id" =>  $last_id,
						"title" =>  trim(strip_tags($val)),
						"amount" =>  trim(strip_tags($post['rec_ing_amount'][$key]))
					));
				}
				if (isset($post['rec_style'])){
					foreach($post['rec_style'] as $key=>$val){
						$db->insert("food_styles", array(
							"recipe_id" =>  $last_id,
							"style_id" =>  (int)$val
						));
					}
				}
				@mkdir($_SERVER['DOCUMENT_ROOT'] . "/food/" . $last_id);
				$i = 0;
				foreach($_FILES as $key=>$file){
					if ($file['name'] != ""){
						$i++;
						$extension = strtolower(end(explode(".", $file["name"])));
						$name_s = $i.".".$extension;
						$fullFilePath = $_SERVER['DOCUMENT_ROOT'] . "/food/" . $last_id . "/".$name_s;
						$t_fullFilePath = $_SERVER['DOCUMENT_ROOT'] . "/food/" . $last_id . "/t_".$name_s;
						copy($file['tmp_name'], $fullFilePath);
						$db->update("food_items", array("image".$i => $name_s), array("id = '" . $last_id . "'"));
						
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
						$canvas_w = 245;
						$canvas_h = 176;
						
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
						
						switch(strtolower($file['type'])){
							case 'image/jpeg':
								imagejpeg($new, $t_fullFilePath);
								break;
							case 'image/png':
								imagepng($new, $t_fullFilePath);
								break;
							case 'image/gif':
								imagegif($new, $t_fullFilePath);
								break;
						}
					}
				}
				$this->_redirect('/maistas/mano');
			}
		}
		
		$select = $db->select()
				->from("food_categories")
				->order("title ASC")
				->where("parent_id = ?", array(0));
		$cats = $db->FetchAll($select);
		foreach($cats as $key=>$val){
			$select = $db->select()
					->from("food_categories")
					->order("title ASC")
					->where("parent_id = ?", array($val['id']));
			$cats[$key]['childs'] = $db->FetchAll($select);
		}
		$this->view->cats = $cats;

		$select = $db->select()
				->from("beer_cats")
				->order("cat_name ASC");
		$styles = $db->FetchAll($select);
		foreach($styles as $key=>$val){
			$select = $db->select()
					->from("beer_styles")
					->order("style_name ASC")
					->where("style_cat = ?", array($val['cat_id']));
			$styles[$key]['childs'] = $db->FetchAll($select);
		}
		$this->view->styles = $styles;
	}
	
	public function editAction() {
		$db = Zend_Registry::get('db');
		$storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $storage->read();
		$me = -1;
		if (isset($this->view->user_info->user_id) && !empty($this->view->user_info->user_id)) {
			$me = $this->view->user_info->user_id;
		}
		if ($me == -1) $this->_redirect('/maistas');
		$this->view->item_id = $item_id = $this->getRequest()->getParam("item");
		
		$select = $db->select()
				->from("food_items")
				->where("id = ?", array($item_id));
		$item = $db->FetchRow($select);

		if (isset($_POST['rec_submit'])){
			$post = $this->getRequest()->getPost();
			$this->view->postdata = $post;
			$errors = array();
			if (!isset($post['rec_title']) || empty($post['rec_title'])){
				$errors[] = "Įveskite recepto pavadinimą";
			}
			if (!isset($post['rec_cat']) || empty($post['rec_cat'])){
				$errors[] = "Pasirinkite recepto kategoriją";
			}
			if (!isset($post['rec_description']) || empty($post['rec_description'])){
				$errors[] = "Aprašykite gaminimo eigą";
			}
			if (!isset($post['rec_ing_name']) && sizeof($post['rec_ing_name']) == 0){
				$errors[] = "Sukurkite bent vieną ingridientą";
			} else {
				foreach($post['rec_ing_name'] as $k=>$v){
					if (empty($v)){
						$errors[] = "Įveskite ingridiento pavadinimą";
						break;
					}
				}
				foreach($post['rec_ing_amount'] as $k=>$v){
					if (empty($v)){
						$errors[] = "Įveskite ingridiento kiekįą";
						break;
					}
				}
			}
			$allowedExts = array("jpg", "jpeg", "gif", "png");
			foreach($_FILES as $key=>$file){
				if ($file["name"] != ""){
					$extension = end(explode(".", $file["name"]));
					if ((($file["type"] != "image/gif") && ($file["type"] != "image/jpeg") && ($file["type"] != "image/png")) || !in_array($extension, $allowedExts)){
						$errors[] = "Neleistinas failas. Galima įkelti tik PNG, GIF ir JPG failus";
						break;
					}
				}
			}
			if (sizeof($errors)>0){
				$this->view->errors = implode("\n", $errors);
			} else {
				$select = $db->select()
						->from("food_categories")
						->where("id = ?", array($post['rec_cat']));
				$cat = $db->FetchRow($select);
				$db->update("food_items", array(
					"cat_id" =>  (int)$post['rec_cat'],
					"parent_cat_id" =>  $cat['parent_id'],
					"title" =>  trim(strip_tags($post['rec_title'])),
					"description" => strip_tags($post['rec_description'], "<a><b><i>"),
					"modified" => date("Y-m-d H:i:s"),
				));
				$last_id = $item_id;
				$db->delete("food_ingridients", "recipe_id = '".$item_id."'");
				foreach($post['rec_ing_name'] as $key=>$val){
					$db->insert("food_ingridients", array(
						"recipe_id" =>  $last_id,
						"title" =>  trim(strip_tags($val)),
						"amount" =>  trim(strip_tags($post['rec_ing_amount'][$key]))
					));
				}
				if (isset($post['rec_style'])){
					$db->delete("food_styles", "recipe_id = '".$item_id."'");
					foreach($post['rec_style'] as $key=>$val){
						$db->insert("food_styles", array(
							"recipe_id" =>  $last_id,
							"style_id" =>  (int)$val
						));
					}
				}
				foreach($_FILES as $key=>$file){
					switch ($key){
						case "rec_image1":
							$i = 1;
						break;
						case "rec_image2":
							$i = 2;
						break;
						case "rec_image3":
							$i = 3;
						break;
					}
					if (isset($post['del_img'.$i])){
						$db->update("food_items", array("image".$i => ""), array("id = '" . $last_id . "'"));
						unlink($_SERVER['DOCUMENT_ROOT'] . "/food/" . $last_id . "/".$item['image'.$i]);
						unlink($_SERVER['DOCUMENT_ROOT'] . "/food/" . $last_id . "/t_".$item['image'.$i]);
					} else {
						if ($file['name'] != ""){
							$extension = strtolower(end(explode(".", $file["name"])));
							$name_s = $i.".".$extension;
							$fullFilePath = $_SERVER['DOCUMENT_ROOT'] . "/food/" . $last_id . "/".$name_s;
							$t_fullFilePath = $_SERVER['DOCUMENT_ROOT'] . "/food/" . $last_id . "/t_".$name_s;
							copy($file['tmp_name'], $fullFilePath);
							$db->update("food_items", array("image".$i => $name_s), array("id = '" . $last_id . "'"));

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
							$canvas_w = 245;
							$canvas_h = 176;

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

							switch(strtolower($file['type'])){
								case 'image/jpeg':
									imagejpeg($new, $t_fullFilePath);
									break;
								case 'image/png':
									imagepng($new, $t_fullFilePath);
									break;
								case 'image/gif':
									imagegif($new, $t_fullFilePath);
									break;
							}
						}
					}
				}
				$this->_redirect('/maistas/mano');
			}
		} else {
			$postdata['rec_title'] = $item['title'];
			$postdata['rec_cat'] = $item['cat_id'];
			$postdata['rec_description'] = $item['description'];
			$postdata['rec_image1'] = $item['image1'];
			$postdata['rec_image2'] = $item['image2'];
			$postdata['rec_image3'] = $item['image3'];
			$postdata['rec_ing_name'] = array();
			$postdata['rec_ing_amount'] = array();
			$postdata['rec_style'] = array();

			$select = $db->select()
					->from("food_ingridients")
					->where("recipe_id = ?", array($item_id));
			$ings = $db->FetchAll($select);
			foreach($ings as $key=>$val){
				$postdata['rec_ing_name'][] = $val['title'];
				$postdata['rec_ing_amount'][] = $val['amount'];
			}
			
			$select = $db->select()
					->from("food_styles")
					->where("recipe_id = ?", array($item_id));
			$stls = $db->FetchAll($select);
			foreach($stls as $key=>$val){
				$postdata['rec_style'][] = $val['style_id'];
			}

			$this->view->postdata = $postdata;
		}
		
		
		$select = $db->select()
				->from("food_categories")
				->order("title ASC")
				->where("parent_id = ?", array(0));
		$cats = $db->FetchAll($select);
		foreach($cats as $key=>$val){
			$select = $db->select()
					->from("food_categories")
					->order("title ASC")
					->where("parent_id = ?", array($val['id']));
			$cats[$key]['childs'] = $db->FetchAll($select);
		}
		$this->view->cats = $cats;

		$select = $db->select()
				->from("beer_cats")
				->order("cat_name ASC");
		$styles = $db->FetchAll($select);
		foreach($styles as $key=>$val){
			$select = $db->select()
					->from("beer_styles")
					->order("style_name ASC")
					->where("style_cat = ?", array($val['cat_id']));
			$styles[$key]['childs'] = $db->FetchAll($select);
		}
		$this->view->styles = $styles;
	}
}