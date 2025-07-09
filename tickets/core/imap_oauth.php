<?php
################################################################################
# @Name : imap_oauth.php
# @Description : mail2ticket for oauth auth
# @Call : /mail2ticket.php
# @Parameters : 
# @Author : Flox
# @Create : 17/10/2022
# @Update : 20/03/2024
# @Version : 3.2.49
################################################################################

//call php-imap components
require_once(__DIR__.'/../vendor/autoload.php');
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Client;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Grant\RefreshToken;

//define upload dir
$upload_folder = __DIR__.'/../upload/ticket/';

//set parameters
set_time_limit(300);
if($rparameters['imap_port']=='993/imap/ssl') {$rparameters['imap_port']='993';}

if(preg_match('/gs_en/',$rparameters['imap_oauth_client_secret'])) {$rparameters['imap_oauth_client_secret']=gs_crypt($rparameters['imap_oauth_client_secret'], 'd' , $rparameters['server_private_key']);}

if($rparameters['imap_auth_type']=='oauth_azure' || $rparameters['imap_auth_type']=='oauth_google')
{
    if($rparameters['imap_auth_type']=='oauth_azure')
    {
        //multi bal parameters
        if($rparameters['imap_mailbox_service'] && $mailbox!=$rparameters['imap_user'])
        {
            $qry=$db->prepare("SELECT * FROM `tparameters_imap_multi_mailbox` WHERE mail=:mail");
            $qry->execute(array('mail' => $mailbox));
            $parameters_imap_multi_mailbox=$qry->fetch();
            $qry->closeCursor();
            if(preg_match('/gs_en/', $parameters_imap_multi_mailbox['mailbox_service_oauth_client_secret'])) { $parameters_imap_multi_mailbox['mailbox_service_oauth_client_secret']=gs_crypt( $parameters_imap_multi_mailbox['mailbox_service_oauth_client_secret'], 'd' , $rparameters['server_private_key']);}

            //generate AccessToken
            $postData = array(
                "grant_type" => "refresh_token",
                "client_id" =>  $parameters_imap_multi_mailbox['mailbox_service_oauth_client_id'],
                "client_secret" => $parameters_imap_multi_mailbox['mailbox_service_oauth_client_secret'],
                "refresh_token" => $parameters_imap_multi_mailbox['mailbox_service_oauth_refresh_token']
            );

        } else { //single bal parameters
           
            //generate AccessToken
            $postData = array(
                "grant_type" => "refresh_token",
                "client_id" =>  $rparameters['imap_oauth_client_id'],
                "client_secret" => $rparameters['imap_oauth_client_secret'],
                "refresh_token" => $rparameters['imap_oauth_refresh_token']
            );
        }
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://login.microsoftonline.com/'.$rparameters['imap_oauth_tenant_id'].'/oauth2/v2.0/token');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER , true);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_MAXREDIRS , 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData));
        if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
        $response = curl_exec($curl);
        curl_close($curl);
        $r  = json_decode($response, true);
        
        if(!isset($r['access_token']))
        {
            //get error messages
            $error='';
            if(isset($r['error'])) {$error.= '<br />ERROR : '.$r['error'].'<br />';}
            if(isset($r['error_description'])) {$error.= 'ERROR DESCRIPTION : '.$r['error_description'].'<br />';}
            if(!isset($r['error']) && $r) { $error.=var_dump($r);}
            //display error
            if($cmd) {echo 'GENERATE ACCESS TOKEN : '.$error.PHP_EOL;} 
            else {echo 'GENERATE ACCESS TOKEN : <span style="color:red">'.$error.'</span><br />'; }
            LogIt('error','ERROR 31 : generate access token failed '.$error,0);
            exit;
        } else {
            $AccessToken=$r['access_token'];
            $NewrefreshToken=$r['refresh_token'];
           
            if(isset($parameters_imap_multi_mailbox['mailbox_service_oauth_refresh_token'])) //case multi service mailbox
            {
                if($cmd) {echo 'OAUTH MULTI GENERATE ACCESS TOKEN : '.substr($AccessToken,0,24).'*****************'.PHP_EOL;} 
                else {echo 'OAUTH MULTI GENERATE ACCESS TOKEN : <span style="color:green">'.substr($AccessToken,0,24).'*****************</span><br />'; }

                if($NewrefreshToken && $NewrefreshToken!=$parameters_imap_multi_mailbox['mailbox_service_oauth_refresh_token']) 
                {
                    //update RefreshToken
                    $qry=$db->prepare("UPDATE `tparameters_imap_multi_mailbox` SET `mailbox_service_oauth_refresh_token`=:mailbox_service_oauth_refresh_token WHERE mail=:mail");
                    $qry->execute(array('mailbox_service_oauth_refresh_token' => $NewrefreshToken, 'mail' => $mailbox));

                    if($cmd) {echo 'OAUTH MULTI UPDATE REFRESH TOKEN : '.substr($NewrefreshToken,0,24).'*****************'.PHP_EOL;} 
                    else {echo 'OAUTH MULTI UPDATE REFRESH TOKEN : <span style="color:green">'.substr($NewrefreshToken,0,24).'*****************</span><br />'; }
                }
            } else { //single mailbox mode
                if($cmd) {echo 'OAUTH SINGLE GENERATE ACCESS TOKEN : '.substr($AccessToken,0,24).'*****************'.PHP_EOL;} 
                else {echo 'OAUTH SINGLE GENERATE ACCESS TOKEN : <span style="color:green">'.substr($AccessToken,0,24).'*****************</span><br />'; }

                if($NewrefreshToken && $NewrefreshToken!=$rparameters['imap_oauth_refresh_token']) 
                {
                    //update RefreshToken
                    $qry=$db->prepare("UPDATE `tparameters` SET `imap_oauth_refresh_token`=:imap_oauth_refresh_token");
                    $qry->execute(array('imap_oauth_refresh_token' => $NewrefreshToken));

                    if($cmd) {echo 'OAUTH SINGLE UPDATE REFRESH TOKEN : '.substr($NewrefreshToken,0,24).'*****************'.PHP_EOL;} 
                    else {echo 'OAUTH SINGLE UPDATE REFRESH TOKEN : <span style="color:green">'.substr($NewrefreshToken,0,24).'*****************</span><br />'; }
                }
            }
        }
    }
   
    if($rparameters['imap_auth_type']=='oauth_google')
    {
        $redirectUri = $rparameters['server_url'].'/mail2ticket.php';
        $provider = new Google([
            'clientId'     => $rparameters['imap_oauth_client_id'],
            'clientSecret' => $rparameters['imap_oauth_client_secret'],
            'redirectUri'  => $redirectUri,
        ]);
        
        $grant = new RefreshToken();
        $AccessToken = $provider->getAccessToken($grant, ['refresh_token' => $rparameters['imap_oauth_refresh_token']]);
        $NewrefreshToken=$AccessToken->getRefreshToken();
        if($NewrefreshToken && $NewrefreshToken!=$rparameters['imap_oauth_refresh_token']) 
        {
            //update RefreshToken
            $qry=$db->prepare("UPDATE `tparameters` SET `imap_oauth_refresh_token`=:imap_oauth_refresh_token");
            $qry->execute(array('imap_oauth_refresh_token' => $NewrefreshToken));

            if($cmd) {echo 'OAUTH UPDATE REFRESH TOKEN : '.substr($NewrefreshToken,0,24).'*****************'.PHP_EOL;} 
            else {echo 'OAUTH UPDATE REFRESH TOKEN : <span style="color:green">'.substr($NewrefreshToken,0,24).'*****************</span><br />'; }
        }
    }

    //connection parameters
    $cm = new ClientManager();
    $client = $cm->make([
        'host' => $rparameters['imap_server'],
        'port' => $rparameters['imap_port'],
        'encryption' => 'ssl', 
        'validate_cert' => $rparameters['imap_ssl_check'],
        'username' => $mailbox,
        'password' => $AccessToken,
        'protocol' => 'imap',
        'authentication' => "oauth",
    ]);
} else { //basic auth

    //decrypt password
    if(preg_match('/gs_en/',$rparameters['imap_password'])) {$rparameters['imap_password']=gs_crypt($rparameters['imap_password'], 'd' , $rparameters['server_private_key']);}

    /*
    $options = [
        'options' => [
            'soft_fail' => false,
            'rfc822' => true,
            'debug' => true,
        ],
    ];
    */

    //connection parameters
    $cm = new ClientManager();
    $client = $cm->make([
        'host' => $rparameters['imap_server'],
        'port' => $rparameters['imap_port'],
        'encryption' => 'ssl', 
        'validate_cert' => $rparameters['imap_ssl_check'],
        'username' => $mailbox,
        'password' => $rparameters['imap_password'],
        'protocol' => 'imap',
    ]);
}

