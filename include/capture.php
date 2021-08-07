<?php

defined('MUGSHOT_PATH') or die('Hacking attempt!');

include_once(PHPWG_ROOT_PATH . 'admin/include/functions.php');
include_once(MUGSHOT_PATH . 'include/nn.training.thumbnails.php');

function add_mugshot_methods($arr) {
  $service = &$arr[0];
  $service -> addMethod(
    'mugshot.bookem',
    'book_mugshots',
    array(),
    'Parses face tags from user.'
  );
}

/**
 * Converts names to ucfirst case, as determined by a space between surnames.
 */
function get_pretty_name($labeledTagName) {
  $safeName = pwg_db_real_escape_string($labeledTagName);

  $splitName = explode(" ", $safeName);

  $arraySize = count($splitName);

  $prettyName = array();

  for ($i=0; $i < $arraySize; $i++) { 
    $prettyName[$i] = ucfirst($splitName[$i]);
  }

  return implode(" ", $prettyName);
}

// Add the posted mugshots to the database
function book_mugshots($data, &$service) {

  if (!$service -> isPost()) {
    return new PwgError(405, "HTTP POST REQUIRED");
  }

  $imageId = pwg_db_real_escape_string($data['imageId']);
  $plugin_config = unserialize(conf_get_param(MUGSHOT_ID));

  unset($data['imageId']);
  $imageIdTagIdInsertionString = '';             // 
  $faceTagPositionsInsertionString = '';          // Variable string. Groups data for entry in SQL database.
  $deleteTagQuery = '';            // Delete string. Tags in the current image being removed.

  $totalImageTags = count($data);

  foreach ($data as $key => $value) {
    $labeledTagName = get_pretty_name($value['name']);
        
    // If something empty was submitted, just ignore it.
    if ($labeledTagName == '') {
      continue;
    }

    $existingTagId = pwg_db_real_escape_string($value['tagId']);
    $top = pwg_db_real_escape_string($value['top']);
    $left = pwg_db_real_escape_string($value['left']);
    $width = pwg_db_real_escape_string($value['width']);
    $height = pwg_db_real_escape_string($value['height']);
    $imgW = pwg_db_real_escape_string($value['imageWidth']);
    $imgH = pwg_db_real_escape_string($value['imageHeight']);
    $rm = pwg_db_real_escape_string($value['removeThis']);

    // If it's a brand new tag, we won't have sent a tag ID back with the data.
    $newTagId = ($existingTagId == -1) ? tag_id_from_tag_name($labeledTagName) : $existingTagId;

    // Create or remove the training thumbnails, depending on webmaster settings.
    if ($plugin_config['autotag']) {
      $sql = "SELECT * FROM `". IMAGES_TABLE . "` WHERE `id`=".$imageId.";";

      $imgData = pwg_db_fetch_assoc(pwg_query($sql));
      
      // DeepFace requires  the persons name and an appended integer for the count of the images, i.e. pinkie_jenkins5, pinkie_jenkins6.
      // By adding the imageId (unique) and tagId (unique per image) we get a simple way of achieving this without doing
      // an SQL query or counting the images in the directory. While $imgNumber is not guaranteed to be globally unique,
      // it will always be unique within our directory structure which is ../pinkie_jenkins/pinkie_jenkins<ImgId><TagId>.
      $imgNumber = ($existingTagId != -1) ? $imageId.$existingTagId : $imageId.$newTagId;

      // Remove or add cropped faces in the images to a directory.
      if(extension_loaded('imagick') === true && $rm == 0 && $width >= 40 && $height >= 40) {
        crop_image_faces($imgData['path'], $imgNumber, $labeledTagName, $imgW, $imgH, $width, $height, $left, $top);
      }
    
      if($rm == 1) {
        delete_image_faces($labeledTagName, $imgNumber);
      }
    }

    // Remove a mugshot
    if ($rm == 1) {
      $deleteTagQuery .= ($existingTagId != '') ? $existingTagId . ',' : '';
      continue;
    }

    // Update a mugshot
    if ($existingTagId == -1) {
      $faceTagPositionsInsertionString .= "('$newTagId','$imageId','$top','$left','$width','$height','$imgW','$imgH'),";
      $imageIdTagIdInsertionString .= "('$imageId','$newTagId'),";
    } elseif ($existingTagId > 0 && $labeledTagName != '') {
      $url = strtolower(str_replace(' ', '_', $labeledTagName));
      $sql = "UPDATE " . TAGS_TABLE . " AS tt SET tt.name='" . $labeledTagName . "', tt.url_name='" . $url . "' WHERE tt.id='" . $existingTagId . "';";
      $r = pwg_query($sql);
    }
  }

  // Add new mugshot
  if ($faceTagPositionsInsertionString !== '') {
    $faceTagPositionsInsertionString = substr(trim($faceTagPositionsInsertionString), 0, -1);
    $frameSql = "INSERT INTO " . MUGSHOT_TABLE . " (`tag_id`, `image_id`, `top`, `lft`, `width`, `height`, `image_width`, `image_height`) ";
    $frameSql .= "VALUES " . $faceTagPositionsInsertionString . " ON DUPLICATE KEY UPDATE `top`=VALUES(`top`), ";
    $frameSql .= "`lft`=VALUES(`lft`), `width`=VALUES(`width`), `height`=VALUES(`height`), `image_width`=VALUES(`image_width`), `image_height`=VALUES(`image_height`);";
    $imageIdTagIdInsertionString = substr(trim($imageIdTagIdInsertionString), 0, -1);
    $imageIdTagIdInsertionString = "INSERT IGNORE INTO " . IMAGE_TAG_TABLE . " (`image_id`, `tag_id`) VALUES " . $imageIdTagIdInsertionString . ';';
    $existingTagIdResult = pwg_query($imageIdTagIdInsertionString);
    $frameResult = pwg_query($frameSql);
  } else {
    $existingTagIdResult = true;
    $frameResult = true;
  }

  // Delete mugshot
  if ($deleteTagQuery !== '') {
    $deleteTagQuery = '(' . substr(trim($deleteTagQuery), 0, -1) . ')';
    $deleteSql1 = "DELETE FROM " . MUGSHOT_TABLE . " WHERE `tag_id` IN $deleteTagQuery AND `image_id`='$imageId';";
    $deleteSql2 = "DELETE FROM " . IMAGE_TAG_TABLE . " WHERE `tag_id` IN $deleteTagQuery;";
    $dResult1 = pwg_query($deleteSql1);
    $dResult2 = pwg_query($deleteSql2);
  } else {
    $dResult1 = true;
    $dResult2 = true;
  }

  return json_encode([$existingTagIdResult, $frameResult, $dResult1, $dResult2]);
}


?>
