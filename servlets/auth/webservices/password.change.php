<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();
	
	if(!$stmt = $sql->prepare("SELECT uid, hash FROM user WHERE email=?")) {
		_log(FN, 'Get user Hash');
	}
	
 	$stmt->bind_param("s", $__email);
	$stmt->execute();
	$stmt->bind_result($uid, $hash);
	$stmt->fetch();
	$stmt->close();
	
	if(!validate_hash($__password_old, $hash)) {
		_error("BAD_PASSWORD", 401);
	}
	
	$hash = create_hash($__password_new);
	$sql->query("UPDATE user SET hash='$hash' WHERE uid='$uid'");
	
?>