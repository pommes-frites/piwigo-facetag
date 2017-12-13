<?php

defined('MUGSHOT_PATH') or die('Hacking attempt!');

include_once(PHPWG_ROOT_PATH . 'admin/include/functions.php');

function add_mugshot_methods($arr) {
  $service = &$arr[0];
  $service -> addMethod(
    'mugshot.bookem',
    'book_mugshots',
    array(),
    'Parses face tags from user.'
  );
}

// Check if the directory is empty
function dir_is_empty($dir) {
  $handle = opendir($dir);
  while (false !== ($entry = readdir($handle))) {
    if ($entry != "." && $entry != "..") {
      return FALSE;
    }
  }
  return TRUE;
}

// Automatically rotate the image before cropping
function auto_rotate_image($image) {
  $o = $image->getImageOrientation();

  switch ($o) {
    case 3:
      $image->rotateImage('#000', 180);
      break;

    case 6:
      $image->rotateImage('#000', 90);
      break;

    case 8:
      $image->rotateImage('#000', -90);
      break;
  }

  $image->setImageOrientation(imagick::ORIENTATION_TOPLEFT);
}

// Crop faces in the image
function crop_image_faces($p, $iw, $ih, $width, $height, $left, $top) {
  try {
    $image = new Imagick($p);
    auto_rotate_image($image);
    $image -> resizeImage($iw, $ih, Imagick::FILTER_LANCZOS,1);
    $image -> cropImage($width, $height, $left, $top);
    $struc = str_replace(' ', '_', strtolower($name));
    $structure = getcwd()."/plugins/MugShot/training/".$struc;

    if (!file_exists($structure)) {
      mkdir($structure, 0775, true);
    }

    if (!file_exists($structure.'/'.$imgFileName)) {
      $image -> writeImage($structure.'/'.$imgFileName);
      chmod($structure, 0760);
    }

  } catch (Exception $e) {
    error_log($e->getMessage(), 0);
  }
}

// Delete image face if the tag is deleted
function delete_image_faces($name, $imgFileName) {
  try {
    $struc = str_replace(' ', '_', strtolower($name));
    $structure = getcwd()."/plugins/MugShot/training/".$struc;
    $remTest = false;
    $dirTest = false;

    if (file_exists($structure.'/'.$imgFileName)) {
      $remTest = unlink($structure.'/'.$imgFileName);
    }

    if (dir_is_empty($structure)) {
      $dirTest = rmdir($structure);
    }

  } catch (Exception $e) {
    error_log($e->getMessage(), 0);
  }
}

// Add the posted mugshots to the database
function book_mugshots($data, &$service) {

  if (!$service -> isPost()) {
    return new PwgError(405, "HTTP POST REQUIRED");
  }

  $imageId = pwg_db_real_escape_string($data['imageId']);
  $plugin_config = unserialize(conf_get_param(MUGSHOT_ID));

  if ($plugin_config['autotag']) {
    $sql = "SELECT * FROM `". IMAGES_TABLE . "` WHERE `id`=".$imageId.";";
    $imgData = pwg_db_fetch_assoc(pwg_query($sql));
    $imgFP = $imgData['path'];
    $imgFileName = $imgData['file'];
  }

  unset($data['imageId']);
  $tagSql = '';
  $varString = '';
  $dString = '';

  foreach ($data as $key => $value) {
    $tag = pwg_db_real_escape_string($value['tagId']);
    $name = pwg_db_real_escape_string($value['name']);
    $top = pwg_db_real_escape_string($value['top']);
    $left = pwg_db_real_escape_string($value['left']);
    $width = pwg_db_real_escape_string($value['width']);
    $height = pwg_db_real_escape_string($value['height']);
    $imgW = pwg_db_real_escape_string($value['imageWidth']);
    $imgH = pwg_db_real_escape_string($value['imageHeight']);
    $rm = pwg_db_real_escape_string($value['removeThis']);

    // Remove or add cropped faces in the images to a directory.
    if($plugin_config['autotag']) {
      if($rm === '0' && $width >= 40 && $height >= 40 && extension_loaded('imagick') === true) {
        return crop_image_faces($imgFP, $imgW, $imgH, $width, $height, $left, $top);
      }

      if($rm == 1) {
        delete_image_faces($name, $imgFileName);
      }
    }

    // Remove a mugshot
    if ($rm == 1) {
      $dString .= ($tag != '') ? $tag . ',' : '';
      continue;
    }


    // Update a mugshot
    if ($tag == -1 && $name != '') {
      $tagName = tag_id_from_tag_name($name);
      $varString .= "('" . $tagName . "','" . $imageId . "','" . $top . "','" . $left . "','";
      $varString .= $width . "','" . $height . "','" . $imgW . "','" . $imgH . "'),";
      $tagSql .= "('" . $imageId . "','" . $tagName . "'),";
    } elseif ($tag > 0 && $name != '') {
      $url = strtolower(str_replace(' ', '_', $name));
      $sql = "UPDATE " . TAGS_TABLE . " AS tt SET tt.name='" . $name . "', tt.url_name='" . $url . "' WHERE tt.id='" . $tag . "';";
      $r = pwg_query($sql);
    }
  }

  // Add new mugshot
  if ($varString !== '') {
    $varString = substr(trim($varString), 0, -1);
    $frameSql = "INSERT INTO " . MUGSHOT_TABLE . " (`tag_id`, `image_id`, `top`, `lft`, `width`, `height`, `image_width`, `image_height`) ";
    $frameSql .= "VALUES " . $varString . " ON DUPLICATE KEY UPDATE `top`=VALUES(`top`), ";
    $frameSql .= "`lft`=VALUES(`lft`), `width`=VALUES(`width`), `height`=VALUES(`height`), ";
    $frameSql .= "`image_width`=VALUES(`image_width`), `image_height`=VALUES(`image_height`);";
    $tagSql = substr(trim($tagSql), 0, -1);
    $tagSql = "INSERT IGNORE INTO " . IMAGE_TAG_TABLE . " (`image_id`, `tag_id`) VALUES " . $tagSql . ';';
    $tagResult = pwg_query($tagSql);
    $frameResult = pwg_query($frameSql);
  } else {
    $tagResult = true;
    $frameResult = true;
  }

  // Delete mugshot
  if ($dString !== '') {
    $dString = '(' . substr(trim($dString), 0, -1) . ')';
    $deleteSql1 = "DELETE FROM `face_tag_positions` WHERE `tag_id` IN " . $dString . " AND `image_id`=".$imageId.";";
    $deleteSql2 = "DELETE FROM " . IMAGE_TAG_TABLE . " WHERE `tag_id` IN " . $dString . ";";
    $dResult1 = pwg_query($deleteSql1);
    $dResult2 = pwg_query($deleteSql2);
  } else {
    $dResult1 = true;
    $dResult2 = true;
  }

  return json_encode([$tagResult, $frameResult, $dResult1, $dResult2]);
}


?>
