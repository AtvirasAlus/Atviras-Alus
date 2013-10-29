<?php

class Zend_View_Helper_Rate extends Zend_View_Helper_Abstract {

	function Rate($bid) {
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("rate_votes")
				->join("users", "users.user_id = rate_votes.user_id", array("user_name", "user_email"))
				->join("rate_systems", "rate_systems.system_id = rate_votes.system_id")
				->where("rate_votes.beer_id = ?", $bid)
				->order("rate_votes.posted DESC");
		$result = $db->fetchAll($select);
		$total = 0;
		$votes = 0;
		foreach ($result as $item){
			$score = 0;
			switch($item['rate_type']){
				case "simple":
					$prop = $item['system_simple_max'] / 10;
					$score = round($item['simple_vote'] / $prop, 1);
				break;
				case "advanced":
					$t = 0;
					$t_s = 0;
					if ($item['system_aroma_use'] == 1){
						$t += $item['aroma_vote'];
						$t_s += $item['system_aroma_max'];
					}
					if ($item['system_appearance_use'] == 1){
						$t += $item['appearance_vote'];
						$t_s += $item['system_appearance_max'];
					}
					if ($item['system_taste_use'] == 1){
						$t += $item['taste_vote'];
						$t_s += $item['system_taste_max'];
					}
					if ($item['system_body_use'] == 1){
						$t += $item['body_vote'];
						$t_s += $item['system_body_max'];
					}
					if ($item['system_style_use'] == 1){
						$t += $item['style_vote'];
						$t_s += $item['system_style_max'];
					}
					if ($item['system_overall_use'] == 1){
						$t += $item['overall_vote'];
						$t_s += $item['system_overall_max'];
					}
					$prop = $t_s / 10;
					$score = round($t / $prop, 1);
				break;
			}
			$total += $score;
			$votes++;
		}
		if ($votes > 0){
			$total = number_format($total / $votes, 1, ".", "");
		} else {
			$total = "";
		}
		return $total;
	}

}