<?php

	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();
	
	$profile = isset($__uid) ? $__uid : $uid;

	$p = getPagination(
		"SELECT count(id) FROM profile_media WHERE uid='$profile'",
		"SELECT id, image, thumb FROM profile_media WHERE uid=? ORDER BY creation"
	);

	if(!$stmt = $sql->prepare($p['query'])) {
		_log(FN, 'Media list retreive data');
	}
	
	$stmt->bind_param("s", $profile);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($id, $image, $thumb);
	
	$_response['media'] = array();
	
	while($stmt->fetch()) {
		$_response['media'][] = array(
			'id' => (int) $id,
			'image' => _n($image),
			'thumb' => _n($thumb),
			'is_default' => getDefaultPictureId($profile) != $id ? false : true
		);
	}
	
	$stmt->close();
	
?>