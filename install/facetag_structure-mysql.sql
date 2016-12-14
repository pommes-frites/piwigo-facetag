--
-- Table structure for table `piwigo_facetags`
--

CREATE TABLE IF NOT EXISTS `piwigo_facetags` (
  `tag_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`tag_id`),
  KEY `facetags_i1` (`tag_id`)
) ENGINE=MyISAM;

DROP TRIGGER IF EXISTS `piwigo_tags_delete_trigger`;
DELIMITER $$
CREATE TRIGGER `piwigo_tags_delete_trigger` AFTER DELETE ON `piwigo_tags`
FOR EACH ROW
BEGIN
DELETE FROM `piwigo_facetags`
    WHERE `piwigo_facetags`.`tag_id` = OLD.`id`;
END$$
DELIMITER ;

--
-- Table structure for table `piwigo_image_facetag`
--

CREATE TABLE IF NOT EXISTS `piwigo_image_facetag` (
  `image_id` mediumint(8) unsigned NOT NULL default '0',
  `tag_id` smallint(5) unsigned NOT NULL default '0',
  `top` float unsigned NOT NULL default '0',
  `left` float unsigned NOT NULL default '0',
  `width` float unsigned NOT NULL default '0',
  `height` float unsigned NOT NULL default '0',
  PRIMARY KEY  (`image_id`,`tag_id`),
  KEY `image_facetag_i1` (`tag_id`)
) ENGINE=MyISAM;

