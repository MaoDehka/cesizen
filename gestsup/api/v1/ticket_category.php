<?php
################################################################################
# @Name : api/type_ticket.php
# @Description : ticket type
# @Call : External application
# @Author : Flox
# @Create : 21/06/2023
# @Update : 21/06/2023
# @Version : 3.2.37
################################################################################

//api init
require('init.php');

switch($request_method)
{
	case 'GET':
        $response=array();
        //display tickets
        $query="SELECT `id`,`name` FROM `tcategory` WHERE id!=0";
        $qry=$db->prepare($query);
        $qry->execute();
        while($category=$qry->fetch()) 
        {
            array_push(
                $response, 
                array(
                    'code' => '0',
                    'type' => 'success',
                    'action' => 'TicketCategoryList',
                    "category_id" => $category['id'],
                    "category_name" => $category['name']
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