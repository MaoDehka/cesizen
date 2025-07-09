<?php
################################################################################
# @Name : /core/message.php
# @Description : page to send mail
# @Call : /core/auto_mail.php 
# @parameters : $from, $to, $message, $object
# @Author : Flox
# @Create : 21/11/2012
# @Update : 08/03/2023
# @Version : 3.2.34
################################################################################

if(!$rparameters['mail']) {echo DisplayMessage('error',T_('Le connecteur SMTP est désactivé')); exit;}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
use Greew\OAuth2\Client\Provider\Azure;

//init var
$mail_send=1;
if(!isset($_GET['id'])) $_GET['id'] = '';

//functions
require_once(__DIR__.'/../core/functions.php');

//load mailer
if($rparameters['mail_auth_type']=='oauth_google' || $rparameters['mail_auth_type']=='oauth_microsoft'|| $rparameters['mail_auth_type']=='oauth_azure') {require_once __DIR__.'/../vendor/autoload.php';}
require_once(__DIR__.'/../vendor/phpmailer/phpmailer/src/PHPMailer.php');
if($rparameters['mail_auth_type']=='oauth_google' || $rparameters['mail_auth_type']=='oauth_microsoft' || $rparameters['mail_auth_type']=='oauth_azure') { require_once(__DIR__.'/../vendor/phpmailer/phpmailer/src/OAuth.php');}
require_once(__DIR__.'/../vendor/phpmailer/phpmailer/src/SMTP.php');
require_once(__DIR__.'/../vendor/phpmailer/phpmailer/src/Exception.php');
if($rparameters['mail_auth_type']=='oauth_google' || $rparameters['mail_auth_type']=='oauth_microsoft' || $rparameters['mail_auth_type']=='oauth_azure') {require_once(__DIR__.'/../vendor/phpmailer/phpmailer/src/OAuthTokenProvider.php');}

