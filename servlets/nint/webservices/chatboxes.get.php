<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$sql = db_connect();

	$p = getPagination(	"SELECT count(c.id)
						   FROM chatbox c 
					 INNER JOIN chatbox_people me
							 ON me.uid = '$uid' and me.chatbox=c.id
					 INNER JOIN chatbox_message m 
							 ON m.id = (SELECT id FROM chatbox_message WHERE chatbox=c.id ORDER BY creation DESC LIMIT 0,1)
						  WHERE me.leaved IS null
						  ORDER BY m.creation DESC",

						"SELECT c.id as cb_id, 
								c.name cb_name, 
								m.id msg_id, 
								if(m.creation > me.last_connection, 1, 0)
						   FROM chatbox c 
					 INNER JOIN chatbox_people me
							 ON me.uid = ? and me.chatbox=c.id
					 INNER JOIN chatbox_message m 
							 ON m.id = (SELECT id FROM chatbox_message WHERE chatbox=c.id ORDER BY creation DESC LIMIT 0,1)
						  WHERE me.leaved IS null
						  ORDER BY m.creation DESC");

	if(!$stmt = $sql -> prepare($p['query'])) {
		_log(FN, 'Retrieve chatboxes list');
	}

	$stmt->bind_param("s", $uid);
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($id, $title, $lastId, $new_message);
	
	$_response['chatboxes'] = array();

	while($stmt->fetch()) {

		if(empty($title) or $title === null) {
			$title = chatboxGetPersonsTitle($id);
		}

		$_response['chatboxes'][] = array(
			'id' => (int) $id,
			'title' => $title,
			'new_message' => (bool) $new_message,
			'last' => chatboxGetMessageFromId($lastId)
		);
	}
	
?>