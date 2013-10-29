<?

class Zend_View_Helper_MarketComments extends Zend_View_Helper_Abstract {

	public function marketComments($market_id) {
		$this->view->addScriptPath(APPLICATION_PATH . "/modules/default/views/helpers/");
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("market_comments")
				->joinLeft("users", "users.user_id = market_comments.user_id", array("user_id", "user_name", "user_email"))
				->where("market_id =?", $market_id)
				->order("comment_date ASC");
		$this->view->comments = $db->fetchAll($select);
		$this->view->market_id = $market_id;
		$out = $this->view->render("market_comments.phtml");
		return $out;
	}

}

?>
