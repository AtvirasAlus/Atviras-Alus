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
	public function populateAction(){
		echo "<pre>";
		$activity = array();
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		
		// FORUMO ŽINUTĖS
		$select = $db->select()
				->from("bb_posts", array("post_id", "poster_id AS user_id", "post_text", "post_time", "post_position"))
				->join("bb_topics", "bb_posts.topic_id=bb_topics.topic_id", array("topic_title", "topic_id"))
				->order("bb_posts.post_time ASC");
		$select->where("1 = 2");
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$temp = array();
			$temp['act_type'] = "forum_post";
			$temp['act_user_id'] = $val['user_id'];
			$temp['act_item_id'] = $val['post_id'];
			$temp['act_posted'] = $val['post_time'];
			$data = array();
			$data['topic_id'] = $val['topic_id'];
			$data['page'] = ceil($val['post_position'] / 15);
			$data['text'] = trim(strip_tags($val['post_text']));
			$data['topic'] = $val['topic_title'];
			$temp['data'] = serialize($data);
			$activity[$val['post_time']." # ".$val['post_id']." # forum_post"] = $temp;
		}
		
		// STRAIPSNIAI
		$select = $db->select()
				->from("beer_articles", array("article_id", "article_resume", "article_title", "article_created"))
				->order("beer_articles.article_created ASC")
				->where("beer_articles.article_publish = ?", 1);
		$select->where("1 = 2");
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$temp = array();
			$temp['act_type'] = "article";
			$temp['act_user_id'] = 0;
			$temp['act_item_id'] = $val['article_id'];
			$temp['act_posted'] = $val['article_created'];
			$data = array();
			$data['title'] = trim(strip_tags($val['article_title']));
			$data['text'] = trim(strip_tags($val['article_resume']));
			$temp['data'] = serialize($data);
			$activity[$val['article_created']." # ".$val['article_id']." # article"] = $temp;
		}
		
		// STRAIPSNIŲ KOMENTARAI
		$select = $db->select()
				->from("beer_articles_comments", array("comment_id", "comment_text", "comment_brewer AS user_id", "comment_date"))
				->join("beer_articles", "beer_articles_comments.comment_article = beer_articles.article_id", array("article_id", "article_title"))
				->order("beer_articles_comments.comment_date ASC")
				->where("beer_articles.article_publish = ?", 1);
		$select->where("1 = 2");
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$temp = array();
			$temp['act_type'] = "article_comment";
			$temp['act_user_id'] = $val['user_id'];
			$temp['act_item_id'] = $val['comment_id'];
			$temp['act_posted'] = $val['comment_date'];
			$data = array();
			$data['title'] = trim(strip_tags($val['article_title']));
			$data['text'] = trim(strip_tags($val['comment_text']));
			$data['article_id'] = $val['article_id'];
			$temp['data'] = serialize($data);
			$activity[$val['comment_date']." # ".$val['comment_id']." # article_comment"] = $temp;
		}
		
		// VIRIMAI
		$select = $db->select()
				->from("beer_brew_sessions", array("session_id", "session_brewer AS user_id", "session_size", "session_primarydate"))
				->join("beer_recipes", "beer_brew_sessions.session_recipe = beer_recipes.recipe_id", array("recipe_id", "recipe_name", "recipe_publish"))
				->order("beer_brew_sessions.session_primarydate ASC")
				->where("beer_brew_sessions.session_primarydate != '0000-00-00' AND beer_brew_sessions.session_size != '0'");
		$select->where("1 = 2");
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$temp = array();
			$temp['act_type'] = "session";
			$temp['act_user_id'] = $val['user_id'];
			$temp['act_item_id'] = $val['session_id'];
			$temp['act_posted'] = $val['session_primarydate'];
			$data = array();
			$data['size'] = $val['session_size'];
			$data['recipe_name'] = trim(strip_tags($val['recipe_name']));
			$data['recipe_id'] = $val['recipe_id'];
			$data['recipe_publish'] = $val['recipe_publish'];
			$temp['data'] = $data;
			$activity[$val['session_primarydate']." # ".$val['session_id']." # session"] = $temp;
		}
				
		/* ======================================= */
		
		ksort($activity);
		print_r($activity);
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
