<?php
################################################################################
# @Name : searchengine_ticket.php
# @Description : search engine in database tickets
# @call : /dashboard.php
# @parameters : keywords
# @Author : Flox
# @Create : 12/01/2011
# @Update : 02/05/2024
# @Version : 3.2.50
################################################################################

//initialize session variables
if(!isset($_SESSION['user_id'])) $_SESSION['user_id'] = '';

//case when keywords contain '
$keywords = str_replace("'","\'",$keywords);

//keywords table space separation
$keyword=explode(" ",$keywords);

//count $keywords
$nbkeyword= sizeof($keyword);

//case meta state detect
if($_GET['state']=='meta'){
	$state='AND	(';
	$qry=$db->prepare("SELECT `id` FROM `tstates` WHERE `meta`='1'");
	$qry->execute();
	while($row=$qry->fetch()) 
	{
		$state.='tincidents.state LIKE '.$row['id'].' OR ';
	}
	$qry->closeCursor();
	$state.=' 1=0)';
} else {$state='';}

//case user company view
if($_GET['companyview']){$where_company="AND tusers.company='$ruser[company]'";} else {$where_company='';}

$select= "
		DISTINCT 
		tincidents.id,
		tincidents.type,
		tincidents.technician,
		tincidents.t_group,
		tincidents.title,
		tincidents.user,
		tincidents.u_group,
		tincidents.u_service,
		tincidents.u_agency,
		tincidents.sender_service,
		tincidents.date_create,
		tincidents.date_hope,
		tincidents.date_res,
		tincidents.date_modif,
		tincidents.time,
		tincidents.state,
		tincidents.priority,
		tincidents.criticality,
		tincidents.category,
		tincidents.subcat,
		tincidents.techread,
		tincidents.place,
		tincidents.asset_id
		";
$join='';

//special case limit service, with user agency and service
if($rparameters['user_limit_service'] && $rparameters['user_agency'] && !$rright['admin'] && $cnt_service!=0 && $cnt_agency!=0)
{
	//modify QRY 
	$where_service=preg_replace('/OR/', 'AND', $where_service, 1);
	$where_agency=str_replace('AND (', '', $where_agency);
	$where_service=str_replace('AND (',"AND ($where_agency OR ",$where_service);
}

