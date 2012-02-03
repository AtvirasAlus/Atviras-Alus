<? class MailController extends Zend_Controller_Action  {
	private  $userInfo;
	public function init() {
		$storage = new Zend_Auth_Storage_Session();
		$this->user = $storage->read();
		if (!isset($this->user->user_id) && !isset($_GET['redirect'])) {
			$this->_redirect("/mail/nologin/?redirect=".$_SERVER['REQUEST_URI']);
		}
	}
	 function indexAction() {
	 	 $this->_helper->layout->setLayout('empty');
		  $this->_helper->viewRenderer->setNoRender();
		  $this->_redirect("/mail/inbox");
		
	 }
	 function inboxAction() {
		 $db = Zend_Registry::get('db');
		$select=$db->select()
		->from("mail_users",array("mail_read"))
		->join("mail","mail_users.mail_id = mail.mail_id")
		->join("users","mail.mail_sender=users.user_id",array("user_name"))
		->where("mail_users.user_id = ?", $this->user->user_id)
		->order("mail_date DESC");
		//$this->view->recipes=$db->fetchAll($select);
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));
		$this->view->content->setItemCountPerPage(21);
	 }
	 function sentmailAction() {
	 	 $this->_helper->layout->setLayout('empty');
		  $this->_helper->viewRenderer->setNoRender();
		 $mail = new Entities_Mail($this->userInfo);
		 $outbox=$mail->outbox();
		 $dojoData=new Zend_Dojo_Data("mail_id", $outbox);
	 	 echo $dojoData->toJson();
	 }
	function outboxAction() {
		 $db = Zend_Registry::get('db');
		$select=$db->select()
		->from("mail")
		->join("mail_users","mail_users.mail_id=mail.mail_id",array())
	
		->join("users","mail_users.user_id=users.user_id",array("mail_to"=>"GROUP_CONCAT(DISTINCT(user_name))"))
		->joinLeft("users_groups","users_groups.group_id=mail_users.group_id",array())
		->joinLeft("groups","users_groups.group_id=groups.group_id",array("group_id","mail_to_group"=>"GROUP_CONCAT(DISTINCT (CONCAT(groups.group_name,' (',groups.group_description,')')))"))
		->where("mail.mail_sender = ?", $this->user->user_id)
		->where("mail.mail_deleted = ?", '0')
		->order("mail_date DESC")
		->group("mail.mail_id");
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));
		$this->view->content->setItemCountPerPage(21);
		 
	 }
	 function nologinAction() {
	 	 if (isset($this->user->user_id) && isset($_GET['redirect'])) {
			$this->_redirect($_GET['redirect']);
		 }else{
		 	 if (isset($this->user->user_id) && !isset($_GET['redirect'])) {
		 	 	 $this->_redirect("/mail/inbox");
		 	 }
		 }
	 }
	function composeAction() {
		
	 }
	
	function respondAction() {
		$db = Zend_Registry::get('db');
		$select=$db->select()
		->from("mail_users",array("mail_read"))
		->join("mail","mail_users.mail_id = mail.mail_id")
		->join("users","mail.mail_sender=users.user_id",array("mail_from"=>"user_name","user_id"))
		->where("mail_users.mail_id = ?", $_GET['id']);
		$this->view->mail=$db->fetchRow($select);
		if (count($this->view->mail)>0) {
		//if ($this->view->mail[0]["group_id"] >0) {
		//}
		
		$this->view->acc=$this->getMailTo($_GET['id'],$this->view->mail['user_id'],$this->user->user_id);
		}

		
		
		
	}
	private function getMailTo($mail_id,$user_id=0,$e_user_id=0) {
    $db = Zend_Registry::get('db');
      $mail_to="";
		
   
		 $mail_to_group=array();
		
    $select=$db->select()
      ->from("groups",array("mail_to"=>"GROUP_CONCAT(DISTINCT (CONCAT(groups.group_name,' (',groups.group_description,')')))","mail_to_id"=>"GROUP_CONCAT(DISTINCT (groups.group_id))"))
 ->join("mail_users","mail_users.group_id=groups.group_id")
     // ->join("mail_users","mail_users.group_id=groups.group_id",array("exclude_users"=>"GROUP_CONCAT(DISTINCT (mail_users.user_id))"))
      ->where("mail_users.mail_id = ?", $mail_id)
      ->where("mail_users.group_id > ?", '0')
      ->group("groups.group_id");
      
      $mail_to_group=$db->fetchRow($select);
		$select=$db->select()
		->from("mail_users",array())
		->join("users","mail_users.user_id=users.user_id",array("mail_to"=>"GROUP_CONCAT(distinct(user_name))"));
		
		if (isset($mail_to_group["mail_to_id"])) {
		      $mail_to=$mail_to_group["mail_to"];
		      $select->where("mail_users.group_id not in ('".$mail_to_group["mail_to_id"]."')");
				//->where("mail_users.user_id not in ('".$mail_to_group["exclude_users"]."')");
		}
		
		if ($e_user_id>0) {
    			  $select->where("users.user_id !=?",$e_user_id);
		}
		$select->where("mail_users.mail_id = ?", $_GET['id']);
		$mail_to_users=$db->fetchRow($select);
		if (isset($mail_to_users["mail_to"])) {
			if (strlen( $mail_to)>0) {
         			$mail_to.=",".$mail_to_users["mail_to"];
			}else{
        			 $mail_to=$mail_to_users["mail_to"];
     			 }
     		}
	if ($user_id>0 && strlen($mail_to_group["mail_to"])>0){
		$select=$db->select()
		->from("users_groups")
		->where("users_groups.group_id   in (".$mail_to_group["mail_to_id"].")")
		->where("users_groups.user_id =?",$user_id);

		if (count($db->fetchAll($select))==0) {
			$select=$db->select()
			->from("users")
			->where("users.user_id =?",$user_id);
			$mail_sender =  $db->fetchRow($select);
			$mail_to.=",".$mail_sender['user_name'];
		}
	}else if ($user_id>0){
		$select=$db->select()
			->from("users")
			->where("users.user_id =?",$user_id);
			$mail_sender =  $db->fetchRow($select);
			$mail_to.=",".$mail_sender['user_name'];
		
	}
      		return $mail_to;
	}
	 function readAction() {

		$db = Zend_Registry::get('db');
		$select=$db->select()
		->from("mail_users",array("mail_read"))
		->join("mail","mail_users.mail_id = mail.mail_id")
		->join("users","mail.mail_sender=users.user_id",array("mail_from"=>"user_name"))
		->where("mail_users.user_id = ?", $this->user->user_id)
		->where("mail_users.mail_id = ?", $_GET['id']);
		$this->view->mail=$db->fetchRow($select);
		if (count($this->view->mail)>0) {
     				$this->view->mail["mail_to"]=$this->getMailTo($_GET['id']);
    
		
		$db->update("mail_users",array("mail_read"=>"1"),"mail_id=".$_GET['id']. " and user_id=".$this->user->user_id );
		}
	 }
	 function readoutboxAction() {
	 	$db = Zend_Registry::get('db');
		$select=$db->select()
		->from("mail")
		->join("users","mail.mail_sender=users.user_id",array("mail_from"=>"user_name"))
		->where("mail.mail_sender = ?", $this->user->user_id)
		->where("mail.mail_id = ?", $_GET['id'])
		->where("mail.mail_deleted = ?", '0');
		$this->view->mail=$db->fetchRow($select);
		
		
		$mail_to=$db->fetchRow($select);
		$this->view->mail["mail_to"]=$this->getMailTo($_GET['id'],$this->user->user_id);
	 }
	 
	 function contactsAction() {
	 	$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender();
		$db = Zend_Registry::get('db');
		$select0=$db->select()
    ->from("users_groups",array())
    ->join("groups","groups.group_id=users_groups.group_id",array("user_id"=>"concat('-',groups.group_id)","user_name"=>"concat(groups.group_name,' (',groups.group_description,')')")) 
    ->where("groups.group_public= ?",'1')
    
      ->where("groups.group_name like '%".$_GET["term"]."%'")
      ->orWhere("groups.group_description like '%".$_GET["term"]."%'");
		$select=$db->select()
			->from("users",array("users.user_id","users.user_name"))
			->where("users.user_name like '%".$_GET["term"]."%'")
			//->where("user_enabled = '1'")
			->where("user_active = '1'");
		
		$u=$db->fetchAll($select->__toString(). " UNION ".$select0->__toString());
		for ($i=0;$i<count($u);$i++) {
			$u[$i]=$u[$i]["user_name"];
		}
	 	 print Zend_Json::encode($u);
	 }
	 function deleteAction() { 
	 	$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender();$db = Zend_Registry::get('db');
		switch($_POST['type']) {
			case "inbox":
	 	
	 	$db->delete("mail_users","mail_id IN ('".implode("','",$_POST['mail_id'])."') and mail_users.user_id=".$this->user->user_id);
	 	//print "mail_in IN ('".implode("','",$_POST['mail_id'])."')";
	 	$this->_redirect($_POST['redirect']);
	 	break;
	 	case "outbox":
	 	$db->update("mail",array("mail_deleted"=>"1"),"mail_id IN ('".implode("','",$_POST['mail_id'])."') and mail.mail_sender=".$this->user->user_id);
	 	//print "mail_in IN ('".implode("','",$_POST['mail_id'])."')";
	 	$this->_redirect($_POST['redirect']);
	 	break;
	 	}
	 	
	
	 }
	
	 function sendAction() {
	 	 $this->_helper->layout->setLayout('empty');
		 $this->_helper->viewRenderer->setNoRender();
		 $mail = new Entities_Mail($this->user);
		 try {
		 	$to_smtp=$mail->send(explode(",",$_POST['mail_to']),$_POST["mail_subject"],$_POST["mail_body"]);

		 	if (count($to_smtp)>0) {
		 		if ($tpl=Entities_Settings::getTemplate('message_received')) {
		 			$tpl_vars=array('from_user_name'=>$this->user->user_name,'mail_subject'=>$_POST["mail_subject"],'mail_body'=>$_POST["mail_body"]);
		 			for ($i=0;$i<count($to_smtp);$i++) {
		 				$tpl_vars['mail_id']=$to_smtp[$i]['mail_id'];
		 				$tpl_vars['to_user_name']=$to_smtp[$i]['contact_name'];
		 				$body=Entities_MicroTemplate::render($tpl["template_body"],$tpl_vars);
		 				Entities_Mail::mail(array($to_smtp[$i]['contact_email']),$tpl["template_subject"],$body);
		 			}
		 		}else{
		 		 	print  Zend_Json::encode(array("status"=>1,"message"=>"Nerastas žinutės šablonas"));
		 	 	}
		 	}else{
		 		print Zend_Json::encode(array("status"=>1));
		 		return;
		 	}
		 	 
		 }catch (Exception $e) {
		 	 print Zend_Json::encode(array("status"=>1,"message"=>$e->getMessage()));
		 	 return;
		 }
		$this->_redirect("/mail/inbox?succes"); 
	 }
	
	 
}
?>
