<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();
	
	if(!chatboxMember($__chatbox, $uid)) {
		_error('CHATBOX_NOT_MEMBER');
	}
	
	chatboxJoin($__chatbox, $uid);

	$_response['title'] = chatboxGetTitle($__chatbox);
	$_response['muted'] = chatboxGetMuted($__chatbox, $uid);
	$_response['mqtt']  = chatboxGetMQTTChannel($__chatbox, $uid);
	$_response['next_ts'] = time();

	if(isset($__from)) {
		$_response['messages'] = chatboxGetMessages($__chatbox, $uid, $__from);
	} else {
		$_response['messages'] = chatboxGetMessages($__chatbox, $uid);
	}

	if(!isset($__page) or $__page == 1) {
		$_response['people'] = chatboxGetPeople($__chatbox);
	}
	
?>