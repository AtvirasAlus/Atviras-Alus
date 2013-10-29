<?php
defined('ROOT_DIR')
    || define('ROOT_DIR',
              realpath('.'));
set_include_path('.' 
	.PATH_SEPARATOR.ROOT_DIR.'/application/library' 
	);
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH',
              realpath('./application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV',
              (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV')
                                         : 'production'));
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

$application=new Zend_Application(APPLICATION_ENV,'./application/configs/application.ini');
$application->bootstrap();
$application->run();
?>
