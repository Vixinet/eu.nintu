<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();

	if(!$stmt = $sql -> prepare("SELECT id FROM interest_secondary WHERE id=?")) {
		_log(FN, 'Check interest exists');
	}
	
  	$stmt->bind_param("i", $__interest);
	$stmt->execute();
	$stmt->store_result();
	if($stmt->num_rows == 0) {
		_error('INTEREST_UNKNOW');
	}
	$stmt->close();


	if(!$stmt = $sql -> prepare("SELECT interest FROM profile_interest WHERE uid=? and interest=?")) {
		_log(FN, 'Check profile interest settings');
	}

	$stmt->bind_param("si", $uid, $__interest);
	$stmt->execute();
	$stmt->store_result();
	$total = $stmt->num_rows;
	$stmt->close();

	if($total == 0) {
		if(!$stmt = $sql -> prepare("INSERT INTO profile_interest (uid, interest) VALUES (?, ?)")) {
			_log(FN, 'Add interest in profile');
		}
		$stmt->bind_param("si", $uid, $__interest);
		$stmt->execute();
		$stmt->close();
	} else {
		if(!$stmt = $sql -> prepare("DELETE FROM profile_interest WHERE uid = ? AND interest = ?")) {
			_log(FN, 'Delete interest in profile');
		}
		$stmt->bind_param("si", $uid, $__interest);
		$stmt->execute();
		$stmt->close();
	}
	


?>