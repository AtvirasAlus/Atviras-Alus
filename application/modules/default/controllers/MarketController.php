<?php

class MarketController extends Zend_Controller_Action {

	function init() {
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		$this->view->user_info = $user_info;
		$this->uid = 0;
		$this->ugroup = 'brewer';
		if (isset($user_info->user_id) && !empty($user_info->user_id)) {
			$this->uid = $user_info->user_id;
			$this->ugroup = $user_info->user_type;
		}
		$this->db = Zend_Registry::get('db');
		$this->view->uid = $this->uid;
		$this->view->ugroup = $this->ugroup;
	}

	function indexAction() {
		$cat = $this->_getParam("cat");
		$this->view->f_cat = $cat;
		$act = $this->_getParam("act");
		$this->view->f_act = $act;
		$select = $this->db->select()
				->from("market_items")
				->join("users", "users.user_id = market_items.user_id", array("user_name", "user_email"))
				->joinLeft("market_comments", "market_comments.market_id=market_items.market_id", array("COUNT(market_comments.market_id) as comments"))
				->where("market_till >= ?", date("Y-m-d"))
				->order(array("market_posted DESC", "market_id DESC"))
				->group("market_items.market_id");
		if (!empty($cat))
			$select->where("market_category = ?", $cat);
		if (!empty($act))
			$select->where("market_action = ?", $act);
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->items = new Zend_Paginator($adapter);
		$this->view->items->setCurrentPageNumber($this->_getParam('page'));
		$this->view->items->setItemCountPerPage(20);
	}

	public function viewAction() {
		$item_id = $this->_getParam("item_id");
		$select = $this->db->select()
				->from("market_items")
				->join("users", "users.user_id = market_items.user_id", array("user_name", "user_email"))
				->where("market_id = ?", $item_id);
		$result = $this->db->FetchRow($select);
		if ($result == false)
			$this->_redirect("/turgus");
		$this->view->item = $result;
	}

	function myAction() {
		if ($this->uid == 0)
			$this->_redirect("/turgus");
		$select = $this->db->select()
				->from("market_items")
				->join("users", "users.user_id = market_items.user_id", array("user_name", "user_email"))
				->joinLeft("market_comments", "market_comments.market_id=market_items.market_id", array("COUNT(market_comments.market_id) as comments"))
				->where("market_till >= ?", date("Y-m-d"))
				->where("market_items.user_id = ?", $this->uid)
				->order(array("market_posted DESC", "market_id DESC"))
				->group("market_items.market_id");
		$result = $this->db->FetchAll($select);
		$this->view->current = $result;
		$select = $this->db->select()
				->from("market_items")
				->join("users", "users.user_id = market_items.user_id", array("user_name", "user_email"))
				->joinLeft("market_comments", "market_comments.market_id=market_items.market_id", array("COUNT(market_comments.market_id) as comments"))
				->where("market_till < ?", date("Y-m-d"))
				->where("market_items.user_id = ?", $this->uid)
				->order(array("market_posted DESC", "market_id DESC"))
				->group("market_items.market_id");
		$result = $this->db->FetchAll($select);
		$this->view->past = $result;
	}

	public function deleteAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$item_id = $this->_getParam("item_id");
		$select = $this->db->select()
				->from("market_items")
				->where("market_id = ?", $item_id)
				->where("user_id = ?", $this->uid);
		$result = $this->db->fetchRow($select);
		if ($result == false && $this->ugroup != "admin") {
			$this->_redirect("/turgus");
		} else {
			$delete = $this->db->delete("market_items", "market_id = '" . $item_id . "'");
			$delete = $this->db->delete("market_comments", "market_id = '" . $item_id . "'");
		}
		if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], "/mano") !== false) {
			$this->_redirect("/turgus/mano");
		} else {
			$this->_redirect("/turgus");
		}
	}

	public function newAction() {
		if ($this->uid == 0) $this->_redirect ("/turgus");
		$select = $this->db->select()
				->from("users_attributes")
				->where("user_id = ?", $this->uid);
		$result = $this->db->FetchRow($select);
		if (isset($result['user_location']) && !empty($result['user_location'])){
			$this->view->city = $result['user_location'];
		} else {
			$this->view->city = "";
		}
		if ($this->_request->isPost()) {
			$data = array();
			$data['user_id'] = $this->uid;
			$data['market_posted'] = date("Y-m-d H:i:s");
			$data['market_title'] = trim($this->_getParam("market_title"));
			$data['market_title'] = strip_tags($data['market_title']);
			$data['market_text'] = trim($this->_getParam("market_text"));
			$data['market_text'] = strip_tags($data['market_text'], "<a><b><i>");
			$data['market_category'] = $this->_getParam("market_category");
			$data['market_action'] = $this->_getParam("market_action");
			$data['market_till'] = $this->_getParam("market_till");
			$data['market_city'] = trim(strip_tags($this->_getParam("market_city")));
			$data['market_sell_option'] = $this->_getParam("market_sell_option");
			$this->db->insert("market_items", $data);
			$this->_redirect("/turgus/skelbimas/".$this->db->lastInsertId());
		}
	}

	public function editAction() {
		if ($this->uid == 0) $this->_redirect ("/turgus");
		$select = $this->db->select()
				->from("market_items")
				->where("user_id = ?", $this->uid)
				->where("market_id = ?", $this->_getParam("item_id"));
		$result = $this->db->fetchRow($select);
		if ($result == false) $this->_redirect ("/turgus");
		$this->view->data = $result;
		if ($this->_request->isPost()) {
			$data = array();
			$data['market_title'] = trim($this->_getParam("market_title"));
			$data['market_title'] = strip_tags($data['market_title']);
			$data['market_text'] = trim($this->_getParam("market_text"));
			$data['market_text'] = strip_tags($data['market_text'], "<a><b><i>");
			$data['market_category'] = $this->_getParam("market_category");
			$data['market_action'] = $this->_getParam("market_action");
			$data['market_till'] = $this->_getParam("market_till");
			$data['market_city'] = trim(strip_tags($this->_getParam("market_city")));
			$data['market_sell_option'] = $this->_getParam("market_sell_option");
			$this->db->update("market_items", $data, "market_id = '".$this->_getParam("item_id")."'");
			$this->_redirect("/turgus/skelbimas/".$this->_getParam("item_id"));
		}
	}
}