<?php
################################################################################
# @Name : ./core/export_tickets.php
# @Description : dump csv files of current query
# @Call : /stat.php
# @Parameters : 
# @Author : Flox
# @Create : 27/01/2014
# @Update : 25/01/2024
# @Version : 3.2.50 p1
################################################################################

//locales
$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
if($lang=='fr') {$_GET['lang'] = 'fr_FR';}
else {$_GET['lang'] = 'en_US';}

define('PROJECT_DIR', realpath('../'));
define('LOCALE_DIR', PROJECT_DIR .'/locale');
define('DEFAULT_LOCALE', '($_GET[lang]');
require_once('../vendor/components/php-gettext/gettext.inc');
$encoding = 'UTF-8';
$locale = (isset($_GET['lang']))? $_GET['lang'] : DEFAULT_LOCALE;
T_setlocale(LC_MESSAGES, $locale);
T_bindtextdomain($_GET['lang'], LOCALE_DIR);
T_bind_textdomain_codeset($_GET['lang'], $encoding);
T_textdomain($_GET['lang']);

//initialize variables 
require_once(__DIR__."/../core/init_get.php");
if(!isset($cnt_service)) $cnt_service=''; 

//database connection
require "../connect.php"; 

//secure var
$db_userid=strip_tags($_GET['userid']);
$db_agency=strip_tags($_GET['agency']);
$db_technician=strip_tags($_GET['technician']);
$db_service=strip_tags($_GET['service']);
$db_type=strip_tags($_GET['type']);
$db_criticality=strip_tags($_GET['criticality']);
$db_category=strip_tags($_GET['category']);
$db_state=strip_tags($_GET['state']);
$db_month=strip_tags($_GET['month']);
$db_year=strip_tags($_GET['year']);
$db_company=strip_tags($_GET['company']);
$db_userid=htmlspecialchars($db_userid, ENT_QUOTES, 'UTF-8');
$db_agency=htmlspecialchars($db_agency, ENT_QUOTES, 'UTF-8');
$db_technician=htmlspecialchars($db_technician, ENT_QUOTES, 'UTF-8');
$db_service=htmlspecialchars($db_service, ENT_QUOTES, 'UTF-8');
$db_type=htmlspecialchars($db_type, ENT_QUOTES, 'UTF-8');
$db_criticality=htmlspecialchars($db_criticality, ENT_QUOTES, 'UTF-8');
$db_category=htmlspecialchars($db_category, ENT_QUOTES, 'UTF-8');
$db_state=htmlspecialchars($db_state, ENT_QUOTES, 'UTF-8');
$db_month=htmlspecialchars($db_month, ENT_QUOTES, 'UTF-8');
$db_year=htmlspecialchars($db_year, ENT_QUOTES, 'UTF-8');
$db_company=htmlspecialchars($db_company, ENT_QUOTES, 'UTF-8');

//check var
if(!is_numeric($db_userid) && $db_userid!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_agency) && $db_agency!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_technician) && $db_technician!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_service) && $db_service!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_type) && $db_type!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_criticality) && $db_criticality!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_category) && $db_category!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_state) && $db_state!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_month) && $db_month!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_year) && $db_year!='%') {echo 'ERROR : incorrect value'; exit;}
if(!is_numeric($db_company) && $db_company!='%') {echo 'ERROR : incorrect value'; exit;}

//check db token
$qry=$db->prepare("SELECT `id`,`token` FROM `ttoken` WHERE `action`='stat_access'  AND `token`=:token AND `user_id`=:user_id AND `ip`=:ip");
$qry->execute(array('token' => $_GET['token'],'user_id' => $db_userid,'ip' => $_SERVER['REMOTE_ADDR']));
$token=$qry->fetch();
$qry->closeCursor();
if(empty($token['id'])) {echo "ERROR : Wrong token"; exit;}

