<?php

class AdminController extends Zend_Controller_Action {

	public function init() {
		$storage = new Zend_Auth_Storage_Session();
		$this->user = $storage->read();
		if (!isset($this->user->user_id)) {
			$this->_redirect("/index");
		} else {
			if ($this->user->user_type != "admin") {
				$this->_redirect("/index");
			}
		}
	}

	public function indexAction() {
		
	}

	public function loadArticlesAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		if (isset($_POST)) {
			$db = Zend_Registry::get("db");
			$select = $db->select()
					->from("beer_articles")
					->where("article_cat =?", $_POST['article_cat'])
					->order("article_modified");
			print Zend_Json::encode(array("status" => 0, "data" => $db->fetchAll($select)));
		}
	}

	public function saveArticleAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		if (isset($_POST)) {
			$db = Zend_Registry::get("db");
			if (isset($_POST['article_id'])) {
				if ($db->update("beer_articles", array("article_id" => $_POST['article_id'], "article_title" => $_POST['article_title'], "article_resume" => $_POST['article_resume'], "article_text" => $_POST['article_text']), "article_id =" . $_POST['article_id'])) {
					print Zend_Json::encode(array("status" => 0));
					return;
				}
			} else {
				if ($db->insert("beer_articles", array("article_title" => $_POST['article_title'], "article_resume" => $_POST['article_resume'], "article_text" => $_POST['article_text']))) {
					print Zend_Json::encode(array("status" => 0));
					return;
				}
			}
			print Zend_Json::encode(array("status" => 1));
		}
	}

	public function loadArticleAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		if (isset($_POST)) {
			$db = Zend_Registry::get("db");
			$select = $db->select()
					->from("beer_articles")
					->where("article_id =?", $_POST['article_id']);
			print Zend_Json::encode(array("status" => 0, "data" => $db->fetchRow($select)));
		}
	}

	public function articlesAction() {
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("beer_articles_cats")
				->where("cat_parent =?", 0)
				->order("cat_name");
		$this->view->article_cats = $db->fetchAll($select);
	}

	public function modStylesAction() {
		if (isset($_POST)) {
			$db = Zend_Registry::get("db");
			$db->update('beer_styles', array("style_cat" => $_POST["style_cat"], "style_name" => $_POST["style_name"], "style_name_en" => $_POST["style_name_en"]), "style_id = '" . $_POST["style_id"] . "'");
			$db->delete('beer_styles_description', "style_id = '" . $_POST["style_id"] . "'");
			$db->insert('beer_styles_description', array("style_aroma" => $_POST["style_aroma"], "style_mouthfeel" => $_POST["style_mouthfeel"], "style_appearance" => $_POST["style_appearance"], "style_ingredients" => $_POST["style_ingredients"], "style_flavor" => $_POST["style_flavor"], "style_history" => $_POST["style_history"], "style_id" => $_POST["style_id"], "style_aroma_en" => $_POST["style_aroma_en"], "style_mouthfeel_en" => $_POST["style_mouthfeel_en"], "style_appearance_en" => $_POST["style_appearance_en"], "style_ingredients_en" => $_POST["style_ingredients_en"], "style_flavor_en" => $_POST["style_flavor_en"], "style_history_en" => $_POST["style_history_en"]));
		}
		$this->_helper->viewRenderer->setNoRender();

		$r = new Zend_Controller_Action_Helper_Redirector();

		$r->gotoUrl('/admin/edit-styles?style_id=' . $_POST['style_id'])->redirectAndExit();
	}

	public function editStylesAction() {
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("beer_cats")
				->order("cat_name_en");
		$this->view->beer_cats = $db->fetchAll($select);
		$select = $db->select()
				->from("beer_styles_description", array("count" => "count(*)"))
				->where("style_ingredients !=''");
		$this->view->total_translated = $db->fetchRow($select);
		$select = $db->select()
				->from("beer_styles", array("style_cat", "style_name", "style_name_en", "style_id"))
				->order("style_name");
		$this->view->beer_styles = $db->fetchAll($select);
		if (isset($_GET["style_id"])) {
			$select = $db->select()
					->from("beer_styles", array("style_cat", "style_name", "style_name_en", "style_id"))
					->joinLeft("beer_styles_description", "beer_styles_description.style_id=beer_styles.style_id", array("style_aroma", "style_mouthfeel", "style_appearance", "style_ingredients", "style_flavor", "style_history", "style_aroma_en", "style_mouthfeel_en", "style_appearance_en", "style_ingredients_en", "style_flavor_en", "style_history_en"))
					->where("beer_styles.style_id= ?", ($_GET["style_id"]));
			$this->view->current_style = $db->fetchRow($select);
		}
	}

}