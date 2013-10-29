<?php

class CalculatorController extends Zend_Controller_Action {

	var $user_info;
	var $use_plato;
	var $db;
	var $storage;
	
	function init(){
		$this->db = Zend_Registry::get("db");
		$this->storage = new Zend_Auth_Storage_Session();
		$this->user_info = $this->storage->read();
		$this->use_plato = false;
		if (isset($this->user_info->user_id) && !empty($this->user_info->user_id)){
			$select = $this->db->select()
					->from("users_attributes")
					->where("users_attributes.user_id = ?", $this->user_info->user_id)
					->limit(1);
			$u_atribs= $this->db->fetchRow($select);
			if ($u_atribs['plato'] == 1) {
				$this->use_plato = true;
			}
		}
		$this->view->use_plato = $this->use_plato;
	}

	function indexAction() {
		$recipe_id = $this->_getParam("recipe_id");
		$data = array(
			"recipe" => array(
				"c_name" => "",
				"c_stype" => "",
				"c_status" => 0,
				"c_notes" => ""
			),
			"details" => array(
				"c_efficiency" => 70,
				"c_size" => 24,
				"c_time" => 60,
				"c_evaporate" => 20,
				"c_topup" => 0
			),
			"fermentables" => array(),
			"hops" => array(),
			"yeasts" => array(),
			"others" => array(),
			"steps" => array()
		);
		if ((int)$recipe_id != 0){
			$select = $this->db->select()
					->from("beer_recipes")
					->where("recipe_id = ?", $recipe_id);
			$result = $this->db->fetchRow($select);
			if ($result != false){
				$data['recipe']['c_name'] = $result['recipe_name'];
				$data['recipe']['c_style'] = $result['recipe_style'];
				$data['recipe']['c_status'] = $result['recipe_publish'];
				$data['recipe']['c_notes'] = $result['recipe_comments'];
			
				$data['details']['c_efficiency'] = $result['recipe_efficiency'] * 100;
				$data['details']['c_size'] = $result['recipe_batch'];
				$data['details']['c_time'] = $result['recipe_boiltime'];
				$data['details']['c_evaporate'] = $result['recipe_evaporate'];
				$data['details']['c_topup'] = $result['recipe_topup'];
			
				$select = $this->db->select()
						->from("beer_recipes_malt")
						->where("recipe_id = ?", $recipe_id)
						->order("malt_weight DESC")
						->order("malt_name ASC");
				$data['fermentables'] = $this->db->fetchAll($select);

				$select = $this->db->select()
						->from("beer_recipes_hops")
						->where("recipe_id = ?", $recipe_id)
						->order("hop_time DESC")
						->order("hop_name ASC");
				$data['hops'] = $this->db->fetchAll($select);

				$select = $this->db->select()
						->from("beer_recipes_yeast")
						->where("recipe_id = ?", $recipe_id)
						->order("yeast_attenuation DESC")
						->order("yeast_name ASC");
				$data['yeasts'] = $this->db->fetchAll($select);

				$select = $this->db->select()
						->from("beer_recipes_others")
						->where("recipe_id = ?", $recipe_id)
						->order("other_time DESC")
						->order("other_name ASC");
				$data['others'] = $this->db->fetchAll($select);

				$select = $this->db->select()
						->from("beer_recipes_steps")
						->where("recipe_id = ?", $recipe_id)
						->order("step_order ASC");
				$data['steps'] = $this->db->fetchAll($select);
			}
		}
		$this->view->data = $data;
		$select = $this->db->select()
				->from("beer_cats", array("cat_id", "cat_name"))
				->order("cat_name ASC");
		$result = $this->db->fetchAll($select);
		foreach($result as $key=>$val){
			$select2 = $this->db->select()
					->from("beer_styles", array("style_id", "style_name"))
					->where("style_cat = ?", $val['cat_id'])
					->order("style_name ASC");
			$result2 = $this->db->fetchAll($select2);
			$result[$key]['childs'] = $result2;
		}
		$this->view->styles = $result;
	}

}