<?php
################################################################################
# @Name : pie_company.php
# @Description : Display Statistics of chart 8 
# @call : /stat.php
# @parameters : 
# @Author : Flox
# @Create : 08/03/2014
# @Update : 19/12/2023
# @Version : 3.2.46
################################################################################

//prevent direct access
if(!isset($_SESSION['user_id'])) {header('HTTP/1.0 403 Forbidden'); exit;}

//check existing company
$qry=$db->prepare("SELECT COUNT(`id`) FROM `tcompany` WHERE `disable`='0'");
$qry->execute();
$company_counter=$qry->fetch();
$qry->closeCursor();

if($company_counter['0']>0)
{
	//array declaration
	$values = array();
	$xnom = array();

	//display title
	$libchart=T_('Répartition du nombre de tickets par sociétés');
	$unit=T_('tickets');

	//query
	$query1 = "SELECT `tcompany`.`name` AS `company`, COUNT(`tincidents`.`id`) AS `nb`
	FROM `tincidents`, `tcompany`, `tusers`
	WHERE 
	`tcompany`.id=`tusers`.`company` AND
	`tusers`.id=`tincidents`.`user` AND
	`tincidents`.`disable`='0' AND
	`tusers`.`company`!='0' AND
	`tusers`.`company` LIKE '$_POST[company]' AND
	`tincidents`.`type` LIKE '$_POST[type]' AND
	`tincidents`.`criticality` LIKE '$_POST[criticality]' AND
	`tincidents`.`u_service` LIKE '$_POST[service]' 
	$where_service 
	$where_agency 
	$where_tech_group AND
	$where_state AND
	`tincidents`.`category` LIKE '$_POST[category]' AND
	`tincidents`.`date_create` LIKE '%-$_POST[month]-%' AND
	`tincidents`.`date_create` LIKE '$_POST[year]-%' AND
	`tincidents`.`technician` LIKE '$_POST[tech]'
	GROUP BY `tcompany`.`name` 
	ORDER BY `nb`
	DESC ";

	if ($rparameters['debug']) {echo $query1;}
	$query = $db->query($query1);
	while ($row=$query->fetch()) 
	{
		$name=substr($row[0],0,35);
		$name=str_replace("'","\'",$name); 
		array_push($values, $row[1]);
		array_push($xnom, $name);
	} 
	$query->closecursor();
	$container='container8';
	include('./stats/graph_pie.php');
	echo '<div class="card-body bgc-dark-l4 p-0 border-1 brc-default-l2 radius-2 px-1 mx-n2 mx-md-0 h-100 d-flex align-items-center" id="'.$container.'"></div>';
	if ($rparameters['debug'])echo $query1;
} else {
	//hide graph no company 
}
?>