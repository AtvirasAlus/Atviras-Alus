<?php

class IdeaController extends Zend_Controller_Action {

	public function init() {
		
	}

	public function indexAction() {
		
	}
	
	public function voteAction(){
		$db = Zend_Registry::get('db');
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		$this->_helper->layout->setLayout('empty');
		//@todo: remove:
		$_POST['idea_id'] = 1;
		if (isset($_POST['idea_id'])) {
			$me = -1;
			if (isset($user_info->user_id) && !empty($user_info->user_id)) $me = $user_info->user_id;
			$select = $db->select()
				->from("idea_items")
				->join("users", "users.user_id=idea_items.user_id", array("user_name", "user_email"))
				->joinLeft("idea_votes", "idea_votes.idea_id=idea_items.idea_id AND idea_votes.user_id='".$me."'", array("vote_value"))
				->where("idea_items.idea_id = ?", $_POST['idea_id'])
				->limit(1);
			$result = $db->fetchRow($select);
			$this->view->idea = $result;
		} else {
			$this->_helper->viewRenderer->setNoRender(true);
			print "Klaida!";
		}
	}

	public function listAction() {
		$storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $storage->read();
		$me = -1;
		if (isset($this->view->user_info->user_id) && !empty($this->view->user_info->user_id)){
			$me = $this->view->user_info->user_id;
		}
		$type = $this->getRequest()->getParam("type");
		if (empty($type)) $type = "new";
		$this->view->type = $type;
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("idea_items")
				->join("users", "users.user_id=idea_items.user_id", array("user_name", "user_email"))
				->joinLeft("idea_votes", "idea_votes.idea_id=idea_items.idea_id AND idea_votes.user_id='".$me."'", array("vote_value"));
		if ($type == "finished"){
			$select->where("idea_items.idea_status = ?", "1");
		} else {
			$select->where("idea_items.idea_status = ?", "0");
		}
		if ($type == "top"){
			$select->order("idea_vote_sum DESC");
		} else {
			$select->order("idea_posted DESC");
		}
		$result = $db->fetchAll($select);
		foreach ($result as $key=>$row){
			$select = $db->select()
				->from("idea_votes")
				->where("idea_votes.idea_id = ?", $row['idea_id']);
			$votes = $db->fetchAll($select);
			$result[$key]['total_votes'] = sizeof($votes);
			if ($votes == 0){
				$result[$key]['up_size'] = 0;
				$result[$key]['down_size'] = 0;
				$result[$key]['neutral_size'] = 0;
			} else {
				$t_up = 0;
				$t_down = 0;
				$t_neutral = 0;
				foreach ($votes as $vote){
					if ($vote['vote_value'] == "1") $t_up++;
					if ($vote['vote_value'] == "0") $t_neutral++;
					if ($vote['vote_value'] == "-1") $t_down++;
				}
				if ($t_up == 0){
					$result[$key]['up_size'] = 0;
				} else {
					$result[$key]['up_size'] = round(100 * $t_up / sizeof($votes));
				}
				if ($t_neutral == 0){
					$result[$key]['neutral_size'] = 0;
				} else {
					$result[$key]['neutral_size'] = round(100 * $t_neutral / sizeof($votes));
				}
				if ($t_down == 0){
					$result[$key]['down_size'] = 0;
				} else {
					$result[$key]['down_size'] = round(100 * $t_down / sizeof($votes));
				}
				$this->view->down_size = 0;
				$this->view->neutral_size = 0;
			}
		}
		//@todo: reikia paginatoriaus idėjų sąrašui
		$this->view->ideas = $result;
	}

	public function listnewAction() {
		$this->_forward("list", null, null, array("type" => "new"));
	}

	public function listtopAction() {
		$this->_forward("list", null, null, array("type" => "top"));
	}

	public function listfinishedAction() {
		$this->_forward("list", null, null, array("type" => "finished"));
	}

	public function viewAction() {
		
	}
}
?>


