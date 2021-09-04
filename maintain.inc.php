<?php

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

/*
 * Plugin Constants
 */
defined('MUGSHOT_ID') or define('MUGSHOT_ID',      basename(dirname(__FILE__)));
defined('MUGSHOT_PATH') or define('MUGSHOT_PATH' ,   PHPWG_PLUGINS_PATH . MUGSHOT_ID . '/');

/*
 * Include custom helper functions
 */
include_once(MUGSHOT_PATH . 'include/helpers.php');

/**
 * This class is used to expose maintenance methods to the plugins manager
 * It must extends PluginMaintain and be named "PLUGINID_maintain"
 * where PLUGINID is the directory name of your plugin
 */
class MugShot_maintain extends PluginMaintain
{
  private $installed = false;

  function __construct($plugin_id)
  {
    parent::__construct($plugin_id);
  }

  /**
   * plugin installation
   *
   * perform here all needed step for the plugin installation
   * such as create default config, add database tables,
   * add fields to existing tables, create local folders...
   */
  function install($plugin_version, &$errors=array())
  {
    // Create the table to store face vector information
    create_facetag_table();

    // Create the trigger to automatically clean tag references when tags are removed.
    create_tag_drop_trigger();

    // Create the tagging user group and associate the current user.
    create_tag_group();

    $this->installed = true;
  }

  function deactivate()
  {
    // Do nothing
  }

  function update($old_version, $new_version, &$errors=array())
  {
    // Do nothing
  }

  function activate($plugin_version, &$errors=array())
  {
    if (!$this->installed)
    {
      $this->install($plugin_version, $errors);
    }
  }

  function uninstall()
  {
    conf_delete_param('MugShot');
  }
}
