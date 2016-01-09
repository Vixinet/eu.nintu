<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php

	$sql = db_connect();

	if(!$stmt = $sql -> prepare("SELECT count(id) FROM profile_activity WHERE media=? and owner=?")) {
		_log(FN, 'Add activity to media');
	}
	$stmt->bind_param("is", $__media, $uid);
	$stmt->execute();
	$stmt->bind_result($total);
	$stmt->fetch();
	$stmt->close();

	if($total == 0) {
		if(!$stmt = $sql -> prepare("INSERT INTO profile_activity (media, owner, action) VALUES (?,?,?)")) {
			_log(FN, 'Add activity to media');
		}
		$stmt->bind_param("isi", $__media, $uid, $__action);
		$stmt->execute();
		$stmt->close();
	} else {
		if(!$stmt = $sql -> prepare("UPDATE profile_activity SET action=? WHERE media=? AND owner=?")) {
			_log(FN, 'Update activity');
		}
		$stmt->bind_param("iis", $__action, $__media, $uid);
		$stmt->execute();
		$stmt->close();
	}
	
?>