<?php
################################################################################
# @Name : r.php
# @Description : add and modify user
# @Call : ./admin/profile.php
# @Parameters :  
# @Author : Flox
# @Create : 24/01/2020
# @Update : 01/09/2022
# @Version : 3.2.35 p1
################################################################################

//initialize variables 
if(!isset($_GET['token'])) $_GET['token']=''; 

//secure data
$_GET['token']=htmlspecialchars($_GET['token'], ENT_QUOTES, 'UTF-8');
$_GET['right']=htmlspecialchars($_GET['right'], ENT_QUOTES, 'UTF-8');
$_GET['profile']=htmlspecialchars($_GET['profile'], ENT_QUOTES, 'UTF-8');
$_GET['enable']=htmlspecialchars($_GET['enable'], ENT_QUOTES, 'UTF-8');
$_GET['user_id']=htmlspecialchars($_GET['user_id'], ENT_QUOTES, 'UTF-8');

//db connect
require('../connect.php');

//load token table
$qry=$db->prepare("SELECT `id` FROM ttoken WHERE action='admin_profile_access' AND token=:token AND `user_id`=:user_id AND `ip`=:ip");
$qry->execute(array('token' => $_GET['token'],'user_id' => $_GET['user_id'],'ip' => $_SERVER['REMOTE_ADDR']));
$token=$qry->fetch();
$qry->closeCursor();

if($token) 
{
	if(isset($_GET['right']) && !empty($_GET['right']))
	{
		$right = $_GET['right'];
		$profile = $_GET['profile'];
		$enable = $_GET['enable'];

		//whitelist check
		$whitelist_profile = array('0', '1', '2', '3', '4');
		if(!in_array($profile, $whitelist_profile)) {echo "ERROR : Wrong profile"; exit;}
		$whitelist_enable = array('0', '1', '2');
		if(!in_array($enable, $whitelist_enable)) {echo "ERROR : Wrong enable"; exit;}
		$whitelist_rights=array();
		$qry=$db->prepare("SHOW COLUMNS FROM trights");
		$qry->execute();
		while($row=$qry->fetch()) {array_push($whitelist_rights, $row['0']);}
		$qry->closeCursor();
		if(!in_array($right, $whitelist_rights)) {echo "ERROR : Wrong right"; exit;} 

		//update
		$db->exec("UPDATE `trights` SET `$right`='$enable' WHERE `profile`='$profile'");

		//load parameters table
		$qry=$db->prepare("SELECT * FROM `tparameters`");
		$qry->execute();
		$rparameters=$qry->fetch();
		$qry->closeCursor();
		//log
		if($rparameters['log'])
		{
			require_once('../core/functions.php');

			if($profile==0) {$profile='technician';}
			if($profile==1) {$profile='poweruser';}
			if($profile==2) {$profile='user';}
			if($profile==3) {$profile='supervisor';}
			if($profile==4) {$profile='admin';}
			if($right=='admin' && $enable=='2')
			{
				logit('security', "Admin right added to new profile",$_GET['user_id']);
			} elseif($enable=='2') {
				logit('security', 'Right "'.$right.'" has been added for profile '.$profile,$_GET['user_id']);
			} else {
				logit('security', 'Right "'.$right.'" has been removed for profile '.$profile,$_GET['user_id']);
			}
		}
	}
} else {
	echo "ERROR : wrong token";
}
?>