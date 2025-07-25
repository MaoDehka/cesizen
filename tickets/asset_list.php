<?php
################################################################################
# @Name : asset_list.php 
# @Description : display assets list
# @Call : /menu.php
# @Author : Flox
# @Create : 20/11/2014
# @Update : 30/01/2024
# @Version : 3.2.47
################################################################################

//secure direct access
if(!isset($_GET['page'])) {echo 'ERROR : invalid access'; exit;}

//initialize variables 
if(!isset($asc)) $asc = ''; 
if(!isset($img)) $img= ''; 
if(!isset($filter)) $filter=''; 
if(!isset($col)) $col=''; 
if(!isset($selectcursor)) $selectcursor=''; 

//get value is for filter case
if($_POST['date_stock']=='') $_POST['date_stock']=$_GET['date_stock'];
if($_POST['location']=='') $_POST['location']=$_GET['location'];
if($_POST['description']=='') $_POST['description']=$_GET['description'];
if($_POST['sn_internal']=='') $_POST['sn_internal']=$_GET['sn_internal'];
if($_POST['netbios']=='') $_POST['netbios']=$_GET['netbios'];
if($_POST['ip']=='') $_POST['ip']=$_GET['ip'];
if($_POST['user']=='') $_POST['user']=$_GET['user'];
if($_POST['type']=='') $_POST['type']=$_GET['type'];
if($_POST['state']=='') $_POST['state']=$_GET['state'];
if($_POST['model']=='') $_POST['model']=$_GET['model'];
if($_POST['date_end_warranty']=='') $_POST['date_end_warranty']=$_GET['date_end_warranty'];
if($_POST['department']=='') $_POST['department']=$_GET['department'];
if($_POST['virtual']=='') $_POST['virtual']=$_GET['virtual'];
if($_POST['warranty_type']=='') $_POST['warranty_type']=$_GET['warranty_type'];
if($_POST['warranty_time']=='') $_POST['warranty_time']=$_GET['warranty_time'];
if($_POST['selectrow']=='') $_POST['selectrow']='';

//default values
if($_GET['cursor']=='') $_GET['cursor']='0'; 

if($_POST['sn_internal']=='') $_POST['sn_internal']='%';
if($_POST['ip']=='') $_POST['ip']='%';
if($_POST['netbios']=='') $_POST['netbios']='%';
if($_POST['user']=='') $_POST['user']='%';
if($_POST['type']=='') $_POST['type']='%';
if($_POST['state']=='') $_POST['state']='%';
if($_POST['model']=='') $_POST['model']='%';
if($_POST['location']=='') $_POST['location']='%';
if($_POST['description']=='') $_POST['description']='%';
if($_POST['sn_manufacturer']=='') $_POST['sn_manufacturer']='%';
if($_POST['date_stock']=='') $_POST['date_stock']='%';
if($_POST['date_end_warranty']=='') $_POST['date_end_warranty']='%';
if($_POST['department']=='') $_POST['department']='%';
if($_POST['virtual']=='') $_POST['virtual']='%';
if($_POST['warranty_type']=='') $_POST['warranty_type']='under_warranty';
if($_POST['warranty_time']=='') $_POST['warranty_time']='0';

if($_GET['sn_internal']=='') $_GET['sn_internal']='%'; 
if($_GET['ip']=='') $_GET['ip']='%'; 
if($_GET['netbios']=='') $_GET['netbios']='%'; 
if($_GET['user']=='') $_GET['user']='%';
if($_GET['type']=='') $_GET['type']='%';
if($_GET['state']=='') $_GET['state']='%'; 
if($_GET['model']=='') $_GET['model']='%';
if($_GET['description']=='') $_GET['description']='%';
if($_GET['date_stock']=='') $_GET['date_stock']='%';
if($_GET['date_end_warranty']=='') $_GET['date_end_warranty']='%';
if($_GET['department']=='') $_GET['department']='%';
if($_GET['location']=='') $_GET['location']='%';
if($_GET['warranty_type']=='') $_GET['warranty_type']='%';
if($_GET['warranty_time']=='') $_GET['warranty_time']='%';

//select auto order 
if($_GET['order']=='' && $_GET['state']==''){$_GET['order']='tassets_state.order';}
if($_GET['order']=='' && $_GET['state']!=1 && $_GET['warranty']!=1){$_GET['order']='tassets_iface.ip'; $_GET['way']='DESC';}
if($_GET['order']=='' && $_GET['state']==1){$_GET['order']='tassets.type,tassets.manufacturer,tassets.model,tassets.sn_internal';}
if($_GET['order']=='' && $_GET['warranty']==1){$_GET['order']='date_end_warranty'; $_GET['way']='ASC';}

//delete cursor value on select change
if($_POST['type']!=$_GET['type'] && $_GET['cursor']!=0) $_GET['cursor']='0';
if($_POST['model']!=$_GET['model'] && $_GET['cursor']!=0) $_GET['cursor']='0';

//restrict to view only asset of:
if($rright['asset_list_department_only']) { //department
	//get service from this user
	$qry=$db->prepare("SELECT `service_id` FROM `tusers_services` WHERE user_id=:user_id");
	$qry->execute(array('user_id' => $ruser['id']));
	$rservice=$qry->fetch();
	$qry->closeCursor();
	if(!empty($rservice['service_id'])) {$_POST['department']=$rservice['service_id'];} else {$_POST['department']='99999';}
	
} elseif($rright['asset_list_company_only']) { //company
	$_POST['company']=$ruser['company'];
}

//restrict to view only active state
if($rright['side_asset_all_state']==0) {$_POST['state']=2;}

if($_GET['way']=='ASC' || $_GET['way']=='DESC') {$db_way=$_GET['way'];} else {$db_way='DESC';}
//convert order in number
if($_GET['order']=='sn_internal') $_GET['order']= 'sn_internal'; //$_GET['order']= 'ABS(sn_internal)';  #5257

//select case no lastname on user filter
if($_GET['order']=='tusers.lastname') {$_GET['order']='tusers.lastname '.$db_way.', tusers.firstname';}

$db_order=strip_tags($db->quote($_GET['order']));
$db_order=str_replace("'","",$db_order);
if(is_numeric($_GET['cursor'])) {$db_cursor=$_GET['cursor'];} else {$db_cursor=0;}
$db_type=strip_tags($db->quote($_GET['type']));
$db_assetkeywords=strip_tags($db->quote($_GET['assetkeywords']));
$_POST['assetkeywords']=strip_tags($_POST['assetkeywords']);
$assetkeywords=strip_tags($assetkeywords);

if($rparameters['debug']) echo "<b><u>DEBUG MODE:</u></b> <br />
<b>CURRENT_VAR:</b> assetkeywords=$assetkeywords POST_assetkeywords=$_POST[assetkeywords] POST_type=$_POST[type] GET_type=$_GET[type] POST_model=$_POST[model] GET_model_$_GET[model] POST_state=$_POST[state] GET_state=$_GET[state] GET_cursor=$_GET[cursor] GET_order=$_GET[order] GET way: $_GET[way] POST_warranty_type: $_POST[warranty_type] POST_warranty_time: $_POST[warranty_time]<br />";

