<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();

	if(!$stmt = $sql -> prepare("INSERT INTO feedback (uid, feedback) VALUES (?,?)")) {
		_log(FN, 'Insert feedback from user');
	}
	
	$stmt->bind_param("ss", $uid, $__feedback);
	$stmt->execute();
	$stmt->close();

	$message  = sprintf("App %s\n", APP_ID);
	$message .= sprintf("From %s <%s>\n", getName($uid), $uid);
	$message .= sprintf("Feedback\n%s", $__feedback);

	mail(FEEDBACK_RECEIVER, FEEDBACK_TITLE, $message,  FEEDBACK_SENDER, '-F "Nintu No reply" -f "no-reply@nintu.eu"');
	
?>