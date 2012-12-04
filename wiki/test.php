<?php
include("inc/lang/en/lang.php");
$orig = $lang;
unset($lang);
include("inc/lang/lt/lang.php");
$un = array();
foreach($orig as $key=>$val){
	if (!isset($lang[$key]) || empty($lang[$key])){
		$un[$key] = $val;
	}
}
echo "<pre>";
print_r($un);