if(!$client){
    LogIt('error','ERROR 22 : IMAP oauth connexion failed ','0');
    if($cmd) {echo 'CONNEXION : failed '.PHP_EOL;} 
    else {echo 'CONNEXION : <span style="color:red">failed</span><br />'; }
}

//imap connexion
try {
    $client->connect();
} catch (Exception $ex) { //on error try with new token
    //LogIt('error','ERROR 23 : IMAP connector connection failed : '.$ex->getMessage().' new token requested','0');
    if($cmd) {echo 'CONNEXION : failed ('.$ex->getMessage().')'.PHP_EOL;} 
    else {echo 'CONNEXION : <span style="color:red">failed('.$ex->getMessage().')</span><br />'; }
    die('CONNEXION : <span style="color:red">'.$ex->getMessage().'</span>');
}

//check connexion, if failed generate new access token
if(!$client->isConnected())
{
    LogIt('error','ERROR 24 : IMAP oauth connexion, not connected','0');
    if($cmd) {echo 'CONNEXION : oauth not connected'.PHP_EOL;} 
    else {echo 'CONNEXION : <span style="color:red">not connected</span><br />'; }
} else {
    if($cmd) {echo 'CONNEXION : OK '.PHP_EOL;} 
    else {echo 'CONNEXION : <span style="color:green">OK</span><br />'; }
}

