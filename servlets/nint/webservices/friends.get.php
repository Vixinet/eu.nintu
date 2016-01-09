<?php

	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();
	
	$p = getPagination(
		"SELECT count(id) FROM people_relation WHERE uid='$uid'",
		"SELECT p.uid, p.name, m.image, m.thumb FROM people_relation r LEFT JOIN profile p ON p.uid = r.friend LEFT JOIN profile_media m 
		 		ON p.picture = m.id WHERE r.uid='$uid' ORDER BY p.name"
	);
	
	if(!$stmt = $sql->prepare($p['query'])) {
		_log(FN, 'My People list retreive data');
	}
	
	$stmt->execute();
	$stmt->bind_result($resultUid, $resultName, $resultImage, $resultThumb);
	
	$_response['people'] = array();
	
	while($stmt->fetch()) {
		$_response['people'][] = array(
			'uid'=> $resultUid,
			'name' => _n($resultName),
			'image' => _n($resultImage),
			'thumb' => _n($resultThumb)
		);
	}
	
	$stmt->close();
	
?>