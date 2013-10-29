<?
class Zend_View_Helper_FoodComments extends Zend_View_Helper_Abstract{
public function  foodComments($food_id) {
		$this->view->addScriptPath(APPLICATION_PATH."/modules/default/views/helpers/");
		$db = Zend_Registry::get("db");
		$select=$db->select()
			->from("food_comments")
			->joinLeft("users","users.user_id = food_comments.user_id",array("user_name","user_email"))
			->where("food_id =?",$food_id)
			->order("comment_date ASC");
			$this->view->comments=$db->fetchAll($select);
			$this->view->food_id=$food_id;
			$out=$this->view->render("food_comments.phtml");
		return 	$out;
		
	}
		
}
?>
