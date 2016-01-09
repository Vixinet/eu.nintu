<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();
	
	if(!chatboxMember($__chatbox, $uid)) {
		_error('CHATBOX_NOT_MEMBER');
	}
	
	if(!$stmt = $sql -> prepare("UPDATE chatbox_people SET leaved = unix_timestamp() WHERE chatbox=? and uid=?")) {
		_log(FN, 'Leave chatbox');
	}

	$stmt->bind_param("is", $__chatbox, $uid);
	$stmt->execute();
	
?>