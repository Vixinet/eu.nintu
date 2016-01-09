<?php
	
	if(!defined('FN')) define('FN', basename(__FILE__, '.php'));
	
	// This file'll be include by service.php
	
	$_response['interests'] = array(
		'main' => array(), 
		'secondary' => array()
	);

	$sql = db_connect();
	
	if(!$stmt = $sql -> prepare("SELECT id, label, temp FROM interest_main ORDER BY label")) {
		_log(FN, 'Retrieve interest main categories');
	}

	$stmt->execute();
	$stmt->bind_result($id, $label, $temp);
	while($stmt->fetch()) {
		$_response['interests']['main'][] = array(
			'id' => (int) $id,
			'label' => _n($label),
			'temp' => (bool) $temp
		);
	}
	$stmt->close();


	if(!$stmt = $sql -> prepare("SELECT id, parent, label, temp FROM interest_secondary ORDER BY parent, label")) {
		_log(FN, 'Retrieve interest secondary categories');
	}

	$stmt->execute();
	$stmt->bind_result($id, $parent, $label, $temp);
	while($stmt->fetch()) {
		$_response['interests']['secondary'][] = array(
			'id' => (int) $id,
			'parent' => (int) $parent,
			'label' => _n($label),
			'temp' => (bool) $temp
		);
	}
	$stmt->close();
	
?>