<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();

	$p = getPagination(
		"SELECT count(id) FROM people_request WHERE receiver='$uid'",
		"SELECT r.id, r.sender, p.name, p.title, m.image, m.thumb, 
				(SELECT count(i.interest) FROM profile_interest i WHERE i.uid=r.sender and i.interest in (SELECT i2.interest FROM profile_interest i2 WHERE i2.uid='$uid')) as ci 
		   FROM people_request r 
	  LEFT JOIN profile p 
	  		 ON r.sender = p.uid  
	  LEFT JOIN profile_media m 
	  		 ON p.picture = m.id  
	  	   WHERE receiver='$uid'
	  	ORDER BY ci DESC"
	);

	if(!$stmt = $sql->prepare($p['query'])) {
		_log(FN, 'Retrieve request on people');
	}
	
	$stmt->execute();
	$stmt->bind_result($id, $sender, $name, $title, $image, $thumb, $commonInterests);

	$_response['people'] = array();
	
	while($stmt->fetch()) {
		$_response['people'][] = array(
			'id' => (int) $id,
			'sender' => _n($sender),
			'name' => _n($name),
			'title' => _n($title),
			'image' => _n($image),
			'thumb' => _n($thumb),
			'interests' => (int) $commonInterests
		);
	}

	$stmt->close();
	
?>