//select folder #6189
$folders = $client->getFolders($hierarchical = false); //https://github.com/Webklex/php-imap/issues/393
foreach($folders as $folder_name){
    if($folder_name->name==$rparameters['imap_inbox']){$folder=$folder_name;}
}

//check existing folder
if(!$folder) {
    LogIt('error','ERROR 32 : IMAP oauth no folder '.$rparameters['imap_inbox'],'0');
    if($cmd) {echo 'FOLDER : IMAP oauth no folder '.$rparameters['imap_inbox'].PHP_EOL;} 
    else {echo 'FOLDER : <span style="color:red">no folder '.$rparameters['imap_inbox'].', check folder parameter or ISP blocking</span><br />'; }
    exit;
}

//get number of messages
$message_counter = $folder->messages()->unseen()->count();
if($cmd) {
    echo 'UNSEEN MAIL : '.$message_counter.PHP_EOL;
} else {
    echo 'UNSEEN MAIL : <span style="color:green">'.$message_counter.'</span><br />';
}

if(!$cmd) {echo '<hr />';} else {echo PHP_EOL;}

//get unseen messages from folder
$imap_mails = $folder->messages()->unseen()->get();

//for each message
foreach($imap_mails as $imap_mail){
    //init var
    $blacklist_mail=0;
    $count=$count+1;
    $body='';
   
    //get message informations
    $attributes = $imap_mail->getAttributes();
    $from=$imap_mail->getFrom()[0]->mail;
    $dest=''; 
    if(isset($attributes["to"])) {
        for($i = 0; $i < count($attributes["to"]->toArray()); $i++) {$dest.=$attributes["to"]->toArray()[$i]->toArray()["mail"]. ' ';}
    }
    $cc='';
    if(isset($attributes["cc"])) {
        for($i = 0; $i < count($attributes["cc"]->toArray()); $i++) {$cc.=$attributes["cc"]->toArray()[$i]->toArray()["mail"]. ' ';}
    }
    $date=$imap_mail->getDate()->toDate()->toDateTimeString();

    //get subject with encoding fix #6068  
    $header=$imap_mail->getHeader();
    $subject=$header->find("/Subject: \"?([^\"]*)[\";\s]/");
    if($subject) {
        $subject=iconv_mime_decode($subject); //encoding pb
    } else {
        $subject=$imap_mail->getSubject();
    }
    if(!$subject){$subject=T_('(Sans objet)');}
    $subject=htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');

    if($imap_mail->hasHTMLBody()) { 
        $body_type='html';
        $body=$imap_mail->mask()->getHTMLBodyWithEmbeddedBase64Images();
    } else {
        $body_type='text';
        $body=$imap_mail->getTextBody();
    }

    //sanitize body HTML
    $config = HTMLPurifier_Config::createDefault();
    if(preg_match('/Linux/',PHP_OS)) //enable cache on Linux
    {
        //create cache dir
        if(!is_dir(__DIR__.'/../upload/html_purifier'))
        {
            if(is_writeable(__DIR__.'/../upload/index.htm')) {mkdir(__DIR__.'/../upload/html_purifier');} else {LogIt('error','ERROR 41 : unable to create upload/html_purifier folder',0);}
        }
        if(is_dir(__DIR__.'/../upload/html_purifier')){
            $config->set('Cache.SerializerPath', __DIR__.'/../upload/html_purifier');}else {
            LogIt('error','ERROR 42 : unable to find upload/html_purifier',0);
        }
    } else { //disable cache on Windows
        $config->set('Cache.DefinitionImpl', null);
    }
    $config->set('URI.AllowedSchemes',  array('data' => true, 'http' => true));
    $purifier = new HTMLPurifier($config);
    $body = $purifier->purify($body);
  
    $attachment = $imap_mail->hasAttachments();
    $attachments = $imap_mail->getAttachments();

    //check blacklist addresses
    if($rparameters['imap_blacklist'])
    {
        $mail_blacklist=explode(';',$rparameters['imap_blacklist']);
        foreach ($mail_blacklist as $value) {
            //check if each blacklist value exit in source mail as sender
            if(preg_match("/$value/i", $from) && $value){$blacklist_mail=1;}
        }
    }
    if($blacklist_mail) { //blacklisted case
        if($cmd) {
            echo '['.$mailbox.'] [mail '.$count.'] Import mail "'.$subject.'" : KO (blacklist detected on '.$from.')'.PHP_EOL;
        } else {
            echo '['.$mailbox.'] [mail '.$count.'] Import mail "'.$subject.'" : <span style="color:red">KO (blacklist detected on '.$from.')</span><br />';
        }
    } else { //not blacklisted case
        
        //display mail main informations
        if($cmd) {
            echo '['.$mailbox.'] [mail '.$count.'] mail data : subject="'.$subject.'" from="'.$from.'" date="'.$date.'" body_type="'.$body_type.'" '.PHP_EOL;
        } else {
            echo '['.$mailbox.'] [mail '.$count.'] mail data : <span style="color:green">subject="'.$subject.'" from="'.$from.'" date="'.$date.'" body_type="'.$body_type.'"</span><br />';
        }

        //find gestsup user id for this mail
        $qry=$db->prepare("SELECT `id` FROM `tusers` WHERE `mail`=:mail AND `disable`='0'");
        $qry->execute(array('mail' => $from));
        $user=$qry->fetch();
        $qry->closeCursor();
        if(isset($user['id'])){ 
            $user_id=$user['id'];
            if($cmd) {
                echo '['.$mailbox.'] [mail '.$count.'] find gestsup user id : find '.$user_id.' for address '.$from.' '.PHP_EOL;
            } else {
                echo '['.$mailbox.'] [mail '.$count.'] find gestsup user id : <span style="color:green">find '.$user_id.' for address '.$from.'</span><br />';
            }
        } else {
             //create user if not exist
             if($rparameters['imap_auto_create_user'] && $from)
             {
                 $from=htmlspecialchars($from, ENT_QUOTES, 'UTF-8');
                 $qry=$db->prepare("INSERT INTO `tusers` (`login`,`lastname`,`mail`,`profile`) VALUES (:login,:lastname,:mail,2)");
                 $qry->execute(array('login' => $from,'lastname' => $from,'mail' => $from));
                 //get user id
                 $user_id=$db->lastInsertId();
                 if($cmd) {
                     echo '['.$mailbox.'] [mail '.$count.'] find gestsup user id : address "'.$from.'" not registered in GestSup user database, create user '.$user_id.' '.PHP_EOL;
                 } else {
                     echo '['.$mailbox.'] [mail '.$count.'] find gestsup user id : <span style="color:red">address "'.$from.'" not registered in GestSup user database , create user '.$user_id.'</span><br />';
                 }

             } else {
                $user_id='0';
                if($cmd) {
                    echo '['.$mailbox.'] [mail '.$count.'] find gestsup user id : address "'.$from.'" not registered in GestSup user database '.PHP_EOL;
                } else {
                    echo '['.$mailbox.'] [mail '.$count.'] find gestsup user id : <span style="color:red">address "'.$from.'" not registered in GestSup user database</span><br />';
                }
             }
        }
       
        //check if update or create ticket
        $c_reg = "/n°(.*?):/i"; //regex to find ticket number
        preg_match($c_reg, $subject, $matches); // extract ticket number
        @$find_ticket_number = $matches[1];
        if(!empty($find_ticket_number)) {$find_ticket_number=str_replace(' ','',$find_ticket_number);}

        if($find_ticket_number && $rparameters['imap_reply'])  //case update an existing ticket
        {
            if($cmd) {
                echo '['.$mailbox.'] [mail '.$count.'] update existing ticket : '.$find_ticket_number.' '.PHP_EOL;
            } else {
                echo '['.$mailbox.'] [mail '.$count.'] update existing ticket : <span style="color:green"><a target="_blank" href="index.php?page=ticket&id='.$find_ticket_number.'">'.$find_ticket_number.'</a></span><br />';
            }

            //find delimiter on body message
            $find_start_delimiter=0;
            $find_end_delimiter=0;
            if(strpos($body,'---- Repondre au dessus de cette ligne ----')) {$start_tag='---- Repondre au dessus de cette ligne ----'; $find_start_delimiter=1;}
            if(strpos($body,'---- Repondre au dessus du ticket ----')) {$end_tag='---- Repondre au dessus du ticket ----'; $find_end_delimiter=1;}
            if(strpos($body,'---- Answer above this line ----')) {$start_tag='---- Answer above this line ----'; $find_start_delimiter=1;}
            if(strpos($body,'---- Answer above the ticket ----')) {$end_tag='---- Answer above the ticket ----'; $find_end_delimiter=1;}
            if(strpos($body,'---- Responda por encima de esta línea ----')) {$start_tag='---- Responda por encima de esta línea ----'; $find_start_delimiter=1;}
            if(strpos($body,'---- Responda arriba del boleto ----')) {$end_tag='---- Responda arriba del boleto ----'; $find_end_delimiter=1;}
            if(strpos($body,'---- Antworte über diese Zeile ----')) {$start_tag='---- Antworte über diese Zeile ----'; $find_start_delimiter=1;}
            if(strpos($body,'---- Über dem Ticket antworten ----')) {$end_tag='---- Über dem Ticket antworten ----';$find_end_delimiter=1;}

            //cut answer if delimiters detected
            if($find_start_delimiter && $find_end_delimiter)
            {
                $end_mail=explode($end_tag,$body);
                $end_mail=$end_mail[1];
                $start_mail=explode($start_tag,$body);
                $start_mail=$start_mail[0];
                $body=$start_mail.$end_mail;	
            } elseif($find_end_delimiter && !$find_start_delimiter) //case only one delimiters detected
            {
                $end_mail=explode($end_tag,$body);
                $end_mail=$end_mail[1];
                $start_mail=explode('----',$body);
                $start_mail=$start_mail[0];
                $body=$start_mail.$end_mail;	
            }
            
            //check attachement
            if($attachment){
                $attachments=$imap_mail->getAttachments();
                foreach($attachments as $attachment)
                {
                   
                    $attributes = $attachment->getAttributes();
                    $filename=$attachment->getName();
                    $extension = substr($filename, -3);
                    $real_filename=preg_replace("/[^A-Za-z0-9\_\-\.]/", '', $filename);
                    $real_filename=strip_tags($real_filename);
                    
                    if(CheckFileExtension($extension)==true) {
                        //generate storage filename
                        $storage_filename=$find_ticket_number.'_'.md5(uniqid());
                        $attachment->save($path = $upload_folder, $filename = $storage_filename);
                        if($cmd) {
                            echo '['.$mailbox.'] [mail '.$count.'] add attachment : '.$real_filename.''.PHP_EOL;
                        } else {
                            echo '['.$mailbox.'] [mail '.$count.'] add attachment : <span style="color:green">'.$real_filename.'</span><br />';
                        }
                        //db insert in attachment table
                        $uid=md5(uniqid());
                        $qry=$db->prepare("INSERT INTO `tattachments` (`uid`,`ticket_id`,`storage_filename`,`real_filename`) VALUES (:uid,:ticket_id,:storage_filename,:real_filename)");
                        $qry->execute(array('uid' => $uid,'ticket_id' => $find_ticket_number,'storage_filename' => $storage_filename,'real_filename' => $real_filename));
                    } else {
                        if($cmd) {
                            echo '['.$mailbox.'] [mail '.$count.'] add attachment : '.$real_filename.''.PHP_EOL;
                        } else {
                            echo '['.$mailbox.'] [mail '.$count.'] Blacklisted file : <span style="color:red">'.$real_filename.'</span><br />';
                        }
                       
                        logit('security', 'IMAP connector : blacklisted file blocked ('.$real_filename.')','0');
                    }
                }
            }

            //update ticket state if ticket is closed and user reply
            $qry=$db->prepare("SELECT `state` FROM `tincidents` WHERE id=:id");
            $qry->execute(array('id' => $find_ticket_number));
            $ticket_state=$qry->fetch();
            $qry->closeCursor();
            if($ticket_state['state']=='3')
            {
                //update unread state
                $qry=$db->prepare("UPDATE `tincidents` SET `state`='2' WHERE `id`=:id");
                $qry->execute(array('id' => $find_ticket_number));
                //insert switch state in thread
                $qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`type`,`state`) VALUES (:ticket,:date,:author,'5','2')");
                $qry->execute(array('ticket' => $find_ticket_number,'date' => $mail_date,'author' => $user_id));
            } elseif($ticket_state['state']=='6')
            {
                //auto modify state
                $qry=$db->prepare("UPDATE `tincidents` SET `state`='2' WHERE `id`=:id");
                $qry->execute(array('id' => $find_ticket_number));
            }

            //sanitize HTML code
            $body=str_replace('<script','< script',$body); //remove input 
            $body=str_replace('<input','< input',$body); //remove input 
            $body=str_replace('<output','< output',$body); //remove input 
            $body=str_replace('<textarea','< textarea',$body); //remove input 
            $body=str_replace('<select','< select',$body); //remove input 
            $body=str_replace('<data','< data',$body); //remove input 
            $body=str_replace('<datalist','< datalist',$body); //remove input 
            $body=str_replace('<menu','< menu',$body); //remove input 
            $body=str_replace('text-decoration:underline;','',$body);
            $body=preg_replace('/(<(style)\b[^>]*>).*?(<\/\2>)/is', "$1$3", $body); //remove style in outlook client
            $body=preg_replace('/(<(base)\b[^>]*>)/is', "", $body); //remove base link
            $body=preg_replace('/(<(body)\b[^>]*>)/is', "<body>", $body); //remove body attribute such as background #4722
            if(!preg_match("/<HTML/i",$body)){$body=strip_tags($body,'<p><a><span><br><div>');}

            //insert thread in ticket
            $qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`text`) VALUES (:ticket,:date,:author,:text)");
            $qry->execute(array('ticket' => $find_ticket_number,'date' => date('Y-m-d H:i:s'),'author' => $user_id,'text' => $body));

            //update modification date
            $qry=$db->prepare("UPDATE `tincidents` SET `date_modif`=:date_modif WHERE `id`=:id");
            $qry->execute(array('id' => $find_ticket_number,'date_modif' => date('Y-m-d H:i:s')));

            //update unread state
            $qry=$db->prepare("UPDATE `tincidents` SET `techread`='0' WHERE `id`=:id");
            $qry->execute(array('id' => $find_ticket_number));
           
           //send mail to technician 
           if($rparameters['mail_auto_tech_modify'])
           {
               if($cmd) {
                   echo '['.$mailbox.'] [mail '.$count.'] send mail to technician : OK (mail_auto_tech_modify parameter enable)'.PHP_EOL;
               } else {
                   echo '['.$mailbox.'] [mail '.$count.'] send mail to technician : <span style="color:green">OK (mail_auto_tech_modify parameter enable)</span><br />';
               }
               
               //get tech mail 
               $qry = $db->prepare("SELECT tusers.mail FROM tusers,tincidents WHERE tusers.id=tincidents.technician AND tincidents.id=:ticket_id");
               $qry->execute(array('ticket_id' => $find_ticket_number));
               $techmail=$qry->fetch();
               $qry->closeCursor();
               if(!empty($techmail))
               {
                   if($rparameters['mail_from_adr']){$from=$rparameters['mail_from_adr'];}
                   $to=$techmail['mail'];
                   $object=T_('Le ticket').' n°'.$find_ticket_number.' : '.T_(' a été modifié');
                   $message = '
                   '.T_('Le ticket').' n°'.$find_ticket_number.' '.T_('a été modifié').' <br />
                   <br />
                   '.T_('Pour consulter le ticket cliquer sur le lien suivant ').' <a href="'.$rparameters['server_url'].'/index.php?page=ticket&id='.$find_ticket_number.'">'.$rparameters['server_url'].'/index.php?page=ticket&id='.$find_ticket_number.'</a>.
                   ';
                   $mail_auto=true;
                   require(__DIR__.'/../core/message.php');
                   
                   //trace mail in thread
                   $qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`text`,`type`,`dest_mail`) VALUES (:ticket,:date,:author,'','3',:dest_mail)");
                   $qry->execute(array('ticket' => $find_ticket_number,'date' => date('Y-m-d H:i:s'),'author' => 0,'dest_mail' => $techmail['mail']));
               }
           }
        } else { //case create new ticket
            if($cmd) {
                echo '['.$mailbox.'] [mail '.$count.'] create new ticket '.PHP_EOL;
            } else {
                echo '['.$mailbox.'] [mail '.$count.'] create new ticket <br />';
            }

            //check user association with technician
            $qry=$db->prepare("SELECT `tech` FROM `tusers_tech` WHERE user=:user");
            $qry->execute(array('user' => $user_id));
            $tech=$qry->fetch();
            $qry->closeCursor();
            if(empty($tech)) {$tech='0';} else {$tech=$tech['tech'];}

            //get user service
            $qry=$db->prepare("SELECT MAX(`id`),service_id FROM `tusers_services` WHERE user_id=:user_id");
            $qry->execute(array('user_id' => $user_id));
            $sender_service=$qry->fetch();
            $qry->closeCursor();
            if(!isset($sender_service['service_id'])) {$sender_service='0';} else {$sender_service=$sender_service['service_id'];}

            //define date create ticket
            if($rparameters['imap_date_create']=='date_mail')
            {
                $mail_date = $date;
            } else {
                if($rparameters['server_timezone'])
                {
                    date_default_timezone_set($rparameters['server_timezone']);
                    $mail_date = date('Y-m-d H:i:s');
                } else {
                    $mail_date = date('Y-m-d H:i:s');
                }
            }

            //create ticket
            $qry=$db->prepare("INSERT INTO `tincidents` (`user`,`sender_service`,`technician`,`title`,`description`,`date_create`,`date_modif`,`techread`,`state`,`criticality`,`disable`,`place`,`creator`) 
            VALUES (:user,:sender_service,:technician,:title,'',:date_create,:date_modif,'0',:state,'4','0','0',:creator)");
            $qry->execute(array('user' => $user_id,'sender_service' => $sender_service,'technician' => $tech,'title' => $subject,'date_create' => $mail_date,'date_modif' => $mail_date,'state' => $rparameters['ticket_default_state'],'creator' => $user_id));
            
            //get ticket id
            $ticket_id = $db->lastInsertId();
            
            //get ticket id
            if($ticket_id)
            {
                if($cmd) {
                    echo '['.$mailbox.'] [mail '.$count.'] ticket created : '.$ticket_id.PHP_EOL;
                } else {
                    echo '['.$mailbox.'] [mail '.$count.'] ticket created : <span style="color:green"><a target="_blank" href="'.$rparameters['server_url'].'/index.php?page=ticket&id='.$ticket_id.'">#'.$ticket_id.'</a></span><br />';
                }
            }

            //insert threads to mark ticket as created by mail
            $qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`type`) VALUES (:ticket,:date,'6')");
            $qry->execute(array('ticket' => $ticket_id,'date' => $mail_date));

            //check if current mailbox is attached with service
            if($rparameters['imap_mailbox_service'])
            {
                //get service id for current mailbox
                $qry=$db->prepare("SELECT `id`,`name` FROM `tservices` WHERE `id` IN (SELECT `service_id` FROM `tparameters_imap_multi_mailbox` WHERE `mail`=:mail)");
                $qry->execute(array('mail' => $mailbox));
                $row=$qry->fetch();
                $qry->closeCursor();

                if(!empty($row['id'])) {
                    if($cmd) {
                        echo '['.$mailbox.'] [mail '.$count.'] service associate with this mailbox : '.$row['name'].' ('.$row['id'].')'.PHP_EOL;
                    } else {
                        echo '['.$mailbox.'] [mail '.$count.'] service associate with this mailbox : <span style="color:green">'.$row['name'].' ('.$row['id'].')</span><br />';
                    }

                    $qry=$db->prepare("UPDATE `tincidents` SET `u_service`=:u_service WHERE `id`=:id");
                    $qry->execute(array('u_service' => $row['id'],'id' => $ticket_id));
                } else {
                    echo '['.$mailbox.'] [mail '.$count.'] Service associate with this mailbox : <span style="color:red">None</span><br />';

                    if($cmd) {
                        echo '['.$mailbox.'] [mail '.$count.'] service associate with this mailbox : None'.PHP_EOL;
                    } else {
                        echo '['.$mailbox.'] [mail '.$count.'] service associate with this mailbox : <span style="color:red">None</span><br />';
                    }
                }
            }

            //generate date to ticket description 
            if(extension_loaded('intl'))
            {
                //add extra informations on ticket description
                if($rparameters['server_language']=='fr_FR'){
                    if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {setlocale(LC_TIME, 'fr_FR.utf8','fra');} else {setlocale(LC_TIME, "fr_FR");}
                    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::SHORT);
                } else {
                    $formatter = new IntlDateFormatter('en_US', IntlDateFormatter::FULL, IntlDateFormatter::SHORT);
                }
                $date=$formatter->format(new DateTime($mail_date));
            } else {
                //add extra informations on ticket description
                if($rparameters['server_language']=='fr_FR'){if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {setlocale(LC_TIME, 'fr_FR.utf8','fra');} else {setlocale(LC_TIME, "fr_FR");}}
                $date=strtotime($mail_date);
                $date_only=mb_convert_encoding(strftime("%A %e %B %G",$date), 'UTF-8', 'ISO-8859-1');
                $date=$date_only.' '.T_('à').' '.strftime("%H:%M",$date);
            }
           
            //generate description header 
            $description_header=
            '<b>'.T_('De').' :</b> '.$from.'<br /> 
            <b>'.T_('Envoyé le').' : </b> '.$date.'<br /> 
            <b>'.T_('Destinataire(s)').' :</b> '.$dest.' <br />';
            if($cc){$description_header.='<b>'.T_('Copie').' :</b> '.$cc.'<br />';}
            $description_header.='
            <b>'.T_('Objet').' :</b> '.$subject.'<br /> 
            <b>'.T_('Message').' :</b><br /> 
            ';

            //sanitize HTML body
            $body=str_replace('<script','< script',$body); //remove input 
            $body=str_replace('<input','< input',$body); //remove input 
            $body=str_replace('<output','< output',$body); //remove input 
            $body=str_replace('<textarea','< textarea',$body); //remove input 
            $body=str_replace('<select','< select',$body); //remove input 
            $body=str_replace('<data','< data',$body); //remove input 
            $body=str_replace('<datalist','< datalist',$body); //remove input 
            $body=str_replace('<menu','< menu',$body); //remove input 
            $body=str_replace("text-decoration:underline;", "", $body);
            $body=preg_replace('/(<(base)\b[^>]*>)/is', "", $body); //remove base link
            $body=preg_replace('/(<(body)\b[^>]*>)/is', "<body>", $body); //remove body attribute such as background #4722
    
            //add header informations
            $body=$description_header.$body;

            //update description
            $qry=$db->prepare("UPDATE `tincidents` SET `description`=:description WHERE `id`=:id");
            $qry->execute(array('description' => $body,'id' => $ticket_id));

            //check attachement
            if($attachment){
                $attachments=$imap_mail->getAttachments();
                foreach($attachments as $attachment)
                {
                    $attributes = $attachment->getAttributes();
                    $filename=$attachment->getName();
                    $extension = substr($filename, -3);
                    $real_filename=preg_replace("/[^A-Za-z0-9\_\-\.]/", '', $filename);
					$real_filename=strip_tags($real_filename);
                  
					if(CheckFileExtension($extension)==true) {

						//generate storage filename
						$storage_filename=$ticket_id.'_'.md5(uniqid());
                       // echo 'case'; exit;
                        $attachment->save($path = "$upload_folder", $filename = $storage_filename);

                        if($cmd) {
                            echo '['.$mailbox.'] [mail '.$count.'] add attachment : '.$real_filename.''.PHP_EOL;
                        } else {
                            echo '['.$mailbox.'] [mail '.$count.'] add attachment : <span style="color:green">'.$real_filename.'</span><br />';
                        }
						
						//db insert in attachment table
						$uid=md5(uniqid());
						$qry=$db->prepare("INSERT INTO `tattachments` (`uid`,`ticket_id`,`storage_filename`,`real_filename`) VALUES (:uid,:ticket_id,:storage_filename,:real_filename)");
						$qry->execute(array('uid' => $uid,'ticket_id' => $ticket_id,'storage_filename' => $storage_filename,'real_filename' => $real_filename));
						
					} else {
						echo '['.$mailbox.'] [mail '.$count.'] Blacklisted file : <span style="color:red">'.$real_filename.'</span><br />';
						logit('security', 'IMAP connector : blacklisted file blocked ('.$real_filename.')','0');
					}
                }
            }

            //send mail to user 
            if($rparameters['mail_auto_user_newticket'] && $rparameters['mail'])
            {
                $send=1;
                $_GET['id']=$ticket_id;
                $datetime=$mail_date;
                include(__DIR__.'/../core/mail.php');
                if($cmd) {
                    echo '['.$mailbox.'] [mail '.$count.'] SEND Mail to sender : OK (mail_auto_user_newticket parameter enable)'.PHP_EOL;
                } else {
                    echo '['.$mailbox.'] [mail '.$count.'] SEND Mail to sender : <span style="color:green">OK (mail_auto_user_newticket parameter enable)</span><br />';
                }
            }

            //send mail to admin 
            if($rparameters['mail_newticket'] && $rparameters['mail_newticket_address'] && $rparameters['mail'])
            {
                $qry = $db->prepare("SELECT tusers.firstname,tusers.lastname,tincidents.title,tincidents.description FROM tusers,tincidents WHERE tusers.id=tincidents.user AND tincidents.id=:ticket_id");
                $qry->execute(array('ticket_id' => $ticket_id));
                $ticket_data=$qry->fetch();
                $qry->closeCursor();
                
                $from=$rparameters['mail_from_adr'];
                $to=$rparameters['mail_newticket_address'];
                $object=T_('Un nouveau ticket a été déclaré par ').$ticket_data['lastname'].' '.$ticket_data['firstname'].' : '.$ticket_data['title'];
                $message = '
                '.T_('Le ticket').' n°'.$ticket_id.' '.T_("a été déclaré par l'utilisateur").' '.$ticket_data['lastname'].' '.$ticket_data['firstname'].'.<br />
                <br />
                <u>'.T_('Objet').':</u><br />
                '.$ticket_data['title'].'<br />		
                <br />	
                <u>'.T_('Description').':</u><br />
                '.$ticket_data['description'].'<br />
                <br />
                '.T_("Pour plus d'informations vous pouvez consulter le ticket sur").' <a href="'.$rparameters['server_url'].'/index.php?page=ticket&id='.$ticket_id.'">'.$rparameters['server_url'].'/index.php?page=ticket&id='.$ticket_id.'</a>.
                ';
                $mail_auto=true;

                require(__DIR__.'/../core/message.php');

                if($cmd) {
                    echo '['.$mailbox.'] [mail '.$count.'] Send mail to administrator : OK (mail_newticket parameter enable)'.PHP_EOL;
                } else {
                    echo '['.$mailbox.'] [mail '.$count.'] Send mail to administrator : <span style="color:green">OK (mail_newticket parameter enable)</span><br />';
                }
            }


        } //end create new ticket

        //post treatment actions
        if($rparameters['imap_post_treatment']=='move' && $rparameters['imap_post_treatment_folder'])
        {
            //move mail
            $imap_mail->setFlag('Seen');
            $imap_mail->move($folder_path = $rparameters['imap_post_treatment_folder']);

            if($cmd) {
                echo '['.$mailbox.'] [mail '.$count.'] post-treatment action : move ('.$rparameters['imap_post_treatment_folder'].' folder)'.PHP_EOL;
            } else {
                echo '['.$mailbox.'] [mail '.$count.'] post-treatment action : <span style="color:green">move ('.$rparameters['imap_post_treatment_folder'].' folder)</span><br />';
            }
        }elseif($rparameters['imap_post_treatment']=='delete')
        { 
            //delete mail
            //imap_delete($con_mailbox->getImapStream(),$tab_MailsInfo->uid,FT_UID);
            $imap_mail->setFlag('Seen');
            $imap_mail = $imap_mail->delete($expunge = true);

            if($cmd) {
                echo '['.$mailbox.'] [mail '.$count.'] post-treatment action : delete'.PHP_EOL;
            } else {
                echo '['.$mailbox.'] [mail '.$count.'] post-treatment action : <span style="color:green">delete</span><br />';						
            }
        } else {
            //unread mail
            $imap_mail->setFlag('Seen');
            if($cmd) {
                echo '['.$mailbox.'] [mail '.$count.'] post-treatment action : seen'.PHP_EOL;
            } else {
                echo '['.$mailbox.'] [mail '.$count.'] post-treatment action : <span style="color:green">seen</span><br />';					
            }
        }
    }

    //display current treatment time
    $elapsed_time=(round(microtime(true) - $start));
    if($cmd) {
        echo '['.$mailbox.'] [mail '.$count.'] time : '.$elapsed_time.' sec.'.PHP_EOL;
    } else {
        echo '['.$mailbox.'] [mail '.$count.'] time : <span style="color:green">'.$elapsed_time.' sec.</span><br />';
    }
   
    if(!$cmd) {echo '<hr />';} else {echo PHP_EOL;}

    //limit 20 mails for timeout
    if($count==20) {
        if($cmd) {
            echo 'MAIL LIMIT '.$count.' REACHED WAIT NEXT CHECK'.PHP_EOL;
            echo PHP_EOL;
        } else {
            echo 'MAIL LIMIT '.$count.' REACHED WAIT NEXT CHECK<br /><hr>';
        }
        break;
    }
}

?>