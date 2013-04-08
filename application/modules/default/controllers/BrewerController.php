<?php

class BrewerController extends Zend_Controller_Action {

	public function init() {
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		$this->show_beta = false;
		$this->show_list = false;
		if (isset($user_info->user_id) && !empty($user_info->user_id)) {
			$db = Zend_Registry::get("db");
			$select = $db->select()
					->from("users_attributes")
					->where("users_attributes.user_id = ?", $user_info->user_id)
					->limit(1);
			$u_atribs = $db->fetchRow($select);
			if ($u_atribs['beta_tester'] == 1) {
				$this->show_beta = true;
			}
			if ($u_atribs['recipe_list'] == 1) {
				$this->show_list = true;
			}
		}
	}

	public function indexAction() {
		
	}

	public function favoritesAction() {
		$db = Zend_Registry::get('db');
		$storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $storage->read();
		$u = $this->view->user_info;
		if (isset($u->user_id) && !empty($u->user_id)) {
			$this->view->pleaselogin = false;
			$me = $u->user_id;
			$select = $db->select()
					->from("beer_recipes_favorites")
					->join("beer_recipes", "beer_recipes_favorites.recipe_id=beer_recipes.recipe_id", array("recipe_name"))
					->join("beer_styles", "beer_recipes.recipe_style=beer_styles.style_id", array("style_name", "style_id"))
					->where("beer_recipes_favorites.user_id = ?", $me)
					->where("beer_recipes.brewer_id = ?", $me)
					->order("beer_recipes_favorites.favorite_date DESC");
			$result = $db->fetchAll($select);
			$this->view->myfavorites = $result;
			$select = $db->select()
					->from("beer_recipes_favorites")
					->join("beer_recipes", "beer_recipes_favorites.recipe_id=beer_recipes.recipe_id", array("recipe_name"))
					->join("beer_styles", "beer_recipes.recipe_style=beer_styles.style_id", array("style_name", "style_id"))
					->join("users", "beer_recipes.brewer_id=users.user_id", array("user_name as brewer_name", "user_id as brewer_id"))
					->where("beer_recipes_favorites.user_id = ?", $me)
					->where("beer_recipes.brewer_id != ?", $me)
					->order("beer_recipes_favorites.favorite_date DESC");
			$result = $db->fetchAll($select);
			$this->view->favorites = $result;
		} else {
			$this->view->pleaselogin = true;
		}
	}

