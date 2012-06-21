<?php

class ContentController extends Zend_Controller_Action {

	public function readAction() {
		$storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $storage->read();
		$cat = $this->_getParam('cat');
		if ($cat != 0) {
			$article = explode("-", $this->_getParam('article'));
			$db = Zend_Registry::get("db");
			$select = $db->select()
					->from("beer_articles")
					->where("article_id =?", $article[0]);

			$this->_helper->viewRenderer("read" . $cat);
			$this->view->article = $db->fetchRow($select);
		}
	}

	public function articleAction() {
		
	}

	public function listAction() {
		$cat_page = explode("-", $this->_getParam('cat_page'));
		$this->view->articles = array();
		if ($cat_page[0]) {
			$db = Zend_Registry::get("db");
			$select = $db->select()
					->from("beer_articles")
					->joinLeft("beer_articles_comments", "beer_articles_comments.comment_article=beer_articles.article_id", array("COUNT(comment_id) AS total"))
					->group("beer_articles.article_id")
					->where("article_cat =?", $cat_page[0])
					->where("article_publish =?", '1')
					->order("article_modified DESC");
			$page = isset($cat_page[1]) ? $cat_page[0] : 1;
			$adapter = new Zend_Paginator_Adapter_DbSelect($select);
			$this->view->content = new Zend_Paginator($adapter);
			$this->view->content->setCurrentPageNumber($page);
			$this->view->content->setItemCountPerPage(100);
		}
	}

}