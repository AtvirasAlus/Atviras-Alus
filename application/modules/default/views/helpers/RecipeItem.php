<?

class Zend_View_Helper_RecipeItem extends Zend_View_Helper_Abstract {

	public $view;

	function recipeItem($item, $type = "large", $options = array()) {
		$db = Zend_Registry::get('db');
		$select = $db->select()
				->from("beer_recipes", array("recipe_abv"))
				->joinLeft("beer_styles", "beer_styles.style_id = beer_recipes.recipe_style", array("style_class"))
				->where("beer_recipes.recipe_id = ?", $item['recipe_id'])
				->limit(1);
		$rcp = $db->fetchRow($select);
		if (($rcp['style_class'] == "beer" && $rcp['recipe_abv'] > 9.5) || ($rcp['style_class'] != "beer" && $rcp['recipe_abv'] > 18)){
			$this->view->legal = false;
		} else {
			$this->view->legal = true;
		}
		$this->view->hex = $this->view->colorHex($item['recipe_ebc']);
		$this->view->item = $item;
		$this->view->options = $options;
		$this->view->addScriptPath(APPLICATION_PATH . "/modules/default/views/helpers/");
		switch ($type) {
			case "large":
				return $this->view->render("recipeitem.phtml");
				break;
			case "thumb":
				return $this->view->render("recipethumb.phtml");
				break;
		}
	}

	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}

}

?>
