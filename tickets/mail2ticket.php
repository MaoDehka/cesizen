<?php
################################################################################
# @Name : mail2ticket.php
# @Description : convert mail to ticket
# @call : parameters in connector tab or using an external cron job
# @parameters : 
# @Author : Flox
# @Create : 07/04/2013
# @Update : 07/12/2022
# @Version : 3.2.31
################################################################################

//check if script is executed from command line
if(php_sapi_name()=='cli') {$cmd=1;} else {$cmd=0;}

//initialize variables 
if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
$previous_time=0;

//connexion script with database parameters
require "connect.php";

//switch SQL MODE to allow empty values with latest version of MySQL
$db->exec('SET sql_mode = ""');

//load parameters table
$qry=$db->prepare("SELECT * FROM `tparameters`");
$qry->execute();
$rparameters=$qry->fetch();
$qry->closeCursor();

//locales
$_GET['lang']=$rparameters['server_language'];

define('PROJECT_DIR', realpath('./'));
define('LOCALE_DIR', PROJECT_DIR .'/locale');
define('DEFAULT_LOCALE', '($_GET[lang]');
require_once('vendor/components/php-gettext/gettext.inc');
$encoding = 'UTF-8';
$locale = (isset($_GET['lang']))? $_GET['lang'] : DEFAULT_LOCALE;
T_setlocale(LC_MESSAGES, $locale);
T_bindtextdomain($_GET['lang'], LOCALE_DIR);
T_bind_textdomain_codeset($_GET['lang'], $encoding);
T_textdomain($_GET['lang']);

//define encodage type 
header('Content-Type: text/html; charset=utf-8');

//functions
require_once('core/functions.php');

//create upload dir if not exist
if(!is_dir(__DIR__."/upload/ticket"))  {mkdir(__DIR__.'/upload/ticket/', 0777, true);}

//initialize counter
$count=0;

//access check
if(!$rparameters['imap']) {echo 'ERROR : disabled function'; die();}
if(php_sapi_name() != "cli")
{
	if(!isset($_GET['token'])) {$_GET['token']='';}
	if(!isset($_GET['key'])) {$_GET['key'] = '';}
	
	if(!empty($_GET['token'])) //call from app
	{
		//check token
		$qry=$db->prepare("SELECT `id` FROM `ttoken` WHERE `action`='mail2ticket' AND `token`=:token AND `ip`=:ip");
		$qry->execute(array('token' => $_GET['token'],'ip' => $_SERVER['REMOTE_ADDR']));
		$token_check=$qry->fetch();
		$qry->closeCursor();
		if(empty($token_check['id'])) {echo "ERROR : Wrong token"; exit;}
	} else {
		//check private key
		if($_GET['key']!=$rparameters['server_private_key']) {echo "ERROR : Wrong key, use server private key in parameter (ex: https://server/mail2ticket.php?key=MyPrivateServerKey)"; exit;}
	}
}

//display error parameter
if($rparameters['debug']) {
	ini_set('display_errors', 'On');
	error_reporting(E_ALL);
} else {
	ini_set('display_errors', 'Off');
	error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

//display title 
if($cmd) {
	echo 'GESTSUP IMAP CONNECTOR v'.$rparameters['version'].PHP_EOL;
	echo PHP_EOL;
} else {
	echo '<h4>GESTSUP IMAP CONNECTOR v'.$rparameters['version'].'</h4>';
}	

//display date time
if($cmd) {
	echo 'DATE : '.date('Y-m-d H:i:s').PHP_EOL;
} else {
	echo 'DATE : <span style="color:green">'.date('Y-m-d H:i:s').'</span><br />';
}	

if($rparameters['imap_server'])
{
	if($cmd) {
		echo 'SERVER : '.$rparameters['imap_server'].PHP_EOL;
	} else {
		echo 'SERVER : <span style="color:green">'.$rparameters['imap_server'].'</span><br />';
	}	
} else {
	if($cmd) {
		echo 'SERVER : No IMAP server detected'.PHP_EOL;
	} else {
		echo 'SERVER : <span style="color:red">No IMAP server detected</span><br /><br />';
	}	
}

//define mailbox to check
$mailboxes=array();
if($rparameters['imap_mailbox_service'])
{
	if($rparameters['imap_user']) {array_push($mailboxes, $rparameters['imap_user']);}
	$qry=$db->prepare("SELECT `id`,`mail` FROM `tparameters_imap_multi_mailbox`");
	$qry->execute();
	while($row=$qry->fetch()) 
	{
		array_push($mailboxes, $row['mail']);		
	}
	$qry->closeCursor();

	if($cmd) {
		echo 'MODE : MULTI'.PHP_EOL;
	} else {
		echo 'MODE : <span style="color:green">multi</span><br />';
	}
} else {
	array_push($mailboxes, $rparameters['imap_user']);
	if($cmd) {
		echo 'MODE : single'.PHP_EOL;
	} else {
		echo 'MODE : <span style="color:green">single</span><br />';
	}
}

//display authentication mode
if($cmd) {
	echo 'AUTHENTICATION TYPE : '.$rparameters['imap_auth_type'].PHP_EOL;
} else {
	echo 'AUTHENTICATION TYPE : <span style="color:green">'.$rparameters['imap_auth_type'].'</span><br />';
}

//check each mailboxes 
$start = microtime(true);
// START plugin  
	$section='mail2ticket';
	include('plugin.php');
// END plugin  
foreach ($mailboxes as $mailbox)
{
	
	if($cmd) {
		echo 'MAILBOX : '.$mailbox.PHP_EOL;
	} else {
		echo 'MAILBOX : <span style="color:green">'.$mailbox.'</span><br />';
	}
	if($rparameters['imap_auth_type']=='oauth_azure' || $rparameters['imap_auth_type']=='login2' || $rparameters['imap_auth_type']=='oauth_google') 
	{
		//Webklex/php-imap
		require('core/imap_oauth.php');
	} else { //basic auth
		//php-imap/php-imap
		require('core/imap_basic.php');
	}
	sleep(1); //timeout 1 seconds to limit network trafic
}

//disconnect from mailbox  #6063
if(isset($con_mailbox)) {$con_mailbox->disconnect();}

if($cmd) {
	echo 'Total '.$count.' mails received in '.round(microtime(true) - $start).' sec.'.PHP_EOL;
	echo PHP_EOL;
} else {
	echo 'Total '.$count.' mails received in '.round(microtime(true) - $start).' sec.<br />';			
}
?>