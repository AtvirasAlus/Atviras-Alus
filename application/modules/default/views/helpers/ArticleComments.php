<?
class Zend_View_Helper_ArticleComments extends Zend_View_Helper_Abstract{
public function  articleComments($article_id) {
		
		
		$this->view->addScriptPath(APPLICATION_PATH."/modules/default/views/helpers/");
		$db = Zend_Registry::get("db");
		$select=$db->select()
			->from("beer_articles_comments")
			->joinLeft("users","users.user_id = beer_articles_comments.comment_brewer",array("user_id","user_name","user_email"))
			->where("comment_article =?",$article_id)
			->order("comment_date DESC");
			$this->view->comments=$db->fetchAll($select);
			$this->view->article_id=$article_id;
			$out=$this->view->render("articlecomments.phtml");
		return 	$out;
		
	}
		
}
?>
