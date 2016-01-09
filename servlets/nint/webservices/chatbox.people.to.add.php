<?php

	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();
	
	$p = getPagination(
		"SELECT count(id) 
		   FROM people_relation r
		  WHERE r.uid='$uid'
		    AND r.friend not in (SELECT cp.uid FROM chatbox_people cp WHERE cp.chatbox='$__chatbox' and leaved is null)",

		"SELECT p.uid, p.name, m.image, m.thumb 
		   FROM people_relation r 
	  LEFT JOIN profile p 
			 ON r.friend = p.uid
	  LEFT JOIN profile_media m 
			 ON p.picture = m.id 
		  WHERE r.uid=?
		    AND r.friend not in (SELECT cp.uid FROM chatbox_people cp WHERE cp.chatbox=? and leaved is null)
	   ORDER BY p.name"
	);
	
	if(!$stmt = $sql->prepare($p['query'])) {
		_log(FN, 'Friends not in chatbox');
	}
	
	$stmt->bind_param("si", $uid, $__chatbox);
	$stmt->execute();
	$stmt->bind_result($resultUid, $resultName, $resultImage, $resultThumb);
	
	// ToDo :
	// - Create function for person array

	$_response['people'] = array();
	
	while($stmt->fetch()) {
		$_response['people'][] = array(
			'uid' => _n($resultUid),
			'name' => _n($resultName),
			'image' => _n($resultImage),
			'thumb' => _n($resultThumb)
		);
	}
	
	$stmt->close();
	
?>