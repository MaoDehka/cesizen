<?php
################################################################################
# @Name : imap_basic.php
# @Description : mail2ticket for basic auth
# @Call : /mail2ticket.php
# @Parameters : 
# @Author : Flox
# @Create : 17/10/2022
# @Update : 30/04/2024
# @Version : 3.2.50
################################################################################

//define upload directory
$c_name_dir_upload =  __DIR__.'/../upload/ticket';

//call php-imap components
require_once(__DIR__.'/../vendor/autoload.php');
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;

//get mailbox password
if($rparameters['imap_mailbox_service'])
{
    $qry=$db->prepare("SELECT `password` FROM `tparameters_imap_multi_mailbox` WHERE mail=:mail");
    $qry->execute(array('mail' => $mailbox));
    $mailbox_password=$qry->fetch();
    $qry->closeCursor();

    if(!empty($mailbox_password['password']))
    {
        //mailbox service password
        if(preg_match('/gs_en/',$mailbox_password['password'])) {
            $mailbox_password=gs_crypt($mailbox_password['password'], 'd' , $rparameters['server_private_key']);
        } else {
            $mailbox_password=$mailbox_password['password'];
        } 
    } else {
        //mailbox service password
        if(preg_match('/gs_en/',$rparameters['imap_password'])) {$rparameters['imap_password']=gs_crypt($rparameters['imap_password'], 'd' , $rparameters['server_private_key']);}
        $mailbox_password=$rparameters['imap_password'];
    }
} else {
    if(preg_match('/gs_en/',$rparameters['imap_password'])) {
        $mailbox_password=gs_crypt($rparameters['imap_password'], 'd' , $rparameters['server_private_key']);
    } else {
        $mailbox_password=$rparameters['imap_password'];
    }
}

//function to add attachment in image on ticket
if(!function_exists("barbushin_func_attachement"))
{
    function barbushin_func_attachement($c_ticket_number,$c_name_dir_upload,$mail,$db,$mailbox,$count,$contentype)
    {
        $c_name_dir_ticket = $c_name_dir_upload; 
        
        //move attachment to upload directory
        $tabAttachments = $mail->getAttachments();
        foreach ($tabAttachments as $tabAttachment){
            //case image inside in mail
            if($tabAttachment->disposition=="inline" || $tabAttachment->disposition=="INLINE" || $tabAttachment->disposition==null) #4015 
            {
                /*
                echo '<pre>';
                print_r($tabAttachment);
                echo '</pre>';
                */

                $c_name_file_original = basename($tabAttachment->filePath);
                $c_name_file = $c_ticket_number.'_'.$c_name_file_original;
                
                echo '['.$mailbox.'] [mail '.$count.'] Image into body : <span style="color:green">'.$c_name_file.'</span><br />';
                $dispo=$tabAttachment->disposition;
                echo '['.$mailbox.'] [mail '.$count.'] Disposition : <span style="color:green">'.$dispo.'</span><br />';
                //check if link are not present #4371 from apple mail
                if($contentype=='textPlain' )
                {
                    $c_name_file = $tabAttachment->name;
                    if($c_name_file && $c_ticket_number)
                    {
                        $real_filename=preg_replace("/[^A-Za-z0-9\_\-\.]/", '', $c_name_file);
                        $real_filename=strip_tags($real_filename);
                        if(CheckFileExtension($real_filename)==true) {
                            $target_folder='./upload/ticket/';
                            //generate storage filename
                            $c_name_file=$c_ticket_number.'_'.md5(uniqid());
                            
                            echo '['.$mailbox.'] [mail '.$count.'] Attachment : <span style="color:green">'.$real_filename.'</span><br />';
                            $dispo=$tabAttachment->disposition;
                            echo '['.$mailbox.'] [mail '.$count.'] Disposition : <span style="color:green">'.$dispo.'</span><br />';
                            //db insert in attachment table
                            $uid=md5(uniqid());
                            $qry=$db->prepare("INSERT INTO `tattachments` (`uid`,`ticket_id`,`storage_filename`,`real_filename`) VALUES (:uid,:ticket_id,:storage_filename,:real_filename)");
                            $qry->execute(array('uid' => $uid,'ticket_id' => $c_ticket_number,'storage_filename' => $c_name_file,'real_filename' => $real_filename));
                            
                        } else {
                            echo '['.$mailbox.'] [mail '.$count.'] Blacklisted file : <span style="color:red">'.$real_filename.'</span><br />';
                            logit('security', 'IMAP connector : blacklisted file blocked ('.$real_filename.')','0');
                        }
                    } 
                }
            } 
            else //case attachment in mail
            {
                $c_name_file = $tabAttachment->name;
                if($c_name_file && $c_ticket_number)
                {
                    $real_filename=preg_replace("/[^A-Za-z0-9\_\-\.\s+]/", '', $c_name_file);
                    $real_filename=strip_tags($real_filename);
                    if(CheckFileExtension($real_filename)==true) {
                        $target_folder='./upload/ticket/';
                        //generate storage filename
                        $c_name_file=$c_ticket_number.'_'.md5(uniqid());
                        
                        echo '['.$mailbox.'] [mail '.$count.'] Attachment : <span style="color:green">'.$real_filename.'</span><br />';
                        $dispo=$tabAttachment->disposition;
                        echo '['.$mailbox.'] [mail '.$count.'] Disposition : <span style="color:green">'.$dispo.'</span><br />';
                        //db insert in attachment table
                        $uid=md5(uniqid());
                        $qry=$db->prepare("INSERT INTO `tattachments` (`uid`,`ticket_id`,`storage_filename`,`real_filename`) VALUES (:uid,:ticket_id,:storage_filename,:real_filename)");
                        $qry->execute(array('uid' => $uid,'ticket_id' => $c_ticket_number,'storage_filename' => $c_name_file,'real_filename' => $real_filename));
                        
                    } else {
                        echo '['.$mailbox.'] [mail '.$count.'] Blacklisted file : <span style="color:red">'.$real_filename.'</span><br />';
                        logit('security', 'IMAP connector : blacklisted file "'.$real_filename.'" blocked, ticket '.$c_ticket_number,'0');
                    }
                } 
            }

            rename($tabAttachment->filePath,$c_name_dir_ticket.'/'.$c_name_file); 
        }
        return $mail->replaceInternalLinks('upload/ticket');
    }
}


