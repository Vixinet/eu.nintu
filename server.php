<?php
	
	session_start();
  
	date_default_timezone_set("UTC");

	include_once('core.local.php');
	include_once(sprintf('configs/%s/core.config.php', _ENV_));
 	include_once('core.library.php');
	
	$_response = array();	
	
	/** ************************************************************************ **
	 ** 				We define the webserver variables :                      **
	 ** ************************************************************************ **
	 **                                                                          **
	 ** 	$__token 		Authenticate token 		Sent in the headers          **
	 ** 	$__service		Service name			Sent in the URL              **
	 ** 	$__servlet 		Servlet choosen			Sent in the headers          **
	 **                                                                          **
	 ** ************************************************************************ **/

	/** ************************************ **/
	/**         CHECK & LOAD TOKEN           **/
	/** ************************************ **/
	
	if(isset($_SERVER['HTTP_NINTU_TOKEN'])) {
		list($uid, $token) = explode(':', $_SERVER['HTTP_NINTU_TOKEN']);
	}
	
	/** ************************************ **/
	/**         CHECK & LOAD SERVLET         **/
	/** ************************************ **/

	if(!isset($_GET['servlet']) or !isset($_GET['service'])) {
		_log('server.php', 'Servlet or servicename not given', 400);
	}

	$__servlet = $_GET['servlet'];
	$__service = $_GET['service'];

	if(!file_exists("servlets/$__servlet/")) {
		_log('server.php', 'Servlet dosent exists on server', 400);
	}
	
	$servlet_config = sprintf('configs/%s/servlet.config.%s.php', _ENV_, $__servlet);
	
	if(file_exists($servlet_config)) {
		include_once($servlet_config);
	}

	$services = prepareServices("servlets/$__servlet/prototypes.xml");
	
	if(!$services) {
		_log('server.php', 'Cannot load services');
	}

	/** ************************************ **/
	/**         CHECK & LOAD SERVICE         **/
	/** ************************************ **/
	
	$service_file = "servlets/$__servlet/webservices/$__service.php";
	
	if(!file_exists($service_file) or !isset($services[$__service])) {
		_log('server.php', 'Service file or declaration in prototypes.xml dosent exist');
	}

	if(!prepareParams($services[$__service])) {
		_log('server.php', 'Cannot prepare params', 400);
	}

	include_once($service_file);

	_response(200);

?>