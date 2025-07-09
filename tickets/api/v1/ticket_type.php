<?php
################################################################################
# @Name : api/type_ticket.php
# @Description : ticket type
# @Call : External application
# @Author : Flox
# @Create : 21/06/2023
# @Update : 26/06/2023
# @Version : 3.2.37
################################################################################

//api init
require('init.php');

//init var
if(!isset($_POST['type_id'])) {$_POST['type_id']='';}

switch($request_method)
{
	case 'GET':
		$response=array();
		//display tickets
		$query="SELECT `id`,`name` FROM `ttypes` WHERE id!=0";
		$qry=$db->prepare($query);
		$qry->execute();
		while($type=$qry->fetch()) 
		{
			array_push(
				$response, 
				array(
					'code' => '0',
					'type' => 'success',
					'action' => 'TicketTypeList',
					"type_id" => $type['id'],
					"type_name" => $type['name']
				)
			);
		}
		$qry->closeCursor();
		header('Content-Type: application/json');
		header("HTTP/1.1 200");
		echo json_encode($response, JSON_PRETTY_PRINT);
		break;
	case 'POST':
		//invalid request method
		LogIt('API_error','HTTP/1.0 405 Method PUT Not Allowed',0);
		header("HTTP/1.0 405 Method Not Allowed");
		break;
	case 'PUT':
		//invalid request method
		LogIt('API_error','HTTP/1.0 405 Method PUT Not Allowed',0);
		header("HTTP/1.0 405 Method Not Allowed");
		break;
	case 'DELETE':
		//delete ticket
		LogIt('API_error','HTTP/1.0 405 Method DELETE Not Allowed',0);
		header("HTTP/1.0 405 Method Not Allowed");
		break;
	default:
		//invalid request method
		LogIt('API_error','HTTP/1.0 405 Method Not Allowed',0);
		header("HTTP/1.0 405 Method Not Allowed");
		break;
}
?>