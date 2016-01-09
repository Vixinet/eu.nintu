<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();
	
	if(!is_email_valid($__email)) {
		_error('EMAIL_NOT_VALID');
	}
	
	if(!$stmt = $sql->prepare("SELECT u.uid, !isnull(l.id), !isnull(p.id)
								 FROM user u
							LEFT JOIN locked l
								   ON l.uid = u.uid
							LEFT JOIN password_retrieve p
								   ON p.uid = u.uid
								WHERE u.email=?")) {
		_log(FN, 'Get user\'s information');
	}
	
  	$stmt->bind_param("s", $__email);
	$stmt->execute();
	$stmt->store_result();
	$total = $stmt->num_rows;
	$stmt->bind_result($uid, $locked, $codeAlreadyExists);
	$stmt->fetch();
	$stmt->close();

	if($total == 0) {
		_error('ACCOUNT_NOT_FOUND');
	}
	
	
	if($locked == 1) {
		_error('ACCOUNT_LOCKED');
	}
	
	$code = mt_rand(100000, 999999);
	
	if($codeAlreadyExists == 1) {
		$sql->query("UPDATE password_retrieve SET code=$code where uid='$uid'");
	} else {
		$sql->query("INSERT INTO password_retrieve (uid, code) VALUES ('$uid', $code)");
	}
	
	mail($__email, "Nintu password retrieve", "Retrieve code: $code",  "From: Nintu No reply <no-reply@nintu.eu>");
	
	// ToDo :
	// - Implement expiring codes 
	// - Add a cron to delete the expired codes
?>