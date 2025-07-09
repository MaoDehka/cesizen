<?php
################################################################################
# @Name : api/v1/ticket.php
# @Description : tickets API : create, update, delete
# @Call : 
# @Author : Flox
# @Create : 19/11/2020
# @Update : 11/11/2021
# @Version : 3.2.33
################################################################################

//api init
require('init.php');

//init var
if(!isset($_POST['ticket_id'])) {$_POST['ticket_id']='';}

//////////////////////////////////////////////////////////////////////////// UPDATE TICKET
function UpdateTicket($id)
{
	LogIt('API_error','Method not available for UpdateTicket',0);
	$response=array('code' => 1,'type' => 'error','message' =>'Method not available for UpdateTicket');
	header('Content-Type: application/json');
	header("HTTP/1.1 405 Method Not Allowed");
	echo json_encode($response, JSON_PRETTY_PRINT);
}

//////////////////////////////////////////////////////////////////////////// DELETE TICKET
function DeleteTicket($id)
{
	LogIt('API_error','Method not available for DeleteTicket',0);
	$response=array('code' => 1,'type' => 'error','message' =>'Method not available for DeleteTicket');
	header('Content-Type: application/json');
	header("HTTP/1.1 405 Method Not Allowed");
	echo json_encode($response, JSON_PRETTY_PRINT);
}

switch($request_method)
{
	case 'GET':
		if(!empty($_GET['id']) && $_GET['id']!=0) //get one ticket
		{
			require('func/TicketGet.php');
			TicketGet(intval($_GET['id']),$parameters['server_url']);
		} elseif(!empty($_GET['user_id'])) { //get user tickets
			require('func/TicketFindByUser.php');
			TicketFindByUser($_GET['user_id'], $_GET['order'], $_GET['sort'], $_GET['limit'], $_GET['offset']);
		} else {
			LogIt('API_error','Get Method not available',0);
			$response=array('code' => 1,'type' => 'error','message' =>'TicketGet Method not available, missing id '.$_GET['id']);
			header('Content-Type: application/json');
			header("HTTP/1.1 405 Method Not Allowed");
			echo json_encode($response, JSON_PRETTY_PRINT);
		}
		break;
	case 'POST':
		if(!empty($_GET['id']) && $_GET['id']!=0) //get one ticket
		{
			//add resolution text
			require('func/TicketAddResolution.php');
			TicketAddResolution(intval($_GET['id']));
		} else {
			//add ticket
			require('func/TicketAdd.php');
			TicketAdd();
		}
		
		break;
	case 'PUT':
		//invalid request method
		LogIt('API_error','HTTP/1.0 405 Method PUT Not Allowed',0);
		header("HTTP/1.0 405 Method Not Allowed");
		break;
	case 'DELETE':
		//delete ticket
		LogIt('API_error','HTTP/1.0 405 Method DELETE Not Allowed',0);
		DeleteTicket(intval($_GET["id"]));
		break;
	default:
		//invalid request method
		LogIt('API_error','HTTP/1.0 405 Method Not Allowed',0);
		header("HTTP/1.0 405 Method Not Allowed");
		break;
}
?>