<?php
################################################################################
# @Name : index.php
# @Description : main page include all sub-pages
# @Call : 
# @Parameters : 
# @Author : Flox
# @Create : 07/03/2010
# @Update : 30/01/2024
# @Version : 3.2.47
################################################################################

//check mandatory php extension
if(!extension_loaded('pdo_mysql')) {echo 'ERROR : PHP pdo_mysql extension missing (apt install php-mysql)';exit;}

//includes core files
require('core/init_get.php');
require('core/functions.php');

//initialize variables
if(!isset($currentpage)) $currentpage = '';
if(!isset($_SERVER['HTTP_USER_AGENT'])) $_SERVER['HTTP_USER_AGENT'] = '';
if(!isset($_GET['page'])) $_GET['page'] = '';
if(!isset($_SESSION['user_id'])) $_SESSION['user_id'] = '';

//cookies initialization
session_set_cookie_params(['httponly' => true]); //secure option
if($_GET['page']!='register' && $_GET['page']!='forgot_pwd') {session_name(md5($_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']));}

session_start();

if($_GET['page']!='ticket' && $_GET['page']!='admin' && $_GET['page'] && $_GET['page']!='procedure') //avoid upload problems
{
    //avoid back problem with browser
    if(!empty($_POST) OR !empty($_FILES))
    {
        $_SESSION['bkp_post'] = $_POST;
        if(!empty($_SERVER['QUERY_STRING'])){ $currentpage .= '?' . $_SERVER['QUERY_STRING'];}
        header('Location: ' . $currentpage);
        exit;
    }
    if(isset($_SESSION['bkp_post']))
    {
        $_POST=$_SESSION['bkp_post'];
        unset($_SESSION['bkp_post']);
    }
}

//includes core files
require('core/init_post.php');

//mobile detection
if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$_SERVER['HTTP_USER_AGENT'])||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr(isset($_SERVER['HTTP_USER_AGENT']),0,4)))
{$mobile=1;} else {$mobile=0;}

//initialize variables
if(!isset($_SESSION['user_id'])) $_SESSION['user_id'] = '';
if(!isset($_SESSION['profile_id'])) $_SESSION['profile_id'] = '';
if(!isset($_SESSION['auth_logout_token'])) $_SESSION['auth_logout_token'] = '';
if(!isset($keywords)) $keywords = '';
if(!isset($ruser['skin'])) $ruser['skin'] = '';

//default values
if(empty($_GET['page'])) $_GET['page'] = 'dashboard';
if(!isset($_GET['userid'])) $_GET['userid'] = $_SESSION['user_id'];
$ticket_observer=0;

//redirect to home page on log-off
if($_GET['action']=='logout')
{
	//include plugin
	$section='logout';
	include('plugin.php');

	$_SESSION = array();
	session_destroy();
	session_unset();
	session_start();
}

//init session variables
if(!isset($_SESSION['user_id'])) $_SESSION['user_id'] = '';
if(!isset($_SESSION['LAST_ACTIVITY'])) $_SESSION['LAST_ACTIVITY'] = 0;

//detect https connection
if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {$http='https';} else {$http='http';}

//detect web server
if(preg_match('#Microsoft-IIS#is', $_SERVER["SERVER_SOFTWARE"])) {$webserver='IIS';} else {$webserver='Apache';}

//redirect to install directory
if(preg_match('#db_name=\'\'#',file_get_contents('connect.php')))
{
	if(is_dir('install')) {echo "<SCRIPT LANGUAGE='JavaScript'>function redirect(){window.location='install'} setTimeout('redirect()',0);</SCRIPT>"; exit;}
}

//connexion script with database parameters
require_once('connect.php');

//switch SQL MODE to allow empty values
$db->exec('SET sql_mode = ""');

$db_userid=strip_tags($db->quote($_GET['userid']));
$db_id=strip_tags($db->quote($_GET['id']));

//load parameters table
$qry=$db->prepare("SELECT * FROM `tparameters`");
$qry->execute();
$rparameters=$qry->fetch();
$qry->closeCursor();

//include plugin
$section='index';
include('plugin.php');

