<?

class Zend_View_Helper_RandomStatistics extends Zend_View_Helper_Abstract {

	var $statistics = array("user_locations", "styles_brewed", "top_hops");

	function randomStatistics($predefined = "") {


		$this->view->addScriptPath(APPLICATION_PATH . "/modules/default/views/helpers/");
		$_type = $this->statistics[rand(0, count($this->statistics) - 1)];
		$this->view->statistics = $this->getStatisticsData($_type);
		$this->view->title = $this->getStatisticsTitle($_type);
		$out = $this->view->render($this->getStatisticsView($_type));
		return $out;
	}

	private function getStatisticsData($_st) {
		$db = Zend_Registry::get("db");
		$select = $db->select();
		switch ($_st) {
			case "top_hops":
				$select->from("beer_recipes_hops", array("data" => "count(recipe_id)", "label" => "hop_name"))
						->group("hop_name")
						->order("data desc")
						->limit(5);
				return $db->fetchAll($select);
				break;
			case "user_locations":
				$select->from("users_attributes", array("data" => "count(user_id)", "label" => "user_location"))
						->group("user_location")
						->order("data desc")
						->where("user_location!=''")
						->limit(5);
				return $db->fetchAll($select);
				break;
			case "styles_brewed":
				$select->from("beer_recipes", array("data" => "count(recipe_id)"))
						->join("beer_styles", "beer_styles.style_id=beer_recipes.recipe_style", array("label" => "style_name"))
						->where("recipe_style!=?", 41)
						->where("recipe_publish= ?", '1')
						->group("recipe_style")
						->order("data desc")
						->limit(5);
				return $db->fetchAll($select);

				break;
		}
	}

	private function getStatisticsView($_st) {
		switch ($_st) {
			case "user_locations":
			case "styles_brewed":
			case "top_hops":
				return "randomstatistics.phtml";
				break;
		}
	}

	private function getStatisticsTitle($_st) {
		switch ($_st) {
			case "user_locations":
				return "Aludarių geografija Top(5)";
				break;
			case "top_hops":
				return "Populiariausi apyniai Top(5)";
				break;
			case "styles_brewed":
				return "Dažniausiai verdami alaus stiliai Top(5)";
				break;
		}
	}

}

?>
