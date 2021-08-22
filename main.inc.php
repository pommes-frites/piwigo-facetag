<?php
/*
Plugin Name: Mug Shot
Version: 2.0.0
Description: Improved face tagging for Piwigo
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=867
Author: cccraig
Has Settings: true
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');


/*
 * Plugin Constants
 */
define('MUGSHOT_ID',      basename(dirname(__FILE__)));
define('MUGSHOT_PATH' ,   PHPWG_PLUGINS_PATH . MUGSHOT_ID . '/');
define('MUGSHOT_ADMIN',   get_root_url() . 'admin.php?page=plugin-' . MUGSHOT_ID);
define('MUGSHOT_BASE_URL',   get_root_url() . 'admin.php?page=plugin-' . MUGSHOT_ID);
define('MUGSHOT_VERSION', '2.0.0');
define('MUGSHOT_TABLE', '`face_tag_positions`');


/*
 * API Functions
 */
$ws_file = MUGSHOT_PATH . 'include/capture.php';


/*
 * Admin Event Handlers
 */
add_event_handler('init', 'mugshot_lang_init');
add_event_handler('loc_begin_page_header', 'mugshot_files', 40, 2);
add_event_handler('loc_end_picture', 'mugshot_button');

/*
 * Include custom helper functions
 */
include_once(MUGSHOT_PATH . 'include/helpers.php');

/*
 * Conditional Logic for groups
 */
$current_user_groups = query_mugshot_groups();

$intersect = array();

if (count($current_user_groups) != 0) {
  $plugin_config = unserialize(conf_get_param(MUGSHOT_ID));

  $group_list = $plugin_config['groups'] ?? array();

  if(is_array($group_list) && count($group_list) != 0) {
    $intersect = array_intersect($group_list, $current_user_groups);
  }
}

if (is_array($current_user_groups) && count($intersect) != 0) {

  // Retrieve the current user theme
  $query = 'SELECT theme FROM ' . USER_INFOS_TABLE . ';';
  $theme = strtolower(pwg_db_fetch_assoc(pwg_query($query))['theme']);

  switch ($theme) {
    case 'bootstrap_darkroom':
      define('BOOT', 1);
      break;

    case 'bootstrapdefault':
      define('BOOT', 1);
      break;

    default:
      define('BOOT', 0);
      break;
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
 * Loads translations
 */
function mugshot_lang_init(){
	load_language('plugin.lang', MUGSHOT_PATH);
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
    return array();
  }

  $sql = 'SELECT gt.id FROM ' . USER_GROUP_TABLE . ' AS ugt
    INNER JOIN ' . GROUPS_TABLE . ' AS gt
    ON ugt.group_id=gt.id
    WHERE ugt.user_id=' . $user . ';';

  $res = fetch_sql($sql, 'id', false);

  return ($res && count($res) == 0) ? array() : $res;
}


/*
 * Queries all tags in database
 */
function defined_tags($max_tags) {
  $sql = 'SELECT name FROM ' . TAGS_TABLE . " ORDER BY lastmodified ASC LIMIT $max_tags;";

  $x = fetch_sql($sql, 'name', false);

  return ($x == 0) ? [] : $x;
}


/*
 * Queries tagged faces for the image id
 */
function defined_mugshots( $id ) {
  $mugshotSql = '
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

  $mugshotSqlResult = fetch_sql($mugshotSql, false, false);

  if (is_array($mugshotSqlResult)) {
    foreach($mugshotSqlResult as $key => $mugshot) {
      $tagSql = '
      SELECT
        id,
        url_name
      FROM ' . TAGS_TABLE . '
      WHERE id=' . $mugshot['tag_id'] . ';
      ';
      $tagSqlResult = fetch_sql($tagSql, false, false);
      $tagUrl = make_index_url(array('tags' => array($tagSqlResult[0])));
      $mugshotSqlResult[$key]['tag_url'] = $tagUrl;
    }
  }


  return json_encode($mugshotSqlResult);
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

  $plugin_config = unserialize(conf_get_param(MUGSHOT_ID));

  $max_tags = $plugin_config['max_tags'] ?? 500;

  /*
   * Array of tags
   */
  $template -> assign('MUGSHOT_TAG_LIST', defined_tags($max_tags));

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
