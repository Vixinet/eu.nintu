<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php

	$sql = db_connect();

	list($msec, $sec) = explode(' ', microtime(false));
	
	$file_id = sprintf("%d_%s_%010s", $sec, substr($msec, 2), rand(0, 999999999));

	$realpath_image = sprintf("binaries/media/nint/%s/%s.jpg", $uid, $file_id);
	$httppath_image = sprintf("%s/media:%s:%s", BIN_HOST, $uid, $file_id);

	$realpath_thumb = sprintf("binaries/media/nint/%s/%s_thumb.jpg", $uid, $file_id);
	$httppath_thumb = sprintf("%s/media:%s:%s_thumb", BIN_HOST, $uid, $file_id);

	if($_FILES['binary']['type'] != 'image/jpeg') {
		_error('FORMAT_NOT_SUPPORTED');
	}

	move_uploaded_file($_FILES['binary']['tmp_name'], $realpath_image);

	$src = imagecreatefromjpeg($realpath_image);
	$exif = exif_read_data($realpath_image);
	if(!empty($exif['Orientation'])) {
		switch($exif['Orientation']) {
			case 8: $src = imagerotate($src,90,0); break;
			case 3: $src = imagerotate($src,180,0); break;
			case 6: $src = imagerotate($src,-90,0); break;
		} 
	}
	imagejpeg($src, $realpath_image, 100);

	list($width, $height) = getimagesize($realpath_image);
	
	$newwidth = floor($width * 0.5);
	$newheight = floor($height * 0.5);
	$tmp = imagecreatetruecolor($newwidth, $newheight);
	imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
	imagejpeg($tmp, $realpath_thumb, 100);
	imagedestroy($src);
	imagedestroy($tmp);

	if(!$stmt = $sql -> prepare("INSERT INTO profile_media (uid, image, thumb) VALUES (?, ?, ?)")) {
		_log(FN, 'Add media in profile');
	}
	$stmt->bind_param("sss", $uid, $httppath_image, $httppath_thumb);
	$stmt->execute();
	$last_id = $stmt->insert_id;
	$stmt->close();

	$_response['image'] = _n($httppath_image);
	$_response['thumb'] = _n($httppath_thumb);

	if(isset($__default) and $__default) {
		$__media = $last_id;
		include_once('profile.picture.set.php');
	}
?>