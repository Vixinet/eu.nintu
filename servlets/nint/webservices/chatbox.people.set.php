<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();

	if(!$stmt = $sql -> prepare("UPDATE chatbox_people SET position=? WHERE uid=? and chatbox=?")) {
		_log(FN, 'Update chatbox member.');
	}

	$stmt->bind_param("ssi", $__position, $uid, $__chatbox);
	$stmt->execute();
	$stmt->close();

	if(!empty($__position)) {
		$header = APNS_header('chatbox', array('chatbox' => $__chatbox));
		$message = sprintf("%s shared a new position.", getName($uid));
		foreach(chatboxGetPeople($__chatbox) as $user_uid => $user) {
			if($user_uid != $uid) {
				_APNS($message, $header, $user_uid);
			}
		}
	}
?>