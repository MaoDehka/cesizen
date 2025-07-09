<?php
################################################################################
# @Name : api/user.php
# @Description : user API : search
# @Call : External application
# @Author : Flox
# @Create : 26/10/2021
# @Update : 26/10/2021
# @Version : 3.2.18
################################################################################

//api init
require('init.php');

//init var
if(!isset($_GET['user_id'])) {$_GET['user_id']='';}
$user_id=$_GET['user_id'];

//////////////////////////////////////////////////////////////////////////// UPDATE USER
function AddUser($user_id)
{
	$response=array('code' => 1,'type' => 'error','message' =>'Method not available for UserAdd');
	header('Content-Type: application/json');
	header("HTTP/1.1 405 Method Not Allowed");
	echo json_encode($response, JSON_PRETTY_PRINT);
}

//////////////////////////////////////////////////////////////////////////// UPDATE USER
function UpdateUser($user_id)
{
	$response=array('code' => 1,'type' => 'error','message' =>'Method not available for UpdateUser');
	header('Content-Type: application/json');
	header("HTTP/1.1 405 Method Not Allowed");
	echo json_encode($response, JSON_PRETTY_PRINT);
}

//////////////////////////////////////////////////////////////////////////// DELETE USER
function DeleteUser($user_id)
{
	$response=array('code' => 1,'type' => 'error','message' =>'Method not available for DeleteUser');
	header('Content-Type: application/json');
	header("HTTP/1.1 405 Method Not Allowed");
	echo json_encode($response, JSON_PRETTY_PRINT);
}

switch($request_method)
{
	case 'GET':
		if($user_id)
		{
			//get user informations from phone number users
			require('func/UserGet.php');
			UserGet($user_id);
		} 
		break;
	case 'POST':
		//add user
		AddUser();
		break;
	case 'PUT':
		//invalid request method
		header("HTTP/1.0 405 Method Not Allowed");
		break;
	case 'DELETE':
		//delete user
		DeleteUser(intval($_GET["id"]));
		break;
	default:
		//invalid request method
		header("HTTP/1.0 405 Method Not Allowed");
		break;
}
?>