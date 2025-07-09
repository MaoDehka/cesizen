<?php
################################################################################
# @Name : pie_tickets_tech.php
# @Description : Display Statistics chart 1
# @call : /stat.php
# @parameters : 
# @Author : Flox
# @Create : 15/02/2014
# @Update : 19/12/2023
# @Version : 3.2.46
################################################################################

//prevent direct access
if(!isset($_SESSION['user_id'])) {header('HTTP/1.0 403 Forbidden'); exit;}

$values = array();
$xnom = array();
$libchart=T_('Tickets par techniciens');
$unit=T_('tickets');

//total
$query=$db->query("SELECT COUNT(`id`) FROM `tincidents` WHERE `disable`='0'");
$month1=$query->fetch();

$query1 = "SELECT CONCAT_WS('. ', left(`tusers_tech`.`firstname`, 1), `tusers_tech`.`lastname`) AS technician, `tgroups`.`name` AS group_name, COUNT(`tincidents`.`id`) as tickets FROM `tincidents` 
INNER JOIN `tusers` AS tusers_tech ON (`tincidents`.`technician`=`tusers_tech`.`id`) 
INNER JOIN `tusers` AS tusers_user ON (`tincidents`.`user`=`tusers_user`.`id`) 
INNER JOIN `tgroups` ON (`tincidents`.t_group=tgroups.id) 
WHERE 
`tusers_user`.`company` LIKE '$_POST[company]' AND
`tincidents`.`technician` LIKE '$_POST[tech]' AND
`tincidents`.`type` LIKE '$_POST[type]' AND
`tincidents`.`u_service` LIKE '$_POST[service]' 
$where_service 
$where_agency 
$where_tech_group AND
$where_state AND
`tincidents`.`criticality` LIKE '$_POST[criticality]' AND
`tincidents`.category LIKE '$_POST[category]' AND
`tincidents`.`date_create` LIKE '%-$_POST[month]-%' AND
`tincidents`.`date_create` LIKE '$_POST[year]-%' AND
`tincidents`.`disable` LIKE '0'
GROUP BY `tusers_tech`.`id`,`tgroups`.`id`
ORDER BY `tickets` DESC";

$query=$db->query($query1);
while ($row = $query->fetch()) 
{
	if($row['group_name'] != 'Aucun') {
		$name='[G] '.addslashes(substr($row['group_name'],0,42));
	} else {
		$name=addslashes(substr($row['technician'],0,42));
	}
	array_push($values, $row['tickets']);
	array_push($xnom, $name);
} 	
$container='container2';
include('./stats/graph_pie.php');
echo '<div class="card-body bgc-dark-l4 p-0 border-1 brc-default-l2 radius-2 px-1 mx-n2 mx-md-0 h-100 d-flex align-items-center" id="'.$container.'"></div>';
if ($rparameters['debug']) echo $query1;
?>