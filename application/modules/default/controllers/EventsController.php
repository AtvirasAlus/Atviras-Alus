<? class EventsController extends Zend_Controller_Action  {
	private  $userInfo;
	public function init() {
		$storage = new Zend_Auth_Storage_Session();
		$this->user = $storage->read();
		
	}
	 function indexAction() {
	 	 
		$db = Zend_Registry::get('db');
		$select=$db->select()
		->from("beer_events")
		
		
		->where("beer_events.event_published = ?", '1')
		->order("beer_events.event_start DESC");
		
		//$this->view->recipes=$db->fetchAll($select);
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));

		$this->view->content->setItemCountPerPage(21);
	 }
	 function viewAction() {
	 	  $storage = new Zend_Auth_Storage_Session(); 
    
     $euid=explode("-",$this->_getParam('event'));
     $event_id=$euid[0];
     
    if ($event_id>0) {	
	$db = Zend_Registry::get("db");
	$select=$db->select();
	$select->from("beer_events")

	->where("event_id = ?",$event_id);
	$this->view->event=$db->fetchRow($select);
	$select=$db->select();
	$select->from("beer_events_users",array())
	->join("users","beer_events_users.user_id=users.user_id",array("user_name"=>"group_concat(user_name)"))
	->where("event_id=?",$event_id);
	$this->view->registered_users=$db->fetchRow($select);
	 }
	 if (isset($this->user->user_id)) {
	 	$select=$db->select();
		$select->from("beer_events_users")
		->where("event_id = ?",$event_id)
		->where("user_id = ?",$this->user->user_id);
		
		$t=$db->fetchAll($select);
		if (count($t)>0) {
			$this->view->registration_status=2;
		}else{
			$this->view->registration_status=1;
		}
	
	 	 
	 }else{
	 	 $this->view->registration_status=0;
	 }
	 }
	
	 function registerAction() {
	 	 $db = Zend_Registry::get('db');
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$storage = new Zend_Auth_Storage_Session(); 
		$u=$storage->read();
		  if (isset($u->user_name)) {
			if (isset($_POST)) {
				$db->delete("beer_events_users","event_id = ".$_POST['id'].' and user_id = '.$u->user_id); 
				switch($_POST['action']) {
				
				case "in":
					$db->insert("beer_events_users",array("event_id"=>$_POST['id'],"user_id"=>$u->user_id)); 
					
					break;
				}
				$select=$db->select();
				$select->from("beer_events_users",array())
				->join("users","beer_events_users.user_id=users.user_id",array("user_name"=>"group_concat(user_name)"))
				->where("event_id=?",$_POST['id']);
				$u=$db->fetchRow($select);
				 print Zend_Json::encode(array("status"=>0,"data"=>$u['user_name']));
			}
		  }else{
		  	    print Zend_Json::encode(array("status"=>1,"errors"=>array(array("message"=>"Neregistruotas vartotojas","type"=>"authentication"))));
		  }
	 }
	
	
	 
}
?>