if($nbkeyword==2)
{
	$from = "tincidents
	LEFT JOIN tusers ON tincidents.user=tusers.id
	LEFT JOIN tstates ON tincidents.state=tstates.id
	LEFT JOIN tthreads ON tincidents.id=tthreads.ticket";
	$where="
	(
		tincidents.title LIKE '%$keyword[0]%' OR 
		tincidents.description LIKE '%$keyword[0]%' OR 
		tthreads.text LIKE '%$keyword[0]%' OR
		tincidents.user IN (SELECT id FROM tusers WHERE (firstname LIKE '%$keyword[0]%' OR lastname LIKE '%$keyword[0]%') AND disable=0) OR
		tincidents.user IN (SELECT id FROM tusers WHERE (phone LIKE '%$keyword[0]%' OR mobile LIKE '%$keyword[0]%') AND disable=0)
	) AND (
		tincidents.title LIKE '%$keyword[1]%' OR 
		tincidents.description LIKE '%$keyword[1]%' OR 
		tthreads.text LIKE '%$keyword[1]%' OR
		tincidents.user IN (SELECT id FROM tusers WHERE (firstname LIKE '%$keyword[1]%' OR lastname LIKE '%$keyword[1]%') AND disable=0) OR
		tincidents.user IN (SELECT id FROM tusers WHERE (phone LIKE '%$keyword[0]%' OR mobile LIKE '%$keyword[0]%') AND disable=0)
	) AND (
		tincidents.user LIKE '$_POST[user]'
		AND	tincidents.disable='0'
		AND	tincidents.u_group LIKE '$_GET[u_group]'
		AND	tincidents.technician LIKE '$_POST[technician]'
		AND	tincidents.t_group LIKE '$_GET[t_group]'
		AND	tincidents.techread LIKE '$_GET[techread]'
		AND	tincidents.category LIKE '$_POST[category]'
		AND	tincidents.subcat LIKE '$_POST[subcat]'
		AND	tincidents.id LIKE '$_POST[ticket]'
		AND	tincidents.user LIKE '$_POST[userid]'
		AND tincidents.date_hope LIKE '$_POST[date_hope]%'
		AND	tincidents.priority LIKE '$_POST[priority]'
		AND	tincidents.criticality LIKE '$_POST[criticality]'
		AND	tincidents.type LIKE '$_POST[type]'
		AND	tincidents.title LIKE '$_POST[title]'
	)
	$where_service
	$where_company
	$state
	AND tincidents.disable='0'
"; 
}
else if($nbkeyword==3)
{
	$from = "tincidents
	LEFT JOIN tusers ON tincidents.user=tusers.id
	LEFT JOIN tstates ON tincidents.state=tstates.id
	LEFT JOIN tthreads ON tincidents.id=tthreads.ticket";
	$where="
	(
		tincidents.title LIKE '%$keyword[0]%' OR 
		tincidents.description LIKE '%$keyword[0]%' OR 
		tthreads.text LIKE '%$keyword[0]%' OR
		tincidents.user IN (SELECT id FROM tusers WHERE (firstname LIKE '%$keyword[0]%' OR lastname LIKE '%$keyword[0]%') AND disable=0) OR
		tincidents.user IN (SELECT id FROM tusers WHERE (phone LIKE '%$keyword[0]%' OR mobile LIKE '%$keyword[0]%') AND disable=0)
	) AND (
		tincidents.title LIKE '%$keyword[1]%' OR 
		tincidents.description LIKE '%$keyword[1]%' OR 
		tthreads.text LIKE '%$keyword[1]%' OR
		tincidents.user IN (SELECT id FROM tusers WHERE (firstname LIKE '%$keyword[1]%' OR lastname LIKE '%$keyword[1]%') AND disable=0) OR
		tincidents.user IN (SELECT id FROM tusers WHERE (phone LIKE '%$keyword[0]%' OR mobile LIKE '%$keyword[0]%') AND disable=0)
	) AND (
		tincidents.title LIKE '%$keyword[2]%' OR 
		tincidents.description LIKE '%$keyword[2]%' OR 
		tthreads.text LIKE '%$keyword[2]%' OR
		tincidents.user IN (SELECT id FROM tusers WHERE (firstname LIKE '%$keyword[2]%' OR lastname LIKE '%$keyword[2]%') AND disable=0) OR
		tincidents.user IN (SELECT id FROM tusers WHERE (phone LIKE '%$keyword[0]%' OR mobile LIKE '%$keyword[0]%') AND disable=0)
	) AND (
		tincidents.user LIKE '$_POST[user]'
		AND	tincidents.disable='0'
		AND	tincidents.u_group LIKE '$_GET[u_group]'
		AND	tincidents.technician LIKE '$_POST[technician]'
		AND	tincidents.t_group LIKE '$_GET[t_group]'
		AND	tincidents.techread LIKE '$_GET[techread]'
		AND	tincidents.category LIKE '$_POST[category]'
		AND	tincidents.subcat LIKE '$_POST[subcat]'
		AND	tincidents.id LIKE '$_POST[ticket]'
		AND	tincidents.user LIKE '$_POST[userid]'
		AND tincidents.date_hope LIKE '$_POST[date_hope]%'
		AND	tincidents.priority LIKE '$_POST[priority]'
		AND	tincidents.criticality LIKE '$_POST[criticality]'
		AND	tincidents.type LIKE '$_POST[type]'
		AND	tincidents.title LIKE '$_POST[title]'
	)
	$where_service
	$where_company
	$state
	AND tincidents.disable='0'
"; 
} 
else if($nbkeyword==4)
{
	$from = "tincidents
	LEFT JOIN tusers ON tincidents.user=tusers.id
	LEFT JOIN tstates ON tincidents.state=tstates.id
	LEFT JOIN tthreads ON tincidents.id=tthreads.ticket";
	$where="
	(
		tincidents.title LIKE '%$keyword[0]%' OR 
		tincidents.description LIKE '%$keyword[0]%' OR 
		tthreads.text LIKE '%$keyword[0]%' OR
		tincidents.user IN (SELECT id FROM tusers WHERE (firstname LIKE '%$keyword[0]%' OR lastname LIKE '%$keyword[0]%') AND disable=0) OR
		tincidents.user IN (SELECT id FROM tusers WHERE (phone LIKE '%$keyword[0]%' OR mobile LIKE '%$keyword[0]%') AND disable=0)
	) AND (
		tincidents.title LIKE '%$keyword[1]%' OR 
		tincidents.description LIKE '%$keyword[1]%' OR 
		tthreads.text LIKE '%$keyword[1]%' OR
		tincidents.user IN (SELECT id FROM tusers WHERE (firstname LIKE '%$keyword[1]%' OR lastname LIKE '%$keyword[1]%') AND disable=0) OR
		tincidents.user IN (SELECT id FROM tusers WHERE (phone LIKE '%$keyword[0]%' OR mobile LIKE '%$keyword[0]%') AND disable=0)
	) AND (
		tincidents.title LIKE '%$keyword[2]%' OR 
		tincidents.description LIKE '%$keyword[2]%' OR 
		tthreads.text LIKE '%$keyword[2]%' OR
		tincidents.user IN (SELECT id FROM tusers WHERE (firstname LIKE '%$keyword[2]%' OR lastname LIKE '%$keyword[2]%') AND disable=0) OR
		tincidents.user IN (SELECT id FROM tusers WHERE (phone LIKE '%$keyword[0]%' OR mobile LIKE '%$keyword[0]%') AND disable=0)
	) AND (
		tincidents.title LIKE '%$keyword[3]%' OR 
		tincidents.description LIKE '%$keyword[3]%' OR 
		tthreads.text LIKE '%$keyword[3]%' OR
		tincidents.user IN (SELECT id FROM tusers WHERE (firstname LIKE '%$keyword[3]%' OR lastname LIKE '%$keyword[3]%') AND disable=0) OR
		tincidents.user IN (SELECT id FROM tusers WHERE (phone LIKE '%$keyword[0]%' OR mobile LIKE '%$keyword[0]%') AND disable=0)
	) AND (
		tincidents.user LIKE '$_POST[user]'
		AND	tincidents.disable='0'
		AND	tincidents.u_group LIKE '$_GET[u_group]'
		AND	tincidents.technician LIKE '$_POST[technician]'
		AND	tincidents.t_group LIKE '$_GET[t_group]'
		AND	tincidents.techread LIKE '$_GET[techread]'
		AND	tincidents.category LIKE '$_POST[category]'
		AND	tincidents.subcat LIKE '$_POST[subcat]'
		AND	tincidents.id LIKE '$_POST[ticket]'
		AND	tincidents.user LIKE '$_POST[userid]'
		AND tincidents.date_hope LIKE '$_POST[date_hope]%'
		AND	tincidents.priority LIKE '$_POST[priority]'
		AND	tincidents.criticality LIKE '$_POST[criticality]'
		AND	tincidents.type LIKE '$_POST[type]'
		AND	tincidents.title LIKE '$_POST[title]'
	)
	$where_service
	$where_company
	$state
	AND tincidents.disable='0'
"; 
} else if($nbkeyword==5)
{
	$from = "tincidents
	LEFT JOIN tusers ON tincidents.user=tusers.id
	LEFT JOIN tstates ON tincidents.state=tstates.id
	LEFT JOIN tthreads ON tincidents.id=tthreads.ticket";
	$where="
	(
		tincidents.title LIKE '%$keyword[0]%' OR 
		tincidents.description LIKE '%$keyword[0]%' OR 
		tthreads.text LIKE '%$keyword[0]%' OR
		tincidents.user IN (SELECT id FROM tusers WHERE (firstname LIKE '%$keyword[0]%' OR lastname LIKE '%$keyword[0]%') AND disable=0)  OR
		tincidents.user IN (SELECT id FROM tusers WHERE (phone LIKE '%$keyword[0]%' OR mobile LIKE '%$keyword[0]%') AND disable=0)
	) AND (
		tincidents.title LIKE '%$keyword[1]%' OR 
		tincidents.description LIKE '%$keyword[1]%' OR 
		tthreads.text LIKE '%$keyword[1]%' OR
		tincidents.user IN (SELECT id FROM tusers WHERE (firstname LIKE '%$keyword[1]%' OR lastname LIKE '%$keyword[1]%') AND disable=0) OR
		tincidents.user IN (SELECT id FROM tusers WHERE (phone LIKE '%$keyword[0]%' OR mobile LIKE '%$keyword[0]%') AND disable=0)
	) AND (
		tincidents.title LIKE '%$keyword[2]%' OR 
		tincidents.description LIKE '%$keyword[2]%' OR 
		tthreads.text LIKE '%$keyword[2]%' OR
		tincidents.user IN (SELECT id FROM tusers WHERE (firstname LIKE '%$keyword[2]%' OR lastname LIKE '%$keyword[2]%') AND disable=0) OR
		tincidents.user IN (SELECT id FROM tusers WHERE (phone LIKE '%$keyword[0]%' OR mobile LIKE '%$keyword[0]%') AND disable=0)
	) AND (
		tincidents.title LIKE '%$keyword[3]%' OR 
		tincidents.description LIKE '%$keyword[3]%' OR 
		tthreads.text LIKE '%$keyword[3]%' OR
		tincidents.user IN (SELECT id FROM tusers WHERE (firstname LIKE '%$keyword[3]%' OR lastname LIKE '%$keyword[3]%') AND disable=0) OR
		tincidents.user IN (SELECT id FROM tusers WHERE (phone LIKE '%$keyword[0]%' OR mobile LIKE '%$keyword[0]%') AND disable=0)
	) AND (
		tincidents.title LIKE '%$keyword[4]%' OR 
		tincidents.description LIKE '%$keyword[4]%' OR 
		tthreads.text LIKE '%$keyword[4]%' OR
		tincidents.user IN (SELECT id FROM tusers WHERE (firstname LIKE '%$keyword[4]%' OR lastname LIKE '%$keyword[4]%') AND disable=0) OR
		tincidents.user IN (SELECT id FROM tusers WHERE (phone LIKE '%$keyword[0]%' OR mobile LIKE '%$keyword[0]%') AND disable=0)
	) AND (
		tincidents.user LIKE '$_POST[user]'
		AND	tincidents.disable='0'
		AND	tincidents.u_group LIKE '$_GET[u_group]'
		AND	tincidents.technician LIKE '$_POST[technician]'
		AND	tincidents.t_group LIKE '$_GET[t_group]'
		AND	tincidents.techread LIKE '$_GET[techread]'
		AND	tincidents.category LIKE '$_POST[category]'
		AND	tincidents.subcat LIKE '$_POST[subcat]'
		AND	tincidents.id LIKE '$_POST[ticket]'
		AND	tincidents.user LIKE '$_POST[userid]'
		AND tincidents.date_hope LIKE '$_POST[date_hope]%'
		AND	tincidents.priority LIKE '$_POST[priority]'
		AND	tincidents.criticality LIKE '$_POST[criticality]'
		AND	tincidents.type LIKE '$_POST[type]'
		AND	tincidents.title LIKE '$_POST[title]'
	)
	$where_service
	$where_company
	$state
	AND tincidents.disable='0'
"; 
}
else
{
	$from = "tincidents
	LEFT JOIN tusers ON tincidents.user=tusers.id
	LEFT JOIN tstates ON tincidents.state=tstates.id
	LEFT JOIN tthreads ON tincidents.id=tthreads.ticket
	LEFT JOIN tsubcat ON tincidents.subcat=tsubcat.id
	LEFT JOIN tcategory ON tincidents.category=tcategory.id
	LEFT JOIN tassets ON tincidents.asset_id=tassets.id";
	$where="
	(
		tincidents.title LIKE '%$keyword[0]%' OR 
		tincidents.description LIKE '%$keyword[0]%' OR 
		tthreads.text LIKE '%$keyword[0]%' OR
		tsubcat.name LIKE '$keyword[0]' OR
		tassets.netbios LIKE '$keyword[0]' OR
		tcategory.name LIKE '$keyword[0]' OR
		tincidents.id = '$keyword[0]' OR
		tincidents.user IN (SELECT id FROM tusers WHERE (firstname LIKE '%$keyword[0]%' OR lastname LIKE '%$keyword[0]%') AND disable=0) OR 
		tincidents.user IN (SELECT id FROM tusers WHERE (phone LIKE '%$keyword[0]%' OR mobile LIKE '%$keyword[0]%') AND disable=0)
	) AND (
		tincidents.user LIKE '$_POST[user]'
		AND	tincidents.disable='0'
		AND	tincidents.u_group LIKE '$_GET[u_group]'
		AND	tincidents.technician LIKE '$_POST[technician]'
		AND	tincidents.t_group LIKE '$_GET[t_group]'
		AND	tincidents.techread LIKE '$_GET[techread]'
		AND	tincidents.category LIKE '$_POST[category]'
		AND	tincidents.subcat LIKE '$_POST[subcat]'
		AND	tincidents.id LIKE '$_POST[ticket]'
		AND	tincidents.user LIKE '$_POST[userid]'
		AND tincidents.date_hope LIKE '$_POST[date_hope]%'
		AND	tincidents.priority LIKE '$_POST[priority]'
		AND	tincidents.criticality LIKE '$_POST[criticality]'
		AND	tincidents.type LIKE '$_POST[type]'
		AND	tincidents.title LIKE '$_POST[title]'
	)
	$where_service
	$state
	$where_company
	AND tincidents.disable='0'
	"; 
}	
?>