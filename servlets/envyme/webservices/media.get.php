<?php

	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php

	$sql = db_connect();
	
	
	if(!isset($__media)) {
		$req = $sql->query("SELECT id FROM profile_media WHERE uid='$uid' order by creation desc LIMIT 0,1");
		$res = $req->fetch_array();
		$__media = $res['id'];
	}

	if(!$stmt = $sql->prepare("SELECT p.completed, m1.id, COALESCE(p.name, ''), p.creation, m1.uid, m1.creation, COALESCE(m1.text, ''), m1.image, m1.thumb,
			(SELECT COALESCE(SUM(action), 0) FROM profile_activity WHERE media=m1.id), 
			COALESCE((SELECT action FROM profile_activity WHERE media=m1.id and owner=? LIMIT 0,1), 0)
		FROM profile_media m1
		LEFT JOIN profile p ON p.uid = m1.uid
		WHERE id=?")) {
		_log(FN, 'Get media info');
	}

	$stmt->bind_param('si', $uid, $__media);
	$stmt->execute();
	$stmt->store_result();

	if($stmt->num_rows == 1) {
		$stmt->bind_result($profile_completed, $media_id, $profile_name, $profile_creation, $profile_uid, $media_creation, $media_text, $media_image, $media_thumb, $media_points, $own_action);
		$stmt->fetch();

		$_response['media'] = array(
			'profile_completed' => $profile_completed,
			'profile_uid' => $profile_uid, 
			'profile_name' => $profile_name,
			'profile_creation' => getDateFromTimestamp($profile_creation),
			'media_creation' => getDateFromTimestamp($media_creation),
			'media_id' => $media_id,  
			'media_text' => $media_text, 
			'media_image' => $media_image, 
			'media_thumb' => $media_thumb, 
			'media_points' => (int) $media_points, 
			'own_action' => $own_action
		);

	} else {
		// $_response['media'] = null;
	}
	
	$stmt->close();
?>