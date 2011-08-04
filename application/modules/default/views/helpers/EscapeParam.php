<?
class Zend_View_Helper_EscapeParam extends Zend_View_Helper_Abstract{
		function escapeParam($predefined="") {
				return str_replace(array('/'),"",$predefined);	
		}
}
?>
