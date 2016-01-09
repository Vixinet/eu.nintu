<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();

	$visitor = isset($__uid);

	if($visitor) {
		$profile = $__uid;
		$params = "p.online, p.name, p.title, p.description, p.hometown, p.relationship, p.age, p.gender, m.image, m.thumb, 
					(SELECT count(id) FROM people_relation WHERE uid='$uid' and friend=p.uid), 
					(SELECT count(id) FROM people_request WHERE (sender='$uid' and receiver=p.uid) or (sender=p.uid and receiver='$uid'))";
	} else {
		$profile = $uid;
		$params = "p.online, p.name, p.title, p.description, p.hometown, p.relationship, p.age, p.gender, m.image, m.thumb, p.completed, p.age_min, p.age_max, p.gender_display, p.position";
	}

	if(!$stmt = $sql -> prepare("SELECT $params FROM profile p  LEFT JOIN profile_media m ON m.id = p.picture WHERE p.uid=?")) {
		_log(FN, 'Retrieve profile settings');
	}
	
  	$stmt->bind_param("s", $profile);
	$stmt->execute();
	$stmt->store_result();

	if($stmt->num_rows == 1) {
		if($visitor) {
			$stmt->bind_result($online, $name, $title, $description, $hometown, $relationship, $age, $gender, $image, $thumb, $is_friend, $is_requested);
		} else {
			$stmt->bind_result($online, $name, $title, $description, $hometown, $relationship, $age, $gender, $image, $thumb, $completed, $age_min, $age_max, $gender_display, $position);
		}

		$stmt->fetch();
		
		$_response['is_visitor'] = (bool) $visitor;
		$_response['online'] = (bool) $online;
		$_response['name'] = _n($name);
		$_response['title'] = _n($title);
		$_response['description'] = _n($description);
		$_response['hometown'] = _n($hometown);
		$_response['relationship'] = (int) $relationship;
		$_response['age'] = (int) $age;
		$_response['gender'] = (int) $gender;
		$_response['image'] = _n($image);
		$_response['thumb'] = _n($thumb);

		if($visitor) {
			$_response['is_friend'] = (bool) $is_friend;
			$_response['is_requested'] = (bool) $is_requested;
		} else {
			$_response['completed'] = (bool) $completed;
			$_response['age_min'] = (int) $age_min;
			$_response['age_max'] = (int) $age_max;
			$_response['gender_display'] = (int) $gender_display;
			$_response['position'] = _n($position);
		}

	} else {
		_error('PROFILE_DOESNT_EXIST');
	}

	$stmt->close();

	$_response['interests'] = array();

	if(!$stmt = $sql -> prepare("SELECT interest FROM profile_interest WHERE uid=?")) {
		_log(FN, 'Retrieve interests settings');
	}
	
	$stmt->bind_param("s", $profile);
	$stmt->execute();
	$stmt->bind_result($interest);
	while($stmt->fetch()) {
		$_response['interests'][] = (int) $interest;
	}
	$stmt->close();
?>