//unsigned cert
if($rparameters['imap_ssl_check']==0) {$ssl_check='/novalidate-cert';} else {$ssl_check='';}

//hostname building
$hostname = '{'.$rparameters['imap_server'].':'.$rparameters['imap_port'].''.$ssl_check.'}'.$rparameters['imap_inbox'].'';

if($rparameters['imap_port'])
{
    if($cmd) {
        echo 'PORT : '.$rparameters['imap_port'].PHP_EOL;
    } else {
        echo 'PORT : <span style="color:green">'.$rparameters['imap_port'].'</span><br />';
    }	
} else {
    if($cmd) {
        echo 'PORT : No IMAP port detected'.PHP_EOL;
    } else {
        echo 'PORT : <span style="color:red">No IMAP port detected</span><br />';
    }	
}

if($cmd) {
    echo 'IMAP connection string : '.$hostname.PHP_EOL;
} else {
    echo 'IMAP connection string : <span style="color:green">'.$hostname.'</span><br />';
}

if(!$cmd) {echo '<hr />';} else {echo PHP_EOL;}

//connect to mailbox
$con_mailbox = new Mailbox($hostname, $mailbox, $mailbox_password,$c_name_dir_upload,'US-ASCII'); #5899 'US-ASCII' 
try {
    $mailsIds = $con_mailbox->searchMailbox('UNSEEN');
} catch (ConnectionException $ex) {
    LogIt('error','ERROR 20 : IMAP connector, connection failed: '.$ex->getMessage(),'0');
    die('IMAP connection failed: '.$ex->getMessage());
} catch (Exception $ex) {
    LogIt('error','ERROR 21 : IMAP connector, An error occurred: '.$ex->getMessage(),'0');
    die('An error occurred: '.$ex->getMessage());
}

