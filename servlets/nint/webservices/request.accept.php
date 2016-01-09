<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();
	
	if(!$stmt = $sql->prepare("SELECT id, sender FROM people_request WHERE id=? and receiver=?")) {
		_log(FN, 'Retrieve request on people');
	}
	
  	$stmt->bind_param("is", $__id, $uid);
	$stmt->execute();
	$stmt->store_result();
	
	if($stmt->num_rows == 0) {
		_error('INVALID_REQUEST');
	}
	
	$stmt->bind_result($id, $sender);
	$stmt->fetch();
	$stmt->close();

	$sql->query("INSERT INTO people_relation (uid, friend) VALUES ('$uid', '$sender')");
	$sql->query("INSERT INTO people_relation (uid, friend) VALUES ('$sender',   '$uid')");
	$sql->query("DELETE FROM people_request WHERE id=$id");
	
	$header = APNS_header('friends', array('user' => $uid));
	$message = sprintf("%s accepted your friend's request", getName($uid));
	_APNS($message, $header, $sender);

?>