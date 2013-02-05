<?php
class RateController extends Zend_Controller_Action {
	public function init() {
		$this->storage = new Zend_Auth_Storage_Session();
		$this->user_info = $this->storage->read();
		$this->db = Zend_Registry::get("db");
	}

	public function indexAction() {
		$db = $this->db;
		$select = $db->select()
				->from("rate_breweries")
				->join("rate_countries", "rate_breweries.country_code = rate_countries.country_code", array("country_title"))
				->order("brewery_title ASC");
		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$page = $this->_getParam('page');
		$this->view->breweries = new Zend_Paginator($adapter);
		$this->view->breweries->setCurrentPageNumber($page);
		$this->view->breweries->setItemCountPerPage(12);

	}

	public function breweryAction() {
		$db = $this->db;
		$bid = $this->_getParam('bid');
		if (!isset($bid) || $bid == 0) $this->_redirect('/vertinimas');
		$select = $db->select()
				->from("rate_breweries")
				->join("rate_countries", "rate_breweries.country_code = rate_countries.country_code", array("country_title"))
				->where("brewery_id = ?", $bid);
		$brewery = $db->fetchRow($select);
		if (!$brewery) $this->_redirect('/vertinimas');
		$this->view->brewery = $brewery;
		
		$select = $db->select()
				->from("rate_beers")
				->join("beer_styles", "rate_beers.style_id = beer_styles.style_id", array("style_name"))
				->order("beer_title ASC")
				->where("brewery_id = ?", $bid);

		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$page = $this->_getParam('page');
		$this->view->beers = new Zend_Paginator($adapter);
		$this->view->beers->setCurrentPageNumber($page);
		$this->view->beers->setItemCountPerPage(12);

	}
	
	public function styleAction() {
		$db = $this->db;
		$sid = $this->_getParam('sid');
		if (!isset($sid) || $sid == 0) $this->_redirect('/vertinimas');
		$select = $db->select()
				->from("beer_styles")
				->where("style_id = ?", $sid);
		$style = $db->fetchRow($select);
		if (!$style) $this->_redirect('/vertinimas');
		$this->view->style = $style;
		$select = $db->select()
				->from("rate_beers")
				->join("beer_styles", "rate_beers.style_id = beer_styles.style_id", array("style_name"))
				->order("beer_display ASC")
				->where("rate_beers.style_id = ?", $sid);

		$adapter = new Zend_Paginator_Adapter_DbSelect($select);
		$page = $this->_getParam('page');
		$this->view->beers = new Zend_Paginator($adapter);
		$this->view->beers->setCurrentPageNumber($page);
		$this->view->beers->setItemCountPerPage(12);

	}

	public function beerAction() {
		$db = $this->db;
		$bid = $this->_getParam('bid');
		if (!isset($bid) || $bid == 0) $this->_redirect('/vertinimas');
		$select = $db->select()
				->from("rate_beers")
				->join("beer_styles", "rate_beers.style_id = beer_styles.style_id", array("style_name"))
				->join("rate_breweries", "rate_beers.brewery_id = rate_breweries.brewery_id", array("brewery_title"))
				->order("beer_title ASC")
				->where("beer_id = ?", $bid);
		$beer = $db->fetchRow($select);
		if (!$beer) $this->_redirect('/vertinimas');
		$this->view->beer = $beer;
		
		$select = $db->select()
				->from("rate_systems")
				->where("system_purpose = ?", array("brewery"));
		$system = $db->FetchRow($select);
		$this->view->system = $system;
		$this->view->user_info = $this->user_info;
		$select = $db->select()
				->from("rate_votes")
				->join("users", "users.user_id = rate_votes.user_id", array("user_name", "user_email"))
				->join("rate_systems", "rate_systems.system_id = rate_votes.system_id")
				->where("rate_votes.beer_id = ?", $bid)
				->order("rate_votes.posted DESC");
		$result = $db->fetchAll($select);
		$this->view->votes = $result;
		$this->view->total_score = $this->view->Rate($bid);
	}
	
	public function rateAction(){
		if (!isset($this->user_info->user_id) || $this->user_info->user_id == 0){
			$this->_redirect("/");
			exit;
		}
		$db = Zend_Registry::get('db');
		$this->_helper->layout->setLayout('empty');
		$this->_helper->viewRenderer->setNoRender(true);
		$system_type = $this->_getParam('system_type');
		$select = $db->select()
				->from("rate_systems")
				->where("system_purpose = ?", array("brewery"));
		$system = $db->FetchRow($select);
		$system_id = $system['system_id'];
		$select = $db->select()
			->from("rate_beers")
			->where("beer_id = ?", $this->_getParam("beer_id"));
		$beer = $db->fetchRow($select);
		$db->delete("rate_votes", "user_id = '".$this->user_info->user_id."' AND beer_id = '".$beer['beer_id']."'");
		switch($system_type){
			case "simple":
				$db->insert("rate_votes", array(
					"rate_type" => "simple",
					"beer_id" => $beer["beer_id"], 
					"user_id" => $this->user_info->user_id, 
					"posted" => date("Y-m-d H:i:s"),
					"system_id" => $system_id,
					"simple_vote" => $this->_getParam('rate_simple_val'),
					"simple_comment" => strip_tags($this->_getParam('rate_simple_comment'), '<a>')
				));
			break;
			case "advanced":
				$db->insert("rate_votes", array(
					"rate_type" => "advanced",
					"beer_id" => $beer["beer_id"], 
					"user_id" => $this->user_info->user_id, 
					"posted" => date("Y-m-d H:i:s"),
					"system_id" => $system_id,
					"aroma_vote" => $this->_getParam('rate_aroma'),
					"aroma_comment" => strip_tags($this->_getParam('rate_aroma_comment'), '<a>'),
					"appearance_vote" => $this->_getParam('rate_appearance'),
					"appearance_comment" => strip_tags($this->_getParam('rate_appearance_comment'), '<a>'),
					"taste_vote" => $this->_getParam('rate_taste'),
					"taste_comment" => strip_tags($this->_getParam('rate_taste_comment'), '<a>'),
					"body_vote" => $this->_getParam('rate_body'),
					"body_comment" => strip_tags($this->_getParam('rate_body_comment'), '<a>'),
					"style_vote" => $this->_getParam('rate_style'),
					"style_comment" => strip_tags($this->_getParam('rate_style_comment'), '<a>'),
					"overall_vote" => $this->_getParam('rate_overall'),
					"overall_comment" => strip_tags($this->_getParam('rate_overall_comment'), '<a>'),
				));
			break;
		}
		$this->_redirect("/vertinimas/alus/".$beer['beer_id']."-".$this->view->urlMaker($beer['beer_display']));
	}
}