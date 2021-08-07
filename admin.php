<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Fetch the template
global $template;

/*
 * Include some php files for functions and such.
 */
 include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');
 include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');

 $my_base_url = get_root_url().'admin.php?page=plugin-'.basename(dirname(__FILE__));

/*
 * Exit if user status is not okay
 */
check_status(ACCESS_WEBMASTER);



// +-----------------------------------------------------------------------+
// |                            Tabssheet
// +-----------------------------------------------------------------------+
$tabsheet = new tabsheet();

$tabs = array(
	array(
		'code' => 'config',
		'label' => l10n('Configuration'),
	),
);

if (empty($conf['MugShot_tabs']))
{
  $conf['MugShot_tabs'] = $tabs;
}

$page['tab'] = isset($_GET['tab']) ? $_GET['tab'] : $conf['MugShot_tabs'][0]['code'];

if (!in_array($page['tab'], $conf['MugShot_tabs'][0])) die('Hacking attempt!');

foreach ($conf['MugShot_tabs'] as $tab)
{
  $tabsheet->add($tab['code'], $tab['label'], $my_base_url.'-'.$tab['code']);
}

$tabsheet->select($page['tab']);

$tabsheet->assign();



/*
 * Update the parameters and escape the serialized string.
 */
if(isset($_POST['save'])) {
	unset($_POST['save']);
	conf_update_param(MUGSHOT_ID, pwg_db_real_escape_string(serialize($_POST)));
	array_push($page['infos'], l10n('Information data registered in database'));
}



/*
 * Add our template to the global template
 */
$template -> set_filenames(
	array(
		'plugin_admin_content' => dirname(__FILE__) . '/template/admin.tpl'
	)
);



/*
 * Assign action to template for the form submit
 */
$template -> assign(
	array(
		'PLUGIN_ACTION' => get_root_url() . 'admin.php?page=plugin-MugShot'
	)
);


/*
 * Retrieve configuration variable.
 */
if (empty($_POST)) {
	$data = unserialize(conf_get_param(MUGSHOT_ID));
} else {
	$data = $_POST;
}



/*
 * Assign configuration data to the template
 */
$template -> assign($data);



/*
 * Fetch groups for mugshot
 */
$query = 'SELECT id FROM '.GROUPS_TABLE.';';

$group_ids = query2array($query, null, 'id');

$template->assign(
	array(
	  'CACHE_KEYS' => get_admin_client_cache_keys(array('groups')),
	  'groups' => $group_ids,
		'groups_selected' => isset($data['groups']) ? $data['groups'] : []
	)
);



/*
 * Assign template contents to admin content
 */
$template -> assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');
?>
