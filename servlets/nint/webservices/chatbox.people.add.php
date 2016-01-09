<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();

	if(!chatboxMember($__chatbox, $uid)) {
		_error('CHATBOX_NOT_MEMBER');
	}
	
	$users = explode(',', $__users);
	$header = APNS_header('chatbox', array('chatbox' => $__chatbox));
	$message = sprintf("%s created a new talkbox", getName($uid));

	foreach($users as $k => $user) {
		$user = trim($user);
		if(chatboxAddPeople($__chatbox, $user, $uid)) {
			_APNS($message, $header, $user);
		} else {
			unset($users[$k]);
		}
	}

	if(count($users) > 0) {
		$message = sprintf("%s added %d new user%s to a talkbox.", getName($uid), count($users), count($users) > 1 ? 's' : '');
		foreach(chatboxGetPeople($__chatbox) as $user_uid => $user) {
			if(!in_array($user_uid, $users) and $user_uid != $uid) {
				_APNS($message, $header, $user_uid);
			}
		}
	}
?>