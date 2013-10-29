<?php
header('Content-type: text/xml; charset=utf-8');
$url = htmlspecialchars($_GET['url']);
echo file_get_contents('http://'.$url,0); ?>