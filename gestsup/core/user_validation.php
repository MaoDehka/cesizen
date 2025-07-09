<?php
################################################################################
# @Name : ./core/user_validation.php
# @Description : Send automatic notification to sender to check if ticket is really close
# @Call : ./core/cron.php
# @Parameters : 
# @Author : Flox
# @Create : 16/10/2020
# @Update : 27/05/2022
# @Version : 3.2.24
################################################################################

if($rparameters['user_validation'] && ($rparameters['user_validation_perimeter']=='all' || $rparameters['user_validation_perimeter']=='mark') && is_numeric($rparameters['user_validation_delay']))
{
    if($rparameters['debug']) {echo '<b>DEBUG MODE</b> : user validation <br /> check date : '.$rparameters['cron_daily'].'<br />';}
   
    $qry=$db->prepare("SELECT `id`,`user_validation`,`user`,`technician`,`category`,`subcat`,`title`,`description`,DATE(date_res) AS date_res, DATE_ADD(DATE(date_res), INTERVAL :delay DAY) AS date_notify FROM `tincidents` WHERE `disable`='0' AND `user`!='0'  AND `state`='3' AND DATE_ADD(DATE(date_res), INTERVAL :delay DAY)=:date;");
    $qry->execute(array('date' => $rparameters['cron_daily'],'delay' => $rparameters['user_validation_delay']));
    while($ticket=$qry->fetch()) 
    {
        $send_notification=0;

        //get user informations
        $qry2=$db->prepare("SELECT `id`,`firstname`,`lastname`,`mail`,`profile` FROM `tusers` WHERE id=:id");
        $qry2->execute(array('id' => $ticket['user']));
        $user=$qry2->fetch();
        $qry2->closeCursor();

        if($rparameters['user_validation_perimeter']=='all') //case all ticket
        {
            if($rparameters['debug']) {echo 'Ticket found :  id='.$ticket['id'].' perimeter=all user_validation='.$ticket['user_validation'].' title='.$ticket['title'].' date_res='.$ticket['date_res'].' date_notify='.$ticket['date_notify'].'<br />';}
            $send_notification=1;
        }
        elseif ($rparameters['user_validation_perimeter']=='mark' && $ticket['user_validation'])  //case marked ticket
        {
            if($rparameters['debug']) {echo 'Ticket found :  id='.$ticket['id'].' perimeter=mark user_validation='.$ticket['user_validation'].' title='.$ticket['title'].' date_res='.$ticket['date_res'].' date_notify='.$ticket['date_notify'].'<br />';}
            $send_notification=1;
        }

        if($send_notification && $user['mail'] && $rparameters['mail'])
        {
            //exclusion check sender is not technician or admin
            if($user['profile']==0 || $user['profile']==4)
            {
                if($rparameters['debug']) {echo '> Exclusion : sender profile admin or technician<br />';}
                $send_notification=0;
            }else {
                //exclusion check category
                $qry2=$db->prepare("SELECT `id` FROM `tparameters_user_validation_exclusion` WHERE category=:category AND category!=0");
                $qry2->execute(array('category' => $ticket['category']));
                $category=$qry2->fetch();
                $qry2->closeCursor();
                //exclusion check subcat
                $qry2=$db->prepare("SELECT `id` FROM `tparameters_user_validation_exclusion` WHERE subcat=:subcat AND subcat!=0");
                $qry2->execute(array('subcat' => $ticket['subcat']));
                $subcat=$qry2->fetch();
                $qry2->closeCursor();
                if(!empty($category['id']) || !empty($subcat['id']))
                {
                    if($rparameters['debug']) {echo '> Exclusion : ticket category or subcat<br />';}
                    $send_notification=0;
                } else {
                    if($rparameters['debug']) {echo '> Send notification <br />';}
                    $send_notification=1;
                }
            }

            //send notification for this ticket
            if($send_notification)
            {
                if($rparameters['debug']) {echo '> Mail for ticket '.$ticket['id'].' to user '.$user['mail'].'<br />';}

                //generate token
                $token = bin2hex(random_bytes(32));
                $qry2=$db->prepare("INSERT INTO `ttoken` (`date`,`token`,`action`,`ticket_id`) VALUES (NOW(),:token,'user_validation',:ticket_id)");
                $qry2->execute(array('token' => $token,'ticket_id' => $ticket['id']));

                //get technician informations
                $qry2=$db->prepare("SELECT `firstname`,`lastname`,`mail` FROM `tusers` WHERE id=:id");
                $qry2->execute(array('id' => $ticket['technician']));
                $technician=$qry2->fetch();
                $qry2->closeCursor();
                if(empty($technician['firstname'])) {$technician['firstname']='';}
                if(empty($technician['lastname'])) {$technician['lastname']='';}

                //generate mail
                if(!$rparameters['mail_from_adr']){if($technician['mail']) {$from=$technician['mail'];}} else {$from=$rparameters['mail_from_adr'];}
                $to=$user['mail'];
                $object='[GestSup - '.$rparameters['company'].'] '.T_('Validation de la résolution de votre ticket n°').$ticket['id'].' : '.$ticket['title'];
                $message='
                <html>
                    <head>
                        <meta charset="UTF-8" />
                    </head>
                    <body>
                        '.T_('Bonjour').',<br />
                        <br />
                        '.T_('Le ticket n°').$ticket['id'].' ('.$ticket['title'].') a été clôturé le '.DateToDisplay($ticket['date_res']).'.<br />
                        '.T_('Si vous estimez que ce dernier n’est pas résolu, vous pouvez cliquer sur le bouton ci-après').' :<br />
                        <br />
                        <a href="'.$rparameters['server_url'].'/user_validation.php?token='.$token.'">
                            <button style="background-color: #008bb2; border: none; color: white; padding: 7px 7px; text-align: center; text-decoration: none; display: inline-block;" name="button">
                                '.T_('Rouvrir mon ticket').'
                            </button>
                        </a><br />
                        <br />
                        <u>'.T_('Description du ticket').' :</u><br />
                        '.$ticket['description'].' <br />
                        <br />
                        '.T_('Cordialement').',<br />
                        '.$technician['firstname'].' '.$technician['lastname'].'
                    </body>
                </html>
                ';
                $ticket_id=$ticket['id'];
                $user_id=$user['id'];
                require('./core/message.php');
                if($mail_send) {logit('user validation', 'Successful sending user validation email for the ticket '.$ticket_id,$user_id);}
            }
        }
    }
    $qry->closeCursor();    
}
?>