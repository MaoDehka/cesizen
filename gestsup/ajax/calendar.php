<?php
################################################################################
# @Name : /core/calendar.php
# @Description : update event in db
# @Call : /calendar.php
# @Parameters : 
# @Author : Flox
# @Create : 19/02/2018
# @Update : 17/01/2024
# @Version : 3.2.47
################################################################################

//security check
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
{
	//init and secure var
	require_once('../core/init_post.php');
	require_once('../core/init_get.php');

	//db connection
	require_once('../connect.php');

	//init var
	if(!isset($_POST['title'])){$_POST['title']='';}
	if(!isset($_POST['start'])){$_POST['start']='';}
	if(!isset($_POST['end'])){$_POST['end']='';}
	if(!isset($_POST['allday'])){$_POST['allday']='';}
	if(!isset($_POST['id'])){$_POST['id']='';}
	if(!isset($_POST['technician'])){$_POST['technician']='';}
	if(!isset($_POST['action'])){$_POST['action']='';}
	if(!isset($_POST['token'])){$_POST['token']='';}

	//secure var
	$_POST['title']=htmlspecialchars($_POST['title']);
	$_POST['start']=htmlspecialchars($_POST['start']);
	$_POST['end']=htmlspecialchars($_POST['end']);
	$_POST['allday']=htmlspecialchars($_POST['allday']);
	$_POST['id']=htmlspecialchars($_POST['id']);
	$_POST['technician']=htmlspecialchars($_POST['technician']);
	$_POST['action']=htmlspecialchars($_POST['action']);
	$_POST['token']=htmlspecialchars($_POST['token']);

	//switch SQL MODE to allow empty values
	$db->exec('SET sql_mode = ""');

	//check token
	$qry=$db->prepare("SELECT `token` FROM ttoken WHERE `action`='calendar_access' AND ip=:ip AND token=:token");
	$qry->execute(array('ip' => $_SERVER['REMOTE_ADDR'],'token' => $_POST['token']));
	$token=$qry->fetch();
	$qry->closeCursor();
	if(empty($token['token'])) {$token=array(); $token['token']='';}

	if($token['token'] && $token['token']==$_POST['token']) {

		//load parameters table
		$qry=$db->prepare("SELECT * FROM `tparameters`");
		$qry->execute();
		$rparameters=$qry->fetch();
		$qry->closeCursor();
		
		//display error parameter
		if($rparameters['debug']) {
			ini_set('display_errors', 'On');ini_set('display_startup_errors', 'On');ini_set('html_errors', 'On');error_reporting(E_ALL);
		} else {
			ini_set('display_errors', 'Off');ini_set('display_startup_errors', 'Off'); ini_set('html_errors', 'Off');error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
		}

		//check if function is enabled
		if(!$rparameters['planning']){echo json_encode(array("status" => "failed","message" => "Disabled function")); exit;}

		//update event title
		if($_POST['action']=='update_title')
		{
			$qry=$db->prepare("UPDATE `tevents` SET `title`=:title WHERE `id`=:id");
			$qry->execute(array(':title'=>htmlspecialchars_decode($_POST['title'], ENT_QUOTES),':id'=>$_POST['id']));
			echo json_encode(array("status" => "success"));
		}

		//update event date on move
		if($_POST['action']=='move_event' || $_POST['action']=='resize_event') {
			$qry=$db->prepare("UPDATE `tevents` SET `date_start`=:date_start, `date_end`=:date_end, `allday`=:allday WHERE `id`=:id");
			$qry->execute(array(':date_start'=>$_POST['start'],':date_end'=>$_POST['end'],':allday'=>$_POST['allday'],':id'=>$_POST['id']));
			echo json_encode(array("status" => "success"));
		} 

		//delete event
		if($_POST['action']=='delete_event')
		{
			$qry = $db->prepare("DELETE FROM `tevents` WHERE `id`=:id");
			$qry->execute(array(':id'=>$_POST['id']));
			echo json_encode(array("status" => "success"));
		}

		//add event
		if($_POST['action']=='add_event')
		{
			$qry = $db->prepare("INSERT INTO `tevents` (`technician`,`title`,`date_start`,`date_end`,`allday`,`className`) VALUES (:technician, :title, :start, :end, :allday, 'badge-primary')");
			$qry->execute(array(':technician'=>$_POST['technician'],':title'=>htmlspecialchars_decode($_POST['title'], ENT_QUOTES), ':start'=>$_POST['start'], ':end'=>$_POST['end'], ':allday'=>$_POST['allday']));
			echo json_encode(array("status" => "success","event_id" => $db->lastInsertId()));
		}	
	} else {
		echo json_encode(array("status" => "failed","message" => "Unauthorized token"));
	}
} else {
	echo json_encode(array("status" => "failed","message" => "Unauthorized access"));
}
?>