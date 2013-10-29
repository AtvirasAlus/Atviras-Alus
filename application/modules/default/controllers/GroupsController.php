<?php

class GroupsController extends Zend_Controller_Action {

	function init() {
		$this->db = Zend_Registry::get('db');
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		$this->show_beta = false;
		if (isset($user_info->user_id) && !empty($user_info->user_id)){
			$select = $this->db->select()
					->from("users_attributes")
					->where("users_attributes.user_id = ?", $user_info->user_id)
					->limit(1);
			$u_atribs= $this->db->fetchRow($select);
			if ($u_atribs['beta_tester'] == 1) {
				$this->show_beta = true;
			}
		}
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
        public function viewAction() {
            $select = $this->db->select()
                        ->from("groups", array("group_id", "group_name" => "concat(group_name,' (',group_description,')')"))
                        ->where("groups.group_public = ?", "1")
                        ->where("groups.group_id = ?", $this->getRequest()->getParam('group_id'));
            $this->view->group = $this->db->fetchRow($select);
            $select = $this->db->select()
                            ->from("users_groups", array())
                            ->join("users", "users.user_id=users_groups.user_id", array("user_id", "user_name", "user_email"))
                            ->where("users_groups.group_id = ?", $this->getRequest()->getParam('group_id'))
                            ->order("users.user_name");
            
               $this->view->group_users =  $this->db->fetchAll($select);
               $select = $this->db->select()
                            ->from("beer_events_groups", array())
                            ->join("beer_events", "beer_events.event_id=beer_events_groups.event_id")
                            ->where("beer_events_groups.group_id = ?", $this->getRequest()->getParam('group_id'))
                            ->order("beer_events.event_start");
                $this->view->group_events =  $this->db->fetchAll($select);
             
        }
        

}