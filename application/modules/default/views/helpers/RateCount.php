<?php

class Zend_View_Helper_RateCount extends Zend_View_Helper_Abstract {

	function RateCount($bid) {
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("rate_votes")
				->join("users", "users.user_id = rate_votes.user_id", array("user_name", "user_email"))
				->join("rate_systems", "rate_systems.system_id = rate_votes.system_id")
				->where("rate_votes.beer_id = ?", $bid)
				->order("rate_votes.posted DESC");
		$result = $db->fetchAll($select);
		return sizeof($result);
	}

}