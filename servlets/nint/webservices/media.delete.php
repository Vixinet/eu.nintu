<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();

	if(!$stmt = $sql -> prepare("SELECT picture FROM profile WHERE uid=?")) {
		_log(FN, 'Check media exists');
	}
  	$stmt->bind_param("s", $uid);
	$stmt->execute();
	$stmt->bind_result($currentPicture);
	$stmt->fetch();
	$stmt->close();

	if($currentPicture == $__media) {
		_error('MEDIA_USED_AS_PROFILE_PICTURE');
	}

	if(!$stmt = $sql -> prepare("SELECT image, thumb FROM profile_media WHERE uid = ? AND id = ?")) {
		_log(FN, 'Retrieve bath on server');
	}
	$stmt->bind_param("si", $uid, $__media);
	$stmt->execute();
	$stmt->bind_result($image, $thumb);
	$stmt->fetch();
	$stmt->close();

	// unlink($image);
	// unlink($thumb);

	if(!$stmt = $sql -> prepare("DELETE FROM profile_media WHERE uid = ? AND id = ?")) {
		_log(FN, 'Delete media in profile');
	}
	$stmt->bind_param("si", $uid, $__media);
	$stmt->execute();
	$stmt->close();

	// ToDo : 
	// - Delete media file from binary folder
	
?>