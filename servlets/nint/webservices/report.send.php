<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();

	if(!$stmt = $sql -> prepare("INSERT INTO report (uid, json, message) VALUES (?,?,?)")) {
		_log(FN, 'Insert feedback from user');
	}
	
	$stmt->bind_param("sss", $uid, $__json, $__report);
	$stmt->execute();
	$stmt->close();

	$message  = sprintf("App %s\n", APP_ID);
	$message .= sprintf("From %s <%s>\n\n", getName($uid), $uid);
	$message .= sprintf("Report\n%s\n\n", $__report);
	$message .= sprintf("JSON\n%s\n\n", $__json);

	mail(REPORT_RECEIVER, REPORT_TITLE, $message,  REPORT_SENDER);
	
?>