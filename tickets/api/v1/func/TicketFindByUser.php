<?php
################################################################################
# @Name : api/v1/func/TicketFindByUser.php
# @Description : Get user tickets
# @Author : Flox
# @Create : 08/02/2023
# @Update : 21/03/2023
# @Version : 3.2.35
################################################################################

function TicketFindByUser($user_id,$order,$sort,$limit,$offset)
{
	global $db;

	//secure sended data
	$user_id = htmlspecialchars($user_id, ENT_QUOTES, 'UTF-8');
	$order = htmlspecialchars($order, ENT_QUOTES, 'UTF-8');
	$sort = htmlspecialchars($sort, ENT_QUOTES, 'UTF-8');
	$limit = htmlspecialchars($limit, ENT_QUOTES, 'UTF-8');
	$offset = htmlspecialchars($offset, ENT_QUOTES, 'UTF-8');

	//calculate offset to have page 1, 2
	$offset=$limit*$offset;

	//check existing parameters
	if(!isset($user_id) || !isset($order) || !isset($sort) || !isset($limit) || !isset($offset))
	{
		LogIt('API_error',"TicketsFindByUser : Missing parameters user_id=$user_id order=$order sort=$sort limit=$limit offset=$offset",0);
		$response=array('code' => 1,'type' => 'error','action' => 'TicketsFindByUser','message' =>"Missing parameters user_id=$user_id order=$order sort=$sort limit=$limit offset=$offset");
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad request");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}

	//check formation parameters
	if($order!='id' && $order!='date_create' && $order!='date_modif')
	{
		LogIt('API_error',"TicketsFindByUser : Incorrect ORDER parameter",0);
		$response=array('code' => 1,'type' => 'error','action' => 'TicketsFindByUser','message' =>"Incorrect ORDER parameter");
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad request");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}
	if($sort!='ASC' && $sort!='DESC')
	{
		LogIt('API_error',"TicketsFindByUser : Incorrect SORT parameter",0);
		$response=array('code' => 1,'type' => 'error','action' => 'TicketsFindByUser','message' =>"Incorrect SORT parameter");
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad request");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}
	if(!is_numeric($limit))
	{
		LogIt('API_error',"TicketsFindByUser : Incorrect LIMIT parameter",0);
		$response=array('code' => 1,'type' => 'error','action' => 'TicketsFindByUser','message' =>"Incorrect LIMIT parameter");
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad request");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}
	if(!is_numeric($offset))
	{
		LogIt('API_error',"TicketsFindByUser : Offset OFFSET parameter",0);
		$response=array('code' => 1,'type' => 'error','action' => 'TicketsFindByUser','message' =>"Incorrect OFFSET parameter");
		header('Content-Type: application/json');
		header("HTTP/1.1 400 Bad request");
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}

	//check existing user
	$qry=$db->prepare("SELECT id FROM `tusers` WHERE `id`=:user_id");
	$qry->execute(array('user_id' => $user_id));
	$user=$qry->fetch();
	$qry->closeCursor();
	if(empty($user['id']))
	{
		LogIt('API_error','TicketsFindByUser : User not found in database application ('.$user['id'].')',0);
		$response=array('code' => 1,'type' => 'error','action' => 'TicketsFindByUser','message' =>'User not found in database application ('.$user['id'].')');
		header('Content-Type: application/json');
		header("HTTP/1.1 404 Not found");
		echo json_encode($response, JSON_PRETTY_PRINT);
	} else {
		//check existing ticket
		if($sort=='ASC') 
		{
			$query="SELECT `tincidents`.`id`,`tincidents`.`date_create`,`tincidents`.`title`,`tincidents`.`state`,`tstates`.`name`,`tincidents`.`date_modif` FROM `tincidents`,`tstates` WHERE `tincidents`.`state`=`tstates`.`id` AND `tincidents`.`user`=:user_id ORDER BY {$order} ASC LIMIT {$limit} OFFSET {$offset}";
		}else {
			$query="SELECT `tincidents`.`id`,`tincidents`.`date_create`,`tincidents`.`title`,`tincidents`.`state`,`tstates`.`name`,`tincidents`.`date_modif` FROM `tincidents`,`tstates` WHERE `tincidents`.`state`=`tstates`.`id` AND `tincidents`.`user`=:user_id ORDER BY {$order} DESC LIMIT {$limit} OFFSET {$offset}";
		}
		$qry=$db->prepare($query);
		$qry->execute(array('user_id' => $user['id']));
		$tickets=$qry->fetch();
		$qry->closeCursor();
		$counter = $qry->rowCount();

		if($counter==0)
		{
			LogIt('API_error','TicketsFindByUser : No tickets found for user id '.$user['id'],0);
			$response=array('code' => 1,'type' => 'error','action' => 'TicketsFindByUser','message' =>'No tickets found for user id '.$user['id']);
			header('Content-Type: application/json');
			header("HTTP/1.1 404 Not found");
			echo json_encode($response, JSON_PRETTY_PRINT);
		} else {
			$response=array();
			//display tickets
			$qry=$db->prepare($query);
			$qry->execute(array('user_id' => $user['id']));
			while($ticket=$qry->fetch()) 
			{
				//transform date to fr
				$date_create_fr = new DateTime($ticket['date_create']);
				$date_create_fr=$date_create_fr->format('d/m/Y H:i:s');

				$date_modif_fr = new DateTime($ticket['date_modif']);
				$date_modif_fr=$date_modif_fr->format('d/m/Y H:i:s');

				array_push(
					$response, 
					array(
						'code' => '0',
						'type' => 'success',
						'action' => 'TicketsFindByUser',
						"ticket_id" => $ticket['id'],
						"ticket_date_create" => $ticket['date_create'],
						"ticket_date_create_fr" => $date_create_fr,
						"ticket_subject" => $ticket['title'],
						"ticket_state_id" => $ticket['state'],
						"ticket_state_name" => $ticket['name'],
						"ticket_date_modif" => $ticket['date_modif'],
						"ticket_date_modif_fr" => $date_modif_fr
					)
				);
			}
			$qry->closeCursor();
			//get ticket informations
			LogIt('API','TicketsFindByUser : list '.$counter.' ticket for user_id '.$user['id'],0);
			header('Content-Type: application/json');
			header("HTTP/1.1 200");
			echo json_encode($response, JSON_PRETTY_PRINT);
		}
	}
}
?>