$mail = new PHPMailer;
try {
	//check imap from address service
	if($_GET['id'] && $rparameters['imap'] && $rparameters['imap_from_adr_service'] && $rparameters['imap_mailbox_service'])
	{
		$qry=$db->prepare("SELECT `mail` FROM `tparameters_imap_multi_mailbox` WHERE `service_id`=(SELECT `u_service` FROM `tincidents` WHERE `id`=:id)");
		$qry->execute(array('id' => $_GET['id']));
		$imap_svc=$qry->fetch();
		$qry->closeCursor();
		if(!empty($imap_svc['mail'])) {$from=$imap_svc['mail'];}
	}

	$mail->CharSet = 'UTF-8'; //ISO-8859-1 possible if string problems
	if($rparameters['mail_smtp_class']=='IsSendMail()') {$mail->IsSendMail();}
	if($rparameters['mail_smtp_class']=='IsSMTP()') {$mail->IsSMTP();} 
	if($rparameters['mail_secure']=='SSL') {$mail->Host = "ssl://$rparameters[mail_smtp]";}
	elseif($rparameters['mail_secure']=='TLS') {$mail->Host = "tls://$rparameters[mail_smtp]";} 
	else {$mail->Host = "$rparameters[mail_smtp]";}
	$mail->SMTPAuth = $rparameters['mail_auth'];
	if($rparameters['debug']) {$mail->SMTPDebug = 4;}
	if($rparameters['mail_auth_type']=='login') {$mail->AuthType = 'LOGIN';}
	elseif($rparameters['mail_auth_type']=='oauth_google') {$mail->AuthType = 'XOAUTH2';}
	elseif($rparameters['mail_auth_type']=='oauth_microsoft') {$mail->AuthType = 'XOAUTH2';}
	elseif($rparameters['mail_auth_type']=='oauth_azure') {$mail->AuthType = 'XOAUTH2';}
	if($rparameters['mail_auth'] && ($rparameters['mail_auth_type']=='oauth_google' || $rparameters['mail_auth_type']=='oauth_microsoft' || $rparameters['mail_auth_type']=='oauth_azure'))
	{
		if(preg_match('/gs_en/',$rparameters['mail_oauth_client_secret'])) {$rparameters['mail_oauth_client_secret']=gs_crypt($rparameters['mail_oauth_client_secret'], 'd' , $rparameters['server_private_key']);}
		if($rparameters['mail_auth_type']=='oauth_google')
		{
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			$provider = new Google(  //Create a new OAuth2 provider instance
				[
					'clientId' => $rparameters['mail_oauth_client_id'],
					'clientSecret' => $rparameters['mail_oauth_client_secret'],
				]
			);
		}
		if($rparameters['mail_auth_type']=='oauth_microsoft')
		{
			$mail->SMTPSecure = 'tls';
			$provider = new Microsoft( //Create a new OAuth2 provider instance
				[
					'clientId' => $rparameters['mail_oauth_client_id'],
					'clientSecret' => $rparameters['mail_oauth_client_secret'],
				]
			);
		}
		if($rparameters['mail_auth_type']=='oauth_azure')
		{
			$mail->SMTPSecure = 'tls';
			$provider = new Azure( //Create a new OAuth2 provider instance
				[
					'clientId' => $rparameters['mail_oauth_client_id'],
					'clientSecret' => $rparameters['mail_oauth_client_secret'],
					'tenantId' => $rparameters['mail_oauth_tenant_id'],
				]
			);
		}
		$mail->setOAuth( //Pass the OAuth provider instance to PHPMailer
			new OAuth(
				[
					'provider' => $provider,
					'clientId' => $rparameters['mail_oauth_client_id'],
					'clientSecret' => $rparameters['mail_oauth_client_secret'],
					'refreshToken' => $rparameters['mail_oauth_refresh_token'],
					'userName' => $rparameters['mail_username'],
				]
			)
		);
	} else {
		if($rparameters['mail_secure']!=0) {$mail->SMTPSecure = $rparameters['mail_secure'];} #6121

		//if($rparameters['mail_secure']!=0) {$mail->SMTPSecure = $rparameters['mail_secure'];} else {$mail->SMTPAutoTLS = false;}
	}
	if($rparameters['mail_port']!=25) {$mail->Port = $rparameters['mail_port'];} else {$mail->SMTPAutoTLS = false;} #6121
	$mail->Username = "$rparameters[mail_username]";
	if(preg_match('/gs_en/',$rparameters['mail_password'])) {$rparameters['mail_password']=gs_crypt($rparameters['mail_password'], 'd' , $rparameters['server_private_key']);}
	$mail->Password = $rparameters['mail_password'];
	$mail->IsHTML(true); 
	$mail->Timeout = 30;
	$mail->From = $from;
	$mail->FromName = htmlspecialchars_decode($rparameters['mail_from_name']);
	$mail->XMailer = '_';

	//multi address case
	if(preg_match('#;#',$to))
	{
		$to=explode(';',$to);
		foreach ($to as &$mailadr) {if($mailadr){$mail->AddAddress("$mailadr");}}
	} else { $mail->AddAddress("$to");}

	//define reply address
	if($rparameters['mail_reply']=='sender'){
		$mail_reply=$from;
	} else{
		//check connected user mail 
		$qry=$db->prepare("SELECT `mail` FROM `tusers` WHERE id=:id");
		$qry->execute(array('id' => $_SESSION['user_id']));
		$connected_user=$qry->fetch();
		$qry->closeCursor();
		if(!empty($connected_user['mail'])){$mail_reply=$connected_user['mail'];} else {$mail_reply=$rparameters['mail_from_name'];}
	} 
	if($mail_reply){$mail->AddReplyTo($mail_reply);}

	$mail->Subject = "$object";
	if ($rparameters['mail_ssl_check']==0)
	{
		$mail->smtpConnect([
		'ssl' => [
			'verify_peer' => false,
			'verify_peer_name' => false,
			'allow_self_signed' => true
			]
		]);
	}

	//detect and convert image in mail
	if(preg_match_all('/<img.*?>/', $message, $matches))
	{
		//for each images detected
		$i = 1;
		foreach ($matches[0] as $img)
		{
			//generate cid
			$cid = 'img'.($i++);
			if(strpos($img, 'base64') !== false)
			{
				if($rparameters['debug']) {echo 'DEBUG : Images base64 detected conversion ('.$img.')<br />';}
				//remove originalsrc
				$img = preg_replace('/originalsrc="(.*?)"/', '', $img);
				//keep data of current image
				preg_match('/src="(.*?)"/', $img, $m);
				//extract image parameters
				$image_data=explode(',',$m[1]);
				$image_encoding=explode(';',$image_data[0]);
				$image_type=explode(':',$image_encoding[0]);
				$message = str_replace($img, '<img alt="" src="cid:'.$cid.'" style="border: none;" />', $message); 
				if(!empty($image_data[1])){$mail->AddStringEmbeddedImage(base64_decode($image_data[1]), $cid, $cid, $image_encoding[1], $image_type[1]);} 
			} else {
				if($rparameters['debug']) {echo 'DEBUG : Images no base64 detected add EmbeddedImage('.$img.')<br />';}
				if(!preg_match('#/logo/#',$img))
				{
					$orig_filename=explode('src="',$img);
					if(!empty($orig_filename[1]))
					{
						$orig_filename=explode('"',$orig_filename[1]);
						$orig_filename=$orig_filename[0];
						if(file_exists($orig_filename))
						{
							$message=str_replace($orig_filename, 'cid:'.$cid,$message);
							$mail->AddEmbeddedImage($orig_filename,$cid,$orig_filename,'base64', 'image/png');
						}
					}
				}
			}
		}
	} 

	$mail->Body = "$message";
	$mail->send();
	$mail->SmtpClose();
} catch (Exception $e) {
	echo DisplayMessage('error',T_('Message non envoyé, vérifier les paramètres de votre connecteur SMTP').' ('.$e->getMessage().')');
	//log
	if($rparameters['log']) {LogIt('error','ERROR 29 : SMTP, '.$e->getMessage(),0);}
	$mail_send=0;
}
?>