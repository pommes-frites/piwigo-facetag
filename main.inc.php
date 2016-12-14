<?php
/*
Plugin Name: facetag
Version: 0.0.1
Description: 
Plugin URI: 
Author: pommes-frites
Author URI: 
*/

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');


// +-----------------------------------------------------------------------+
// | plugin constants                                               |
// +-----------------------------------------------------------------------+
define('FACETAG_ID', basename(dirname(__FILE__)));
define('FACETAG_PATH', PHPWG_PLUGINS_PATH . FACETAG_ID . '/');
define('IMAGE_FACETAG_TABLE', '`piwigo_image_facetag`');
define('FACETAGS_TABLE', '`piwigo_facetags`');

if (!defined('IN_ADMIN')) {
	// file containing all public handlers functions
	$public_file = FACETAG_PATH . 'include/public_events.inc.php';

	// add button on photos pages
	add_event_handler('loc_end_picture', 'facetag_add_button', EVENT_HANDLER_PRIORITY_NEUTRAL, $public_file);
}

// file containing API function
$ws_file = FACETAG_PATH . 'include/ws_functions.inc.php';

// add API function
add_event_handler('ws_add_methods', 'facetag_ws_add_methods', EVENT_HANDLER_PRIORITY_NEUTRAL, $ws_file);
