<?
class Zend_View_Helper_IdeaComments extends Zend_View_Helper_Abstract{
public function  ideaComments($idea_id) {
		$this->view->addScriptPath(APPLICATION_PATH."/modules/default/views/helpers/");
		$db = Zend_Registry::get("db");
		$select=$db->select()
			->from("idea_comments")
			->joinLeft("users","users.user_id = idea_comments.user_id",array("user_name","user_email"))
			->where("idea_id =?",$idea_id)
			->order("comment_date ASC");
			$this->view->comments=$db->fetchAll($select);
			$this->view->idea_id=$idea_id;
			$out=$this->view->render("idea_comments.phtml");
		return 	$out;
		
	}
		
}
?>
