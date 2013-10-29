<?
class Default_Bootstrap extends Zend_Application_Module_Bootstrap
{
 function _initAutoload()
	{
		$autoloader = new Zend_Application_Module_Autoloader(array('namespace' => '' ,'basePath' => dirname(__FILE__)));
		return $autoloader;
	}
}
?>
