<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');


/*
 * Creates the tagging user group and associates the current user with that group;
 */
function create_tag_group() {
    global $conf;

    if (!isset($conf['MugShot'])):
      include(dirname(__FILE__).'/config_default.inc.php');
      conf_update_param('MugShot', $config_default);
      load_conf_from_db();
    endif;

    $conf['MugShot'] = unserialize($conf['MugShot']);

    // Checks to see if a taggers group exists.
    $checkTaggerGroupQuery = "SELECT id FROM " . GROUPS_TABLE . " WHERE name='Taggers'";
    
    $result = fetch_sql($checkTaggerGroupQuery, 'id', false);

    if ($result == 0)
    {
      $makeTaggerGroupQuery = "INSERT INTO " . GROUPS_TABLE . ' (name) VALUES ("Taggers")';

      pwg_query($makeTaggerGroupQuery);
      
      $result = fetch_sql($checkTaggerGroupQuery, 'id', false);
    }

    $group_id = $result[0];

    $user = $_SESSION['pwg_uid'];

    // Determines if the taggers group is associated with the current user and, if not, associates it.
    $checkUserAssociationQuery = "SELECT * FROM " . USER_GROUP_TABLE . " WHERE group_id=$group_id AND user_id=$user";
    
    $association = fetch_sql($checkUserAssociationQuery, 'id', false);

    if ($association == 0)
    {
        $makeUserAssociationQuery = "INSERT INTO " . USER_GROUP_TABLE . " (group_id, user_id) VALUES ('$group_id','$user')";

        pwg_query($makeUserAssociationQuery);
    }

    // Note that array_push returns the index of the new array item.
    array_push($conf['MugShot']['groups'], $group_id);

    $conf['MugShot']['groups'] = array_unique($conf['MugShot']['groups']);

    conf_update_param(MUGSHOT_ID, $conf['MugShot']);

    load_conf_from_db();
}

/*
 * Creates the drop trigger to clear database values
 */
function create_tag_drop_trigger() {
    $deleteTriggerQuery = "DROP TRIGGER IF EXISTS `sync_mug_shot`;";

    pwg_query($deleteTriggerQuery);

    // [mysql error 1419] You do not have the SUPER privilege and binary logging is enabled (you *might* want to use the less safe log_bin_trust_function_creators variable)
    // This query is silently failing.
    $makeTriggerQuery = "CREATE TRIGGER `sync_mug_shot`
      AFTER DELETE ON ".TAGS_TABLE."
      FOR EACH ROW DELETE FROM face_tag_positions
      WHERE face_tag_positions.tag_id = old.id";

    pwg_query($makeTriggerQuery);
}

/*
 * Creates the MugShot face tag table with all data columns required for resizing.
 */
function create_facetag_table() {
    $configQuery = 'INSERT INTO ' . CONFIG_TABLE . ' (param,value,comment) VALUES ("MugShot","","MugShot configuration values");';

    pwg_query($configQuery);

    $createTableQuery = 'CREATE TABLE IF NOT EXISTS `face_tag_positions` (
      `image_id` mediumint(8) unsigned NOT NULL default "0",
      `tag_id` smallint(5) unsigned NOT NULL default "0",
      `top` float unsigned NOT NULL default "0",
      `lft` float unsigned NOT NULL default "0",
      `width` float unsigned NOT NULL default "0",
      `height` float unsigned NOT NULL default "0",
      `image_width` float unsigned NOT NULL default "0",
      `image_height` float unsigned NOT NULL default "0",
      PRIMARY KEY (`image_id`,`tag_id`)
    )';

    pwg_query($createTableQuery);
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

  ?>