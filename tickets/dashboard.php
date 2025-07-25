<?php
################################################################################
# @Name : dashboard.php 
# @Description : Display tickets list
# @Call : /index.php
# @Parameters : 
# @Author : Flox
# @Create : 17/07/2009
# @Update : 12/01/2024
# @Version : 3.2.47
################################################################################

//secure direct access
if(!isset($_GET['page'])) {echo 'ERROR : invalid access'; exit;}

//initialize variables 
if(!isset($asc)) $asc = ''; 
if(!isset($late)) $late= '';   
if(!isset($from)) $from=''; 
if(!isset($filter)) $filter=''; 
if(!isset($col)) $col=''; 
if(!isset($view)) $view=''; 
if(!isset($nkeyword)) $nkeyword=''; 
if(!isset($rowlastname)) $rowlastname=''; 
if(!isset($resultcriticality['color'])) $resultcriticality['color']= ''; 
if(!isset($displayusername)) $displayusername= ''; 
if(!isset($displaytechname)) $displaytechname= ''; 
if(!isset($u_group)) $u_group= ''; 
if(!isset($t_group)) $t_group= ''; 
if(!isset($techread)) $techread= '';  
if(!isset($userread)) $userread= '';  
if(!isset($start_page)) $start_page= '';  
if(!isset($cursor)) $cursor= '';  
if(!isset($selectcursor)) $selectcursor= '';  
if(!isset($date_start)) $date_start= '';  
if(!isset($date_end)) $date_end= '';  
if(!isset($db_priority)) $db_priority= '';  

//get value is for filter case
if($_POST['ticket']=='') $_POST['ticket']=$_GET['ticket'];
if($_POST['technician']=='') $_POST['technician']=$_GET['technician'];
if($_POST['title']=='') $_POST['title']=$_GET['title'];
if($_POST['ticket']=='') $_POST['ticket']= '';
if($_POST['userid']=='') $_POST['userid']= '';	
if($_POST['company']=='') $_POST['company']= $_GET['company'];
if($_POST['user']=='') $_POST['user']= $_GET['user'];
if($_POST['category']=='') $_POST['category']=$_GET['category'];
if($_POST['subcat']=='') $_POST['subcat']=$_GET['subcat'];
if($_POST['asset']=='') $_POST['asset']=$_GET['asset'];
if($_POST['place']=='') $_POST['place']=$_GET['place'];
if($_POST['service']=='') $_POST['service']=$_GET['service'];
if($_POST['sender_service']=='') $_POST['sender_service']=$_GET['sender_service'];
if($_POST['agency']=='') $_POST['agency']=$_GET['agency'];
if($_POST['date_create']=='') $_POST['date_create']=$_GET['date_create']; 
if($_POST['time']=='') $_POST['time']=$_GET['time']; 
if($_POST['date_hope']=='') $_POST['date_hope']=$_GET['date_hope']; 
if($_POST['date_res']=='') $_POST['date_res']=$_GET['date_res']; 
if($_POST['date_modif']=='') $_POST['date_modif']=$_GET['date_modif']; 
if($_POST['date_start']=='') $_POST['date_start']=$_GET['date_start']; 
if($_POST['date_end']=='') $_POST['date_end']=$_GET['date_end']; 
if($_POST['state']=='') $_POST['state']=$_GET['state'];
if($_POST['priority']=='') $_POST['priority']=$_GET['priority'];
if($_POST['criticality']=='') $_POST['criticality']=$_GET['criticality']; 
if($_POST['type']=='') $_POST['type']=$_GET['type']; 
if($_POST['u_group']=='') $_POST['u_group']=$_GET['u_group']; 
if($_POST['t_group']=='') $_POST['t_group']=$_GET['t_group']; 

//default values
if($techread=='') $techread='%';
if($userread=='') $userread='%';
if($state=='')$state='%';
if($_GET['way']=='') $_GET['way']='DESC';	
if($_GET['category']=='') $_GET['category']= '%'; 
if($_GET['t_group']=='') $_GET['t_group']= '%'; 
if($_GET['u_group']=='') $_GET['u_group']= '%'; 
if($_GET['subcat']=='') $_GET['subcat']= '%';
if($_GET['asset']=='') $_GET['asset']= '%';
if($_GET['place']=='') $_GET['place']= '%';
if($_GET['cursor']=='') $_GET['cursor']='0'; 
if($_GET['techread']=='') $_GET['techread']='%';
if($_GET['userread']=='') $_GET['userread']='%';
if($_GET['type']=='') {$_GET['type']='%'; }

if($_POST['criticality']=='') $_POST['criticality']= '%'; 
if($_POST['priority']=='') $_POST['priority']='%';
if($_POST['state']=='') {$_POST['state']='%'; }
if($_POST['type']=='') {$_POST['type']='%'; }

//avoid page 2 bug when technician switch
if(($_POST['technician']!=$_GET['technician']) && ($_GET['cursor']!=0)) {$_GET['cursor']=0;} 

//default values check user profil parameters

//if admin user
if($_SESSION['profile_id']==0 || $_SESSION['profile_id']==4)
{
	if($_POST['technician']=='') $_POST['technician']= $_GET['userid'];
	if($_POST['user']=='') $_POST['user']= '%'; 	
} elseif($rright['ticket_tech_super'] && $_SESSION['profile_id']==3) { #case super is tech to have access to your tickets #6212
	if($_POST['technician']=='') $_POST['technician']= $_GET['userid'];
	if($_POST['user']=='') $_POST['user']= '%'; 	
} elseif($rright['userbar'] && $rright['side_all'] && $_GET['userid']==0) { #case super have access to new tickets #6209
	if($_POST['technician']=='') $_POST['technician']= $_GET['userid'];
	if($_POST['user']=='') $_POST['user']= '%'; 	
} else {
	if($_POST['user']=='') $_POST['user']= $_GET['userid'];
	if($_POST['technician']=='') $_POST['technician']= '%';
}

// check if user have right to display all user ticket
if(($_POST['user']=='%' || $_POST['user']=='%25') && $_POST['technician']!=$_SESSION['user_id']&& !$rright['side_all']&&!$_GET['companyview']&&!$_GET['techgroup']) {$_POST['user']=$_SESSION['user_id'];}

if($_POST['date_create']=='') $_POST['date_create']= '%'; 
if($_POST['time']=='') $_POST['time']= '%'; 
if($_POST['date_res']=='') $_POST['date_res']= '%'; 
if($_POST['date_modif']=='') $_POST['date_modif']= '%'; 
if($_POST['sender_service']=='') $_POST['sender_service']= '%'; 
if($_POST['title']=='') $_POST['title']= '%'; 
if($_POST['ticket']=='') $_POST['ticket']= '%'; 
if($_POST['userid']=='') $_POST['userid']= '%'; 
if($_POST['category']=='') $_POST['category']= '%'; 
if($_POST['subcat']=='') $_POST['subcat']= '%';
if($_POST['asset']=='') $_POST['asset']= '%';
if($_POST['place']=='') $_POST['place']= '%';
if($_POST['service']=='') $_POST['service']= '%';
if($_POST['agency']=='') $_POST['agency']= '%';
if($_POST['company']=='') $_POST['company']= '%';

//secure check var
if(!is_numeric($rparameters['maxline'])) {echo 'ERROR : Wrong maxline value'; exit;}

//technician and technician group separate
if(substr($_POST['technician'], 0, 1) =='G') 
{
 	$t_group = explode("_", $_POST['technician']);
	$t_group=$t_group[1];
	$_GET['t_group']=$t_group;
	$_POST['technician']='%';
}
//user and user group separate
if(substr($_POST['user'], 0, 1) =='G') 
{
 	$u_group = explode("_", $_POST['user']);
	$u_group=$u_group[1];
	$_GET['u_group']=$u_group;
	$_POST['user']='%';
}
//special case to filter technician group is send
if($rright['side_your_tech_group'] && $_GET['techgroup']){$_POST['technician']="%";}

//special case to display observer list, remove user or technician filter
if($rright['side_your_observer'] && $_GET['view']=='observer'){$_POST['technician']="%"; $_POST['user']="%";}

//select order 
if(($filter=='on' || $_GET['order']=='')){
    if($ruser['dashboard_ticket_order']) 
	{
		$_GET['order']=$ruser['dashboard_ticket_order'];
		if($ruser['dashboard_ticket_order']=='tincidents.date_hope') {$_GET['way']='ASC';} else {$_GET['way']='DESC';} #3697
	} else {
		//modify order to resolution date for state 3 and 4 
		if(preg_match("#tstates.number, tincidents.date_hope#i", "'.$rparameters[order].'") && (($_GET['state']==3) || ($_GET['state']==4)))
		{
			$_GET['order']='tincidents.date_res';
			$_GET['way']='DESC';
		} else {
			$_GET['order']=$rparameters['order'];
		}
	}
	$_GET['order']=str_replace(' ','', $_GET['order']);
}
elseif($_GET['order']=='')
{$_GET['order']='priority';}

$db_order=strip_tags($db->quote($_GET['order']));
$db_order=str_replace("'","",$db_order);
if($_GET['way']=='ASC' || $_GET['way']=='DESC') {$db_way=$_GET['way'];} else {$db_way='DESC';}
$db_state=strip_tags($db->quote($_GET['state']));
$db_viewid=strip_tags($db->quote($_GET['viewid']));
$db_techgroup=strip_tags($db->quote($_GET['techgroup']));
$db_u_group=strip_tags($db->quote($_GET['u_group']));
$db_t_group=strip_tags($db->quote($_GET['t_group']));
$db_techread=strip_tags($db->quote($_GET['techread']));
$db_userread=strip_tags($db->quote($_GET['userread']));
$db_keywords=strip_tags($db->quote($_GET['keywords']));
if(is_numeric($_GET['cursor'])) {$db_cursor=$_GET['cursor'];} else {$db_cursor=0;}

//meta state generation
if($_GET['state']=='meta')
{
	$state='AND	(';
	$qry=$db->prepare("SELECT `id` FROM `tstates` WHERE `meta`='1'");
	$qry->execute();
	while($row=$qry->fetch()) 
	{
		$state.='tincidents.state LIKE '.$row['id'].' OR ';
	}
	$qry->closeCursor();
	$state.=' 1=0)';
	
    //change order in this case
    if($_GET['order']=='tstates.number, tincidents.priority, tincidents.criticality, tincidents.date_create') {$_GET['order']='tincidents.priority, tincidents.criticality, tincidents.date_create';}
    if($_GET['order']=='tstates.number, tincidents.priority, tincidents.criticality, tincidents.date_hope') {$_GET['order']='tincidents.priority, tincidents.criticality, tincidents.date_hope';}
    if($_GET['order']=='tstates.number, tincidents.date_hope, tincidents.priority, tincidents.criticality') {$_GET['order']='tincidents.date_hope, tincidents.priority, tincidents.criticality';}
    if($_GET['order']=='tstates.number, tincidents.date_hope, tincidents.criticality, tincidents.priority') {$_GET['order']='tincidents.date_hope, tincidents.criticality, tincidents.priority';}
    if($_GET['order']=='tstates.number, tincidents.criticality, tincidents.date_hope, tincidents.priority') {$_GET['order']='tincidents.criticality, tincidents.date_hope, tincidents.priority';}
} else {
    $state='AND	tincidents.state LIKE \''.$_POST['state'].'\'';
}

$url_post_parameters="userid=$_GET[userid]&amp;t_group=$_GET[t_group]&amp;state=$_POST[state]&amp;viewid=$_GET[viewid]&amp;ticket=$_POST[ticket]&amp;technician=$_POST[technician]&amp;techgroup=$_GET[techgroup]&amp;user=$_POST[user]&amp;sender_service=$_POST[sender_service]&amp;category=$_POST[category]&amp;subcat=$_POST[subcat]&amp;asset=$_POST[asset]&amp;title=$_POST[title]&amp;date_create=$_POST[date_create]&amp;date_modif=$_POST[date_modif]&amp;priority=$_POST[priority]&amp;criticality=$_POST[criticality]&amp;place=$_POST[place]&amp;service=$_POST[service]&amp;agency=$_POST[agency]&amp;companyview=$_GET[companyview]&amp;type=$_POST[type]&amp;company=$_POST[company]&amp;keywords=$keywords&amp;view=$_GET[view]&amp;date_start=$_POST[date_start]&amp;date_end=$_POST[date_end]&amp;time=$_POST[time]&amp;techread=$_GET[techread]";
$url_post_parameters=preg_replace('/%/','%25',$url_post_parameters);
//special case redirect to all ticket if date create is filtered on activity view
if(!isset($today)) {$today=date('Y-m-d');}
if($_GET['view']=='activity' && $_POST['date_create']!=$today && $_POST['date_create']!='current' && $_POST['date_create']!='%')
{
	echo "<SCRIPT LANGUAGE='JavaScript'>
				<!--
				function redirect()
				{
				window.location='./index.php?page=dashboard&amp;userid=%25&amp;state=%25&ticket=%25&amp;technician=%25&amp;user=%25&amp;category=%25&amp;subcat=%25&amp;title=%25&amp;date_create=$_POST[date_create]&amp;priority=%25&amp;criticality=%25&amp;company=%'
				}
				setTimeout('redirect()',0);
				-->
		</SCRIPT>";
} 
if($_GET['view']=='activity' && $_POST['date_res']!=$today && $_POST['date_res']!='current' && $_POST['date_res']!='%')
{
	echo "<SCRIPT LANGUAGE='JavaScript'>
				<!--
				function redirect()
				{
				window.location='./index.php?page=dashboard&amp;userid=%25&amp;state=%25&amp;ticket=%25&amp;technician=%25&amp;user=%25&amp;category=%25&amp;subcat=%25&amp;title=%25&amp;date_res=$_POST[date_res]&amp;priority=%25&amp;criticality=%25&amp;company=%'
				}
				setTimeout('redirect()',0);
				-->
		</SCRIPT>";
} 

