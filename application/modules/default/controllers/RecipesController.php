<?php
class RecipesController extends Zend_Controller_Action {
	function init() {
		 //$this->_helper->layout->setLayout('main');
	}
    public function indexAction() { 
		$db = Zend_Registry::get('db');
		$select=$db->select()
		->from("beer_recipes")
		->join("users","users.user_id = beer_recipes.brewer_id",array("user_name"))
		->joinLeft("beer_styles","beer_recipes.recipe_style = beer_styles.style_id",array("style_name"))
		->where("beer_recipes.recipe_publish = ?", '1')
		->order("beer_recipes.recipe_created DESC");
		
		//$this->view->recipes=$db->fetchAll($select);
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));

		$this->view->content->setItemCountPerPage(21);
    	   
    }
    public function stylesAction() {
    	$db = Zend_Registry::get("db");
      $select=$db->select()
			->from("beer_recipes")
			->join("users","users.user_id = beer_recipes.brewer_id",array("user_name"))
			->joinLeft("beer_styles","beer_recipes.recipe_style = beer_styles.style_id",array("style_name"));
			$select->where("beer_recipes.recipe_publish = ?", '1');
			$select->where("beer_recipes.recipe_style= ?",$this->_getParam('style'));
			$select->order("beer_recipes.recipe_created DESC");
			$adapter = new Zend_Paginator_Adapter_DbSelect($select);
			$this->view->content = new Zend_Paginator($adapter);
			$this->view->content->setCurrentPageNumber($this->_getParam('page'));
			$this->view->content->setItemCountPerPage(15);
			$select=$db->select()
			->from("beer_styles")
			->where("beer_styles.style_id= ?",$this->_getParam('style'));
			$this->view->beer_style=$db->fetchRow($select);
			$this->_helper->viewRenderer("index");	
    }
    public function searchAction() {
    	    $db = Zend_Registry::get("db");
		$params=explode("|",$this->getRequest()->getParam("params"));
		$mask=array("type"=>"recipe_type","style"=>"recipe_style","name"=>"recipe_name","hops"=>"hop_name","malts"=>"malt_name","yeasts"=>"yeast_name","brewer"=>"user_name","tags"=>"tag_text");
		$select=$db->select()
			->from("beer_styles" ,array("style_name","style_id","style_cat"))
			->joinLeft("VIEW_public_recipes","VIEW_public_recipes.recipe_style=beer_styles.style_id",array("count"=>"count(VIEW_public_recipes.recipe_id)"))
			->group("beer_styles.style_id")
			->order("style_name");
			$this->view->beer_styles=$db->fetchAll($select);
		$filter=array();
		for ($i=0;$i<count($params);$i++) {
			$c=explode(":",$params[$i]);
			if (isset($mask[$c[0]])) {
				if (isset($c[1])) {
					$filter[$mask[$c[0]]]=$c[1];
				}
			}
		}
		
		$select=$db->select();
		$select->from("beer_recipes")
		->join("users","users.user_id=beer_recipes.brewer_id",array("user_name"))
		->joinLeft("beer_styles","beer_styles.style_id=beer_recipes.recipe_style",array("style_name"));
		if (!empty($filter)) {
			if (isset($filter["hop_name"])) {
				$select->joinLeft("beer_recipes_hops","beer_recipes_hops.recipe_id=beer_recipes.recipe_id");
			}
		}
		if (!empty($filter)) {
			if (isset($filter["tag_text"])) {
				$select->joinLeft("beer_recipes_tags","beer_recipes_tags.tag_recipe_id=beer_recipes.recipe_id");
			}
			}
		if (!empty($filter)) {
			if (isset($filter["malt_name"])) {
				$select->joinLeft("beer_recipes_malt","beer_recipes_malt.recipe_id=beer_recipes.recipe_id");
			}
		}
		if (!empty($filter)) {
			if (isset($filter["yeast_name"])) {
				$select->joinLeft("beer_recipes_yeast","beer_recipes_yeast.recipe_id=beer_recipes.recipe_id");
			}
		}
		
		
		if (!empty($filter)) {
			if (isset($filter["recipe_type"])) {
				$select->where("recipe_type = ?",$filter["recipe_type"]);
			}else{
				$filter["recipe_type"]=0;
			}
			if (isset($filter["recipe_style"])) {
				$select->where("recipe_style = ?",intval($filter["recipe_style"]));
			}else{
				$filter["recipe_style"]=0;
			}
			if (isset($filter["recipe_name"])) {
					$select->where("recipe_name LIKE '%".$filter["recipe_name"]."%'");
			}else{
				$filter["recipe_name"]="";
			}
			
			if (isset($filter["hop_name"])) {
				$hops=explode(",",$filter["hop_name"]);
				for ($i=0;$i<count($hops);$i++) {
					if (strlen(trim($hops[$i]))>0) {
					$select->where("hop_name LIKE '%".trim($hops[$i])."%'");
					}
				}
				
			}else{
				$filter["hop_name"]="";
			}
			if (isset($filter["tag_text"])) {
				$tags=explode(",",$filter["tag_text"]);
				for ($i=0;$i<count($tags);$i++) {
					if (strlen(trim($tags[$i]))>0) {
					$select->where("tag_text LIKE '%".trim($tags[$i])."%'");
					}
				}
				
			}else{
				$filter["tag_text"]="";
			}
			if (isset($filter["malt_name"])) {
				$malt=explode(",",$filter["malt_name"]);
				for ($i=0;$i<count($malt);$i++) {
					if (strlen(trim($malt[$i]))>0) {
					$select->where("malt_name LIKE '%".trim($malt[$i])."%'");
					}
				}
				
			}else{
				$filter["malt_name"]="";
			}
			if (isset($filter["yeast_name"])) {
				$yeast=explode(",",$filter["yeast_name"]);
				for ($i=0;$i<count($yeast);$i++) {
					if (strlen(trim($yeast[$i]))>0) {
					$select->where("yeast_name LIKE '%".trim($yeast[$i])."%'");
					}
				}
				
			}else{
				$filter["yeast_name"]="";
			}
			if (isset($filter["user_name"])) {
			
					$select->where("user_name LIKE '%".$filter["user_name"]."%'");
					
				
				
			}else{
				$filter["user_name"]="";
			}
			$select->where("recipe_publish = ?",'1');
			$select->group("beer_recipes.recipe_id");
			$select->order("beer_recipes.recipe_name");
			$adapter = new Zend_Paginator_Adapter_DbSelect($select);
			$this->view->content = new Zend_Paginator($adapter);
			$this->view->content->setCurrentPageNumber($this->_getParam('page'));
			$this->view->content->setItemCountPerPage(15);
			//print $select->__toString();
			
		}else{
			$filter=array("recipe_style"=>0,"recipe_type"=>0,"recipe_name"=>"","hop_name"=>"","yeast_name"=>"","malt_name"=>"","user_name"=>"");
		}
		$this->view->filter_values=$filter;
			
		
    }
   
    public function modTagsAction() {
      $this->_helper->layout->setLayout('empty');
      $this->_helper->viewRenderer->setNoRender(true);
      $storage = new Zend_Auth_Storage_Session(); 
      $u=$storage->read(); 
      $tags = isset($_POST['tags']) ? $_POST['tags'] : '';
      $db = Zend_Registry::get("db");
      if ($u->user_id) {
      $tags = isset($_POST['tags']) ? $_POST['tags'] : '';
     	$db->delete("beer_recipes_tags","tag_recipe_id = ".$_POST['recipe_id'].' and tag_owner = '.$u->user_id); 
     	$tags_array=explode(",",$tags);
     	foreach ($tags_array as  $tag) {
        $db->insert("beer_recipes_tags",array("tag_recipe_id" => $_POST['recipe_id'], "tag_owner"=> $u->user_id, "tag_text"=>$tag));
     	}
       print Zend_Json::encode(array("tags"=>$tags));
       }else{
       print Zend_Json::encode(array("result"=>1));
       }
       
    }
    public function cloudAction() {
    	   //  $this->_helper->layout->setLayout('empty');
    	    	    $db = Zend_Registry::get("db");
    	    	    $select=$db->select();
    	    	    $select->from("beer_recipes")
    	    	    ->where('recipe_publish =?','1');
    	    	    if (isset($_GET['id'])) {
    	    	    	    $select->where("brewer_id=?",$_GET['id']);
    	    	    }
    	    	    $select->limit(25);
    	    	    $this->view->words=$db->fetchAll($select);
    	    
    }
    public function viewAction() { 
    $storage = new Zend_Auth_Storage_Session(); 
    
     $ruid=explode("-",$this->_getParam('recipe'));
     $recipe_id=$ruid[0];
    $this->view->data=array();
    if ($recipe_id>0) {	
	$db = Zend_Registry::get("db");
	$select=$db->select();
	$select->from("beer_recipes")
	->join("users","users.user_id=beer_recipes.brewer_id",array("user_name"))
	->joinLeft("beer_styles","beer_styles.style_id=beer_recipes.recipe_style",array("style_name"))
	->where("recipe_id = ?",$recipe_id);
	$this->view->data["recipe"]=$db->fetchRow($select);
	$select=$db->select();
	$select->from("beer_recipes_malt")
	->where("recipe_id = ?",$recipe_id)
	->order("malt_weight DESC");
	
	$this->view->data["malt"]=$db->fetchAll($select);
	$select=$db->select();
	$select->from("beer_recipes_hops")
	->where("recipe_id = ?",$recipe_id)
	->order("hop_time DESC");
	$this->view->data["hops"]=$db->fetchAll($select);
	$select=$db->select();
	$select->from("beer_recipes_yeast")
	->where("recipe_id = ?",$recipe_id);
	$this->view->data["yeast"]=$db->fetchAll($select);
	
	$this->view->user_info=$storage->read();
	$select=$db->select();
	$select->from("beer_brew_sessions",array("count"=>"count(*)"))
	->where("session_recipe =?",$recipe_id);
	$this->view->data["brew_session"]=$db->fetchRow($select);
	$this->view->tags="";
	$select=$db->select();
	$select->from("beer_recipes_tags",array("tags"=>"group_concat(tag_text)"))
	->where("tag_recipe_id =?",$recipe_id);
	
	if ($tags=$db->fetchRow($select)) {
	$this->view->tags=$tags['tags'];
	}
	
	$user_id=0;
	if ($this->view->user_info) {
		$user_id=$this->view->user_info->user_id;
	}
	$this->view->recipe_votes=array("total"=>$this->getVotes($recipe_id),"user_vote"=>$this->getUserVotes($recipe_id,$user_id));
		  	 
	
	
    }
    }
    public function favoritesAction() {
    	$db = Zend_Registry::get('db');
      $select=$db->select();
        $select=$db->select() 
			 ->from('VIEW_fav_recipes',array("votes"))
			 ->join("beer_recipes","beer_recipes.recipe_id = VIEW_fav_recipes.recipe_id")
			 ->join("beer_styles","beer_styles.style_id = beer_recipes.recipe_style",array("style_name"))
			 ->join("users","users.user_id=VIEW_fav_recipes.brewer_id",array("user_name","user_email"));
			 	$adapter = new Zend_Paginator_Adapter_DbSelect($select);
			$this->view->content = new Zend_Paginator($adapter);
			$this->view->content->setCurrentPageNumber($this->_getParam('page'));
			$this->view->content->setItemCountPerPage(100);
    }
    public function getVotes($recipe_id=0) {
	$db = Zend_Registry::get('db');
	$select=$db->select();
	$select->from("beer_recipes_favorites",array("count"=>"count(*)"))
	->where("recipe_id =?",$recipe_id);
	$fv=$db->fetchRow($select);
	return $fv["count"];
    }
     public function getUserVotes($recipe_id=0,$user_id=0) {
	$db = Zend_Registry::get('db');
	$select=$db->select();
	$select->from("beer_recipes_favorites",array("count"=>"count(*)"))
	->where("recipe_id =?",$recipe_id)
	->where("user_id =?",$user_id);
	$fv=$db->fetchRow($select);
	return $fv["count"];
    }
    public function voteAction() {
    	    	$db = Zend_Registry::get('db');
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$storage = new Zend_Auth_Storage_Session(); 
		$u=$storage->read();
		  if (isset($u->user_name)) {
			if (isset($_POST)) {
				$db->delete("beer_recipes_favorites","recipe_id = ".$_POST['id'].' and user_id = '.$u->user_id); 
				switch($_POST['action']) {
				
				case "vote_up":
					$db->insert("beer_recipes_favorites",array("recipe_id"=>$_POST['id'],"user_id"=>$u->user_id)); 
					
					break;
				}
				 print Zend_Json::encode(array("status"=>0,"data"=>array("votes"=>$this->getVotes($_POST['id']))));
			}
		  }else{
		  	    print Zend_Json::encode(array("status"=>1,"errors"=>array(array("message"=>"Neregistruotas vartotojas","type"=>"authentication"))));
		  }
    }
      public function publishAction() {
    		if (isset($_POST)) {
			$this->_helper->layout->setLayout('empty');
			$this->_helper->viewRenderer->setNoRender(true);
		  	  $storage = new Zend_Auth_Storage_Session(); 
		  	  $u=$storage->read();
		  	  if (isset($u->user_name)) {
		  	  	   $db = Zend_Registry::get('db');
		  	  	   if (strlen($_POST["recipe_id"])>0) {
		  	  	   $select=$db->select();
		  	  	   $select->from("beer_recipes",array("recipe_id"))
		  	  	   ->where("brewer_id = ?",$u->user_id)
		  	  	   ->where("recipe_id = ?",$_POST["recipe_id"]);
		  	  	   $r=$db->fetchRow($select);
		  	  	   if (isset($r)) {
			  	  	   $db->update("beer_recipes",array("recipe_publish"=>$_POST['publish']),"recipe_id = ".$r['recipe_id']);
			  	  	  
			  	  	   print Zend_Json::encode(array("status"=>0));
			  	  	   return;
		  	  	   }else{
		  	  	  	 
		  	  	   }
		  	  	   }print Zend_Json::encode(array("status"=>1,"errors"=>array(array("message"=>"Receptas nerastas","type"=>"system"))));
		  	  }else{
		  	  print Zend_Json::encode(array("status"=>1,"errors"=>array(array("message"=>"Neregistruotas vartotojas","type"=>"authentication"))));
		  	  }
	}
    }
    public function deleteAction() {
    		if (isset($_POST)) {
			$this->_helper->layout->setLayout('empty');
			$this->_helper->viewRenderer->setNoRender(true);
		  	  $storage = new Zend_Auth_Storage_Session(); 
		  	  $u=$storage->read();
		  	  if (isset($u->user_name)) {
		  	  	   $db = Zend_Registry::get('db');
		  	  	   if (strlen($_POST["recipe_id"])>0) {
		  	  	   $select=$db->select();
		  	  	   $select->from("beer_recipes",array("recipe_id"))
		  	  	   ->where("brewer_id = ?",$u->user_id)
		  	  	   ->where("recipe_id = ?",$_POST["recipe_id"]);
		  	  	   $r=$db->fetchRow($select);
		  	  	   if (isset($r)) {
			  	  	   $db->delete("beer_recipes","recipe_id = ".$r['recipe_id']);
			  	  	   $db->delete("beer_recipes_malt","recipe_id = ".$r['recipe_id']);
			  	  	   $db->delete("beer_recipes_hops","recipe_id = ".$r['recipe_id']);
			  	  	   $db->delete("beer_recipes_yeast","recipe_id = ".$r['recipe_id']);
			  	  	   $db->delete("beer_recipes_comments","comment_recipe = ".$r['recipe_id']);
			  	  	   $db->delete("beer_recipes_favorites","recipe_id = ".$r['recipe_id']);
			  	  	   print Zend_Json::encode(array("status"=>0));
			  	  	   return;
		  	  	   }else{
		  	  	  	 
		  	  	   }
		  	  	   }print Zend_Json::encode(array("status"=>1,"errors"=>array(array("message"=>"Receptas nerastas","type"=>"system"))));
		  	  }else{
		  	  print Zend_Json::encode(array("status"=>1,"errors"=>array(array("message"=>"Neregistruotas vartotojas","type"=>"authentication"))));
		  	  }
	}
    }
  	public function saveAction() {
	  	if (isset($_POST)) {
			$this->_helper->layout->setLayout('empty');
			$this->_helper->viewRenderer->setNoRender(true);
		  	  $storage = new Zend_Auth_Storage_Session(); 
		  	  $u=$storage->read();
		  	  
		  	  if (isset($u->user_name)) {
		  	  	   $db = Zend_Registry::get('db');
		  	  	  
		  	  	   $fields_recipe=array('recipe_batch'=>'bash_size','recipe_boiltime'=>'boil_time','recipe_efficiency'=>'efficiency','recipe_attenuation'=>'attenuation','recipe_name'=>'beer_name','recipe_style'=>'style_id','recipe_comments'=>'comments','recipe_sg'=>'recipe_sg','recipe_fg'=>'recipe_fg','recipe_ebc'=>'recipe_ebc','recipe_abv'=>'recipe_abv','recipe_ibu'=>'recipe_ibu');
		  	  	   $ins=array();
		  	  	   $recipe_update=false;
		  	  	   foreach ($fields_recipe as $key => $value) {
		  	  	   	$ins[$key]=$_POST[$value];
		  	  	   	
		  	  	   } 
		  	  	   $recipe_type ="grain";
		  	  	   if (isset($_POST["malt_list"])) { 
			  	  	   for ($i=0;$i<count($_POST["malt_list"]);$i++) {
				  	  	   if ($_POST["malt_type"][$i]=="extract") {
				  	  	   	$recipe_type="partial";
				  	  	   }
			  	  	   }
		  	  	   }
		  	  	   $ins["recipe_type"]=$recipe_type;
		  	  	 if (strlen($_POST["recipe_id"])>0) {
		  	  	   $select=$db->select();
		  	  	   $select->from("beer_recipes",array("recipe_id"))
		  	  	   ->where("brewer_id = ?",$u->user_id)
		  	  	   ->where("recipe_id = ?",$_POST["recipe_id"]);
		  	  	   $r=$db->fetchAll($select);
		  	  	   if (count($r)>0) {
		  	  	   	
		  	  	   }else{
		  	  	   $_POST["recipe_id"]="";
		  	  	   }
		  	  	   
		  	  	 }
		  	  	   if (strlen($_POST["recipe_id"])>0 && !isset($_POST["duplicate"])) {
		  	  	 
		  	  	   $ins["recipe_modified"]=new Zend_Db_Expr('CURRENT_TIMESTAMP');
		  	  	    if ( $db->update("beer_recipes",$ins,"recipe_id = ".$_POST["recipe_id"])) {
		  	  	     	 $recipe_id=$_POST["recipe_id"];
		  	  	     	 $recipe_update=true;
		  	  	    }
		  	  	   }else{  
					   $ins["brewer_id"]=$u->user_id;
					   if ($db->insert("beer_recipes",$ins)) {;
						$recipe_id=$db->lastInsertId();
					   }
		  	  	   }
		  	  	 
		  	  	   $fields_malt = array("malt_id"=>"malt_id","malt_type"=>"malt_type","malt_name"=>'malt_list',"malt_extract"=>"malt_extract","malt_ebc"=>"malt_color","malt_weight"=>"malt_weight");
		  	  	   $fields_hops = array("hop_id"=>"hop_id","hop_time"=>"hop_time","hop_name"=>'hop_list',"hop_alpha"=>"hop_alpha","hop_weight"=>"hop_weight");
		  	  	    $fields_yeast = array("yeast_id"=>"yeast_id","yeast_name"=>'yeast_list',"yeast_weight"=>"yeast_weight");
		  	  	  
		  	  	   if ($recipe_id) {
		  	  	   if ($recipe_update) {// istrinti
		  	  	   $db->delete("beer_recipes_malt","recipe_id = ".$recipe_id);
		  	  	   $db->delete("beer_recipes_hops","recipe_id = ".$recipe_id);
		  	  	   $db->delete("beer_recipes_yeast","recipe_id = ".$recipe_id);
		  	  	   }
		  	  	    if (isset($_POST["malt_list"])) {
		  	  	   for ($i=0;$i<count($_POST["malt_list"]);$i++) {
			  	  	   $ins=array();
			  	  	   foreach ($fields_malt as $key => $value) {
			  	  	   	$ins[$key]=$_POST[$value][$i];
			  	  	   	
			  	  	   	
			  	  	   } 
			  	  	   $ins["recipe_id"]= $recipe_id;
			  	  	   $db->insert("beer_recipes_malt",$ins);
		  	  	   }
		  	  	   }
		  	  	   if (isset($_POST["hop_list"])) {
		  	  	   for ($i=0;$i<count($_POST["hop_list"]);$i++) {
			  	  	   $ins=array();
			  	  	   foreach ($fields_hops as $key => $value) {
			  	  	   	$ins[$key]=$_POST[$value][$i];
			  	  	   	
			  	  	   	
			  	  	   } 
			  	  	   $ins["recipe_id"]= $recipe_id;
			  	  	   $db->insert("beer_recipes_hops",$ins);
		  	  	   }
		  	  	   }
		  	  	    if (isset($_POST["yeast_list"])) {
		  	  	   for ($i=0;$i<count($_POST["yeast_list"]);$i++) {
			  	  	   $ins=array();
			  	  	   foreach ($fields_yeast as $key => $value) {
			  	  	   	$ins[$key]=$_POST[$value][$i];
			  	  	   	
			  	  	   	
			  	  	   } 
			  	  	   $ins["recipe_id"]= $recipe_id;
			  	  	   $db->insert("beer_recipes_yeast",$ins);
		  	  	   }
		  	  	   }
		  	  	   }
		  	  	   print Zend_Json::encode(array("status"=>0,"data"=>array("recipe_id"=>$recipe_id)));
		  	  	   
		  	  	//  print $db->lastInsertId().$_POST['recipe_sg'];
		  	  }else{
		  	  	print Zend_Json::encode(array("status"=>1,"errors"=>array(array("message"=>"Neregistruotas vartotojas","type"=>"authentication"))));
		  	  }
	 	}
 	}
 	public function randomAction() {
 		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		 $db = Zend_Registry::get('db');
		 $select=$db->select();
		 $select->from("beer_recipes",array("recipe_id"))
		 ->where("recipe_publish =?",'1')
		 ->order("Rand()")
		 ->limit(1);
		 if ($random=$db->fetchRow($select))  {
		 	 $this->_redirect("/recipes/view/".$random["recipe_id"]);
		 }
		
 	}
 	 function findAction() {
	 	$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender();
		$db = Zend_Registry::get('db');
		if (isset($_GET['tags'])) {
      	$select=$db->select()
			->from("beer_recipes_tags",array("tag_text"=>"distinct(tag_text)"))
			->where("tag_text like '%".$_GET["term"]."%'");
			$u=$db->fetchAll($select);
		for ($i=0;$i<count($u);$i++) {
			$u[$i]=$u[$i]["tag_text"];
		}
		}
		if (isset($_GET['hops'])) {
		$select=$db->select()
			->from("beer_recipes_hops",array("hop_name"=>"distinct(hop_name)"))
			->where("hop_name like '%".$_GET["term"]."%'");
			//->where("user_enabled = '1'");;
			//->where("user_active = '1'");
		$u=$db->fetchAll($select);
		for ($i=0;$i<count($u);$i++) {
			$u[$i]=$u[$i]["hop_name"];
		}
		}
		if (isset($_GET['malts'])) {
		$select=$db->select()
			->from("beer_recipes_malt",array("malt_name"=>"distinct(malt_name)"))
			->where("malt_name like '%".$_GET["term"]."%'");
			//->where("user_enabled = '1'");;
			//->where("user_active = '1'");
		$u=$db->fetchAll($select);
		for ($i=0;$i<count($u);$i++) {
			$u[$i]=$u[$i]["malt_name"];
		}
		}
		if (isset($_GET['yeasts'])) {
		$select=$db->select()
			->from("beer_recipes_yeast",array("yeast_name"=>"distinct(yeast_name)"))
			->where("yeast_name like '%".$_GET["term"]."%'");
			//->where("user_enabled = '1'");;
			//->where("user_active = '1'");
		$u=$db->fetchAll($select);
		for ($i=0;$i<count($u);$i++) {
			$u[$i]=$u[$i]["yeast_name"];
		}
		}
	 	 print Zend_Json::encode($u);
	 }

}
?>

					 
