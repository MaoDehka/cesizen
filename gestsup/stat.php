<?php
################################################################################
# @Name : stat.php
# @Description : Display Statistics
# @call : /menu.php
# @parameters : 
# @Author : Flox
# @Create : 12/01/2011
# @Update : 19/12/2023
# @Version : 3.2.46
################################################################################

//prevent direct access
if(!isset($_SESSION['user_id'])) {header('HTTP/1.0 403 Forbidden'); exit;}

//initialize variables 
if(!isset($select)) $select = '';
if(!isset($libgraph)) $libgraph = '';
if(!isset($selected)) $selected= '';
if(!isset($selected1)) $selected1= '';
if(!isset($selected2)) $selected2= '';
if(!isset($find)) $find= '';
if(!isset($subcat)) $subcat= '%';
if(!isset($category)) $category= '';
if(!isset($result)) $result= '';
if(!isset($monthm)) $monthm= '';
if(!isset($container)) $container= '';
if(!isset($_POST['tech'])) $_POST['tech']='';
if(!isset($_POST['type'])) $_POST['type']='';
if(!isset($_POST['criticality'])) $_POST['criticality']='';
if(!isset($_POST['category'])) $_POST['category']='';
if(!isset($_POST['subcat'])) $_POST['subcat']= '';
if(!isset($_POST['year'])) $_POST['year'] = '';
if(!isset($_POST['month'])) $_POST['month'] = '';
if(!isset($_POST['category'])) $_POST['category'] = '';
if(!isset($_POST['service'])) $_POST['service'] = '';
if(!isset($_POST['company'])) $_POST['company'] = '';
if(!isset($_POST['agency'])) $_POST['agency'] = '';
if(!isset($_POST['model'])) $_POST['model'] = '';
if(!isset($_POST['netbios'])) $_POST['netbios'] = '';
if(!isset($_POST['state'])) $_POST['state'] = '';
$where_tech_group='';

//default values 
if($_POST['tech']=="") $_POST['tech']="%";
if($_POST['criticality']=="") $_POST['criticality']="%";
if($_POST['year']=="") $_POST['year']=date('Y');
if($_POST['month']=="") $_POST['month']=date('m');
if($_POST['type']=="") $_POST['type']='%';
if($_POST['category']=="") $_POST['category']='%';
if($_POST['service']=="") $_POST['service']='%';
if($_POST['company']=="") $_POST['company']='%';
if($_POST['agency']=="") $_POST['agency']='%';
if($_POST['model']=="") $_POST['model']='%';
if($_POST['netbios']=="") $_POST['netbios']='%';
if($_POST['state']=="") $_POST['state']='%';
if($_GET['tab']=="") $_GET['tab']='ticket';
if($ruser['skin']=='skin-4') {$bgc='#29343b';} else {$bgc='transparent';}

//secure posting string
if(!is_numeric($_POST['criticality'])) {$_POST['criticality']='%';}
if(!is_numeric($_POST['year'])) {$_POST['year']='%';}
if(!is_numeric($_POST['month'])) {$_POST['month']='%';}
if(!is_numeric($_POST['type'])) {$_POST['type']='%';}
if(!is_numeric($_POST['category'])) {$_POST['category']='%';}
if(!is_numeric($_POST['service'])) {$_POST['service']='%';}
if(!is_numeric($_POST['company'])) {$_POST['company']='%';}
if(!is_numeric($_POST['agency'])) {$_POST['agency']='%';}
if(!is_numeric($_POST['model'])) {$_POST['model']='%';}
if(!is_numeric($_POST['state']) && $_POST['state']!='meta') {$_POST['state']='%';}

//generate stat token access
if($rright['stat'])
{
	$token = bin2hex(random_bytes(32));
	$qry=$db->prepare("INSERT INTO `ttoken` (`date`,`token`,`action`,`user_id`,`ip`) VALUES (NOW(),:token,'stat_access',:user_id,:ip)");
	$qry->execute(array('token' => $token,'user_id' => $_SESSION['user_id'],'ip' => $_SERVER['REMOTE_ADDR']));
}

//count company from company list to display company filter or not
$qry=$db->prepare("SELECT COUNT(id) FROM `tcompany` WHERE disable='0'");
$qry->execute();
$company_cnt=$qry->fetch();
$qry->closeCursor();
if($company_cnt[0]>1 && $rparameters['user_advanced']==1) {
	$company_filter=1;
	
	if($_POST['company'])
	{
		$where_company='go';
	} else {
		$where_company='';
	}
} else {$company_filter=0; $where_company='';}

//case user have agency and services limits
if($rparameters['user_agency'] && $rright['dashboard_agency_only'] && $rright['dashboard_agency_only'] && ($cnt_agency || $cnt_service))
{
	if($_POST['agency']!='%') //agency filter line
	{
		$where_agency="AND tincidents.u_agency LIKE '$_POST[agency]'";
		$where_service='';
	} else if($_POST['service']!='%') //service filter line
	{
		$where_service="AND tincidents.u_service LIKE '$_POST[service]'";
		$where_agency='';
	} else { //limit with user agencies and services
		$where_service=$where_agency.$where_service;
		$where_agency='';
		//special case with 1 service and 1 agency
		if($cnt_service==1 && $cnt_agency>0){$where_service=str_replace('AND tincidents.u_service','OR tincidents.u_service',$where_service);}
	}
} elseif($rparameters['user_agency']) //add filter with no agency limit
	{$where_agency="AND tincidents.u_agency LIKE '$_POST[agency]'";
} else {
	$where_agency='';
} 

