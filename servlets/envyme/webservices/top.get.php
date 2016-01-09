<?php

	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php

	$sql = db_connect();

	$p = getPagination(
		   "SELECT count(*) FROM (SELECT COALESCE((SELECT SUM(action) FROM profile_activity a WHERE media=m1.id), 0) sum
		   	FROM profile_media m1
			INNER JOIN (select max(x.creation) AS creation_, x.uid AS uid_ FROM profile_media x GROUP BY x.uid) m2
			ON m1.creation = creation_ AND m1.uid = uid_
			ORDER BY sum  DESC LIMIT 0,100
			) xx",

		   "SELECT * FROM (
		   	SELECT m1.id, COALESCE(p.name, ''), p.creation c1, m1.uid, m1.creation c2, COALESCE(m1.text, ''), m1.image, m1.thumb,
		   		COALESCE((SELECT SUM(action) FROM profile_activity a WHERE media=m1.id), 0) as sum, 
		   		COALESCE((SELECT action FROM profile_activity a WHERE media=m1.id and owner='$uid' LIMIT 0,1),0)
			FROM profile_media m1
			INNER JOIN (select max(x.creation) AS creation_, x.uid AS uid_ FROM profile_media x GROUP BY x.uid) m2
			ON m1.creation = creation_ AND m1.uid = uid_
			LEFT JOIN profile p 
			ON p.uid = m1.uid
			ORDER BY sum DESC LIMIT 0,100
			) xx"
	);
	
	if(!$stmt = $sql->prepare($p['query'])) {
		_log(FN, 'Feed list retreive data');
	}
	
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($media_id, $profile_name, $profile_creation, $profile_uid, $media_creation, $media_text, $media_image, $media_thumb, $media_points, $own_action);
	
	$_response['top'] = array();
	while($stmt->fetch()) {
		$_response['top'][] = array(
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
	}
	
	$stmt->close();
?>