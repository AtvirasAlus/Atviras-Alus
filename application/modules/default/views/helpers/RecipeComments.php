<?
class Zend_View_Helper_RecipeComments extends Zend_View_Helper_Abstract{
public function  recipeComments($recipe_id) {
		
		
		$this->view->addScriptPath(APPLICATION_PATH."/modules/default/views/helpers/");
		$db = Zend_Registry::get("db");
		$select=$db->select()
			->from("beer_recipes_comments")
			->joinLeft("users","users.user_id = beer_recipes_comments.comment_brewer",array("user_id","user_name","user_email"))
			->where("comment_recipe =?",$recipe_id)
			->order("comment_date ASC");
			$this->view->comments=$db->fetchAll($select);
			$this->view->recipe_id=$recipe_id;
			$out=$this->view->render("comments.phtml");
		return 	$out;
		
	}
		
}
?>
