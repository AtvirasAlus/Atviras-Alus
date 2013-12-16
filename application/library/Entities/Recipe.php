<?

class Entities_Recipe {

	var $recipe_id;
	var $db;

	function Entities_Recipe($id = 0) {
		$this->recipe_id = $id;
		$this->db = Zend_Registry::get('db');
	}

	public function getProperties() {
		if ($this->recipe_id > 0) {
			$select = $this->db->select();
			$select->from("beer_recipes")
					->join("users", "users.user_id=beer_recipes.brewer_id", array("user_name"))
					->joinLeft("beer_styles", "beer_styles.style_id=beer_recipes.recipe_style", array("style_name", "style_class"))
					->where("recipe_id = ?", $this->recipe_id);
			$this->properties = $this->db->fetchRow($select);
			return $this->properties;
		} else {
			return false;
		}
	}

	public function getMalts() {
		if ($this->recipe_id > 0) {
			$select = $this->db->select();
			$select->from("beer_recipes_malt")
					->where("recipe_id = ?", $this->recipe_id)
					->order("malt_weight DESC");
			return $this->db->fetchAll($select);
		}
	}

	public function getHops() {
		if ($this->recipe_id > 0) {
			$select = $this->db->select();
			$select->from("beer_recipes")
					->where("recipe_id = ?", $this->recipe_id);
			$result = $this->db->fetchRow($select);
			$liters = $result['recipe_batch'];
			$gravity = $result['recipe_sg'];

			$select = $this->db->select();
			$select->from("beer_recipes_hops")
					->where("recipe_id = ?", $this->recipe_id)
					->order("hop_time DESC")
					->order("hop_name ASC");
			$result = $this->db->fetchAll($select);
			$gallons = $liters * 0.264172052;
			foreach ($result as $k=>$v){
				$ibu = 0;
				$util = (1.65 * pow(0.000125, $gravity - 1)) * ((1 - exp(-0.04 * $v['hop_time'])) / 4.15);
				$ibu = $util * ($v['hop_weight'] * 0.0352739619 * ($v['hop_alpha'] / 100) * 7490) / $gallons;
				$result[$k]['hop_ibu'] = number_format($ibu, 1);
			}
			return $result;
		}
	}

	public function getYeasts() {
		if ($this->recipe_id > 0) {
			$select = $this->db->select();
			$select->from("beer_recipes_yeast")
					->where("recipe_id = ?", $this->recipe_id);
			return $this->db->fetchAll($select);
		}
	}

}