//load in url parameter of filter, for using back button of browser on ticket page
if(
	($_POST['ticket']!='%' && $_GET['ticket']=='%') ||
	($_POST['technician']!='%' && $_GET['technician']=='%') ||
	($_POST['user']!='%' && $_GET['user']=='%') ||
	($_POST['sender_service']!='%' && $_GET['sender_service']=='%') ||
	($_POST['category']!='%' && $_GET['category']=='%') ||
	($_POST['subcat']!='%' && $_GET['subcat']=='%') ||
	($_POST['asset']!='%' && $_GET['asset']=='%') ||
	($_POST['title']!='%' && $_GET['title']=='%') ||
	($_POST['priority']!='%' && $_GET['priority']=='%') ||
	($_POST['criticality']!='%' && $_GET['criticality']=='%') ||
	($_POST['place']!='%' && $_GET['place']=='%') || 
	($_POST['service']!='%' && $_GET['service']=='%') || 
	($_POST['agency']!='%' && $_GET['agency']=='%') || 
	($_POST['type']!='%' && $_GET['type']=='%') || 
	($_POST['company']!='%' && $_GET['company']=='%') || 
	($_POST['state']!='%' && $_GET['state']=='%') || 
	($_GET['date_range']==1)
)
{
	$reload=1;

	//replace &amp; in script
	$url_post_parameters_script=str_replace('&amp;','&',$url_post_parameters);
	//redirect
	echo "<SCRIPT LANGUAGE='JavaScript'>
				<!--
				function redirect()
				{
				window.location='./index.php?page=dashboard&$url_post_parameters_script'
				}
				setTimeout('redirect()',0);
				-->
		</SCRIPT>";
	echo '<i class="fa fa-spinner fa-spin text-info text-120"><!----></i>&nbsp;'.T_('Chargement...');
} else {$reload=0;}

///// SQL QUERY
	//Date conversion for filter line
	if($_POST['date_create']!='%')
	{
		$date_create=$_POST['date_create'];
		$find='/';
		$find= strpos($date_create, $find);
		if($find!=false)
		{			
			$date_create=explode("/",$date_create);
			if(isset($date_create[2]) && isset($date_create[1]) && isset($date_create[0])){$_POST['date_create']="$date_create[2]-$date_create[1]-$date_create[0]";}
		}
	}
	if($_POST['date_hope']!='%')
	{
		$date_hope=$_POST['date_hope'];
		$find='/';
		$find= strpos($date_hope, $find);
		if($find!=false)
		{			
			$date_hope=explode("/",$date_hope);
			if(isset($date_hope[2]) && isset($date_hope[1]) && isset($date_hope[0])){$_POST['date_hope']="$date_hope[2]-$date_hope[1]-$date_hope[0]";}
		}
	}
	if($_POST['date_res']!='%')
	{
		$date_res=$_POST['date_res'];
		$find='/';
		$find= strpos($date_res, $find);
		if($find!=false)
		{			
			$date_res=explode("/",$date_res);
			if(isset($date_res[2]) && isset($date_res[1]) && isset($date_res[0])){$_POST['date_res']="$date_res[2]-$date_res[1]-$date_res[0]";}
		}
	}
	if($_POST['date_modif']!='%')
	{
		$date_modif=$_POST['date_modif'];
		$find='/';
		$find= strpos($date_modif, $find);
		if($find!=false)
		{			
			$date_modif=explode("/",$date_modif);
			if(isset($date_modif[2]) && isset($date_modif[1]) && isset($date_modif[0])){$_POST['date_modif']="$date_modif[2]-$date_modif[1]-$date_modif[0]";}
		}
	}
	if($keywords && $reload==0)
	{
		include "./core/searchengine_ticket.php";
	} else {
		//escape special char and secure string before database insert
		$db_sender_service=strip_tags($db->quote($_POST['sender_service']));
		$db_category=strip_tags($db->quote($_POST['category']));
		$db_subcat=strip_tags($db->quote($_POST['subcat']));
		$db_asset=strip_tags($db->quote($_POST['asset']));
		$db_userid=strip_tags($db->quote($_POST['userid']));
		$db_user=strip_tags($db->quote($_POST['user']));
		$db_ticket=strip_tags($db->quote($_POST['ticket']));
		$db_priority=strip_tags($db->quote($_POST['priority']));
		$db_criticality=strip_tags($db->quote($_POST['criticality']));
		$db_type=strip_tags($db->quote($_POST['type']));
		$db_technician=strip_tags($db->quote($_POST['technician']));
		$db_title=strip_tags($db->quote($_POST['title']));
		$db_title=str_replace("'","",$db_title);
		$db_u_group=strip_tags($db->quote($_GET['u_group']));
		$db_t_group=strip_tags($db->quote($_GET['t_group']));
		$db_techread=strip_tags($db->quote($_GET['techread']));
		$db_userread=strip_tags($db->quote($_GET['userread']));
	
		//build SQL query //remove DISTINCT 
		$select= "
		tincidents.id,
		tincidents.technician,
		tincidents.t_group,
		tincidents.title,
		tincidents.user,
		tincidents.u_group,
		tincidents.techread_date,
		tincidents.techread,
		tincidents.userread,
		tincidents.date_hope,
		tincidents.date_modif,
		tincidents.date_res,
		";
		//add only display cols
		if($rright['dashboard_col_user_service']){$select.='tincidents.sender_service,';}
		if($rright['dashboard_col_service']){$select.='tincidents.u_service,';}
		if($rright['dashboard_col_agency']){$select.='tincidents.u_agency,';}
		if($rright['dashboard_col_type']){$select.='tincidents.type,';}
		if($rright['dashboard_col_category']){$select.='tincidents.category,';}
		if($rright['dashboard_col_subcat']){$select.='tincidents.subcat,';}
		if($rright['dashboard_col_asset']){$select.='tincidents.asset_id,';}
		if($rright['dashboard_col_criticality']){$select.='tincidents.criticality,';}
		if($rright['dashboard_col_priority']){$select.='tincidents.priority,';}
		if($rright['dashboard_col_date_create'] ||$rright['dashboard_col_date_create_hour']){$select.='tincidents.date_create,';}
		if($rright['dashboard_col_time']){$select.='tincidents.time,';}
		if($rright['ticket_billable']){$select.='tincidents.billable,';}
		if($rparameters['ticket_places']){$select.='tincidents.place,';}
		$select.='tincidents.state';

		$from="tincidents";
		$join='LEFT JOIN tstates ON tincidents.state=tstates.id ';
		$where="tincidents.disable='0' ";
		if($db_sender_service!="'%'") {$where.="AND tincidents.sender_service LIKE $db_sender_service";}
		if($db_u_group!="'%'") {$where.="AND tincidents.u_group LIKE $db_u_group";}
		if($db_t_group!="'%'") {$where.="AND tincidents.t_group LIKE $db_t_group";}
		if($db_techread!="'%'") {$where.="AND tincidents.techread LIKE $db_techread AND tincidents.technician=$_GET[userid] "; }
		if($db_userread!="'%'") {$where.="AND tincidents.userread LIKE $db_userread";}
		if($db_category!="'%'") {$where.="AND tincidents.category LIKE $db_category";}
		if($db_subcat!="'%'") {$where.="AND	tincidents.subcat LIKE $db_subcat";}
		if($db_asset!="'%'") {$where.="AND	tincidents.asset_id LIKE $db_asset";}
		if($db_ticket!="'%'") {$where.="AND	tincidents.id LIKE $db_ticket";}
		if($db_userid!="'%'") {$where.="AND	tincidents.user LIKE $db_userid";}
		if($db_priority!="'%'") {$where.="AND tincidents.priority LIKE $db_priority";}
		if($db_criticality!="'%'") {$where.="AND tincidents.criticality LIKE $db_criticality";}
		if($db_type!="'%'") {$where.="AND tincidents.type LIKE $db_type";}
		if($db_title!='%') {$where.="AND tincidents.title LIKE '%$db_title%'";}
		if($_POST['date_hope']!='') {$where.="AND tincidents.date_hope LIKE '$_POST[date_hope]%'";}
		
		if($db_state!="''" && $db_state!="'%'" && $_GET['state']!='meta' && $_POST['state']!='%') {$where.="AND tincidents.state LIKE $db_state";}
		if($db_state!="'%'" && $_GET['state']=='meta') {$where.=$state; }
		if($db_state!="'%'" && $_GET['techgroup']) {$where.=$state;} #5993
		if($_GET['state']!="%" && $_POST['state']=='%') {$where.=$state;  } #6019
	
		//special case to display ticket where technician is user associated to the ticket
		if($db_user=="'%'" && $db_technician=="'%'")
		{
			//remove from query
		}else {
			if(($_SESSION['profile_id']==0 || $_SESSION['profile_id']==4) && $_GET['userid']!='%' && $_GET['userid'] && $_GET['state']!='meta')
			{
				$where.="AND tincidents.user LIKE $db_user AND (tincidents.technician LIKE $db_technician OR tincidents.user LIKE $db_technician) ";
			} else {
				$where.="AND tincidents.user LIKE $db_user AND tincidents.technician LIKE $db_technician ";
			}
		}
		
		//special case to filter query by user company
		if($rparameters['user_company_view'] && $rright['side_company'] && $_GET['companyview'])
		{
			//check if company table is not empty, before add join
			$qry=$db->prepare("SELECT count(id) FROM `tcompany`");
			$qry->execute();
			$row=$qry->fetch();
			$qry->closeCursor();
			if($row[0]>1)
			{
				$join.='LEFT JOIN tusers ON tincidents.user=tusers.id ';
				$where.="AND tusers.company='$ruser[company]' ";
			}
		}
		//special case to filter query by observer
		if($rparameters['ticket_observer'] && $rright['side_your_observer'] && $_GET['view']=='observer')
		{
			$where.='AND (tincidents.observer1='.$_SESSION['user_id'].' OR tincidents.observer2='.$_SESSION['user_id'].' OR tincidents.observer3='.$_SESSION['user_id'].') ';
		}
		//special case to filter query when user company right is enable
		if($rright['dashboard_col_company'])
		{
			$db_company=strip_tags($db->quote($_POST['company']));
			if(!preg_match('#LEFT JOIN tusers#',$join)) {$join.='LEFT JOIN tusers ON tincidents.user=tusers.id ';}
			$join.='LEFT JOIN tcompany ON tusers.company=tcompany.id';
			if($db_company!="'%'") {$where.="AND tcompany.id LIKE $db_company ";}
		}
				
		//special case where user have service and agency
		if(($rparameters['user_agency']==1 && ($_GET['userid']=='%' || $_GET['userid']=='0'))&&($rparameters['user_limit_service']==1 && ($_GET['userid']=='%' || $_GET['userid']=='0'))&& $cnt_agency!=0 && $cnt_service!=0)
		{
			$where.= "$where_agency $where_service $parenthese2" ;
		} else {
			//special case query when agency parameter is enable to display agency ticket and user tickets
			if($rparameters['user_agency']==1 && ($_GET['userid']=='%' || $_GET['userid']=='0')){$where.=$where_agency;} else {$where.=$where_agency_your;} 
		
			//special case query when service parameter is enable to display service ticket and user tickets
			if($rparameters['user_limit_service']==1 && ($_GET['userid']=='%' || $_GET['userid']=='0')){$where.=$where_service;} else {$where.=$where_service_your;} 
		}
		
		//special case to filter query when place view is selected
		if($rparameters['ticket_places']==1){
			$db_place=strip_tags($db->quote($_POST['place']));
			$where.="AND tincidents.place LIKE $db_place ";
		}
		
		//special case to filter query when service parameter is enable
		if($rright['dashboard_col_service']){
			$db_service=strip_tags($db->quote($_POST['service']));
			if($db_service!="'%'") {$where.="AND tincidents.u_service LIKE $db_service ";}
		}
		
		//special case to filter query 
		if($rright['dashboard_col_time']){
			$db_time=strip_tags($db->quote($_POST['time']));
			if($db_time!="'%'") {$where.="AND tincidents.time LIKE $db_time ";}
		}
		
		//special case to filter query when agency parameter is enable
		if($rright['dashboard_col_agency']){
			$db_agency=strip_tags($db->quote($_POST['agency']));
			if($db_agency!="'%'") {$where.="AND tincidents.u_agency LIKE $db_agency ";}
		}
		
		//special case to filter query for activities tickets
		if($_GET['view']=='activity')
		{
			//add distinct
			$select=str_replace('tincidents.id','DISTINCT tincidents.id',$select);

			//case of range period selected else today tickets
			if($_POST['date_start'] && $_POST['date_end'])
			{
				if(preg_match('#/#', $_POST['date_start'])) //convert date only if slash detected
				{
					//convert date format
					$_POST['date_start']=DateTime::createFromFormat('d/m/Y', $_POST['date_start']);
					$_POST['date_start']=$_POST['date_start']->format('Y-m-d');
					$_POST['date_end']=DateTime::createFromFormat('d/m/Y', $_POST['date_end']);
					$_POST['date_end']=$_POST['date_end']->format('Y-m-d');
				}
				$from="tincidents,tthreads,tstates";
				$join='';
				$where.="AND tincidents.id=tthreads.ticket AND tincidents.state=tstates.id ";
				$where.="AND ((tincidents.date_create BETWEEN '$_POST[date_start] 00:00:00' AND '$_POST[date_end] 23:59:59') OR ((tincidents.date_res BETWEEN '$_POST[date_start] 00:00:00' AND '$_POST[date_end] 23:59:59') AND tincidents.date_res!='0000-00-00 00:00:00') OR (tthreads.date BETWEEN '$_POST[date_start] 00:00:00' AND '$_POST[date_end] 23:59:59' AND tthreads.type=0))";
			} else {
				$from="tincidents,tthreads,tstates";
				$join='';
				$where.="AND tincidents.id=tthreads.ticket AND tincidents.state=tstates.id ";
				$where.="AND (tincidents.date_create LIKE '$_POST[date_create]%' OR tincidents.date_res LIKE '$_POST[date_create]%' OR (tthreads.date LIKE '$_POST[date_create]%' AND tthreads.type=0))";
			}
			//case company col
			if($rright['dashboard_col_company'])
			{
				$from.=",tcompany,tusers";
				$where.="AND tincidents.user=tusers.id AND tusers.company=tcompany.id ";
				$where.="AND tcompany.id LIKE '$_POST[company]' ";
			}
			//case side_company and activity view
			if($rright['side_company'] && !preg_match('#tusers#',$from))
			{
				$from.=',tusers';
				$where.='AND tincidents.technician=tusers.id ';
			}
		} else {
			if($_POST['date_create']!='%') {$where.="AND tincidents.date_create LIKE '$_POST[date_create]%'";}
			if($_POST['date_res']!='%') {$where.="AND tincidents.date_res LIKE '$_POST[date_res]%'";}
			if($_POST['date_modif']!='%') {$where.="AND tincidents.date_modif LIKE '$_POST[date_modif]%'";}
		}
		//special case to filter technician group is send
		if($rright['side_your_tech_group'] && $_GET['techgroup'])
		{
			$where.="AND tincidents.t_group='$_GET[techgroup]' ";
		}
	}
	
	if($rparameters['debug'])
	{
		$where_debug=str_replace("AND", "AND <br />",$where);
		$where_debug=str_replace("OR", "OR <br />",$where_debug);
		$join_debug =str_replace("LEFT", "<br />LEFT",$join);

		echo "
		<b><u>DEBUG MODE :</u></b> <i>(To disable go to Administration > Parameters > General, and uncheck debug)<!----></i><br />
		<b>SELECT</b> $select <br />
		<b>FROM</b> $from
		$join_debug<br />
		<b>WHERE</b> <br />
		$where_debug<br />
		<b>ORDER BY</b> $db_order $db_way<br />
		<b>LIMIT</b> $db_cursor,$rparameters[maxline]<br />
		<b>VAR:</b>
		POST_keywords=$_POST[keywords] GET_keywords=$db_keywords |
		POST_state=$_POST[state] GET_state=$_GET[state] state=$state |
		POST_priority=$_POST[priority] GET_priority=$_GET[priority] db_priority=$db_priority|
		POST_date_create=$_POST[date_create] GET_date_create=$_GET[date_create] |
		cnt_service=$cnt_service  |
		GET_view=$_GET[view] | 
		POST_date_start=$_POST[date_start] |
		POST_date_end=$_POST[date_end] 
		POST_date_end= 
		";
		
		if($user_services) {echo ' user_services=';foreach($user_services as $value) {echo $value.' ';}} 
		echo '| cnt_agency='.$cnt_agency;
		if($user_agencies) {echo ' user_agencies=';foreach($user_agencies as $value) {echo $value.' ';}} 
		
	}
	if(!$reload) //avoid double query for reload parameters in url optimization for large database
	{
		$masterquery = $db->query("
		SELECT SQL_CALC_FOUND_ROWS $select
		FROM $from
		$join
		WHERE $where
		ORDER BY $db_order $db_way
		LIMIT $db_cursor,
		$rparameters[maxline]
		"); 
	} else {$masterquery='';}
	$query=$db->query("SELECT FOUND_ROWS();");
	$resultcount=$query->fetch();
	$query->closeCursor();
	
//check box selection SQL updates
if($_POST['selectrow'] && $_POST['selectrow']!='selectall')
{
	while($row=$masterquery->fetch())
	{
		//initialize variables 
		if(!isset($_POST['checkbox'.$row["id"]])) $_POST['checkbox'.$row["id"]] = ''; 
		if($_POST['checkbox'.$row['id']]!='') 
		{
			//change state
			if($_POST['selectrow']=="delete" && $rright['ticket_delete'] && $row['id'])
			{
				DeleteTicket($row['id']);
				echo DisplayMessage('success',T_('Ticket supprimé'));
			} elseif ($_POST['selectrow']=="read")
			{
				$qry=$db->prepare("UPDATE `tincidents` SET `techread`='1' WHERE id=:id"); //technician read
				$qry->execute(array('id' => $row['id']));
			}else{			
				$qry=$db->prepare("UPDATE `tincidents` SET `state`=:state WHERE `id`=:id");
				$qry->execute(array('state' => $_POST['selectrow'],'id' => $row['id']));
				//insert ticket threads
				if($_POST['selectrow']==3)
				{
					$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`type`,`state`) VALUES (:ticket,:date,:author,4,3)");
					$qry->execute(array('ticket' => $row['id'],'date' => date('Y-m-d H:i:s'),'author' => $_SESSION['user_id']));
				} elseif(is_numeric($_POST['selectrow']))
				{
					$qry=$db->prepare("INSERT INTO `tthreads` (`ticket`,`date`,`author`,`type`,`state`) VALUES (:ticket,:date,:author,5,:state)");
					$qry->execute(array('ticket' => $row['id'],'date' => date('Y-m-d H:i:s'),'author' => $_SESSION['user_id'],'state' => $_POST['selectrow']));
				}	
				echo DisplayMessage('success',T_('Ticket').' '.$row['id'].' '.T_('modifié'));			
				if($_POST['selectrow']==3) //case solved state 
				{
					//insert current date in resolution date
					$currentdate=date("Y-m-d H:i:s"); 
					$qry=$db->prepare("UPDATE `tincidents` SET `date_res`=:date_res WHERE `id`=:id");
					$qry->execute(array('date_res' => $currentdate,'id' => $row['id']));
					//send mail notifications
					if($rparameters['mail_auto']) 
					{
						$_GET['id']=$row['id'];
						$autoclose=1;
						require('core/auto_mail.php');
					}
				}
			}
		}
	}
	$masterquery->closeCursor();
	
	//redirect
	$url="./index.php?page=dashboard&state=$_GET[state]&userid=$_GET[userid]";
	$url=preg_replace('/%/','%25',$url);
	echo "<SCRIPT LANGUAGE='JavaScript'>
				<!--
				function redirect()
				{
				window.location='$url'
				}
				setTimeout('redirect()',$rparameters[time_display_msg]);
				-->
		</SCRIPT>";
}
?>

