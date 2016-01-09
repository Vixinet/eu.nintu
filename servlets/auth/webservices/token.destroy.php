<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();

	if(!$stmt = $sql->prepare("SELECT tid, hash FROM token WHERE uid=?")) {
		_log(FN, 'Get token id & hashes');
	}
	
	if(strpos($__token, ':') === false) {
		_log(FN, 'Token format');
	}

	list($uid, $token) = explode(':', $__token);
	
  	$stmt->bind_param("s", $uid);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($tid, $hash);
	
	$token_deleted = false;

	while($stmt->fetch() and !$token_deleted) {
		if(validate_hash($token, $hash)) {
			$sql->query("DELETE FROM token WHERE tid=$tid");
			$token_deleted = true;
			break;
		}
	}

	if(!$token_deleted) {
		_error('INVALID_TOKEN');
	}
	
?>