//page url to keep filters
$url_post_parameters="sn_internal=$_POST[sn_internal]&ip=$_POST[ip]&netbios=$_POST[netbios]&user=$_POST[user]&type=$_POST[type]&model=$_POST[model]&description=$_POST[description]&department=$_POST[department]&location=$_POST[location]&virtual=$_POST[virtual]&date_stock=$_POST[date_stock]&date_end_warranty=$_POST[date_end_warranty]&state=$_POST[state]&warranty=$_GET[warranty]&assetkeywords=$assetkeywords&warranty_time=$_POST[warranty_time]&warranty_type=$_POST[warranty_type]";
$url_post_parameters=preg_replace('/%/','%25',$url_post_parameters);

//load filter data to url 
if( 
	
	($_POST['sn_internal']!='%' && $_POST['sn_internal']!='' && $_GET['sn_internal']!=$_POST['sn_internal']) ||
	($_POST['ip']!='%' && $_POST['ip']!='' && $_GET['ip']!=$_POST['ip']) ||
	($_POST['netbios']!='%' && $_POST['netbios']!='' && $_GET['netbios']!=$_POST['netbios']) ||
	($_POST['user']!='%' && $_POST['user']!='' && $_GET['user']!=$_POST['user'])  ||
	($_POST['type']!='%' && $_POST['type']!='' && $_GET['type']!=$_POST['type']) ||
	($_POST['model']!='%' && $_POST['model']!='' && $_GET['model']!=$_POST['model'])||
	($_POST['description']!='%' && $_POST['description']!='' && $_GET['description']!=$_POST['description'])||
	($_POST['department']!='%' && $_POST['department']!='' && $_GET['department']!=$_POST['department'])||
	($_POST['location']!='%' && $_POST['location']!='' && $_GET['location']!=$_POST['location'])||
	($_POST['virtual']!='%' && $_POST['virtual']!='' && $_GET['virtual']!=$_POST['virtual'])||
	($_POST['date_stock']!='%' && $_POST['date_stock']!='' && $_GET['date_stock']!=$_POST['date_stock'])||
	($_POST['date_end_warranty']!='%' && $_POST['date_end_warranty']!='' && $_GET['date_end_warranty']!=$_POST['date_end_warranty'])||
	($_POST['assetkeywords']!='%' && $_POST['assetkeywords']!='' && $_GET['assetkeywords']!=$_POST['assetkeywords'])||
	($_POST['warranty_type']!='%' && $_POST['warranty_type']!='' && $_GET['warranty_type']!=$_POST['warranty_type'])||
	($_POST['warranty_time']!='%' && $_POST['warranty_time']!='' && $_GET['warranty_time']!=$_POST['warranty_time'])||
	($_POST['state']!='%' && $_POST['state']!='' && $_GET['state']!=$_POST['state'])
)
{
	$url='index.php?page=asset_list&'.$url_post_parameters;
	
	//redirect
	echo "<SCRIPT LANGUAGE='JavaScript'>
				<!--
				function redirect()
				{
				window.location='$url'
				}
				setTimeout('redirect()',0);
				-->
		</SCRIPT>";
}

//date conversion for filter line
if($_POST['date_stock']!='%')
{
	$date=$_POST['date_stock'];
	$find='/';
	$find= strpos($date, $find);
	if($find!=false)
	{			
		$date=explode("/",$date);
		$_POST['date_stock']="$date[2]-$date[1]-$date[0]";
	}
}
if($assetkeywords)
{
	include "./core/searchengine_asset.php";
} else {
	//secure string
	$_POST['description']=str_replace("'","",strip_tags($db->quote($_POST['description'])));
	$_POST['sn_internal']=str_replace("'","",strip_tags($db->quote($_POST['sn_internal'])));
	$_POST['ip']=str_replace("'","",strip_tags($db->quote($_POST['ip'])));
	$_POST['netbios']=str_replace("'","",strip_tags($db->quote($_POST['netbios'])));
	$_POST['location']=str_replace("'","",strip_tags($db->quote($_POST['location'])));
	$_POST['date_stock']=str_replace("'","",strip_tags($db->quote($_POST['date_stock'])));
	
	$from='tassets';
	$join='
	LEFT JOIN tassets_state ON tassets.state=tassets_state.id
	LEFT JOIN tusers ON tassets.user=tusers.id
	LEFT JOIN tassets_iface ON tassets.id=tassets_iface.asset_id
	';

	$where="tassets.disable='0' "; 
	if($_POST['sn_internal']!='%') {$where.="AND tassets.sn_internal LIKE '$_POST[sn_internal]%'"; }
	if($_POST['user']!='%') {$where.="AND tassets.user LIKE '$_POST[user]'";}
	if($_POST['type']!='%') {$where.="AND tassets.type LIKE '$_POST[type]'";}
	if($_POST['model']!='%') {$where.="AND	tassets.model LIKE '$_POST[model]'";}
	if($_POST['description']!='%') {$where.="AND tassets.description LIKE '%$_POST[description]%'";}
	if($_POST['date_stock']!='%') {$where.="AND	tassets.date_stock LIKE '%$_POST[date_stock]%'";}
	if($_POST['state']!='%') {$where.="AND tassets.state LIKE '$_POST[state]'";}
	if($_POST['department']!='%') {$where.="AND	tassets.department LIKE '$_POST[department]'";}
	if($rright['asset_list_col_sn_manufacturer'] && $_POST['sn_manufacturer']!='%'){$where.="AND tassets.sn_manufacturer LIKE '%$_POST[sn_manufacturer]%'";}

	//special case for asset_iface filter
	if($_POST['ip']!='%' && $_POST['ip']!='') {$where.=" AND tassets_iface.ip LIKE '%$_POST[ip]%' ";} else {$where.=" AND (tassets_iface.ip LIKE '%$_POST[ip]%' OR tassets_iface.ip is null)";}
	if($_POST['netbios']) {$where.=" AND (tassets_iface.netbios LIKE '%$_POST[netbios]%' OR tassets.netbios LIKE '%$_POST[netbios]%' )";} else {$where.=" AND (tassets_iface.netbios LIKE '%$_POST[netbios]%' OR tassets_iface.netbios is null)";}
	$where.=' AND (tassets_iface.disable=0 OR tassets_iface.disable=1 OR tassets_iface.disable is null)';
}
if($rright['asset_list_col_location']!=0 && !$assetkeywords)
{
	$join.='LEFT JOIN tassets_location ON tassets.location=tassets_location.id';
	if($_POST['location'] && $_POST['location']!='%'){$where.=" AND tassets_location.name LIKE '%$_POST[location]%' ";} else {$where.=" AND (tassets_location.name LIKE '%$_POST[location]%' OR tassets_location.name is null)";}
}
//add warranty period selection
if($rparameters['asset_warranty']==1 && $_GET['warranty']==1)
{
	if($_POST['warranty_type']=='under_warranty')
	{
		if($_POST['warranty_time']==0) {
			$where.=" AND tassets.date_end_warranty > '$today'";
		} else {
			$limit_warranty=strtotime(date("Y-m-d", strtotime($today)) . " + $_POST[warranty_time] day");
			$limit_warranty=date('Y-m-d', $limit_warranty);
			$where.=" AND tassets.date_end_warranty BETWEEN '$today' AND '$limit_warranty'";
		}
	} elseif($_POST['warranty_type']=='except_warranty') {
		if($_POST['warranty_time']==0) {
			$where.=" AND tassets.date_end_warranty < '$today' AND tassets.date_end_warranty!='0000-00-00'";
		} else {
			$limit_warranty=strtotime(date("Y-m-d", strtotime($today)) . " - $_POST[warranty_time] day");
			$limit_warranty=date('Y-m-d', $limit_warranty);
			$where.=" AND tassets.date_end_warranty BETWEEN  '$limit_warranty' AND '$today'";
		}
	}
	
}
//add company limit
if($rright['asset_list_company_only']!=0)
{
	$where.=" AND tusers.company='$ruser[company]'";
}

