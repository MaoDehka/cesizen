<?php
################################################################################
# @Name : api/v1/init.php
# @Description : API init script
# @Call : 
# @prerequisite : mod_rewrite of Apache and AllowOverride All on apache2.conf
# @Author : Flox
# @Create : 08/02/2023
# @Update : 22/03/2023
# @Version : 3.2.35
################################################################################

//define header to database
header('Content-Type: application/json; charset=utf-8');

//connect to database
include("./../../connect.php");

//init functions
include("./../../core/functions.php");

//switch SQL MODE to allow empty values
$db->exec('SET sql_mode = ""');

//read header informations
$request_method = $_SERVER["REQUEST_METHOD"];

$post_json_data= file_get_contents('php://input');

//init var
if(!isset($_GET['id'])) {$_GET['id']='';}
if(!isset($_GET['user_id'])) {$_GET['user_id']='';}
if(!isset($_GET['order'])) {$_GET['order']='';}
if(!isset($_GET['sort'])) {$_GET['sort']='';}
if(!isset($_GET['limit'])) {$_GET['limit']='';}
if(!isset($_GET['offset'])) {$_GET['offset']='';}

//get api parameters
$qry=$db->prepare("SELECT `api`,`api_key`,`api_client_ip`,`server_url` FROM `tparameters`");
$qry->execute();
$parameters=$qry->fetch();
$qry->closeCursor();

//check api enable
if(!$parameters['api'])
{
	LogIt('API_error','API disabled in application',0);
	$response=array('code' => 1, 'type' => 'error', 'message' =>'API disabled in application');
	header('Content-Type: application/json');
	header("HTTP/1.1 403 Forbidden");
	echo json_encode($response, JSON_PRETTY_PRINT);
	exit;
}

//get api key
if(isset($_SERVER["HTTP_X_API_KEY"])) // X-API-KEY
{
	$api_key=$_SERVER["HTTP_X_API_KEY"];
} else { //basic auth
	$headers = apache_request_headers();
	if(!empty($headers['Authorization']))
	{
		$AuthorizationHeader=$headers['Authorization'];
		$api_key=explode('Basic ',$AuthorizationHeader);
		$api_key=$api_key[1];
		$api_key=base64_decode($api_key);
	} else {
		LogIt('API_error','Unable to get API Key, add X-API-KEY header',0);
		$response=array('code' => 1, 'type' => 'error', 'message' =>'Unable to get API Key, add X-API-KEY header');
		header('Content-Type: application/json');
		header("HTTP/1.1 403 Forbidden");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}
}

//check configure api key
if(!$parameters['api_key'])
{
	LogIt('API_error','API key not defined in application',0);
	$response=array('code' => 1, 'type' => 'error', 'message' =>'API key not defined in application');
	header('Content-Type: application/json');
	header("HTTP/1.1 403 Forbidden");
	echo json_encode($response, JSON_PRETTY_PRINT);
	exit;
}

//check allowed remote client IP
if($parameters['api_client_ip'])
{
	$valid_ip=0;
	$ip_whitelist=explode(',',$parameters['api_client_ip']);
	foreach($ip_whitelist as $ip)
	{
		if(preg_match('/'.$ip.'/',$_SERVER['REMOTE_ADDR'])) {$valid_ip=1;}
	}
	if(!$valid_ip) 
	{
		LogIt('API_error','Unauthorized IP ('.$_SERVER['REMOTE_ADDR'].') in application',0);
		$response=array('code' => 1, 'type' => 'error', 'message' =>'Unauthorized IP ('.$_SERVER['REMOTE_ADDR'].') in application');
		header('Content-Type: application/json');
		header("HTTP/1.1 403 Forbidden");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}
}

//check api key
if(!$api_key)
{
	LogIt('API_error','Missing API key parameter',0);
	$response=array('code' => 1, 'type' => 'error', 'message' =>'Missing API key parameter');
	header('Content-Type: application/json');
	header("HTTP/1.1 403 Forbidden");
	echo json_encode($response, JSON_PRETTY_PRINT);
	exit;
}

//check configure api key
if($parameters['api_key'] != $api_key)
{
	//test removing prefix
	$api_key_sub=substr($api_key,1);
	if($parameters['api_key'] != $api_key_sub)
	{
		LogIt('API_error','Wrong API Key ('.$api_key.')',0);
		$response=array('code' => 1, 'type' => 'error', 'message' =>'Wrong API Key ('.$api_key.')');
		header('Content-Type: application/json');
		header("HTTP/1.1 403 Forbidden");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}
}

//check https
if($_SERVER["SERVER_PORT"]!='443') 
{
	LogIt('API_error','Unauthorized access port, use 443. ('.$_SERVER["SERVER_PORT"].')',0);
	$response=array('code' => 1, 'type' => 'error', 'message' =>'Unauthorized access port, use 443. ('.$_SERVER["SERVER_PORT"].')');
	header('Content-Type: application/json');
	header("HTTP/1.1 403 Forbidden");
	echo json_encode($response, JSON_PRETTY_PRINT);
	exit;
}

?>