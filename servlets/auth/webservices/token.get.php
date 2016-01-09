<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();
	
	if(!$stmt = $sql->prepare("SELECT uid, hash FROM user WHERE email=?")) {
		_log(FN, 'Get user UID & Hash');
	}
	
 	$stmt->bind_param("s", $__email);
	$stmt->execute();
	$stmt->bind_result($uid, $hash);
	$stmt->fetch();
	$stmt->close();
	
	if(!validate_hash($__password, $hash)) {
		_error("ACCESS_DENIED", 401);
	}
	
	$token = md5($__email . ':' . time()) ;
	$hash = create_hash($token);
	
	$sql->query("INSERT INTO token (uid, hash) VALUES ('$uid', '$hash')");
	
	$_response['token']  = $uid.':'.$token;
	
?>