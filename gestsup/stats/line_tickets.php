<?php
################################################################################
# @Name : line_ticket.php
# @Description : Display a line graph with open AND close tickets 
# @call : /ticket_stat.php
# @parameters : 
# @Author : Flox
# @Create : 15/02/2014
# @Update : 19/12/2023
# @Version : 3.2.46
################################################################################

//prevent direct access
if(!isset($_SESSION['user_id'])) {header('HTTP/1.0 403 Forbidden'); exit;}

//count create period
$query="SELECT COUNT(`tincidents`.`id`) FROM `tincidents`,`tusers` 
WHERE 
`tincidents`.`user`=`tusers`.`id` AND
`tusers`.`company` LIKE '$_POST[company]' AND
`tincidents`.`technician` LIKE '$_POST[tech]' AND
`tincidents`.`criticality` LIKE '$_POST[criticality]' AND
`tincidents`.`type` LIKE '$_POST[type]' AND
`tincidents`.`category` LIKE '$_POST[category]' AND
`tincidents`.`u_service` LIKE '$_POST[service]' 
$where_service 
$where_agency 
$where_tech_group AND 
$where_state AND 
`tincidents`.`date_create` NOT LIKE '0000-00-00 00:00:00' AND
`tincidents`.`date_create` LIKE '$_POST[year]-$_POST[month]-%' AND
`tincidents`.`disable`='0'";
if($rparameters['debug']) {echo $query;}
$query=$db->query($query);
$row=$query->fetch();
$count=$row[0];
$query->closeCursor(); 

