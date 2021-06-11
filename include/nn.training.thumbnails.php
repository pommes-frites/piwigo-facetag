<?php

defined('MUGSHOT_PATH') or die('Hacking attempt!');

// Check if the directory is empty
function dir_is_empty($dir) {
  if (!file_exists($dir)) {
    return;
  }
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
function crop_image_faces($p, $imgFileName, $name, $iw, $ih, $width, $height, $left, $top) {
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

    // If the file exists that means you have,
    // 1) tagged someone twice in the same photo,
    // 2) tagged someone in the same photo that is asociated with another album.
    // In Either case, we don't want duplicate photos that are almost exactly the same cluttering up our training data.
    if (!file_exists($structure.'/'.$imgFileName)) {
      $image -> writeImage($structure.'/'.$imgFileName.'.jpg');
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
      $remTest = unlink($structure.'/'.$imgFileName.'.jpg');
    }

    if (dir_is_empty($structure)) {
      $dirTest = rmdir($structure);
    }

  } catch (Exception $e) {
    error_log($e->getMessage(), 0);
  }
}

?>
