<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php

	$sql = db_connect();

	list($msec, $sec) = explode(' ', microtime(false));
	
	$file_id = sprintf("%d_%s_%010s", $sec, substr($msec, 2), rand(0, 999999999));
	$filename_image = sprintf("%s.jpg", $file_id);
	$realpath_image = sprintf("%s/media/envyme/%s/%s", PATH_BINARIES, $uid, $filename_image);
	$httppath_image = sprintf("media/envyme/%s/%s", $uid, $filename_image);

	$filename_thumb = sprintf("%s_thumb.jpg", $file_id);
	$realpath_thumb = sprintf("%s/media/envyme/%s/%s", PATH_BINARIES, $uid, $filename_thumb);
	$httppath_thumb = sprintf("media/envyme/%s/%s", $uid, $filename_thumb);

	if($_FILES['binary']['type'] != 'image/jpeg') {
		_error('FORMAT_NOT_SUPPORTED');
	}

	move_uploaded_file($_FILES['binary']['tmp_name'], $realpath_image);

	$src = imagecreatefromjpeg($realpath_image);
	list($width,$height)=getimagesize($realpath_image);
	$newwidth=120;
	$newheight=($height/$width)*120;
	$tmp = imagecreatetruecolor($newwidth,$newheight);
	imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
	imagejpeg($tmp, $realpath_thumb, 100);
	imagedestroy($src);
	imagedestroy($tmp);

	if(!$stmt = $sql -> prepare("INSERT INTO profile_media (uid, image, thumb, text) VALUES (?,?,?,?)")) {
		_log(FN, 'Add media to profile');
	}
	$stmt->bind_param("ssss", $uid, $httppath_image, $httppath_thumb, $__text);
	$stmt->execute();
	$stmt->close();
	
?>