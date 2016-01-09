<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();

	if(!chatboxMember($__chatbox, $uid)) {
		_error('CHATBOX_NOT_MEMBER');
	}

	$id = chatboxAddMessage($__chatbox, $__message, $uid);

	$header = APNS_header('chatbox', array('chatbox' => $__chatbox, 'message' => $id));
	$message = sprintf("%s sent a new message.", getName($uid));
	$mqtt_message = json_encode(chatboxGetMessageFromId($id));

	foreach(chatboxGetPeople($__chatbox) as $user_uid => $user) {
		if($user['connected']) {
			// _log2('', sprintf("%s -> %s - mqtt topic %s", $uid, $user_uid, chatboxGetMQTTChannel($__chatbox, $user_uid)));
			mqtt_pub(chatboxGetMQTTChannel($__chatbox, $user_uid), $mqtt_message);
		} elseif($user_uid != $uid) {
			// _log2('', sprintf("%s -> %s - apns", $uid, $user_uid));
			_APNS($message, $header, $user_uid);
		}
	}

?>	