<?
class Zend_View_Helper_Fortune extends Zend_View_Helper_Abstract{
		function fortune($predefined="") {
				$db = Zend_Registry::get("db");
				$select=$db->select() 
    	  		->from("beer_patarles",array("patarle_id","patarle_text"))
		 	->order("Rand()")
			 ->limit(2);
			 $f=$db->fetchAll($select);
			 return $f[0]['patarle_text'];
		}
}
?>
