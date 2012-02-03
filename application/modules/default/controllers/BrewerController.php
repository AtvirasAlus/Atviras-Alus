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
		->joinLeft("users_attributes","users_attributes.user_id=users.user_id")
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
		$storage=new Zend_Auth_Storage_Session(); 
		$this->view->user_info=$storage->read();
		$u = $this->view->user_info;
		$this->view->changePasswordForm=new Form_Profile();
		
		$this->view->userAttributesForm=new Form_Attributes();
		$this->view->errors=array();
		
	 	if($this->getRequest()->isPost()){
			$action="";
			
			if (isset($_POST['action'])) {
			
				$action =$_POST['action'];
				if ($action=="attributes") {
          $form=$this->view->userAttributesForm;
          $form_valid=$form->isValid($_POST);
				}else if($action=="groups") {
           $form_valid=true;
				}
			}else{
				$action = "psw";
				$form=$this->view->changePasswordForm;
				$form_valid=$form->isValid($_POST);
			}
			if($form_valid){
				
				switch($action) {
				case "psw":
					
	
					if ($this->getRequest()->getPost('user_password')==$this->getRequest()->getPost('user_password_repeat')) {
						if ($this->updatePassword($u->user_id,$this->getRequest()->getPost('user_password_old'),$this->getRequest()->getPost('user_password'))) {
							$this->view->success="Slaptažodis pakeistas";
						}else{
							 $this->view->errors[] =  array("type"=>"system","message"=>"Neteisingas slaptažodis");
						}
				

					}else{
					 	$this->view->errors[] =  array("type"=>"system","message"=>"Slaptažodžiai nesutampa");
					}
				break;
				case "groups":
				if (isset($_POST['group'])) {
					$this->updateUserGroups($u->user_id,$_POST['group']);
					}else{
					$this->updateUserGroups($u->user_id,array());
					}
				break;
				case "attributes":
					$location = $_POST['user_location'];
					if ($_POST['use_other_location']=='1') {
						$location = $_POST['user_other_location'];
					}
					if ($this->updateAttributes($u->user_id,array('user_location'=>$location,'user_about'=>$_POST['user_about']))) {
					}else{
            $this->view->errors[] =  array("type"=>"system","message"=>"Išsaugoti informacijos nepavyko");
					}	
				break;
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
	$this->view->user_attributes=$this->getUserAttributes($u->user_id);
	$this->view->user_groups=$this->getUserAviableGroups($u->user_id);
    }
	private function updateUserGroups($user_id,$groups) {
		$db = Zend_Registry::get('db');
          	$where = array();
         	$where[] = $db->quoteInto('user_id = ?', $user_id);
		$db->delete("users_groups", $where);
		
		for ($i=0; $i<count($groups);$i++) {
		 $db->insert("users_groups",array("user_id"=>$user_id,"group_id"=>$groups[$i]));
		}
		return true;
	}	
	private function getUserAviableGroups($user_id) {
		$db = Zend_Registry::get('db');
		$select=$db->select()
		->from("users_groups",array("user_id"=>"concat('0')"))
		->joinRight("groups", "groups.group_id=users_groups.group_id")
		->where("groups.group_public = ?","1")
		->where("groups.group_registration = ?","public")
		->orWhere("users_groups.user_id = ?",$user_id)
		->group("groups.group_id");
		$aviable_gr=$db->fetchAll($select);
		$select=$db->select()
			->from("users_groups")
			->where("user_id = ?",$user_id);
			$subscribed_gr=$db->fetchAll($select);
    for ($i=0;$i<count($aviable_gr);$i++) {
         for ($ii=0;$ii<count($subscribed_gr);$ii++) {
           if ($aviable_gr[$i]["group_id"]==$subscribed_gr[$ii]["group_id"]) {
            $aviable_gr[$i]["user_id"]=$user_id;
           
           }
         }
    }
		
		return $aviable_gr;
	
	}
   	private function getUserAttributes($user_id) {	
		$db = Zend_Registry::get('db');
		$select=$db->select()
		->from("users_attributes")
		->where("user_id = ?",$user_id);
		if ($row=$db->fetchRow($select)) {
			
		}else{
			$row= array("user_id"=>$user_id,"user_location"=>"","user_about"=>"");
		}
		$row["user_about"]=preg_replace('/((?:[^"\'])(?:http|https|ftp):\/\/(?:[A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?[^\s\"\']+)/i','<a href="$1" rel="nofollow" target="blank">$1</a>',nl2br($row["user_about"]));
		return $row;
	}
	private function updateAttributes($user_id,$att) {
		$db = Zend_Registry::get('db');
          	$where = array();
         	$where[] = $db->quoteInto('user_id = ?', $user_id);
		$db->delete("users_attributes", $where);
		$stripTags = new Zend_Filter_StripTags( array('p','b','br','strong'),array()); 
		$user_about = $stripTags->filter($att["user_about"]);
		
		return $db->insert("users_attributes",array("user_id"=>$user_id,"user_location"=>$att["user_location"],"user_about"=>$user_about));
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

					 
