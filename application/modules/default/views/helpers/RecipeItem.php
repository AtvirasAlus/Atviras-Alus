<?
class Zend_View_Helper_RecipeItem extends Zend_View_Helper_Abstract{
   public $view; 

function  recipeItem($item,$type="large", $options = array()) { 
        $this->view->hex=$this->view->colorHex($item['recipe_ebc']);
        $this->view ->item = $item;
        $this->view ->options = $options;
        $this->view->addScriptPath(APPLICATION_PATH."/modules/default/views/helpers/");
		switch ($type)  {
      case "large":
        return $this->view ->render("recipeitem.phtml");
      break;
      case "thumb":
        return $this->view ->render("recipethumb.phtml");
      break;
		}
	}
public function setView(Zend_View_Interface $view) 
    { 
        $this->view = $view; 
    } 
}

?>
