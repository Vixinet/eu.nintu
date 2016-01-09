<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();
	
	if(!is_email_valid($__email)) {
		_error('EMAIL_NOT_VALID');
	}
	
	if(!$stmt = $sql->prepare("SELECT u.uid, !isnull(l.id), !isnull(p.id), p.code, p.tries
								 FROM user u
							LEFT JOIN locked l
								   ON l.uid = u.uid
							LEFT JOIN password_retrieve p
								   ON p.uid = u.uid
								WHERE u.email=?")) {
		_log(FN, 'Get user Information');
	}
	
  	$stmt->bind_param("s", $__email);
	$stmt->execute();
	$stmt->store_result();
	$total = $stmt->num_rows;
	$stmt->bind_result($uid, $locked, $codeAlreadyExists, $code, $tries);
	$stmt->fetch();
	$stmt->close();
	
	if($codeAlreadyExists == 0) {
		_log(FN, 'Code dosent exist', 400);
	}
	
	if($locked == 1) {
		_error('ACCOUNT_LOCKED');
	}

	if($__code == $code) {
		$hash = create_hash($__password);
		$sql->query("UPDATE user SET hash='$hash' where uid='$uid'");
		$sql->query("DELETE FROM password_retrieve WHERE uid='$uid'");

		include_once('token.get.php');
	}

	$tries++;
	$sql->query("UPDATE password_retrieve SET tries=$tries where uid='$uid'");
	if($tries >= 3) {
		$sql->query("INSERT INTO locked (uid) VALUES ('$uid')");
		_error('ACCOUNT_LOCKED');
	} else {
		_error('WRONG_CODE');
	}
	
?>