	public function infoAction() {
		$db = Zend_Registry::get('db');
		$this->view->user_info = array("total_brewed_mead" => 0, "total_brewed_kvass" => 0, "total_brewed_cider" => 0, "total_sessions" => 0, "total_brewed" => 0, "total_recipes" => 0, "user_lastlogin" => 0, "user_created" => 0, "user_name" => '');
		if ($this->_getParam('brewer') > 0) {
			$brewer = $this->_getParam('brewer');

			// BREWER INFO
			$select = $db->select()
					->from("beer_awards")
					->join("beer_recipes", "beer_recipes.recipe_id=beer_awards.recipe_id", array("recipe_name"))
					->where("beer_recipes.brewer_id = ?", $brewer)
					->order("beer_awards.posted DESC");
			$awards = $db->fetchAll($select);
			$this->view->awards = $awards;
			$select = $db->select()
					->from("users_nominations")
					->where("users_nominations.user_id = ?", $brewer)
					->order("users_nominations.posted DESC");
			$nominations = $db->fetchAll($select);
			$this->view->nominations = $nominations;
			$select = $db->select()
					->from("users")
					->joinLeft("users_attributes", "users_attributes.user_id=users.user_id", array("user_location", "user_about"))
					->where("users.user_active = ?", '1')
					->where("users.user_id = ?", $brewer);
			$this->view->user_info = $db->fetchRow($select);
			if (!isset($this->view->user_info['user_id'])) {
				$this->_redirect("/");
			}
			$this->view->user_info['user_id'] = $brewer;
			if ($this->view->user_info) {
				$select = $db->select()
						->from("beer_recipes", array("count" => "count(*)"))
						->where("beer_recipes.recipe_publish = ?", '1')
						->where("beer_recipes.brewer_id= ?", $brewer);
				if ($row = $db->fetchRow($select)) {
					$this->view->user_info["total_recipes"] = $row["count"];
				}
				$select = $db->select()
						->from("beer_brew_sessions", array("count" => "count(*)", "total" => "COALESCE(sum(session_size),0)"))
						->joinLeft("beer_recipes", "beer_recipes.recipe_id = beer_brew_sessions.session_recipe")
						->where("beer_recipes.recipe_style != ?", 82) //kvass
						->where("beer_recipes.recipe_style != ?", 84) //cider
						->where("beer_recipes.recipe_style != ?", 85) //mead
						->where("session_brewer =?", $brewer);
				if ($row = $db->fetchRow($select)) {
					$this->view->user_info["total_brewed"] = $row["total"];
					$this->view->user_info["total_sessions"] = $row["count"];
				}
				$select = $db->select()
						->from("beer_brew_sessions", array("count" => "count(*)", "total" => "COALESCE(sum(session_size),0)"))
						->joinLeft("beer_recipes", "beer_recipes.recipe_id = beer_brew_sessions.session_recipe")
						->where("beer_recipes.recipe_style = ?", 82)
						->where("session_brewer =?", $brewer);
				if ($row = $db->fetchRow($select)) {
					$this->view->user_info["total_brewed_kvass"] = $row["total"];
					$this->view->user_info["total_sessions"] += $row["count"];
				}
				$select = $db->select()
						->from("beer_brew_sessions", array("count" => "count(*)", "total" => "COALESCE(sum(session_size),0)"))
						->joinLeft("beer_recipes", "beer_recipes.recipe_id = beer_brew_sessions.session_recipe")
						->where("beer_recipes.recipe_style = ?", 84)
						->where("session_brewer =?", $brewer);
				if ($row = $db->fetchRow($select)) {
					$this->view->user_info["total_brewed_cider"] = $row["total"];
					$this->view->user_info["total_sessions"] += $row["count"];
				}
				$select = $db->select()
						->from("beer_brew_sessions", array("count" => "count(*)", "total" => "COALESCE(sum(session_size),0)"))
						->joinLeft("beer_recipes", "beer_recipes.recipe_id = beer_brew_sessions.session_recipe")
						->where("beer_recipes.recipe_style = ?", 85)
						->where("session_brewer =?", $brewer);
				if ($row = $db->fetchRow($select)) {
					$this->view->user_info["total_brewed_mead"] = $row["total"];
					$this->view->user_info["total_sessions"] += $row["count"];
				}
				$select = $db->select()
						->from("beer_brew_sessions", array("count" => "count(*)", "total" => "COALESCE(sum(session_size),0)"))
						->joinLeft("beer_recipes", "beer_recipes.recipe_id = beer_brew_sessions.session_recipe")
						->where("session_brewer =?", $brewer);
				if ($row = $db->fetchRow($select)) {
					$this->view->user_info["total_brewed_unknown"] = number_format($row["total"] -
							$this->view->user_info["total_brewed_kvass"] -
							$this->view->user_info["total_brewed_cider"] -
							$this->view->user_info["total_brewed_mead"] -
							$this->view->user_info["total_brewed"], 1);
					$this->view->user_info["total_sessions"] = $row["count"];
				}
				$select = $db->select()
						->from("beer_recipes", array("count" => "count(*)"))
						->where("beer_recipes.recipe_publish = ?", '1')
						->where("beer_recipes.recipe_total_sessions = ?", '0')
						->where("beer_recipes.brewer_id= ?", $brewer);
				$row = $db->fetchRow($select);
				$this->view->hidden_recipes = $row["count"];
				$select = $db->select()
						->from("beer_recipes")
						->where("beer_recipes.recipe_publish = ?", '1')
						->where("beer_recipes.brewer_id= ?", $brewer);
				if (!isset($_COOKIE['show_empty_recipes']) || $_COOKIE['show_empty_recipes'] != "1") {
					$select->where("recipe_total_sessions > 0");
				}
				$this->view->user_info["recipes"] = array();
				if ($rows = $db->fetchAll($select)) {
					$this->view->user_info["recipes"] = $rows;
				}
				$select = $db->select()
						->from("beer_recipes_tags", array("weight" => "count(tag_text)", "tag_text" => "tag_text"))
						->where("tag_owner = ?", $brewer)
						->group("tag_text")
						->order("weight DESC")
						->limit(100);

				$this->view->user_info["tags"] = array();
				if ($rows = $db->fetchAll($select)) {
					$this->view->user_info["tags"] = $rows;
				}
				$select = $db->select()
						->from("beer_brew_sessions", array("session_caskingdate", "session_secondarydate", "session_primarydate", "session_recipe", "session_fg", "session_og", "session_size", "session_brewer", "session_name", "session_id", 'session_comments' => 'LEFT( session_comments, LOCATE( " ", session_comments, 30 ) )'))
						->join("users", "beer_brew_sessions.session_brewer = users.user_id")
						->joinLeft("beer_recipes", "beer_brew_sessions.session_recipe=beer_recipes.recipe_id", array("recipe_id", "recipe_name", "recipe_publish", "recipe_abv"))
						->joinLeft("beer_styles", "beer_styles.style_id = beer_recipes.recipe_style", array("style_class"))
						->joinLeft("users AS recu", "beer_recipes.brewer_id=recu.user_id", array("user_id AS recu_id", "user_name AS recu_name"))
						->where("beer_brew_sessions.session_brewer =?", $brewer)
						->order("session_primarydate DESC");
				$this->view->user_info['sessions'] = array();
				$this->view->user_info['sessions_size'] = 0;
				if ($rows = $db->fetchAll($select)) {
					$this->view->user_info["sessions_size"] = sizeof($rows);
					$this->view->user_info["sessions"] = array_slice($rows, 0, 10);
				}

				$filter_type = $this->getRequest()->getParam("type");
				if (!isset($filter_type) || empty($filter_type))
					$filter_type = "all";
				$this->view->filter_type = $filter_type;
				$select = $db->select()
						->from("activity")
						->joinLeft("users", "users.user_id = activity.user_id", array("user_name", "MD5 (user_email) as email_hash"))
						->order("posted DESC")
						->order("id DESC")
						->where("activity.user_id = ?", $brewer)
						->limit(30);
				switch ($filter_type) {
					case "vote":
						$select->where("type = 'vote'");
						break;
					case "market":
						$select->where("type = 'market'");
						break;
					case "market_comment":
						$select->where("type = 'market_comment'");
						break;
					case "idea":
						$select->where("type = 'idea'");
						break;
					case "idea_comment":
						$select->where("type = 'idea_comment'");
						break;
					case "forum_post":
						$select->where("type = 'forum_post'");
						break;
					case "article":
						$select->where("type = 'article'");
						break;
					case "article_comment":
						$select->where("type = 'article_comment'");
						break;
					case "session":
						$select->where("type = 'session'");
						break;
					case "event":
						$select->where("type = 'event'");
						break;
					case "event_comment":
						$select->where("type = 'event_comment'");
						break;
					case "food":
						$select->where("type = 'food'");
						break;
					case "food_comment":
						$select->where("type = 'food_comment'");
						break;
					case "recipe":
						$select->where("type = 'recipe'");
						break;
					case "recipe_comment":
						$select->where("type = 'recipe_comment'");
						break;
					case "tweet":
						$select->where("type = 'tweet'");
						break;
					case "user":
						$select->where("type = 'user'");
						break;
					case "rss":
						$select->where("type = 'rss'");
						break;
				}
				$result = $db->fetchAll($select);
				$this->view->activity = $result;

				// STATISTICS INFO
				$select = $db->select()
						->from("beer_brew_sessions", array("DISTINCT(session_brewer) AS brewer_id", "SUM(session_size) AS kiekis"))
						->order("kiekis DESC")
						->group("session_brewer");
				$result = $db->FetchAll($select);
				$this->view->total_positions = sizeof($result);
				$this->view->positions = $result;
				$pos = 0;
				foreach($result as $key=>$val){
					$pos++;
					if ($val['brewer_id'] == $this->_getParam("brewer")) {
						$this->view->position = $pos;
						$this->view->position_amount = $val['kiekis'];
						break;
					}
				}

				$select = $db->select()
						->from("beer_brew_sessions", array("session_primarydate AS date", "session_size AS size"))
						->where("session_brewer = ?", $this->_getParam("brewer"))
						->where("session_primarydate <= ?", date("Y-m-d"))
						->where("session_primarydate >= ?", $this->view->user_info['user_created'])
						->order("session_primarydate ASC");
				$result = $db->FetchAll($select);
				$statsess = array();
				$sum = 0;
				foreach($result as $key=>$val){
					$d = strtotime($val['date'])*1000;
					if (isset($statsess[$d])){
						$statsess[$d] += $val['size'];
					} else {
						$statsess[$d] = $sum + $val['size'];
					}
					$sum += $val['size'];
				}
				if (sizeof($statsess) > 0){
					$now = strtotime(date("Y-m-d"))*1000;
					if (!isset($statsess[$now])){
						$statsess[$now] = $sum;
					}
				}
				$this->view->statsess = $statsess;
				
				$select = $db->select()
						->from("beer_recipes", array("recipe_ibu"))
						->where("brewer_id = ?", $brewer)
						->order("recipe_ibu DESC");
				$result = $db->FetchAll($select);
				$iburt['light'] = false;
				$iburt['medium'] = false;
				$iburt['strong'] = false;
				foreach($result as $key=>$val){
					if ($iburt['light'] === false && $val['recipe_ibu'] <= 30) $iburt['light'] = $key;
					if ($iburt['medium'] === false && $val['recipe_ibu'] <= 60 && $val['recipe_ibu'] > 30) $iburt['medium'] = $key;
					if ($iburt['strong'] === false && $val['recipe_ibu'] > 60) $iburt['strong'] = $key;
				}
				$this->view->iburt = $iburt;
				$this->view->statribu = $result;
				if (sizeof($result) > 0){
					$select = $db->select()
							->from("beer_recipes", array("AVG(recipe_ibu) as average_ibu"))
							->where("brewer_id = ?", $brewer);
					$result = $db->FetchRow($select);
					$this->view->statribu_avg = $result['average_ibu'];
				}

				$select = $db->select()
						->from("beer_brew_sessions", array())
						->join("beer_recipes", "beer_recipes.recipe_id = beer_brew_sessions.session_recipe", array("recipe_ibu"))
						->where("beer_brew_sessions.session_brewer = ?", $brewer)
						->order("recipe_ibu DESC");
				$result = $db->FetchAll($select);
				$ibust['light'] = false;
				$ibust['medium'] = false;
				$ibust['strong'] = false;
				foreach($result as $key=>$val){
					if ($ibust['light'] === false && $val['recipe_ibu'] <= 30) $ibust['light'] = $key;
					if ($ibust['medium'] === false && $val['recipe_ibu'] <= 60 && $val['recipe_ibu'] > 30) $ibust['medium'] = $key;
					if ($ibust['strong'] === false && $val['recipe_ibu'] > 60) $ibust['strong'] = $key;
				}
				$this->view->ibust = $ibust;
				$this->view->statsibu = $result;
				if (sizeof($result) > 0){
					$select = $db->select()
							->from("beer_brew_sessions", array())
							->join("beer_recipes", "beer_recipes.recipe_id = beer_brew_sessions.session_recipe", array("AVG(recipe_ibu) as average_ibu"))
							->where("beer_brew_sessions.session_brewer = ?", $brewer);
					$result = $db->FetchRow($select);
					$this->view->statsibu_avg = $result['average_ibu'];
				}
				
				$select = $db->select()
						->from("beer_recipes", array("recipe_ebc"))
						->where("brewer_id = ?", $brewer)
						->order("recipe_ebc DESC");
				$result = $db->FetchAll($select);
				$ebcrt['light'] = false;
				$ebcrt['medium'] = false;
				$ebcrt['strong'] = false;
				foreach($result as $key=>$val){
					if ($ebcrt['light'] === false && $val['recipe_ebc'] <= 15) $ebcrt['light'] = $key;
					if ($ebcrt['medium'] === false && $val['recipe_ebc'] <= 38 && $val['recipe_ebc'] > 15) $ebcrt['medium'] = $key;
					if ($ebcrt['strong'] === false && $val['recipe_ebc'] > 38) $ebcrt['strong'] = $key;
				}
				$this->view->ebcrt = $ebcrt;
				$this->view->statrebc = $result;
				if (sizeof($result) > 0){
					$select = $db->select()
							->from("beer_recipes", array("AVG(recipe_ebc) as average_ebc"))
							->where("brewer_id = ?", $brewer);
					$result = $db->FetchRow($select);
					$this->view->statrebc_avg = $result['average_ebc'];
				}

				$select = $db->select()
						->from("beer_brew_sessions", array())
						->join("beer_recipes", "beer_recipes.recipe_id = beer_brew_sessions.session_recipe", array("recipe_ebc"))
						->where("beer_brew_sessions.session_brewer = ?", $brewer)
						->order("recipe_ebc DESC");
				$result = $db->FetchAll($select);
				$ebcst['light'] = false;
				$ebcst['medium'] = false;
				$ebcst['strong'] = false;
				foreach($result as $key=>$val){
					if ($ebcst['light'] === false && $val['recipe_ebc'] <= 15) $ebcst['light'] = $key;
					if ($ebcst['medium'] === false && $val['recipe_ebc'] <= 38 && $val['recipe_ebc'] > 15) $ebcst['medium'] = $key;
					if ($ebcst['strong'] === false && $val['recipe_ebc'] > 38) $ebcst['strong'] = $key;
				}
				$this->view->ebcst = $ebcst;
				$this->view->statsebc = $result;
				if (sizeof($result) > 0){
					$select = $db->select()
							->from("beer_brew_sessions", array())
							->join("beer_recipes", "beer_recipes.recipe_id = beer_brew_sessions.session_recipe", array("AVG(recipe_ebc) as average_ebc"))
							->where("beer_brew_sessions.session_brewer = ?", $brewer);
					$result = $db->FetchRow($select);
					$this->view->statsebc_avg = $result['average_ebc'];
				}
				
				$select = $db->select()
						->from("beer_recipes", array("recipe_abv"))
						->where("brewer_id = ?", $brewer)
						->order("recipe_abv DESC");
				$result = $db->FetchAll($select);
				$abvrt['light'] = false;
				$abvrt['medium'] = false;
				$abvrt['strong'] = false;
				foreach($result as $key=>$val){
					if ($abvrt['light'] === false && $val['recipe_abv'] <= 5.5) $abvrt['light'] = $key;
					if ($abvrt['medium'] === false && $val['recipe_abv'] <= 9.5 && $val['recipe_abv'] > 5.5) $abvrt['medium'] = $key;
					if ($abvrt['strong'] === false && $val['recipe_abv'] > 9.5) $abvrt['strong'] = $key;
				}
				$this->view->abvrt = $abvrt;
				$this->view->statrabv = $result;
				if (sizeof($result) > 0){
					$select = $db->select()
							->from("beer_recipes", array("AVG(recipe_abv) as average_abv"))
							->where("brewer_id = ?", $brewer);
					$result = $db->FetchRow($select);
					$this->view->statrabv_avg = $result['average_abv'];
				}

				$select = $db->select()
						->from("beer_brew_sessions", array())
						->join("beer_recipes", "beer_recipes.recipe_id = beer_brew_sessions.session_recipe", array("recipe_abv"))
						->where("beer_brew_sessions.session_brewer = ?", $brewer)
						->order("recipe_abv DESC");
				$result = $db->FetchAll($select);
				$abvst['light'] = false;
				$abvst['medium'] = false;
				$abvst['strong'] = false;
				foreach($result as $key=>$val){
					if ($abvst['light'] === false && $val['recipe_abv'] <= 5.5) $abvst['light'] = $key;
					if ($abvst['medium'] === false && $val['recipe_abv'] <= 9.5 && $val['recipe_abv'] > 5.5) $abvst['medium'] = $key;
					if ($abvst['strong'] === false && $val['recipe_abv'] > 9.5) $abvst['strong'] = $key;
				}
				$this->view->abvst = $abvst;
				$this->view->statsabv = $result;
				if (sizeof($result) > 0){
					$select = $db->select()
							->from("beer_brew_sessions", array())
							->join("beer_recipes", "beer_recipes.recipe_id = beer_brew_sessions.session_recipe", array("AVG(recipe_abv) as average_abv"))
							->where("beer_brew_sessions.session_brewer = ?", $brewer);
					$result = $db->FetchRow($select);
					$this->view->statsabv_avg = $result['average_abv'];
				}
			}
		}
	}

