<?php
################################################################################
# @Name : api/v1/func/TicketAdd.php
# @Description : add new ticket 
# @Author : Flox
# @Create : 08/02/2023
# @Update : 08/04/2024
# @Version : 3.2.48
################################################################################

function TicketAdd()
{
	global $db;

	//init var
	$attachment_error='';
	$info='';
	$upload='success';
	if(!isset($_POST['ticket_user_mail'])){$_POST['ticket_user_mail']='';}
	if(!isset($_POST['ticket_type'])){$_POST['ticket_type']='';}
	if(!isset($_POST['ticket_title'])){$_POST['ticket_title']='';}
	if(!isset($_POST['ticket_description'])){$_POST['ticket_description']='';}
	if(!isset($_POST['file1'])){$_POST['file1']='';}

	//secure posted values
	$_POST['ticket_user_mail'] = htmlspecialchars($_POST['ticket_user_mail'], ENT_QUOTES, 'UTF-8');
	$_POST['ticket_type'] = htmlspecialchars($_POST['ticket_type'], ENT_QUOTES, 'UTF-8');
	$_POST['ticket_title'] = htmlspecialchars($_POST['ticket_title'], ENT_QUOTES, 'UTF-8');
	$_POST['ticket_description'] = htmlspecialchars($_POST['ticket_description'], ENT_QUOTES, 'UTF-8');

	//check mandatory fields
	if(!$_POST['ticket_title'])
	{
		LogIt('API_error','TicketAdd : Missing required ticket_title field',0);
		$response=array('code' => 1, 'type' => 'error','action' => 'TicketAdd', 'message' =>'Missing required ticket_title field');
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad request");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}
	elseif(!$_POST['ticket_description'])
	{
		LogIt('API_error','TicketAdd : Missing required ticket_description field',0);
		$response=array('code' => 1, 'type' => 'error','action' => 'TicketAdd', 'message' =>'Missing required ticket_description field');
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad request");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}

	//check data
	if($_POST['ticket_type'] && !is_numeric($_POST['ticket_type']))
	{
		LogIt('API_error','TicketAdd : no numeric value detected on ticket_type',0);
		$response=array('code' => 1, 'type' => 'error','action' => 'TicketAdd', 'message' =>'no numeric value detected on ticket_type');
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad request");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}

	//get user id if mail sended
	if($_POST['ticket_user_mail'])
	{
		$qry=$db->prepare("SELECT `id` FROM `tusers` WHERE `mail`=:mail");
		$qry->execute(array('mail' => $_POST['ticket_user_mail']));
		$user=$qry->fetch();
		$qry->closeCursor();
		if(isset($user['id'])) {$user_id=$user['id'];} else {$user_id=0;}
	} else {
		$user_id=0;
	}

	//TicketAdd
	$qry=$db->prepare("INSERT INTO `tincidents` (`user`,`type`,`title`,`description`,`date_create`,`date_modif`,`state`) VALUES (:user,:type,:title,:description,:date_create,:date_modif,'5')");
	$stmt = $qry->execute(
		array(
			'user' => $user_id,
			'type' => $_POST['ticket_type'],
			'title' => $_POST['ticket_title'],
			'description' => $_POST['ticket_description'],
			'date_create' => date('Y-m-d H:i:s'),
			'date_modif' => date('Y-m-d H:i:s')
		)
	);
	$ticket_id=$db->lastInsertId();
	
	if($stmt)
	{
		//get URL parameters
		$qry=$db->prepare("SELECT `server_url` FROM `tparameters`");
		$qry->execute();
		$parameters=$qry->fetch();
		$qry->closeCursor();

		logit('API','TicketAdd : ticket '.$ticket_id.' created',0);
		$ticket_url=$parameters['server_url'].'/index.php?page=ticket&id='.$ticket_id;
		$response=array('code' => 0,'type' => 'success','action' => 'TicketAdd','message' =>'Ticket '.$ticket_id.' created', 'ticket_id' => $ticket_id, 'ticket_url' => $ticket_url);
		header('Content-Type: application/json');
		header("HTTP/1.1 200");
		echo json_encode($response, JSON_PRETTY_PRINT);
	} else {
		logit('API_error','TicketAdd : '.$db->errorInfo(),0);
		$response=array('code' => 1,'type' => 'error','action' => 'TicketAdd','message' =>$db->errorInfo(),'ticket_id' => $ticket_id);
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad request");
		echo json_encode($response, JSON_PRETTY_PRINT);
	}
}
?>