<?php
################################################################################
# @Name : api/v1/func/GetTicket.php
# @Description : add new ticket 
# @Author : Flox
# @Create : 08/02/2023
# @Update : 26/06/2023
# @Version : 3.2.37
################################################################################

function TicketAddResolution($ticket_id)
{
	global $db;

	//init var
	$info='';
	$upload='success';
	if(!isset($ticket_id)){$ticket_id='';}
	if(!isset($_POST['text'])){$_POST['text']='';}
	if(!isset($_POST['user_id'])){$_POST['user_id']='';}

	//secure posted values
	$ticket_id = htmlspecialchars($ticket_id, ENT_QUOTES, 'UTF-8');
	$_POST['text'] = htmlspecialchars($_POST['text'], ENT_QUOTES, 'UTF-8');
	$_POST['user_id'] = htmlspecialchars($_POST['user_id'], ENT_QUOTES, 'UTF-8');

	//check mandatory fields
	if(!$ticket_id)
	{
		LogIt('API_error','TicketAddResolution : Missing required ticket_id field',0);
		$response=array('code' => 1, 'type' => 'error','action' => 'TicketAddResolution', 'message' =>'Missing required ticket_id field');
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad request");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}
	elseif(!$_POST['text'])
	{
		LogIt('API_error','TicketAddResolution : Missing required text field',0);
		$response=array('code' => 1, 'type' => 'error','action' => 'TicketAddResolution', 'message' =>'Missing required text field');
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad request");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}elseif(!$_POST['user_id'])
	{
		LogIt('API_error','TicketAddResolution : Missing required user_id field',0);
		$response=array('code' => 1, 'type' => 'error','action' => 'TicketAddResolution', 'message' =>'Missing required user_id field');
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad request");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}

	//check data
	if(!is_numeric($_POST['user_id']))
	{
		LogIt('API_error','TicketAddResolution : no numeric value detected on user_id',0);
		$response=array('code' => 1, 'type' => 'error','action' => 'TicketAddResolution', 'message' =>'no numeric value detected on user_id');
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad request");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}
	if(!is_numeric($ticket_id))
	{
		LogIt('API_error','TicketAddResolution : no numeric value detected on ticket_id',0);
		$response=array('code' => 1, 'type' => 'error','action' => 'TicketAddResolution', 'message' =>'no numeric value detected on ticket_id');
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad request");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}

	//TicketAddResolution
	$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`author`,`date`,`type`,`text`) VALUES (:ticket,:author,:date,:type,:text)");
	$stmt = $qry->execute(
		array(
			'ticket' => $ticket_id,
			'author' => $_POST['user_id'],
			'date' =>  date('Y-m-d H:i:s'),
			'type' => 0,
			'text' => $_POST['text']
		)
	);


	if($stmt)
	{
		//get URL parameters
		$qry=$db->prepare("SELECT `server_url` FROM `tparameters`");
		$qry->execute();
		$parameters=$qry->fetch();
		$qry->closeCursor();

		logit('API','TicketAddResolution : add resolution on ticket '.$ticket_id,0);
		$ticket_url=$parameters['server_url'].'/index.php?page=ticket&id='.$ticket_id;
		$response=array('code' => 0,'type' => 'success','action' => 'TicketAddResolution','message' =>'Add resolution on ticket '.$ticket_id, 'ticket_id' => $ticket_id, 'ticket_url' => $ticket_url);
		header('Content-Type: application/json');
		header("HTTP/1.1 200");
		echo json_encode($response, JSON_PRETTY_PRINT);
	} else {
		logit('API_error','TicketAddResolution : '.$db->errorInfo(),0);
		$response=array('code' => 1,'type' => 'error','action' => 'TicketAddResolution','message' =>$db->errorInfo(),'ticket_id' => $ticket_id);
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad request");
		echo json_encode($response, JSON_PRETTY_PRINT);
	}
}
?>