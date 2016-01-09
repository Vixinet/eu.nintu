<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php

	$sql = db_connect();

	if(!$stmt = $sql -> prepare("SELECT id FROM profile_media WHERE uid=? AND id=?")) {
		_log(FN, 'Check media exists');
	}
  	$stmt->bind_param("si", $uid, $__media);
	$stmt->execute();
	$stmt->store_result();
	$total = $stmt->num_rows;
	$stmt->close();

	if($total == 0) {
		_error('MEDIA_UNKNOW');
	}

	if(!$stmt = $sql -> prepare("UPDATE profile SET picture=? WHERE uid=?")) {
		_log(FN, 'Update profile picture');
	}
	$stmt->bind_param("is", $__media, $uid);
	$stmt->execute();
	$stmt->close();
	
?>