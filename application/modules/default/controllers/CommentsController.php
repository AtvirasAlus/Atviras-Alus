<?php

class CommentsController extends Zend_Controller_Action {

	function init() {
		//$this->_helper->layout->setLayout('main');
	}

	function indexAction() {
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("beer_recipes_comments", array("comment_id", "comment_brewer", "comment_recipe", "comment_date" => "MAX(comment_date)"))
				->join("VIEW_public_recipes", "VIEW_public_recipes.recipe_id=beer_recipes_comments.comment_recipe", array("recipe_name", "recipe_id"))
				->join("users", "beer_recipes_comments.comment_brewer=users.user_id", array("user_name", "user_email", "user_id"))
				->group("comment_id")
				->order("comment_date DESC");
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));

		$this->view->content->setItemCountPerPage(100);
	}

	function submitAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		if (isset($_POST)) {
			$db = Zend_Registry::get('db');
			if (isset($_POST['recipe_id'])) {
				$db->insert("beer_recipes_comments", array("comment_recipe" => $_POST['recipe_id'], "comment_brewer" => $_POST['brewer_id'], "comment_text" => strip_tags($_POST['comment'], '<a>')));
				Entities_Events::trigger("new_recipe_comment", array("comment_recipe" => $_POST['recipe_id'], "comment_brewer" => $_POST['brewer_id'], "comment_text" => strip_tags($_POST['comment'], '<a>')));
				$this->_redirect("/recipes/view/" . $_POST['recipe_id']);
			} else {
				$db->insert("beer_articles_comments", array("comment_article" => $_POST['article_id'], "comment_brewer" => $_POST['brewer_id'], "comment_text" => strip_tags($_POST['comment'], '<a>')));
				$this->_redirect("/content/read/1/" . $_POST['article_id']);
			}
		} else {
			$this->_redirect("/");
		}
	}

	function deleteAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		if (isset($_POST)) {
			$db = Zend_Registry::get('db');
			if (isset($_POST["article"])) {
				$db->delete("beer_articles_comments", array("comment_id = " . $_POST['comment_id']));
			} else {
				$db->delete("beer_recipes_comments", array("comment_id = " . $_POST['comment_id']));
			}
		} else {
			
		}
		print Zend_Json::encode(array("status" => 0, "data" => array()));
	}

}