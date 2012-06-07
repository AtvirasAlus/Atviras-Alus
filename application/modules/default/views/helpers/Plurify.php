<?
class Zend_View_Helper_Plurify extends Zend_View_Helper_Abstract{
	public function  plurify($number, $str_as, $str_ai, $str_u) {
		if ($number == 1) return $str_as;
		if ($number > 1 && $number < 10) return $str_ai;
		if ($number > 9 && $number < 20) return $str_u;
		if ($number % 100 == 11) return $str_u;
		if ($number % 10 == 1) return $str_as;
		if ($number % 10 != 0) return $str_ai;
		return $str_u;
		
	}
		
}
?>
