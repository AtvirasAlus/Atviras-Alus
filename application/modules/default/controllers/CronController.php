<?php

class CronController extends Zend_Controller_Action {

	public function init() {

	}

	public function indexAction() {
		
	}

	public function favoritesAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$select = "select count(distinct `beer_recipes_favorites`.`user_id`) AS `votes`,count(distinct `beer_recipes_comments`.`comment_id`) AS `comments`,count(distinct `beer_brew_sessions`.`session_id`) AS `sessions`,`beer_recipes`.`recipe_id` AS `recipe_id`,`beer_recipes`.`recipe_style` AS `recipe_style`,`beer_recipes`.`brewer_id` AS `brewer_id`,`beer_recipes`.`recipe_name` AS `recipe_name`,((count(distinct `beer_recipes_comments`.`comment_id`) + (count(distinct `beer_brew_sessions`.`session_id`) * 3)) + (count(distinct `beer_recipes_favorites`.`user_id`) * 5)) AS `total` from (`beer_recipes_comments` left join ((`beer_recipes` left join `beer_recipes_favorites` on((`beer_recipes_favorites`.`recipe_id` = `beer_recipes`.`recipe_id`))) left join `beer_brew_sessions` on((`beer_brew_sessions`.`session_recipe` = `beer_recipes`.`recipe_id`))) on((`beer_recipes_comments`.`comment_recipe` = `beer_recipes`.`recipe_id`))) where (`beer_recipes`.`recipe_publish` = '1') group by `beer_recipes`.`recipe_id` order by ((count(distinct `beer_recipes_comments`.`comment_id`) + (count(distinct `beer_brew_sessions`.`session_id`) * 3)) + (count(distinct `beer_recipes_favorites`.`user_id`) * 5)) desc";
		$result = $db->fetchAll($select);
		$db->query("TRUNCATE TABLE cache_fav_recipes");
		foreach($result as $key=>$val){
			$db->insert("cache_fav_recipes", array(
				"votes" => $val['votes'],
				"comments" => $val['comments'],
				"sessions" => $val['sessions'],
				"recipe_id" => $val['recipe_id'],
				"recipe_style" => $val['recipe_style'],
				"brewer_id" => $val['brewer_id'],
				"recipe_name" => $val['recipe_name'],
				"total" => $val['total'],
				"updated" => date("Y-m-d H:i:s"),
			));
		}
		echo "Done.";
		
	}
	
	public function fixideasAction() {
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("idea_items");
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$db->delete("idea_votes", array("user_id = '" . $val['user_id']. "'", "idea_id = '" . $val['idea_id'] . "'"));
		}
		
		foreach ($result as $key=>$val){
			$select2 = $db->select()
					->from("idea_votes", "SUM(idea_votes.vote_value) as total")
					->where("idea_votes.idea_id = ?", $val['idea_id']);
			$result2 = $db->fetchRow($select2);
			$db->update("idea_items", array(
				"idea_vote_sum" => $result2['total']
			), "idea_items.idea_id = '".$val['idea_id']."'");
		}
		
		echo "Done";
	}

}