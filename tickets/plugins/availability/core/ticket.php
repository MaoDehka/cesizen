<?php
################################################################################
# @Name : /plugins/availability/core/ticket.php
# @Description : actions on ticket field
# @Call : /plugins.php
# @Parameters : 
# @Author : Flox
# @Create : 27/01/2021
# @Update : 26/08/2022
# @Version : 3.2.26
################################################################################

//initialize variables
if(!isset($_POST['start_availability'])) $_POST['start_availability'] = '';
if(!isset($_POST['end_availability'])) $_POST['end_availability'] = '';
if(!isset($_POST['availability_planned'])) $_POST['availability_planned'] = '';

if(!isset($start_availability)) $start_availability = '';
if(!isset($end_availability)) $end_availability = '';

//secure strings
$_POST['start_availability']=htmlspecialchars($_POST['start_availability'], ENT_QUOTES, 'UTF-8');
$_POST['end_availability']=htmlspecialchars($_POST['end_availability'], ENT_QUOTES, 'UTF-8');
$_POST['availability_planned']=htmlspecialchars($_POST['availability_planned'], ENT_QUOTES, 'UTF-8');

//convert date
if($_POST['start_availability'])
{
    $start_availability=DateTime::createFromFormat('d/m/Y H:i:s',$_POST['start_availability']);
    $start_availability=$start_availability->format('Y-m-d H:i:s');
    $end_availability=DateTime::createFromFormat('d/m/Y H:i:s',$_POST['end_availability']);
    $end_availability=$end_availability->format('Y-m-d H:i:s');
}

if(!$error)
{
    if($_GET['action']=='new') //ticket creation
    {
        
    }elseif($_GET['id']) { //ticket update
        $qry=$db->prepare("UPDATE `tincidents` SET `start_availability`=:start_availability,`end_availability`=:end_availability,`availability_planned`=:availability_planned WHERE `id`=:id");
        $qry->execute(array('start_availability' => $start_availability,'end_availability' => $end_availability,'availability_planned' => $_POST['availability_planned'],'id' => $_GET['id']));
    }
}

//defaults values for new tickets
if(!isset($globalrow['start_availability'])) $globalrow['start_availability'] = date("Y-m-d").' '.date("H:i:s");
if(!isset($globalrow['end_availability'])) $globalrow['end_availability'] = date("Y-m-d").' '.date("H:i:s");
if(!isset($globalrow['availability_planned'])) $globalrow['availability_planned'] = 0;
?>