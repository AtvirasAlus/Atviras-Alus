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
		->join("mail_users","mail_users.mail_id=mail.mail_id")
		->join("users","mail_users.user_id=users.user_id",array("mail_to"=>"GROUP_CONCAT(user_name)"))
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
		->join("users","mail.mail_sender=users.user_id",array("mail_from"=>"user_name"))
		->where("mail_users.mail_id = ?", $_GET['id']);
		$this->view->mail=$db->fetchRow($select);
		if (count($this->view->mail)>0) {
		$select=$db->select()
		->from("mail_users",array())
		->join("users","mail_users.user_id=users.user_id",array("user_name"))
		->where("mail_users.mail_id = ?", $_GET['id'])
		->where("mail_users.user_id != ?", $this->user->user_id);
		$this->view->acc=$db->fetchAll($select);
		}
		
		
		
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
		
		$select=$db->select()
		->from("mail_users",array())
		->join("users","mail_users.user_id=users.user_id",array("mail_to"=>"GROUP_CONCAT(user_name)"))
		->where("mail_users.mail_id = ?", $_GET['id']);
		$mail_to=$db->fetchRow($select);
		$this->view->mail["mail_to"]=$mail_to["mail_to"];
		$db->update("mail_users",array("mail_read"=>"1"),"mail_id=".$_GET['id']. " and user_id=".$this->user->user_id );
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
		$select=$db->select()
		->from("mail_users",array())
		->join("users","mail_users.user_id=users.user_id",array("mail_to"=>"GROUP_CONCAT(user_name)"))
		->where("mail_users.mail_id = ?", $_GET['id']);
		
		$mail_to=$db->fetchRow($select);
		$this->view->mail["mail_to"]=$mail_to["mail_to"];
	 }
	 
	 function contactsAction() {
	 	$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender();
		$db = Zend_Registry::get('db');
		$select=$db->select()
			->from("users",array("user_name"))
			->where("user_name like '%".$_GET["term"]."%'")
			//->where("user_enabled = '1'")
			->where("user_active = '1'");
		$u=$db->fetchAll($select);
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
		 				$tpl_vars['to_user_name']=$to_smtp[$i]['user_name'];
		 				$body=Entities_MicroTemplate::render($tpl["template_body"],$tpl_vars);
		 				Entities_Mail::mail(array($to_smtp[$i]['user_email']),$tpl["template_subject"],$body);
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
		 }
		$this->_redirect("/mail/inbox?succes"); 
	 }
	
	 
}
?>
