<?
class Zend_View_Helper_BackLink extends Zend_View_Helper_Abstract{
		function backLink($predefined="") {
			if (isset($_SERVER['HTTP_REFERER'])){
				return $_SERVER['HTTP_REFERER'];
			}else{
				return $predefined;
			}
		}
}
?>
