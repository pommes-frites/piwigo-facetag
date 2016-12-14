<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

define('FACETAG_ID', basename(dirname(__FILE__)));
define('FACETAG_PATH', PHPWG_PLUGINS_PATH . FACETAG_ID . '/');

class facetag_maintain extends PluginMaintain
{
	function install($plugin_version, &$errors=array()) { 
		run_sql_file(FACETAG_PATH.'install/facetag_structure-mysql.sql');
	}
	
	function activate($plugin_version, &$errors=array()) {
		
	}
	
	function update($old_version, $new_version, &$errors=array()) {
		
	}
	
	function deactivate() {
		
	}
	
	function uninstall() {
		
	}
}
	
function run_sql_file($location){
	//load file
	$commands = file_get_contents($location);

	//delete comments
	$lines = explode("\n",$commands);
	$commands = '';
	foreach($lines as $line){
		$line = trim($line);
		if( $line && !startsWith($line,'--') ){
			$commands .= $line . "\n";
		}
	}

	//convert to array
	$commands = explode(";", $commands);

	//run commands
	foreach($commands as $command){
		if(trim($command)){
			pwg_query($command);
		}
	}
}


// Here's a startsWith function
function startsWith($haystack, $needle){
	$length = strlen($needle);
	return (substr($haystack, 0, $length) === $needle);
}
