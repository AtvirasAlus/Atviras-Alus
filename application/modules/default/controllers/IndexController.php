<?php

class IndexController extends Zend_Controller_Action {

	function init() {
		//$this->_helper->layout->setLayout('main');
	}

	public function indexAction() {
		// $this->view->addHelperPath(APPLICATION_PATH .'/default/helpers', 'View_Helper');
		$db = Zend_Registry::get("db");
		$frontendOptions = array(
			'lifetime' => 3600, // cache lifetime of 2 hours
			'automatic_serialization' => true
		);

		$backendOptions = array(
			'cache_dir' => './cache/' // Directory where to put the cache files
		);


		// getting a Zend_Cache_Core object
		$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
                
                //
                
                $frontendOptions2 = array(
			'lifetime' => (3600*24), // cache lifetime of 24 hours
			'automatic_serialization' => true
		);

		$backendOptions2 = array(
			'cache_dir' => './cache/' // Directory where to put the cache files
		);


		// getting a Zend_Cache_Core object
		$cache2 = Zend_Cache::factory('Core', 'File', $frontendOptions2, $backendOptions2);
		
		$storage = new Zend_Auth_Storage_Session();
		$user_info = $storage->read();
		if (isset($user_info->user_id) && !empty($user_info->user_id)){
			$select = $db->select()
					->from("idea_items", array("idea_id"))
					->where("idea_items.idea_status = 0 AND idea_items.user_id != ?", $user_info->user_id);
			$ideas = $db->fetchAll($select);
			$ids = array();
			foreach($ideas as $key=>$val){
				$ids[] = $val['idea_id'];
			}
			$select = $db->select()
					->from("idea_votes", array("COUNT(idea_id) AS kiekis"))
					->where("idea_votes.idea_id IN (".implode(",", $ids).") AND idea_votes.user_id = '".$user_info->user_id."'");
			$voted = $db->fetchRow($select);
			$this->view->unvoted = sizeof($ids)-$voted['kiekis'];
		}
		$select = $db->select()
				->from("users", array("user_name", "user_id", "user_email"))
				->joinLeft("VIEW_public_recipes", "VIEW_public_recipes.brewer_id=users.user_id", array("count" => "count(VIEW_public_recipes.recipe_id)"))
				->where("users.user_active = ?", '1')
				->group("users.user_id")
				->order("user_lastlogin DESC")
				->order("count DESC")
				->order("recipe_created DESC")
				->order("user_name ASC")
				->limit(12);
		$this->view->users = $db->fetchAll($select);

		$select = $db->select()
				->from("users", array("count" => "count(*)"))
				->where("users.user_active = ?", '1');
		$this->view->users_total = $db->fetchRow($select);
		$select = $db->select()
				->from("VIEW_public_recipes", array("recipe_id", "recipe_name", "recipe_published" => "MAX(recipe_published)"))
				->joinLeft("beer_styles", "VIEW_public_recipes.recipe_style=beer_styles.style_id", array("style_name"))
				->group("VIEW_public_recipes.recipe_id")
				->order("recipe_published DESC")
				->limit(12);
		$this->view->recipes = $db->fetchAll($select);
		$select = $db->select()
				->from("users", array("user_name", "user_id", "user_created" => "MAX(user_created)"))
				->where("user_active =?", '1')
				->group("users.user_id")
				->order("user_created DESC")
				->limit(1);
		$this->view->welcome = $db->fetchRow($select);
		$select = $db->select()
				->from("VIEW_public_recipes", array("count" => "count(*)"));
		$this->view->total_recipes = $db->fetchRow($select);
		$select = $db->select()
				->from("beer_recipes_comments", array("comment_id", "comment_brewer", "comment_recipe", "comment_date" => "MAX(comment_date)"))
				->join("VIEW_public_recipes", "VIEW_public_recipes.recipe_id=beer_recipes_comments.comment_recipe", array("recipe_name", "recipe_id"))
				->join("users", "beer_recipes_comments.comment_brewer=users.user_id", array("user_name", "user_email", "user_id"))
				->group("comment_id")
				->order("comment_date DESC")
				->limit(10);
		$this->view->comments = $db->fetchAll($select);
		$select = $db->select()
				->from("VIEW_public_recipes", array("recipe_id", "recipe_name"))
				->join("beer_brew_sessions", "VIEW_public_recipes.recipe_id=beer_brew_sessions.session_recipe")
				->join("users", "beer_brew_sessions.session_brewer=users.user_id", array("user_id", "user_name"))
				->where("session_primarydate <= CURDATE( )")
				->where("session_primarydate  >= DATE_SUB(CURDATE(),INTERVAL 2 MONTH)")
				->where("recipe_publish =?", '1')
				->where("session_caskingdate  = '0000-00-00' OR session_caskingdate > CURDATE( )")
				->order("session_primarydate DESC")
				->limit(10);
		$this->view->brew_session = $db->fetchAll($select);


		//
		$select = $db->select()
				->from('VIEW_brew_total', array("beer_total" => "SUM(sum)", "brewers_total" => "COUNT(sum)"));
		$this->view->total_brewed = $db->fetchRow($select);
		//
		$select = $db->select()
				->from('cache_fav_recipes')
				->join("users", "users.user_id=cache_fav_recipes.brewer_id", array("user_name"))
				->limit(5)
				->order("total DESC");
		$this->view->fav_recipes = $db->fetchAll($select);
		$select = $db->select()
				->from("beer_articles")
				->joinLeft("VIEW_article_comments_total", "VIEW_article_comments_total.article_id=beer_articles.article_id", array("total"))
				->where("article_cat =?", 1)
				->where("article_publish =?", '1')
				->order("article_modified DESC")
				->limit(5);
		$this->view->articles = $db->fetchAll($select);
		$select = $db->select()
				->from("VIEW_bblast_posts")
				->limit(7);
		;
		$this->view->posts = $db->fetchAll($select);
		if ($this->view->blogs = $cache->load('blog_latest')) {
			
		} else {
			$select = $db->select()
					->from("VIEW_blog_latest")
					->join("users", "users.user_id=VIEW_blog_latest.post_author", array("user_name"))
					->order("post_date DESC")
					->limit(7);
			$this->view->blogs = $db->fetchAll($select);
			$cache->save($this->view->blogs, 'blog_latest');
		}
		$select = $db->select()
				->from("beer_events")
				->where("event_registration_end >= CURDATE( )")
				->where("event_registration_end  != '0000-00-00'")
				->order("event_start");
		$this->view->events = $db->fetchAll($select);
	}

