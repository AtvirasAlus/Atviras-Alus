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
		set_time_limit(0);
		$tpl = array();
		$tpl['user_id'] = NULL;
		$tpl['item_id'] = NULL;
		$tpl['posted'] = NULL;
		$tpl['type'] = NULL;
		$tpl['forum_post_topic_id'] = NULL;
		$tpl['forum_post_post_position'] = NULL;
		$tpl['forum_post_post_text'] = NULL;
		$tpl['forum_post_topic_title'] = NULL;
		$tpl['article_article_title'] = NULL;
		$tpl['article_article_resume'] = NULL;
		$tpl['article_comment_article_title'] = NULL;
		$tpl['article_comment_comment_text'] = NULL;
		$tpl['article_comment_comment_article'] = NULL;
		$tpl['session_size'] = NULL;
		$tpl['session_recipe_name'] = NULL;
		$tpl['session_recipe_id'] = NULL;
		$tpl['session_recipe_publish'] = NULL;
		$tpl['event_name'] = NULL;
		$tpl['event_resume'] = NULL;
		$tpl['event_start'] = NULL;
		$tpl['event_registration_end'] = NULL;
		$tpl['event_type'] = NULL;
		$tpl['event_published'] = NULL;
		$tpl['recipe_name'] = NULL;
		$tpl['recipe_style_id'] = NULL;
		$tpl['recipe_style_name'] = NULL;
		$tpl['recipe_comment_recipe_name'] = NULL;
		$tpl['recipe_comment_text'] = NULL;
		$tpl['recipe_comment_recipe_id'] = NULL;
		$tpl['tweet_message'] = NULL;
		$tpl['tweet_link_url'] = NULL;
		$tpl['tweet_link_description'] = NULL;
		$tpl['tweet_link_title'] = NULL;
		$tpl['tweet_link_image'] = NULL;
		$tpl['idea_title'] = NULL;
		$tpl['idea_description'] = NULL;
		$tpl['idea_status'] = NULL;
		$tpl['idea_finishdate'] = NULL;
		$tpl['idea_comment_idea_title'] = NULL;
		$tpl['idea_comment_text'] = NULL;
		$tpl['idea_comment_idea_id'] = NULL;
		$tpl['user_name'] = NULL;
		
		$limit = 100000000000;

		echo "<pre>";
		$activity = array();
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		
		// FORUMO ŽINUTĖS
		$select = $db->select()
				->from("bb_posts", array("post_id", "poster_id AS user_id", "post_text", "post_time", "post_position"))
				->join("bb_topics", "bb_posts.topic_id=bb_topics.topic_id", array("topic_title", "topic_id"))
				->order("bb_posts.post_time ASC")
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$temp = $tpl;
			$temp['type'] = "forum_post";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['post_id'];
			$temp['posted'] = $val['post_time'];
			$temp['forum_post_topic_id'] = $val['topic_id'];
			$temp['forum_post_post_position'] = $val['post_position'];
			$temp['forum_post_post_text'] = $val['post_text'];
			$temp['forum_post_topic_title'] = $val['topic_title'];
			$activity[] = $temp;
		}
		
		// STRAIPSNIAI
		$select = $db->select()
				->from("beer_articles", array("article_id", "article_resume", "article_title", "article_created"))
				->order("beer_articles.article_created ASC")
				->where("beer_articles.article_publish = ?", 1)
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$temp = $tpl;
			$temp['type'] = "article";
			$temp['user_id'] = 0;
			$temp['item_id'] = $val['article_id'];
			$temp['posted'] = $val['article_created'];
			$temp['article_article_title'] = $val['article_title'];
			$temp['article_article_resume'] = $val['article_resume'];
			$activity[] = $temp;
		}

		// STRAIPSNIŲ KOMENTARAI
		$select = $db->select()
				->from("beer_articles_comments", array("comment_id", "comment_text", "comment_brewer AS user_id", "comment_date"))
				->join("beer_articles", "beer_articles_comments.comment_article = beer_articles.article_id", array("article_id", "article_title"))
				->order("beer_articles_comments.comment_date ASC")
				->where("beer_articles.article_publish = ?", 1)
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$temp = $tpl;
			$temp['type'] = "article_comment";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['comment_id'];
			$temp['posted'] = $val['comment_date'];
			$temp['article_comment_article_title'] = $val['article_title'];
			$temp['article_comment_comment_text'] = $val['comment_text'];
			$temp['article_comment_comment_article'] = $val['article_id'];
			$activity[] = $temp;
		}
		
		// VIRIMAI
		$select = $db->select()
				->from("beer_brew_sessions", array("session_id", "session_brewer AS user_id", "session_size", "session_primarydate"))
				->join("beer_recipes", "beer_brew_sessions.session_recipe = beer_recipes.recipe_id", array("recipe_id", "recipe_name", "recipe_publish"))
				->order("beer_brew_sessions.session_primarydate ASC")
				->where("beer_brew_sessions.session_primarydate != '0000-00-00' AND beer_brew_sessions.session_size != '0'")
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$temp = $tpl;
			$temp['type'] = "session";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['session_id'];
			$temp['posted'] = $val['session_primarydate'];
			$temp['session_size'] = $val['session_size'];
			$temp['session_recipe_name'] = $val['recipe_name'];
			$temp['session_recipe_id'] = $val['recipe_id'];
			$temp['session_recipe_publish'] = $val['recipe_publish'];
			$activity[] = $temp;
		}
		
		// ĮVYKIAI
		$select = $db->select()
				->from("beer_events", array("event_id", "event_name", "event_resume", "event_start", "event_registration_end", "event_type", "event_posted", "event_published"))
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$temp = $tpl;
			$temp['type'] = "event";
			$temp['user_id'] = 0;
			$temp['item_id'] = $val['event_id'];
			if ($val['event_posted'] == "0000-00-00 00:00:00") $val['event_posted'] = $val['event_start']; 
			$temp['posted'] = $val['event_posted'];
			$temp['event_name'] = $val['event_name'];
			$temp['event_resume'] = $val['event_resume'];
			$temp['event_start'] = $val['event_start'];
			$temp['event_registration_end'] = $val['event_registration_end'];
			$temp['event_type'] = $val['event_type'];
			$temp['event_published'] = $val['event_published'];
			$activity[] = $temp;
		}
		
		// RECEPTAI
		$select = $db->select()
				->from("beer_recipes", array("recipe_id", "recipe_name", "recipe_style", "brewer_id AS user_id", "recipe_created", "recipe_published"))
				->joinLeft("beer_styles", "beer_recipes.recipe_style=beer_styles.style_id", array("style_name"))
				->where("beer_recipes.recipe_publish = '1'")
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$temp = $tpl;
			$temp['type'] = "recipe";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['recipe_id'];
			if ($val['recipe_published'] == "0000-00-00 00:00:00") $val['recipe_published'] = $val['recipe_created']; 
			$temp['posted'] = $val['recipe_published'];
			$temp['recipe_name'] = $val['recipe_name'];
			$temp['recipe_style_id'] = $val['recipe_style'];
			$temp['recipe_style_name'] = $val['style_name'];
			$activity[] = $temp;
		}
		
		// RECEPTŲ KOMENTARAI
		$select = $db->select()
				->from("beer_recipes_comments", array("comment_id", "comment_text", "comment_brewer AS user_id", "comment_date"))
				->join("beer_recipes", "beer_recipes_comments.comment_recipe = beer_recipes.recipe_id", array("recipe_id", "recipe_name"))
				->where("beer_recipes.recipe_publish = 1")
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$temp = $tpl;
			$temp['type'] = "recipe_comment";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['comment_id'];
			$temp['posted'] = $val['comment_date'];
			$temp['recipe_comment_recipe_name'] = $val['recipe_name'];
			$temp['recipe_comment_text'] = $val['comment_text'];
			$temp['recipe_comment_recipe_id'] = $val['recipe_id'];
			$activity[] = $temp;
		}

		// BURBULIATORIUS
		$select = $db->select()
				->from("beer_tweets", array("tweet_id", "tweet_message", "tweet_owner AS user_id", "tweet_link_url", "tweet_link_description", "tweet_link_title", "tweet_link_image", "tweet_date"))
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$temp = $tpl;
			$temp['type'] = "tweet";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['tweet_id'];
			$temp['posted'] = $val['tweet_date'];
			$temp['tweet_message'] = $val['tweet_message'];
			$temp['tweet_link_url'] = $val['tweet_link_url'];
			$temp['tweet_link_description'] = $val['tweet_link_description'];
			$temp['tweet_link_title'] = $val['tweet_link_title'];
			$temp['tweet_link_image'] = $val['tweet_link_image'];
			$activity[] = $temp;
		}

		// IDĖJŲ KOMENTARAI
		$select = $db->select()
				->from("idea_comments", array("comment_id", "comment_text", "user_id", "comment_date"))
				->join("idea_items", "idea_comments.idea_id = idea_items.idea_id", array("idea_id", "idea_title"))
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$temp = $tpl;
			$temp['type'] = "idea_comment";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['comment_id'];
			$temp['posted'] = $val['comment_date'];
			$temp['idea_comment_idea_title'] = $val['idea_title'];
			$temp['idea_comment_text'] = $val['comment_text'];
			$temp['idea_comment_idea_id'] = $val['idea_id'];
			$activity[] = $temp;
		}

		// IDĖJOS
		$select = $db->select()
				->from("idea_items", array("idea_id", "idea_title", "idea_description", "user_id", "idea_posted", "idea_finishdate", "idea_status"))
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$temp = $tpl;
			$temp['type'] = "idea";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['idea_id'];
			$temp['posted'] = $val['idea_posted'];
			$temp['idea_title'] = $val['idea_title'];
			$temp['idea_description'] = $val['idea_description'];
			$temp['idea_status'] = $val['idea_status'];
			$temp['idea_finishdate'] = $val['idea_finishdate'];
			$activity[] = $temp;
		}

		// NAUDOTOJAI
		$select = $db->select()
				->from("users", array("user_id", "user_name", "user_created"))
				->where("users.user_active = '1' AND users.user_enabled = '1'")
				->limit($limit);
		$result = $db->fetchAll($select);
		foreach($result as $key=>$val){
			$temp = $tpl;
			$temp['type'] = "user";
			$temp['user_id'] = $val['user_id'];
			$temp['item_id'] = $val['user_id'];
			$temp['posted'] = $val['user_created'];
			$temp['user_name'] = $val['user_name'];
			$activity[] = $temp;
		}
		
		$sql = "TRUNCATE TABLE activity";
		$db->query($sql);

		foreach($activity as $key=>$val) {
			$db->insert("activity", $val);
		}
		echo "Done.";
		//print_r($activity);
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