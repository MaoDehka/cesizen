<?php
################################################################################
# @Name : pie_states.php
# @Description : Display Statistics chart 3
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
$query = $db->query("SELECT COUNT(`tincidents`.`id`) FROM `tincidents` WHERE disable='0'");
$rtotal=$query->fetch();

$libchart=T_('Tickets par états');
$unit=T_('tickets');
$query1 = "SELECT `tstates`.`name` AS `state`, COUNT(`tincidents`.`id`) AS `nb` FROM `tincidents` 
INNER JOIN `tstates` ON (`tincidents`.`state`=`tstates`.`id`)
INNER JOIN `tusers` ON (`tincidents`.`user`=`tusers`.`id`)
WHERE 
`tusers`.`company` LIKE '$_POST[company]' AND
`tincidents`.`disable` LIKE '0' AND
`tincidents`.`technician` LIKE '$_POST[tech]' AND
`tincidents`.`u_service` LIKE '$_POST[service]' 
$where_service 
$where_agency 
$where_tech_group AND
$where_state AND
`tincidents`.`type` LIKE '$_POST[type]' AND
`tincidents`.`criticality` like '$_POST[criticality]' AND
`tincidents`.`category` LIKE '$_POST[category]' AND
`tincidents`.`date_create` LIKE '%-$_POST[month]-%' AND
`tincidents`.`date_create` LIKE '$_POST[year]-%'
GROUP BY `tstates`.`number`
ORDER BY `nb`
DESC
";
$query=$db->query($query1);
while ($row = $query->fetch()) 
{
	array_push($values, $row[1]);
	array_push($xnom, T_($row['state']));
} 
$container='container3';
include('./stats/graph_pie.php');
echo '<div class="card-body bgc-dark-l4 p-0 border-1 brc-default-l2 radius-2 px-1 mx-n2 mx-md-0 h-100 d-flex align-items-center" id="'.$container.'"></div>';
if ($rparameters['debug'])echo $query1;
?>