//log off on timeout
if($rparameters['timeout'])
{
	if($rparameters['debug']) {$session_time='time='.(time() - $_SESSION['LAST_ACTIVITY']).'max='.(60*$rparameters['timeout']);}
	if($_SESSION['LAST_ACTIVITY'] && (time() - $_SESSION['LAST_ACTIVITY'] > 60*$rparameters['timeout'])) {
		session_unset();    
		session_destroy();
		if(!isset($_SESSION['user_id'])) $_SESSION['user_id'] = '';
		if(!isset($_SESSION['LAST_ACTIVITY'])) $_SESSION['LAST_ACTIVITY'] = '';
	}
	if($_GET['page']=='dashboard' && $rparameters['auto_refresh']!=0 ) {} else {$_SESSION['LAST_ACTIVITY'] = time();}
	if(!$_SESSION['LAST_ACTIVITY']) {$_SESSION['LAST_ACTIVITY'] = time();}
} elseif($rparameters['auto_refresh']!=0) {
	$maxlifetime = ini_get("session.gc_maxlifetime");
	if($rparameters['debug']) {$session_time='time='.(time() - $_SESSION['LAST_ACTIVITY']).'max='.$maxlifetime;}
	if($_SESSION['LAST_ACTIVITY'] && (time() - $_SESSION['LAST_ACTIVITY'] > $maxlifetime)) {
		session_unset();    
		session_destroy();
		if(!isset($_SESSION['user_id'])) $_SESSION['user_id'] = '';
		if(!isset($_SESSION['LAST_ACTIVITY'])) $_SESSION['LAST_ACTIVITY'] = '';
	}
	if($_GET['page']!='dashboard') {$_SESSION['LAST_ACTIVITY'] = time();}
	if(!$_SESSION['LAST_ACTIVITY']) {$_SESSION['LAST_ACTIVITY'] = time();}
}

//define timezone
if($rparameters['server_timezone']) {date_default_timezone_set($rparameters['server_timezone']);}

//load common variables
$daydate=date('Y-m-d');
$datetime=date("Y-m-d H:i:s");

//display error parameter
if($rparameters['debug']) {
	ini_set('display_errors', 'On');
	ini_set('display_startup_errors', 'On');
	ini_set('html_errors', 'On');
	error_reporting(E_ALL);
	$start_time = microtime(TRUE);
} else {
	ini_set('display_errors', 'Off');
	ini_set('display_startup_errors', 'Off');
	ini_set('html_errors', 'Off');
	error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
}

//auth token using in azure ad and saml2 plugin
if($_GET['auth_token']) //login if auth token exist
{
	//check token
	$qry=$db->prepare("SELECT `user_id`,`token` FROM `ttoken` WHERE token=:token");
	$qry->execute(array('token' => $_GET['auth_token']));
	$gs_token=$qry->fetch();
	$qry->closeCursor();
	if(!empty($gs_token['user_id']))
	{
		$_SESSION['user_id']=$gs_token['user_id'];
		$_SESSION['auth_logout_token']=$_GET['auth_token'];

		//update last login date
		$qry=$db->prepare("UPDATE `tusers` SET `last_login`=:last_login,`ip`=:ip WHERE `id`=:id");
		$qry->execute(array('last_login' => date('Y-m-d H:i:s'),'ip' => $_SERVER['REMOTE_ADDR'],'id' => $_SESSION['user_id']));

		//remove token
		$qry=$db->prepare("DELETE FROM `ttoken` WHERE `action`='auth_token'");
		$qry->execute();

		//redirect to application
		echo '<script language="Javascript">document.location.replace("'.$rparameters['server_url'].'/index.php?page=dashboard&userid='.$gs_token['user_id'].'&state=%25");</script>';
	} else {
		exit('ERROR : GestSup authentication token not found');
	}
}

//azure ad sso
if($rparameters['azure_ad_sso'])
{
	if(!empty($_GET['local_auth'])) { 
		//GestSup auth
	} elseif($_GET['action']=='logout') { //azure logout
		//check token
		$qry=$db->prepare("SELECT `id`,`token` FROM `ttoken` WHERE `token`=:token AND `action`='azure_auth'");
		$qry->execute(array('token' => $_GET['token']));
		$gs_auth_token=$qry->fetch();
		$qry->closeCursor();
	   
		if(!empty($gs_auth_token['id']))
		{
			//remove auth token
			$qry=$db->prepare("DELETE FROM `ttoken` WHERE token=:token");
			$qry->execute(array('token' => $gs_auth_token['token']));
			echo '<script language="Javascript">document.location.replace("https://login.microsoftonline.com/common/oauth2/v2.0/logout");</script>';
		}
	} 
}