	public function moreAction() {
		$this->_helper->layout->disableLayout();
		$db = Zend_Registry::get("db");
		$storage = new Zend_Auth_Storage_Session();
		$brewer = $this->_getParam('brewer');
		$filter_type = $this->getRequest()->getParam("type");
		$last_id = $this->getRequest()->getParam("last");
		$select = $db->select("posted")
				->from("activity")
				->where("id = ?", $last_id)
				->limit(1);
		$result = $db->fetchRow($select);
		$last_stamp = $result['posted'];
		$this->view->last_stamp = $last_stamp;
		if (!isset($filter_type) || empty($filter_type))
			$filter_type = "all";
		$this->view->filter_type = $filter_type;
		$select = $db->select()
				->from("activity")
				->joinLeft("users", "users.user_id = activity.user_id", array("user_name", "MD5 (user_email) as email_hash"))
				->order("posted DESC")
				->order("id DESC")
				->where("posted < '" . $last_stamp . "'")
				->where("activity.user_id = ?", $brewer)
				->limit(30);
		switch ($filter_type) {
			case "vote":
				$select->where("type = 'vote'");
				break;
			case "market":
				$select->where("type = 'market'");
				break;
			case "market_comment":
				$select->where("type = 'market_comment'");
				break;
			case "idea":
				$select->where("type = 'idea'");
				break;
			case "idea_comment":
				$select->where("type = 'idea_comment'");
				break;
			case "forum_post":
				$select->where("type = 'forum_post'");
				break;
			case "article":
				$select->where("type = 'article'");
				break;
			case "article_comment":
				$select->where("type = 'article_comment'");
				break;
			case "session":
				$select->where("type = 'session'");
				break;
			case "event":
				$select->where("type = 'event'");
				break;
			case "event_comment":
				$select->where("type = 'event_comment'");
				break;
			case "food":
				$select->where("type = 'food'");
				break;
			case "food_comment":
				$select->where("type = 'food_comment'");
				break;
			case "recipe":
				$select->where("type = 'recipe'");
				break;
			case "recipe_comment":
				$select->where("type = 'recipe_comment'");
				break;
			case "tweet":
				$select->where("type = 'tweet'");
				break;
			case "user":
				$select->where("type = 'user'");
				break;
			case "rss":
				$select->where("type = 'rss'");
				break;
		}
		$result = $db->fetchAll($select);
		$this->view->items = $result;
	}