if(!$mailsIds) {
    if($cmd) {
        echo '['.$mailbox.'] Detect mail in mailbox : NO'.PHP_EOL;
    } else {
        echo '['.$mailbox.'] Detect mail in mailbox : <span style="color:green">NO</span><br />';
    }
} else {
    if($cmd) {
        echo '['.$mailbox.'] Detect mail in mailbox : YES'.PHP_EOL;
    } else {
        echo '['.$mailbox.'] Detect mail in mailbox : <span style="color:green">YES</span><br />';
    }

    //treatment for all mail inside mailbox
    $seen=0;
    $tab_MailsInfos =  $con_mailbox ->getMailsInfo($mailsIds);		
    foreach ($tab_MailsInfos as $tab_MailsInfo){
        if($tab_MailsInfo->seen==0)
        {
            $seen=1;
            $count=$count+1;
            $mail = $con_mailbox ->getMail($tab_MailsInfo->uid);
            $from = $mail->fromAddress;
            $subject = $mail->subject;
            $subject=htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');

            //define date create ticket
            if($rparameters['imap_date_create']=='date_mail')
            {
                $datetime = $mail->date;
                if($cmd) {
                    echo '['.$mailbox.'] [mail '.$count.'] Mail date : '.$datetime.PHP_EOL;
                } else {
                    echo '['.$mailbox.'] [mail '.$count.'] Mail date : '.$datetime.'<br />';
                }
            } else {
                if($rparameters['server_timezone'])
                {
                    date_default_timezone_set($rparameters['server_timezone']);
                    $datetime = date('Y-m-d H:i:s');
                } else {
                    $datetime = date('Y-m-d H:i:s');
                }
                if($cmd) {
                    echo '['.$mailbox.'] [mail '.$count.'] System date : '.$datetime.PHP_EOL;
                } else {
                    echo '['.$mailbox.'] [mail '.$count.'] System date : '.$datetime.'<br />';
                }
            }
            
            $blacklist_mail=0;
            if(!$subject){$subject=T_('(Sans objet)');} //default subject 
            //detect blacklist mail or domain for exclusion
            if($rparameters['imap_blacklist']!='')
            {
                $mail_blacklist=explode(';',$rparameters['imap_blacklist']);
                foreach ($mail_blacklist as $value) {
                    //check if each blacklist value exit in source mail as sender
                    if(preg_match("/$value/i", $from) && $value){$blacklist_mail=1;}
                }
            }
            if($blacklist_mail==1) {
                if($cmd) {
                    echo '['.$mailbox.'] [mail '.$count.'] Import mail "'.$subject.'" : KO (blacklist detected on '.$from.')'.PHP_EOL;
                } else {
                    echo '['.$mailbox.'] [mail '.$count.'] Import mail "'.$subject.'" : <span style="color:red">KO (blacklist detected on '.$from.')</span><br />';
                }
            } 
            else
            {
                //check if mail is HTML
                if($mail->textHtml == NULL){
                    $contentype='textPlain';
                    $message = nl2br($mail->textPlain);
                    $description = nl2br($mail->textPlain);
                }else{
                    $contentype='textHtml';
                    $message = $mail->textHtml;
                    $description = $mail->textHtml;
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
                $description = $purifier->purify($description);

                //special char convert
                $subject = str_replace('_', ' ', $subject);

                //find gestsup userid from mail address
                $qry=$db->prepare("SELECT `id` FROM `tusers` WHERE mail=:mail AND disable='0'");
                $qry->execute(array('mail' => $from));
                $row=$qry->fetch();
                $qry->closeCursor();
                if(isset($row['id']))
                {
                    $user_id=$row['id'];
                    $c_FromMessage='';
                } else {
                    //create user if not exist
                    if($rparameters['imap_auto_create_user'] && $from)
                    {
                        $from=htmlspecialchars($from, ENT_QUOTES, 'UTF-8');
                        $qry=$db->prepare("INSERT INTO `tusers` (`login`,`lastname`,`mail`,`profile`) VALUES (:login,:lastname,:mail,2)");
                        $qry->execute(array('login' => $from,'lastname' => $from,'mail' => $from));
                        //get user id
                        $user_id=$db->lastInsertId();
                    } else {
                        $user_id='0';
                        $c_FromMessage='';
                    }
                }
                
                //get extra informations from message header
                $head = $con_mailbox->getMailHeader($tab_MailsInfo->uid);
               
                //detect ticket number in subject to update an existing ticket
                $c_reg = "/n°(.*?):/i"; //regex for extract ticket number
                preg_match($c_reg, $subject, $matches); // extract ticket number
                @$find_ticket_number = $matches[1];
                if(!empty($find_ticket_number)) {$find_ticket_number=str_replace(' ','',$find_ticket_number);}
                //update existing ticket
                if($find_ticket_number && $rparameters['imap_reply']) 
                {
                    //get attachement and image 
                    if($contentype=='textHtml') { 
                        $message = (isset($c_FromMessage)?$c_FromMessage:'').barbushin_func_attachement($find_ticket_number,$c_name_dir_upload,$mail,$db,$mailbox,$count,$contentype);
                    } else { //case plain text with attachment
                        $message = (isset($c_FromMessage)?$c_FromMessage:'').barbushin_func_attachement($find_ticket_number,$c_name_dir_upload,$mail,$db,$mailbox,$count,$contentype);
                    }

                    if($rparameters['debug']) {echo '['.$mailbox.'] [mail '.$count.'] Mail content :<br />'.$message.'<br />';}
                    $find_start_delimiter=0;
                    $find_end_delimiter=0;
                    //delete ticket part from mail to keep only answer
                    if(strpos($message,'---- Repondre au dessus de cette ligne ----')) {$start_tag='---- Repondre au dessus de cette ligne ----'; $find_start_delimiter=1;}
                    if(strpos($message,'---- Repondre au dessus du ticket ----')) {$end_tag='---- Repondre au dessus du ticket ----'; $find_end_delimiter=1;}
                    if(strpos($message,'---- Answer above this line ----')) {$start_tag='---- Answer above this line ----'; $find_start_delimiter=1;}
                    if(strpos($message,'---- Answer above the ticket ----')) {$end_tag='---- Answer above the ticket ----'; $find_end_delimiter=1;}
                    if(strpos($message,'---- Responda por encima de esta línea ----')) {$start_tag='---- Responda por encima de esta línea ----'; $find_start_delimiter=1;}
                    if(strpos($message,'---- Responda arriba del boleto ----')) {$end_tag='---- Responda arriba del boleto ----'; $find_end_delimiter=1;}
                    if(strpos($message,'---- Antworte über diese Zeile ----')) {$start_tag='---- Antworte über diese Zeile ----'; $find_start_delimiter=1;}
                    if(strpos($message,'---- Über dem Ticket antworten ----')) {$end_tag='---- Über dem Ticket antworten ----';$find_end_delimiter=1;}

                    //cut answer if delimiters detected
                    if($find_start_delimiter && $find_end_delimiter)
                    {
                        $end_mail=explode($end_tag,$message);
                        $end_mail=$end_mail[1];
                        $start_mail=explode($start_tag,$message);
                        $start_mail=$start_mail[0];
                        $message=$start_mail.$end_mail;	
                    } elseif($find_end_delimiter && !$find_start_delimiter) //case only one delimiters detected
                    {
                        $end_mail=explode($end_tag,$message);
                        $end_mail=$end_mail[1];
                        $start_mail=explode('----',$message);
                        $start_mail=$start_mail[0];
                        $message=$start_mail.$end_mail;	
                    }
                    
                    //update img link
                    $tabAttachments = $mail->getAttachments();
                    foreach ($tabAttachments as $tabAttachment){
                        if($tabAttachment->disposition=="inline" || $tabAttachment->disposition=="INLINE" || $tabAttachment->disposition==null)  //case image inside in mail
                        {
                            $c_name_file_original = basename($tabAttachment->filePath);
                            $c_name_file_rename = $find_ticket_number.'_'.$c_name_file_original;
                            $message=str_replace($c_name_file_original,$c_name_file_rename,$message);
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
                        $qry->execute(array('ticket' => $find_ticket_number,'date' => $datetime,'author' => $user_id));
                    } elseif($ticket_state['state']=='6')
                    {
                        //auto modify state
                        $qry=$db->prepare("UPDATE `tincidents` SET `state`='2' WHERE `id`=:id");
                        $qry->execute(array('id' => $find_ticket_number));
                    }

                    //sanitize HTML code
                    $message=str_replace('<input','< input',$message); //remove input 
                    $message=str_replace('<output','< output',$message); //remove input 
                    $message=str_replace('<textarea','< textarea',$message); //remove input 
                    $message=str_replace('<select','< select',$message); //remove input 
                    $message=str_replace('<data','< data',$message); //remove input 
                    $message=str_replace('<datalist','< datalist',$message); //remove input 
                    $message=str_replace('<menu','< menu',$message); //remove input 
                    $message=str_replace('text-decoration:underline;','',$message);
                    $message=preg_replace('/(<(style)\b[^>]*>).*?(<\/\2>)/is', "$1$3", $message); //remove style in outlook client
                    $message=preg_replace('/(<(base)\b[^>]*>)/is', "", $message); //remove base link
                    $message=preg_replace('/(<(body)\b[^>]*>)/is', "<body>", $message); //remove body attribute such as background #4722
                    if(!preg_match("/<HTML/i",$message)){$message=strip_tags($message,'<p><a><span><br><div>');}
                    
                    //insert thread in ticket
                    $qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`text`) VALUES (:ticket,:date,:author,:text)");
                    $qry->execute(array('ticket' => $find_ticket_number,'date' => $datetime,'author' => $user_id,'text' => $message));

                    //update modification date
                    $qry=$db->prepare("UPDATE `tincidents` SET `date_modif`=:date_modif WHERE `id`=:id");
                    $qry->execute(array('id' => $find_ticket_number,'date_modif' => $datetime));

                    if($cmd) {
                        echo '['.$mailbox.'] [mail '.$count.'] Import mail "'.$subject.'" : OK'.PHP_EOL;
                        echo '['.$mailbox.'] [mail '.$count.'] Update ticket : OK (ID='.$find_ticket_number.')'.PHP_EOL;
                        echo '['.$mailbox.'] [mail '.$count.'] Content type detected : '.$contentype.''.PHP_EOL;
                        echo '['.$mailbox.'] [mail '.$count.'] Encoding type detected : '; echo mb_detect_encoding($message, ['ASCII', 'UTF-8', 'ISO-8859-1'], true); echo PHP_EOL;
                       
                    } else {
                        echo '['.$mailbox.'] [mail '.$count.'] Import mail "'.$subject.'" : <span style="color:green">OK</span><br />';
                        echo '['.$mailbox.'] [mail '.$count.'] Update ticket : <span style="color:green">OK (ID=<a href="index.php?page=ticket&id='.$find_ticket_number.'" target="_blank\" >'.$find_ticket_number.'</a>)</span><br />';
                        echo '['.$mailbox.'] [mail '.$count.'] Content type detected : <span style="color:green">'.$contentype.'</span><br />';
                        echo '['.$mailbox.'] [mail '.$count.'] Encoding type detected : <span style="color:green">'; echo  mb_detect_encoding($message, ['ASCII', 'UTF-8', 'ISO-8859-1'], true); echo '</span><br />';
                    }
                    
                    //update unread state
                    $qry=$db->prepare("UPDATE `tincidents` SET `techread`='0' WHERE `id`=:id");
                    $qry->execute(array('id' => $find_ticket_number));
                    
                    //send mail to technician 
                    if($rparameters['mail_auto_tech_modify'])
                    {
                        if($cmd) {
                            echo '['.$mailbox.'] [mail '.$count.'] SEND Mail to technician : OK (mail_auto_tech_modify parameter enable)'.PHP_EOL;
                        } else {
                            echo '['.$mailbox.'] [mail '.$count.'] SEND Mail to technician : <span style="color:green">OK (mail_auto_tech_modify parameter enable)</span><br />';
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
                            $qry->execute(array('ticket' => $find_ticket_number,'date' => $datetime,'author' => 0,'dest_mail' => $techmail['mail']));
                        }
                        
                    }
                } else { //create ticket
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

                    //create ticket
                    $qry=$db->prepare("INSERT INTO `tincidents` (`user`,`sender_service`,`technician`,`title`,`description`,`date_create`,`date_modif`,`techread`,`state`,`criticality`,`disable`,`place`,`creator`) 
                    VALUES (:user,:sender_service,:technician,:title,'',:date_create,:date_modif,'0',:state,'4','0','0',:creator)");
                    $qry->execute(array('user' => $user_id,'sender_service' => $sender_service,'technician' => $tech,'title' => $subject,'date_create' => $datetime,'date_modif' => $datetime,'state' => $rparameters['ticket_default_state'],'creator' => $user_id));
                    
                    //get ticket number
                    $c_ticket_number = $db->lastInsertId();
                    
                    //insert threads
                    $qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`type`) VALUES (:ticket,:date,'6')");
                    $qry->execute(array('ticket' => $c_ticket_number,'date' => $datetime));
                    
                    //check if current mailbox is attached with service
                    if($rparameters['imap_mailbox_service'])
                    {
                        //get service id for current mailbox
                        $qry=$db->prepare("SELECT `id`,`name` FROM `tservices` WHERE id IN (SELECT service_id FROM `tparameters_imap_multi_mailbox` WHERE mail=:mail)");
                        $qry->execute(array('mail' => $mailbox));
                        $row=$qry->fetch();
                        $qry->closeCursor();

                        if(!empty($row['id'])) {
                            echo '['.$mailbox.'] [mail '.$count.'] Service associate with this mailbox : <span style="color:green">'.$row['name'].' ('.$row['id'].')</span><br />';
                            $qry=$db->prepare("UPDATE `tincidents` SET `u_service`=:u_service WHERE `id`=:id");
                            $qry->execute(array('u_service' => $row['id'],'id' => $c_ticket_number));
                        } else {
                            echo '['.$mailbox.'] [mail '.$count.'] Service associate with this mailbox : <span style="color:red">None</span><br />';
                        }
                    }

                    if($cmd) {
                        echo '['.$mailbox.'] [mail '.$count.'] Import mail "'.$subject.'": OK'.PHP_EOL;
                        echo '['.$mailbox.'] [mail '.$count.'] Create new ticket : ('.$c_ticket_number.')'.PHP_EOL;
                        echo '['.$mailbox.'] [mail '.$count.'] Content type detected : '.$contentype.PHP_EOL;
                        echo '['.$mailbox.'] [mail '.$count.'] Encoding type detected : '; echo mb_detect_encoding($message); echo PHP_EOL;
                    } else {
                        echo '['.$mailbox.'] [mail '.$count.'] Import mail "'.$subject.'": <span style="color:green">OK</span><br />';
                        echo '['.$mailbox.'] [mail '.$count.'] Create new ticket : <span style="color:green">OK <a href="index.php?page=ticket&id='.$c_ticket_number.'" target="_blank\" >('.$c_ticket_number.')</a></span><br />';
                        echo '['.$mailbox.'] [mail '.$count.'] Content type detected : <span style="color:green">'.$contentype.'</span><br />';
                        echo '['.$mailbox.'] [mail '.$count.'] Encoding type detected : <span style="color:green">'; echo mb_detect_encoding($message); echo '</span><br />';
                    }
                    
                    //get attachement and images from mail
                    $message = (isset($c_FromMessage)?$c_FromMessage:'').barbushin_func_attachement($c_ticket_number,$c_name_dir_upload,$mail,$db,$mailbox,$count,$contentype);
                    
                    if(extension_loaded('intl'))
                    {
                        //add extra informations on ticket description
                        if($rparameters['server_language']=='fr_FR'){
                            if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {setlocale(LC_TIME, 'fr_FR.utf8','fra');} else {setlocale(LC_TIME, "fr_FR");}
                            $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::SHORT);
                        } else {
                            $formatter = new IntlDateFormatter('en_US', IntlDateFormatter::FULL, IntlDateFormatter::SHORT);
                        }
                        $date=$formatter->format(new DateTime($datetime));
                    } else {
                        //add extra informations on ticket description
                        if($rparameters['server_language']=='fr_FR'){if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {setlocale(LC_TIME, 'fr_FR.utf8','fra');} else {setlocale(LC_TIME, "fr_FR");}}
                        $date=strtotime($datetime);
                        $date_only=mb_convert_encoding(strftime("%A %e %B %G",$date), 'UTF-8', 'ISO-8859-1');
                        $date=$date_only.' '.T_('à').' '.strftime("%H:%M",$date);
                    }
                    
                    $description_header=
                    '<b>'.T_('De').' :</b> '.$mail->fromAddress.'<br /> 
                    <b>'.T_('Envoyé le').' : </b> '.$date.'<br /> 
                    <b>'.T_('Destinataire(s)').' :</b> '.$head->toString.' <br />';
                    if(isset($head->headers->ccaddress)){$description_header.='<b>'.T_('Copie').' :</b> '.$head->headers->ccaddress.'<br />';}
                    $description_header.='
                    <b>'.T_('Objet').' :</b> '.$subject.'<br /> 
                    <b>'.T_('Message').' :</b><br /> 
                    ';
                    
                    if($contentype=='textPlain')
                    {
                        //sanitize HTML
                        $description=str_replace('<input','< input',$description); //remove input
                        $description=str_replace('<input','< input',$description); //remove input 
                        $description=str_replace('<output','< output',$description); //remove input 
                        $description=str_replace('<textarea','< textarea',$description); //remove input 
                        $description=str_replace('<select','< select',$description); //remove input 
                        $description=str_replace('<data','< data',$description); //remove input 
                        $description=str_replace('<datalist','< datalist',$description); //remove input 
                        $description=str_replace('<menu','< menu',$description); //remove input 
                        if(isset($c_FromMessage)) {$description=$description_header.$c_FromMessage.$description;}
                        $qry=$db->prepare("UPDATE `tincidents` SET `description`=:description WHERE `id`=:id");
                        $qry->execute(array('description' => $description,'id' => $c_ticket_number));

                       
                    }
                    else //html case
                    {
                        //update img link
                        $tabAttachments = $mail->getAttachments();
                        foreach ($tabAttachments as $tabAttachment){
                            if($tabAttachment->disposition=="inline" || $tabAttachment->disposition=="INLINE" || $tabAttachment->disposition==null)  //case image inside in mail
                            {
                                $c_name_file_original = basename($tabAttachment->filePath);
                                $c_name_file_rename = $c_ticket_number.'_'.$c_name_file_original;
                                $message=str_replace($c_name_file_original,$c_name_file_rename,$message);
                            }
                        }
                        
                        //sanitize HTML
                        $message=str_replace('<input','< input',$message); //remove input 
                        $message=str_replace('<output','< output',$message); //remove input 
                        $message=str_replace('<textarea','< textarea',$message); //remove input 
                        $message=str_replace('<select','< select',$message); //remove input 
                        $message=str_replace('<data','< data',$message); //remove input 
                        $message=str_replace('<datalist','< datalist',$message); //remove input 
                        $message=str_replace('<menu','< menu',$message); //remove input 
                        $message=str_replace("text-decoration:underline;", "", $message);
                        $message=preg_replace('/(<(base)\b[^>]*>)/is', "", $message); //remove base link
                        $message=preg_replace('/(<(body)\b[^>]*>)/is', "<body>", $message); //remove body attribute such as background #4722
                        
                        //convert encoding from ISO TO UTF8
                        $message_encoding=mb_detect_encoding($message, ['ASCII', 'UTF-8', 'ISO-8859-1'], true);
                        if($message_encoding=='ISO-8859-1') {
                            $message = mb_convert_encoding($message, 'UTF-8', 'ISO-8859-1'); 

                            if($cmd) {
                                echo '['.$mailbox.'] [mail '.$count.'] MESSAGE ENCODING CONVERSION : FROM ISO-8859-1 TO UTF-8'.PHP_EOL;
                            } else {
                                echo '['.$mailbox.'] [mail '.$count.'] MESSAGE ENCODING CONVERSION : <span style="color:green">FROM ISO-8859-1 TO UTF-8</span><br />';
                            }
                        }

                        //add header informations
                        $message=$description_header.$message;
                       
                        
                        //update description
                        $qry=$db->prepare("UPDATE `tincidents` SET `description`=:description WHERE `id`=:id");
                        $qry->execute(array('description' => $message,'id' => $c_ticket_number));
                    }
                    
                    //send mail to user 
                    if($rparameters['mail_auto_user_newticket'])
                    {
                        $send=1;
                        $_GET['id']=$c_ticket_number;
                        include(__DIR__.'/../core/mail.php');
                        if($cmd) {
                            echo '['.$mailbox.'] [mail '.$count.'] SEND Mail to sender : OK (mail_auto_user_newticket parameter enable)'.PHP_EOL;
                        } else {
                            echo '['.$mailbox.'] [mail '.$count.'] SEND Mail to sender : <span style="color:green">OK (mail_auto_user_newticket parameter enable)</span><br />';
                        }
                    }
                    //send mail to admin 
                    if($rparameters['mail_newticket'] && $rparameters['mail_newticket_address'])
                    {
                        $qry = $db->prepare("SELECT tusers.firstname,tusers.lastname,tincidents.title,tincidents.description FROM tusers,tincidents WHERE tusers.id=tincidents.user AND tincidents.id=:ticket_id");
                        $qry->execute(array('ticket_id' => $c_ticket_number));
                        $ticket_data=$qry->fetch();
                        $qry->closeCursor();
                        
                        $from=$rparameters['mail_from_adr'];
                        $to=$rparameters['mail_newticket_address'];
                        $object=T_('Un nouveau ticket a été déclaré par ').$ticket_data['lastname'].' '.$ticket_data['firstname'].' : '.$ticket_data['title'];
                        $message = '
                        '.T_('Le ticket').' n°'.$c_ticket_number.' '.T_("a été déclaré par l'utilisateur").' '.$ticket_data['lastname'].' '.$ticket_data['firstname'].'.<br />
                        <br />
                        <u>'.T_('Objet').':</u><br />
                        '.$ticket_data['title'].'<br />		
                        <br />	
                        <u>'.T_('Description').':</u><br />
                        '.$ticket_data['description'].'<br />
                        <br />
                        '.T_("Pour plus d'informations vous pouvez consulter le ticket sur").' <a href="'.$rparameters['server_url'].'/index.php?page=ticket&id='.$c_ticket_number.'">'.$rparameters['server_url'].'/index.php?page=ticket&id='.$c_ticket_number.'</a>.
                        ';
                        $mail_auto=true;
                        require(__DIR__.'/../core/message.php');

                        if($cmd) {
                            echo '['.$mailbox.'] [mail '.$count.'] Send mail to administrator : OK (mail_newticket parameter enable)'.PHP_EOL;
                        } else {
                            echo '['.$mailbox.'] [mail '.$count.'] Send mail to administrator : <span style="color:green">OK (mail_newticket parameter enable)</span><br />';
                        }
                    }
                }
                //post treatment actions
                if($rparameters['imap_post_treatment']=='move' && $rparameters['imap_post_treatment_folder'])
                {
                    //move mail
                    $con_mailbox->moveMail($tab_MailsInfo->uid,$rparameters['imap_post_treatment_folder']);

                    if($cmd) {
                        echo '['.$mailbox.'] [mail '.$count.'] Post-treatment action : MOVE ('.$rparameters['imap_post_treatment_folder'].' folder)'.PHP_EOL;
                    } else {
                        echo '['.$mailbox.'] [mail '.$count.'] Post-treatment action : <span style="color:green">MOVE ('.$rparameters['imap_post_treatment_folder'].' folder)</span><br />';
                    }
                }elseif($rparameters['imap_post_treatment']=='delete')
                { 
                    //delete mail
                    imap_delete($con_mailbox->getImapStream(),$tab_MailsInfo->uid,FT_UID);

                    if($cmd) {
                        echo '['.$mailbox.'] [mail '.$count.'] Post-treatment action : DELETE'.PHP_EOL;
                    } else {
                        echo '['.$mailbox.'] [mail '.$count.'] Post-treatment action : <span style="color:green">DELETE</span><br />';						
                    }
                } else {
                    //unread mail
                    if($cmd) {
                        echo '['.$mailbox.'] [mail '.$count.'] Post-treatment action : UNREAD'.PHP_EOL;
                    } else {
                        echo '['.$mailbox.'] [mail '.$count.'] Post-treatment action : <span style="color:green">UNREAD</span><br />';					
                    }
                }
            } //END for each no blacklist mail
        } //END for each unread mail 
    } //END for each mail
}
//display time time by mailboxes
$elapsed_time=(round(microtime(true) - $start))-$previous_time;
if($cmd) {
    echo '['.$mailbox.'] Time : '.$elapsed_time.' sec.'.PHP_EOL;
} else {
    echo '['.$mailbox.'] Time : '.$elapsed_time.' sec.<br />';				
}
$previous_time=round(microtime(true) - $start);
if(!$cmd) {echo '<hr />';} else {echo PHP_EOL;}
?>