//planethoster URL BUG
if($db_order=='tassets_iface.ip') $_GET['order']= "INET_ATON(tassets_iface.ip) $db_way,tassets.id"; 

if($rparameters['debug'])
{
	$where_debug=str_replace("AND", "AND <br />",$where);
	$where_debug=str_replace("OR", "OR <br />",$where_debug);
	$join_debug =str_replace("LEFT", "<br />LEFT",$join);
	echo "
	<b>SELECT DISTINCT</b> 
	tassets.id,
	tassets.netbios,
	tassets.user,
	tassets.type,
	tassets.model,
	tassets.description,
	tassets.location,
	tassets.date_stock,
	tassets.state,
	tassets.department,
	tassets.date_end_warranty,
	tassets.sn_internal<br />
	<b>FROM</b>	$from
	$join_debug <br />
	<b>WHERE</b><br />
	$where_debug<br />
	<b>ORDER BY</b> $db_order $db_way <b>LIMIT</b> $db_cursor,$rparameters[maxline]<br />
	";
}

$query=$db->query("SELECT COUNT(DISTINCT tassets.id) FROM $from $join WHERE $where");
$resultcount=$query->fetch();
$query->closeCursor();

$masterquery = $db->query("
SELECT DISTINCT 
tassets.id,
tassets.sn_manufacturer,
tassets.netbios,
tassets.user,
tassets.type,
tassets.id,
tassets.model,
tassets.description,
tassets.location,
tassets.date_stock,
tassets.state,
tassets.department,
tassets.date_end_warranty,
tassets.discover_net_scan,
tassets.discover_import_csv,
tassets.sn_internal
FROM $from $join 
WHERE $where 
ORDER BY $db_order $db_way 
LIMIT $db_cursor,$rparameters[maxline]"); 


//check box selection SQL updates
if($_POST['selectrow'] && $_POST['selectrow']!='selectall')
{
	while ($row=$masterquery->fetch())
	{
		//initialize variables 
		if(!isset($_POST['checkbox'.$row["id"]])) $_POST['checkbox'.$row["id"]] = ''; 
		if($_POST['checkbox'.$row['id']]!='') 
		{
			//change state
			if($_POST['selectrow']=="delete" && $rright['asset_delete'] && $row['id'])
			{
				$qry=$db->prepare("DELETE FROM `tassets` WHERE id=:id"); //delete asset
				$qry->execute(array('id' => $row['id']));
				$qry=$db->prepare("DELETE FROM `tassets_iface` WHERE asset_id=:asset_id"); //delete associate events
				$qry->execute(array('asset_id' => $row['id']));
				$qry=$db->prepare("UPDATE `tincidents` SET `asset_id`=0 WHERE `asset_id`=:asset_id");
				$qry->execute(array('asset_id' => $row['id']));
				echo DisplayMessage('success',T_('Équipement supprimé'));
			} else {			
				$qry=$db->prepare("UPDATE `tassets` SET `state`=:state WHERE `id`=:id");
				$qry->execute(array('state' => $_POST['selectrow'],'id' => $row['id']));
				echo DisplayMessage('success',T_('Équipement modifié'));			
			}
		}
	}
	$masterquery->closeCursor();
	//redirect
	$url="./index.php?page=asset_list&state=$_GET[state]";
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


//auto launch asset if only one resultcount
if($resultcount[0]==1 && $assetkeywords!='')
{
	$findonlyone=$masterquery->fetch();
	echo 
		T_("Un seul équipement trouvé, ouverture de l'équipement en cours...").'
		<SCRIPT LANGUAGE=\'JavaScript\'>
			<!--
			function redirect()
			{
				window.location=\'./index.php?page=asset&'.$url_post_parameters.'&id='.$findonlyone['id'].'\'
			}
			setTimeout(\'redirect()\');
			-->
		</SCRIPT>
	';
}
?>

<div class="page-header position-relative">
	<h1 class="page-title text-primary-m2">
		<?php
		//display page title of asset list
		if($assetkeywords) 
		{
			$disp_assetkeywords=str_replace("'","",$db_assetkeywords);
			$disp_assetkeywords=strip_tags($disp_assetkeywords);
			echo '<i class="fa fa-search text-primary-m2"></i> '.T_("Recherche d'équipements").' : '.$disp_assetkeywords;
		}
		else
		{echo '<i class="fa fa-desktop text-primary-m2"></i> '.T_('Liste des équipements');}
		//modify title 
		if($rright['asset_list_department_only']) //for department view only
		{
			if(empty($rservice['service_id'])) {$rservice['service_id']='9999';}
			//get department name
			$qry=$db->prepare("SELECT `name` FROM `tservices` WHERE id=:id");
			$qry->execute(array('id' => $rservice['service_id']));
			$row=$qry->fetch();
			$qry->closeCursor();
			if(empty($row['name'])) {$row['name']=T_('Aucun');}
			echo T_(' du service').' '.$row['name'];
		} elseif($rright['asset_list_company_only']!=0) //for company view only
		{
			//get company name
			$qry=$db->prepare("SELECT `name` FROM `tcompany` WHERE id=:id");
			$qry->execute(array('id' => $ruser['company']));
			$row=$qry->fetch();
			$qry->closeCursor();
			
			echo T_(' de la société').' '.$row[0];
		} 
		//modify title for warranty view only
		if($rparameters['asset_warranty']==1 && $_GET['warranty']==1)
		{
			echo '
			<form style="display: inline-block;" name="warranty" id="warranty" method="post" action="" onsubmit="loadVal();" >
				<small class="page-info text-secondary-d2" >
					<select class="form-control form-control-sm d-inline-block" name="warranty_type" onchange="submit()">
						<option '; if($_POST['warranty_type']=='under_warranty') {echo ' selected ';} echo' value="under_warranty">'.T_("Sous garantie").'</option>
						<option '; if($_POST['warranty_type']=='except_warranty') {echo ' selected ';} echo' value="except_warranty">'.T_("Hors garantie").'</option>
					</select>
				</small>
			';
		} 
		//display counter
		echo '
		<small class="page-info text-secondary-d2">
			<i class="fa fa-angle-double-right"></i>
			&nbsp;'.T_('Nombre').' : '.$resultcount[0].'
		</small>
		';
		//modify title for warranty view only
		if($rparameters['asset_warranty']==1 && $_GET['warranty']==1)
		{
			echo ' |
			';
				if($_POST['warranty_type']=='under_warranty')
				{
					echo '
						<small class="page-info text-secondary-d2">
							<select class="form-control form-control-sm d-inline-block" name="warranty_time" onchange="submit()">
								<option '; if($_POST['warranty_time']=='0') {echo ' selected ';} echo ' value="0">'.T_("Garantie actuellement").'</option>
								<option '; if($_POST['warranty_time']=='31') {echo ' selected ';} echo ' value="31">'.T_("Garantie prenant fin dans les 1 mois").'</option>
								<option '; if($_POST['warranty_time']=='62') {echo ' selected ';} echo ' value="62">'.T_("Garantie prenant fin dans les 2 mois").'</option>
								<option '; if($_POST['warranty_time']=='186') {echo ' selected ';} echo ' value="186">'.T_("Garantie prenant fin dans les 6 mois").'</option>
								<option '; if($_POST['warranty_time']=='365') {echo ' selected ';} echo ' value="365">'.T_("Garantie prenant fin dans les 1 an").'</option>
								<option '; if($_POST['warranty_time']=='730') {echo ' selected ';} echo ' value="730">'.T_("Garantie prenant fin dans les 2 ans").'</option>
								<option '; if($_POST['warranty_time']=='1095') {echo ' selected ';} echo ' value="1095">'.T_("Garantie prenant fin dans les 3 ans").'</option>
							</select>
						</small>
					';
				} else 	if($_POST['warranty_type']=='except_warranty')
				{
					echo '
						<small class="page-info text-secondary-d2">
							<select name="warranty_time" onchange="submit()">
								<option '; if($_POST['warranty_time']=='0') {echo ' selected ';} echo ' value="0">'.T_("Hors garantie actuellement").'</option>
								<option '; if($_POST['warranty_time']=='31') {echo ' selected ';} echo ' value="31">'.T_("Garantie ayant pris fin il y a moins d'1 mois").'</option>
								<option '; if($_POST['warranty_time']=='62') {echo ' selected ';} echo ' value="62">'.T_("Garantie ayant pris fin il y a moins de 2 mois").'</option>
								<option '; if($_POST['warranty_time']=='186') {echo ' selected ';} echo ' value="186">'.T_("Garantie ayant pris fin il y a moins de 6 mois").'</option>
								<option '; if($_POST['warranty_time']=='365') {echo ' selected ';} echo ' value="365">'.T_("Garantie ayant pris fin il y a moins de 1 an").'</option>
								<option '; if($_POST['warranty_time']=='730') {echo ' selected ';} echo ' value="730">'.T_("Garantie ayant pris fin il y a moins de 2 ans").'</option>
								<option '; if($_POST['warranty_time']=='1095') {echo ' selected ';} echo ' value="1095">'.T_("Garantie ayant pris fin il y a moins de 3 ans").'</option>
							</select>
						</small>
					';
				}
			echo '</form>';
		} 
		//modify title for search view
		if($assetkeywords)
		{
			//if virtual asset detected display new select box filter
			$qry=$db->prepare("SELECT COUNT(id) FROM `tassets` WHERE virtualization='1' AND disable='0'");
			$qry->execute();
			$row=$qry->fetch();
			$qry->closeCursor();
			
			if($row[0]>0)
			{
				echo '|
				<form style="display: inline-block;" class="form-horizontal" name="virtual" id="virtual" method="post" action="" onsubmit="loadVal();" >
					<small class="page-info text-secondary-d2">
						'.T_('Équipements').' :
						<select class="form-control form-control-sm" onchange="submit()" name="virtual">
							<option ';if($_POST['virtual']=='%') {echo 'selected'; } echo ' value="%" >'.T_('Physiques et virtuels').'</option>
							<option ';if($_POST['virtual']=='0') {echo 'selected'; } echo ' value="0" >'.T_('Physiques').'</option>
							<option ';if($_POST['virtual']=='1') {echo 'selected'; } echo ' value="1" >'.T_('Virtuels').'</option>
						</select>
						&nbsp;&nbsp;
						<input name="assetkeywords" type="hidden" value="'.$_POST['assetkeywords'].'" />
					</small>
				</form>
				';
			}
		}
		?>
	</h1>
</div>
<?php
	//display message if search result is null
	if($resultcount[0]==0 && $assetkeywords!="") {
		echo DisplayMessage('error',T_("Aucun équipement trouvé pour la recherche").' : '.$disp_assetkeywords);
	}
?>
<div class="mt-4 mt-lg-0 card bcard h-auto overflow-hidden shadow border-0">
	<div class="table-responsive">
		<div class="col-xs-12">
			<form name="filter" id="filter" method="POST"></form>
			<form name="actionlist" id="actionlist" method="POST"></form>
			<table id="simple-table" class="table table-bordered table-bordered table-striped table-hover text-dark-m2"> 
				<?php 
				//*********************** FIRST LINE *********************** 
				if($_GET['way']=='ASC') $arrow_way='DESC'; else $arrow_way='ASC';
				//build page url link, from generate page links from searchengine_asset
				$url="./index.php?page=asset_list&amp;state=$_GET[state]&amp;ip=$_POST[ip]&amp;netbios=$_POST[netbios]&amp;user=$_GET[user]&amp;type=$_GET[type]&amp;model=$_GET[model]&amp;description=$_POST[description]&amp;department=$_GET[department]&amp;location=$_POST[location]&amp;virtual=$_POST[virtual]&amp;warranty=$_GET[warranty]&amp;date_end_warranty=$_GET[date_end_warranty]&amp;warranty_time=$_GET[warranty_time]&amp;warranty_type=$_GET[warranty_type]&amp;assetkeywords=$assetkeywords&amp;date_stock=$_POST[date_stock]";
				$url=preg_replace('/%/','%25',$url);
				echo '
				<thead>
					<tr class="bgc-white text-secondary-d3 text-95">
						<th style="text-align:center;" '; if($_GET['order']=='ABS(sn_internal)') echo 'class="active"'; echo ' >
							<a class="text-primary-m2" title="'.T_("Identifiant de l'équipement").'" href="'.$url.'&amp;order=ABS(sn_internal)&amp;way='.$arrow_way.'">
								<i class="fa fa-tag"></i><br />
								'.T_('Numéro');
								//Display way arrows
								if($_GET['order']=='ABS(sn_internal)'){
									if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"></i>';}
									if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"></i>';}
								}
								echo '
							</a>
						</th>
						';
						if($rparameters['asset_ip']==1)
						{
							echo '
							<th style="text-align:center;" '; if($_GET['order']=='ip') echo 'class="active"'; echo '>
								<a class="text-primary-m2" title="'.T_('Adresse IP').'"  href="'.$url.'&amp;order=tassets_iface.ip&amp;way='.$arrow_way.'">
									<i class="fa fa-network-wired"></i><br />
									'.T_('Adresse IP');
									//Display arrows
									if($_GET['order']=="INET_ATON(tassets_iface.ip) $db_way,tassets.id"){
										if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"></i>';}
										if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"></i>';}
									}
									echo'
								</a>
							</th>
							';
						}
						echo '
						<th style="text-align:center;" '; if($_GET['order']=='netbios') echo 'class="active"'; echo '>
							<a class="text-primary-m2" title="'.T_("Nom de l'équipement").'"  href="'.$url.'&amp;order=netbios&amp;way='.$arrow_way.'">
								<i class="fa fa-desktop"></i><br />
								'.T_('Nom');
								//display arrows
								if($_GET['order']=='netbios'){
									if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"></i>';}
									if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"></i>';}
								}
								echo '
							</a>
						</th>
						';
						?>
						<th style="text-align:center;" <?php if($_GET['order']=='tusers.lastname ASC, tusers.firstname' || 'tusers.lastname DESC, tusers.firstname') echo 'class="active"'; ?> >
							<a class="text-primary-m2" title="<?php echo T_('Utilisateur'); ?>"  href="<?php echo $url; ?>&amp;order=tusers.lastname&amp;way=<?php echo $arrow_way; ?>">
								<i class="fa fa-male"></i><br />
								<?php 
								echo T_('Utilisateur'); 
								//Display arrows
								if($_GET['order']=='tusers.lastname ASC, tusers.firstname' || 'tusers.lastname DESC, tusers.firstname'){
									if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"></i>';}
									if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"></i>';}
								}
								?>
							</a>
						</th>
						<th style="text-align:center;" <?php if($_GET['order']=='type') echo 'class="active"'; ?> >
							<a class="text-primary-m2" title="Type" href="<?php echo $url; ?>&amp;order=type&amp;way=<?php echo $arrow_way; ?>">
								<i class="fa fa-square"></i><br />
								<?php
								echo T_('Type');
								//Display arrows
								if($_GET['order']=='type'){
									if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"></i>';}
									if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"></i>';}
								}
								?>
							</a>
						</th>
						<th style="text-align:center;" <?php if($_GET['order']=='model') echo 'class="active"'; ?> >
							<a class="text-primary-m2" title="<?php echo T_('Modèle'); ?>"  href="<?php echo $url; ?>&amp;order=model&amp;way=<?php echo $arrow_way; ?>">
								<i class="fa fa-sitemap"></i><br />
								<?php
								echo T_('Modèle'); 
								//Display arrows
								if($_GET['order']=='model'){
									if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"></i>';}
									if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"></i>';}
								}
								?>
							</a>
						</th>
						<th style="text-align:center;" <?php if($_GET['order']=='description') echo 'class="active"'; ?> >
							<a class="text-primary-m2" title="<?php echo T_('Description'); ?>"  href="<?php echo $url; ?>&amp;order=description&amp;way=<?php echo $arrow_way; ?>">
								<i class="fa fa-file-alt"></i><br />
								<?php
								echo T_('Description'); 
								//Display arrows
								if($_GET['order']=='description'){
									if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"></i>';}
									if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"></i>';}
								}
								?>
							</a>
						</th>
						<th style="text-align:center;" <?php if($_GET['order']=='department') echo 'class="active"'; ?> >
							<a class="text-primary-m2" title="<?php echo T_('Service'); ?>"  href="<?php echo $url; ?>&amp;order=department&amp;way=<?php echo $arrow_way; ?>">
								<i class="fa fa-building"></i><br />
								<?php
								echo T_('Service');
								//Display arrows
								if($_GET['order']=='department'){
									if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"></i>';}
									if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"></i>';}
								}
								?>
							</a>
						</th>
						<?php
						if($rright['asset_list_col_location']!=0 && $_POST['virtual']!=1)
						{
							echo '
							<th style="text-align:center;" ';if($_GET['order']=='location') echo 'class="active"'; echo ' >
								<a class="text-primary-m2"  title="'.T_('Localisation').'" href="'.$url.'&amp;order=location&amp;way='.$arrow_way.'">
									<i class="fa fa-compass"></i><br />
									';
									echo T_('Localisation');
									//Display arrows
									if($_GET['order']=='location'){
										if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"></i>';}
										if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"></i>';}
									}
									echo '
								</a>
							</th>
							';
						}
						if($rright['asset_list_col_sn_manufacturer'])
						{
							echo '
							<th style="text-align:center;" ';if($_GET['order']=='sn_manufacturer') echo 'class="active"'; echo ' >
								<a class="text-primary-m2"  title="'.T_('Numéro de série fabricant').'" href="'.$url.'&amp;order=sn_manufacturer&amp;way='.$arrow_way.'">
									<i class="fa fa-tag"></i><br />
									';
									echo T_('Numéro de série fabricant');
									//Display arrows
									if($_GET['order']=='sn_manufacturer'){
										if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"></i>';}
										if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"></i>';}
									}
									echo '
								</a>
							</th>
							';
						}
						?>
						<th style="text-align:center;"  <?php if($_GET['order']=='date_stock') echo 'class="active"'; ?> >
							<a class="text-primary-m2"  title="<?php echo T_("Date d'achat"); ?>"  href="<?php echo $url; ?>&amp;order=date_stock&amp;way=<?php echo $arrow_way; ?>">
								<i class="fa fa-calendar"></i><br />
								<?php
								echo T_('Date achat');
								//Display arrows
								if($_GET['order']=='date_stock'){
									if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"></i>';}
									if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"></i>';}
								}
								?>
							</a>
						</th>
						<?php 
						if($rparameters['asset_warranty']==1 && $_GET['warranty']==1)
						{
							echo '
							<th style="text-align:center;"  '; if($_GET['order']=='date_end_warranty') echo 'class="active"'; echo ' >
								<a class="text-primary-m2" title="'.T_('Date de fin de garantie').'"  href="'.$url.'&amp;order=date_end_warranty&amp;way='.$arrow_way.'">
									<i class="fa fa-calendar"></i><br />
									'.T_('Date fin garantie')				
									;
									//Display arrows
									if($_GET['order']=='date_end_warranty'){
										if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"></i>';}
										if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"></i>';}
									}
									echo '
								</a>
							</th>
							';
						}
						?>
						<th style="text-align:center;" <?php if($_GET['order']=='state') echo 'class="active"'; ?> >
							<a class="text-primary-m2" title="<?php echo T_('État'); ?>" href="<?php echo $url; ?>&amp;order=state&amp;way=<?php echo $arrow_way; ?>">
								<i class="fa fa-adjust"></i><br />
								<?php
								echo T_('État');
								//Display arrows
								if($_GET['order']=='state'){
									if($_GET['way']=='ASC') {echo ' <i class="fa fa-sort-up"></i>';}
									if($_GET['way']=='DESC') {echo ' <i class="fa fa-sort-down"></i>';}
								}
								?>
							</a>
						</th>
					</tr>
					<?php // *********************************** FILTER LINE ************************************** ?>
						<tr class="bgc-white text-secondary-d3 text-95">
							<td>
								<input form="filter" class="form-control form-control-sm" name="sn_internal" style="width:100%; text-align:center; margin:0 auto;" onchange="submit();" type="text" value="<?php if($_POST['sn_internal']!='%')echo $_POST['sn_internal']; ?>" />
							</td>
							<?php
								if($rparameters['asset_ip']==1)
								{
									echo '
									<td >
										<input form="filter" class="form-control form-control-sm" name="ip" style="width:100%; text-align:center; margin:0 auto;" onchange="submit();" type="text" value="'; if($_POST['ip']!='%') {echo $_POST['ip'];} echo '" />
									</td>
									';
								}
							?>
							<td>
								<input form="filter" class="form-control form-control-sm" name="netbios" style="width:100%; text-align:center; margin:0 auto" onchange="submit();" type="text" value="<?php if($_POST['netbios']!='%')echo $_POST['netbios']; ?>" />
							</td>
							<td >
								<select form="filter" class="form-control form-control-sm" name="user" style="width:80px; text-align:center; margin:0 auto;" onchange="submit()">
									<option value="%">&nbsp;</option>
									<?php
									$query = $db->query("SELECT DISTINCT tusers.id, tusers.firstname, tusers.lastname FROM $from $join WHERE $where ORDER BY tusers.lastname,tusers.firstname");
									while ($row=$query->fetch())
									{
										if($row['id']==0 && $row['lastname']) {$row['lastname']=T_($row['lastname']);}
										if($row['id']!='')
										{
											if($_POST['user']==$row['id']) echo "<option selected value=\"$row[id]\">$row[lastname] $row[firstname]</option>"; else echo "<option value=\"$row[id]\">$row[lastname] $row[firstname]</option>";
										}
									} 
									$query->closeCursor(); 
									?>
								</select>
							</td>
							<td>
								<select form="filter" class="form-control form-control-sm" name="type" style="width:100px; text-align:center; margin:0 auto;" onchange="submit()">
									<option value="%">&nbsp;</option>
									<?php
									$query = $db->query("SELECT DISTINCT tassets_type.id, tassets_type.name FROM tassets_type,$from $join WHERE tassets_type.id=tassets.type AND $where ORDER BY tassets_type.name");
									while ($row=$query->fetch())
									{
										if($_POST['type']==$row['id']) echo "<option selected value=\"$row[id]\">$row[name]</option>"; else echo "<option value=\"$row[id]\">$row[name]</option>";
									} 
									$query->closeCursor(); 
									?>
								</select>
							</td>
							<td>
								<select form="filter" class="form-control form-control-sm" style="width:90px; text-align:center; margin:0 auto;" name="model" onchange="submit()">
									<option value="%">&nbsp;</option>
									<?php
									$query = $db->query("SELECT DISTINCT tassets_model.id,tassets_model.name FROM tassets_model, $from $join WHERE tassets_model.id=tassets.model AND tassets_model.type LIKE $db_type AND $where ORDER BY tassets_model.name");
									while ($row=$query->fetch())
									{
										if($_POST['model']==$row['id']) echo "<option selected value=\"$row[id]\">$row[name]</option>"; else echo "<option value=\"$row[id]\">$row[name]</option>";
									} 
									?>
								</select>
							</td>
							<td>
								<input form="filter" class="form-control form-control-sm" name="description" style="width:100%" onchange="submit();" type="text"  value="<?php if($_POST['description']!='%')echo $_POST['description']; ?>" />
							</td>
							<td>
								<select form="filter" class="form-control form-control-sm" style="width:45px; text-align:center; margin:0 auto;" id="department" name="department" onchange="submit()">
									<option value="%">&nbsp;</option>
									<?php
									$query = $db->query("SELECT DISTINCT tservices.id,tservices.name FROM tservices, $from $join WHERE tservices.id=tassets.department AND $where ORDER BY tservices.name");
									while ($row=$query->fetch()){
										if($_POST['department']==$row['id']) {echo "<option selected value=\"$row[id]\">$row[name]</option>";} else {echo "<option value=\"$row[id]\">$row[name]</option>";}
									} 
									$query->closeCursor();
									?>
								</select>
							</td>
							<?php 
							if($rright['asset_list_col_location']!=0 && $_POST['virtual']!=1)
							{
								echo '
								<td>
									<input form="filter" class="form-control form-control-sm" name="location" onchange="submit();" style="width:70px; text-align:center; margin:0 auto;" type="text"  value="';if($_POST['location']!='%') {echo $_POST['location'];} echo '" />
								</td>
								';
							}
							if($rright['asset_list_col_sn_manufacturer'])
							{
								echo '
								<td>
									<input form="filter" class="form-control form-control-sm" name="sn_manufacturer" onchange="submit();" style="width:auto;" type="text"  value="';if($_POST['sn_manufacturer']!='%') {echo $_POST['sn_manufacturer'];} echo '" />
								</td>
								';
							}
							?>
							<td>
								<input form="filter" class="form-control form-control-sm" name="date_stock" onchange="submit();" style="width:82px; text-align:center; margin:0 auto;" type="text"  value="<?php if($_POST['date_stock']!='%') {echo $_POST['date_stock'];} ?>" />
							</td>
							<?php 
							if($rparameters['asset_warranty']==1 && $_GET['warranty']==1){echo '<td></td>';}
							?>
							<td>
								<select form="filter" class="form-control form-control-sm" style="width:50px; text-align:center; margin:0 auto;" id="state" name="state" onchange="submit()" >	
									<option value="%">&nbsp;</option>
									<?php
										$query="SELECT DISTINCT `tassets_state`.`id`,`tassets_state`.`name` 
										FROM `tassets_state` 
										INNER JOIN `tassets` ON `tassets`.`state`=`tassets_state`.`id`
										$join WHERE $where ORDER BY `tassets_state`.`order`";
										if(preg_match('#LEFT JOIN tassets_state ON tassets.state=tassets_state.id#',$query)) {$query=str_replace("LEFT JOIN tassets_state ON tassets.state=tassets_state.id",'',$query);} //remove existing state join
										if($rparameters['debug']) {echo $query;}
										$query = $db->query($query);
										while($row=$query->fetch()) 
										{
											if($_POST['state']==$row['id']) {echo '<option selected value="'.$row['id'].'">'.T_($row['name']).'</option>';} else {echo '<option value="'.$row['id'].'">'.T_($row['name']).'</option>';}
										}
										$query->closeCursor();
									?>
								</select>
							</td>
						</tr>
				</thead>
				<tbody>
					<?php
						while ($row=$masterquery->fetch())
						{ 
							//get user name
							$qry=$db->prepare("SELECT `id`,`firstname`,`lastname` FROM `tusers` WHERE id=:id");
							$qry->execute(array('id' => $row['user']));
							$rowuser=$qry->fetch();
							$qry->closeCursor();
							if(!isset($rowuser['id'])) {$rowuser=array();}
							if(empty($rowuser['id'])) {$rowuser['id']=0;}
							if(empty($rowuser['firstname'])) {$rowuser['firstname']='';}
							if(empty($rowuser['lastname'])) {$rowuser['lastname']='';}
							
							//get type name
							$qry=$db->prepare("SELECT `id`,`name` FROM `tassets_type` WHERE id=:id");
							$qry->execute(array('id' => $row['type']));
							$rtype=$qry->fetch();
							$qry->closeCursor();
							if(empty($rtype)) {$rtype=array();}
							if(empty($rtype['id'])) {$rtype['id']=0;}
							if(empty($rtype['name'])) {$rtype['name']='';}
							
							//get model name
							$qry=$db->prepare("SELECT `name` FROM `tassets_model` WHERE id=:id");
							$qry->execute(array('id' => $row['model']));
							$rmodel=$qry->fetch();
							$qry->closeCursor();
							if(empty($rmodel)) {$rmodel=array();}
							if(empty($rmodel['name'])) {$rmodel['name']='';}
							
							//get state name
							$qry=$db->prepare("SELECT `name`,`display` FROM `tassets_state` WHERE id=:id");
							$qry->execute(array('id' => $row['state']));
							$rstate=$qry->fetch();
							$qry->closeCursor();
							if(empty($rstate)) {$rstate=array();}
							if(empty($rstate['name'])) {$rstate['name']='';}
							if(empty($rstate['display'])) {$rstate['display']='';}
							
							//get department name
							$qry=$db->prepare("SELECT `name` FROM `tservices` WHERE id=:id");
							$qry->execute(array('id' => $row['department']));
							$rdepartment=$qry->fetch();
							$qry->closeCursor();
							if(empty($rdepartment)) {$rdepartment=array();}
							if(empty($rdepartment['name'])) {$rdepartment['name']='';}
							
							if($rright['asset_list_col_location']!=0 && $_POST['virtual']!=1)
							{
								//get location name
								$qry=$db->prepare("SELECT `name` FROM `tassets_location` WHERE id=:id");
								$qry->execute(array('id' => $row['location']));
								$rlocation=$qry->fetch();
								$qry->closeCursor();
								if(empty($rlocation['name'])) {$rlocation=array(); $rlocation['name']='';}
							}

							$rowdate= date_cnv($row['date_stock']);
							$rowdateendwarranty= date_cnv($row['date_end_warranty']);
							
							//if title is too long cut
							$description=$row['description'];
							if(strlen($description)>25)
							{
								$description=mb_substr($description,0,25);
								$description=$description.'...';
							}
							
							//display asset edit link if right is ok
							if($rright['asset_list_view_only']!=0)
							{$asset_link="./index.php?page=asset_list";}
							else
							{
								$asset_link='./index.php?page=asset&amp;id='.$row['id'].'&amp;'.$url_post_parameters.'&amp;way='.$_GET['way'].'&amp;order='.$_GET['order'].'&cursor='.$_GET['cursor'];
							}	
							
							//check if asset have been discover by import csv file
							$qry=$db->prepare("SELECT `id` FROM `tassets` WHERE discover_import_csv='1'");
							$qry->execute();
							$discover_import_csv=$qry->fetch();
							$qry->closeCursor();
							if($discover_import_csv) {
								//generate network discover flag
								if($row['discover_net_scan']==1 && $row['discover_import_csv']==0)
								{
									$flag='&nbsp;<i title="'.T_("Équipement découvert par le scan réseau, mais pas dans l'import de fichier CSV").'" class="fa fa-flag text-danger"></i>';
								} else {$flag='';}
							} else {$flag='';}

							/*
							if($rparameters['asset_ip'])
							{
								//ip connectivity flag
								$qry2=$db->prepare("SELECT COUNT(id)  FROM `tassets_iface` WHERE `asset_id`=:asset_id AND `disable`='0'");
								$qry2->execute(array('asset_id' => $row['id']));
								$iface_counter=$qry2->fetch();
								$qry2->closeCursor();

								if($iface_counter['0']==1)
								{
									$qry2=$db->prepare("SELECT `date_ping_ok`,`date_ping_ko` FROM `tassets_iface` WHERE `asset_id`=:asset_id AND `disable`='0'");
									$qry2->execute(array('asset_id' => $row['id']));
									$iface=$qry2->fetch();
									$qry2->closeCursor();

									$date_ping_ok=explode(' ',$iface['date_ping_ok']);
									$date_ping_ok=$date_ping_ok[0];

									$date_ping_ko=explode(' ',$iface['date_ping_ko']);
									$date_ping_ko=$date_ping_ko[0];

									if($date_ping_ok==date('Y-m-d')) {$flag='<i title="'.T_('Ping réussi ce jour').'" class="fa fa-flag text-success"></i>';}
									if($date_ping_ko==date('Y-m-d')) {$flag='<i title="'.T_('Ping échoué ce jour').'" class="fa fa-flag text-danger"></i>';}

								}elseif($iface_counter['0']>0)
								{
									$flag='<i title="'.T_("L'une des interfaces à répondue au ping mais pas l'ensemble").'" class="fa fa-flag text-warning"></i>';

									//$flag='<i class="fa fa-flag text-success"></i>';
								} else {
									$flag='<i title="'.T_('Aucune interface réseau sur cet équipement').'" class="fa fa-flag text-grey"></i>';
								}
								
							}
							*/
							
							////////////////////////////////////////////////////////////////display each line 
							echo "
								<tr class=\"bgc-h-default-l3 d-style\">
									<td class=\"text-left pr-0 pos-rel\" style=\"vertical-align:middle;\" >
										<div class=\"position-tl h-100 ml-n1px border-l-4 brc-info-m1 v-hover\"></div>
											";
											//display checkbox for each line
											if($rright['task_checkbox']) {
												if($_POST['selectrow']=='selectall') {$checked='checked';} else {$checked='';}
												echo '<input form="actionlist" class="mt-1" type="checkbox" name="checkbox'.$row['id'].'" value="'.$row['id'].'" '.$checked.' />&nbsp;';
											} 
											echo "
											<a href=\"$asset_link\"><span class=\"badge badge-primary\">$row[sn_internal]</span></a>
											$flag
									</td>
									";
									if($rparameters['asset_ip'])
									{
										echo "
										<td style=\"vertical-align:middle;\" onclick=\"document.location='$asset_link'\" >
											<a class=\"td\" href=\"$asset_link\">
											";
											//find all ip address from iface table
											$qry2=$db->prepare("SELECT `ip` FROM `tassets_iface` WHERE asset_id=:asset_id AND ip!='' AND disable=0 ORDER BY ip ASC");
											$qry2->execute(array('asset_id' => $row['id']));
											while($row2=$qry2->fetch()) 
											{
												echo $row2['ip'].'<br />';
											}
											$qry2->closeCursor();
											echo "
											</a>
										</td>
										";
									}
									echo "
									<td style=\"vertical-align:middle;\" onclick=\"document.location='$asset_link'\">
										<a class=\"td\" href=\"$asset_link\">
											$row[netbios]
										</a>
									</td>
									<td style=\"vertical-align:middle;\" onclick=\"document.location='$asset_link'\">
										<a class=\"td\" href=\"$asset_link\">
											"; if($rowuser['lastname']) {echo T_($rowuser['lastname']);} else {echo $rowuser['lastname'];} echo " $rowuser[firstname]
										</a>
									</td>
									<td style=\"vertical-align:middle;\" onclick=\"document.location='$asset_link'\">
										<a class=\"td\" href=\"$asset_link\">
											$rtype[name]
										</a>
									</td>
									<td style=\"vertical-align:middle;\" onclick=\"document.location='$asset_link'\">
										<a class=\"td\" href=\"$asset_link\">
											$rmodel[name]
										</a>
									</td>
									<td style=\"vertical-align:middle;\" onclick=\"document.location='$asset_link'\">
										<a title=\"$row[description]\" class=\"td\" href=\"$asset_link\">
											$description
										</a>
									</td>
									<td style=\"vertical-align:middle;\" onclick=\"document.location='$asset_link'\">
										<a class=\"td\" href=\"$asset_link\" > 
											$rdepartment[name]
										</a>
									</td>
									";
									if($rright['asset_list_col_location']!=0 && $_POST['virtual']!=1)
									{
										echo '
										<td style="vertical-align:middle;" onclick="document.location=\''.$asset_link.'\'">
											<a class="td" href="'.$asset_link.'"> 
												'.$rlocation['name'].'
											</a>
										</td>
										';
									}
									if($rright['asset_list_col_sn_manufacturer'])
									{
										echo '
										<td style="vertical-align:middle;" onclick="document.location=\''.$asset_link.'\'">
											<a class="td" href="'.$asset_link.'"> 
												'.$row['sn_manufacturer'].'
											</a>
										</td>
										';
									}
									echo "
									<td style=\"vertical-align:middle; text-align:center;\" onclick=\"document.location='$asset_link'\">
										<a class=\"td\" href=\"$asset_link\">
											$rowdate
										</a>
									</td>
									";
									if($rparameters['asset_warranty'] && $_GET['warranty']==1)
									{
											echo "
										<td style=\"vertical-align:middle; text-align:center;\" onclick=\"document.location='$asset_link'\">
											<a class=\"td\" href=\"$asset_link\">
												$rowdateendwarranty
											</a>
										</td>
										";
									}
									echo '
									<td class="text-center" onclick="document.location=\''.$asset_link.'\'">
										<a class="td" href="'.$asset_link.'"> 
											<span class="'.$rstate['display'].'">
												'.T_($rstate['name']).'
											</span>
										</a>
									</td>
								</tr>
							';
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
			<i class="fa fa-level-down-alt fa-rotate-180 text-130 mb-3 ml-2 pr-4 text-secondary-d2 mr-2 ml-1"></i>
			<select form="actionlist" style="width:auto" class="form-control form-control-sm mt-4" title="'.T_('Effectue des actions pour les équipements sélectionnés').'." name="selectrow" onchange="if(confirm(\''.T_('Êtes-vous sûr de réaliser cette opération sur les équipements sélectionnés').'?\')) this.form.submit();">
				<option value="selectall"> > '.T_('Sélectionner tout').'</option>
				<option selected> > '.T_('Pour la sélection').' :</option>
				';
				if($rright['asset_delete']){
					echo '<option value="delete">'.T_('Supprimer').'</option>';
				}
				//display list of ticket states
				$qry=$db->prepare("SELECT `id`,`name` FROM `tassets_state` ORDER BY name");
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
	?>
</div>

<?php
//multi-pages link
if  ($resultcount[0]>$rparameters['maxline'])
{
	//count number of page
	$total_page=ceil($resultcount[0]/$rparameters['maxline']);
	echo '
	<div class="row justify-content-center mt-4">
		<nav aria-label="Page navigation">
			<ul class="pagination nav-tabs-scroll is-scrollable">';
				//display previous button if it's not the first page
				if($_GET['cursor']!=0)
				{
					$cursor=$_GET['cursor']-$rparameters['maxline'];
					echo '<li class="page-item"><a class="page-link" title="'.T_('Page précédente').'" href="'.$url.'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor='.$cursor.'"><i class="fa fa-arrow-left"></i></a></li>';
				}
				//display first page
				if($_GET['cursor']==0){$active='active';} else	{$active='';}
				echo '<li class="page-item '.$active.'"><a class="page-link" title="'.T_('Première page').'" href="'.$url.'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor=0">&nbsp;1&nbsp;</a></li>';
				//calculate current page
				$current_page=($_GET['cursor']/$rparameters['maxline'])+1;
				//calculate min and max page 
				if(($current_page-3)<3) {$min_page=2;} else {$min_page=$current_page-3;}
				if(($total_page-$current_page)>3) {$max_page=$current_page+4;} else {$max_page=$total_page;}
				//display all pages links
				for ($page = $min_page; $page <= $total_page; $page++) {
					//display start "..." page link
					if(($page==$min_page) && ($current_page>5)){echo '<li class="page-item"><a class="page-link" title="'.T_('Pages masqués').'" href="">&nbsp;...&nbsp;</a></li>';}
					//init cursor
					if($page==1) {$cursor=0;}
					$selectcursor=$rparameters['maxline']*($page-1);
					if($_GET['cursor']==$selectcursor){$active='active';} else	{$active='';}
					$cursor=(-1+$page)*$rparameters['maxline'];
					//display page link
					if($page!=$max_page) echo '<li class="page-item '.$active.'"><a class="page-link" title="'.T_('Page').' '.$page.'" href="'.$url.'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor='.$cursor.'">&nbsp;'.$page.'&nbsp;</a></li>';
					//display end "..." page link
					if(($page==($max_page-1)) && ($page!=$total_page-1)) {
						echo '<li class="page-item"><a class="page-link" title="'.T_('Pages masqués').'" href="">&nbsp;...&nbsp;</a></li>';
					}
					//cut if there are more than 3 pages
					if($page==($current_page+4)) {
						$page=$total_page;
					} 
				}
				//display last page
				$cursor=($total_page-1)*$rparameters['maxline'];
				if($_GET['cursor']==$selectcursor){$active='active';} else	{$active='';}
				echo '<li class="page-item '.$active.'"><a class="page-link" title="'.T_('Dernière page').'" href="'.$url.'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor='.$cursor.'">&nbsp;'.$total_page.'&nbsp;</a></li>';
				//display next button if it's not the last page
				if($_GET['cursor']<($resultcount[0]-$rparameters['maxline']))
				{
					$cursor=$_GET['cursor']+$rparameters['maxline'];
					echo '<li class="page-item"><a class="page-link" title="'.T_('Page suivante').'" href="'.$url.'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor='.$cursor.'"><i class="fa fa-arrow-right"></i></a></li>';
				}
				echo '
			</ul>
		</nav>
	</div>
';
if($rparameters['debug']){echo "<br /><b><u>DEBUG MODE</u></b> [Multi-page links] _GET[cursor]=$_GET[cursor] | current_page=$current_page | total_page=$total_page | min_page=$min_page | max_page=$max_page";}
}
?>