	public function sessionsAction() {

		$db = Zend_Registry::get('db');
		$this->view->user_info = array("total_sessions" => 0, "total_brewed" => 0, "total_recipes" => 0, "user_lastlogin" => 0, "user_created" => 0, "user_name" => '');
		if ($this->_getParam('brewer') > 0) {
			$brewer = $this->_getParam('brewer');
			$select = $db->select()
					->from("users")
					->where("users.user_active = ?", '1')
					->where("users.user_id = ?", $brewer);
			$this->view->user_info = $db->fetchRow($select);
			if ($this->view->user_info) {
				$select = $db->select()
						->from("beer_brew_sessions", array("session_caskingdate", "session_secondarydate", "session_primarydate", "session_recipe", "session_fg", "session_og", "session_size", "session_brewer", "session_name", "session_id", 'session_comments' => 'LEFT( session_comments, LOCATE( " ", session_comments, 30 ) )'))
						->join("users", "beer_brew_sessions.session_brewer = users.user_id")
						->joinLeft("beer_recipes", "beer_brew_sessions.session_recipe=beer_recipes.recipe_id", array("recipe_id", "recipe_name", "recipe_publish", "recipe_abv"))
						->joinLeft("beer_styles", "beer_styles.style_id = beer_recipes.recipe_style", array("style_class"))
						->joinLeft("users AS recu", "beer_recipes.brewer_id=recu.user_id", array("user_id AS recu_id", "user_name AS recu_name"))
						->where("beer_brew_sessions.session_brewer =?", $brewer)
						->order("session_primarydate DESC");
				$this->view->user_info['sessions'] = array();
				$this->view->user_info['sessions_size'] = 0;
				if ($rows = $db->fetchAll($select)) {
					$this->view->user_info["sessions_size"] = sizeof($rows);
					$this->view->user_info["sessions"] = $rows;
				}
			}
		}
	}