//month & day table 
$month = array();
$month = array("01" => T_('Janvier'), "02"=> T_('Février'), "03"=> T_('Mars'), "04"=> T_('Avril'), "05"=> T_('Mai'), "06"=> T_('Juin'), "07"=> T_('Juillet'), "08"=> T_('Août'), "09"=> T_('Septembre'), "10"=> T_('Octobre'), "11"=> T_('Novembre'), "12"=> T_('Décembre'));
$day= array();
$day = array(1 => "1", 2=> "2", 3=> "3", 4=> "4", 5=> "5", 6=> "6", 7=> "7", 8=> "8", 9=> "9", 10=> "10", 11=> "11", 12=> "12", 13=> "13", 14=> "14", 15=> "15", 16=> "16", 17=> "17", 18=> "18", 19=> "19", 20=> "20", 21=> "21", 22=> "22", 23=> "23", 24=> "24", 25=> "25", 26=> "26", 27=> "27", 28=> "28", 29=> "29", 30=> "30", 31=> "31");

//call highcharts scripts
echo'
<script src="vendor/components/Highcharts/highcharts.js"></script>
<script src="vendor/components/Highcharts/modules/exporting.js"></script>
<script src="vendor/components/Highcharts/modules/accessibility.js"></script>
';

if(($rparameters['user_limit_service']==1 && $cnt_service!=0) || $rright['stat'])
{
	echo '
	<div class="page-header position-relative">
		<h1 class="page-title text-primary-m2 ml-3">
			<i class="fa fa-chart-line"><!----></i>  '.T_('Statistiques').'
		</h1>
		<div class="page-tools mr-3">
			';
			if($_GET['tab']=='asset')
			{
				//replace % char in URL
				$url_post_parameters='./core/export_assets.php?token='.$token.'&amp;user_id='.$_SESSION['user_id'].'&amp;technician='.$_POST['tech'].'&amp;service='.$_POST['service'].'&amp;type='.$_POST['type'].'&amp;model='.$_POST['model'].'&amp;netbios='.$_POST['netbios'].'&amp;criticality='.$_POST['criticality'].'&amp;category='.$_POST['category'].'&amp;month='.$_POST['month'].'&amp;year='.$_POST['year'].'&amp;company='.$_POST['company'];
				$url_post_parameters=preg_replace('/%/','%25',$url_post_parameters);
				echo'
					<a title="'.T_("Télécharge un fichier au format CSV avec l'ensemble des équipements").'" target="_blank" href="'.$url_post_parameters.'">
						<button class="btn btn-xs btn-purple">
							<i class="fa fa-download"><!----></i>
							'.T_('Export CSV').'
						</button>
					</a>
				';
			} else {
				//replace % char in URL
				$url_post_parameters='./core/export_tickets.php?token='.$token.'&amp;user_id='.$_SESSION['user_id'].'&amp;technician='.$_POST['tech'].'&amp;service='.$_POST['service'].'&amp;agency='.$_POST['agency'].'&amp;type='.$_POST['type'].'&amp;criticality='.$_POST['criticality'].'&amp;category='.$_POST['category'].'&amp;state='.$_POST['state'].'&amp;month='.$_POST['month'].'&amp;year='.$_POST['year'].'&amp;userid='.$_SESSION['user_id'].'&amp;company='.$_POST['company'];
				$url_post_parameters=preg_replace('/%/','%25',$url_post_parameters);
				echo'
					<a title="'.T_("Télécharge un fichier au format CSV, avec les tickets sélectionnés dans le filtre").'" target="_blank" href="'.$url_post_parameters.'">
						<button  class="btn btn-xs btn-purple">
							<i class="fa fa-download"><!----></i>
							'.T_('Export CSV').'
						</button>
					</a>
				';
			}
			echo '
		</div>
	</div>
	<div class="">	
		<div class="col-12 tabs-above">
			<ul class="nav nav-tabs nav-justified" role="tablist">
				<li class="nav-item mr-1px" >
					<a class="nav-link text-left radius-0 '; if($_GET['tab']=='ticket') echo 'active'; echo '" href="./index.php?page=stat&amp;tab=ticket">
						<i class="fa fa-ticket text-success-m1 mr-3px"><!----></i>
						'.T_('Tickets').'
					</a>
				</li>
				';
				//if asset function is enable
				if($rparameters['asset'])
				{
					echo '
					<li class="nav-item mr-1px">
						<a class="nav-link text-left radius-0 '; if($_GET['tab']=='asset') echo 'active'; echo '" href="./index.php?page=stat&amp;tab=asset">
							<i class="fa fa-desktop text-warning-m1 mr-3px"><!----></i>
							'.T_('Équipements').'
						</a>
					</li>
					';
				}
				echo '
			</ul>
			<div style="background-color:#FFF;" class="tab-content rounded-bottom">
				<div id="ticket" class="tab-pane '; if($_GET['tab']=='ticket') echo 'active'; echo ' ">
					'; include('./ticket_stat.php'); echo '
				</div>
				<div id="asset" class="tab-pane '; if($_GET['tab']=='asset') echo 'active'; echo ' ">
					'; include('./asset_stat.php'); echo '
				</div>
			</div>
		</div>
	</div>
	';
} else {
	echo '<div class="alert alert-danger"><strong><i class="fa fa-remove"><!----></i>'.T_('Erreur').':</strong> '.T_("Vous devez posséder au moins un service associé pour afficher cette page, contactez votre administrateur").'.<br></div>';
}
?>