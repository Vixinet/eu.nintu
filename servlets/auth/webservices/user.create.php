<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	if(!is_email_valid($__email)) {
		_error('EMAIL_NOT_VALID');
	}
	
	$__password = trim($__password);
	
	if(strlen($__password) < 8) {
		_error('PASSWORD_LENGHT');
	}
	
	$sql = db_connect();
	
	if(!$stmt = $sql -> prepare("SELECT uid FROM user WHERE email=?")) {
		_log(FN, 'Retrieve UID');
	}
	
  	$stmt->bind_param("s", $__email);
	$stmt->execute();
	$stmt->store_result();
	$total = $stmt->num_rows;
	$stmt->close();
		
	if($total > 0) {
		include_once('token.get.php');
		_response(200);
	}
	
	if(!$stmt = $sql -> prepare("INSERT INTO user (uid, email, hash) VALUES (?,?,?)")) {
		_log(FN, 'Insert new user');
	}
	
	$uid  = generateUID();
	$hash = create_hash($__password);
	$stmt->bind_param("sss", $uid, $__email, $hash);
	$stmt->execute();
	$stmt->close();
	
	// Création des dossiers binaire de l'utilisateur
	if(!mkdir(sprintf("binaries/media/envyme/%s", $uid), 0777)) {
		_log(FN, 'Cannot create envyme user media folder');
	}
	if(!mkdir(sprintf("binaries/media/nint/%s", $uid), 0777)) {
		_log(FN, 'Cannot create nintu user media folder');
	}

	include_once('token.get.php');
	
?>