	public function listAction() {
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("users", array("user_name", "user_id", "user_email"))
				->joinLeft("VIEW_public_recipes", "VIEW_public_recipes.brewer_id=users.user_id", array("count" => "count(VIEW_public_recipes.recipe_id)"))
				->where("users.user_active = ?", '1')
				->group("users.user_id")
//				->order("user_lastlogin DESC")
//				->order("count DESC")
//				->order("recipe_created DESC")
				->order("TRIM(user_name) ASC");
		$search = $this->_getParam("brewer_search");
		$this->view->search = "";
		if (isset($search) && !empty($search)) {
			$this->view->search = $search;
			$select->where("users.user_name LIKE '%" . $search . "%'");
		}
		$result = $db->FetchAll($select);
		if (sizeof($result) == 1) {
			header("location: /brewers/" . $result[0]['user_id']);
		}
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->view->content = new Zend_Paginator($adapter);
		$this->view->content->setCurrentPageNumber($this->_getParam('page'));
		$this->view->content->setItemCountPerPage(40);
	}

	public function profileAction() {
		$storage = new Zend_Auth_Storage_Session();
		$this->view->user_info = $storage->read();
		$u = $this->view->user_info;
		$this->view->changePasswordForm = new Form_Profile();

		$this->view->userAttributesForm = new Form_Attributes();
		$this->view->errors = array();

		if ($this->getRequest()->isPost()) {
			$action = "";

			if (isset($_POST['action'])) {

				$action = $_POST['action'];
				if ($action == "attributes") {
					$form = $this->view->userAttributesForm;
					$form_valid = $form->isValid($_POST);
				} else if ($action == "groups") {
					$form_valid = true;
				}
			} else {
				$action = "psw";
				$form = $this->view->changePasswordForm;
				$form_valid = $form->isValid($_POST);
			}
			if ($form_valid) {

				switch ($action) {
					case "psw":


						if ($this->getRequest()->getPost('user_password') == $this->getRequest()->getPost('user_password_repeat')) {
							if ($this->updatePassword($u->user_id, $this->getRequest()->getPost('user_password_old'), $this->getRequest()->getPost('user_password'))) {
								$this->view->success = "Slaptažodis pakeistas";
							} else {
								$this->view->errors[] = array("type" => "system", "message" => "Neteisingas slaptažodis");
							}
						} else {
							$this->view->errors[] = array("type" => "system", "message" => "Slaptažodžiai nesutampa");
						}
						break;
					case "groups":
						$user = new Entities_User($u->user_id);
						if (isset($_POST['group'])) {
							$user->updateGroups($_POST['group']);
						} else {
							$user->updateGroups($u->user_id, array());
						}
						break;
					case "attributes":
						$location = $_POST['user_location'];
						if ($_POST['use_other_location'] == '1') {
							$location = $_POST['user_other_location'];
						}

						$user = new Entities_User($u->user_id);
						if ($user->updateAttributes(array('user_location' => $location, 'user_about' => $_POST['user_about'], 'user_mail_comments' => $_POST['user_mail_comments'], 'beta_tester' => $_POST['beta_tester'], 'plato' => $_POST['plato']))) {
							$this->_redirect("/brewer/profile");
						} else {
							$this->view->errors[] = array("type" => "system", "message" => "Išsaugoti informacijos nepavyko");
						}
						break;
				}
			} else {
				$err_codes = new Entities_FormErrors();
				foreach ($form->getErrors() as $key => $error) {
					if (count($error) > 0) {
						$this->view->errors[] = array("type" => "form", "message" => $form->getElement($key)->getLabel() . " - " . $err_codes->getError($error[0]));
					}
				}
			}
		}
		$user = new Entities_User($u->user_id);

		$this->view->user_attributes = $user->getAttributes();
		$this->view->user_groups = $user->getGroups();
	}

