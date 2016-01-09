<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();
	
	if(!chatboxMember($__chatbox, $uid)) {
		_error('CHATBOX_NOT_MEMBER');
	}
	
	chatboxLeave($__chatbox, $uid);
	
?>