<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php

	$sql = db_connect();

	$__action = -1;
	
	include('media.vote.php');
	
?>