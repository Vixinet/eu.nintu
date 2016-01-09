<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php

	$sql = db_connect();

	if(!$stmt = $sql -> prepare('UPDATE profile SET completed=?, name=? WHERE uid=?')) {
		_log(FN, 'Change profile information');
	}

	$stmt->bind_param("iss", $__completed, $__name, $uid);
	$stmt->execute();
	$stmt->close();
	
?>