<div class="page-header position-relative">
	<h1 class="page-title text-primary-m2">
		<?php
		if($keywords)
		{
			$disp_keyword=str_replace("'","",$keywords);
			echo '<i class="fa fa-search text-primary-m2"><!----></i> '.T_('Recherche de tickets').' : '.$keywords.' ';
		} 
		elseif($_GET['view']=='activity')
		{
			//convert and create date format to display
			if($_POST['date_start'] && $_POST['date_end']) // case post date
			{
				//convert date to display
				$date_start_db=DateTime::createFromFormat('Y-m-d', $_POST['date_start']);
				$date_end_db=DateTime::createFromFormat('Y-m-d', $_POST['date_end']);
				$date_start=$date_start_db->format('d/m/Y');
				$date_end=$date_end_db->format('d/m/Y');
				$date_start_db=$date_start_db->format('Y-m-d');
				$date_end_db=$date_end_db->format('Y-m-d');
			} else { //default date is today date
				$date_start_db=DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
				$date_end_db=DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
				$date_start=$date_start_db->format('d/m/Y');
				$date_end=$date_end_db->format('d/m/Y');
				$date_start_db=$date_start_db->format('Y-m-d');
				$date_end_db=$date_end_db->format('Y-m-d');
			}
			
			//count open ticket for selected period
			$query="
			SELECT DISTINCT(id) FROM `tincidents`
			WHERE
			tincidents.date_create BETWEEN '$date_start_db 00:00:00' AND '$date_end_db 23:59:59' 
			AND	tincidents.id LIKE $db_ticket
			AND	tincidents.technician LIKE '$_POST[technician]'
			AND	tincidents.user LIKE '$_POST[user]'
			AND	tincidents.category LIKE '$_POST[category]'
			AND	tincidents.subcat LIKE '$_POST[subcat]'
			AND	tincidents.title LIKE '%$db_title%'
			AND tincidents.date_create LIKE '$_POST[date_create]%'
			AND tincidents.date_hope LIKE '$_POST[date_hope]%'
			AND tincidents.date_res LIKE '$_POST[date_res]%'
			AND	tincidents.state LIKE '$_POST[state]'
			AND	tincidents.criticality LIKE '$_POST[criticality]'
			AND	tincidents.priority LIKE '$_POST[priority]'
			AND disable='0'
			$where_agency $where_service $parenthese2";
			$query = $db->query($query);  
			$cnt_activity_open=$query->rowCount();
			$query->closecursor();
			//count advanced ticket for selected period (technician add text resolution and ticket not disable)
			$query="
			SELECT DISTINCT(tthreads.id) FROM `tthreads`,`tincidents` 
			WHERE 
			tincidents.id=tthreads.ticket 
			AND tincidents.technician=tthreads.author 
			AND tincidents.state!=3 
			AND	tincidents.id LIKE $db_ticket
			AND	tincidents.technician LIKE '$_POST[technician]'
			AND	tincidents.user LIKE '$_POST[user]'
			AND	tincidents.category LIKE '$_POST[category]'
			AND	tincidents.subcat LIKE '$_POST[subcat]'
			AND	tincidents.title LIKE '%$db_title%'
			AND tincidents.date_create LIKE '$_POST[date_create]%'
			AND tincidents.date_hope LIKE '$_POST[date_hope]%'
			AND tincidents.date_res LIKE '$_POST[date_res]%'
			AND	tincidents.state LIKE '$_POST[state]'
			AND	tincidents.criticality LIKE '$_POST[criticality]'
			AND	tincidents.priority LIKE '$_POST[priority]'
			AND tincidents.disable=0
			AND tthreads.type='0'
			AND tthreads.date BETWEEN '$date_start_db 00:00:00' AND '$date_end_db 23:59:59' 
			$where_agency $where_service $parenthese2
			";
			$query = $db->query($query);  
			$cnt_activity_advanced=$query->rowCount();
			$query->closecursor();
			//count close tickets for selected period
			$query="
			SELECT DISTINCT(id) FROM `tincidents`
			WHERE 
			tincidents.state='3' 
			AND	tincidents.id LIKE $db_ticket
			AND	tincidents.technician LIKE '$_POST[technician]'
			AND	tincidents.user LIKE '$_POST[user]'
			AND	tincidents.category LIKE '$_POST[category]'
			AND	tincidents.subcat LIKE '$_POST[subcat]'
			AND	tincidents.title LIKE '%$db_title%'
			AND tincidents.date_create LIKE '$_POST[date_create]%'
			AND tincidents.date_hope LIKE '$_POST[date_hope]%'
			AND tincidents.date_res LIKE '$_POST[date_res]%'
			AND	tincidents.state LIKE '$_POST[state]'
			AND	tincidents.criticality LIKE '$_POST[criticality]'
			AND	tincidents.priority LIKE '$_POST[priority]'
			AND date_res BETWEEN '$date_start_db 00:00:00' AND '$date_end_db 23:59:59' 
			AND disable='0' 
			$where_agency $where_service $parenthese2";
			$query = $db->query($query);  
			$cnt_activity_close=$query->rowCount();
			$query->closecursor();
			
			//display title with date selection form
			echo '
			<span title="'.T_("Liste des tickets modifiés: ouverts ou fermés ou sur lesquels un élément de résolution à été ajouté sur la période sélectionnée").'">
			<i class="fa fa-calendar text-primary-m2"><!----></i> 
			'.T_('Tickets modifiés du').'
			</span>
			<form style="display: inline-block;" class="form-horizontal" name="period" id="period" method="post" action="./index.php?page=dashboard&amp;userid='.$_GET['userid'].'&amp;state=%25&amp;view=activity&amp;date_range=1" onsubmit="loadVal();" >
				<input class="form-control-sm" data-toggle="datetimepicker" data-target="#date_start" type="text" autocomplete="off" size="10" name="date_start" id="date_start" value="'.$date_start.'" onchange="" >
				'.T_('au').'
				<input class="form-control-sm" data-toggle="datetimepicker" data-target="#date_end" type="text" autocomplete="off" size="10" name="date_end" id="date_end" value="'.$date_end.'" onchange="" >
				<button class="btn btn-xs btn-success" title="'.T_('Valider la sélection').'" name="modify" value="submit" type="submit" id="modify_btn"><i class="fa fa-check text-110"><!----></i></button>
			</form>
			';
		}
		else 
		{
		    //find state name for display in title
			$qry=$db->prepare("SELECT `description` FROM `tstates` WHERE id=:id");
			$qry->execute(array('id' => $_GET['state']));
			$rstate=$qry->fetch();
			$qry->closeCursor();
			if(empty($rstate)) {$rstate=array();} 
            if(!$rstate && !$_GET['viewid'] && !$_GET['techgroup']) {$rstate['description']=T_('tickets non lus');} //case not read
            if($_GET['state']=='meta') $rstate['description']=T_('tickets à traiter'); //case not read
			//special case for service only add name of services at the end of the title
			if($rright['dashboard_service_only'] && $rparameters['user_limit_service']==1 && $cnt_service!=0 )  
			{
				$service_title='';
				$cnt=0;
				//get services of current user
				$qry=$db->prepare("SELECT `service_id` FROM `tusers_services` WHERE user_id=:user_id");
				$qry->execute(array('user_id' => $_SESSION['user_id']));
				while($row=$qry->fetch()) 
				{
					$cnt++;
					//get service name to display in title
					$qry2=$db->prepare("SELECT `name` FROM `tservices` WHERE id=:id");
					$qry2->execute(array('id' => $row['service_id']));
					$row2=$qry2->fetch();
					$qry2->closeCursor();
					
					if($cnt==1){$service_title.=' '.$row2['name'];} else {$service_title.=' '.T_("et").' '.$row2['name'];}
				}
				$qry->closeCursor();
				$service_title=' '.T_("du service").' '.$service_title;
			} else {$service_title='';}
			//special case for agency only add name of agencies at the end of the title
			if($rright['dashboard_agency_only'] && $cnt_agency!=0 && $rparameters['user_agency'])  
			{
				$agency_title='';
				$cnt=0;
				//get agencies of current user
				$qry=$db->prepare("SELECT `agency_id` FROM `tusers_agencies` WHERE user_id=:user_id");
				$qry->execute(array('user_id' => $_SESSION['user_id']));
				while($row=$qry->fetch()) 
				{
					$cnt++;
					//get agency name to display in title
					$qry2=$db->prepare("SELECT `name` FROM `tagencies` WHERE id=:id");
					$qry2->execute(array('id' => $row['agency_id']));
					$row2=$qry2->fetch();
					$qry2->closeCursor();
					if($cnt==1){$agency_title.=' '.$row2['name'];} else {$agency_title.=' '.T_("et").' '.$row2['name'];}
				}
				$qry->closeCursor();
				$agency_title=' '.T_("de l'agence").' '.$agency_title;
			} else {$agency_title='';}
            //find view name to display in title
            if($_GET['viewid']) 
            {
				$qry=$db->prepare("SELECT `name` FROM `tviews` WHERE id=:id");
				$qry->execute(array('id' => $_GET['viewid']));
				$rview=$qry->fetch();
				$qry->closeCursor();
				if(empty($rview['name'])) {
					//get technician name
					$qry2 = $db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
					$qry2->execute(array('id' => $_GET['technician']));
					$technician=$qry2->fetch();
					$qry2->closeCursor();

					$rview['name']=T_('technicien').' '.$technician['firstname'].' '.$technician['lastname'];

					$rstate['description']=T_('tickets à traiter de la vue').' '.$rview['name'].'';
					echo '<i class="fa fa-ticket text-primary-m2"><!----></i> '.T_('Tickets à traiter de la vue').' '.$rview['name'];
				} else {
					$rstate['description']=T_('tickets de la vue').' '.$rview['name'].'';
					echo '<i class="fa fa-ticket text-primary-m2"><!----></i> '.T_('Tickets de la vue').' '.$rview['name'];
				}
            }
            elseif($_GET['userid']=='%' && $_GET['companyview']=='')
			{
			    if($_GET['state']=='%') {echo '<i class="fa fa-ticket text-primary-m2"><!----></i> '.T_('Tous les tickets').$service_title.$agency_title;} else {echo '<i class="fa fa-ticket text-primary-m2"><!----></i> '.T_('Tous les').' '.T_($rstate['description']).$service_title.$agency_title;}
			}
			elseif($_GET['userid']=='%' && $_GET['companyview']!='')
			{
			    if($_GET['state']=='%') {echo '<i class="fa fa-ticket text-primary-m2"><!----></i> '.T_('Tous les tickets de ma société');} else {echo '<i class="fa fa-ticket text-primary-m2"><!----></i> '.T_($rstate['description']).' '.T_('de ma société');}
			}elseif($_GET['view']=='observer')
			{
				echo '<i class="fa fa-ticket text-primary-m2"><!----></i> '.T_('Vos tickets observés');
			}
			elseif($_GET['userid']!='0'  && !$_GET['techgroup'])
			{
			    if($_GET['state']=='%') {echo '<i class="fa fa-ticket text-primary-m2"><!----></i> '.T_('Tous vos tickets').'';} else {echo '<i class="fa fa-ticket text-primary-m2"><!----></i> '.T_('Vos').' '.T_($rstate['description']);}
			}
			elseif($_GET['techgroup'])
			{
				//get name of current group
				$qry=$db->prepare("SELECT `name` FROM `tgroups` WHERE id=:id");
				$qry->execute(array('id' => $_GET['techgroup']));
				$group_name=$qry->fetch();
				$qry->closeCursor();
				
				echo '<i class="fa fa-ticket text-primary-m2"><!----></i> '.T_('Vos tickets du groupe').' '.$group_name['name'];
			}
			if($_GET['state']=='%' && $_GET['userid']==0 && $_GET['userid']!='%') echo '<i class="fa fa-ticket text-primary-m2"><!----></i> '.T_('Tous les tickets non attribués'); //case not read
		}
		?>
		<small class="page-info text-secondary-d2">
			<i class="fa fa-angle-double-right"><!----></i>
			&nbsp;<?php if($mobile==0) {echo T_('Nombre').' :';} ?> <?php echo $resultcount[0]; ?>
			<?php
				//display counter section activity page
				if($_GET['view']=='activity')
				{
					echo ' 	| 
					<span class="mr-2" title="'.T_('Nombre de tickets pour lesquels la date de création est dans la période sélectionnée').'">'.T_('Ouverts').' : '.$cnt_activity_open.'</span>
					<span class="mr-2" title="'.T_("Nombre de tickets pour lesquels un élément de résolution textuel à été ajouté par le technicien en charge dans la période sélectionnée et qui ne sont pas dans l'état résolu").'">'.T_('Avancés').' : '.$cnt_activity_advanced.'</span>
					<span title="'.T_("Nombre de tickets pour lesquels la date de résolution est dans la période sélectionnée et qui sont dans l'état résolu").'">'.T_('Fermés').' :</span> '.$cnt_activity_close;
				}
			?>
		</small>
	</h1>
