<?php
################################################################################
# @Name : get_auth_token.php
# @Description : generate and store token for MS and Google Oauth Provider
# @Call : 
# @Parameters : 
# @Author : Flox
# @Create : 01/06/2022
# @Update : 08/04/2023
# @Version : 3.2.34
################################################################################

namespace PHPMailer\PHPMailer;

//db connexion
require_once(__DIR__.'/connect.php');

//db connexion
require_once(__DIR__.'/core/functions.php');

//get db parameters
$db->exec('SET sql_mode = ""');
$qry=$db->prepare("SELECT * FROM `tparameters`");
$qry->execute();
$rparameters=$qry->fetch();
$qry->closeCursor();

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

/**
 * Aliases for League Provider Classes
 * Make sure you have added these to your composer.json and run `composer install`
 * Plenty to choose from here:
 * @see http://oauth2-client.thephpleague.com/providers/thirdparty/
 */
//@see https://github.com/thephpleague/oauth2-google
use League\OAuth2\Client\Provider\Google;
//@see https://packagist.org/packages/hayageek/oauth2-yahoo
use Hayageek\OAuth2\Client\Provider\Yahoo;
//@see https://github.com/stevenmaguire/oauth2-microsoft
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
//@see https://github.com/greew/oauth2-azure-provider
use Greew\OAuth2\Client\Provider\Azure;

//php-imap
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;

require (__DIR__.'/vendor/autoload.php');

if (!isset($_GET['code']) && !isset($_GET['provider'])) {
    ?>
<html>
<body>Select Provider:<br>
<a href='?provider=smtp_azure'>SMTP - Azure</a><br>
<a href='?provider=imap_azure'>IMAP - Azure</a><br>
<a href='?provider=smtp_google'>SMTP - Google</a><br>
<a href='?provider=imap_google'>IMAP - Google</a><br>
<a href='?provider=Yahoo'>Yahoo</a><br>
<a href='?provider=Microsoft'>Microsoft</a><br>
</body>
</html>
    <?php
    exit;
}

session_start();

$providerName = '';
$mailboxid = '';

if (array_key_exists('provider', $_GET)) {
    $providerName = $_GET['provider'];
    $_SESSION['provider'] = $providerName;
} elseif (array_key_exists('provider', $_SESSION)) {
    $providerName = $_SESSION['provider'];
}
//keep mailboxid
if (array_key_exists('mailboxid', $_GET)) {
    $mailboxid = $_GET['mailboxid'];
    $_SESSION['mailboxid'] = $mailboxid;
} elseif (array_key_exists('mailboxid', $_SESSION)) {
    $mailboxid = $_SESSION['mailboxid'];
}

if (!in_array($providerName, ['Google', 'Microsoft', 'Yahoo', 'smtp_google', 'smtp_azure', 'imap_azure', 'imap_google', 'imap_google_service', 'imap_azure_service'],true)) {
    exit('Only Google, Microsoft and Yahoo OAuth2 providers are currently supported in this script.');
}

//These details are obtained by setting up an app in the Google developer console,
//or whichever provider you're using.

//If this automatic URL doesn't work, set it yourself manually to the URL of this script
//$redirectUri = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$redirectUri = $rparameters['server_url'].'/get_oauth_token.php';

