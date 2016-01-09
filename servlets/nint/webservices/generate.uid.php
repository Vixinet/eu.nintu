<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	$sql = db_connect();

	_log2(FN, "Hello $__firstname you are $__age years old");
	
	if(isset($__lastname)) {
		_log2(FN, "that's a cool lastname: $__lastname");
	}

	$_response['generated_uid'] = '12345678';
	$_response['generated_uid2'] = generateUID(false);

	$_response['name'] = $__firstname;

?>