	public function sitemapAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$sitemap = $this->view->navigation(new Zend_Navigation(new Zend_Config_Xml(APPLICATION_PATH . "/configs/defaultNavigation.xml", "nav")));
		$sitemap->sitemap()

				// ->setFormatOutput(true); 
				->setUseXmlDeclaration(true) // default is true
				->setServerUrl('http://atvirasalus.lt');

// default is to detect automatically
// print sitemap

		echo $sitemap->sitemap();
	}

	public function rssAction() {
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$db = Zend_Registry::get("db");
		$feed = new Zend_Feed_Writer_Feed();
		$feed->setTitle('Atviro Alaus dienoraštis');
		$feed->setLink('http://www.atvirasalus.lt');
		$feed->setFeedLink('http://www.atvirasalus.lt/index/rss', 'rss');
		$feed->addAuthor(array(
			'name' => 'atvirasalus.lt',
			'email' => 'info@atvirasalus.lt',
			'uri' => 'http://www.atvirasalus.lt',
		));
		$feed->setId('http://www.atvirasalus.lt/index/rss');
		$feed->setDateModified(time());
		if (!isset($_GET['blogs_only'])) {
			$select = $db->select()
					->from("beer_articles")
					->where("article_publish = 1")
					->order("article_created DESC")
					->limit(10);
			$articles = $db->fetchAll($select);
			for ($i = 0; $i < count($articles); $i++) {
				$entry = $feed->createEntry();
				$entry->setTitle($articles[$i]['article_title']);
				$entry->setLink('http://www.atvirasalus.lt/content/read/1/' . $articles[$i]['article_id'] . urlencode($articles[$i]['article_title']));
				$entry->setId('http://www.atvirasalus.lt/content/read/1/' . $articles[$i]['article_id'] . urlencode($articles[$i]['article_title']));
				$entry->addAuthor(array('name' => 'Atviras Alus', 'email' => 'info@atvirasalus.lt', 'uri' => 'http://www.atvirasalus.lt'));
				$entry->setDateCreated(new Zend_Date($articles[$i]['article_created'], Zend_Date::ISO_8601));
				$entry->setDescription($articles[$i]['article_resume']);
				$entry->setContent($articles[$i]['article_text']);
				$feed->addEntry($entry);
			}
		}
		if (!isset($_GET['articles_only'])) {
			$select = $db->select()
					->from("VIEW_blog_latest")
					->join("users", "users.user_id=VIEW_blog_latest.post_author", array("user_name"))
					->order("post_date DESC")
					->limit(10);
			$blogs = $db->fetchAll($select);
			for ($i = 0; $i < count($blogs); $i++) {
				$entry = $feed->createEntry();
				$entry->setTitle($blogs[$i]['post_title']);
				$entry->setLink($blogs[$i]['guid']);
				$entry->setId($blogs[$i]['guid']);
				$entry->addAuthor(array('name' => $blogs[$i]['user_name'], 'email' => 'info@atvirasalus.lt', 'uri' => 'http://www.atvirasalus.lt'));
				// $entry->setDateModified($blogs[$i]['post_modified']);
				// $entry->setDateCreated($blogs[$i]['post_modified']);
				// $entry->setDateModified(new Zend_Date($blogs[$i]['post_modified'], Zend_Date::ISO_8601));
				$entry->setDateCreated(new Zend_Date($blogs[$i]['post_date'], Zend_Date::ISO_8601));
				$entry->setDescription($blogs[$i]['post_title']);
				$entry->setContent($blogs[$i]['post_content']);
				$feed->addEntry($entry);
			}
		}
		header("content-type: text/xml");
		print trim($feed->export('rss', true));
	}

	public function printRecipeAction() {
		$this->_helper->layout->setLayout('empty');

		if (isset($_GET['recipe_id'])) {
			$recipe = new Entities_Recipe(intval($_GET['recipe_id']));
			$this->view->print_data = array();
			if ($recipe_data = $recipe->getProperties()) {
				$data_mask = array('recipe_boiltime' => 'boil_time', 'recipe_comments' => 'comments', 'recipe_name' => 'beer_name', 'recipe_batch' => 'bash_size', 'recipe_efficiency' => 'efficiency', 'recipe_attenuation' => 'attenuation', 'style_name' => 'beer_style');
				foreach ($recipe_data as $key => $data) {
					if (isset($data_mask[$key])) {
						$this->view->print_data[$data_mask[$key]] = $data;
					} else {
						$this->view->print_data[$key] = $data;
					}
				}

				$malt_list = $recipe->getMalts();
				if (count($malt_list) > 0) {
					$this->view->print_data['malt_list'] = array();
					$this->view->print_data['malt_color'] = array();
					$this->view->print_data['malt_weight'] = array();
					foreach ($malt_list as $malt) {
						$this->view->print_data['malt_list'][] = $malt['malt_name'];
						$this->view->print_data['malt_color'][] = $malt['malt_ebc'];
						$this->view->print_data['malt_weight'][] = $malt['malt_weight'];
					}
				}
				$hop_list = $recipe->getHops();
				if (count($hop_list) > 0) {
					$this->view->print_data['hop_list'] = array();
					$this->view->print_data['hop_alpha'] = array();
					$this->view->print_data['hop_weight'] = array();
					$this->view->print_data['hop_time'] = array();
					foreach ($hop_list as $hop) {
						$this->view->print_data['hop_list'][] = $hop['hop_name'];
						$this->view->print_data['hop_alpha'][] = $hop['hop_alpha'];
						$this->view->print_data['hop_weight'][] = $hop['hop_weight'];
						$this->view->print_data['hop_time'][] = $hop['hop_time'];
					}
				}
				$yeast_list = $recipe->getYeasts();
				if (count($yeast_list) > 0) {
					$this->view->print_data['yeast_list'] = array();
					$this->view->print_data['yeast_weight'] = array();

					foreach ($yeast_list as $yeast) {
						$this->view->print_data['yeast_list'][] = $yeast['yeast_name'];
						$this->view->print_data['yeast_weight'][] = $yeast['yeast_weight'];
					}
				}
			}
		} else {
			$this->view->print_data = $_POST;
		}
	}

	public function calculusAction() {
		$recipe_id = $this->getRequest()->getParam("recipe");
		$this->view->data = array();
		if ($recipe_id > 0) {
			$db = Zend_Registry::get("db");
			$select = $db->select();
			$select->from("beer_recipes")
					->where("recipe_id = ?", $recipe_id);
			$this->view->data["recipe"] = $db->fetchRow($select);
			$select = $db->select();
			$select->from("beer_recipes_malt")
					->where("recipe_id = ?", $recipe_id)
					->order("malt_weight DESC");

			$this->view->data["malt"] = $db->fetchAll($select);
			$select = $db->select();
			$select->from("beer_recipes_hops")
					->where("recipe_id = ?", $recipe_id)
					->order("hop_time DESC");
			$this->view->data["hops"] = $db->fetchAll($select);
			$select = $db->select();
			$select->from("beer_recipes_yeast")
					->where("recipe_id = ?", $recipe_id);
			$this->view->data["yeast"] = $db->fetchAll($select);
		}
	}

	public function recipesAction() {
		
	}

	public function stylesAction() {
		
	}

}