//if user is connected
if($_SESSION['user_id'])
{
	//load variables
	$uid=$_SESSION['user_id'];
	
	//load user table
	$qry=$db->prepare("SELECT * FROM `tusers` WHERE id=:id");
	$qry->execute(array('id' => $_SESSION['user_id']));
	$ruser=$qry->fetch();
	$qry->closeCursor();

	if(!isset($ruser['profile'])) {
		echo 'ERROR : Unable to load user profile, clean your browser cache ('.$_SESSION['user_id'].')';
		LogIt('error','ERROR 1 : Unable to load user profile ('.$_SESSION['user_id'].')',0);
		exit;
	}
	
	//find profile id of connected user 
	$_SESSION['profile_id']=$ruser['profile'];

	//load rights table
	$qry=$db->prepare("SELECT * FROM `trights` WHERE profile=:profile");
	$qry->execute(array('profile' => $_SESSION['profile_id']));
	$rright=$qry->fetch();
	$qry->closeCursor();
	
	//set role name of profile
	if($_SESSION['profile_id']==0)	{$profile="technician";}
	elseif($_SESSION['profile_id']==1)	{$profile="user";}
	elseif($_SESSION['profile_id']==4)	{$profile="technician";}
	elseif($_SESSION['profile_id']==3) {$profile="user";}
	else {$profile="user";}
}

//define current language
require "localization.php";

//check php version
if(phpversion()<8) {echo DisplayMessage('error', T_('Version de PHP non supportée, veuillez mettre à jour votre version de PHP'));};

//put keywords in variable
if($_POST['keywords']||$_GET['keywords']) {
	$keywords="$_GET[keywords]$_POST[keywords]";
	$keywords=htmlspecialchars($keywords, ENT_QUOTES, 'UTF-8');
} else {$keywords='';}
if($_POST['userkeywords']||$_GET['userkeywords']) {
	$userkeywords="$_GET[userkeywords]$_POST[userkeywords]"; 
	$userkeywords=htmlspecialchars($userkeywords, ENT_QUOTES, 'UTF-8');
} else {$userkeywords='';}
if($_POST['assetkeywords']||$_GET['assetkeywords']) {
	$assetkeywords="$_GET[assetkeywords]$_POST[assetkeywords]";
	$assetkeywords=htmlspecialchars($assetkeywords, ENT_QUOTES, 'UTF-8');
} else {$assetkeywords='';}
if($_POST['rightkeywords']||$_GET['rightkeywords']) {
	$rightkeywords="$_GET[rightkeywords]$_POST[rightkeywords]";
	$rightkeywords=htmlspecialchars($rightkeywords, ENT_QUOTES, 'UTF-8');
} else {$rightkeywords='';}
if($_POST['procedurekeywords']||$_GET['procedurekeywords']) {
	$procedurekeywords="$_GET[procedurekeywords]$_POST[procedurekeywords]";
	$procedurekeywords=htmlspecialchars($procedurekeywords, ENT_QUOTES, 'UTF-8');
} else {$procedurekeywords='';}
if($_POST['listkeywords']||$_GET['listkeywords']) {
	$listkeywords="$_GET[listkeywords]$_POST[listkeywords]";
	$listkeywords=htmlspecialchars($listkeywords, ENT_QUOTES, 'UTF-8');
} else {$listkeywords='';}
if($_POST['logkeywords']||$_GET['logkeywords']) {
	$logkeywords="$_GET[logkeywords]$_POST[logkeywords]";
	$logkeywords=htmlspecialchars($logkeywords, ENT_QUOTES, 'UTF-8');
} else {$logkeywords='';}

//download backup file
if($_GET['download_backup'] && $rright['admin'] && $_SESSION['user_id']) {header("location: ./backup/$_GET[download_backup]");}

//download configuration
if($_GET['action']=='download_configuration' && $rright['admin'] && $_SESSION['user_id'] && $_GET['download_file']) {header("location: ./$_GET[download_file]"); }

