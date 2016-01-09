<?php

	define("PBKDF2_HASH_ALGORITHM", "sha256");
	define("PBKDF2_ITERATIONS", 1000);
	define("PBKDF2_SALT_BYTE_SIZE", 24);
	define("PBKDF2_HASH_BYTE_SIZE", 24);
	define("HASH_SECTIONS", 4);
	define("HASH_ALGORITHM_INDEX", 0);
	define("HASH_ITERATION_INDEX", 1);
	define("HASH_SALT_INDEX", 2);
	define("HASH_PBKDF2_INDEX", 3);

	define('LOG_DIRECTORY', 'log/');
	define('LOG_FILE', 'log-'.date('Ymd').'.log');

	define('WS_HOST', 'https://ws.op3m.com/');
	define('BIN_HOST', 'https://ws.op3m.com');

	define('MYSQL_DATE_TIME_FORMAT', 'Y-m-d H:i:s');

	define('APNS_GATEWAY', 	 'ssl://gateway.push.apple.com:2195');
	
?>