	private function updatePassword($user_id, $old_password, $new_password) {

		$db = Zend_Registry::get('db');
		$where = array();
		$where[] = $db->quoteInto('user_id = ?', $user_id);
		$where[] = $db->quoteInto('user_password = ?', md5($old_password));
		return $db->update("users", array('user_password' => md5($new_password)), $where);
	}

	public function recipesAction() {
		$par = array();
		$par['brewer'] = $this->_getParam("brewer");
		$par['page'] = $this->_getParam("page");
		$par['sort'] = $this->_getParam("sort");
		$this->view->param = $par;
		$this->view->show_list = $this->show_list;
		$storage = new Zend_Auth_Storage_Session();
		$u = $storage->read();
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("beer_awards")
				->order("posted DESC");
		$result = $db->FetchAll($select);
		$aw = array();
		foreach ($result as $key => $val) {
			$aw[$val['recipe_id']][] = $val;
		}
		$this->view->awards = $aw;
		if ($this->_getParam('brewer') > 0) {
			$brewer = $this->_getParam('brewer');
			//$this->_helper->viewRenderer->render("../recipes/index");
			$this->_helper->viewRenderer("public");
			$select = $db->select()
					->from("beer_recipes", array("count" => "count(*)"))
					->where("beer_recipes.recipe_publish = ?", '1')
					->where("beer_recipes.brewer_id= ?", $brewer);
			$total = $db->fetchRow($select);
		} else {
			if (isset($u->user_id)) {
				$brewer = $u->user_id;
				$select = $db->select()
						->from("beer_recipes", array("count" => "count(*)"))
						->where("beer_recipes.brewer_id= ?", $brewer);
				$total = $db->fetchRow($select);
			}
		}
		$select = $db->select()
				->from("users")
				->where("user_id = ?", $brewer);
		$brewer = $db->fetchRow($select);
		if (isset($brewer)) {
			$select = $db->select()
					->from("beer_recipes")
					->join("users", "users.user_id = beer_recipes.brewer_id", array("user_name"))
					->joinLeft("beer_styles", "beer_recipes.recipe_style = beer_styles.style_id", array("style_name"));
			if ($this->_getParam('brewer') > 0) {
				$select->where("beer_recipes.recipe_publish = ?", '1');
			}
			$select->where("beer_recipes.brewer_id= ?", $brewer["user_id"]);
			$select->where("beer_recipes.recipe_archived = ?", 0);
			switch($this->_getParam("sort")){
				case "1":
					$select->order("beer_recipes.recipe_name ASC");
				break;
				case "2":
					$select->order(array("beer_styles.style_name ASC", "beer_recipes.recipe_created DESC"));
				break;
				case "0":
				default:
					$select->order("beer_recipes.recipe_created DESC");
				break;
			}

			if ($this->show_list === true && $this->_getParam('brewer') == 0) {
				$this->view->content = $db->fetchAll($select);
			} else {
				$adapter = new Zend_Paginator_Adapter_DbSelect($select);
				$this->view->content = new Zend_Paginator($adapter);
				$this->view->content->setCurrentPageNumber($this->_getParam('page'));
				$this->view->content->setItemCountPerPage(15);
			}
			$this->view->brewer = $brewer;
			$this->view->brewer["total"] = $total["count"];
		}
	}
	public function archiveAction() {
		$par = array();
		$par['brewer'] = $this->_getParam("brewer");
		$par['page'] = $this->_getParam("page");
		$par['sort'] = $this->_getParam("sort");
		$this->view->param = $par;
		$this->view->show_list = $this->show_list;
		$storage = new Zend_Auth_Storage_Session();
		$u = $storage->read();
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("beer_awards")
				->order("posted DESC");
		$result = $db->FetchAll($select);
		$aw = array();
		foreach ($result as $key => $val) {
			$aw[$val['recipe_id']][] = $val;
		}
		$this->view->awards = $aw;
		$brewer = $u->user_id;
		$select = $db->select()
				->from("beer_recipes", array("count" => "count(*)"))
				->where("beer_recipes.brewer_id= ?", $brewer);
		$total = $db->fetchRow($select);
		$select = $db->select()
				->from("users")
				->where("user_id = ?", $brewer);
		$brewer = $db->fetchRow($select);
		if (isset($brewer)) {
			$select = $db->select()
					->from("beer_recipes")
					->join("users", "users.user_id = beer_recipes.brewer_id", array("user_name"))
					->joinLeft("beer_styles", "beer_recipes.recipe_style = beer_styles.style_id", array("style_name"));
			$select->where("beer_recipes.brewer_id= ?", $brewer["user_id"]);
			$select->where("beer_recipes.recipe_archived = ?", 1);
			switch($this->_getParam("sort")){
				case "1":
					$select->order("beer_recipes.recipe_name ASC");
				break;
				case "2":
					$select->order(array("beer_styles.style_name ASC", "beer_recipes.recipe_created DESC"));
				break;
				case "0":
				default:
					$select->order("beer_recipes.recipe_created DESC");
				break;
			}

			if ($this->show_list === true && $this->_getParam('brewer') == 0) {
				$this->view->content = $db->fetchAll($select);
			} else {
				$adapter = new Zend_Paginator_Adapter_DbSelect($select);
				$this->view->content = new Zend_Paginator($adapter);
				$this->view->content->setCurrentPageNumber($this->_getParam('page'));
				$this->view->content->setItemCountPerPage(15);
			}
			$this->view->brewer = $brewer;
			$this->view->brewer["total"] = $total["count"];
		}
	}

	public function enableblocksAction() {
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		if (isset($user_info->user_id) && !empty($user_info->user_id)) {
			$db = Zend_Registry::get("db");
			$db->update("users_attributes", array("recipe_list" => "0"), array("user_id = '" . $user_info->user_id . "'"));
		}
		if ($this->_getParam("archive") == 1){
			$this->_redirect("/brewer/archive");
		} else {
			$this->_redirect("/brewer/recipes");
		}
	}

	public function enablelistAction() {
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		if (isset($user_info->user_id) && !empty($user_info->user_id)) {
			$db = Zend_Registry::get("db");
			$db->update("users_attributes", array("recipe_list" => "1"), array("user_id = '" . $user_info->user_id . "'"));
		}
		if ($this->_getParam("archive") == 1){
			$this->_redirect("/brewer/archive");
		} else {
			$this->_redirect("/brewer/recipes");
		}
	}

}