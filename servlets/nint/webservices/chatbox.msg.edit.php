<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();

	if(!chatboxMessageOwner($__id, $uid)) {
		_error('CHATBOX_MESSAGE_NOT_OWNER');
	}

	if(!$stmt = $sql -> prepare("UPDATE chatbox_message SET edited = unix_timestamp(), message = ? WHERE id=?")) {
		_log(FN, 'Edit message');
	}

	$stmt->bind_param("si", $__message, $__id);
	$stmt->execute();
	
?>	