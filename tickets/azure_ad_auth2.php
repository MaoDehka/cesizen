<?php 
################################################################################################
# @Name : azure_ad_auth.php 
# @Description : Oauth request on MS Entra ID (Azure AD) for Azure authentication tenant2
# @Call : index.php
# @Author : Flox
# @Create : 16/08/2023
# @Update : 10/10/2023
# @Version : 3.2.50 p1
################################################################################################

//call require files
require_once('connect.php');
require_once('core/functions.php');

//db connexion
$db->exec('SET sql_mode = ""');

//get app parameters 
$qry=$db->prepare("SELECT * FROM `tparameters`");
$qry->execute();
$parameters=$qry->fetch();
$qry->closeCursor();

//get tenant 2 informations
$qry=$db->prepare("SELECT * FROM `tentra_tenant` WHERE `id`='2'");
$qry->execute();
$tenant=$qry->fetch();
$qry->closeCursor();

//decrypt application password
if(preg_match('/gs_en/',$tenant['client_secret'])) {$tenant['client_secret']=gs_crypt($tenant['client_secret'], 'd' , $parameters['server_private_key']);}

//init var
if(!isset($_SESSION['user_id'])) {$_SESSION['user_id']='';}
if(!isset($_SESSION['oauth2state'])) {$_SESSION['oauth2state']='';}

if($parameters['azure_ad_sso'] && !$_SESSION['user_id'])
{
    require_once 'vendor/autoload.php';

    //start user session reqd for storing state
    session_start(); 

    //app configs
    $provider = new TheNetworg\OAuth2\Client\Provider\Azure([
        'clientId'          => $tenant['client_id'],
        'clientSecret'      => $tenant['client_secret'],
        'redirectUri'       => $parameters['server_url'].'/azure_ad_auth2.php'
    ]);

    //add scope read of /me endpoint 
    $provider->scope = ['offline_access User.Read'];
    $provider->urlAPI = "https://graph.microsoft.com/v1.0/";

    //this tells the library not to pass resource reqd for v2.0
    $provider->authWithResource = false;

    //obtain the auth code
    if (!isset($_GET['code'])) {
        $authUrl = $provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $provider->getState();
        header('Location: '.$authUrl);
        exit;
    //state validation 
    } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
        exit("State mismatch, ending auth");
    } else {
        //exchange auth code for tokens
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code'],
            'resource' => 'https://graph.microsoft.com',
        ]);
        //now we can call /me endpoint of MS Graph 
        try {
            $me = $provider->get("me", $token);
            $azure_id=$me['id'];

            //check gestsup account with this Azure id
            $qry=$db->prepare("SELECT `id` FROM `tusers` WHERE azure_ad_id=:azure_ad_id");
            $qry->execute(array('azure_ad_id' => $azure_id));
            $gs_user=$qry->fetch();
            $qry->closeCursor();

            if(!empty($gs_user['id']))
            {
                echo 'Entra ID authentification successful, connection to GestSup...';
                    //generate gs auth token
                    $gs_token = bin2hex(random_bytes(32));
                    $qry=$db->prepare("INSERT INTO `ttoken` (`date`,`token`,`action`,`user_id`) VALUES (NOW(),:token,'azure_auth',:user_id)");
                    $qry->execute(array('token' => $gs_token,'user_id' => $gs_user['id']));

                    //redirect to GestSup index with auth token
                    echo '<script language="Javascript">document.location.replace("'.$parameters['server_url'].'/index.php?auth_token='.$gs_token.'");</script>';
            } else {
                exit('ERROR : Azure object id not found in GestSup user database.');
            }

        } catch (Exception $e) {
            var_dump($e);
            exit('ERROR : Failed to call the me endpoint of MS Graph.');
        }
    }
}