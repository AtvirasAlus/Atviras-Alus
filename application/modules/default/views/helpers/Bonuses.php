<?php

class Zend_View_Helper_Bonuses extends Zend_View_Helper_Abstract {

	function Bonuses() {
		return $this;
	}
	
	function getmoney(){
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("bonuses", array("SUM(bo_amount) as money"));
		$result = $db->fetchRow($select);
		return round($result['money']);
	}

	function getnext(){
		$db = Zend_Registry::get("db");
		$select = $db->select()
				->from("bonuses")
				->where("bo_description LIKE '%planas 4GHz:4096MB:20GB:100Mbps%'")
				->order("bo_created DESC")
				->limit(1);		
		$result = $db->fetchRow($select);
		$till = $result['bo_description'];
		$pos = strpos($till, "iki") + 4;
		$till = substr($till, $pos);
		$till = explode(", ", $till);
		$year = $till[1];
		$till = explode(" ", $till[0]);
		$day = $till[1];
		if ($day < 10) $day = "0".$day;
		switch($till[0]){
			case "Sausio":
				$month = "01";
			break;
			case "Vasario":
				$month = "02";
			break;
			case "Kovo":
				$month = "03";
			break;
			case "Balandžio":
				$month = "04";
			break;
			case "Gegužės":
				$month = "05";
			break;
			case "Birželio":
				$month = "06";
			break;
			case "Liepos":
				$month = "07";
			break;
			case "Rugpjūčio":
				$month = "08";
			break;
			case "Rugsėjo":
				$month = "09";
			break;
			case "Spalio":
				$month = "10";
			break;
			case "Lapkričio":
				$month = "11";
			break;
			case "Gruodžio":
				$month = "12";
			break;
		}
		$date = $year."-".$month."-".$day;
		$next = date("Y-m-d", strtotime("-6 days", strtotime($date)));
		return $next;
	}

}