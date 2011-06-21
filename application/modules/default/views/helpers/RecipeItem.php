<?
class Zend_View_Helper_RecipeItem extends Zend_View_Helper_Abstract{
   public $view; 

function  recipeItem($item) {
		
		$this->view->hex=$this->view->colorHex($item['recipe_ebc']);
		$this->view ->item = $item;
		$this->view->addScriptPath(APPLICATION_PATH."/modules/default/views/helpers/");
		return $this->view ->render("recipeitem.phtml");
	}
public function setView(Zend_View_Interface $view) 
    { 
        $this->view = $view; 
    } 
}

?>
