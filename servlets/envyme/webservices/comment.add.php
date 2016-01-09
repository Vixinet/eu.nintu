<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php

	$sql = db_connect();

	if(!$stmt = $sql -> prepare('INSERT INTO profile_media_comment (owner, media, text) VALUES (?, ?, ?)')) {
		_log(FN, 'Add comment');
	}

	$stmt->bind_param("sis", $uid, $__media, $__text);
	$stmt->execute();
	$stmt->close();
	
?>