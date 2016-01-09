<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();

	if(!$stmt = $sql -> prepare("DELETE FROM people_relation WHERE (uid = ? AND friend = ?) or (friend = ? AND uid = ?)")) {
		_log(FN, 'Delete friend');
	}
	
	$stmt->bind_param("ssss", $uid, $__uid, $uid, $__uid);
	$stmt->execute();
	$stmt->close();
	
?>