//secure connect from authenticated user
if($_GET['token']==$token['token'] && $_GET['token']) 
{
	//get current date
	$daydate=date('Y-m-d');

	// output headers so that the file is downloaded rather than displayed
	header('Content-Encoding: UTF-8');
	header("Content-Type: text/csv; charset=UTF-8");
	header("Content-Disposition: attachment; filename=\"$daydate-GestSup-export-tickets.csv\"");

	//load parameters table
	$qry = $db->prepare("SELECT * FROM `tparameters`");
	$qry->execute();
	$rparameters=$qry->fetch();
	$qry->closeCursor();
	
	//display error parameter
	if($rparameters['debug']) {
		ini_set('display_errors', 'On');
		ini_set('display_startup_errors', 'On');
		ini_set('html_errors', 'On');
		error_reporting(E_ALL);
	} else {
		ini_set('display_errors', 'Off');
		ini_set('display_startup_errors', 'Off');
		ini_set('html_errors', 'Off');
		error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
	}
	
	//load rights table
	$qry = $db->prepare("SELECT * FROM trights WHERE profile=(SELECT profile FROM tusers WHERE id=:id)");
	$qry->execute(array('id' => $db_userid));
	$rright=$qry->fetch();
	$qry->closeCursor();
	
	$where='';
	
	//get services associated with this user
	$qry = $db->prepare("SELECT service_id FROM `tusers_services` WHERE user_id=:user_id");
	$qry->execute(array('user_id' => $db_userid));
	$cnt_service=$qry->rowCount();
	$row=$qry->fetch();
	$qry->closeCursor();
	
	//case limit user service
	if($rparameters['user_limit_service']==1 && $rright['admin']==0 && $_GET['service']=='%' && $cnt_service!=0 && $rright['dashboard_service_only'])
	{
		//get services associated with this user
		$qry = $db->prepare("SELECT service_id FROM `tusers_services` WHERE user_id=:user_id");
		$qry->execute(array('user_id' => $db_userid));
		$cnt_service=$qry->rowCount();
		$row=$qry->fetch();
		$qry->closeCursor();
		
		if($cnt_service==0) {$where_service.='';}
		elseif($cnt_service==1) {
			$where.="u_service='$row[service_id]' AND ";
		} else {
			$cnt2=0;
			$qry = $db->prepare("SELECT service_id FROM `tusers_services` WHERE user_id=:user_id");
			$qry->execute(array('user_id' => $db_userid));
			$where.='(';
			while ($row=$qry->fetch())	
			{
				$cnt2++;
				$where.="u_service='$row[service_id]'";
				if($cnt_service!=$cnt2) $where.=' OR '; 
			}
			$where.=' OR user='.$db_userid.' OR technician='.$db_userid.' ';
			$where.=') AND ';
			$qry->closecursor();
		}
	}

	//case technician group selected on filter
	if(preg_match('/G_/',$_GET['technician']))
	{
		//get technician group id
		$tech_group_id=explode('G_',$_GET['technician']);
		$tech_group_id=$tech_group_id['1'];

		$where_tech_grp=' (';
		//generate where for each member of technician group
		$qry=$db->prepare("SELECT `user` FROM `tgroups_assoc` WHERE `group`=:group");
		$qry->execute(array('group' => $tech_group_id));
		while($row=$qry->fetch()) 
		{
			$where_tech_grp.="tincidents.technician=$row[user] OR ";
		}
		$qry->closeCursor();
		$where_tech_grp=substr($where_tech_grp, 0, -4);
		$where_tech_grp.=') AND ';
		$where.=$where_tech_grp;
		$db_technician='%';
	}
	
	//create a file pointer connected to the output stream
	$output = fopen('php://output', 'w');
	
	//avoid UTF8 encoding problem
	fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
	
	//output the column headings
	$select='';
	fputcsv($output, array(T_('Numéro du ticket'), T_('Type'), T_('Type de réponse'), T_('Technicien'), T_('Demandeur'), T_('Service'), T_('Service du demandeur'), T_('Agence'), T_('Date de première réponse'), T_('Société'),T_('Créateur'), T_('Catégorie'), T_('Sous-catégorie'), T_('Lieu'), T_('Type équipement'), T_('Équipement'),T_('Titre'), T_('Temps passé'), T_('Temps estimé'), T_('Date de création'),T_('Date de résolution estimée'), T_('Date de résolution'), T_('Date de dernière modification'), T_('État'), T_('Priorité'), T_('Criticité'), T_('Facturable')),";");
	$select.='sender_service,u_agency, img2, img1,';
	$where.="u_agency LIKE '$db_agency' AND";
	
	//special case to filter by meta state
	if($db_state=='meta') {
		$qry="
		SELECT tincidents.id,type,type_answer,technician,user,u_service, $select creator,category,subcat,place,img3,asset_id,title,time,time_hope,date_create,date_hope,date_res,date_modif,state,priority,criticality,billable 
		FROM tincidents
		INNER JOIN `tusers` AS `tusers_user` ON (`tincidents`.`user`=`tusers_user`.`id`)  
		WHERE
		`tusers_user`.`company` LIKE '$db_company' AND
		tincidents.technician LIKE '$db_technician' AND
		tincidents.u_service LIKE '$db_service' AND
		tincidents.type LIKE '$db_type' AND
		tincidents.criticality LIKE '$db_criticality' AND
		tincidents.category LIKE '$db_category' AND
		tincidents.date_create LIKE '%-$db_month-%' AND
		tincidents.date_create LIKE '$db_year-%' AND
		tincidents.u_agency LIKE '$db_agency' AND
		tincidents.state IN (SELECT id FROM tstates WHERE meta=1) AND
		$where
		tincidents.disable=0
		";
	} else {
		$qry="
		SELECT tincidents.id,type,type_answer,technician,user,u_service, $select creator,category,subcat,place,img3,asset_id,title,time,time_hope,date_create,date_hope,date_res,date_modif,state,priority,criticality,billable 
		FROM tincidents 
		INNER JOIN `tusers` AS `tusers_user` ON (`tincidents`.`user`=`tusers_user`.`id`) 
		WHERE
		`tusers_user`.`company` LIKE '$db_company' AND
		tincidents.technician LIKE '$db_technician' AND
		tincidents.u_service LIKE '$db_service' AND
		tincidents.type LIKE '$db_type' AND
		tincidents.criticality LIKE '$db_criticality' AND
		tincidents.category LIKE '$db_category' AND
		tincidents.state LIKE '$db_state' AND
		tincidents.date_create LIKE '%-$db_month-%' AND
		tincidents.date_create LIKE '$db_year-%' AND
		tincidents.u_agency LIKE '$db_agency' AND
		$where
		tincidents.disable=0
		";
	}
	if($rparameters['debug']) {echo $qry;}
	$qry = $db->query($qry);
	while ($row = $qry->fetch(PDO::FETCH_ASSOC)) 
	{
		//detect technician group to display group name instead of technician name
		if($row['technician']==0)
		{
			//check if group exist on this ticket
			$qry2=$db->prepare("SELECT t_group FROM tincidents WHERE id=:id");
			$qry2->execute(array('id' => $row['id']));
			$row2=$qry2->fetch();
			$qry2->closeCursor();
			if($row2['t_group']!='0')
			{
				//get group name
				$qry2=$db->prepare("SELECT `name` FROM tgroups WHERE id=:id");
				$qry2->execute(array('id' => $row2['t_group']));
				$row2=$qry2->fetch();
				$qry2->closeCursor();
				if(empty($row2)) {$row2['name']='';}
				$row['technician']="$row2[name]";
			}
		} else {
			$qry2=$db->prepare("SELECT firstname,lastname FROM tusers WHERE id=:id ");
			$qry2->execute(array('id' => $row['technician']));
			$resulttech=$qry2->fetch();
			$qry2->closeCursor();
			if(empty($resulttech)) {$resulttech['firstname']=''; $resulttech['lastname']='';}
			$row['technician']="$resulttech[firstname] $resulttech[lastname]";
		}
		
		$qry2=$db->prepare("SELECT `name` FROM ttypes WHERE id=:id ");
		$qry2->execute(array('id' => $row['type']));
		$resulttype=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($resulttype)) {$resulttype['name']='';}
		$row['type']=$resulttype['name'];
		
		$qry2=$db->prepare("SELECT `name` FROM ttypes_answer WHERE id=:id ");
		$qry2->execute(array('id' => $row['type_answer']));
		$resulttype_answer=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($resulttype_answer['name'])) {$resulttype_answer=array(); $resulttype_answer['name']='';}
		if(isset($resulttype_answer['name'])) {$row['type_answer']=$resulttype_answer['name'];} else {$row['type_answer']='Aucun';}
		
		$qry2=$db->prepare("SELECT `name` FROM tcompany,tusers WHERE tusers.company=tcompany.id AND tusers.id=:id");
		$qry2->execute(array('id' => $row['user']));
		$resultcompany=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($resultcompany)) {$resultcompany['name']='';}
		$row['img1']="$resultcompany[name]";
		
		//detect user group to display group name instead of user name
		if($row['user']=='')
		{
			//check if group exist on this ticket
			$qry2=$db->prepare("SELECT u_group FROM tincidents WHERE id=:id");
			$qry2->execute(array('id' => $row['id']));
			$row2=$qry2->fetch();
			$qry2->closeCursor();
			if($row2['u_group']!='0')
			{
				//get group name
				$qry2=$db->prepare("SELECT `name` FROM tgroups WHERE id=:id");
				$qry2->execute(array('id' => $row2['u_group']));
				$row2=$qry2->fetch();
				$qry2->closeCursor();
				if(empty($row2['name'])) {$row2['name']='';}
				$row['user']="$row2[name]";
			}
		} else {
			$qry2=$db->prepare("SELECT firstname,lastname FROM tusers WHERE id=:id");
			$qry2->execute(array('id' => $row['user']));
			$resultuser=$qry2->fetch();
			$qry2->closeCursor();
			if(empty($resultuser)) {$resultuser['firstname']='';$resultuser['lastname']='';}
			$row['user']="$resultuser[firstname] $resultuser[lastname]";
		}
		
		//get sender service name
		$qry2=$db->prepare("SELECT `name` FROM tservices WHERE id=:id");
		$qry2->execute(array('id' => $row['sender_service']));
		$result_sender_service=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($result_sender_service['name'])) {$result_sender_service=array(); $result_sender_service['name']='';}
		$row['sender_service']="$result_sender_service[name]";

		//get agency name
		$qry2=$db->prepare("SELECT `name` FROM tagencies WHERE id=:id");
		$qry2->execute(array('id' => $row['u_agency']));
		$resultagency=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($resultagency['name'])) {$resultagency['name']='';}
		$row['u_agency']="$resultagency[name]";

		//find date first answer
		$qry2=$db->prepare("SELECT MIN(date) FROM `tthreads` WHERE ticket=:ticket AND type='0'");
		$qry2->execute(array('ticket' => $row['id']));
		$resultfirst=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($resultfirst['name'])) {$resultfirst['name']='';}
		$row['img2']="$resultfirst[0]";
		
		$qry2=$db->prepare("SELECT `name` FROM tservices WHERE id=:id");
		$qry2->execute(array('id' => $row['u_service']));
		$resultservice=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($resultservice['name'])) {$resultservice['name']='';}
		if(isset($resultservice['name'])) {$row['u_service']=$resultservice['name'];} else {$row['u_service']=T_('Aucun');}
		 
		$qry2=$db->prepare("SELECT firstname,lastname FROM tusers WHERE id=:id");
		$qry2->execute(array('id' => $row['creator']));
		$resultcreator=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($resultcreator['firstname'])) {$resultcreator=array(); $resultcreator['firstname']=''; $resultcreator['lastname']='';}
		$row['creator']="$resultcreator[firstname] $resultcreator[lastname]";
		
		$qry2=$db->prepare("SELECT `name` FROM tcategory WHERE id=:id");
		$qry2->execute(array('id' => $row['category']));
		$resultcat=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($resultcat['name'])) {$resultcat=array(); $resultcat['name']='';}
		$row['category']=$resultcat['name'];
		
		$qry2=$db->prepare("SELECT `name` FROM tsubcat WHERE id=:id");
		$qry2->execute(array('id' => $row['subcat']));
		$resultscat=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($resultscat['name'])) {$resultscat=array();  $resultscat['name']='';}
		$row['subcat']=$resultscat['name'];
		
		$qry2=$db->prepare("SELECT `name` FROM tplaces WHERE id=:id");
		$qry2->execute(array('id' => $row['place']));
		$resultplace=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($resultplace['name'])) {$resultplace=array(); $resultplace['name']='';}
		$row['place']=$resultplace['name'];

		$qry2=$db->prepare("SELECT `tassets_type`.`name` FROM `tassets_type`,`tassets` WHERE `tassets`.`type`=`tassets_type`.`id` AND `tassets`.`id`=:asset_id");
		$qry2->execute(array('asset_id' => $row['asset_id']));
		$resultassettype=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($resultassettype['name'])) {$resultassettype=array();$resultassettype['name']='';}
		$row['img3']=$resultassettype['name'];
		
		$qry2=$db->prepare("SELECT `netbios` FROM tassets WHERE `id`=:id");
		$qry2->execute(array('id' => $row['asset_id']));
		$resultasset=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($resultasset['netbios'])) {$resultasset=array(); $resultasset['name']='';}
		$row['asset_id']=$resultasset['netbios'];
		
		$qry2=$db->prepare("SELECT `name` FROM tstates WHERE id=:id");
		$qry2->execute(array('id' => $row['state']));
		$resultstate=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($resultstate['name'])) {$resultstate=array(); $resultstate['name']='';}
		$row['state']=$resultstate['name'];
		
		$qry2=$db->prepare("SELECT `name` FROM tpriority WHERE id=:id");
		$qry2->execute(array('id' => $row['priority']));
		$resultpriority=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($resultpriority['name'])) {$resultpriority=array(); $resultpriority['name']='';}
		$row['priority']=$resultpriority['name'];

		$qry2=$db->prepare("SELECT `name` FROM tcriticality WHERE id=:id");
		$qry2->execute(array('id' => $row['criticality']));
		$resultcriticality=$qry2->fetch();
		$qry2->closeCursor();
		if(empty($resultcriticality['name'])) {$resultcriticality=array(); $resultcriticality['name']='';}
		$row['criticality']=$resultcriticality['name'];

		if($row['billable']) {$row['billable']=T_('Oui');} else {$row['billable']=T_('Non');}

		$row['title']=htmlspecialchars_decode($row['title'], ENT_QUOTES);

		//update time, for time by response #4369 
		if($row['time']==0)
		{
			$qry2=$db->prepare("SELECT SUM(time) FROM `tthreads` WHERE `ticket`=:ticket");
			$qry2->execute(array('ticket' => $row['id']));
			$resulttime=$qry2->fetch();
			$qry2->closeCursor();
			$row['time']=$resulttime[0];
		}
		
		fputcsv($output, $row,';');
	}
	$qry->closeCursor();
} else {
	echo '<br /><br /><span style="font-size: x-large; color: red; text-align:center;"><b>'.T_("Erreur d'accès à la page, essayer de recharger la page statistique, si le problème persiste contactez votre administrateur").'.</b></span>';		
}
$db = null;
?>