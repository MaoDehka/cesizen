<?php
################################################################################
# @Name : main.php 
# @Description : ui for connected user
# @Call : /index.php
# @Author : Flox
# @Create : 26/12/2019
# @Update : 26/03/2024
# @Version : 3.2.50 p1
################################################################################

//secure direct access
if(!isset($_GET['page'])) {echo 'ERROR : invalid access'; exit;}

//init var
if(!isset($_SESSION['sidebar_collapsed'])) {$_SESSION['sidebar_collapsed']='';}
if(!isset($_GET['sidebar_collapsed'])) {$_GET['sidebar_collapsed']='disable';}

//sidebar collapsed
if($_GET['sidebar_collapsed']=='enable')
{
	$_SESSION['sidebar_collapsed']='collapsed';
	$button_collapsed='disable';
}elseif($_GET['sidebar_collapsed']=='disable')
{
	$_SESSION['sidebar_collapsed']='';
	$button_collapsed='enable';
} else {
	if($_SESSION['sidebar_collapsed']) {$button_collapsed='disable';} else {$button_collapsed='enable';}
}

//check install directory for admin access
if(is_dir('install') && $rparameters['server_private_key']!='5a237a1a7c078b09e819f7e3a5825065'){
	echo DisplayMessage('error',T_('Le répertoire "install" doit être supprimé de votre serveur pour utiliser le logiciel'));
	exit;
}

