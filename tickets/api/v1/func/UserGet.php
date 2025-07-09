<?php
################################################################################
# @Name : api/v1/func/UserGet.php
# @Description : get user informations
# @Author : Flox
# @Create : 21/03/2023
# @Update : 21/03/2023
# @Version : 3.2.37
################################################################################

function UserGet($user_id)
{
	global $db;

	//secure sended data
	$user_id = htmlspecialchars($user_id, ENT_QUOTES, 'UTF-8');

	//check existing user
	$qry=$db->prepare("SELECT COUNT(`id`) FROM `tusers` WHERE `id`=:id AND disable=0 ");
	$qry->execute(array('id' => $user_id));
	$user_counter=$qry->fetch();
	$qry->closeCursor();

	if($user_counter[0]==0)
	{
		LogIt('API_error','UserGet : User '.$user_id.' not found',0);
		$response=array('code' => 1,'type' => 'error','action' => 'UserGet','message' =>'User '.$user_id.' not found');
		header('Content-Type: application/json');
		header("HTTP/1.1 404 Not found");
		echo json_encode($response, JSON_PRETTY_PRINT);
	} else {
		//init array
		$response=array();

		//Get user informations
		$qry=$db->prepare("SELECT * FROM `tusers` WHERE id=:id");
		$qry->execute(array('id' => $user_id));
		$user=$qry->fetch();
		$qry->closeCursor();

		$response['code']='0';
		$response['type']='success';
		$response['action']='UserGet';
		$response['user_id']=$user_id;

		$response['firstname']=$user['firstname'];
		$response['lastname']=$user['lastname'];
		$response['mail']=$user['mail'];
		$response['phone']=$user['phone'];
		$response['mobile']=$user['mobile'];
		$response['fax']=$user['fax'];
		$response['function']=$user['function'];
		$response['profile']=$user['profile'];

		header('Content-Type: application/json');
		header("HTTP/1.1 200");
		echo json_encode($response, JSON_PRETTY_PRINT);
	}
}
?>