<?php
################################################################################
# @Name : profile_update.php
# @Description : update right on profile
# @Call : ./admin/profile.php
# @Parameters :  
# @Author : Flox
# @Create : 24/01/2020
# @Update : 17/01/2024
# @Version : 3.2.47
################################################################################

//init and secure var
require_once(__DIR__.'/../core/init_post.php');
require_once(__DIR__.'/../core/init_get.php');

//check numeric var
if(!is_numeric($_GET['profile'])) {header('HTTP/1.0 403 Forbidden'); exit;}
if(!is_numeric($_GET['enable'])) {header('HTTP/1.0 403 Forbidden'); exit;}
if(!is_numeric($_GET['user_id'])) {header('HTTP/1.0 403 Forbidden'); exit;}

//initialize variables 
if(!isset($_GET['token'])) $_GET['token']=''; 

//secure data
$_GET['right']=preg_replace('/[^A-Za-z_]/', '', $_GET['right']);

//db connect
require('../connect.php');

//load token table
$qry=$db->prepare("SELECT `id` FROM ttoken WHERE `action`='admin_profile_access' AND `token`=:token AND `user_id`=:user_id AND `ip`=:ip");
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
		if(!in_array($profile, $whitelist_profile,true)) {echo "ERROR : wrong profile"; exit;}
		$whitelist_enable = array('0', '1', '2');
		if(!in_array($enable, $whitelist_enable,true)) {echo "ERROR : wrong enable"; exit;}
		$whitelist_rights=array();
		$qry=$db->prepare("SHOW COLUMNS FROM trights");
		$qry->execute();
		while($row=$qry->fetch()) {array_push($whitelist_rights, $row['0']);}
		$qry->closeCursor();
		if(!in_array($right, $whitelist_rights,true)) {echo "ERROR : wrong right"; exit;} 

		//update right function with prepared query
		function UpdateRight($right,$enable,$profile) 
		{
			global $db;

			//update right
			$qry="UPDATE `trights` SET `$right`=:enable WHERE `profile`=:profile";
			$qry=$db->prepare($qry);
			$qry->execute(array('enable' => $enable,'profile' => $profile));
		}

		//check if right exist in trights table before start update
		$qry=$db->prepare("SHOW COLUMNS FROM `trights` WHERE `Field`=:field");
		$qry->execute(array('field' => $right));
		$row=$qry->fetch();
		$qry->closeCursor();
		if(isset($row[0])){UpdateRight($right,$enable,$profile);}
	
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