<?php
class BrewerController extends Zend_Controller_Action {
	 public function init()
      {
      }
    public function indexAction() { 
    	  
    	 
    	   
    }
    public function infoAction() {
    	    $db = Zend_Registry::get('db');
    	    $this->view->user_info=array("total_sessions"=>0,"total_brewed"=>0,"total_recipes"=>0,"user_lastlogin"=>0,"user_created"=>0,"user_name"=>'');
    	    if ($this->_getParam('brewer') > 0) {   
		$brewer=$this->_getParam('brewer');
		$select=$db->select()
		->from("users")
		->where("users.user_active = ?", '1')
		->where("users.user_id = ?",$brewer);
			 $this->view->user_info=$db->fetchRow($select);
			 if ( $this->view->user_info) {
				$select=$db->select()
				->from("beer_recipes",array("count"=>"count(*)"))
				->where("beer_recipes.recipe_publish = ?", '1')
				->where("beer_recipes.brewer_id= ?",$brewer);
				if ($row=$db->fetchRow($select)) {
					$this->view->user_info["total_recipes"]=$row["count"];	
				}
				$select=$db->select()
				->from("beer_brew_sessions",array("count"=>"count(*)","total"=>"COALESCE(sum(session_size),0)"))
				->where("session_brewer =?",$brewer);
				if ($row=$db->fetchRow($select)) {
					$this->view->user_info["total_brewed"]=$row["total"];	
					$this->view->user_info["total_sessions"]=$row["count"];	
				}
				$select=$db->select()
				->from("beer_recipes")
				->where("beer_recipes.recipe_publish = ?", '1')
				->where("beer_recipes.brewer_id= ?",$brewer);
				$this->view->user_info["recipes"]=array();
				if ($rows=$db->fetchAll($select)) {
					$this->view->user_info["recipes"]=$rows;	
				}
				$select=$db->select()
				->from("beer_recipes_comments",array("comment_text","comment_date"))
				->join("beer_recipes","comment_recipe=recipe_id",array("recipe_name","recipe_id"))
				->where("comment_brewer = ?",$brewer)
				->order("comment_date desc")
				->limit(10);
					$this->view->user_info["recipes_comments"]=array();
          if ($rows=$db->fetchAll($select)) {
            $this->view->user_info["recipes_comments"]=$rows;	
          }
			 }
	    }
    	   
    }
    public function listAction() {
		$db = Zend_Registry::get('db');
		$select=$db->select()
		->from("users" ,array("user_name","user_id","user_email"))
		->joinLeft("VIEW_public_recipes","VIEW_public_recipes.brewer_id=users.user_id",array("count"=>"count(VIEW_public_recipes.recipe_id)"))
		->where("users.user_active = ?", '1')
		->group("users.user_id")
		->order("user_lastlogin DESC")
		->order("count DESC")
		->order("recipe_created DESC")
		->order("user_name ASC");
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));
		$this->view->content->setItemCountPerPage(40);
    }
    public function profileAction() { 
		$form=new Form_Profile();
		$this->view->changePasswordForm=$form;
		$this->view->errors=array();
		$storage=new Zend_Auth_Storage_Session(); 
		$this->view->user_info=$storage->read();
	
	 	if($this->getRequest()->isPost()){
			if($form->isValid($_POST)){
				$storage = new Zend_Auth_Storage_Session(); 
	$u=$storage->read(); 
	
				if ($this->getRequest()->getPost('user_password')==$this->getRequest()->getPost('user_password_repeat')) {
					if ($this->updatePassword($u->user_id,$this->getRequest()->getPost('user_password_old'),$this->getRequest()->getPost('user_password'))) {
						$this->view->success="Slaptažodis pakeistas";
				}else{
					 $this->view->errors[] =  array("type"=>"system","message"=>"Neteisingas slaptažodis");
				}

}else{
 $this->view->errors[] =  array("type"=>"system","message"=>"Slaptažodžiai nesutampa");
}
			}else{
			$err_codes=new Entities_FormErrors();
		      foreach ($form->getErrors() as $key => $error) {
		          if (count($error) > 0) {
		          	  $this->view->errors[] =  array("type"=>"form","message"=>$form->getElement($key)->getLabel() ." - ". $err_codes->getError($error[0]));
		          }
		      }
			
		}
		
	 }
    }
   
     private function updatePassword($user_id, $old_password, $new_password) {
     	  
          $db = Zend_Registry::get('db');
          $where = array();
          $where[] = $db->quoteInto('user_id = ?', $user_id);
          $where[] = $db->quoteInto('user_password = ?', md5($old_password));
          return $db->update("users",array('user_password' => md5($new_password)), $where);
      }
    
    public function recipesAction() { 
		$storage = new Zend_Auth_Storage_Session(); 
		$u=$storage->read();
		$db = Zend_Registry::get('db');
		if ($this->_getParam('brewer') > 0) {
			$brewer=$this->_getParam('brewer');
			//$this->_helper->viewRenderer->render("../recipes/index");
			$this->_helper->viewRenderer("public");
			$select=$db->select()
			->from("beer_recipes",array("count"=>"count(*)"))
			->where("beer_recipes.recipe_publish = ?", '1')
			->where("beer_recipes.brewer_id= ?",$brewer);
			$total=$db->fetchRow($select);
		}else{ 
			if (isset($u->user_id)) {
				$brewer=$u->user_id;
				$select=$db->select()
				->from("beer_recipes",array("count"=>"count(*)"))
				->where("beer_recipes.brewer_id= ?",$brewer);
				$total=$db->fetchRow($select);
			
			}
		}
		$select=$db->select()
		->from("users")
		->where("user_id = ?",$brewer);
		$brewer=$db->fetchRow($select);
		if (isset($brewer)) {
		$select=$db->select()
		->from("beer_recipes")
		->join("users","users.user_id = beer_recipes.brewer_id",array("user_name"))
		->joinLeft("beer_styles","beer_recipes.recipe_style = beer_styles.style_id",array("style_name"));
		if ($this->_getParam('brewer') > 0) {
			$select->where("beer_recipes.recipe_publish = ?", '1');
		}
		$select->where("beer_recipes.brewer_id= ?",$brewer["user_id"]);
		$select->order("beer_recipes.recipe_created DESC");
		
	   //$this->view->recipes=$db->fetchAll($select);
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));
		$this->view->content->setItemCountPerPage(15);
		$this->view->brewer=$brewer;
		$this->view->brewer["total"]=$total["count"];
		}
		
    }
  

}
?>

					 
