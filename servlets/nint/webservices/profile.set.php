<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	// ToDo
	// - delete the online field
	
	$sql = db_connect();
	
	if(isset($__interests)) {
		$interests = explode(",", $__interests);
		$condition = '';
		foreach($interests as $k => $interest) {
			$interest = (int) $interest;
			$interests[$k] = $interest;
			$condition .= " id=$interest or";
		}
		$condition = substr($condition, 0, -3);

		if(!$stmt = $sql -> prepare("SELECT count(id) FROM interest_secondary WHERE $condition")) {
			_log(FN, 'Check interests exist');
		}

		$stmt->execute();
		$stmt->bind_result($total);
		$stmt->fetch();

		if($total != count($interests)) {
			_log(FN, 'Interests list not valid', 400);
		}

		foreach($interests as $__interest) {
			include('profile.interest.toggle.php');
		}
	}

	if(!$stmt = $sql -> prepare("SELECT completed, online, name, title, description, hometown, relationship, age, age_min, age_max, gender, gender_display, position, apns FROM profile WHERE uid=?")) {
		_log(FN, 'Retrieve account settings');
	}
	
	$settings = array();
	
  	$stmt->bind_param("s", $uid);
	$stmt->execute();
	$stmt->bind_result(
		$profile['completed'],
		$profile['online'],
		$profile['name'],
		$profile['title'],
		$profile['description'],
		$profile['hometown'],
		$profile['relationship'],
		$profile['age'],
		$profile['age_min'],
		$profile['age_max'],
		$profile['gender'],
		$profile['gender_display'],
		$profile['position'],
		$profile['apns']
	);
	$stmt->fetch();
	$stmt->close();

	if(isset($__completed))			$profile['completed']		= $__completed;
	if(isset($__online))			$profile['online']		 	= $__online;
	if(isset($__name))				$profile['name']		 	= $__name;
	if(isset($__title))				$profile['title'] 			= $__title;
	if(isset($__description))		$profile['description'] 	= $__description;
	if(isset($__hometown))			$profile['hometown'] 		= $__hometown;
	if(isset($__relationship))		$profile['relationship'] 	= $__relationship;
	if(isset($__age))				$profile['age'] 			= $__age;
	if(isset($__age_min))			$profile['age_min'] 		= $__age_min;
	if(isset($__age_max))			$profile['age_max'] 		= $__age_max;
	if(isset($__gender))			$profile['gender']			= $__gender;
	if(isset($__gender_display))	$profile['gender_display'] 	= $__gender_display;
	if(isset($__position))			$profile['position'] 		= $__position;
	if(isset($__apns))				$profile['apns'] 			= $__apns;
	
	if(!$stmt = $sql -> prepare("UPDATE profile
									SET completed = ?,
										online = ?,
										name = ?,
										title = ?,
										description = ?,
										hometown = ?,
										relationship = ?,
										age = ?,
										age_min = ?,
										age_max = ?,
										gender = ?,
										gender_display = ?,
										position = ?,
										apns = ?
								  WHERE uid=?")) {
		_log(FN, 'Save profile information');
	}
	
	$stmt->bind_param(
		"iissssiiiiiisss",
		$profile['completed'],
		$profile['online'],
		$profile['name'],
		$profile['title'],
		$profile['description'],
		$profile['hometown'],
		$profile['relationship'],
		$profile['age'],
		$profile['age_min'],
		$profile['age_max'],
		$profile['gender'],
		$profile['gender_display'],
		$profile['position'],
		$profile['apns'],
	 	$uid
	 );

	$stmt->execute();
	$stmt->close();
	
?>