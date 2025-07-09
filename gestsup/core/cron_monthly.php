<?php
################################################################################
# @Name : cron_monthly.php
# @Description : execute tasks in time interval
# @Call : ./index.php
# @Parameters : 
# @Author : Flox
# @Create : 20/10/2020
# @Update : 01/11/2023
# @Version : 3.2.42
################################################################################

//update last execution time
if($rparameters['cron_monthly']!='12') {$new_month=$rparameters['cron_monthly']+1;} else {$new_month=1;}
$qry=$db->prepare("UPDATE `tparameters` SET `cron_monthly`=:cron_monthly");
$qry->execute(array('cron_monthly' => $new_month));

//update current var
$rparameters['cron_monthly']=$new_month;

//auto clean logs
$qry=$db->prepare("DELETE FROM tlogs WHERE DATE(`date`) < (CURDATE() - INTERVAL 365 DAY)");
$qry->execute();

//telemetry
if($rparameters['telemetry'] && (!$rparameters['server_date_install'] || $rparameters['server_date_install']=='0000-00-00' || $rparameters['server_date_install']=='0001-11-30' || $rparameters['server_date_install']<'2007-01-01')) {
    //update date install server
    $qry=$db->prepare("SELECT MIN(date) FROM `tthreads`;");
    $qry->execute();
    $min_date=$qry->fetch();
    $qry->closeCursor();
    if($min_date[0] && $min_date[0]!='0000-00-00' && $min_date[0]!='0001-11-30')
    {
        $qry=$db->prepare("UPDATE `tparameters` SET `server_date_install`=:server_date_install");
        $qry->execute(array('server_date_install' => $min_date[0]));
    }
	
    //update telemetry
    if($rparameters['debug']) {echo 'TELEMETRY';}
    Telemetry();
}

//clean old conf file
$arrFiles = scandir('backup');
foreach($arrFiles as $file)
{
    if(preg_match('/configuration/',$file)) {
        unlink("backup/$file");
    }
}
?>