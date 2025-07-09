<?php
################################################################################
# @Name : api/v1/func/GetTicket.php
# @Description : get ticket information
# @Author : Flox
# @Create : 08/02/2023
# @Update : 21/03/2023
# @Version : 3.2.35
################################################################################

function TicketGet($id,$server_url)
{
	global $db;

	//secure sended data
	$id = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');

	//define thread type name
	$thread_type_name = array (
		array('0','text'),
		array('1','attribution'),
		array('2','transfert'),
		array('3','mail'),
		array('4','close'),
		array('5','switch state'),
	  );

	//check existing ticket
	$qry=$db->prepare("SELECT COUNT(`id`) FROM `tincidents` WHERE `id`=:id AND disable=0 ");
	$qry->execute(array('id' => $id));
	$ticket_counter=$qry->fetch();
	$qry->closeCursor();

	if($ticket_counter[0]==0)
	{
		LogIt('API_error','TicketGet : Ticket '.$id.' not found',0);
		$response=array('code' => 1,'type' => 'error','action' => 'TicketGet','message' =>'Ticket '.$id.' not found');
		header('Content-Type: application/json');
		header("HTTP/1.1 404 Not found");
		echo json_encode($response, JSON_PRETTY_PRINT);
	} else {
		//init array
		$response=array();

		//TicketGet informations
		$qry=$db->prepare("SELECT `id`,`technician`,`type`,`title`,`user`,`description`,`date_create`,`state` FROM `tincidents` WHERE id=:id");
		$qry->execute(array('id' => $id));
		$ticket=$qry->fetch();
		$qry->closeCursor();

		//get technician name
		$qry=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
		$qry->execute(array('id' => $ticket['technician']));
		$technician=$qry->fetch();
		$qry->closeCursor();
		if(empty($technician['firstname'])) {$technician['firstname']='';}
		if(empty($technician['lastname'])) {$technician['lastname']='';}

		//get state name
		$qry=$db->prepare("SELECT `name` FROM `tstates` WHERE id=:id");
		$qry->execute(array('id' => $ticket['state']));
		$state=$qry->fetch();
		$qry->closeCursor();
		if(empty($state['name'])) {$state['name']='';}

		//get type name
		$qry=$db->prepare("SELECT `name` FROM `ttypes` WHERE id=:id");
		$qry->execute(array('id' => $ticket['type']));
		$type=$qry->fetch();
		$qry->closeCursor();
		if(empty($type['name'])) {$type['name']='';}

		//transform date to fr
		$date_create_fr = new DateTime($ticket['date_create']);
		$date_create_fr=$date_create_fr->format('d/m/Y H:i:s');

		$response['code']='0';
		$response['type']='success';
		$response['action']='TicketGet';
		$response['ticket_id']=$ticket['id'];
		$response['ticket_technician']=$technician['firstname'].' '.$technician['lastname'];
		
		$response['ticket_type_id']=$ticket['type'];
		$response['ticket_type_name']=$type['name'];
		$response['ticket_title']=$ticket['title'];
		$response['ticket_description']=$ticket['description'];
		
		$response['ticket_date_create']=$ticket['date_create'];
		$response['ticket_date_create_fr']=$date_create_fr;
		$response['ticket_state_id']=$ticket['state'];
		$response['ticket_state_name']=$state['name'];
		$response['ticket_resolution']=array();
		
		//generate resolution field 
		$qry=$db->prepare("SELECT `tthreads`.`id`,`tthreads`.`date`,CONCAT(`tusers`.firstname,' ',`tusers`.lastname) AS author,`tthreads`.`text`,`tthreads`.`type`,`tthreads`.`state` FROM `tthreads`,`tusers` WHERE `tthreads`.`author`=`tusers`.`id` AND `tthreads`.`ticket`=:ticket ORDER BY `tthreads`.`date`");
		$qry->execute(array('ticket' => $ticket['id']));
		while($thread=$qry->fetch()) 
		{
			if($thread['type']==0)
			{
				//add thread data
				array_push(
					$response['ticket_resolution'], 
					array(
						"thread_id" => $thread['id'],
						"thread_type_id" => $thread['type'],
						"thread_type_name" => $thread_type_name[$thread['type']][1],
						"thread_date" => $thread['date'],
						"thread_author" => $thread['author'],
						"thread_text" => $thread['text']
					)
				);
			} else {
				if($thread['type']==5)
				{
					$qry2=$db->prepare("SELECT `name` FROM `tstates` WHERE id=:id");
					$qry2->execute(array('id' => $thread['state']));
					$state=$qry2->fetch();
					$qry2->closeCursor();
					if(empty($state['name'])) {$state=array();$state['name']='';}
				} else {$state['name']='';}

				//add thread data without attachment
				array_push(
					$response['ticket_resolution'], 
					array(
						"thread_id" => $thread['id'],
						"thread_type_id" => $thread['type'],
						"thread_type_name" => $thread_type_name[$thread['type']][1],
						"thread_state_name" => $state['name'],
						"thread_date" => $thread['date'],
						"thread_author" => $thread['author'],
						"thread_text" => $thread['text'],
					)
				);
			}
			
		}
		$qry->closeCursor();

		header('Content-Type: application/json');
		header("HTTP/1.1 200");
		echo json_encode($response, JSON_PRETTY_PRINT);
	}
}
?>