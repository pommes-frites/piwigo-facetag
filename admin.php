<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Fetch the template
global $template;

/*
 * Include some php files for functions and such.
 */
 include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');
 include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');



/*
 * Exit if user status is not okay
 */
check_status(ACCESS_ADMINISTRATOR);



/*
 * Update the parameters and escape the serialized string.
 */
if(isset($_POST['save'])) {
	unset($_POST['save']);
	conf_update_param(MUGSHOT_ID, pwg_db_real_escape_string(serialize($_POST)));
	array_push($page['infos'], l10n('Information data registered in database'));
}



/*
 * Create tab for the admin page
 */
$tabs = array(
	array(
		'code' => 'config',
		'label' => l10n('Configuration'),
	),
);

$tab_codes = array_map(create_function('$a', 'return $a["code"];'), $tabs);

if (isset($_GET['tab']) && in_array($_GET['tab'], $tab_codes)) {
	$page['tab'] = $_GET['tab'];
} else {
	$page['tab'] = $tabs[0]['code'];
}

/*
 * Assign the tabs to tabsheet
 */
 $tabsheet = new tabsheet();
 foreach ($tabs as $tab)
 {
   $tabsheet->add(
     $tab['code'],
     $tab['label'],
     MUGSHOT_BASE_URL.'-'.$tab['code']
     );
 }
 $tabsheet->select($page['tab']);
 $tabsheet->assign();



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
		'PLUGIN_ACTION' => get_root_url() . 'admin.php?page=plugin-MugShot-admin'
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