//count close period
$query=$db->query("SELECT COUNT(`tincidents`.`id`) FROM `tincidents`,`tusers`
WHERE 
`tincidents`.`user`=`tusers`.`id` AND
`tusers`.`company` LIKE '$_POST[company]' AND
`tincidents`.`technician` LIKE '$_POST[tech]' AND
`tincidents`.`criticality` LIKE '$_POST[criticality]' AND
`tincidents`.`type` LIKE '$_POST[type]' AND
`tincidents`.`category` LIKE '$_POST[category]' AND
`tincidents`.`u_service` LIKE '$_POST[service]' 
$where_service 
$where_agency 
$where_tech_group AND
$where_state AND
`tincidents`.`date_res` NOT LIKE '0000-00-00 00:00:00' AND
`tincidents`.`date_res` LIKE '$_POST[year]-$_POST[month]-%' AND
`tincidents`.`state`='3' AND
`tincidents`.`disable`='0'");
$row=$query->fetch();
$count2=$row[0];
$query->closeCursor(); 

//count current advance
$query="SELECT STRAIGHT_JOIN COUNT(DISTINCT `tincidents`.`id`)  FROM `tincidents`,`tthreads`,`tusers`
WHERE
`tincidents`.`user`=`tusers`.`id` AND
`tusers`.`company` LIKE '$_POST[company]' AND
`tincidents`.`id`=`tthreads`.`ticket` AND
`tincidents`.`technician` LIKE '$_POST[tech]' AND
`tincidents`.`technician`=`tthreads`.`author` AND
`tincidents`.`u_service` LIKE '$_POST[service]' 
$where_service 
$where_agency 
$where_tech_group AND 
$where_state AND
`tincidents`.`criticality` LIKE '$_POST[criticality]' AND
`tincidents`.`type` LIKE '$_POST[type]' AND
`tincidents`.`category` LIKE '$_POST[category]' AND
`tthreads`.`date` LIKE '$_POST[year]-$_POST[month]-%' AND
`tthreads`.`type`='0' AND
`tincidents`.`state`!=3 AND
`tincidents`.`disable`='0' 
";
$query=$db->query($query);
$row=$query->fetch();
$count3=$row[0];
$query->closeCursor(); 

//count total
$query=$db->query("SELECT COUNT(*) FROM `tincidents`,`tusers`
WHERE 
`tincidents`.`user`=`tusers`.`id` AND
`tusers`.`company` LIKE '$_POST[company]' AND
`tincidents`.`technician` LIKE '$_POST[tech]' AND
`tincidents`.`criticality` LIKE '$_POST[criticality]' AND
`tincidents`.`type` LIKE '$_POST[type]' AND
`tincidents`.`category` LIKE '$_POST[category]' AND
`tincidents`.`u_service` LIKE '$_POST[service]' 
$where_service 
$where_agency 
$where_tech_group AND
$where_state AND
`tincidents`.`disable`='0'");
$row=$query->fetch();
$count4=$row[0];
$query->closeCursor();

//queries for year selection
if(($_POST['month']=='%') && ($_POST['year']!='%'))
{
    $values1 = array();
    $values2 = array();
    $values3 = array();
    $xnom1 = array();
    $xnom2 = array();
    $xnom3 = array();
	$libchart=T_('Évolution des tickets ouverts et fermés sur').' '.$_POST['year'];
	
	$query2=$db->query("SELECT month(`date_create`) AS x,COUNT(*) AS y FROM `tincidents`,`tusers` 
		WHERE 
		`tincidents`.`user`=`tusers`.`id` AND
		`tusers`.`company` LIKE '$_POST[company]' AND
		`tincidents`.`technician` LIKE '$_POST[tech]' AND
		`tincidents`.`u_service` LIKE '$_POST[service]' 
		$where_service 
		$where_agency 
		$where_tech_group AND
		$where_state AND
		`tincidents`.`criticality` LIKE '$_POST[criticality]' AND
		`tincidents`.`type` LIKE '$_POST[type]' AND
		`tincidents`.`category` LIKE '$_POST[category]' AND
		`tincidents`.`date_create` NOT LIKE '0000-00-00 00:00:00' AND
		`tincidents`.`date_create` LIKE '$_POST[year]-$_POST[month]-%' AND
		`tincidents`.`disable`='0' 
		GROUP BY x ORDER BY x");
	$query3=$db->query("SELECT month(`date_res`) AS x,COUNT(*) AS y FROM `tincidents`,`tusers` 
		WHERE 
		`tincidents`.`user`=`tusers`.`id` AND
		`tusers`.`company` LIKE '$_POST[company]' AND
		`tincidents`.`technician` LIKE '$_POST[tech]' AND
		`tincidents`.`u_service` LIKE '$_POST[service]' 
		$where_service 
		$where_agency 
		$where_tech_group AND 
		$where_state AND 
		`tincidents`.`criticality` LIKE '$_POST[criticality]' AND 
		`tincidents`.`type` LIKE '$_POST[type]' AND 
		`tincidents`.`category` LIKE '$_POST[category]' AND 
		`tincidents`.`date_res` NOT LIKE '0000-00-00 00:00:00' AND 
		`tincidents`.`date_res` LIKE '$_POST[year]-$_POST[month]-%' AND 
		`tincidents`.`state`='3' AND 
		`tincidents`.`disable`='0' 
		GROUP BY x ORDER BY x");
	$query1=$db->query("SELECT month(`tthreads`.`date`) AS x,COUNT(DISTINCT `tincidents`.`id`) AS y FROM `tincidents`,`tthreads`,`tusers` 
		WHERE
		`tincidents`.`user`=`tusers`.`id` AND
		`tusers`.`company` LIKE '$_POST[company]' AND
		`tincidents`.`id`=`tthreads`.`ticket` AND
		`tincidents`.`technician` LIKE '$_POST[tech]' AND
		`tincidents`.`technician`=`tthreads`.`author` AND
		`tincidents`.`u_service` LIKE '$_POST[service]' 
		$where_service $where_agency $where_tech_group AND 
		$where_state AND
		`tincidents`.`criticality` LIKE '$_POST[criticality]' AND
		`tincidents`.`type` LIKE '$_POST[type]' AND
		`tincidents`.`category` LIKE '$_POST[category]' AND
		`tthreads`.`date` LIKE '$_POST[year]-$_POST[month]-%' AND
		`tthreads`.`type`='0' AND
		`tincidents`.`state`!=3 AND
		`tincidents`.`disable`='0' 
		GROUP BY x ORDER BY x
	");
	//push data in table
	while($data = $query1->fetch())
	{
		array_push($values1 ,$data['y']);
		array_push($xnom1 ,$data['x']);
	}
	$query1->closeCursor(); 
	while($data = $query2->fetch())
	{
		array_push($values2 ,$data['y']);
		array_push($xnom2 ,$data['x']);
	}
	$query2->closeCursor(); 
	while($data = $query3->fetch())
	{
		array_push($values3 ,$data['y']);
		array_push($xnom3 ,$data['x']);
	}
	$query3->closeCursor(); 
}

//queries for month selection
elseif($_POST['month']!='%')
{
    $values1 = array();
    $values2 = array();
    $values3 = array();
    $xnom1 = array();
    $xnom2 = array();
    $xnom3 = array();
	$monthm=$_POST['month'];
	if($_POST['year']=='%') {$postyear=T_('de toutes les années');} else {$postyear=$_POST['year'];}
	$libchart=T_('Évolution des tickets ouverts et fermés pour le mois de').' '.$month[$monthm].' '.$postyear;

	$query_1="SELECT day(`tincidents`.`date_create`) AS x,COUNT(`tincidents`.`id`) AS y FROM `tincidents`,`tusers` 
	WHERE 
	`tincidents`.`user`=`tusers`.`id` AND
	`tusers`.`company` LIKE '$_POST[company]' AND
	`tincidents`.`technician` LIKE '$_POST[tech]' AND 
	`tincidents`.`u_service` LIKE '$_POST[service]' 
	$where_service 
	$where_agency 
	$where_tech_group AND 
	$where_state AND 
	`tincidents`.`criticality` LIKE '$_POST[criticality]' AND 
	`tincidents`.`type` LIKE '$_POST[type]' AND 
	`tincidents`.`category` LIKE '$_POST[category]' AND 
	`tincidents`.`date_create` NOT LIKE '0000-00-00 00:00:00' AND 
	`tincidents`.`date_create` LIKE '$_POST[year]-$_POST[month]-%' AND 
	`tincidents`.`disable`='0' 
	GROUP BY x ORDER BY x";
	if($rparameters['debug']) {echo '<br><br>'.$query_1;}
	$query2=$db->query($query_1);
	
	$query3=$db->query("SELECT day(`tincidents`.`date_res`) AS x,COUNT(`tincidents`.`id`) AS y FROM `tincidents`,`tusers`  
	WHERE 
	`tincidents`.`user`=`tusers`.`id` AND
	`tusers`.`company` LIKE '$_POST[company]' AND
	`tincidents`.`technician` LIKE '$_POST[tech]' AND 
	`tincidents`.`u_service` LIKE '$_POST[service]' 
	$where_service 
	$where_agency 
	$where_tech_group AND 
	$where_state AND 
	`tincidents`.`criticality` LIKE '$_POST[criticality]' AND 
	`tincidents`.`type` LIKE '$_POST[type]' AND 
	`tincidents`.`category` LIKE '$_POST[category]' AND 
	`tincidents`.`date_res` NOT LIKE '0000-00-00 00:00:00' AND 
	`tincidents`.`date_res`  LIKE '$_POST[year]-$_POST[month]-%' AND 
	`tincidents`.`state`='3' AND 
	`tincidents`.`disable`='0' 
	GROUP BY x ORDER BY x");
	$query1=$db->query("SELECT day(`tthreads`.`date`) AS x,COUNT(DISTINCT `tincidents`.id) AS y FROM `tincidents`,`tthreads`,`tusers`  
		WHERE
		`tincidents`.`user`=`tusers`.`id` AND
		`tusers`.`company` LIKE '$_POST[company]' AND
		`tincidents`.`id`=`tthreads`.`ticket` AND
		`tincidents`.`technician` LIKE '$_POST[tech]' AND
		`tincidents`.`technician`=`tthreads`.`author` AND
		`tincidents`.`u_service` LIKE '$_POST[service]' 
		$where_service 
		$where_agency 
		$where_tech_group AND 
		$where_state AND
		`tincidents`.`criticality` LIKE '$_POST[criticality]' AND
		`tincidents`.`type` LIKE '$_POST[type]' AND
		`tincidents`.`category` LIKE '$_POST[category]' AND
		`tthreads`.`date` LIKE '$_POST[year]-$_POST[month]-%' AND
		`tthreads`.`type`='0' AND
		`tincidents`.`state`!=3 AND
		`tincidents`.`disable`='0' GROUP BY x ORDER BY x 
	");
	//push data in table
	while($data = $query1->fetch())
	{
    	array_push($values1 ,$data['y']);
    	array_push($xnom1 ,$day[$data['x']]);
	}
	$query1->closeCursor(); 
	while($data = $query2->fetch())
	{
    	array_push($values2 ,$data['y']);
    	array_push($xnom2 ,$day[$data['x']]);
	}
	$query2->closeCursor(); 
	while($data = $query3->fetch())
	{
    	array_push($values3 ,$data['y']);
    	array_push($xnom3 ,$day[$data['x']]);
	}
	$query3->closeCursor(); 
}

//queries for all years selection
elseif($_POST['year']=='%')
{
    $values1 = array();
    $values2 = array();
    $values3 = array();
    $xnom1 = array();
    $xnom2 = array();
    $xnom3 = array();
	$libchart=T_('Évolution des tickets ouverts et fermés sur toutes les années');
	//open tickets
	$query2="SELECT year(`tincidents`.`date_create`) AS x,COUNT(`tincidents`.`id`) AS y FROM `tincidents`,`tusers`   
		WHERE 
		`tincidents`.`user`=`tusers`.`id` AND
		`tusers`.`company` LIKE '$_POST[company]' AND
		`tincidents`.`technician` LIKE '$_POST[tech]' AND
		`tincidents`.`u_service` LIKE '$_POST[service]' 
		$where_service 
		$where_agency 
		$where_tech_group AND
		$where_state AND
		`tincidents`.`criticality` LIKE '$_POST[criticality]' AND
		`tincidents`.`type` LIKE '$_POST[type]' AND
		`tincidents`.`category` LIKE '$_POST[category]' AND
		`tincidents`.`date_create` NOT LIKE '0000-00-00 00:00:00' AND
		`tincidents`.`date_create` LIKE '$_POST[year]-$_POST[month]-%' AND
		`tincidents`.`disable`='0' 
		GROUP BY x 
		ORDER BY x
	";
	$query2=$db->query($query2);

	//close tickets
	$query3=$db->query("SELECT year(`tincidents`.`date_res`) AS x,COUNT(*) AS y FROM `tincidents`,`tusers`  
	WHERE 
	`tincidents`.`user`=`tusers`.`id` AND
	`tusers`.`company` LIKE '$_POST[company]' AND
	`tincidents`.`technician` LIKE '$_POST[tech]' AND 
	`tincidents`.`u_service` LIKE '$_POST[service]' 
	$where_service 
	$where_agency 
	$where_tech_group AND 
	$where_state AND 
	`tincidents`.`criticality` LIKE '$_POST[criticality]' AND 
	`tincidents`.`type` LIKE '$_POST[type]' AND 
	`tincidents`.`category` LIKE '$_POST[category]' AND 
	`tincidents`.`date_res` NOT LIKE '0000-00-00 00:00:00' AND 
	`tincidents`.`date_res` > '1900-00-00 00:00:00' AND 
	`tincidents`.`date_res`  LIKE '$_POST[year]-$_POST[month]-%' AND 
	`tincidents`.`state`='3' AND 
	`tincidents`.`disable`='0' 
	GROUP BY x ORDER BY x");

	$query1=$db->query("SELECT year(`tthreads`.`date`) AS x,COUNT(DISTINCT `tincidents`.`id`) AS y FROM `tincidents`,`tthreads`,`tusers` 
		WHERE
		`tincidents`.`user`=`tusers`.`id` AND
		`tusers`.`company` LIKE '$_POST[company]' AND
		`tincidents`.`id`=`tthreads`.`ticket` AND
		`tincidents`.`technician` LIKE '$_POST[tech]' AND
		`tincidents`.`technician`=`tthreads`.`author` AND
		`tincidents`.`u_service` LIKE '$_POST[service]' 
		$where_service 
		$where_agency 
		$where_tech_group AND 
		$where_state AND
		`tincidents`.`criticality` LIKE '$_POST[criticality]' AND
		`tincidents`.`type` LIKE '$_POST[type]' AND
		`tincidents`.`category` LIKE '$_POST[category]' AND
		`tthreads`.`date` LIKE '$_POST[year]-$_POST[month]-%' AND
		`tthreads`.`date` > '1900-00-00 00:00:00' AND
		`tthreads`.`type`='0' AND
		`tincidents`.`state`!=3 AND
		`tincidents`.`disable`='0' 
		GROUP BY x ORDER BY x
	");
	// push data in table
	while($data = $query1->fetch())
	{
		array_push($values1 ,$data['y']); array_push($xnom1 ,$data['x']);	
	}	
	$query1->closeCursor(); 
	while($data = $query2->fetch())
	{
		array_push($values2 ,$data['y']); array_push($xnom2 ,$data['x']);
	}
	$query2->closeCursor(); 
	while($data = $query3->fetch())
	{
		array_push($values3 ,$data['y']); array_push($xnom3 ,$data['x']);
	}
	$query3->closeCursor(); 
}

if($count!=0) 
{
	$liby=T_('Nombre de tickets');
	$container="container1";		
	include('./stats/graph_line.php');
	echo '<div class="card-body bgc-dark-l4 p-0 border-1 brc-default-l2 radius-2 px-1 mx-n2 mx-md-0 h-100 d-flex align-items-center" id="'.$container.'"></div>';
} else { 
	echo DisplayMessage('error',T_('Aucun ticket ouvert et fermé dans la plage indiqué'));
}

//display query on debug mode
if($rparameters['debug'])
{
    print_r($values1);echo "<br />";
    for($i=0;$i<sizeof($values1);$i++) 
    { 
		$last=sizeof($values1)-1;
		if ($i!=$last) echo '['.$xnom1[$i].','.$values1[$i].'],'; else echo '['.$xnom1[$i].','.$values1[$i].']';
    } 
    echo "<br />";
    print_r($values2);echo "<br />";
    for($i=0;$i<sizeof($values2);$i++) 
    { 
		$last=sizeof($values2)-1;
		if ($i!=$last) echo '['.$xnom2[$i].','.$values2[$i].'],'; else echo '['.$xnom2[$i].','.$values2[$i].']';
    } 
}
echo '<p style="font-size:10px;text-align:right">* '.T_("Tickets avancés: Tickets sur lesquels un élément de résolution textuel à été ajouté par le technicien et qui ne sont pas dans l'état résolu").'</p>';
?>	