//download attachment file
if($_GET['download'] && $_SESSION['user_id']) {require('core/download.php'); exit;}

//include plugin
$section='download';
include('plugin.php');

?>
<!doctype html>
<html lang="fr" style="--scrollbar-width:17px; --moz-scrollbar-thin:17px; font-size: 0.925rem;">
	<head>
		<meta charset="utf-8">
		<meta name="theme-color" content="#4aa0df">	
		<?php 
			if($_SESSION['user_id'] && $rparameters['auto_refresh'] && $_GET['page']=='dashboard' && !$_POST['keywords'])
			{
				echo '<meta http-equiv="Refresh" content="'.$rparameters['auto_refresh'].';">'; 
			}
			header('x-ua-compatible: ie=edge'); //disable ie compatibility mode 
			header('X-Frame-Options: deny'); //security options
			header('X-Content-Type-Options: nosniff'); //security options
		?>
		<title>GestSup | <?php echo T_('Gestion de Support'); ?></title>
		
		<?php
		$favicon='images/favicon_ticket.png'; 
		if($_GET['page']=='asset_list' || $_GET['page']=='asset' || $_GET['page']=='asset_stock') {$favicon='images/favicon_asset.png';} 
		elseif($_GET['page']=='procedure') {$favicon='images/favicon_procedure.png';} 
		elseif($_GET['page']=='calendar') {$favicon='images/favicon_planning.png';} 
		elseif($_GET['page']=='stat') {$favicon='images/favicon_stat.png';} 
		elseif($_GET['page']=='admin') {$favicon='images/favicon_admin.png';} 
		elseif($_GET['page']=='project') {$favicon='images/favicon_project.png';} 
		elseif($_GET['page']=='contract') {$favicon='images/favicon_contract.png';} 
		//include plugin
		$section='favicon';
		include('plugin.php');
		echo '<link rel="shortcut icon" type="image/png" href="'.$favicon.'">';
		?>
		<meta name="description" content="gestsup" />
		<meta name="robots" content="noindex, nofollow">
		<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
		
		<!-- CSP -->
		<!-- <meta http-equiv="Content-Security-Policy" content=" default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';" /> -->
		
		<!-- bootstrap styles -->
		<link rel="stylesheet" href="./vendor/components/bootstrap/dist/css/bootstrap.min.css" />
		
		<!-- fontawesome styles -->
		<link rel="stylesheet" type="text/css" href="./vendor/fortawesome/font-awesome/css/fontawesome.min.css">
		<link rel="stylesheet" type="text/css" href="./vendor/fortawesome/font-awesome/css/solid.min.css">
		
		<?php 
		//add special css for selected page
		if($_GET['page']=='ticket' || $_GET['page']=='asset' || $_GET['page']=='test' || $_GET['table']=='tcompany' || ($_GET['page']=='dashboard' && $_GET['view']=='activity')) 
		{
			echo '
			<!-- datetimepicker styles -->
			<link rel="stylesheet" href="vendor/components/tempusdominus/bootstrap-4/build/css/tempusdominus-bootstrap-4.min.css" />
			';
		}
		if(($_GET['page']=='ticket') || ($_GET['page']=='asset') || ($_GET['page']=='dashboard') || ($_GET['page']=='admin/user') || ($_GET['subpage']=='user')) 
		{
			echo '
			<!-- selectize styles -->
			<link rel="stylesheet" type="text/css" href="vendor/components/selectize/css/selectize.bootstrap4.css" />
			<style>
				.selectize-control {display: inline-block;}
				';
				//specific css properties by page
				if($_GET['page']=='dashboard') {echo '.selectize-dropdown {width: max-content !important; text-align:left !important;}';}
				if($_GET['page']=='ticket') {echo '.selectize-input {padding-left:7px !important; padding-right:30px !important; min-width: 269px !important; }';}
				echo '
			</style>
			';
		}
		if(($_GET['page']=='admin' && ($_GET['subpage']=='parameters' || $_GET['subpage']=='list')) || $_GET['subpage']=='user')
		{
			echo '
			<!-- colorpicker styles -->
			<link rel="stylesheet" href="./vendor/components/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css" >
			';
		}
		?>
		<!-- ace styles -->
		<link rel="stylesheet" type="text/css" href="./template/ace/dist/css/ace-font.css" />
		<link rel="stylesheet" type="text/css" href="./template/ace/dist/css/ace.min.css" />
		<link rel="stylesheet" type="text/css" href="./template/ace/dist/css/ace-themes.min.css" />
		
		<!-- fix pasting data from MS soft -->
		<style> .MsoPlainText {line-height: 0.50;}</style>
		
		<!-- dark theme styles -->
		<?php 
		if($ruser['skin']=='skin-4') {echo '<link rel="stylesheet" type="text/css" href="./template/ace/dist/css/gestsup-dark.min.css" />';}
		?>
	
		<!-- JQuery script -->
		<script type="text/javascript" src="./vendor/components/jquery/jquery.min.js"></script>
	</head>
	<body>
	
	<?php
		//dark theme styles
		if($ruser['skin']=='skin-4') {
			echo '<form><input type="hidden" id="theme" name="theme" value="dark" /></form>';
		} else {
			echo '<form><input type="hidden" id="theme" name="theme" value="light" /></form>';
		}
		//logon user case
		if($_SESSION['user_id']) 
		{
			require('main.php');
		} else { //not login
			//launch cron
			if($rparameters['cron_daily']!=date('Y-m-d')) {require('./core/cron_daily.php');}
			if($rparameters['cron_monthly']!=date('m')) {require('./core/cron_monthly.php');}
			
			//check restrict IP access
			if($rparameters['restrict_ip'])
			{
				$ipcheck=0;
				$allow_ip=explode(',',$rparameters['restrict_ip']);
				foreach($allow_ip as $ip)
				{
					if(preg_match("#$ip#", $_SERVER['REMOTE_ADDR'])) {$ipcheck=1;}
					//allow localhost
					if(preg_match("#127.0.0.1#", $_SERVER['REMOTE_ADDR'])) {$ipcheck=1;}
					if(preg_match("#localhost#", $_SERVER['REMOTE_ADDR'])) {$ipcheck=1;}
					if(preg_match("#fe80::#", $_SERVER['REMOTE_ADDR'])) {$ipcheck=1;}
					if(preg_match("#::1#", $_SERVER['REMOTE_ADDR'])) {$ipcheck=1;}
				}
				if($ipcheck==0) {echo DisplayMessage('error',T_("Vous n'avez pas accès à ce logiciel.").' ('.$_SERVER['REMOTE_ADDR'].')');}
			} else {$ipcheck=1;}
			
			//check SSO
			if($rparameters['ldap_sso'] && isset($_SERVER['REMOTE_USER']) && $_GET['action']!='logout' && $ipcheck==1)
			{
				require('core/sso.php');
			} elseif($ipcheck==1) {
				if($_GET['page']=='register') {require('register.php');}
				elseif($_GET['page']=='forgot_pwd') {require('forgot_pwd.php');}
				else {require('login.php');}
			}
		}
		//loading js scripts
		echo'
			<!-- popper using in wysiwyg and datetimepicker -->
			<script type="text/javascript" src="./vendor/components/popper-js/dist/umd/popper.min.js"></script>
			<script type="text/javascript" src="./vendor/components/bootstrap/dist/js/bootstrap.min.js"></script>
			<script type="text/javascript" src="./template/ace/dist/js/ace.min.js"></script>
		';
		
		if($_SESSION['user_id'])
		{
			
			//include specific script for page
			if($_GET['page']=='ticket' || $_GET['page']=='procedure'|| $_GET['subpage']=='parameters') {include ('./wysiwyg.php');}
			if(($_GET['page']=='admin' && ($_GET['subpage']=='parameters' || $_GET['subpage']=='list')) || $_GET['subpage']=='user') {echo '<script type="text/javascript" src="./vendor/components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>';}
			
			//selectize script
			if(($_GET['page']=='ticket') || ($_GET['page']=='asset') || ($_GET['page']=='dashboard') || ($_GET['page']=='admin/user') || ($_GET['subpage']=='user')) 
			{
				echo '
				<script type="text/javascript" src="vendor/components/selectize/js/selectize.min.js"></script>
				<script>
					$(\'#user\').selectize({normalize: true});
					$(\'#location\').selectize({normalize: true});
					$(\'#company\').selectize({normalize: true});
					$(\'#observer\').selectize({normalize: true});
					';
					if($_GET['page']=='dashboard'){
						echo '$(\'#technician\').selectize({normalize: true});';
						echo '$(\'#company\').selectize({normalize: true});';
						echo '$(\'#sender_service\').selectize({normalize: true});';
						echo '$(\'#type\').selectize({normalize: true});';
						echo '$(\'#category\').selectize({normalize: true});';
						echo '$(\'#subcat\').selectize({normalize: true});';
						echo '$(\'#asset\').selectize({normalize: true});';
						echo '$(\'#place\').selectize({normalize: true});';
						echo '$(\'#service\').selectize({normalize: true});';
						echo '$(\'#agency\').selectize({normalize: true});';
						echo '$(\'#priority\').selectize({normalize: true});';
						echo '$(\'#criticality\').selectize({normalize: true});';
						echo '$(\'#state\').selectize({normalize: true});';
					} 
				
					//style
					if($ruser['skin']=='skin-4')
					{
						echo '$(\'.selectize\').css("background-color","#33414a");';
						echo '$(\'.selectize\').css("color","#99a0a5");';
						echo '$(\'.selectize-input\').css("background-color","#33414a");';
						echo '$(\'.selectize-input\').css("color","#99a0a5");';
						echo '$(\'.selectize-dropdown\').css("background-color","#33414a");';
						echo '$(\'.selectize-dropdown\').css("color","#99a0a5");';
						echo '$(\'.selectize-input input\').css("color","#99a0a5");';
					} 
					echo '
			  	</script>
				';
			}
			
			//log off popup 500000
			if(!$rparameters['ldap_sso'])
			{
				if($rparameters['timeout']) {$timeout=$rparameters['timeout']*60000;} else {$timeout=ini_get("session.gc_maxlifetime")*1000;}
				if($timeout>9000000000) {$timeout='9000000000';} #3661 bug
				if(!isset($_SESSION['auth_logout_token'])) $_SESSION['auth_logout_token']='';
				echo '
					<script type="text/javascript">
						setInterval(function(){
							window.alert("'.T_('Session expirée').'");
							window.location.href="index.php?action=logout&token='.$_SESSION['auth_logout_token'].'";
						},'.$timeout.');
					</script>
				';
			}
			
			//call reminder popup
			include "./reminder.php"; 
			
			//call pwd switch popup
			if($ruser['chgpwd']){include "./modify_pwd.php";}
			if($rparameters['user_password_policy'] && $rparameters['user_password_policy_expiration']!=0)
			{
				$password_expiration_date=date('Y-m-d', strtotime($ruser['last_pwd_chg']. ' + '.$rparameters['user_password_policy_expiration'].' days'));
				if($password_expiration_date < date('Y-m-d') && $ruser['last_pwd_chg']!='0000-00-00') {include "./modify_pwd.php";}
			}

			//display admin message popup
			if(isset($_SESSION['profile_id']) && $_SESSION['profile_id']==4 && $rparameters['admin_message_alert'])
			{
				echo '
				<script type="text/javascript">
					$.aceToaster.add({
						placement: \'tc\',
						title: \''.$rparameters['admin_message_alert'].'\',
						body: \'\',
						icon: \'<i class="text-danger mr-2 text-130"><i class="fas fa-exclamation-triangle mt-1 fa-2x text-danger"></i></i>\',
						iconClass: \'\',
						delay: 2000,
						closeClass: \'btn btn-light-danger border-0 btn-bgc-tp btn-xs px-2 py-0 text-150 position-tr mt-n25\',
						className: \'bgc-white-tp1 border-none border-t-4 brc-danger-tp1 rounded-sm pl-3 pr-1\',
						headerClass: \'bg-transparent border-0 text-120 text-danger-d3 font-bolder mt-3\',
						bodyClass: \'pt-4 pb-0 text-105\'
					})
				</script>
				';
			}

			//display time to execute
			if($rparameters['debug'])
			{
				$end_time = microtime(TRUE);
				printf('<div class="text-right">Page loaded in %f seconds</div>', $end_time - $start_time );
			}
		}
		//close database access
		$db = null;
		?>
	</body> 
</html>