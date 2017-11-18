<?php

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');


/**
 * This class is used to expose maintenance methods to the plugins manager
 * It must extends PluginMaintain and be named "PLUGINID_maintain"
 * where PLUGINID is the directory name of your plugin
 */
class MugShot_maintain extends PluginMaintain
{

  /**
   * plugin installation
   *
   * perform here all needed step for the plugin installation
   * such as create default config, add database tables,
   * add fields to existing tables, create local folders...
   */
  function install($plugin_version, &$errors=array())
  {
    $configQuery = 'INSERT INTO ' . CONFIG_TABLE . ' (param,value,comment) VALUES ("MugShot","","MugShot configuration values");';

    $createTableQuery = 'CREATE TABLE IF NOT EXISTS `face_tag_positions` (
      `image_id` mediumint(8) unsigned NOT NULL default "0",
      `tag_id` smallint(5) unsigned NOT NULL default "0",
      `top` float unsigned NOT NULL default "0",
      `left` float unsigned NOT NULL default "0",
      `width` float unsigned NOT NULL default "0",
      `height` float unsigned NOT NULL default "0",
      `image_width` float unsigned NOT NULL default "0",
      `image_height` float unsigned NOT NULL default "0",
      PRIMARY KEY (`image_id`,`tag_id`)
    )';

    $deleteTriggerQuery = "DROP TRIGGER IF EXISTS `sync_mug_shot`;";

    $makeTriggerQuery = "CREATE TRIGGER `sync_mug_shot`
      AFTER DELETE ON `piwigo_tags`
      FOR EACH ROW DELETE FROM face_tag_positions
      WHERE face_tag_positions.tag_id = old.id";

    pwg_query($configQuery);
    pwg_query($createTableQuery);
    pwg_query($deleteTriggerQuery);
    pwg_query($makeTriggerQuery);

  }

  function deactivate()
  {
    // Do nothing
  }

  function update($old_version, $new_version, &$errors=array())
  {
    // Do nothing
  }

  function uninstall()
  {
    conf_delete_param('MugShot');
  }

}
