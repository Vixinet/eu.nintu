<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();

	if(!chatboxMessageOwner($__id, $uid)) {
		_error('CHATBOX_MESSAGE_NOT_OWNER');
	}
	
	if(!$stmt = $sql -> prepare("UPDATE chatbox_message SET deleted = unix_timestamp() WHERE id=?")) {
		_log(FN, 'Delete message');
	}

	$stmt->bind_param("i", $__id);
	$stmt->execute();
	$stmt->close();
	
?>	