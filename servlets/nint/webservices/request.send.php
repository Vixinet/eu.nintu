<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();
	
	$sender = $uid;
	$receiver = $__uid;
	
	if($sender == $receiver) {
		_log(FN, 'Sender and receiver are the same person', 400);
	}
	
	if(!$stmt = $sql -> prepare("SELECT count(uid) FROM profile WHERE uid=?")) {
		_log(FN, 'Control user exist');
	}
  	$stmt->bind_param("s", $receiver);
	$stmt->execute();
	$stmt->bind_result($total);
	$stmt->fetch();
	$stmt->close();

	if($total == 0) {
		_error('INVALID_PROFILE');
	}

	if(!$stmt = $sql -> prepare("SELECT count(id) FROM people_relation WHERE (uid=? AND friend=?) or (friend=? AND uid=?)")) {
		_log(FN, 'Retrieve request on people');
	}
  	$stmt->bind_param("ssss", $sender, $receiver, $sender, $receiver);
	$stmt->execute();
	$stmt->bind_result($total);
	$stmt->fetch();
	$stmt->close();
	if($total > 0) {
		_error('ALREADY_FRIENDS');
	}

	if(!$stmt = $sql -> prepare("SELECT count(id) FROM people_request WHERE sender=? and receiver=?")) {
		_log(FN, 'Retrieve request on people');
	}
  	$stmt->bind_param("ss", $sender, $receiver);
	$stmt->execute();
	$stmt->bind_result($total);
	$stmt->fetch();
	$stmt->close();
	if($total > 0) {
		_error('ALREADY_REQUESTED');
	}

	if(!$stmt = $sql -> prepare("INSERT INTO people_request (sender, receiver) VALUES (?, ?)")) {
		_log(FN, 'New request');
	}
	$stmt->bind_param("ss", $sender, $receiver);
	$stmt->execute();
	$stmt->close();

	$header = APNS_header('requests', array('user' => $uid));
	$message = sprintf("%s send you a friend's request", getName($uid));
	_APNS($message, $header, $receiver);
	
?>