//skin generation
if($ruser['skin']=='skin-8') {$navbar='navbar-orange'; $sidebar='sidebar-plum'; $background_color="";} //orange and purple
if($ruser['skin']=='skin-7') {$navbar='navbar-green'; $sidebar='sidebar-gradient2'; $background_color="";} //green and purple
if($ruser['skin']=='skin-6') {$navbar='navbar-orange'; $sidebar='sidebar-cadetblue'; $background_color="";} //orange and green
if($ruser['skin']=='skin-5') {$navbar='navbar-cadetblue'; $sidebar='sidebar-darkslategrey'; $background_color="";} //green
if($ruser['skin']=='skin-4') {$navbar='navbar-dark'; $sidebar='sidebar-dark'; $background_color="background-color:#343a40;";} //dark
if($ruser['skin']=='skin-3') {$navbar='navbar-slategrey'; $sidebar='sidebar-slategrey'; $background_color="";} //grey
if($ruser['skin']=='skin-2') {$navbar='navbar-plum'; $sidebar='sidebar-plum'; $background_color="";} //purple
if($ruser['skin']=='skin-1') {$navbar='navbar-dark'; $sidebar='sidebar-dark'; $background_color="";} //black
if(!$ruser['skin']) {$navbar='navbar-skyblue'; $sidebar='sidebar-darkblue'; $background_color="";}
echo '
<div class="body-container" >
	<nav class="navbar navbar-expand-lg navbar-fixed '.$navbar.'">
		<div class="navbar-inner">
			<div class="navbar-intro justify-content-xl-between">
				<button type="button" class="btn btn-burger burger-arrowed static collapsed ml-2 d-flex d-xl-none" data-toggle-mobile="sidebar" data-target="#sidebar" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle sidebar">
					<span class="bars"><!----></span>
				</button><!-- mobile sidebar toggler button -->
				<a class="navbar-brand text-white" target="_blank" title="'.T_('Ouvre un nouvel onglet sur le site de GestSup').'" href="https://gestsup.fr">
					<img title="'.T_('Ouvre un nouvel onglet sur le site de GestSup').'" width="34" src="images/logo_gestsup_white.svg" />
					<!-- <i class="fa fa-ticket text-80" style="ms-transform:rotate(45deg); webkit-transform:rotate(45deg); transform:rotate(45deg);"> -->
					'; 
					if($mobile) 
					{
						if(isset($rparameters['company'])) {
							//test string lenght to prevent default on mobile access
							if(strlen($rparameters['company'])>10) {echo substr($rparameters['company'],0,10).'...';} else {echo $rparameters['company'];}								}
						}
					else
					{echo '<span>GestSup</span>&nbsp;<span style="font-size:10px;" >'.$rparameters['version'].'</span>';} 
					echo '
				</a><!-- /.navbar-brand -->
				<a href="index.php?page='.$_GET['page'].'&amp;userid='.$_GET['userid'].'&amp;state='.$_GET['state'].'&amp;id='.$_GET['id'].'&amp;action='.$_GET['action'].'&amp;sidebar_collapsed='.$button_collapsed.'">
					<button type="button" class="btn btn-burger mr-2 d-none d-xl-flex" data-toggle="sidebar" data-target="#sidebar" aria-controls="sidebar" aria-expanded="true" aria-label="Toggle sidebar">
						<span class="bars"><!----></span>
					</button><!-- sidebar toggler button -->
				</a>
			</div><!-- /.navbar-intro -->
			<div class="navbar-content">
				';
				if(!$mobile)
				{
					if($rparameters['logo'] && file_exists("./upload/logo/$rparameters[logo]"))
					{
						//re-size logo if height superior 40px
						$height = getimagesize("./upload/logo/$rparameters[logo]");
						if(!empty($height))
						{
							$height=$height[1];
							if($height>40) {$logo_size='height="40"';} else {$logo_size='';}
						} else {$logo_size='';}
						echo '<img class="ml-3" style="display:inline; border-style: none" '.$logo_size.' alt="logo" src="./upload/logo/'.$rparameters['logo'].'" />';
					} else {
						echo '<span style="color: White;"><i class="fa fa-dice-d6 text-200 ml-3 mr-1"><!----></i></span>';
					}
					echo '
					<a class="navbar-brand text-white ml-2" href="#">
						'; if(isset($rparameters['company'])) echo $rparameters['company']; echo '
					</a>
					';
				}
				echo '
			</div><!-- .navbar-content -->
			<!-- mobile #navbarMenu toggler button -->
			';
			//get profile image
			$qry=$db->prepare("SELECT `img` FROM `tprofiles` WHERE level=:level");
			$qry->execute(array('level' => $_SESSION['profile_id']));
			$rprofile_img=$qry->fetch();
			$qry->closeCursor();
			echo '
			<button class="navbar-toggler ml-1 mr-2 px-1" type="button" data-toggle="collapse" data-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navbar menu">
				<span class="pos-rel">
					<img alt="avatar" class="border-2 brc-white-tp1 radius-round" width="36" src="images/avatar/'.$rprofile_img['img'].'" />
				</span>
			</button>
			<div class="navbar-menu collapse navbar-collapse navbar-backdrop" id="navbarMenu">
				<div class="navbar-nav">
					<ul class="nav">
						';
						require('userbar.php');
						$qry=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
						$qry->execute(array('id' => $_SESSION['user_id']));
						$current_user=$qry->fetch();
						$qry->closeCursor();
						$qry=$db->prepare("SELECT `img` FROM `tprofiles` WHERE level=:level");
						$qry->execute(array('level' => $_SESSION['profile_id']));
						$rprofile_img=$qry->fetch();
						$qry->closeCursor();
						echo '
						<li class="nav-item dropdown order-first order-lg-last">
							<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">          
								<img id="id-navbar-user-image" class="d-none d-lg-inline-block radius-round border-2 brc-white-tp1 mr-2" src="images/avatar/'.$rprofile_img['img'].'" alt="'.$current_user['firstname'].' '.$current_user['lastname'].'">
								<span class="d-inline-block d-lg-none d-xl-inline-block">
									<span class="text-90" id="id-user-welcome">'.T_('Bienvenue').',</span>
									<span class="nav-user-name">'.$current_user['firstname'].' '.$current_user['lastname'].'</span>
								</span>
								<i class="caret fa fa-angle-down d-none d-xl-block"><!----></i>
								<i class="caret fa fa-angle-left d-block d-lg-none"><!----></i>
							</a>
							<div class="dropdown-menu dropdown-caret dropdown-menu-right dropdown-animated brc-primary-m3">
								<a class="dropdown-item btn btn-outline-grey btn-h-lighter-primary btn-a-lighter-primary" href="index.php?page=admin/user&amp;action=edit&amp;userid='.$_SESSION['user_id'].'&amp;tab=infos">
									<i class="fa fa-user text-primary-m2 text-105 mr-1"><!----></i>
									'.T_('Profil').'
								</a>
								<div class="dropdown-divider brc-primary-l2"></div>
								<a class="dropdown-item btn btn-outline-grey btn-h-lighter-secondary btn-a-lighter-secondary" target="about_blank" href="https://doc.gestsup.fr">
									<i class="fa fa-book text-dark-d1 text-105 mr-1"><!----></i>
									'.T_('Documentation').'
								</a>
								<div class="dropdown-divider brc-primary-l2"></div>
								<a class="dropdown-item btn btn-outline-grey btn-h-lighter-secondary btn-a-lighter-secondary" href="index.php?action=logout&amp;token='.$_SESSION['auth_logout_token'].'">
									<i class="fa fa-power-off text-warning-d1 text-105 mr-1"><!----></i>
									'.T_('Déconnexion').'
								</a>
							</div>
						</li><!-- /.nav-item:last -->
					</ul><!-- /.navbar-nav menu -->
				</div><!-- /.navbar-nav -->
			</div><!-- /.navbar-menu.navbar-collapse -->
		</div><!-- /.navbar-inner -->
	</nav>
	<div class="main-container " style="'.$background_color.'">
		<div id="sidebar" class="overflow-auto sidebar sidebar-fixed expandable sidebar-color '.$sidebar.' '.$_SESSION['sidebar_collapsed'].'">
			';
			require('menu.php');
			echo '
		</div>
		<!-- /#sidebar -->
		<div role="main" class="w-100 overflow-auto main-content">
			<div class="pt-2 page-content">
				<div class="pb-1">
					';
					//previous page on ticket page
					if($_GET['page']=='ticket' ) {
						//init var
						if(!isset($_GET['order'])) $_GET['order'] = '';
						if(!isset($_GET['user'])) $_GET['user'] = '';
						if(!isset($_GET['sender_service'])) $_GET['sender_service'] = '';
						if(!isset($_GET['company'])) $_GET['company'] = '';
						if(!isset($_GET['companyview'])) $_GET['companyview'] = '';
						if(!isset($_GET['service'])) $_GET['service'] = '';
						if(!isset($_GET['agency'])) $_GET['agency'] = '';
						if(!isset($_GET['asset'])) $_GET['asset'] = '';
						//url using in cancel button from ticket page and back arrow on ticket
						$url_get_parameters='state='.$_GET['state'].'&amp;userid='.$_GET['userid'].'&amp;technician='.$_GET['technician'].'&amp;user='.$_GET['user'].'&amp;techgroup='.$_GET['techgroup'].'&amp;sender_service='.$_GET['sender_service'].'&amp;category='.$_GET['category'].'&amp;subcat='.$_GET['subcat'].'&amp;asset='.$_GET['asset'].'&amp;title='.$_GET['title'].'&amp;date_create='.$_GET['date_create'].'&amp;priority='.$_GET['priority'].'&amp;criticality='.$_GET['criticality'].'&amp;viewid='.$_GET['viewid'].'&amp;type='.$_GET['type'].'&amp;place='.$_GET['place'].'&amp;service='.$_GET['service'].'&amp;agency='.$_GET['agency'].'&amp;company='.$_GET['company'].'&amp;view='.$_GET['view'].'&amp;date_range='.$_GET['date_range'].'&amp;date_start='.$_GET['date_start'].'&amp;date_end='.$_GET['date_end'].'&amp;keywords='.$_GET['keywords'].'&amp;companyview='.$_GET['companyview'].'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor='.$_GET['cursor'].'';
						$url_get_parameters=preg_replace('/%/','%25',$url_get_parameters);
						echo '<a title="'.T_('Retour à la liste').'" href="./index.php?page=dashboard&amp;'.$url_get_parameters.'" ><i class="pr-2 pl-1 fa fa-arrow-circle-left  text-primary-m2"><!----></i></a>';
					}
					//previous page on asset page
					if($_GET['page']=='asset' ) {
						//init var
						if(!isset($_GET['date_end_warranty'])) $_GET['date_end_warranty'] = '';
						if(!isset($_GET['sn_internal'])) $_GET['sn_internal'] = '';
						if(!isset($_GET['order'])) $_GET['order'] = '';
						if(!isset($_GET['ip'])) $_GET['ip'] = '';
						if(!isset($_GET['netbios'])) $_GET['netbios'] = '';
						if(!isset($_GET['user'])) $_GET['user'] = '';
						if(!isset($_GET['model'])) $_GET['model'] = '';
						if(!isset($_GET['description'])) $_GET['description'] = '';
						if(!isset($_GET['department'])) $_GET['department'] = '';
						if(!isset($_GET['date_stock'])) $_GET['date_stock'] = '';
						if(!isset($_GET['virtual'])) $_GET['virtual'] = '';
						if(!isset($_GET['warranty_type'])) $_GET['warranty_type'] = '';
						if(!isset($_GET['warranty_time'])) $_GET['warranty_time'] = '';
						//url using to keep data from filter and sort of asset_list on asset page
						$url_get_parameters='sn_internal='.$_GET['sn_internal'].'&amp;ip='.$_GET['ip'].'&amp;netbios='.$_GET['netbios'].'&amp;user='.$_GET['user'].'&amp;type='.$_GET['type'].'&amp;model='.$_GET['model'].'&amp;description='.$_GET['description'].'&amp;department='.$_GET['department'].'&amp;date_stock='.$_GET['date_stock'].'&amp;date_end_warranty='.$_GET['date_end_warranty'].'&amp;assetkeywords='.$_GET['assetkeywords'].'&amp;virtual='.$_GET['virtual'].'&amp;state='.$_GET['state'].'&amp;warranty='.$_GET['warranty'].'&amp;warranty_type='.$_GET['warranty_type'].'&amp;warranty_time='.$_GET['warranty_time'].'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor='.$_GET['cursor'];
						$url_get_parameters=preg_replace('/%/','%25',$url_get_parameters);
						echo '<a title="'.T_('Retour à la liste').'" href="./index.php?page=asset_list&amp;'.$url_get_parameters.'" ><i class="pr-2 pl-1 fa fa-arrow-circle-left  text-primary-m2"><!----></i></a>';
					}
					//breadcrumb first level
					echo '<a href="./index.php?page=dashboard&amp;userid='.$_SESSION['user_id'].'&amp;state=%25"><i class="fa fa-home text-primary-m2"><!----></i></a>';
					if(($_GET['page']=='dashboard' || $_GET['page']=='ticket' || $_GET['page']=='preview_mail' ) && $_GET['viewid']=='') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="./index.php?page=dashboard&amp;userid='.$_SESSION['user_id'].'&amp;state=%25"><i class="fa fa-ticket text-primary-m2"><!----></i></a>&nbsp;';
					if($_GET['page']=='procedure') echo '<i class="pr-2 pl-2 pl-2 fa fa-angle-right "><!----></i><a href="./index.php?page=procedure"><i class="fa fa-book text-primary-m2"><!----></i></a>';
					if($_GET['page']=='project') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="./index.php?page=project"><i class="fa fa-tasks text-primary-m2"><!----></i></a>';
					if($_GET['page']=='contract') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="./index.php?page=contract"><i class="fa fa-file-contract text-primary-m2"><!----></i></a>';
					if($_GET['page']=='calendar') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="./index.php?page=calendar"><i class="fa fa-calendar text-primary-m2"><!----></i></a>';
					if($_GET['page']=='stat') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="./index.php?page=stat&amp;tab=ticket"><i class="fa fa-chart-line text-primary-m2"><!----></i></a>';
					if($_GET['page']=='admin/user' && $_GET['action']=='edit') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=admin/user&amp;action=edit&amp;userid='.$_GET['userid'].'"><i class="fa fa-user text-primary-m2"><!----></i></a>';
					if($_GET['page']=='admin' || $_GET['page']=='changelog') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="./index.php?page=admin"><i class="fa fa-cogs text-primary-m2"><!----></i></a>';
					if($_GET['viewid']!='' || $_GET['page']=='view') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=dashboard"><i class="fa fa-eye text-primary-m2"><!----></i></a>';
					if($_GET['page']=='asset' || $_GET['page']=='asset_list') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=asset_list"><i class="fa fa-desktop text-primary-m2"><!----></i></a>';
					if($_GET['page']=='asset_stock') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=asset_stock"><i class="fa fa-desktop text-primary-m2"><!----></i></a>';
					//include plugin menu
					$section='breadcrumb';
					include('plugin.php');
					//breadcrumb second level
					if($_GET['subpage']=='parameters' ) echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=admin&amp;subpage=parameters"><i class="fa fa-cog text-primary-m2"><!----></i></a> ';
					if($_GET['subpage']=='user' ) echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=admin&amp;subpage=user"><i class="fa fa-user text-primary-m2"><!----></i></a> ';
					if($_GET['subpage']=='group' ) echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=admin&amp;subpage=group"><i class="fa fa-users text-primary-m2"><!----></i></a> ';
					if($_GET['subpage']=='profile' ) echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=admin&amp;subpage=profile"><i class="fa fa-lock text-primary-m2"><!----></i></a> ';
					if($_GET['subpage']=='list' ) echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=admin&amp;subpage=list"><i class="fa fa-list text-primary-m2"><!----></i></a> ';
					if($_GET['subpage']=='backup' ) echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=admin&amp;subpage=backup"><i class="fa fa-save text-primary-m2"><!----></i></a> ';
					if($_GET['subpage']=='update' ) echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=admin&amp;subpage=update"><i class="fa fa-cloud-upload-alt text-primary-m2"><!----></i></a> ';
					if($_GET['subpage']=='system' ) echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=admin&amp;subpage=system"><i class="fa fa-desktop text-primary-m2"><!----></i></a> ';
					if($_GET['subpage']=='log' ) echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=admin&amp;subpage=log"><i class="fa fa-clipboard-list text-primary-m2"><!----></i></a> ';
					if($_GET['subpage']=='infos' || $_GET['page']=='changelog' ) echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=admin&amp;subpage=infos"><i class="fa fa-info-circle text-primary-m2"><!----></i></a> ';
					if(($_GET['page']=='ticket' || $_GET['page']=='preview_mail') && $_GET['action']=='new') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=ticket&amp;action=new&amp;userid=1"><i class="fa fa-plus text-primary-m2"><!----></i></a> ';
					if(($_GET['page']=='ticket' || $_GET['page']=='preview_mail') && $_GET['action']!='new') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=ticket&amp;id='.$_GET['id'].'&amp;userid='.$_GET['userid'].'&amp;state='.$_GET['state'].'&amp;category='.$_GET['category'].'&amp;subcat='.$_GET['subcat'].'&amp;viewid='.$_GET['viewid'].'"><i class="fa fa-pencil-alt text-primary-m2"><!----></i></a> ';
					if($_GET['page']=='asset' && $_GET['action']!='new') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=asset&amp;id='.$_GET['id'].'"><i class="fa fa-pencil-alt text-primary-m2"><!----></i></a> ';
					if($_GET['page']=='asset' && $_GET['action']=='new') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=asset&amp;action=new"><i class="fa fa-plus text-primary-m2"><!----></i></a> ';
					//breadcrumb third level
					if($_GET['page']=='admin' && $_GET['subpage']=='user' && $_GET['ldap']==1) echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=admin&amp;subpage=user&amp;ldap=1"><i class="fa fa-sync text-primary-m2"><!----></i></a> ';
					if($_GET['page']=='preview_mail') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=preview_mail&amp;id='.$_GET['id'].'&amp;userid='.$_GET['userid'].'&amp;state='.$_GET['state'].'&amp;category='.$_GET['category'].'&amp;subcat='.$_GET['subcat'].'&amp;viewid='.$_GET['viewid'].'"><i class="fa fa-envelope text-primary-m2"><!----></i></a> ';
					if($_GET['page']=='changelog') echo '<i class="pr-2 pl-2 fa fa-angle-right "><!----></i><a href="index.php?page=changelog"><i class="fa fa-file text-primary-m2"><!----><!----></i></a> ';
					echo '

				</div>
				';
				
				//company information message
				if($rparameters['company_message'])
				{
					//check company message
					$qry=$db->prepare("SELECT `information_message` FROM `tcompany` WHERE `id`=:id");
					$qry->execute(array('id' => $ruser['company']));
					$information_message=$qry->fetch();
					$qry->closeCursor();
					if($information_message['information_message'])
					{
						echo '
							<div class="alert bgc-info-l4 border-none border-l-4 brc-info-tp1 radius-0 text-dark-tp2 mt-4">
								<h5 class="alert-heading text-info-m1 font-bolder">
									<i class="fas fa-info-circle mr-1 mb-1"></i>
									'.T_('Information').'
								</h5>
								'.nl2br($information_message['information_message']).'
							</div>
						';
					}
				}

				//security check own ticket right
				$msg_error='';
				if(($_GET['page']=='ticket') && ($_GET['action']!='new')) 
				{
					$qry=$db->prepare("SELECT `user` FROM `tincidents` WHERE id=:id");
					$qry->execute(array('id' => $_GET['id']));
					$rticket=$qry->fetch();
					$qry->closeCursor();
				} else $rticket[0]=$_SESSION['user_id'];
				
				//ACL security check for page
				if(
					(
						($_SESSION['profile_id']!=4 && $_SESSION['profile_id']!=0 && $_SESSION['profile_id']!=3) //user profil
						&&
						($_SESSION['user_id']!=$_GET['userid'] && $_GET['userid']!='')  
					) || (
						($_SESSION['profile_id']!=4 && $_SESSION['profile_id']!=0 && $_SESSION['profile_id']!=3) 
						&&
						($rticket[0]!=$_SESSION['user_id']) 
					)
				)
				{
					//check if ticket is deleted
					if($_GET['page']=='ticket' && $_GET['id'])
					{
						$qry=$db->prepare("SELECT `disable` FROM `tincidents` WHERE id=:id");
						$qry->execute(array('id' => $_GET['id']));
						$check_ticket_disable=$qry->fetch();
						$qry->closeCursor();
						if(!isset($check_ticket_disable['disable'])) {$check_ticket_disable=array(); $check_ticket_disable['disable']='';}
						$check_ticket_disable=$check_ticket_disable['disable'];
						
					} else {$check_ticket_disable=0;}
					if($check_ticket_disable==1) {$msg_error=T_("ce ticket a été supprimé");}
					//allow display pages from asset function
					elseif($_GET['page']=='asset_list' && $rright['asset']!=0 && $rparameters['asset']==1) {include("$_GET[page].php");}
					//allow display pages from template function
					elseif($_GET['page']=='ticket' && $rright['ticket_template']!=0 && $_GET['action']=='template') {include("$_GET[page].php");}
					//allow open new ticket
					elseif($_GET['page']=='ticket' && $_GET['action']=='new' && $rright['side_open_ticket']!=0) {include("$_GET[page].php");}
					//allow display all tickets for user with display all service, if rights are enable
					elseif($_GET['page']=='dashboard' && $rright['side_all_service_disp']) {include("$_GET[page].php");}
					//allow display all tickets for user with display all agency, if rights are enable
					elseif($_GET['page']=='dashboard' && $rright['side_all_agency_disp']) {include("$_GET[page].php");}
					//allow display all tickets for user with have right to view all
					elseif($_GET['page']=='dashboard' && $rright['side_all'] && !$rparameters['user_company_view'] && !$rparameters['user_agency']&& !$rparameters['user_limit_service']) {include("$_GET[page].php");}
					//allow modify ticket for user with same service service, if rights are enable (cnt_agency for case user have service and agency to allow edit)
					elseif($_GET['page']=='ticket' && $_GET['action']!='new' && $rright['side_all_service_edit']!=0 && $cnt_service!=0 && $user_services) {
						//check if open ticket is associated to the same service as the current user services
						$qry=$db->prepare("SELECT `u_service` FROM `tincidents` WHERE id=:id");
						$qry->execute(array('id' => $_GET['id']));
						$check_ticket_service=$qry->fetch();
						$qry->closeCursor();
						$service_check=0;
						foreach($user_services as $value) {
							if($check_ticket_service[0]==$value){$service_check=1;}
						}
						if($service_check) {include("$_GET[page].php");}
						else {
							//check if current user is sender
							$qry=$db->prepare("SELECT `user` FROM `tincidents` WHERE id=:id");
							$qry->execute(array('id' => $_GET['id']));
							$check_ticket_user_sender=$qry->fetch();
							$qry->closeCursor();
							if($check_ticket_user_sender[0]!=$_SESSION['user_id'])
							{
								//check if open ticket is associated to the same service as the current user service
								$qry=$db->prepare("SELECT `u_service` FROM `tincidents` WHERE id=:id");
								$qry->execute(array('id' => $_GET['id']));
								$check_ticket_service=$qry->fetch();
								$qry->closeCursor();
								$service_check=0;
								foreach($user_services as $value) {
									if($check_ticket_service[0]==$value){$service_check=1;}
								}
								if($service_check==1) {include("$_GET[page].php");}
								else {
									//case agency and service on same user
									if($rright['side_all_agency_edit'] && $cnt_agency) {
										//check if open ticket is associated to the same agency as the current user agencies
										$qry=$db->prepare("SELECT `u_agency` FROM `tincidents` WHERE id=:id");
										$qry->execute(array('id' => $_GET['id']));
										$check_ticket_agency=$qry->fetch();
										$qry->closeCursor();
										$agency_check=0;
										foreach($user_agencies as $value) {if($check_ticket_agency[0]==$value){$agency_check=1;}}
										if($agency_check==1) {include("$_GET[page].php");}
										else {$msg_error=T_("Vous n'avez pas les droits d'accès pour modifier le ticket de cette agence, contactez votre administrateur");}
									} else {
										$msg_error=T_("Vous n'avez pas les droits d'accès pour modifier le ticket de ce service, contactez votre administrateur");
									}
								}
							} else {
								include("$_GET[page].php");
							}
						}
					}
					//allow modify ticket for user with same agency, if rights are enable
					elseif(($_GET['page']=='ticket' || $_GET['page']=='preview_mail') && $rright['side_all_agency_edit']!=0 && $cnt_agency!=0 ) {
						//check if open ticket is associated to the same agency as the current user agencies
						$qry=$db->prepare("SELECT `u_agency` FROM `tincidents` WHERE id=:id");
						$qry->execute(array('id' => $_GET['id']));
						$check_ticket_agency=$qry->fetch();
						$qry->closeCursor();
						$agency_check=0;
						foreach($user_agencies as $value) {if($check_ticket_agency[0]==$value){$agency_check=1;}}
						if($agency_check==1) {include("$_GET[page].php");}
						else {$msg_error=T_("Vous n'avez pas les droits d'accès pour modifier le ticket de cette agence, contactez votre administrateur");}
					}
					//allow display pages to company view
					elseif(($_GET['companyview']==1 || $_GET['page']=='preview_mail') && $rparameters['user_company_view']==1 && $rright['side_company']!=0 && $ruser['company'])	
					{
						if(($_GET['page']=='ticket' || $_GET['page']=='preview_mail') && $_GET['id'] && $_GET['action']!='template')
						{
							//check if ticket is the same company than connected user
							$qry=$db->prepare("SELECT `user` FROM `tincidents` WHERE id=:id");
							$qry->execute(array('id' => $_GET['id']));
							$check_ticket_company=$qry->fetch();
							$qry->closeCursor();
							$qry=$db->prepare("SELECT `company` FROM `tusers` WHERE id=:id");
							$qry->execute(array('id' => $check_ticket_company['user']));
							$check_ticket_company=$qry->fetch();
							$qry->closeCursor();
							if(($check_ticket_company['company']==$ruser['company']) && ($ruser['company']!=0))
							{
								include("$_GET[page].php");
							} else {
								$msg_error=T_("Vous n'avez pas les droits de consulter ce ticket, contactez votre administrateur (ERREUR 1)");
							}
						} elseif($_GET['page']=='dashboard' || $_GET['action']=='template') {include("$_GET[page].php");}
					}
					//allow display pages to observer view
					elseif($_GET['view']=='observer' && $rparameters['ticket_observer'] && $rright['side_your_observer'] && $_GET['page']=='ticket' && $_GET['id'])	
					{
						//check if current user is observer of this ticket
						$qry=$db->prepare("SELECT `id` FROM `tincidents` WHERE (observer1=:current_user OR observer2=:current_user OR observer3=:current_user) AND id=:id");
						$qry->execute(array('current_user' => $_SESSION['user_id'],'id' => $_GET['id']));
						$check_ticket_observer=$qry->fetch();
						if(!empty($check_ticket_observer))
						{
							$ticket_observer=1;
							include("$_GET[page].php");
						} else {
							$msg_error=T_("Vous n'avez pas les droits de consulter ce ticket, contactez votre administrateur (ERREUR 2)");
						}
					}
					//allow user display all ticket without service agency access
					elseif($rright['side_all'] && !$rright['dashboard_service_only']&& !$rright['dashboard_agency_only'] && ($_GET['page']=='dashboard' || $_GET['page']=='ticket'))	
					{
						include("$_GET[page].php");
					}
					else {$msg_error=T_("Vous n'avez pas les droits d'accès à cette page, contactez votre administrateur (ERREUR 1)");}
				} else	{
					//check rights page before display
					if($_GET['page']=='ticket' && $_GET['id']) //check if ticket is deleted
					{
						$qry=$db->prepare("SELECT `disable`,`technician` FROM `tincidents` WHERE id=:id");
						$qry->execute(array('id' => $_GET['id']));
						$check_ticket=$qry->fetch();
						$qry->closeCursor();
						if(!empty($check_ticket))
						{
							$deleted_ticket=0;
							//case tech group to display other ticket of his group
							if($_SESSION['profile_id']==0)
							{
								$qry=$db->prepare("SELECT `tgroups_assoc`.`id` FROM `tgroups_assoc`,`tincidents` WHERE `tincidents`.`t_group`=`tgroups_assoc`.`group` AND `tgroups_assoc`.`user`=:technician AND `tincidents`.`id`=:id");
								$qry->execute(array('technician' => $_SESSION['user_id'],'id' => $_GET['id']));
								$check_t_group=$qry->fetch();
								$qry->closeCursor();
								if(empty($check_t_group)) {$check_t_group=array();}
								if(empty($check_t_group['id'])) {$check_t_group['id']=0;}
							} else {$check_t_group['id']=0; }
						} else {
							$deleted_ticket=1;
						}
					} else {$deleted_ticket=0; $check_ticket['technician']=0; $check_t_group['id']=0;}
					if($deleted_ticket) {$msg_error=T_("Ce ticket a été supprimé");}
					elseif($_GET['page']=='ticket' && $_GET['id'] && isset($check_t_group['id'])==0 && isset($check_ticket['technician'])!=$_SESSION['user_id'] && $rright['side_all']==0 && $_SESSION['profile_id']==0) {$msg_error=T_("Vous n'avez pas les droits d'accès au ticket d'un autre technicien, contactez votre administrateur");}
					elseif($_GET['page']=='procedure' && $rright['procedure']==0) {$msg_error=T_("Vous n'avez pas les droits d'accès aux procédures, contactez votre administrateur");}
					elseif($_GET['page']=='ticket_template' && $rright['ticket_template']==0) {$msg_error=T_("Vous n'avez pas les droits d'accès aux modèles de tickets, contactez votre administrateur");}
					elseif($_GET['page']=='preview_mail' && $rright['ticket_send_mail']==0) {$msg_error=T_("Vous n'avez pas les droits d'accès à la prévisualisation des mails, contactez votre administrateur");}
					elseif($_GET['page']=='asset_list' && $rright['asset']==0) {$msg_error=T_("Vous n'avez pas les droits d'accès aux équipements, contactez votre administrateur");}
					elseif($_GET['page']=='asset' && $rright['asset']==0) {$msg_error=T_("Vous n'avez pas les droits d'accès aux équipements, contactez votre administrateur");}
					elseif($_GET['page']=='asset' && $rright['asset_list_view_only']!=0) {$msg_error=T_("Vous n'avez pas les droits d'accès à la fiche de cet équipement, contactez votre administrateur");}
					elseif($_GET['page']=='asset_stock' && $rright['asset']==0) {$msg_error=T_("Vous n'avez pas les droits d'accès aux équipements, contactez votre administrateur");}
					elseif($_GET['page']=='calendar' && $rright['planning']==0) {$msg_error=T_("Vous n'avez pas les droits d'accès aux calendriers, contactez votre administrateur");}
					elseif($_GET['page']=='admin/user' && $rright['admin']==0 && $_GET['userid']!=$_SESSION['user_id']) {$msg_error=T_("Vous n'avez pas le droit de modifier un autre utilisateur, contactez votre administrateur");}
					elseif(preg_match( '/^admin.*/', $_GET['page']) && $_GET['page']!='admin/user' && !$rright['admin'] && !$rright['admin_lists'] && !$rright['admin_groups']) {$msg_error=T_("Vous n'avez pas les droits d'accès à l'administration du logiciel, contactez votre administrateur");}
					elseif(preg_match( '/^stat.*/', $_GET['page']) && $rright['stat']==0) {$msg_error=T_("Vous n'avez pas les droits d'accès aux statistiques du logiciel, contactez votre administrateur");}
					elseif(preg_match( '/^core.*/', $_GET['page']) && $rright['admin']==0) {$msg_error=T_("Vous n'avez pas les droits d'accès à cette page, contactez votre administrateur (ERREUR 2)");}
					elseif($_GET['page']=='dashboard' && $_GET['userid']=='%' && $rright['side_all']==0 && $_GET['companyview']==0) {$msg_error=T_("Vous n'avez pas les droits d'accès à la liste de tous les tickets, contactez votre administrateur");}
					elseif($_GET['page']=='dashboard' && $_GET['userid']!='%' && $rright['side_all']==0 && $_GET['companyview']==0 && $_GET['userid']!=$_SESSION['user_id']) {$msg_error=T_("Vous n'avez pas les droits de consulter les tickets d'un autre utilisateur, contactez votre administrateur");}
					elseif($_GET['page']=='ticket' && $rright['dashboard_service_only'] && $_GET['id'] && $rparameters['user_limit_service']==1 && ($cnt_agency==0 || !$cnt_agency) && $_GET['action']!='template') //case to user profil super try to open another ticket of another service
					{
						//check if ticket service is the same as user
						$qry=$db->prepare("SELECT id FROM tusers_services WHERE user_id=:user_id AND service_id=(SELECT u_service FROM tincidents WHERE id=:id)");
						$qry->execute(array('user_id' => $_SESSION['user_id'],'id' => $_GET['id']));
						$check_ticket_service=$qry->fetch();
						$qry->closeCursor();
						if(!$check_ticket_service) {
							//allow technician to view ticket when there is sender
							$qry=$db->prepare("SELECT `user`,`technician` FROM `tincidents` WHERE id=:id");
							$qry->execute(array('id' => $_GET['id']));
							$check_ticket_tech_sender=$qry->fetch();
							$qry->closeCursor();
							if($check_ticket_tech_sender[0]!=$_SESSION['user_id'] && $check_ticket_tech_sender[1]!=$_SESSION['user_id']) {$msg_error=T_("Vous n'avez pas les droits de consulter le ticket de ce service, contactez votre administrateur");} else {include("$_GET[page].php");}
						} else {
							include("$_GET[page].php");
						}
					}
					elseif($_GET['page']=='asset' && $rright['asset_list_company_only']!=0 && $_GET['action']!='new') // restrict user access to asset of our company only
					{
						//check is current user have right to display current asset
						$qry=$db->prepare("SELECT `company` FROM `tusers` WHERE id=(SELECT user FROM tassets WHERE id=:id)");
						$qry->execute(array('id' => $_GET['id']));
						$check_asset_company=$qry->fetch();
						$qry->closeCursor();
						
						if($check_asset_company['company']!=$ruser['company'])
						{$msg_error=T_("Vous n'avez pas les droits d'accès à la fiche de cet équipement, contactez votre administrateur");}
						else {include("$_GET[page].php");} 
					}
					//allow display pages to company view
					elseif(($_GET['companyview']==1 || $_GET['page']=='preview_mail') && $rparameters['user_company_view']==1 && $rright['side_company']!=0 && $ruser['company'])	
					{
						if(($_GET['page']=='ticket' || $_GET['page']=='preview_mail') && $_GET['id'] && $_GET['action']!='template')
						{
							//check if ticket is the same company than connected user
							$qry=$db->prepare("SELECT `user` FROM `tincidents` WHERE id=:id");
							$qry->execute(array('id' => $_GET['id']));
							$check_ticket_company=$qry->fetch();
							$qry->closeCursor();
							$qry=$db->prepare("SELECT `company` FROM `tusers` WHERE id=:id");
							$qry->execute(array('id' => $check_ticket_company['user']));
							$check_ticket_company=$qry->fetch();
							$qry->closeCursor();
							if(($check_ticket_company['company']==$ruser['company']) && ($ruser['company']!=0))
							{
								include("$_GET[page].php");
							} else {
								$msg_error=T_("Vous n'avez pas les droits de consulter ce ticket, contactez votre administrateur (ERREUR 1)");
							}
						} elseif($_GET['page']=='dashboard' || $_GET['action']=='template') {include("$_GET[page].php");}
					}
					elseif($_GET['page']=='dashboard' && !$rright['side_all'] && $_GET['userid']!=$_SESSION['user_id'] && $_GET['userid']) //check user URL 
					{
						$msg_error=T_("Vous n'avez pas les droits de consulter le ticket de cet utilisateur, contactez votre administrateur");
					}
					else{include("$_GET[page].php");}
				}
				if($msg_error){echo DisplayMessage('error',$msg_error);}
				echo '
			</div><!-- /.page-content -->
			<footer class="footer d-none d-sm-block">
				<div class="footer-tools">
					<a id="btn-scroll-up" href="#" class="btn-scroll-up btn btn-dark btn-smd mb-2 mr-2">
						<i class="fa fa-angle-double-up mx-1"><!----></i>
					</a>
				</div>
			</footer>
		</div><!-- /main -->
	</div><!-- /.main-container -->
</div><!-- /.body-container -->
';
?>