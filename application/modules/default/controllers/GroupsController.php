<?php

class GroupsController extends Zend_Controller_Action {

	function init() {
		$this->db = Zend_Registry::get('db');
	}

	public function indexAction() {
		$select = $this->db->select()
				->from("groups", array("group_id", "group_name" => "concat(group_name,' (',group_description,')')"))
				->where("groups.group_public = ?", "1")
				->order("groups.group_name");
		$groups = $this->db->fetchAll($select);
		for ($i = 0; $i < count($groups); $i++) {
			$groups[$i]["users"] = array();
			$select = $this->db->select()
					->from("users_groups", array())
					->join("users", "users.user_id=users_groups.user_id", array("user_id", "user_name", "user_email"))
					->where("users_groups.group_id = ?", $groups[$i]["group_id"])
					->order("users.user_name");
			$groups[$i]["users"] = $this->db->fetchAll($select);
		}
		$this->view->groups = $groups;
	}

}