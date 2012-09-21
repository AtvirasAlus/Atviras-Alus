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
	}
}