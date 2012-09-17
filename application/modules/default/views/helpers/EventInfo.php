<?
class Zend_View_Helper_EventInfo extends Zend_View_Helper_Abstract{
	public $view; 
	
	public function eventInfo() {
		 $this->db = Zend_Registry::get("db");
      	
		
		$this->view->addScriptPath(APPLICATION_PATH."/modules/default/views/helpers/eventInfo/");
		return $this;
	}
	
	public function showCompetitionDetails($event_id,$title="") {
    $select =$this->db->select()
    	->from("beer_competition_entries",array("count"=>"count(beer_competition_entries.style_id)"))
			  ->joinLeft("beer_styles","beer_competition_entries.style_id=beer_styles.style_id",array())
			  ->joinLeft("beer_cats","beer_cats.cat_id=beer_styles.style_cat",array("name"=>"cat_name")) 
			  ->where("beer_competition_entries.event_id = ?",$event_id)
			  ->group("beer_styles.style_cat")
			  ->order("count DESC");
			  $this->view->title =$title;
			  $this->view->categories = $this->db->fetchAll($select);
        return $this->view->render("competition.phtml");
		 
	}
	public function showExhibitionDetails($event_id,$title="") {
    $select =$this->db->select()
    	->from("beer_competition_entries",array())
			  ->joinLeft("beer_styles","beer_competition_entries.style_id=beer_styles.style_id")
			   ->joinLeft("beer_recipes","beer_competition_entries.recipe_id=beer_recipes.recipe_id")
			  ->joinLeft("users","beer_competition_entries.event_user_id=users.user_id") 
			  ->where("beer_competition_entries.event_id = ?",$event_id);
			
		
			  $this->view->title =$title;
			  $this->view->recipes = $this->db->fetchAll($select);
        return $this->view->render("exhibition.phtml");
		 
	}
	
	public function setView(Zend_View_Interface $view) 
	{ 

	$this->view = $view;
	 
	} 
}
?>
