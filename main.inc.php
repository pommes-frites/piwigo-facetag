<?php
/*
Plugin Name: Mug Shot
Version: 1.0.0
Description: Improved face tagging for Piwigo
Plugin URI: auto
Author: cccraig
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');


/*
 * Plugin Constants
 */
define('MUGSHOT_ID',      basename(dirname(__FILE__)));
define('MUGSHOT_PATH' ,   PHPWG_PLUGINS_PATH . MUGSHOT_ID . '/');
define('MUGSHOT_ADMIN',   get_root_url() . 'admin.php?page=plugin-' . MUGSHOT_ID);
define('MUGSHOT_VERSION', '1.0.0');
define('MUGSHOT_TABLE', '`face_tag_positions`');


/*
 * API Functions
 */
$ws_file = MUGSHOT_PATH . 'include/capture.php';


/*
 * Admin Event Handlers
 */
add_event_handler('get_admin_plugin_menu_links', 'mugshot_admin_menu');
add_event_handler('init', 'mugshot_lang_init');
add_event_handler('loc_begin_page_header', 'mugshot_files', 40, 2);
add_event_handler('loc_end_picture', 'mugshot_button');


/*
 * Conditional Logic for groups
 */
$current_user_groups = query_mugshot_groups();
$plugin_config = unserialize(conf_get_param(MUGSHOT_ID));
$group_list = explode(',', $plugin_config['group_list']);

if ($current_user_groups != 0) {
  $intersect = array_intersect($group_list, $current_user_groups);
}

if (is_array($current_user_groups) && count($intersect) != 0) {

  // Retrieve the current user theme
  $query = 'SELECT theme FROM ' . USER_INFOS_TABLE . ';';
  $theme = pwg_db_fetch_assoc(pwg_query($query))['theme'];

  if (strpos($theme, 'bootstrap') !== false) {
    define('BOOT', true);
  } else {
    define('BOOT', false);
  }

  define('MUGSHOT_USER_ADMIN', true);
  if(script_basename() != 'admin') {
    add_event_handler('loc_end_page_tail', 'insert_tag_list');
  }
  add_event_handler('ws_add_methods', 'add_MUGSHOT_methods', EVENT_HANDLER_PRIORITY_NEUTRAL, $ws_file);
} else {
  define('MUGSHOT_USER_ADMIN', false);
}


/*
 * Fetches Sql
 */
function fetch_sql($sql, $col, $ser) {
  $result = pwg_query($sql);

  while ($row = pwg_db_fetch_assoc($result)) {
    $data[] = $row;
  }

  if (!isset($data)) {
    $data = 0;
  } else {
    if($col !== false) {
      $data = array_column($data, $col);
    }
  }

  return ($ser) ? json_encode($data) : $data;
}


/*
 * Loads translations
 */
function mugshot_lang_init(){
	load_language('plugin.lang', MUGSHOT_PATH);
}


/*
 * Initializes the admin menu
 */
function mugshot_admin_menu( $menu ) {
	array_push(
		$menu,
		array(
			'NAME'  => 'MugShot',
			'URL'   => get_admin_plugin_menu_link(dirname(__FILE__)).'/admin.php'
		)
	);
	return $menu;
}


/*
 * Catch the page header and combine our css
 */
function mugshot_files() {

	if(script_basename() != 'admin') {

		global $template;

    if(MUGSHOT_USER_ADMIN) {
      $style_path = 'plugins/MugShot/css/admin_style.css';
      $script_path = 'plugins/MugShot/js/admin_mug.js';
    } else {
      $style_path = 'plugins/MugShot/css/style.css';
      $script_path = 'plugins/MugShot/js/mug.js';
    }

		$template -> func_combine_css(array('id' => 'customMugCss', 'path' => $style_path));
    $template -> func_combine_script(  array('id' => 'customMugJs', 'path' => $script_path, 'load' => 'async'));
	}
}


/*
 * Queries current user groups
 */
function query_mugshot_groups() {
  if (isset($_SESSION['pwg_uid'])) {
    $user = $_SESSION['pwg_uid'];
  } else {
    return 0;
  }

  $sql = 'SELECT gt.name FROM ' . USER_GROUP_TABLE . ' AS ugt
    INNER JOIN ' . GROUPS_TABLE . ' AS gt
    ON ugt.group_id=gt.id
    WHERE ugt.user_id=' . $user . ';';

  return fetch_sql($sql, 'name', false);
}


/*
 * Queries all tags in database
 */
function defined_tags() {
  $sql = 'SELECT name FROM ' . TAGS_TABLE . ' ORDER BY lastmodified ASC LIMIT 100;';

  $x = fetch_sql($sql, 'name', false);

  return ($x == 0) ? [] : $x;
}


/*
 * Queries tagged faces for the image id
 */
function defined_mugshots( $id ) {
	$sql = '
  SELECT
    mst.image_id,
    mst.tag_id,
    mst.top,
    mst.lft,
    mst.width,
    mst.height,
    mst.image_width,
    mst.image_height,
    tt.name
  FROM ' . MUGSHOT_TABLE . ' AS mst
  INNER JOIN `' . TAGS_TABLE . '` AS tt ON mst.tag_id = tt.id
  WHERE mst.image_id = ' . $id . ';';

  return fetch_sql($sql, false, true);
}


/*
 * Insert the tag button on photo pages
 */
function mugshot_button() {

	if(script_basename() != 'admin') {

		global $template, $page;

    /*
     * Path to processing file
     */
    $url = get_root_url() . 'ws.php?format=json&method=mugshot.bookem';


		/*
		 * Assign template variables
		 */
		$template -> assign('MUGSHOT_BUTTON', realpath(MUGSHOT_PATH));
    $template -> assign('MUGSHOT_ACTION', $url);
    $template -> assign('IMAGE_ID', $page['image_id']);
    $template -> assign('MUGSHOTS', defined_mugshots($page['image_id']));


		/*
		 * Parse button template file and append to picture buttons
		 */
 		$template -> set_filename('button', realpath(MUGSHOT_PATH . 'template/button.tpl'));
    $button = $template -> parse('button', true);
    $template -> add_picture_button($button, 1);
    $template -> parse_picture_buttons();
	}
}


/*
 * Insert list of tags for autopopulating tags
 */
function insert_tag_list() {

  global $template;

  /*
   * Array of tags
   */
  $template -> assign('MUGSHOT_TAG_LIST', defined_tags());

  /*
   * Specify the tag list template file
   */
  $template -> set_filename('MUGSHOT_TAG_TEMP', realpath(MUGSHOT_PATH . 'template/taglist.tpl'));

  /*
   * Parse template file and append to main template
   */
  $template -> append('footer_elements', $template -> parse('MUGSHOT_TAG_TEMP', false));
}
?>
