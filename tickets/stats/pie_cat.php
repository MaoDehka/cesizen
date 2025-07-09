<?php
################################################################################
# @Name : pie_cat.php
# @Description : Display Statistics of chart 2 number of tickets by categories
# @call : /stat.php
# @parameters : 
# @Author : Flox
# @Create : 15/02/2014
# @Update : 19/12/2023
# @Version : 3.2.46
################################################################################

//prevent direct access
if(!isset($_SESSION['user_id'])) {header('HTTP/1.0 403 Forbidden'); exit;}

//array declaration
$values = array();
$xnom = array();

//display title
$libchart=T_('Répartition du nombre de tickets par catégories');
if ($_POST['category']!="%")
{
	$query1 = "SELECT `tsubcat`.`name` AS `cat`, COUNT(`tincidents`.`id`) AS `nb` FROM `tincidents` 
	INNER JOIN `tsubcat` ON (`tincidents`.`subcat`=`tsubcat`.`id`)
	INNER JOIN `tusers` ON (`tincidents`.`user`=`tusers`.`id`)
	WHERE 
	`tusers`.`company` LIKE '$_POST[company]' AND
	`tincidents`.`category` LIKE '$_POST[category]' AND
	`tincidents`.`disable`='0' AND
	`tincidents`.`type` LIKE '$_POST[type]' AND
	`tincidents`.`u_service` LIKE '$_POST[service]' 
	$where_service 
	$where_agency 
	$where_tech_group AND
	$where_state AND
	`tincidents`.`criticality` LIKE '$_POST[criticality]' AND
	`tincidents`.`date_create` LIKE '%-$_POST[month]-%' AND
	`tincidents`.`date_create` LIKE '$_POST[year]-%' AND
	`tincidents`.`technician` LIKE '$_POST[tech]'
	GROUP BY `tsubcat`.`name` 
	ORDER BY `nb`
	DESC limit 0,10
	";
} else {
	$query1 = "SELECT `tcategory`.`name` AS `cat`, COUNT(`tincidents`.`id`) AS `nb` FROM `tincidents` 
		INNER JOIN `tcategory` ON (`tincidents`.`category`=`tcategory`.`id`)
		INNER JOIN `tusers` ON (`tincidents`.`user`=`tusers`.`id`)
		WHERE 
		`tusers`.`company` LIKE '$_POST[company]' AND
		`tincidents`.`disable`='0' AND
    	`tincidents`.`type` LIKE '$_POST[type]' AND
		`tincidents`.`u_service` LIKE '$_POST[service]' 
		$where_service 
		$where_agency 
		$where_tech_group AND
		$where_state AND
    	`tincidents`.`criticality` LIKE '$_POST[criticality]' AND
    	`tincidents`.`date_create` LIKE '%-$_POST[month]-%' AND
    	`tincidents`.`date_create` LIKE '$_POST[year]-%' AND
    	`tincidents`.`technician` LIKE '$_POST[tech]'
		GROUP BY `tcategory`.`name`
		ORDER BY `nb`
		DESC limit 0,10";
}
$query=$db->query($query1);
while ($row = $query->fetch()) 
{
	$name=substr($row[0],0,35);
	$name=str_replace("'","\'",$name); 
	array_push($values, $row[1]);
	array_push($xnom, $name);
} 
$container='container4';
include('./stats/graph_pie.php');
echo '<div class="card-body bgc-dark-l4 p-0 border-1 brc-default-l2 radius-2 px-1 mx-n2 mx-md-0 h-100 d-flex align-items-center" id="'.$container.'"></div>';
if ($rparameters['debug'])echo $query1;
?>