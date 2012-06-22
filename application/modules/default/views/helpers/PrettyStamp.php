<?
class Zend_View_Helper_PrettyStamp extends Zend_View_Helper_Abstract{
		function prettyStamp($date) {
			$to_time = time();
			$from_time = strtotime($date);
			$sec_diff = round(abs($to_time - $from_time));
			$day_diff = floor($sec_diff / 86400);
			if ($sec_diff < 60){
				return "Kątik";
			} else if ($sec_diff < 120){
				return "Prieš minutę";
			} else if ($sec_diff < 3600){
				return "Prieš ".floor($sec_diff / 60)." min.";
			} else if ($sec_diff < 7200){
				return "Prieš valandą";
			} else if ($sec_diff < 86400){
				return "Prieš ".floor($sec_diff / 3600)." val.";
			} else if ($day_diff == 1){
				return "Vakar";
			} else if ($day_diff < 7){
				return "Prieš ".$day_diff." d.";
			} else if ($day_diff < 31){
				return "Prieš ".ceil($day_diff / 7 )." sav.";
			} else {
				return date("Y", $from_time)." m. ".$this->numToMonth(date("n", $from_time))." ".date("j", $from_time)." d.";
			}			
			
			return $date;
		}
		function numToMonth($month){
			$months = array(
				1 => "Sausio",
				2 => "Vasario",
				3 => "Kovo",
				4 => "Balandžio",
				5 => "Gegužės",
				6 => "Birželio",
				7 => "Liepos",
				8 => "Rugpjūčio",
				9 => "Rugsėjo",
				10 => "Spalio",
				11 => "Lapkričio",
				12 => "Gruodžio"
			);
			return $months[$month];
		}
}
?>
