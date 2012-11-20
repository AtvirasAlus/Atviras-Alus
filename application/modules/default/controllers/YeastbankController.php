<?php

class YeastbankController extends Zend_Controller_Action {

	function init() {
		$storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $this->user_info = $storage->read();
	}

	public function indexAction() {
		
	}
	
	public function sellAction() {
		$this->view->type = "sell";
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("yeastbank_items")
				->join("users", "users.user_id = yeastbank_items.yb_user", array("user_name", "user_id", "user_email"))
				->where("yb_till >= NOW()")
				->where("yb_type = 'sell'")
				->order("yb_posted DESC");
		$result = $db->fetchAll($select);
		$this->view->items = $result;
	}

	public function buyAction() {
		$this->view->type = "buy";
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("yeastbank_items")
				->join("users", "users.user_id = yeastbank_items.yb_user", array("user_name", "user_id", "user_email"))
				->where("yb_till >= NOW()")
				->where("yb_type = 'buy'")
				->order("yb_posted DESC");
		$result = $db->fetchAll($select);
		$this->view->items = $result;
	}

	public function myAction() {
		if (isset($this->user_info->user_id) && $this->user_info->user_id != 0){
			$this->view->type = "my";
			$db = Zend_Registry::get("db");
			$select = $db->select()
					->from("yeastbank_items")
					->join("users", "users.user_id = yeastbank_items.yb_user", array("user_name", "user_id", "user_email"))
					->where("yb_user = ?", $this->user_info->user_id)
					->order("yb_posted DESC");
			$result = $db->fetchAll($select);
			$this->view->items = $result;
		} else {
			$this->_redirect("/");
		}
	}
	
	public function deleteAction(){
		if (isset($this->user_info->user_id) && $this->user_info->user_id != 0){
			$yb_id = $this->getRequest()->getParam('yb_id');
			$db = Zend_Registry::get("db");
			$delete = $db->delete("yeastbank_items", array("yb_id = '".$yb_id."'", "yb_user = '".$this->user_info->user_id."'"));
			$this->_redirect("/mieliu-bankas/mano");
		} else {
			$this->_redirect("/");
		}
		
	}

	public function newAction() {
		if (isset($this->user_info->user_id) && $this->user_info->user_id != 0){
			$this->view->type = "new";
			$db = Zend_Registry::get("db");
			if (isset($_POST['yb_submit']) && !empty($_POST['yb_submit'])){
				$insert = $db->insert("yeastbank_items", array(
					"yb_type" => $_POST['yb_type'],
					"yb_user" => $this->user_info->user_id,
					"yb_posted" => date("Y-m-d H:i:s"),
					"yb_from" => trim($_POST['yb_from']),
					"yb_till" => trim($_POST['yb_till']),
					"yb_title" => trim($_POST['yb_title']),
					"yb_text" => strip_tags($_POST['yb_text']),
					"yb_sell_type" => $_POST['yb_sell_type'],
					"yb_sell_price" => trim($_POST['yb_sell_price']),
					"yb_sell_item" => trim($_POST['yb_sell_item']),
					"yb_city" => trim($_POST['yb_city']),
					"yb_phone" => trim($_POST['yb_phone'])
				));
				$this->_redirect("/mieliu-bankas/mano");
			}
			$select = $db->select()
					->from("users_attributes", array("user_location"))
					->where("user_id = ?", $this->user_info->user_id);
			$result = $db->fetchRow($select);
			$this->view->city = $result['user_location'];
		} else {
			$this->_redirect("/");
		}
	}
	
	public function editAction() {
		if (isset($this->user_info->user_id) && $this->user_info->user_id != 0){
			$yb_id = $this->getRequest()->getParam('yb_id');
			$this->view->type = "edit";
			$db = Zend_Registry::get("db");
			if (isset($_POST['yb_submit']) && !empty($_POST['yb_submit'])){
				$update = $db->update("yeastbank_items", array(
					"yb_type" => $_POST['yb_type'],
					"yb_user" => $this->user_info->user_id,
					"yb_posted" => date("Y-m-d H:i:s"),
					"yb_from" => trim($_POST['yb_from']),
					"yb_till" => trim($_POST['yb_till']),
					"yb_title" => trim($_POST['yb_title']),
					"yb_text" => strip_tags($_POST['yb_text']),
					"yb_sell_type" => $_POST['yb_sell_type'],
					"yb_sell_price" => trim($_POST['yb_sell_price']),
					"yb_sell_item" => trim($_POST['yb_sell_item']),
					"yb_city" => trim($_POST['yb_city']),
					"yb_phone" => trim($_POST['yb_phone'])
				), array("yb_user = '".$this->user_info->user_id."'", "yb_id = '".$yb_id."'"));
				$this->_redirect("/mieliu-bankas/mano");
			}
			$select = $db->select()
					->from("yeastbank_items")
					->where("yb_user = ?", $this->user_info->user_id)
					->where("yb_id = ?", $yb_id);
			$result = $db->fetchRow($select);
			$this->view->item = $result;
		} else {
			$this->_redirect("/");
		}
	}
}