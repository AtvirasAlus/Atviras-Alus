<?
class Zend_View_Helper_EventComments extends Zend_View_Helper_Abstract{
public function  eventComments($event_id) {
		
		
		$this->view->addScriptPath(APPLICATION_PATH."/modules/default/views/helpers/");
		$db = Zend_Registry::get("db");
		$select=$db->select()
			->from("beer_events_comments")
			->joinLeft("users","users.user_id = beer_events_comments.comment_brewer",array("user_id","user_name","user_email"))
			->where("comment_event =?",$event_id)
			->order("comment_date ASC");
			$this->view->comments=$db->fetchAll($select);
			$this->view->event_id=$event_id;
			$out=$this->view->render("eventcomments.phtml");
		return 	$out;
		
	}
		
}
?>
