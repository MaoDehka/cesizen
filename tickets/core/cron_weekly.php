<?php
################################################################################
# @Name : cron_weekly.php
# @Description : execute tasks in time interval
# @Call : ./index.php
# @Parameters : 
# @Author : Flox
# @Create : 27/05/2024
# @Update : 27/05/2024
# @Version : 3.2.51
################################################################################

//update last execution time
$qry=$db->prepare("UPDATE `tparameters` SET `cron_weekly`=:cron_weekly");
$qry->execute(array('cron_weekly' => date("W", time())));

//include plugin
$section='cron_weekly';
include('plugin.php');

?>