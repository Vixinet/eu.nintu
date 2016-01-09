<?php

	$db_connection = array(
		'host' => '173.194.109.87',
		'user' => 'unintu',
		'pass' => 'uU39248204',
		'name' => 'nintu_nint',
		'port' => 3306
	);

	if (_DEBUG_) echo 'servlet.config.nint.php - Step 1 : before require_auth()' . PHP_EOL;
	if(!_DEBUG_) require_auth();

	define('APNS_CERT_FILE', sprintf('configs/%s/apns-nint.pem', _ENV_));
	define('APNS_CERT_PASS', 'mier1234');
	
	define('MQTT_CA', sprintf('/var/www/configs/%s/mqtt-nint.crt', _ENV_));
	define('MQTT_HOST', 'ws.a2bh.com');
	define('MQTT_PORT', 8883);
	
	define('APP_ID', "[DEV][app-ios-nintu]");
	
	define("FEEDBACK_SENDER", "From: Nintu No reply <no-reply@nintu.eu>");
	define("FEEDBACK_RECEIVER", "feedback@nintu.eu");
	define("FEEDBACK_TITLE", "[DEV] New feedback");

	define("REPORT_SENDER", "From: Nintu No reply <no-reply@nintu.eu>");
	define("REPORT_RECEIVER", "feedback@nintu.eu");
	define("REPORT_TITLE", "[DEV] New report");
?>