<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();
	
	if(!chatboxMember($__chatbox, $uid)) {
		_error('CHATBOX_NOT_MEMBER');
	}
	
	if(!$stmt = $sql -> prepare("UPDATE chatbox_people SET clear = unix_timestamp() WHERE uid=? and chatbox=?")) {
		_log(FN, 'Check users exists');
	}

	$stmt->bind_param("si", $uid, $__chatbox);
	$stmt->execute();
	$stmt->close();
	
?>