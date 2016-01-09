<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();

	if(!$stmt = $sql->prepare("SELECT hash FROM token WHERE uid=?")) {
		_log(FN, 'Get user Hash & expire');
	}
	
	if(strpos($__token, ':') === false) {
		_log(FN, 'Token format');
	}

	list($uid, $token) = explode(':', $__token);
	
  	$stmt->bind_param("s", $uid);
	$stmt->execute();
	$stmt->bind_result($hash);
	
	while($stmt->fetch()) {
		if(validate_hash($token, $hash)) {
			$_response['valid'] = true;
			break;
		}
	}
	
	$stmt->close();
	
	if(!isset($_response['valid'])) {
		$_response['valid'] = false;
	}
	
?>