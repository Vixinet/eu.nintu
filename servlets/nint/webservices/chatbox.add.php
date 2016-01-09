<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	// ToDo :
	// - Not allow to create a second single box with the same user

	$sql = db_connect();

	$users = explode(',', $__users);

	if(!in_array($uid, $users)) {
		$users[] = $uid;
	}

	if(count($users) == 2) {

		if(!$stmt = $sql -> prepare("SELECT c.id FROM chatbox c
								 INNER JOIN chatbox_people p ON c.id = p.chatbox AND p.uid = ?
									  WHERE (SELECT count(x.id) FROM chatbox_people x WHERE x.chatbox = c.id) = 2
										AND (SELECT count(x.id) FROM chatbox_people x WHERE x.chatbox = c.id and x.uid = ?) = 1")) {
			_log(FN, 'Retrieve chatbox id for 2 people');
		}

		$secondUser = $users[0] == $uid ? $users[1] : $users[0];

		$stmt->bind_param("ss", $uid, $secondUser);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($id);
		$total = $stmt->num_rows;

		if($total > 0) {
			$stmt->fetch();	
			$_response['chatbox'] = $id;
			_response(200);
		}

		$stmt->close();
	}

	$id = chatboxCreate();

	/*
	- ToDo :
	- Change the submitted title from the app TestChatbox
	
	if(isset($__title)) {
		chatboxRename($id, $__title);
	}
	*/

	$header = APNS_header('chatbox', array('chatbox' => $id));

	foreach($users as $user) {
		chatboxAddPeople($id, trim($user), $uid);
	}

	$_response['chatbox'] = (int) $id;
	
?>