<?php

	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php

	$sql = db_connect();

	$condition = isset($__interest) 	? "= $__interest"
										: "IN (SELECT ui.interest FROM profile_interest ui WHERE ui.uid = '$uid')";
		
	if(!$stmt = $sql->prepare("SELECT age_min, age_max, gender_display, position
								 FROM profile
								WHERE uid = ?")) {
		_log(FN, 'User profile info retreive data');
	}
	
	$stmt->bind_param("s", $uid);
	$stmt->execute();
	$stmt->bind_result($ageMin, $ageMax, $genderDisplay, $position);
	$stmt->fetch();
	$stmt->close();
	
	if($ageMin === null or $ageMax === null or $genderDisplay === null or $position === null) {
		_error('PROFILE_NOT_COMPLETED');
	}

	list($lat, $lon) = explode(',', $position);

	$p = getPagination(
		   "SELECT count(DISTINCT resultProfile.uid)
			  FROM profile resultProfile
		 LEFT JOIN profile_media resultMedia 
		 		ON resultProfile.picture = resultMedia.id
		 LEFT JOIN profile_interest resultInterests 
		 		ON resultInterests.uid = resultProfile.uid
			 WHERE resultProfile.uid != '$uid'
			   AND resultProfile.completed = 1
			   AND (resultProfile.gender & $genderDisplay) > 0 
			   AND resultProfile.age BETWEEN $ageMin AND $ageMax
			   AND resultInterests.interest $condition",

  "SELECT DISTINCT	resultProfile.uid, 
					resultProfile.name,
					resultProfile.title,
					resultProfile.hometown,
					resultProfile.relationship,
					resultProfile.description,
					resultProfile.gender,  
					resultProfile.age, 
					resultMedia.image,
					resultMedia.thumb,
					( acos(   cos(radians(?)) 
							* cos(radians(SUBSTRING_INDEX(resultProfile.position, ',', 1))) 
							* cos(radians(SUBSTRING_INDEX(resultProfile.position, ',', -1))-radians(?)) 
							+ sin(radians(?))
							* sin(radians(SUBSTRING_INDEX(resultProfile.position, ',', 1)))
						) * 6371
					) distance
			  FROM profile resultProfile
		 LEFT JOIN profile_media resultMedia 
		 		ON resultProfile.picture = resultMedia.id
		 LEFT JOIN profile_interest resultInterests 
		 		ON resultInterests.uid = resultProfile.uid
			 WHERE resultProfile.uid != '$uid'
			   AND resultProfile.completed = 1
			   AND (resultProfile.gender & ?) > 0 
			   AND resultProfile.age BETWEEN ? AND ?
			   AND resultInterests.interest $condition
		  ORDER BY distance"
	);
	
	if(!$stmt = $sql->prepare($p['query'])) {
		_log(FN, 'Matches list retreive data');
	}
	
	$stmt->bind_param("dddiii", $lat, $lon, $lat, $genderDisplay, $ageMin, $ageMax);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($resultUid, $resultName, $resultTitle, $resultHometown, $resultRelationship, $resultDescription, $resultGender, $resultAge, $resultImage, $resultThumb, $resultDistance);
	
	$_response['results'] = array();
	
	while($stmt->fetch()) {
		if(!isset($__interest)) {
			$interests = array();

			if(!$stmt2 = $sql->prepare("SELECT ri.interest 
										  FROM profile_interest ri 
										 WHERE ri.uid = ? 
										   AND ri.interest IN (SELECT ui.interest FROM profile_interest ui WHERE ui.uid=?)")) {
				_log(FN, 'Interest list retreive data');
			}
			$stmt2->bind_param("ss", $resultUid, $uid);
			$stmt2->execute();
			$stmt2->bind_result($interest);
			while($stmt2->fetch()) {
				$interests[] = $interest;
			}
			$stmt2->close();
		} else {
			$interests = (int) $__interest;
		}

		$_response['results'][] = array(
			'uid' => _n($resultUid),
			'name' => _n($resultName), 
			'title' => _n($resultTitle),
			'description' => _n($resultDescription),
			'relationship' => (int) $resultRelationship,
			'hometown' => _n($resultHometown),
			'gender' => (int) $resultGender, 
			'age' => (int) $resultAge,
			'image' => _n($resultImage),
			'thumb' => _n($resultThumb),
			'distance' => (float) $resultDistance,
			'interests' => $interests
		);
	}
	
	$stmt->close();
 
?>