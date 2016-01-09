<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();
	
	if(!$stmt = $sql->prepare("SELECT id, sender, receiver, creation FROM people_request WHERE id=? and receiver=?")) {
		_log(FN, 'Retrieve request on people');
	}
	
  	$stmt->bind_param("is", $__id, $uid);
	$stmt->execute();
	$stmt->store_result();

	if($stmt->num_rows == 0) {
		_error('INVALID_REQUEST');
	}

	$stmt->bind_result($id, $sender, $receiver, $time);
	$stmt->fetch();
	$stmt->close();

	// log the deny on a denied table
	if(!$stmt = $sql->prepare("INSERT INTO people_request_denied (sender, receiver, time) VALUES (?,?,?)")) {
		_log(FN, 'Request denied');
	}
	$stmt->bind_param("ssi", $sender, $receiver, $time);
	$stmt->execute();
	$stmt->close();

	$sql->query("DELETE FROM people_request WHERE id=$id");
	
?>