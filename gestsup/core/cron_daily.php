<?php
################################################################################
# @Name : cron_daily.php
# @Description : execute tasks in time interval
# @Call : ./index.php
# @Parameters : 
# @Author : Flox
# @Create : 09/05/2019
# @Update : 15/12/2023
# @Version : 3.2.46
################################################################################

//update last execution time
$new_date=date_create($rparameters['cron_daily']);
date_add($new_date, date_interval_create_from_date_string('1 days'));
$new_date=date_format($new_date, 'Y-m-d');
$qry=$db->prepare("UPDATE `tparameters` SET `cron_daily`=:cron_daily");
$qry->execute(array('cron_daily' => $new_date));

//update current var
$rparameters['cron_daily']=$new_date;

if($rparameters['debug']) {echo 'CRON DAILY : '.$rparameters['cron_daily'].'<br />';}

//case app installation
$diff_days = strtotime(date('Y-m-d')) - strtotime($rparameters['cron_daily']);
$diff_days=abs(round($diff_days / 86400));
if($diff_days>60)
{
    $qry=$db->prepare("UPDATE `tparameters` SET `cron_daily`=:cron_daily");
    $qry->execute(array('cron_daily' => date('Y-m-d')));
}

//autoclose ticket parameter
if($rparameters['ticket_autoclose']) {require('./core/auto_close.php');}

//user validation parameter
if($rparameters['user_validation']) {require('./core/user_validation.php');}

//auto clean old token
$qry=$db->prepare("DELETE FROM `ttoken` WHERE DATE(`date`) < (CURDATE() - INTERVAL 7 DAY)");
$qry->execute();

//clean login ip attempt
$qry=$db->prepare("DELETE FROM `tauth_attempts`");
$qry->execute();

//check for application update 
CheckUpdate();

//check recurrent ticket creation
if($rparameters['ticket_recurrent_create'])
{
    //daily ticket creation
    $qry2=$db->prepare("SELECT `id`,`incident` FROM `ttemplates` WHERE `frequency`='daily' AND `date_start`<=:date_to_check AND (`last_execution_date`!=:date_to_check OR `last_execution_date`='0000-00-00')");
    $qry2->execute(array('date_to_check' => $rparameters['cron_daily']));
    while($template=$qry2->fetch()) 
    {
        //create ticket
        $_POST['duplicate']=1;
        $rright['ticket_template']=2;
        $_POST['template']=$template['incident'];
        $ticket_auto_create=1;
        require('includes/ticket_template.php');

        //update last execution date
        $qry3=$db->prepare("UPDATE `ttemplates` SET `last_execution_date`=:last_execution_date WHERE `id`=:id");
        $qry3->execute(array('last_execution_date' => $rparameters['cron_daily'],'id' => $template['id']));

        //debug
        if($rparameters['debug']) {echo 'TICKET_RECURRENT : create daily ticket '.$newticketid.' from template ticket number '.$template['incident'].'<br />';}
        
        //log
	    LogIt('recurrent_ticket','Create daily ticket '.$newticketid.' from template ticket number '.$template['incident'],$_SESSION['user_id']);
    }
    $qry2->closeCursor();    
    
    //weekly ticket creation
    $qry2=$db->prepare("SELECT `id`,`incident` FROM `ttemplates` WHERE `frequency`='weekly' AND `date_start`<=:date_to_check AND ((:date_to_check=DATE(`last_execution_date` + INTERVAL 7 DAY)) OR `last_execution_date`='0000-00-00')");
    $qry2->execute(array('date_to_check' => $rparameters['cron_daily']));
    while($template=$qry2->fetch()) 
    {
        //create ticket
        $_POST['duplicate']=1;
        $rright['ticket_template']=2;
        $_POST['template']=$template['incident'];
        $ticket_auto_create=1;
        require('includes/ticket_template.php');

        //update last execution date
        $qry3=$db->prepare("UPDATE `ttemplates` SET `last_execution_date`=:last_execution_date WHERE `id`=:id");
        $qry3->execute(array('last_execution_date' => $rparameters['cron_daily'],'id' => $template['id']));

        //debug
        if($rparameters['debug']) {echo 'TICKET_RECURRENT : create weekly ticket '.$newticketid.' from template ticket number '.$template['incident'].'<br />';}
        
        //log
	    LogIt('recurrent_ticket','Create weekly ticket '.$newticketid.' from template ticket number '.$template['incident'],$_SESSION['user_id']);
    }
    $qry2->closeCursor();
    
    //monthly ticket creation
    $qry2=$db->prepare("SELECT `id`,`incident` FROM `ttemplates` WHERE `frequency`='monthly' AND `date_start`<=:date_to_check AND ((:date_to_check=DATE(`last_execution_date` + INTERVAL 30 DAY)) OR `last_execution_date`='0000-00-00')");
    $qry2->execute(array('date_to_check' => $rparameters['cron_daily']));
    while($template=$qry2->fetch()) 
    {
        //create ticket
        $_POST['duplicate']=1;
        $rright['ticket_template']=2;
        $_POST['template']=$template['incident'];
        $ticket_auto_create=1;
        require('includes/ticket_template.php');

        //update last execution date
        $qry3=$db->prepare("UPDATE `ttemplates` SET `last_execution_date`=:last_execution_date WHERE `id`=:id");
        $qry3->execute(array('last_execution_date' => $rparameters['cron_daily'],'id' => $template['id']));

        //debug
        if($rparameters['debug']) {echo 'TICKET_RECURRENT : create monthly ticket '.$newticketid.' from template ticket number '.$template['incident'].'<br />';}
        
        //log
	    LogIt('recurrent_ticket','Create monthly ticket '.$newticketid.' from template ticket number '.$template['incident'],$_SESSION['user_id']);
    }
    $qry2->closeCursor();

    //yearly ticket creation
    $qry2=$db->prepare("SELECT `id`,`incident` FROM `ttemplates` WHERE `frequency`='yearly' AND `date_start`<=:date_to_check AND ((:date_to_check=DATE(`last_execution_date` + INTERVAL 365 DAY)) OR `last_execution_date`='0000-00-00')");
    $qry2->execute(array('date_to_check' => $rparameters['cron_daily']));
    while($template=$qry2->fetch()) 
    {
        //create ticket
        $_POST['duplicate']=1;
        $rright['ticket_template']=2;
        $_POST['template']=$template['incident'];
        $ticket_auto_create=1;
        require('includes/ticket_template.php');

        //update last execution date
        $qry3=$db->prepare("UPDATE `ttemplates` SET `last_execution_date`=:last_execution_date WHERE `id`=:id");
        $qry3->execute(array('last_execution_date' => $rparameters['cron_daily'],'id' => $template['id']));

        //debug
        if($rparameters['debug']) {echo 'TICKET_RECURRENT : create yearly ticket '.$newticketid.' from template ticket number '.$template['incident'].'<br />';}
        
        //log
	    LogIt('recurrent_ticket','Create yearly ticket '.$newticketid.' from template ticket number '.$template['incident'],$_SESSION['user_id']);
    }
    $qry2->closeCursor();
}

//telemetry
if($rparameters['server_date_install'] && $rparameters['telemetry'])
{
    $day=explode('-',$rparameters['server_date_install']);
    $day=$day[2];
    if($day==date('d'))
    {
        if($rparameters['debug']) {echo 'TELEMETRY';}
        Telemetry();
    }
}
?>