<?php

/*
Plugin Name: Facebook, Twitter & Google+ Social Widgets
Plugin URI: http://www.ab-weblog.com/en/wordpress-plug-ins/social-widgets/
Description: Simple plugin that displays the most important social widgets from Facebook, Twitter and Google+ below your posts. Very easy to setup: just activate the plug-in and the social widgets are displayed immediately - no complicated configuration!
Version: 1.3.6
Author: Andreas Breitschopp
Author URI: http://www.ab-weblog.com
*/

/*
Copyright 2011 by Andreas Breitschopp (e-mail: a-breitschopp@ab-tools.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined ('SW_DEF_STRING')) define('SW_DEF_STRING', 'sw');

if (!defined ('SW_PLUGIN_PATH'))  
  define ('SW_PLUGIN_PATH', plugin_basename (__FILE__));

load_plugin_textdomain(SW_DEF_STRING, false, dirname(SW_PLUGIN_PATH) . '/languages/');

require_once (dirname(__FILE__) . '/includes/SWOutput.php');

if (is_admin())
  require_once (dirname(__FILE__) . '/includes/SWAdmin.php');
?>
