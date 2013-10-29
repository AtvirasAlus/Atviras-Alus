<?php

class CalculusController extends Zend_Controller_Action {
	
	function init(){
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		$this->use_plato = false;
		$this->show_beta = false;
		if (isset($user_info->user_id) && !empty($user_info->user_id)){
			$db = Zend_Registry::get("db");
			$select = $db->select()
					->from("users_attributes")
					->where("users_attributes.user_id = ?", $user_info->user_id)
					->limit(1);
			$u_atribs= $db->fetchRow($select);
			if ($u_atribs['beta_tester'] == 1) {
				$this->show_beta = true;
			}
			if ($u_atribs['plato'] == 1) {
				$this->use_plato = true;
			}
		}
		$this->view->use_plato = $this->use_plato;
	}

	function indexAction() {
		//$this->_helper->layout->setLayout('main');
	}

}