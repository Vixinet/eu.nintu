<?php

	function mqtt_pub($topic, $payload) {
		shell_exec(sprintf('mosquitto_pub --cafile %s -h %s -p %s -t "%s" --tls-version tlsv1.2 --insecure -m "%s"', MQTT_CA, MQTT_HOST, MQTT_PORT, $topic, addslashes($payload)));
	}

	function _n($text) {
		return $text == null ? '' : $text;
	}
	
	function chatboxLeave($chatbox, $user) {

		global $sql;

		if(!$stmt = $sql->prepare("UPDATE chatbox_people SET connected=0, last_connection=unix_timestamp() WHERE chatbox=? AND uid=?")) {
			_log(FN, 'User leave chatbox');
		}

		$stmt->bind_param("is", $chatbox, $user);
		$stmt->execute();
		$stmt->close();

	}

	function chatboxJoin($chatbox, $user) {

		global $sql;

		if(!$stmt = $sql->prepare("UPDATE chatbox_people SET connected=1, last_connection=unix_timestamp() WHERE chatbox=? AND uid=?")) {
			_log(FN, 'User join chatbox');
		}

		$stmt->bind_param("is", $chatbox, $user);
		$stmt->execute();
		$stmt->close();

	}

	function chatboxAddMessage($chatbox, $message, $owner) {
		
		global $sql;

		if(!$stmt = $sql->prepare("INSERT INTO chatbox_message (chatbox, uid, message) VALUES (?, ?, ?)")) {
			_log(FN, 'Insert message');
		}

		$stmt->bind_param("iss", $chatbox, $owner, $message);
		$stmt->execute();
		$id = $stmt->insert_id;
		$stmt->close();

		return (int) $id;
	}

	function chatboxGetMessage($msg_time, $msg_owner, $msg_text, $msg_edited, $msg_deleted) {
		return array(
				'owner'        => _n($msg_owner),
				'message'      => $msg_deleted != null ? '' : $msg_text,
				'creation'     => $msg_time    != null ? getDateFromTimestamp($msg_time) : '',
				'edited'       => $msg_edited  != null,
				'edited_time'  => $msg_edited  != null ? getDateFromTimestamp($msg_edited) : '',
				'deleted'      => $msg_deleted != null,
				'deleted_time' => $msg_deleted != null ? getDateFromTimestamp($msg_deleted) : ''
		);
	}

	function chatboxGetMessageFromId($msg_id) {

		global $sql;

		if(!$stmt = $sql -> prepare("SELECT m.creation, m.uid, p.name, media.image, media.thumb, m.message, m.edited, m.deleted
									   FROM chatbox_message m
								  LEFT JOIN profile p ON p.uid = m.uid
								  LEFT JOIN profile_media media ON media.id = p.picture
									  WHERE m.id = ?")) {
			_log(FN, 'Retrieve message from id');
		}

		$stmt->bind_param("i", $msg_id);
		$stmt->execute();
		$stmt->bind_result($msg_time, $msg_owner, $msg_owner_name, $msg_owner_image, $msg_owner_thumb, $msg_text, $msg_edited, $msg_deleted);
		$stmt->fetch();
		$stmt->close();

		return chatboxGetMessageWithOwner($msg_time, $msg_owner, $msg_text, $msg_edited, $msg_deleted, $msg_owner_name, $msg_owner_image, $msg_owner_thumb);
	}

	function chatboxGetMessageWithOwner($msg_time, $msg_owner, $msg_text, $msg_edited, $msg_deleted, $msg_owner_name, $msg_owner_image, $msg_owner_thumb) {
		
		$msg = chatboxGetMessage($msg_time, $msg_owner, $msg_text, $msg_edited, $msg_deleted);

		$msg['owner_name']  = _n($msg_owner_name);
		$msg['owner_image'] = _n($msg_owner_image);
		$msg['owner_thumb'] = _n($msg_owner_thumb);

		return $msg;
	}

	function chatboxGetMessages($chatbox, $owner, $from = null) {

		global $sql;

		$from_condition = $from === null ? '' : " AND m.creation >= $from ";

		$p = getPagination(
			"SELECT count(m.id)
			   FROM chatbox_message m
		  LEFT JOIN chatbox_people p ON p.chatbox = m.chatbox AND p.uid = '$owner'
			  WHERE m.chatbox = '$chatbox' 
			    AND (p.clear is null or m.creation >= p.clear)
			    	$from_condition",

			"SELECT m.id, m.creation, m.uid, m.message, m.edited, m.deleted
			   FROM chatbox_message m
		  LEFT JOIN profile p ON p.uid = m.uid
		  LEFT JOIN profile_media media ON media.id = p.picture
		  LEFT JOIN chatbox_people o ON o.chatbox = m.chatbox AND o.uid = ?
			  WHERE m.chatbox = ?
			    AND (o.clear is null or m.creation >= o.clear)
			    	$from_condition
		   ORDER BY m.creation DESC"
		);

		if(!$stmt = $sql -> prepare($p['query'])) {
			_log(FN, 'Retrieve messages from chatbox');
		}

		$stmt->bind_param("si", $owner, $chatbox);
		$stmt->execute();
		$stmt->bind_result($msg_id, $msg_time, $msg_owner, $msg_text, $msg_edited, $msg_deleted);

		$out = array();

		while($stmt->fetch()) {
			$out[$msg_id] = chatboxGetMessage($msg_time, $msg_owner, $msg_text, $msg_edited, $msg_deleted);
		}
		
		$stmt->close();

		return $out;
	}
	
	function chatboxGetPersonsTitle($chatbox) {

		global $sql, $uid;

		if(!$stmt = $sql -> prepare("SELECT u.name
									   FROM chatbox_people p
								  RIGHT JOIN profile u ON u.uid = p.uid
								  	  WHERE p.chatbox = ? and p.uid != ?
								   ORDER BY u.name")) {
			_log(FN, 'Retrieve users for chatbox to build title');
		}

		$stmt->bind_param("is", $chatbox, $uid);
		$stmt->execute();
		$stmt->bind_result($name);

		$out = '';

		while($stmt->fetch()) {
			$out .= $name.', ';
		}
		
		$stmt->close();

		return substr($out, 0, -2);
	}

	function chatboxGetPeople($chatbox) {

		global $sql;

		if(!$stmt = $sql -> prepare("SELECT p.uid, m.image, m.thumb,  u.name, p.position, p.muted, p.leaved, p.connected
									   FROM chatbox_people p
								  LEFT JOIN profile u ON u.uid = p.uid
								  LEFT JOIN profile_media m ON u.picture = m.id
								  	  WHERE chatbox = ?
								   ORDER BY u.name")) {
			_log(FN, 'Retrieve users for chatbox');
		}

		$stmt->bind_param("i", $chatbox);
		$stmt->execute();
		$stmt->bind_result($uid, $image, $thumb, $name, $position, $muted, $leaved, $connected);

		$out = array();

		while($stmt->fetch()) {
			$out[_n($uid)] = array(
				'name'			=> _n($name),
				'image'			=> _n($image),
				'thumb'			=> _n($thumb),
				'position'		=> getLatLon($position),
				'connected'		=> (bool) $connected,
				'muted'			=> (bool) $muted,
				'leaved'		=> $leaved != null,
				'leaved_time'	=> $leaved != null ? getDateFromTimestamp($leaved) : ''
			);
		}
		
		$stmt->close();

		return $out;
	}

	function chatboxCreate() {

		global $sql;

		if(!$stmt = $sql -> prepare("INSERT INTO chatbox (id) value (null)")) {
			_log(FN, 'Create chatbox.');
		}
		$stmt->execute();
		$id = $stmt->insert_id;
		$stmt->close();

		return (int) $id;
	}

	function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}

	function chatboxAddPeople($chatbox, $user, $owner) {

		global $sql;

		if(!profileExists($user)) {
			_log2('chatboxAddPeople()', sprintf("Profile '%s' doesn't exist", $user));
			return false;
		}

		if(!chatboxMemberOrLeaver($chatbox, $user)) {
			if(!$stmt = $sql -> prepare("INSERT INTO chatbox_people (chatbox, uid, owner, mqtt) value (?, ?, ?, ?)")) {
				_log(FN, 'Add people in chatbox.');
			}

			$mqtt = generateRandomString(32);

			$stmt->bind_param("isss", $chatbox, $user, $owner, $mqtt);
			$stmt->execute();
			$stmt->close();
		} else {
			if(!$stmt = $sql -> prepare("UPDATE chatbox_people SET leaved = null WHERE chatbox=? and uid=?")) {
				_log(FN, 'Join chatbox');
			}

			$stmt->bind_param("is", $chatbox, $user);
			$stmt->execute();
		}
		
		return true;
	}

	function chatboxRename($chatbox, $title) {

		global $sql;

		if(!$stmt = $sql -> prepare("UPDATE chatbox SET name = ? WHERE id = ?")) {
			_log(FN, 'Update chatbox name');
		}

		$stmt->bind_param("si", $title, $chatbox);
		$stmt->execute();
		$stmt->close();
	}

	function chatboxMessageOwner($chatbox, $uid) {

		global $sql;

		if(!$stmt = $sql -> prepare("SELECT count(id) FROM chatbox_message WHERE uid=? AND id=?")) {
			_log(FN, 'Retrieve total messages');
		}

		$stmt->bind_param("si", $uid, $chatbox);
		$stmt->execute();
		$stmt->bind_result($total);
		$stmt->fetch();
		$stmt->close();

		if($total == 0) {
			return false;
		} else {
			return true;
		}
	}

	function chatboxOwner($uid, $chatbox) {

		global $sql;
		
		if(!$stmt = $sql -> prepare("SELECT count(id) FROM chatbox_people WHERE uid=? AND owner=? AND chatbox=?")) {
			_log(FN, 'Retrieve chatbox user ID');
		}

		$stmt->bind_param("ssi", $uid, $uid, $chatbox);
		$stmt->execute();
		$stmt->bind_result($total);
		$stmt->fetch();
		$stmt->close();

		if($total == 0) {
			return false;
		} else {
			return true;
		}
	}

	function chatboxMemberOrLeaver($chatbox, $user) {
		global $sql;
		
		if(!$stmt = $sql -> prepare("SELECT count(id) FROM chatbox_people WHERE uid=? AND chatbox=?")) {
			_log(FN, 'Retrieve chatbox user ID');
		}

		$stmt->bind_param("si", $user, $chatbox);
		$stmt->execute();
		$stmt->bind_result($total);
		$stmt->fetch();
		$stmt->close();

		if($total == 0) {
			return false;
		} else {
			return true;
		}
	}

	function chatboxMember($chatbox, $user) {

		global $sql;
		
		if(!$stmt = $sql -> prepare("SELECT count(id) FROM chatbox_people WHERE uid=? AND chatbox=? AND leaved IS null")) {
			_log(FN, 'Retrieve chatbox user ID');
		}

		$stmt->bind_param("si", $user, $chatbox);
		$stmt->execute();
		$stmt->bind_result($total);
		$stmt->fetch();
		$stmt->close();

		if($total == 0) {
			return false;
		} else {
			return true;
		}
	}

	function profileExists($uid) {

		global $sql;

		$req = $sql->query("SELECT uid FROM profile WHERE uid='$uid'");

		if($req->num_rows == 0) {
			$res = $req->fetch_array();
			return false;
		} else {
			return true;
		}
	}

	function chatboxGetMQTTChannel($chatbox, $owner) {

		global $sql;

		$req = $sql->query("SELECT mqtt FROM chatbox_people WHERE chatbox=$chatbox and uid='$owner'");
		
		if($req->num_rows == 1) {
			$res = $req->fetch_array();
			return $res['mqtt'];
		} else {
			return '';
		}
	}

	function chatboxGetMuted($chatbox, $owner) {

		global $sql;

		$req = $sql->query("SELECT muted FROM chatbox_people WHERE chatbox=$chatbox and uid='$owner'");
		
		if($req->num_rows == 1) {
			$res = $req->fetch_array();
			return (bool) $res['muted'];
		} else {
			return false;
		}
	}

	function chatboxGetTitle($chatbox) {

		global $sql;
		
		$req = $sql->query("SELECT name FROM chatbox WHERE id=$chatbox");
		
		if($req->num_rows == 1) {
			$res = $req->fetch_array();
			return _n($res['name']);
		} else {
			return '';
		}
	}

	function getDefaultPictureId($uid) {

		global $sql;

		$req = $sql->query("SELECT picture FROM profile WHERE uid='$uid'");
		
		if($req->num_rows == 1) {
			$res = $req->fetch_array();
			return (int) $res['picture'];
		} else {
			_log('getName()', sprintf('Trying to retrieve the picture of a non existing user [uid="%s"]', $uid));
		}
	}

	function getName($uid) {

		global $sql;

		$req = $sql->query("SELECT name FROM profile WHERE uid='$uid'");
		
		if($req->num_rows == 1) {
			$res = $req->fetch_array();
			return _n($res['name']);
		} else {
			_log('getName()', sprintf('Trying to retrieve the name of a non existing user [uid="%s"]', $uid));
		}
	}

	function getAPNS($uid) {

		global $sql;

		$req = $sql->query("SELECT apns FROM profile WHERE uid='$uid'");
		$res = $req->fetch_array();

		if($req->num_rows == 1) {
			return _n($res['apns']);
		} else {
			_log('getAPNS()', sprintf('Trying to retrieve the APNS of a non existing user [uid="%s"]', $uid));
		}
	}

	function APNS_header($section, $params) {
		return array('section' => $section, 'params' => $params);
	}

	function _APNS($message, $header, $uid) {
		return APNS($message, $header, getAPNS($uid));
	}

	function APNS($message, $header, $token) {
		
		$token = trim($token);

		if(empty($token)) {
			_log2('APNS()', 'Empty tocken!!!');
			return false;
		}

		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', APNS_CERT_FILE);
		stream_context_set_option($ctx, 'ssl', 'passphrase', APNS_CERT_PASS);

		// Open a connection to the APNS server
		$fp = stream_socket_client(APNS_GATEWAY, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

		if (!$fp) {
			_log2('APNS()', sprintf('Failed to connect: [%s] %s (%s, %s)', $err, $errstr, APNS_GATEWAY, APNS_CERT_FILE));
			return false;
		}

		$body['aps'] = array(
			'alert' => $message,
			'sound' => 'default', 
			'badge' => 1,
			'header' => $header
		);

		$payload = json_encode($body);
		$msg = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
		$result = fwrite($fp, $msg, strlen($msg));
		fclose($fp);

		if (!$result) {
			_log2('APNS()', sprintf('Something wrong: [%s] %s (%s, %s) : %s', $err, $errstr, APNS_GATEWAY, APNS_CERT_FILE, $msg));
			return false;
		}

		return true;
	}
	
	function require_auth() {
		global $uid, $token;

		if(!isset($uid) or !isset($token) or !isValidToken(sprintf('%s:%s', $uid, $token))) {
			_error("ACCESS_DENIED", 401);
		}
	}

	function getLatLon($location) {
		if($location !== null and !empty($location)) {
			list($lat, $lon) = explode(',', $location);
			$position = array('lat' => (float) $lat, 'lon' => (float) $lon);
		} else {
			$position = array('lat' => 0, 'lon' => 0);
		}
		return $position;
	}
	
	function _log($scope, $data, $code = 500) {
		_log2($scope, $data);
		_response($code);
	}

	function _log2($scope, $data) {
		
		file_put_contents(LOG_DIRECTORY.LOG_FILE, date('[Ymd His] ').$scope.' > '.$data."\n", FILE_APPEND);
	}

	function _error($error, $code = 400) {
		if($error != null) {
			header('Nintu-Error: ' . $error);
		}
		_response($code);
	}

	function getDateFromTimestamp($ts = null) {
		$d = new DateTime();
		$d->setTimestamp($ts === null ? time() : $ts);
		return $d->format('r');
	}

	function _response($code) {

		global $sql, $_response;

		if(is_object($sql) && get_class($sql) == 'mysqli') {
			if($sql_thread = mysqli_thread_id($sql)) {
				$sql->kill($sql_thread);
			}
			$sql->close();
		}

		$http_messages = array(
			200 => 'OK',
			400 => 'Bad request',
			401 => 'Unauthorized',
			500 => 'Internal Server Error'
		);

		header(sprintf("HTTP/1.1 %d %s", $code, $http_messages[$code]));
		header('Content-Type: application/json');
		die(json_encode($_response));
	}
	
	function prepareParams($serviceParams) {
		
		foreach($_POST as $k => $v) {
			unset($_POST[$k]);
			$v = urldecode($v);
			$k = strtolower($k);
			$_POST[$k] = $v;
		}

		foreach($serviceParams as $k => $v) {
			
			if($v['required'] and !isset($_POST[$k]) and !isset($_FILES[$k]) ) {
				return false;
			}
			
			if(isset($_POST[$k]) or isset($_FILES[$k])) {
				
				if($v['type'] == 'binary') {
					// On sort de la fonction car on ne fait pas
					// de traitement sur un fichier binaire
					// Ainsi, on peut utiliser le $_POST en dessous
					// sans avoir une erreur
					// NB. le binary type est donné dans la variable
					// global $_FILES et non pas $_POST
				} else {

					$paramValue = $_POST[$k];

					if($v['type'] == 'integer' or $v['type'] == 'int') {
						if(is_numeric($paramValue) and $paramValue == (int)$paramValue) {
							$_POST[$k] = (int) $paramValue;
						} else {
							_log('prepareParams', 'Check parameters : Value is not numeric and integer');
							return false;
						}
					} elseif($v['type'] == 'float') {
						if(is_numeric($paramValue)) {
							$_POST[$k] = (float) $paramValue;
						} else {
							_log('prepareParams', 'Check parameters : Value is not numeric');
							return false;
						}
					} elseif($v['type'] == 'boolean' or $v['type'] == 'bool') {
						if($paramValue == 'true' or $paramValue == 1) {
							$_POST[$k] = 1;
						} elseif($paramValue == 'false' or $paramValue == 0) {
							$_POST[$k] = 0;
						} else {
							_log('prepareParams', 'Check parameters : Boolean value must be [true, false, 0, 1]');
							return false;
						}
					} elseif(strpos($v['type'], 'string') === 0) {
						$paramValue = utf8_encode($paramValue);
						if(strpos($v['type'], 'string:') === 0 and strlen($paramValue) > (int)substr($v['type'], 7)) {
							return false;
							_log('prepareParams', 'Check parameters : String too long');
						}
					} else {
						_log('prepareParams', 'Check parameters : Unknow type '.$v['type']);
						return false;
					}

					if(isset($v['options']) and !array_key_exists($paramValue, $v['options']) ) {
						_log('prepareParams', "Check parameters : Value for the parameter $k not in range");
						return false;
					}
				}
			}
		}

		foreach($_POST as $k => $v) {
			$GLOBALS["__$k"] = $v;
		}
		
		return true;
	}
	
	function prepareServices($filePrototypes) {
		if(file_exists($filePrototypes)) {
			$xmlServicesDoc = new DOMDocument();
			$xmlServicesDoc->load($filePrototypes);
			$xphServicesDoc = new DOMXpath($xmlServicesDoc);
			$xphServices = $xphServicesDoc->query("/services/service");
			if (!is_null($xphServices)) {
				$services = array();
				foreach($xphServices as $xphService) {
					$name = $xphService->getAttribute('name');
					$xphParams = $xphServicesDoc->query("param", $xphService);
					$services[$name] = array();
					foreach($xphParams as $xphParam) {
						$param = $xphParam->getAttribute('name');
						$required = $xphParam->getAttribute('required');
						$services[$name][$param] = array(
							'required'	=> ($required == 'true') ? true : false,
							'type' 		=> $xphParam->getAttribute('type')
						);
						$xhrOptions = $xphServicesDoc->query("option", $xphParam);
						if($xhrOptions->length > 0) {
							$services[$name][$param]['options'] = array();
							foreach($xhrOptions as $xhrOption) {
								$value = $xhrOption->getAttribute('value');
								$services[$name][$param]['options'][$value] = $xhrOption->nodeValue;
							}
						}
					}
				}
				return $services;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function db_connect() {
		global $db_connection;
		
		$link = @new mysqli($db_connection['host'], $db_connection['user'], $db_connection['pass'], $db_connection['name'], $db_connection['port']);
		
		if ($link->connect_errno) {
			if (_DEBUG_) echo 'MySQL Connexion error : ('.$link->connect_errno.') '.$link->connect_error . PHP_EOL;
			_log('db_connect', 'MySQL Connexion error : ('.$link->connect_errno.') '.$link->connect_error);
		} else {
			return $link;
		}
	}
	
	/**
		* Validate an email address.
		* Provide email address (raw input)
		* Returns true if the email address has the email 
		* address format and the domain exists.
	 **/
	function is_email_valid($email) {
		$isValid = true;
		$atIndex = strrpos($email, "@");
		if (is_bool($atIndex) && !$atIndex) {
			$isValid = false;
		} else {
			$domain = substr($email, $atIndex+1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if ($localLen < 1 || $localLen > 64) {
				// local part length exceeded
				$isValid = false;
			} else if ($domainLen < 1 || $domainLen > 255) {
				// domain part length exceeded
				$isValid = false;
			} else if ($local[0] == '.' || $local[$localLen-1] == '.') {
				// local part starts or ends with '.'
				$isValid = false;
			} else if (preg_match('/\\.\\./', $local)) {
				// local part has two consecutive dots
				$isValid = false;
			} else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
				// character not valid in domain part
				$isValid = false;
			} else if (preg_match('/\\.\\./', $domain)) {
				// domain part has two consecutive dots
				$isValid = false;
			} else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
				// character not valid in local part unless 
				// local part is quoted
				if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
					$isValid = false;
				}
			}
			if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
				// domain not found in DNS
				$isValid = false;
			}
		}
		return $isValid;
	}
	
	function generateUID($check = true) {
		
		global $sql;
		
		$chars = array(0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
	
		do {
			$uid = '';
			for($i = 0; $i < 8; $i++) {
				$uid .= $chars[rand(0,count($chars)-1)];
			}
			if($check) { 
				$req = $sql->query("SELECT count(uid) FROM user WHERE uid='$uid'");
				$res = $req->fetch_array();
			} else {
				$res[0] = 0;
			}
		} while($res[0] > 0);
		
		return $uid;
	}

	// ToDo :
	// - Change first query to a prepared query
	function getPagination($q1, $q2, $ipp = 20) {
		global $sql, $__page, $_response;
		
		if(!isset($__page)) {
			$__page = 1;
		}

		$req = $sql->query($q1);
		$res = $req->fetch_array();

		$out = array(
			'current' => $__page,
			'ipp'	 => $ipp,
			'total'   => $res[0],
			'start'   => ($__page - 1) * $ipp,
			'next'	=> $__page + 1
		);

		
		if($out['start'] > MAX($out['total']-1, 0)) {
			_log(FN, 'Page not in a valid range', 500);
		}
		
		if(($out['next']-1) * $ipp < $out['total']) {
			// header('Nintu-Pagination: ' . $out['next']);
			$_response['next'] = $out['next'];
		}

		$out['query'] = sprintf("$q2 LIMIT %d,%d", $out['start'], $out['ipp']);

		return $out;
	}
	
	function isValidToken($token) {
		
		$fields = array(
			'token' => urlencode($token)
		);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, WS_HOST.'auth/token.verify');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
 		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 		
		$r = curl_exec($ch);

		list($header, $content) = explode("\r\n\r\n", $r, 2);
		
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if($code != 200) {
			return false;
		}
		
		$a = json_decode($content, true);

		return $a['valid'];
		
	}

	// EXTERNAL LIBRARIES
	/*
	 * Password Hashing With PBKDF2 (http://crackstation.net/hashing-security.htm).
	 * Copyright (c) 2013, Taylor Hornby
	 * All rights reserved.
	 *
	 * Redistribution and use in source and binary forms, with or without 
	 * modification, are permitted provided that the following conditions are met:
	 *
	 * 1. Redistributions of source code must retain the above copyright notice, 
	 * this list of conditions and the following disclaimer.
	 *
	 * 2. Redistributions in binary form must reproduce the above copyright notice,
	 * this list of conditions and the following disclaimer in the documentation 
	 * and/or other materials provided with the distribution.
	 *
	 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
	 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
	 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
	 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE 
	 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
	 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
	 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
	 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
	 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
	 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
	 * POSSIBILITY OF SUCH DAMAGE.
	 */

	function create_hash($password) {
		// format: algorithm:iterations:salt:hash
		$salt = base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTE_SIZE, MCRYPT_DEV_URANDOM));
		return PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" .  $salt . ":" . 
			base64_encode(pbkdf2(
				PBKDF2_HASH_ALGORITHM,
				$password,
				$salt,
				PBKDF2_ITERATIONS,
				PBKDF2_HASH_BYTE_SIZE,
				true
			));
	}

	function validate_hash($password, $correct_hash) {
		$params = explode(":", $correct_hash);
		if(count($params) < HASH_SECTIONS)
		   return false; 
		$pbkdf2 = base64_decode($params[HASH_PBKDF2_INDEX]);
		return slow_equals(
			$pbkdf2,
			pbkdf2(
				$params[HASH_ALGORITHM_INDEX],
				$password,
				$params[HASH_SALT_INDEX],
				(int) $params[HASH_ITERATION_INDEX],
				strlen($pbkdf2),
				true
			)
		);
	}

	// Compares two strings $a and $b in length-constant time.
	function slow_equals($a, $b)
	{
		$diff = strlen($a) ^ strlen($b);
		for($i = 0; $i < strlen($a) && $i < strlen($b); $i++)
		{
			$diff |= ord($a[$i]) ^ ord($b[$i]);
		}
		return $diff === 0; 
	}

	/*
	 * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
	 * $algorithm - The hash algorithm to use. Recommended: SHA256
	 * $password - The password.
	 * $salt - A salt that is unique to the password.
	 * $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.
	 * $key_length - The length of the derived key in bytes.
	 * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
	 * Returns: A $key_length-byte key derived from the password and salt.
	 *
	 * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
	 *
	 * This implementation of PBKDF2 was originally created by https://defuse.ca
	 * With improvements by http://www.variations-of-shadow.com
	 */
	function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false) {
		$algorithm = strtolower($algorithm);
		if(!in_array($algorithm, hash_algos(), true))
			trigger_error('PBKDF2 ERROR: Invalid hash algorithm.', E_USER_ERROR);
		if($count <= 0 || $key_length <= 0)
			trigger_error('PBKDF2 ERROR: Invalid parameters.', E_USER_ERROR);

		if (function_exists("hash_pbkdf2")) {
			// The output length is in NIBBLES (4-bits) if $raw_output is false!
			if (!$raw_output) {
				$key_length = $key_length * 2;
			}
			return hash_pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output);
		}

		$hash_length = strlen(hash($algorithm, "", true));
		$block_count = ceil($key_length / $hash_length);

		$output = "";
		for($i = 1; $i <= $block_count; $i++) {
			// $i encoded as 4 bytes, big endian.
			$last = $salt . pack("N", $i);
			// first iteration
			$last = $xorsum = hash_hmac($algorithm, $last, $password, true);
			// perform the other $count - 1 iterations
			for ($j = 1; $j < $count; $j++) {
				$xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
			}
			$output .= $xorsum;
		}

		if($raw_output)
			return substr($output, 0, $key_length);
		else
			return bin2hex(substr($output, 0, $key_length));
	}
?>