if($providerName=='imap_azure')
{
    $clientId = $rparameters['imap_oauth_client_id'];
    if(preg_match('/gs_en/',$rparameters['imap_oauth_client_secret'])) {$rparameters['imap_oauth_client_secret']=gs_crypt($rparameters['imap_oauth_client_secret'], 'd' , $rparameters['server_private_key']);}
    $clientSecret = $rparameters['imap_oauth_client_secret'];
    $tenantId = $rparameters['imap_oauth_tenant_id'];
}elseif($providerName=='imap_google') 
{
    $clientId = $rparameters['imap_oauth_client_id'];
    if(preg_match('/gs_en/',$rparameters['imap_oauth_client_secret'])) {$rparameters['imap_oauth_client_secret']=gs_crypt($rparameters['imap_oauth_client_secret'], 'd' , $rparameters['server_private_key']);}
    $clientSecret = $rparameters['imap_oauth_client_secret'];
}elseif($providerName=='imap_google_service' && $mailboxid) //mailbox google service case
{
    //get auth parameters for current mailbox service
    $qry=$db->prepare("SELECT `mail`,`mailbox_service_oauth_client_id`,`mailbox_service_oauth_client_secret` FROM `tparameters_imap_multi_mailbox` WHERE id=:id");
    $qry->execute(array('id' => $mailboxid));
    $service_mailbox=$qry->fetch();
    $qry->closeCursor();
    if(preg_match('/gs_en/',$service_mailbox['mailbox_service_oauth_client_secret'])) {$service_mailbox['mailbox_service_oauth_client_secret']=gs_crypt($service_mailbox['mailbox_service_oauth_client_secret'], 'd' , $rparameters['server_private_key']);}

    $clientId = $service_mailbox['mailbox_service_oauth_client_id'];
    $clientSecret = $service_mailbox['mailbox_service_oauth_client_secret'];
}elseif($providerName=='imap_azure_service' && $mailboxid) //mailbox google service case
{
    //get auth parameters for current mailbox service
    $qry=$db->prepare("SELECT `mail`,`mailbox_service_oauth_client_id`,`mailbox_service_oauth_client_secret`,`mailbox_service_oauth_tenant_id` FROM `tparameters_imap_multi_mailbox` WHERE id=:id");
    $qry->execute(array('id' => $mailboxid));
    $service_mailbox=$qry->fetch();
    $qry->closeCursor();
    if(preg_match('/gs_en/',$service_mailbox['mailbox_service_oauth_client_secret'])) {$service_mailbox['mailbox_service_oauth_client_secret']=gs_crypt($service_mailbox['mailbox_service_oauth_client_secret'], 'd' , $rparameters['server_private_key']);}

    $clientId = $service_mailbox['mailbox_service_oauth_client_id'];
    $clientSecret = $service_mailbox['mailbox_service_oauth_client_secret'];
    $tenantId = $service_mailbox['mailbox_service_oauth_tenant_id'];
} else {
    if(preg_match('/gs_en/',$rparameters['mail_oauth_client_secret'])) {$rparameters['mail_oauth_client_secret']=gs_crypt($rparameters['mail_oauth_client_secret'], 'd' , $rparameters['server_private_key']);}
    $clientId = $rparameters['mail_oauth_client_id'];
    $clientSecret = $rparameters['mail_oauth_client_secret'];
    $tenantId = $rparameters['mail_oauth_tenant_id'];
}

$params = [
    'clientId' => $clientId,
    'clientSecret' => $clientSecret,
    'redirectUri' => $redirectUri,
    'accessType' => 'offline'
];

$options = [];
$provider = null;

switch ($providerName) {
    case 'smtp_google':
        $provider = new Google($params);
        $options = [
            'scope' => [
                'https://mail.google.com/'
            ]
        ];
        break;

    case 'Yahoo':
        $provider = new Yahoo($params);
        break;

    case 'Microsoft':
        $provider = new Microsoft($params);
        $options = [
            'scope' => [
                'wl.imap',
                'wl.offline_access'
            ]
        ];
        break;

    case 'smtp_azure':
        $params['tenantId'] = $tenantId;
        $provider = new Azure($params);
        $options = [
            'scope' => [
                'https://outlook.office.com/SMTP.Send',
                'offline_access'
            ]
        ];
        break;

    case 'imap_azure':
        $params['tenantId'] = $tenantId;
        $provider = new Azure($params);
        $options = [
            'scope' => [
                'https://outlook.office.com/IMAP.AccessAsUser.All',
                'offline_access',
                'email',
                'openid',
                'profile',
                'User.Read',
            ]
        ];
        break; 

    case 'imap_google':
        $provider = new Google($params);
        $options = [
            'scope' => [
                'https://mail.google.com/'
            ]
        ];
        break;

    case 'imap_google_service':
        $provider = new Google($params);
        $options = [
            'scope' => [
                'https://mail.google.com/'
            ]
        ];
        break;

    case 'imap_azure_service':
        $params['tenantId'] = $tenantId;
        $provider = new Azure($params);
        $options = [
            'scope' => [
                'https://outlook.office.com/IMAP.AccessAsUser.All',
                'offline_access',
                'email',
                'openid',
                'profile',
                'User.Read',
            ]
        ];
        break;
}

