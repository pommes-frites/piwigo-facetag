<?php
defined('FACETAG_PATH') or die('Hacking attempt!');

// For tag_id_from_tag_name function
include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');

function facetag_ws_add_methods($arr) {
    $service = &$arr[0];

    $service->addMethod('facetag.listTags', 'facetag_listTags', array('imageId' => array()), 'retrieves a list of tags than can be filtered');
    $service->addMethod('facetag.changeTag', 'facetag_changeTag', array('id' => array(), 'imageId' => array(), 'top' => array(), 'left' => array(), 'width' => array(), 'height' => array(), 'name' => array()), 'retrieves a list of tags than can be filtered');    
}

//https://nas.fritz.box/apps/piwigo/ws.php?format=json&method=facetag.listTags
function facetag_listTags($params, &$service) {
	if (!$service->isPost()) {
      return new PwgError(405, "This method requires HTTP POST");
    }
	
	$imageId = $params['imageId'];
	
	$answer = array();
	if($imageId == "-1") {
		$answer = getFaceTags();
	} else {
		$answer = getImageFaceTags($imageId);
	}
	
	return json_encode($answer);
}

function getFaceTags() {
	$sql  = "SELECT `id`, `name` FROM " . TAGS_TABLE . " WHERE EXISTS (";
	$sql .= "SELECT 1 FROM " . FACETAGS_TABLE . " WHERE `tag_id` = `id`);";
	return queryResult2Array(pwg_query($sql));
}

function getImageFaceTags($imageId) {
	$sql  = "SELECT imgFaceTag.`tag_id`, imgFaceTag.`top`, imgFaceTag.`left`, imgFaceTag.`width`, imgFaceTag.`height`, tags.`name` ";
	$sql .= "FROM " . IMAGE_FACETAG_TABLE . " imgFaceTag , " . TAGS_TABLE . " tags ";
	$sql .= "WHERE imgFaceTag.`tag_id` = tags.`id` AND imgFaceTag.`image_id` = " . $imageId . " AND EXISTS (";
	$sql .= "SELECT 1 FROM " . IMAGE_TAG_TABLE . " imgTag WHERE imgTag.`image_id` = imgFaceTag.`image_id` AND imgTag.`tag_id` = imgFaceTag.`tag_id`);";
	return queryResult2Array(pwg_query($sql));
}

function queryResult2Array($result) {
	$resultArray = array();
	while ($row = pwg_db_fetch_assoc($result)) {
		$resultArray[] = $row;
	}
	return $resultArray;
}

function facetag_changeTag($params, &$service) {
	if (!$service->isPost()) {
      return new PwgError(405, "This method requires HTTP POST");
    }
	
	$id = $params['id'];
	
	$answer = array();
	if($id < 0) {
		$answer['action'] = "INSERT";
		$answer['id'] = addImageFaceTag($params['imageId'], $params['name'], $params['top'], $params['left'], $params['width'], $params['height']);
	} elseif($params['name'] == "__DELETE__") {
		$answer['action'] = "DELETE";
		$answer['id'] = removeImageFaceTag($id, $params['imageId']);
	} else {
		$answer['action'] = "UPDATE";
		removeImageFaceTag($id, $params['imageId']);
		$answer['id'] = addImageFaceTag($params['imageId'], $params['name'], $params['top'], $params['left'], $params['width'], $params['height']);
	}
	
	return json_encode($answer);
}

function addImageFaceTag($imageId, $tagName, $top, $left, $width, $height) {
	$tagId = tag_id_from_tag_name($tagName);
	
	$sql = "INSERT IGNORE INTO " . IMAGE_TAG_TABLE . " (`image_id`, `tag_id`) VALUES (" . $imageId . ", " . $tagId . ");";
	pwg_query($sql);
	
	$sql = "INSERT INTO " . IMAGE_FACETAG_TABLE . " (`image_id`, `tag_id`, `top`, `left`, `width`, `height`) VALUES (" . $imageId . ", " . $tagId . ", " . $top . ", " . $left . ", " . $width . ", " . $height . ")";
	$sql .= " ON DUPLICATE KEY UPDATE `top` = VALUES(`top`), `left` = VALUES(`left`), `width` = VALUES(`width`), `height` = VALUES(`height`);";
	pwg_query($sql);
	
	$sql = "INSERT IGNORE INTO " . FACETAGS_TABLE . " (`tag_id`) VALUES (" . $tagId . ");";
	pwg_query($sql);
	
	return $tagId;
}

function removeImageFaceTag($tagId, $imageId) {
	$sql = "DELETE FROM " . IMAGE_TAG_TABLE . " WHERE `image_id` = " . $imageId . " AND `tag_id` = " . $tagId . ";";
	pwg_query($sql);
	
	$sql  = "DELETE imgFaceTag FROM " . IMAGE_FACETAG_TABLE . " imgFaceTag ";
	$sql .= "LEFT JOIN " . IMAGE_TAG_TABLE . " imgTag ON imgFaceTag.`image_id` = imgTag.`image_id` AND imgFaceTag.`tag_id` = imgTag.`tag_id` ";
	$sql .= "WHERE imgTag.`image_id` IS NULL;";
	//$sql  = "DELETE imgFaceTag FROM " . IMAGE_FACETAG_TABLE . " imgFaceTag WHERE NOT EXISTS (";
	//$sql .= "SELECT 1 FROM " . IMAGE_TAG_TABLE . " imgTag WHERE imgTag.`image_id` = imgFaceTag.`image_id` AND imgTag.`tag_id` = imgFaceTag.`tag_id`);";
	pwg_query($sql);
	
	return -1;
}
