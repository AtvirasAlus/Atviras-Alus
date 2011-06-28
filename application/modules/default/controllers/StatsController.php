<?php
class StatsController extends Zend_Controller_Action {
	function init() {
		 //$this->_helper->layout->setLayout('main');
	}
    public function indexAction() { 
    	       $db = Zend_Registry::get('db');
    	       $select=$db->select();
    	       $id= isset($_GET['id']) ? $_GET['id'] : "";
		switch($id) {

			case "sessions":
				$select->from("@days",array("day"=>"DATE"))
				->joinLeft("beer_brew_sessions","`@days`.DATE=beer_brew_sessions.session_primarydate",array("total"=>"COALESCE(sum(session_size),0)","avg"=>"COALESCE(sum(session_size)/count(distinct(session_brewer)),0)","count"=>"count(distinct(session_brewer))"))
				
				->where("`@days`.DATE >= ?",new Zend_Db_Expr("DATE(NOW())-(31*6)"))	
				->where("`@days`.DATE <= ?",new Zend_Db_Expr("NOW()"))
					->group("day");
				
				$this->view->sessions_count= Zend_Json::encode($db->fetchAll($select));
				
			break;
			case "users":
				$select->from("users",array("total"=>"count(user_id)","day"=>"DATE(user_created)"))
					->group("day");
				$this->view->user_count= Zend_Json::encode($db->fetchAll($select));
			break;
			default:    
				$select->from("beer_recipes",array("total"=>"count(recipe_id)","day"=>"DATE(recipe_created)"))
					->group("day");
				$this->view->recipes_count= Zend_Json::encode($db->fetchAll($select));
			break;
		}
    	    
    	    
    	      
    }
    public function sessionsAction() {
    	     $db = Zend_Registry::get('db');
    	     $select=$db->select();  
    	     $select->from("@days",array("day"=>"DATE"))
				->joinLeft("beer_brew_sessions","`@days`.DATE=beer_brew_sessions.session_primarydate",array("total"=>"COALESCE(sum(session_size),0)","avg"=>"COALESCE(sum(session_size)/count(distinct(session_brewer)),0)","count"=>"count(distinct(session_brewer))"))
				->where("`@days`.DATE >= ?",new Zend_Db_Expr("DATE(NOW())-(31*6)"))	
				->where("`@days`.DATE <= ?",new Zend_Db_Expr("NOW()"))
				->group("day");
print $select->__toString();
				//$this->view->sessions_count= Zend_Json::encode($db->fetchAll($select));
				
    }
     public function mysessionsAction() {
     	     $storage = new Zend_Auth_Storage_Session();
		$this->user = $storage->read();
		$user_id=false;
		if (isset($this->user->user_id )) {
			$user_id=$this->user->user_id;
		}
		if (isset($_GET['uid'])) {
		$user_id= $_GET['uid'] ;
		}
		if ($user_id) {
			
			$db = Zend_Registry::get('db');
    	       $select=$db->select();
    	       
    	    $select->from("beer_brew_sessions")
				->joinLeft("beer_recipes","beer_brew_sessions.session_recipe =beer_recipes.recipe_id",array("recipe_name"))
				->where("session_brewer =?",$user_id)
				->group("session_primarydate");
				$this->view->sessions_count= Zend_Json::encode($db->fetchAll($select));
		}
		
				
    }
    

}
?>
