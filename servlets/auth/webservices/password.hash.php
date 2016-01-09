<?php

	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
		
	$_response['password'] = $__password;
	$_response['hash'] = create_hash($__password);
	
?>

