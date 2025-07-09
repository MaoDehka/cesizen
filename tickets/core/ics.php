<?php
################################################################################
# @Name : /core/ics.php
# @Description : generate ics link for external calendar app
# @Call : external app
# @Parameters : 
# @Author : Flox
# @Create : 26/06/2023
# @Update : 28/26/2023
# @Version : 3.2.37
################################################################################

//init var 
if(!isset($_GET['key'])) {$_GET['key']='';}

//db connection
require('./../connect.php');

//load functions
require('functions.php');

//load parameters table
$qry=$db->prepare("SELECT * FROM `tparameters`");
$qry->execute();
$rparameters=$qry->fetch();
$qry->closeCursor();

//check parameters
if(!$rparameters['planning']) { echo 'ERROR : planning function disable'; exit;}
if(!$rparameters['planning_ics']) { echo 'ERROR : planning share disable'; exit;}

//check key exist
if(!$_GET['key']) { echo 'ERROR : key parameter missing'; exit;}

//check key parameters
$technician_id=gs_crypt($_GET['key'], 'd' , $rparameters['server_private_key']);
if(!is_numeric($technician_id)) { echo 'ERROR : wrong key'; exit;}

//check if technician exist
$qry=$db->prepare("SELECT `id` FROM `tusers` WHERE id=:id");
$qry->execute(array('id' => $technician_id));
$technician=$qry->fetch();
$qry->closeCursor();
if(!$technician['id']) { echo 'ERROR : wrong technician id'; exit;}

//generate ics
header("Content-Type: text/Calendar");
header("Content-Disposition: inline; filename=calendar.ics");

//common part
echo "BEGIN:VCALENDAR\n";
    echo "VERSION:2.0\n";
    echo "X-WR-TIMEZONE:Europe/Paris\n";
    echo "BEGIN:VTIMEZONE\n";
        echo "TZID:Europe/Paris\n";
        echo "X-LIC-LOCATION:Europe/Paris\n";
        echo "BEGIN:DAYLIGHT\n";
            echo "TZOFFSETFROM:+0100\n";
            echo "TZOFFSETTO:+0200\n";
            echo "TZNAME:CEST\n";
            echo "DTSTART:19700329T020000\n";
            echo "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU\n";
        echo "END:DAYLIGHT\n";
        echo "BEGIN:STANDARD\n";
            echo "TZOFFSETFROM:+0200\n";
            echo "TZOFFSETTO:+0100\n";
            echo "TZNAME:CET\n";
            echo "DTSTART:19701025T030000\n";
            echo "RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU\n";
        echo "END:STANDARD\n";
    echo "END:VTIMEZONE\n";
    echo "PRODID:GestSup\n";
    echo "METHOD:REQUEST\n"; 
    //generate event section
    $qry=$db->prepare("SELECT * FROM `tevents` WHERE `technician`=:technician");
    $qry->execute(array('technician' => $technician['id']));
    while($event=$qry->fetch()) {
        //date conversion
        $event['date_start']=str_replace('-','',$event['date_start']);
        $event['date_start']=str_replace(':','',$event['date_start']);
        $event['date_start']=str_replace(' ','T',$event['date_start']);
        $event['date_end']=str_replace('-','',$event['date_end']);
        $event['date_end']=str_replace(':','',$event['date_end']);
        $event['date_end']=str_replace(' ','T',$event['date_end']);
        $date=date('Ymd');
        $date=$date.'T'.date('His');
        if($event['date_end']=='00000000T000000') {$event['date_end']=$event['date_start'];}
        echo "BEGIN:VEVENT\n";
            echo "DTSTART;TZID=Europe/Paris:$event[date_start]\n";
            echo "DTEND;TZID=Europe/Paris:$event[date_end]\n";
            echo "SUMMARY:$event[title]\n";
            echo "LOCATION:\n";
            echo "DESCRIPTION:$event[title]\n";
            echo "UID:$event[id]\n";
            echo "SEQUENCE:0\n";
            echo "DTSTAMP:$date\n";
        echo "END:VEVENT\n";
    }
    $qry->closeCursor();
echo "END:VCALENDAR";
?>