</div>
<?php
	//display message if search result is null
	if($resultcount[0]==0 && $keywords!="") {
		echo DisplayMessage('error',T_("Aucun ticket trouvé"));
	}
?>
<div class="mt-4 mt-lg-0 card bcard h-auto shadow border-0"> 
	<div class="table-responsive">
		<div class="col-xs-12">
			<form name="filter" id="filter" method="POST"></form>
			<form name="actionlist" id="actionlist" method="POST"> </form>
			<table id="simple-table" class="table table-bordered table-bordered table-striped table-hover text-dark-m2 " > 
				<?php 
				if($_GET['way']=='ASC') $arrow_way='DESC'; else $arrow_way='ASC';
				//*********************** FIRST LINE *********************** 
				echo '
				<thead class="text-dark-m3 bgc-grey-l4">
					<tr class="bgc-white text-secondary-d3 text-95">
						<th class="text-center '; if($_GET['order']=='id') {echo 'active';} echo '" >
							<a class="text-primary-m2" title="'.T_('Numéro du ticket').'" href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=id&amp;way='.$arrow_way.'">
								<i class="fa fa-tag text-primary-m2"><!----></i><br />
								'.T_('Numéro');
								//Display way arrows
								if($_GET['order']=='id'){
									if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
									if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
								}
								echo "
							</a>
						</th>
						";
						//display tech column, do not display tech column if technician is connected
						if($_SESSION['profile_id']!=0 || $_SESSION['profile_id']!=4 || $_GET['userid']=='%')
						{
							echo '
								<th class="text-center '; if($_GET['order']=='technician') {echo 'active';} echo '" >
									<a class="text-primary-m2" title="'.T_('Technicien en charge du ticket').'" href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=technician&amp;way='.$arrow_way.'">
										<i class="fa fa-user text-primary-m2"><!----></i><br />
										'.T_('Technicien');
										//Display arrows
										if($_GET['order']=='technician'){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
											if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo'
									</a>
								</th>
							';
						} 
						//display user company column
						if($rright['dashboard_col_company'])
						{
							echo '
								<th class="text-center '; if($_GET['order']=='company') {echo 'active';} echo '" >
									<a class="text-primary-m2" title="'.T_('Société du demandeur').'"  href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=company&amp;way='.$arrow_way.'">
										<i class="fa fa-building text-primary-m2"><!----></i><br />
										'.T_('Société');
										//Display arrows
										if($_GET['order']=='company'){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
											if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo'
									</a>
								</th>
							';
						}
						//display user column
						if(($_SESSION['profile_id']==0 || $_SESSION['profile_id']==3 || $_SESSION['profile_id']==4) || ($rright['side_all'] && ($_GET['userid']=='%'|| $keywords!='')) || ($rparameters['user_company_view']!=0 && $_GET['userid']=='%' && ($rright['side_company'] || $keywords!=''))) 
						{
							echo '
								<th class="text-center '; if($_GET['order']=='user') {echo 'active';} echo '" >
									<a class="text-primary-m2" title="'.T_('Demandeur associé au ticket').'" href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=user&amp;way='.$arrow_way.'">
										<i class="fa fa-male text-primary-m2"><!----></i><br />
										'.T_('Demandeur');
										//Display arrows
										if($_GET['order']=='user'){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
											if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo"
									</a>
								</th>
							";
						}
						//display user service column
						if($rright['dashboard_col_user_service']) 
						{
							echo '
							<th class="text-center '; if($_GET['order']=='sender_service') {echo 'active';} echo '" >
								<a class="text-primary-m2" title="'.T_('Service du demandeur').'" href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=sender_service&amp;way='.$arrow_way.'">
									<i class="fa fa-users text-primary-m2"><!----></i><br />
									'.T_('Service du demandeur');
									//Display arrows
									if($_GET['order']=='sender_service'){
										if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
										if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
									}
									echo"
								</a>
							</th>
							";
						}
						//display ticket type column
						if($rright['dashboard_col_type']) 
						{
							echo '
							<th class="text-center '; if($_GET['order']=='type') {echo 'active';} echo '" >
								<a class="text-primary-m2" title="'.T_('Type de ticket').'" href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=type&amp;way='.$arrow_way.'">
									<i class="fa fa-flag text-primary-m2"><!----></i><br />
									'.T_('Type');
									//Display arrows
									if($_GET['order']=='type'){
										if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
										if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
									}
									echo"
								</a>
							</th>
							";
						}
						//display ticket category column
						if($rright['dashboard_col_category']) 
						{
							echo '
								<th class="text-center '; if($_GET['order']=='category') {echo 'active';} echo '" >
									<a class="text-primary-m2" title="'.T_('Catégorie').'"  href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=category&amp;way='.$arrow_way.'">
										<i class="fa fa-square text-primary-m2"><!----></i><br />
										'.T_('Catégorie');
										//Display arrows
										if($_GET['order']=='category'){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
											if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo '
									</a>
								</th>
							';
						}
						//display ticket subcat column
						if($rright['dashboard_col_subcat']) 
						{
							echo'
								<th class="text-center '; if($_GET['order']=='subcat') {echo 'active';} echo '" >
									<a class="text-primary-m2" title="'.T_('Sous-catégorie').'" href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=subcat&amp;way='.$arrow_way.'">
										<i class="fa fa-sitemap text-primary-m2"><!----></i><br />
										'.T_('Sous-catégorie'); 
										//Display arrows
										if($_GET['order']=='subcat'){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
											if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo '
									</a>
								</th>
							';
						}
						//display ticket asset column
						if($rright['dashboard_col_asset']) 
						{
							echo'
								<th class="text-center '; if($_GET['order']=='asset_id') {echo 'active';} echo '" >
									<a class="text-primary-m2" title="'.T_('Équipement').'"  href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=asset_id&amp;way='.$arrow_way.'">
										<i class="fa fa-desktop text-primary-m2"><!----></i><br />
										'.T_('Équipement'); 
										//Display arrows
										if($_GET['order']=='asset_id'){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
											if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo '
									</a>
								</th>
							';
						}
						?>
						<?php if($rparameters['ticket_places']==1){ ?>
						<th class="text-center <?php if($_GET['order']=='place') echo 'active'; ?>" >
							<a class="text-primary-m2" title="<?php echo T_('Emplacement du ticket'); ?>"  href="<?php echo './index.php?page=dashboard&amp;'.$url_post_parameters; ?>&amp;order=place&amp;way=<?php echo $arrow_way; ?>">
								<i class="fa fa-globe text-primary-m2"><!----></i><br />
								<?php
								echo T_('Lieu'); 
								//Display arrows
								if($_GET['order']=='place'){
									if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
									if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
								}
								?>
							</a>
						</th>
						<?php } ?>
						<?php
						if($rright['dashboard_col_service']) 
						{
							echo'
								<th class="text-center '; if($_GET['order']=='service') {echo 'active';} echo '" >
									<a class="text-primary-m2" title="'.T_('Service').'"  href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=u_service&amp;way='.$arrow_way.'">
										<i class="fa fa-users text-primary-m2"><!----></i><br />
										'.T_('Service'); 
										//Display arrows
										if($_GET['order']=='u_service'){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
											if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo '
									</a>
								</th>
							';
						}
						if($rright['dashboard_col_agency']) 
						{
							echo'
								<th class="text-center '; if($_GET['order']=='u_agency') {echo 'active';} echo '" >
									<a class="text-primary-m2" title="'.T_('Agence').'"  href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=u_agency&amp;way='.$arrow_way.'">
										<i class="fa fa-globe text-primary-m2"><!----></i><br />
										'.T_('Agence'); 
										//Display arrows
										if($_GET['order']=='u_agency'){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
											if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo '
									</a>
								</th>
							';
						}
						?>
						<th class="text-center <?php if($_GET['order']=='title') echo 'active'; ?>" >
							<a class="text-primary-m2" title="<?php echo T_('Titre du ticket'); ?>"  href="<?php echo './index.php?page=dashboard&amp;'.$url_post_parameters; ?>&amp;order=title&amp;way=<?php echo $arrow_way; ?>">
								<i class="fa fa-file-alt text-primary-m2"><!----></i><br />
								<?php
								echo T_('Titre'); 
								//Display arrows
								if($_GET['order']=='title'){
									if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
									if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
								}
								?>
							</a>
						</th>
						<?php
							if($rright['dashboard_col_date_create'])
							{
								echo '
								<th class="text-center '; if($_GET['order']=='date_create') echo 'active'; echo'" >
									<a class="text-primary-m2" title="'.T_('Date de création du ticket').'"  href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=date_create&amp;way='.$arrow_way.'">
										<i class="fa fa-calendar text-primary-m2"><!----></i><br />
										'.T_('Date de création');
										//Display arrows
										if(preg_match("#date_create#i", "'.$_GET[order].'")){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
											if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo '
									</a>
								</th>
								';
							}
							if($rright['dashboard_col_date_hope'])
							{
								echo '
								<th class="text-center '; if($_GET['order']=='date_hope') echo 'active'; echo'" >
									<a class="text-primary-m2" title="'.T_('Date de résolution estimée du ticket').'"  href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=date_hope&amp;way='.$arrow_way.'">
										<i class="fa fa-calendar text-primary-m2"><!----></i><br />
										'.T_('Date de résolution estimée');
										//Display arrows
										if(preg_match("#date_hope#i", "'.$_GET[order].'")){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
											if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo '
									</a>
								</th>
								';
							}
							if($rright['dashboard_col_date_res'])
							{
								echo '
								<th class="text-center '; if($_GET['order']=='date_res') echo 'active'; echo'" >
									<a class="text-primary-m2" title="'.T_('Date de résolution du ticket').'"  href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=date_res&amp;way='.$arrow_way.'">
										<i class="fa fa-calendar text-primary-m2"><!----></i><br />
										'.T_('Date de résolution');
										//Display arrows
										if(preg_match("#date_res#i", "'.$_GET[order].'")){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
											if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo '
									</a>
								</th>
								';
							}
							if($rright['dashboard_col_date_modif'])
							{
								echo '
								<th class="text-center '; if($_GET['order']=='date_modif') echo 'active'; echo'" >
									<a class="text-primary-m2" title="'.T_('Date de dernière modification du ticket').'"  href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=date_modif&amp;way='.$arrow_way.'">
										<i class="fa fa-calendar text-primary-m2"><!----></i><br />
										'.T_('Date de modification');
										//Display arrows
										if(preg_match("#date_modif#i", "'.$_GET[order].'")){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
											if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo '
									</a>
								</th>
								';
							}
							//display time column
							if($rright['dashboard_col_time'])
							{
								echo '
								<th class="text-center '; if($_GET['order']=='time') echo 'active'; echo'" >
									<a class="text-primary-m2" title="'.T_('Date de résolution du ticket').'"  href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=time&amp;way='.$arrow_way.'">
										<i class="fa fa-clock text-primary-m2"><!----></i><br />
										'.T_('Temps passé');
										//Display arrows
										if(preg_match("#time#i", "'.$_GET[order].'")){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
											if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo '
									</a>
								</th>
								';
							}
							//display priority column
							if($rright['dashboard_col_priority'])
							{
								echo '
								<th class="text-center '; if($_GET['order']=='priority') echo 'active'; echo '" >
									<a class="text-primary-m2" title="'.T_('Priorité').'"  href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=priority&amp;way='.$arrow_way.'">
										<i class="fa fa-sort-amount-down-alt text-primary-m2"><!----></i><br />
										'.T_('Priorité');
										//Display arrows
										if($_GET['order']=='priority'){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
											if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo '
									</a>
								</th>
								';
							}
							//display criticality column
							if($rright['dashboard_col_criticality']) 
							{
								echo '
								<th class="text-center '; if($_GET['order']=='criticality') echo 'active'; echo '" >
									<a class="text-primary-m2" title="'.T_('Criticité').'"  href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order=criticality&amp;way='. $arrow_way.'">
										<i class="fa fa-bullhorn text-primary-m2"><!----></i><br />
										'.T_('Criticité');
										//Display arrows
										if($_GET['order']=='criticality'){
											if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
										if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
										}
										echo '
									</a>
								</th>
								';
							}
						?>
						<th class="text-center <?php if($_GET['order']=='state') echo 'active'; ?>" >
							<a class="text-primary-m2" title="<?php echo T_('État'); ?>" href="<?php echo './index.php?page=dashboard&amp;'.$url_post_parameters; ?>&amp;order=state&amp;way=<?php echo $arrow_way; ?>">
								<i class="fa fa-adjust text-primary-m2"><!----></i><br />
								<?php 
								if($_GET['view']=='activity') {echo T_('État actuel');} else {echo T_('État');}
								//Display arrows
								if(($_GET['order']=='state') || ($_GET['order']=='tstates.number, tincidents.date_hope, tincidents.priority, tincidents.criticality' && $_GET['state']=='%')){
									if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"><!----></i>';}
									if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"><!----></i>';}
								}
								?>
							</a>
						</th>
					</tr>
					<?php // *********************************** FILTER LINE ************************************** ?>
					<tr class="bgc-white text-secondary-d3 text-95">
						<td class="text-center" style="max-width:115px" >
							<input form="filter" class="form-control" name="ticket" onchange="submit();" type="text" value="<?php if($_POST['ticket']!='%') {echo $_POST['ticket'];} ?>" />
						</td>			
						<?php
							//display filter of technician column
							if($_SESSION['profile_id']!=0 || $_SESSION['profile_id']!=4 || $_GET['userid']=='%')
							{
								echo '
								<td>
									<select form="filter" style="width: 100% !important; min-width: 100%;" name="technician" id="technician" onchange="submit()" >
										<option value=""></option>
										<option value="%">&nbsp;</option>
										<option value="0">'.T_('Aucun').'</option>
										';
										//tech list
										if($join)
										{
											$query="SELECT STRAIGHT_JOIN DISTINCT tusers.id,tusers.lastname,tusers.firstname FROM tusers INNER JOIN tincidents ON tusers.id=tincidents.technician INNER JOIN tcompany ON tusers.company=tcompany.id INNER JOIN tthreads ON tincidents.id=tthreads.ticket $join WHERE $where AND (profile='0' or profile='4' or profile='3') ORDER BY tusers.lastname";
											if(preg_match('#FROM tusers#',$query)) {$query=str_replace("LEFT JOIN tusers ON tincidents.user=tusers.id","",$query);} //avoid company column pb
											if(preg_match('#INNER JOIN tcompany ON tusers.company=tcompany.id#',$query)) {$query=str_replace("INNER JOIN tcompany ON tusers.company=tcompany.id","",$query);} //avoid company column pb
											if(preg_match("#AND tusers.company='$ruser[company]'#",$query)) {$query=str_replace("AND tusers.company='$ruser[company]'","",$query);} //fix empty technician filter on company view
										}
										else
										{$query="SELECT tusers.id,tusers.lastname,tusers.firstname FROM tusers WHERE (profile='0' or profile='4') and disable='0' ORDER BY lastname";}
										if($rparameters['debug']) {echo $query;}
										$query = $db->query($query);
										while ($row = $query->fetch())
										{
											if($_POST['technician']==$row['id']) echo "<option selected value=\"$row[id]\">$row[firstname] $row[lastname]</option>"; else echo "<option value=\"$row[id]\">$row[firstname] $row[lastname]</option>";
										} 
										//tech group list
										$query = $db->query("SELECT `id`,`name` FROM tgroups WHERE disable='0' AND type='1' ORDER BY name");
										while ($row = $query->fetch())
										{
											if($t_group==$row['id'] || $_GET['t_group']==$row['id']) echo "<option selected value=\"G_$row[id]\">[G] $row[name]</option>"; else echo "<option value=\"G_$row[id]\">[G] $row[name]</option>";
										} 
									echo "
									</select>
								</td>";
							} 
							
							//display filter of user company column
							if($rright['dashboard_col_company'])
							{
								echo '
								<td>
									<select form="filter" style="width: 100% !important; max-width: 99%" id="company" name="company" onchange="submit()">
										<option value=""></option>
										<option value="%">&nbsp;</option>
										';
										//display company list
										if($join)
										{$query = $db->query("SELECT DISTINCT tcompany.id, tcompany.name FROM tcompany INNER JOIN tusers ON tusers.company=tcompany.id INNER JOIN tincidents ON tusers.id=tincidents.user INNER JOIN tthreads ON tincidents.id=tthreads.ticket WHERE $where ORDER BY tcompany.name");}
										else
										{$query = $db->query("SELECT tcompany.id, tcompany.name FROM tcompany WHERE disable='0' ORDER BY name");} //query for searchengine
										while ($row=$query->fetch()) 
										{
											if($_POST['company']==$row['id']) 
											{echo '<option selected value="'.$row['id'].'">'.strtoupper($row['name']).'</option>';}
											else
											{echo '<option value="'.$row['id'].'">'.strtoupper($row['name']).'</option>';}
										} 
										echo '
									</select>
								</td>';
							}
							//display filter of user column
							if(($_SESSION['profile_id']==0 || $_SESSION['profile_id']==3 || $_SESSION['profile_id']==4) || ($rright['side_all'] && ($_GET['userid']=='%'|| $keywords!='')) || ($rparameters['user_company_view']!=0 && $_GET['userid']=='%' && ($rright['side_company'] || $keywords!=''))) 
							{
								echo '
								<td>
									<select form="filter" style="width: 100% !important;" name="user" id="user" onchange="submit()">
										<option value=""></option>
										<option value="%">&nbsp;</option>
										<option value="0">'.T_('Aucun').'</option>';
										//display users list
										if($join)
										{
											$query="SELECT DISTINCT tusers.id,tusers.firstname,tusers.lastname FROM tusers INNER JOIN tincidents ON tusers.id=tincidents.user INNER JOIN tcompany ON tusers.company=tcompany.id INNER JOIN tthreads ON tincidents.id=tthreads.ticket $join WHERE $where ORDER BY tusers.lastname";
											if(preg_match('#FROM tusers#',$query)) {$query=str_replace("LEFT JOIN tusers ON tincidents.user=tusers.id","",$query);} //avoid company column pb
											if(preg_match('#INNER JOIN tcompany#',$query)) {$query=str_replace("LEFT JOIN tcompany ON tusers.company=tcompany.id","",$query);} //avoid company column pb
											if($rparameters['debug']) {echo $query;}
											$query = $db->query($query);
										}
										else
										{$query = $db->query("SELECT tusers.id,tusers.firstname,tusers.lastname FROM tusers WHERE disable='0' ORDER BY lastname");} //query for searchengine
										while ($row=$query->fetch()) 
										{
											if($_POST['user']==$row['id']) 
											{echo '<option selected value="'.$row['id'].'">'.$row['firstname'].' '.$row['lastname'].'</option>';}
											elseif($row['firstname']=='' && $row['lastname']=='') {}
											else
											{echo '<option value="'.$row['id'].'">'.$row['lastname'].' '.$row['firstname'].' </option>';}
										} 
										//user group list
										$query = $db->query("SELECT `id`,`name` FROM tgroups WHERE disable='0' AND type='0' ORDER BY name");
										while ($row=$query->fetch()) 
										{
											if($u_group==$row['id'] || $_GET['u_group']==$row['id']) echo "<option selected value=\"G_$row[id]\">[G] $row[name]</option>"; else echo "<option value=\"G_$row[id]\">[G] $row[name]</option>";
										} 
										echo '
									</select>
								</td>';
							}
							//display filter of user service column
							if($rright['dashboard_col_user_service']) 
							{
								echo'
								<td class="text-center" style="max-width:100px">
									<select form="filter" class="form-control" name="sender_service" id="sender_service" onchange="submit()"> 
										<option value=""></option>
										<option value="%">&nbsp;</option>
										';
											if($join)
											{
												$query="SELECT DISTINCT tservices.id,tservices.name FROM tservices INNER JOIN tincidents ON tincidents.sender_service=tservices.id $join WHERE $where AND tservices.disable='0' ORDER BY tservices.name";
												if($rparameters['debug']) {echo $query;}
												$query = $db->query($query);
											}
											else
											{$query = $db->query("SELECT tservices.id,tservices.name FROM tservices WHERE disable='0' ORDER BY name");}
											
											while ($row=$query->fetch())
											{
												if($row['id']==0) {$row['name']=T_($row['name']);} //translate only none database value
												if($_POST['sender_service']==$row['id']) 
												{echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';}
												else
												{echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';}
											} 
										echo '
									</select>
								</td>
								';
							}
							//display filter of type column
							if($rright['dashboard_col_type']) 
							{
								echo '
									<td>
										<select form="filter" class="form-control" name="type" id="type" onchange="submit()">
											<option value=""></option>
											<option value="%">&nbsp;</option>';
											//display type list
											$qry=$db->prepare("SELECT `id`,`name` FROM `ttypes` ORDER BY name");
											$qry->execute();
											while($row=$qry->fetch()) 
											{
												if($_POST['type']==$row['id']) 
												{echo '<option selected value="'.$row['id'].'">'.T_($row['name']).'</option>';}
												else
												{echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';}
											}
											$qry->closeCursor();
											echo '
										</select>
									</td>
								';	
							}
							//display filter of category column
							if($rright['dashboard_col_category']) 
							{
								echo '
								<td>
									<select form="filter" class="form-control" name="category" id="category" onchange="submit()" >
										<option value=""></option>
										<option value="%">&nbsp;</option>
										';
											if($join)
											{$query="SELECT DISTINCT tcategory.id,tcategory.name FROM tcategory INNER JOIN tincidents ON tincidents.category=tcategory.id $join WHERE $where ORDER BY tcategory.name";}
											else
											{$query="SELECT tcategory.id,tcategory.name FROM tcategory ORDER BY name";}
											if($rparameters['debug']) {echo $query;}
											$query = $db->query($query);
											while ($row=$query->fetch()) 
											{
												if($row['id']==0) {$row['name']=T_($row['name']);} //translate only none database value
												if($_POST['category']==$row['id']) 
												{echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';}
												else 
												{echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';}
											} 
										echo '
									</select>	
								</td>
								';
							}
							//display filter of subcat column
							if($rright['dashboard_col_subcat']) 
							{
								echo'
								<td >
									<select form="filter" class="form-control" name="subcat" id="subcat" onchange="submit()">
										<option value=""></option>
										<option value="%">&nbsp;</option>
										';
											if($_POST['category']!='%')
											{
												if($join)
												{$query="SELECT DISTINCT tsubcat.id,tsubcat.name FROM tsubcat INNER JOIN tincidents ON tincidents.subcat=tsubcat.id $join WHERE $where AND cat LIKE $_POST[category] ORDER BY tsubcat.name";}
												else
												{$query="SELECT tsubcat.id,tsubcat.name FROM tsubcat WHERE cat LIKE $_POST[category] ORDER BY name";}
											}
											else
											{
												if($join)
												{$query="SELECT DISTINCT tsubcat.id,tsubcat.name FROM tsubcat INNER JOIN tincidents ON tincidents.subcat=tsubcat.id $join WHERE $where AND tsubcat.name!='' ORDER BY tsubcat.name";}
												else
												{$query="SELECT tsubcat.id,tsubcat.name FROM tsubcat ORDER BY name";}
											}
											if($rparameters['debug']) {echo $query;}
											$query = $db->query($query);
											while ($row=$query->fetch())
											{
												if($row['id']==0) {$row['name']=T_($row['name']);} //translate only none database value
												if($_POST['subcat']==$row['id']) 
												{echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';}
												else
												{echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';}
											} 
										echo '
									</select>
								</td>
								';
							}
							//display filter of asset column
							if($rright['dashboard_col_asset']) 
							{
								echo '
								<td>
									<select form="filter" class="form-control" name="asset" id="asset" onchange="submit()" >
										<option value=""></option>
										<option value="%">&nbsp;</option>
										';
											if($join)
											{$query = $db->query("SELECT DISTINCT tassets.id,tassets.netbios FROM tassets INNER JOIN tincidents ON tincidents.asset_id=tassets.id $join WHERE $where AND netbios!='' ORDER BY tassets.netbios");}
											else
											{$query = $db->query("SELECT DISTINCT tassets.id,tassets.netbios FROM tassets WHERE netbios!='' ORDER BY netbios");}
											while ($row=$query->fetch()) 
											{
												if($row['id']==0) {$row['netbios']=T_($row['netbios']);} //translate only none database value
												if($_POST['asset']==$row['id']) 
												{echo '<option selected value="'.$row['id'].'">'.$row['netbios'].'</option>';}
												else 
												{echo '<option value="'.$row['id'].'">'.$row['netbios'].'</option>';}
											} 
										echo '
									</select>	
								</td>
								';
							}
						?>
						<?php if($rparameters['ticket_places']==1){ ?>
							<td>
								<select form="filter" class="form-control" name="place" id="place" onchange="submit()" >
									<option value=""></option>
									<option value="%">&nbsp;</option>
									<?php
									if($join)
									{$query = $db->query("SELECT DISTINCT tplaces.id,tplaces.name FROM tplaces INNER JOIN tincidents ON tincidents.place=tplaces.id $join WHERE $where ORDER BY tplaces.name");}
									else
									{$query = $db->query("SELECT tplaces.id,tplaces.name FROM tplaces ORDER BY name");}
									while ($row=$query->fetch()) 
									{
										if($row['id']==0) {$row['name']=T_($row['name']);} //translate only none database value
										if($_POST['place']==$row['id']) 
										{echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';}
										else
										{echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';}
									} 
									?>
								</select>	
							</td>
						<?php } ?>
						<?php
							//display filter for service column
							if($rright['dashboard_col_service']) 
							{
								echo'
								<td>
									<select form="filter" class="form-control" name="service" id="service" onchange="submit()">
										<option value=""></option>
										<option value="%">&nbsp;</option>
										';
											if($join && $_GET['order']!='tservices.name')
											{
												$query="SELECT DISTINCT tservices.id,tservices.name FROM tservices INNER JOIN tincidents ON tincidents.u_service=tservices.id $join WHERE $where AND tservices.disable='0' ORDER BY tservices.name";
												if($rparameters['debug']) {echo $query;}
												$query = $db->query($query);
											}
											else
											{$query = $db->query("SELECT tservices.id,tservices.name FROM tservices WHERE disable='0' ORDER BY name");}
											while ($row=$query->fetch())
											{
												if($row['id']==0) {$row['name']=T_($row['name']);} //translate only none database value
												if($_POST['service']==$row['id']) 
												{echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';}
												else
												{echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';}
											} 
										echo '
									</select>
								</td>
								';
							}
							//display filter of agency column
							if($rright['dashboard_col_agency']) 
							{
								echo'
								<td>
									<select form="filter" class="form-control" name="agency" id="agency" onchange="submit()">
										<option value=""></option>
										<option value="%">&nbsp;</option>
										';
											if($join)
											{$query="SELECT DISTINCT tagencies.id,tagencies.name FROM tagencies INNER JOIN tincidents ON tincidents.u_agency=tagencies.id $join WHERE $where AND tagencies.disable='0' ORDER BY tagencies.name";}
											else
											{$query="SELECT tagencies.id,tagencies.name FROM tagencies WHERE tagencies.disable='0' ORDER BY name";}
											if($rparameters['debug']) {echo $query;}
											$query = $db->query($query);
											while ($row=$query->fetch())
											{
												if($row['id']==0) {$row['name']=T_($row['name']);} //translate only none database value
												if($_POST['agency']==$row['id']) 
												{echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';}
												else
												{echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';}
											} 
										echo '
									</select>
								</td>
								';
							}
						?>
						<td>
							<input form="filter" class="form-control" name="title" style="width:100%" onchange="submit();" type="text"  value="<?php if($_POST['title']!='%')echo $_POST['title']; ?>" />
						</td>
						<?php
							//display filter of date create column
							if($rright['dashboard_col_date_create'])
							{
								if($_POST['date_create']!='%' && $_POST['date_create']  && $_POST['date_create']!='current')
								{
									//format date if detect
									if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$_POST['date_create'])) 
									{
										//convert date format to display format
										$_POST['date_create']=DateTime::createFromFormat('Y-m-d', $_POST['date_create']);
										$_POST['date_create']=$_POST['date_create']->format('d/m/Y');
									} 
								}
								echo '
								<td class="text-center" style="max-width:130px">
									<input form="filter" class="form-control" title="'.T_('La date doit être au format JJ/MM/AAAA').'" name="date_create" onchange="submit();" type="text"  value="'; if($_POST['date_create']!='%' && !$_GET['view']) {echo $_POST['date_create'];} echo '" />
								</td>
								';
							}
							//display filter of date hope column
							if($rright['dashboard_col_date_hope'])
							{
								if($_POST['date_hope']!='%' && $_POST['date_hope'])
								{
									//convert date format to display format
									if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$_POST['date_hope'])) 
									{
										$_POST['date_hope']=DateTime::createFromFormat('Y-m-d', $_POST['date_hope']);
										$_POST['date_hope']=$_POST['date_hope']->format('d/m/Y');
									}
								}
								echo '
								<td class="text-center" style="max-width:130px">
									<input form="filter" class="form-control" title="'.T_('La date doit être au format JJ/MM/AAAA').'" name="date_hope" onchange="submit();" type="text"  value="'; if($_POST['date_hope']!='%' && !$_GET['view']) {echo $_POST['date_hope'];} echo '" />
								</td>
								';
							}
							//display filter of date res column
							if($rright['dashboard_col_date_res'])
							{
								if($_POST['date_res']!='%' && $_POST['date_res'])
								{
									//convert date format to display format
									if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$_POST['date_res'])) 
									{
										$_POST['date_res']=DateTime::createFromFormat('Y-m-d', $_POST['date_res']);
										$_POST['date_res']=$_POST['date_res']->format('d/m/Y');
									}
								}
								echo '
								<td class="text-center" style="max-width:130px" >
									<input form="filter" class="form-control" title="'.T_('La date doit être au format JJ/MM/AAAA').'" name="date_res" onchange="submit();" type="text"  value="'; if($_POST['date_res']!='%' && !$_GET['view']) {echo $_POST['date_res'];} echo '" />
								</td>
								';
							}//display filter of date modif column
							if($rright['dashboard_col_date_modif'])
							{
								if($_POST['date_modif']!='%' && $_POST['date_modif'])
								{
									//convert date format to display format
									if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$_POST['date_modif'])) 
									{
										$_POST['date_modif']=DateTime::createFromFormat('Y-m-d', $_POST['date_modif']);
										$_POST['date_modif']=$_POST['date_modif']->format('d/m/Y');
									}
								}
								echo '
								<td class="text-center" style="max-width:130px">
									<input form="filter" class="form-control" title="'.T_('La date doit être au format JJ/MM/AAAA').'" name="date_modif" onchange="submit();" type="text"  value="'; if($_POST['date_modif']!='%' && !$_GET['view']) {echo $_POST['date_modif'];} echo '" />
								</td>
								';
							}
							//display filter of time column
							if($rright['dashboard_col_time'])
							{
								echo '
								<td class="text-center" style="max-width:130px">
									<input form="filter" class="form-control" title="'.T_('Le temps doit être renseigné en minutes').'" name="time" onchange="submit();" type="text" value="'; if($_POST['time']!='%' && !$_GET['view']) {echo $_POST['time'];} echo '" />
								</td>
								';
							}
							//display filter of priority column
							if($rright['dashboard_col_priority'])
							{
								echo '
								<td>
									<select form="filter" class="form-control" name="priority" id="priority" onchange="submit()">
										<option value=""></option>
										<option value="%">&nbsp;</option>
										';
										if($join)
										{$query = $db->query("SELECT DISTINCT tpriority.id,tpriority.name FROM tpriority INNER JOIN tincidents ON tincidents.priority=tpriority.id $join WHERE $where ORDER BY tpriority.number");}
										else
										{$query = $db->query("SELECT tpriority.id,tpriority.name FROM tpriority ORDER BY number");}
										while ($row=$query->fetch()){
											if($_POST['priority']==$row['id']) echo '<option selected value="'.$row['id'].'">'.T_($row['name']).'</option>'; else echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';
										} 
										echo '
									</select>
								</td>
								';
							}
							//display filter of criticality column
							if($rright['dashboard_col_criticality']) 
							{
								echo '
								<td>
									<select form="filter" class="form-control" id="criticality" name="criticality" onchange="submit()">
										<option value=""></option>
										<option value="%">&nbsp;</option>
										';
										if($join)
										{$query = $db->query("SELECT DISTINCT tcriticality.id,tcriticality.number,tcriticality.name FROM tcriticality INNER JOIN tincidents ON tincidents.criticality=tcriticality.id $join WHERE $where ORDER BY tcriticality.number");}
										else
										{$query = $db->query("SELECT `id`,`name` FROM tcriticality ORDER BY number");}
										while ($row=$query->fetch())
										{
											if($_POST['criticality']==$row['id']) echo '<option selected value="'.$row['id'].'">'.T_($row['name']).'</option>'; else echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';
										} 
									echo '
									</select>
								</td>
								';
							}
						?>
						<td>
							<select form="filter" class="form-control" name="state" id="state" onchange="submit()" >	
								<option value=""></option>
								<option value="%">&nbsp;</option>
								<?php
								if($join && $where!="tincidents.disable='0' ")
								//{$query = $db->query("SELECT DISTINCT tstates.id,tstates.number,tstates.name FROM tstates INNER JOIN tincidents ON tincidents.state=tstates.id INNER JOIN tusers ON tusers.id=tincidents.user INNER JOIN tcompany ON tusers.company=tcompany.id  INNER JOIN tthreads ON tincidents.id=tthreads.ticket WHERE $where ORDER BY tstates.number");}
								{
									$query="SELECT DISTINCT tstates.id,tstates.number,tstates.name FROM tstates INNER JOIN tincidents ON tincidents.state=tstates.id INNER JOIN tusers ON tusers.id=tincidents.user INNER JOIN tcompany ON tusers.company=tcompany.id  INNER JOIN tthreads ON tincidents.id=tthreads.ticket $join WHERE $where ORDER BY tstates.number";
									if(preg_match('#INNER JOIN tusers#',$query)) {$query=str_replace("LEFT JOIN tusers ON tincidents.user=tusers.id","",$query);} //avoid company column pb
									if(preg_match('#INNER JOIN tcompany#',$query)) {$query=str_replace("LEFT JOIN tcompany ON tusers.company=tcompany.id","",$query);} //avoid company column pb
									if(preg_match('#LEFT JOIN tstates ON tincidents.state=tstates.id#',$query)) {$query=str_replace('LEFT JOIN tstates ON tincidents.state=tstates.id','',$query);}
									if($rparameters['debug']) {echo $query;}
									$query = $db->query($query);
								}										
								else
								{
									$query = $db->query("SELECT tstates.id,tstates.number,tstates.name FROM tstates ORDER BY name");
								}
								//display each value of query
								while ($row=$query->fetch())  {
									if($_POST['state']==$row['id']) {
									echo '<option selected value="'.$row['id'].'">'.T_($row['name']).'</option>';
									} else {
									echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';
									}
								} 
								//special case meta state
								if($_GET['state']=='meta') {echo '<option selected value="meta">'.T_('A traiter').'</option>';}
								?>
							</select>
							<input name="filter" type="hidden" value="on" />
						</td>
					</tr>
				</thead>
				<tbody>
				
					<?php
					if($reload==0)
					{
						while ($row=$masterquery->fetch())
						{ 
							//select name of states
							$qry=$db->prepare("SELECT `display`,`description`,`name`,`icon` FROM `tstates` WHERE `id`=:id");
							$qry->execute(array('id' => $row['state']));
							$resultstate=$qry->fetch();
							$qry->closeCursor();
							if(empty($resultstate['display'])) {$resultstate['display']='';}
							if(empty($resultstate['description'])) {$resultstate['description']='';}
							if(empty($resultstate['name'])) {$resultstate['name']='';}
							if(empty($resultstate['icon'])) {$resultstate['icon']='';}
							if($resultstate['icon'])
							{
								$icon_state='<i class="fa '.$resultstate['icon'].'"><!-- --></i> ';
							} else {
								$icon_state='';
							}

							
							if($rright['dashboard_col_priority'])
							{
								//select name of priority
								$qry=$db->prepare("SELECT `name`,`color` FROM `tpriority` WHERE `id`=:id");
								$qry->execute(array('id' => $row['priority']));
								$resultpriority=$qry->fetch();
								$qry->closeCursor();
								if(empty($resultpriority['name'])) {$resultpriority=array(); $resultpriority['name']='';}
								if(empty($resultpriority['color'])) {$resultpriority['color']='';}
							}
							//select name of user
							$qry=$db->prepare("SELECT `id`,`phone`,`mobile`,`lastname`,`firstname` FROM `tusers` WHERE `id`=:id");
							$qry->execute(array('id' => $row['user']));
							$resultuser=$qry->fetch();
							$qry->closeCursor();
							if(empty($resultuser['id'])) {$resultuser=array(); $resultuser['id']=0;}
							if(empty($resultuser['lastname'])) {$resultuser['lastname']='';}
							if(empty($resultuser['firstname'])) {$resultuser['firstname']='';}
							if(empty($resultuser['phone'])) {$resultuser['phone']='';}
							if(empty($resultuser['mobile'])) {$resultuser['mobile']='';}
							
							//select name of user group
							$qry=$db->prepare("SELECT `id`,`name` FROM `tgroups` WHERE `id`=:id");
							$qry->execute(array('id' => $row['u_group']));
							$resultusergroup=$qry->fetch();
							$qry->closeCursor();
							if(empty($resultusergroup['id'])) {$resultusergroup['id']=0;}
							if(empty($resultusergroup['name'])) {$resultusergroup['name']='';}
							
							//select name of technician
							$qry=$db->prepare("SELECT `id`,`lastname`,`firstname` FROM `tusers` WHERE `id`=:id");
							$qry->execute(array('id' => $row['technician']));
							$resulttech=$qry->fetch();
							$qry->closeCursor();
							if(empty($resulttech['id'])) {$resulttech['id']=0;}
							if(empty($resulttech['lastname'])) {$resulttech['lastname']='';}
							if(empty($resulttech['firstname'])) {$resulttech['firstname']='';}
							
							//test if attachment exist and display in title col
							$qry=$db->prepare("SELECT `id` FROM `tattachments` WHERE `ticket_id`=:ticket_id");
							$qry->execute(array('ticket_id' => $row['id']));
							$attachment_exist=$qry->fetch();
							$qry->closeCursor();
							if(!empty($attachment_exist)) {$attachment_exist='&nbsp;<i title="'.T_('Une pièce jointe est associée à ce ticket').'" class="fa fa-paperclip text-primary-m2"><!----></i>';} else {$attachment_exist='';}

							//select name of technician group
							if($row['t_group'])
							{
								$qry=$db->prepare("SELECT `id`,`name` FROM `tgroups` WHERE `id`=:id");
								$qry->execute(array('id' => $row['t_group']));
								$resulttechgroup=$qry->fetch();
								$qry->closeCursor(); 
								if(empty($resulttechgroup['id'])) {$resulttechgroup['id']=0;}
								if(empty($resulttechgroup['name'])) {$resulttechgroup['name']='';}
							} else {$resulttechgroup['id']=0; $resulttechgroup['name']=T_('Aucun');}
							
							if($rright['dashboard_col_category'])
							{
								//select name of category
								$qry=$db->prepare("SELECT `name` FROM `tcategory` WHERE `id`=:id");
								$qry->execute(array('id' => $row['category']));
								$resultcat=$qry->fetch();
								$qry->closeCursor();
								if($row['category']==0) {$resultcat['name']=T_($resultcat['name']);}
								if(empty($resultcat['name'])) {$resultcat['name']=T_('Aucune');}
							}
							if($rright['dashboard_col_subcat'])
							{
								//select name of subcategory
								$qry=$db->prepare("SELECT `name` FROM `tsubcat` WHERE `id`=:id");
								$qry->execute(array('id' => $row['subcat']));
								$resultscat=$qry->fetch();
								$qry->closeCursor();
								if($row['subcat']==0) {$resultscat['name']=T_($resultscat['name']);}
								if(empty($resultscat['name'])) {$resultscat=array(); $resultscat['name']=T_('Aucune');}
							}
							if($rright['dashboard_col_asset'])
							{
								//select name of asset
								$qry=$db->prepare("SELECT `netbios` FROM `tassets` WHERE `id`=:id");
								$qry->execute(array('id' => $row['asset_id']));
								$resultasset=$qry->fetch();
								$qry->closeCursor();
								if(empty($resultasset['netbios'])) {$resultasset['netbios']='';}
							}
							if($rright['dashboard_col_criticality'])
							{
								//select name of criticality
								$qry=$db->prepare("SELECT `name`,`color` FROM `tcriticality` WHERE `id`=:id");
								$qry->execute(array('id' => $row['criticality']));
								$resultcriticality=$qry->fetch();
								$qry->closeCursor();
								if(empty($resultcriticality)) {$resultcriticality=array();}
								if(empty($resultcriticality['name'])) {$resultcriticality['name']='';}
								if(empty($resultcriticality['color'])) {$resultcriticality['color']='';}
							}
							if($rright['dashboard_col_type']) 
							{
								//select name of type
								$qry=$db->prepare("SELECT `name` FROM `ttypes` WHERE `id`=:id");
								$qry->execute(array('id' => $row['type']));
								$resulttype=$qry->fetch();
								$qry->closeCursor();
								if(empty($resulttype['name'])) {$resulttype['name']='';}
							}
							if($rparameters['ticket_places']) {
								//select name of place
								$qry=$db->prepare("SELECT `name` FROM `tplaces` WHERE `id`=:id");
								$qry->execute(array('id' => $row['place']));
								$resultplace=$qry->fetch();
								$qry->closeCursor();
								if(empty($resultplace['name'])) 
								{
									$nameplace=T_('Aucun');
									$fullnameplace=T_('Aucun');
								} else {
									//cut long place
									$fullnameplace=$resultplace['name'];
									//if(mb_strlen($resultplace['name'], 'UTF-8')>15){$resultplace['name']=substr($resultplace['name'],0,15).'...';}
									$nameplace = $resultplace['name'];
								}
							}
							if($rright['dashboard_col_service']) 
							{
								//select name of service
								$qry=$db->prepare("SELECT `name` FROM `tservices` WHERE `id`=:id");
								$qry->execute(array('id' => $row['u_service']));
								$resultservice=$qry->fetch();
								$qry->closeCursor();
								if(empty($resultservice['name'])) {$nameservice=T_('Aucun');} else {$nameservice = $resultservice['name'];}
							}
							if($rright['dashboard_col_user_service'])
							{
								//get user service data
								$qry=$db->prepare("SELECT `name` FROM `tservices` WHERE `id`=:id");
								$qry->execute(array('id' => $row['sender_service']));
								$resultsenderservice=$qry->fetch();
								$qry->closeCursor();
								if(empty($resultsenderservice['name'])) {$name_sender_service=T_('Aucun');} else {$name_sender_service = $resultsenderservice['name'];}
							}
							if($rright['dashboard_col_agency']) 
							{
								//select name of agency
								$qry=$db->prepare("SELECT `name` FROM `tagencies` WHERE `id`=:id");
								$qry->execute(array('id' => $row['u_agency']));
								$resultagency=$qry->fetch();
								$qry->closeCursor();
								if(empty($resultagency['name'])) {$nameagency=T_('Aucune');} else {$nameagency = $resultagency['name'];}
							}

							if($rright['dashboard_firstname'])
							{
								$Fname=$resultuser['firstname'];
								$Ftname=$resulttech['firstname'];
								
							} else {
								//cut first letter of firstname
								if(extension_loaded('mbstring')) {
									$Fname=mb_substr($resultuser['firstname'], 0, 1).'.';
									$Ftname=mb_substr($resulttech['firstname'], 0, 1).'.';
								} else {
									$Fname=substr($resultuser['firstname'], 0, 1).'.';
									$Ftname=substr($resulttech['firstname'], 0, 1).'.';
								}
							}
							if($resultuser['phone']){$resultuser['phone']=T_('Tel').' : '.$resultuser['phone'];} elseif($resultuser['mobile']) {$resultuser['phone']=T_('Tel').' : '.$resultuser['mobile'];} else {$resultuser['phone']='';}
							
							//display user name or group name
							if($resultusergroup['id'] && $resultusergroup['name']) {
								$displayusername="[G] $resultusergroup[name]";
							} else {
								if($resultuser['id']==0) {$displayusername=$resultuser['lastname'];} else {$displayusername=$Fname.' '.$resultuser['lastname'];}
							}	
							if ($resulttechgroup['id'] && $resulttechgroup['name']) {
								$displaytechname="[G] $resulttechgroup[name]";
							} else {
								if($resulttech['id']==0) {$displaytechname=$resulttech['lastname'];} else {$displaytechname=$Ftname.' '.$resulttech['lastname'];}
							}
							//display user company name
							if($rright['dashboard_col_company'])
							{
								$qry=$db->prepare("SELECT `name` FROM `tcompany` WHERE id=(SELECT `company` FROM `tusers` WHERE `id`=:user_id)");
								$qry->execute(array('user_id' => $row['user']));
								$resultusercompany=$qry->fetch();
								$qry->closeCursor();
								$displaycompanyname = $resultusercompany['name'];
							}
							//convert SQL date to human readable date
							$rowdate_hope= date_cnv($row['date_hope']);
							
							$rowdate_res_hour=date_create($row['date_res']);
							$rowdate_res_hour= date_format($rowdate_res_hour, 'H:i');
							$rowdate_res= date_cnv($row['date_res']);
						
							if($row['date_modif']=='0000-00-00 00:00:00')
							{
								$rowdate_modif='';
								$rowdate_modif_hour='';
							} else {
								$rowdate_modif=date_create($row['date_modif']);
								$rowdate_modif_hour= date_format($rowdate_modif, 'H:i');
								$rowdate_modif= date_format($rowdate_modif, 'd/m/Y H:i');
							}
							if($rright['dashboard_col_date_create_hour']) //display hour in create date column
							{
								$rowdate_create=date_create($row['date_create']);
								$rowdate_create_hour=date_format($rowdate_create, 'd/m/Y H:i');
								$rowdate_create=date_format($rowdate_create, 'd/m/Y H:i');
							} else {
								$rowdate_create=date_create($row['date_create']);
								$rowdate_create_hour=date_format($rowdate_create, 'd/m/Y H:i');
								$rowdate_create=date_format($rowdate_create, 'd/m/Y');
							}
							//date hope
							$late='';
							if($rright['ticket_date_hope_disp'])
							{
								if(!isset($row['date_hope'])) $row['date_hope']= ''; 
									
								$qry=$db->prepare("SELECT DATEDIFF(NOW(), :date_hope) ");
								$qry->execute(array('date_hope' => $row['date_hope']));
								$resultdiff=$qry->fetch();
								$qry->closeCursor();
								
								if(($resultdiff[0]>0) && ($row['state']!='3') && ($row['state']!='4')) $late = '<i title="'.$resultdiff[0].' '.T_('jours de retard').'" class="fa fa-clock fa-pulse text-warning-m2 "><!----></i>';
							}
							//billable
							$billable='';
							if($rright['ticket_billable'])
							{
								if(!empty($row['billable'])){$billable='<i title="'.T_('Ticket facturable').'" class="fa fa-dollar-sign text-success" />';}
							}
							//colorize ticket id and add tag
							$new_ticket='';
							$bgcolor='badge-primary'; //default value
							$comment='';
							if($_GET['view']=='activity') 
							{
								//ticket open in selected period
								$date_create_day=explode(' ',$row['date_create']);
								$date_create_day=$date_create_day[0];
								if($date_create_day>=$date_start_db && $date_create_day<=$date_end_db) {$new_ticket='<i title="'.T_("Ticket ouvert dans la période sélectionnée le").' '.$rowdate_create.'" class="fa fa-certificate text-success-m2 "><!----></i>';}
								//colorize ticket id in red for unread technician in selected period
								if($row['techread_date']!='0000-00-00 00:00:00') { 
									if($row['techread_date']>"$date_end_db 23:59:59"){$bgcolor="badge-danger"; $comment=T_("Ticket non lu par le technicien en charge dans la période indiquée");}
								} elseif($row['techread']==0) {
									$bgcolor="badge-danger"; $comment=T_("Ticket non lu par le technicien en charge dans la période indiquée");
								}
								//colorize ticket id in orange read by technician but no text résolution in selected period
								if($row['techread_date']!='0000-00-00 00:00:00' || $row['techread']==1) {
									$date_start=$date_start_db.' 00:00:00';
									$date_end=$date_end_db.' 23:59:59';
									$qry=$db->prepare("SELECT id FROM tthreads WHERE ticket=:ticket AND date BETWEEN :date_start AND :date_end AND type='0' AND author=:author");
									$qry->execute(array('ticket' => $row['id'],'date_start' => $date_start,'date_end' => $date_end,'author' => $row['technician']));
									$tech_add_res=$qry->rowCount();
									$qry->closeCursor();
									
									if($tech_add_res==0)
									{
										if($row['state']==3)
										{
											$bgcolor="badge-primary"; $comment=T_("Ticket avancé, qui à été fermé dans la période sélectionnée");
										} else {
											$bgcolor="badge-warning"; $comment=T_("Ticket lu par le technicien en charge mais aucun élément de réponse n'a été ajouté dans la période sélectionnée");
										}
									}
									else
									{$bgcolor="badge-primary"; $comment=T_("Ticket avancé, sur lequel un élément de résolution à été ajouté par le technicien en charge dans la période sélectionnée");}
								}
							} else {
								//ticket open today
								if(date('Y-m-d')==date('Y-m-d',strtotime($row['date_create']))) {$new_ticket='<i title="'.T_("Ticket ouvert aujourd'hui le").' '.$rowdate_create.'" class="fa fa-certificate text-success-m2 pt-1"><!----></i>';} 
								//colorize ticket id
								if($row['techread']==0 && $row['t_group']==0)  //technician not read
								{
									$bgcolor="badge-danger"; $comment=T_("Ticket non lu par le technicien en charge");
								} elseif(date('Y-m-d')==date('Y-m-d',strtotime($row['date_res']))) //today close
								{
									$bgcolor="badge-success"; $comment=T_("Ticket fermé aujourd'hui");
								} else { //technician not add res
									$qry=$db->prepare("SELECT `id` FROM `tthreads` WHERE `ticket`=:ticket AND `type`='0' AND `author`=:author");
									$qry->execute(array('ticket' => $row['id'],'author' => $row['technician']));
									$tech_add_res=$qry->rowCount();
									$qry->closeCursor();
									if(!$tech_add_res) {$bgcolor="badge-warning"; $comment=T_("Ticket lu par le technicien en charge mais aucun élément de réponse n'a été ajouté");}
								}
							}
							if(!isset($bgcolor)) {$bgcolor="badge-primary";}
							if(!isset($comment)) {$comment='Couleur par défaut des tickets';}
							
							$title='';
							foreach(explode(' ',$row['title']) as $word)
							{
								if(strlen($word)>30) {$word=substr($word,0,30).'...';} 
								$title=$title.' '.$word;
							}
							
							//display warning if date res hope is null and if it's mandatory field
							if($rright['ticket_date_hope_mandatory'] && ($row['date_hope']=='0000-00-00') && ($row['technician']!='0') && ($row['state']!='3') && ($row['state']!='4')) {$warning_hope='<i title="'.T_("La date de résolution estimée n'a pas été renseignée").'" class="fa fa-exclamation-triangle text-danger-m2 "><!----></i> ';} else {$warning_hope='';}
							//generate open ticket link
							$open_ticket_link="./index.php?page=ticket&amp;id=$row[id]&amp;$url_post_parameters&amp;order=$_GET[order]&amp;way=$_GET[way]&amp;cursor=$_GET[cursor]";
							
							// *********************************** DISPLAY EACH LINE **************************************
							echo '
								<tr class="bgc-h-default-l3 d-style">
									<td class="text-left pr-0 pos-rel">
										<div class="position-tl h-100 ml-n1px border-l-4 brc-info-m1 v-hover"></div>
										';
										//display checkbox for each line
										if($rright['task_checkbox']) {
											if($_POST['selectrow']=='selectall') {$checked='checked';} else {$checked='';}
											echo '<input form="actionlist"  class="mt-1" type="checkbox" name="checkbox'.$row['id'].'" value="'.$row['id'].'" '.$checked.' />';
										} 
										echo '
										<a href="'.$open_ticket_link.'"><span style="min-width:30px" title="'.$comment.'" class="badge '.$bgcolor.' "><span style="color:#FFF;">'.$row['id'].'</span></span></a>
										'.$new_ticket.'
										'.$late.'
										'.$warning_hope.'
										'.$billable.'
									</td>
									'; 
									//display tech
									if($_SESSION['profile_id']!=0 || $_SESSION['profile_id']!=4 || $_GET['userid']=='%') 
									{
										echo '<td class="text-center" onclick="document.location=\' '.$open_ticket_link.' \'" ><a class="td" title="'.$resulttech['firstname'].' '.$resulttech['lastname'].'" href="'.$open_ticket_link.'">'.T_(" $displaytechname").'</a></td>';
									} 
									//display user company
									if($rright['dashboard_col_company']) 
									{
										echo '<td class="text-center" onclick="document.location=\' '.$open_ticket_link.' \'"><a class="td" href="'.$open_ticket_link.'">'.T_(" $displaycompanyname").'</a></td>';
									}
									//display user
									if(($_SESSION['profile_id']==0 || $_SESSION['profile_id']==3 || $_SESSION['profile_id']==4) || ($rright['side_all'] && ($_GET['userid']=='%'|| $keywords!='')) || ($rparameters['user_company_view']!=0 && $_GET['userid']=='%' && ($rright['side_company'] || $keywords!=''))) 
									{
										echo '<td class="text-center" onclick="document.location=\' '.$open_ticket_link.' \'"><a class="td" title="'.$resultuser['firstname'].' '.$resultuser['lastname'].' '.$resultuser['phone'].'" href="'.$open_ticket_link.'">'.T_(" $displayusername").'</a></td>';
									}
									//display user service
									if($rright['dashboard_col_user_service']) {
										echo '<td class="text-center" onclick="document.location=\' '.$open_ticket_link.' \'"><a class="td" href="'.$open_ticket_link.'">'.T_($name_sender_service).'</a></td>';
									}
									//display type
									if($rright['dashboard_col_type']) {echo'<td onclick="document.location=\' '.$open_ticket_link.' \'"><a class="td" href="'.$open_ticket_link.'">'.T_($resulttype['name']).'</a></td>';}
									//display category
									if($rright['dashboard_col_category']) {echo'<td onclick="document.location=\' '.$open_ticket_link.' \'"><a class="td" href="'.$open_ticket_link.'">'.$resultcat['name'].'</a></td>';}
									//display subcat
									if($rright['dashboard_col_subcat']) {echo'<td onclick="document.location=\' '.$open_ticket_link.' \'"><a class="td" href="'.$open_ticket_link.'">'.$resultscat['name'].'</a></td>';}
									//display asset
									if($rright['dashboard_col_asset']) {echo'<td onclick="document.location=\' '.$open_ticket_link.' \'"><a class="td" href="'.$open_ticket_link.'">'.$resultasset['netbios'].'</a></td>';}
									//display place
									if($rparameters['ticket_places']){echo '<td class="text-center" title="'.$fullnameplace.'" onclick="document.location=\' '.$open_ticket_link.' \'"><a class="td" href="'.$open_ticket_link.'">'.T_($nameplace).'</a></td>';}
									//display service
									if($rright['dashboard_col_service']){echo '<td class="text-center" onclick="document.location=\' '.$open_ticket_link.' \'"><a class="td" href="'.$open_ticket_link.'">'.T_($nameservice).'</a></td>';}
									//display agency
									if($rright['dashboard_col_agency']){echo '<td class="text-center" onclick="document.location=\' '.$open_ticket_link.' \'"><a class="td" href="'.$open_ticket_link.'">'.T_($nameagency).'</a></td>';}
									//display title
									echo "<td onclick=\"document.location='$open_ticket_link'\"><a class=\"td\" title=\"$title \" href=\"$open_ticket_link\">$title</a>$attachment_exist</td>";
									//display date create
									if($rright['dashboard_col_date_create']){echo "<td class=\"text-center\" title=\"$rowdate_create_hour\" onclick=\"document.location='$open_ticket_link'\"><a class=\"td\" href=\"$open_ticket_link\">$rowdate_create</a></td>";}
									//display date hope
									if($rright['dashboard_col_date_hope']){echo "<td class=\"text-center\" onclick=\"document.location='$open_ticket_link'\"><a class=\"td\" href=\"$open_ticket_link\">$rowdate_hope</a></td>";}
									//display resolution date
									if($rright['dashboard_col_date_res']){echo "<td class=\"text-center\" title=\"$rowdate_res_hour\" onclick=\"document.location='$open_ticket_link'\"><a class=\"td\" href=\"$open_ticket_link\">$rowdate_res</a></td>";}
									//display resolution date
									if($rright['dashboard_col_date_modif']){echo "<td class=\"text-center\" title=\"$rowdate_modif_hour\" onclick=\"document.location='$open_ticket_link'\"><a class=\"td\" href=\"$open_ticket_link\">$rowdate_modif</a></td>";}
									//display time
									if($rright['dashboard_col_time']){echo '<td class="text-center" onclick="document.location=\''.$open_ticket_link.'\'"><a class="td" href="'.$open_ticket_link.'">'.MinToHour($row['time']).'</a></td>';}
									//display priority
									if($rright['dashboard_col_priority']){echo '<td class="text-center" onclick="document.location=\''.$open_ticket_link.'\'"><a title="'.T_('Priorité').' '.T_($resultpriority['name']).'" class="td" href="'.$open_ticket_link.'"> <i title="'.T_($resultpriority['name']).'" class="fa fa-exclamation-triangle" style="color:'.$resultpriority['color'].'"><!----></i></a></td>';}
									//display criticality
									if($rright['dashboard_col_criticality']){echo '<td class="text-center" onclick="document.location=\''.$open_ticket_link.'\'"><a title="'.T_('Criticité').' '.T_($resultcriticality['name']).'" class="td" href="'.$open_ticket_link.'" ><i title="'.T_($resultcriticality['name']).'" class="fa fa-bullhorn" style="color:'.$resultcriticality['color'].'" ><!----></i></a></td>';}
									//display state
									echo '<td class="text-center" onclick="document.location=\''.$open_ticket_link.'\'"><a class="td" href="'.$open_ticket_link.'"><span class="'.$resultstate['display'].'" title="'.T_($resultstate['description']).'">'.$icon_state.T_($resultstate['name']).'</span></a></td>';
									echo '
								</tr>
							';
						}
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="row">
	<?php
	//display multi check options
	if($rright['task_checkbox'] && $resultcount[0]>0)
	{
		echo '
			<i class="fa fa-level-down-alt fa-rotate-180 text-130 mb-3 ml-2 mr-2 pr-4 text-secondary-d2 "><!----></i>
			<select form="actionlist" style="width:auto" class="form-control form-control-sm mt-4" title="'.T_('Effectue des actions pour les tickets sélectionnés dans la liste des tickets').'." name="selectrow" onchange="if(confirm(\''.T_('Êtes-vous sûr de réaliser cette opération sur les tickets sélectionnés').'?\')) this.form.submit();">
				<option value="selectall"> > '.T_('Sélectionner tout').'</option>
				<option selected> > '.T_('Pour la sélection').' :</option>
				';
				if($rright['ticket_delete']){
					echo '<option value="delete">'.T_('Supprimer').'</option>';
				}
				echo '<option value="read">'.T_('Marquer comme lu').'</option>';
				//display list of ticket states
				$qry=$db->prepare("SELECT `id`,`name` FROM `tstates` ORDER BY name");
				$qry->execute();
				while($row=$qry->fetch()) 
				{
					echo '<option value="'.$row['id'].'">'.T_('Marquer comme').' "'.T_($row['name']).'"</option>';
				}
				$qry->closeCursor();
				echo '
			</select>
		';
	}
	echo '
</div> <!-- end row -->';
	//multi-pages link
	if($resultcount[0]>$rparameters['maxline'])
	{
		//count number of page
		$total_page=ceil($resultcount[0]/$rparameters['maxline']);
		echo '
		<div class="row justify-content-center mt-4">
			<nav aria-label="Page navigation">
				<ul class="pagination nav-tabs-scroll is-scrollable mb-0">';
					//display previous button if it's not the first page
					if($_GET['cursor']!=0)
					{
						$cursor=$_GET['cursor']-$rparameters['maxline'];
						echo '<li class="page-item"><a class="page-link" title="'.T_('Page précédente').'" href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor='.$cursor.'"><i class="fa fa-arrow-left"><!----></i></a></li>';
					}
					//display first page
					if($_GET['cursor']==0){$active='active';} else {$active='';}
					echo '<li class="page-item '.$active.'"><a class="page-link" title="'.T_('Première page').'" href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor=0">&nbsp;1&nbsp;</a></li>';
					//calculate current page
					$current_page=($_GET['cursor']/$rparameters['maxline'])+1;
					//calculate min and max page 
					if(($current_page-3)<3) {$min_page=2;} else {$min_page=$current_page-3;}
					if(($total_page-$current_page)>3) {$max_page=$current_page+4;} else {$max_page=$total_page;}
					//display all pages links
					for ($page = $min_page; $page <= $total_page; $page++) {
						//display start "..." page link
						if(($page==$min_page) && ($current_page>5)){echo '<li class="page-item"><a class="page-link" title="'.T_('Pages masqués').'" href="index.php?page=dashboard&amp;userid='.$_GET['userid'].'&amp;state='.$_GET['state'].'">&nbsp;...&nbsp;</a></li>';}
						//init cursor
						if($page==1) {$cursor=0;}
						$selectcursor=$rparameters['maxline']*($page-1);
						if($_GET['cursor']==$selectcursor){$active='active';} else	{$active='';}
						$cursor=(-1+$page)*$rparameters['maxline'];
						//display page link
						if($page!=$max_page) echo '<li class="page-item '.$active.'"><a class="page-link" title="'.T_('Page').' '.$page.'" href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor='.$cursor.'">&nbsp;'.$page.'&nbsp;</a></li>';
						//display end "..." page link
						if(($page==($max_page-1)) && ($page!=$total_page-1)) {
							echo '<li class="page-item"><a class="page-link" title="'.T_('Pages masqués').'" href="index.php?page=dashboard&amp;userid='.$_GET['userid'].'&amp;state='.$_GET['state'].'">&nbsp;...&nbsp;</a></li>';
						}
						//cut if there are more than 3 pages
						if($page==($current_page+4)) {
							$page=$total_page;
						} 
					}
					//display last page
					$cursor=($total_page-1)*$rparameters['maxline'];
					if($_GET['cursor']==$selectcursor){$active='active';} else	{$active='';}
					echo '<li class="page-item '.$active.'"><a class="page-link" title="'.T_('Dernière page').'" href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor='.$cursor.'">&nbsp;'.$total_page.'&nbsp;</a></li>';
					//display next button if it's not the last page
					if($_GET['cursor']<($resultcount[0]-$rparameters['maxline']))
					{
						$cursor=$_GET['cursor']+$rparameters['maxline'];
						echo '<li class="page-item"><a class="page-link" title="'.T_('Page suivante').'" href="./index.php?page=dashboard&amp;'.$url_post_parameters.'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor='.$cursor.'"><i class="fa fa-arrow-right"><!----></i></a></li>';
					}
					echo '
				</ul>
			</nav>
		</div>
	';
	if($rparameters['debug']){echo "<br /><b><u>DEBUG MODE</u></b><br />&nbsp;&nbsp;&nbsp;&nbsp;[Multi-page links] _GET[cursor]=$_GET[cursor] | current_page=$current_page | total_page=$total_page | min_page=$min_page | max_page=$max_page";}
	}
//play notify sound for tech and admin in new ticket case
if($rparameters['notify']==1 && ($_SESSION['profile_id']==4 || $_SESSION['profile_id']==0) && $_GET['keywords']=='')
{
	$query="SELECT id FROM `tincidents` WHERE technician='0' and t_group='0' and techread='0' and disable='0' and notify='0' $where_agency $where_service $parenthese2";
	if($rparameters['debug']) {echo "[Notification] $query";}
	$query=$db->query($query);
	$row=$query->fetch();
	if(empty($row)) {$row=array();} 
	if(!empty($row[0])) {
		echo'<audio hidden="false" autoplay="true" src="./sounds/notify.ogg" controls="controls"></audio>';
		$qry=$db->prepare("UPDATE `tincidents` SET `notify`='1' WHERE `id`=:id");
		$qry->execute(array('id' => $row['id']));
	}
}

//display date picker for activity view
if($_GET['view']=='activity')
{
	echo '
	<!-- datetime picker scripts  -->
	<script type="text/javascript" src="./vendor/moment/moment/min/moment.min.js"></script>
	';
	if($ruser['language']=='fr_FR') {echo '<script src="./vendor/moment/moment/locale/fr.js" charset="UTF-8"></script>';} 
	if($ruser['language']=='de_DE') {echo '<script src="./vendor/moment/moment/locale/de.js" charset="UTF-8"></script>';} 
	if($ruser['language']=='es_ES') {echo '<script src="./vendor/moment/moment/locale/es.js" charset="UTF-8"></script>';} 
	if($ruser['language']=='it_IT') {echo '<script src="./vendor/moment/moment/locale/it.js" charset="UTF-8"></script>';} 
	echo '
	<script src="./vendor/components/tempusdominus/bootstrap-4/build/js/tempusdominus-bootstrap-4.min.js" charset="UTF-8"></script> 
	<script type="text/javascript">
		var date = moment($(\'#date_start\').val(), \'DD-MM-YYYY\').toDate();
		$(\'#date_start\').datetimepicker({ format: \'DD/MM/YYYY\' });
		var date = moment($(\'#date_end\').val(), \'DD-MM-YYYY\').toDate();
		$(\'#date_end\').datetimepicker({  format: \'DD/MM/YYYY\' });
	</script>
	';
}
?>