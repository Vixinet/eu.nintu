<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php

	$sql = db_connect();

	$p = getPagination(
		"SELECT count(c.id) FROM profile_media_comment c WHERE c.media=$__media",
		"SELECT c.creation, c.text, COALESCE(p.name, '') FROM profile_media_comment c LEFT JOIN profile p ON p.uid = c.owner WHERE c.media=? ORDER BY c.creation DESC"
	);

	if(!$stmt = $sql -> prepare($p['query'])) {
		_log(FN, 'Get comment list');
	}

	$stmt->bind_param("i", $__media);
	$stmt->execute();
	$stmt->bind_result($creation, $text, $name);

	$_response['comments'] = array();

	while($stmt->fetch()) {
		$_response['comments'][] = array(
			'creation' => getDateFromTimestamp($creation),
			'text' => $text,  
			'name' => $name
		);
	}

	$stmt->close();
	
?>