if (null === $provider) {
    exit('Provider missing');
}

if (!isset($_GET['code'])) {
    //If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl($options);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl.'&mailboxid=11');
    exit;
    //Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    unset($_SESSION['provider']);
    exit('Invalid state');
} else {
    unset($_SESSION['provider']);
    //Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken(
        'authorization_code',
        [
            'code' => $_GET['code']
        ]
    );
 
    $AccessToken=$token;
    $RefreshToken=$token->getRefreshToken();

    echo '<h2>PROVIDER : '.$providerName.' v'.$rparameters['version'].'</h2>';

    if($providerName=='imap_azure' || $providerName=='imap_google')  //imap connector
    {
        if($RefreshToken)
        {
            $qry=$db->prepare("UPDATE `tparameters` SET `imap_oauth_refresh_token`=:imap_oauth_refresh_token");
            $qry->execute(array('imap_oauth_refresh_token' => $RefreshToken));
            echo '<h4>IMAP RefreshToken added to GestSup database :</h4>'.$RefreshToken; 
        }
        if($AccessToken) {echo '<hr /><h4>IMAP AccessToken  :</h4>'.$AccessToken; }
    }elseif($providerName=='smtp_azure' || $providerName=='smtp_google')  //smtp connector
    {
        if($RefreshToken)
        {
            $qry=$db->prepare("UPDATE `tparameters` SET `mail_oauth_refresh_token`=:mail_oauth_refresh_token");
            $qry->execute(array('mail_oauth_refresh_token' => $RefreshToken));
            echo '<h4>SMTP RefreshToken added to GestSup database :</h4>'.$RefreshToken; 
        }
        if($AccessToken) {echo '<hr /><h4>SMTP AccessToken  :</h4>'.$AccessToken; }
    }elseif(($providerName=='imap_google_service' || $providerName=='imap_azure_service') && $mailboxid)  //imap service connector
    {
        if($RefreshToken)
        {
            $qry=$db->prepare("UPDATE `tparameters_imap_multi_mailbox` SET `mailbox_service_oauth_refresh_token`=:mailbox_service_oauth_refresh_token WHERE id=:id");
            $qry->execute(array('mailbox_service_oauth_refresh_token' => $RefreshToken,'id' => $mailboxid));
            echo '<h4>IMAP MAILBOX SERVICE RefreshToken added to GestSup database :</h4>'.$RefreshToken; 
        }
        if($AccessToken) {echo '<hr /><h4>IMAP MAILBOX SERVICE AccessToken  :</h4>'.$AccessToken; }
    }else{ //other
        if($RefreshToken)
        {
            $qry=$db->prepare("UPDATE `tparameters` SET `mail_oauth_refresh_token`=:mail_oauth_refresh_token");
            $qry->execute(array('mail_oauth_refresh_token' => $RefreshToken));
            echo '<h4>SMTP RefreshToken added to GestSup database :</h4>'.$RefreshToken; 
        }
        if($AccessToken) {echo '<hr /><h4>SMTP AccessToken  :</h4>'.$AccessToken; }
    }
    echo '<hr>Open GestSup connector tab : <a href="'.$rparameters['server_url'].'/index.php?page=admin&subpage=parameters&tab=connector">'.$rparameters['server_url'].'/index.php?page=admin&subpage=parameters&tab=connector</a>'; 
}
