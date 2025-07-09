<?php
################################################################################
# @Name : /menu.php
# @Description : display left panel menu
# @Call : /main.php
# @Parameters : 
# @Author : Flox
# @Create : 06/09/2013
# @Update : 13/04/2023
# @Version : 3.2.35
################################################################################

//block direct access
if(!isset($_SESSION['user_id'])) {echo 'ERROR : forbidden access'; exit;}

//initialize variables 
if(!isset($state)) $state = ''; 

echo '
<div class="sidebar-inner">
	<div class="flex-grow-1 ace-scroll ">
		<div class="sidebar-section my-2">
			<div class="sidebar-section-item fadeable-left">
				<div class="fadeinable sidebar-shortcuts-mini">
					<span class="btn btn-success p-0"><!----></span>
				</div>
				<div class="fadeable">
					<div class="sub-arrow"></div>
					<div>
						';
						//new ticket button
						if($rright['side_open_ticket'] && (($_GET['page']!='asset_list') || ($rright['side_asset_create']==0)) && ($_GET['page']!='asset')  && ($_GET['page']!='procedure') && ($_GET['page']!='project'))
						{
							if($ruser['default_ticket_state']!='')
							{
								if($ruser['default_ticket_state']=='meta_all')
								{
									$target_url='./index.php?page=ticket&amp;action=new&amp;userid=%25&amp;state=meta&amp;view='.$_GET['view'].'&amp;date_start='.$_GET['date_start'].'&amp;date_end='.$_GET['date_end'];
								} else {
									if($ruser['default_ticket_state']=='all') {$ruser['default_ticket_state']='%25';}
									$target_url='./index.php?page=ticket&amp;action=new&amp;userid='.$_GET['userid'].'&amp;state='.$ruser['default_ticket_state'].'&amp;view='.$_GET['view'].'&amp;date_start='.$_GET['date_start'].'&amp;date_end='.$_GET['date_end'];
								}
							} else {
								$target_url='./index.php?page=ticket&amp;action=new&amp;userid='.$_GET['userid'].'&amp;state='.$_GET['state'].'&amp;view='.$_GET['view'].'&amp;date_start='.$_GET['date_start'].'&amp;date_end='.$_GET['date_end'];
							}
							echo'
							<a href="'.$target_url.'">
								<button accesskey="n" title="'.T_("Ajoute un nouveau ticket").' (ALT+n)" onclick=\'window.location.href="'.$target_url.'"\' class="btn btn-smd btn-success">
									<i class="fa fa-plus"><!----></i>
									'.T_('Nouveau ticket').'
								</button>
							</a>
							';
						}
						//new asset button
						if($rright['side_asset_create'] && ($rparameters['asset']==1) && (($_GET['page']=='asset_list') || ($_GET['page']=='asset') ) && ($_GET['state']!='1'))
						{
							echo'
							<button accesskey="n" title="'.T_("Ajoute un nouvel équipement").' (SHIFT+ALT+n)" onclick=\'window.location.href="index.php?page=asset&amp;action=new"\' class="btn btn-smd btn-success">
								<i class="fa fa-plus"><!----></i>
								'.T_('Nouvel équipement').'
							</button>
							';
						}
						//new asset lot button
						if($rright['side_asset_create'] && ($rparameters['asset']==1) && (($_GET['page']=='asset_list') || ($_GET['page']=='asset') ) && ($_GET['state']=='1'))
						{
							echo'
							<button accesskey="n" title="'.T_("Permet l'ajout de plusieurs équipements à la fois").' (SHIFT+ALT+n)" onclick=\'window.location.href="index.php?page=asset_stock"\' class="btn btn-smd btn-warning">
								<span style="color:#FFF;">
									<i class="fa fa-plus"><!----></i>
									'.T_('Ajouter un lot').'
								</span>
							</button>
							';
						}
						//new procedure button
						if($rright['procedure_add'] && ($_GET['page']=='procedure'))
						{
							echo'
							<button accesskey="n" title="'.T_("Ajoute une nouvelle procédure").' (SHIFT+ALT+n)" onclick=\'window.location.href="index.php?page=procedure&amp;action=add"\' class="btn btn-smd btn-success">
								<i class="fa fa-plus"><!----></i>
								'.T_('Nouvelle procédure').'
							</button>
							';
						}
						if($rright['project'] && ($_GET['page']=='project'))
						{
							echo'
							<button accesskey="n" title="'.T_("Ajoute un nouveau projet").' (SHIFT+ALT+n)" onclick=\'window.location.href="index.php?page=project&amp;action=add"\' class="btn btn-smd btn-success">
								<i class="fa fa-plus"><!----></i>
								'.T_('Nouveau projet').'
							</button>
							';
						}
						echo '
					</div>
				</div>
			</div>
			';
			if($rright['search'])
			{
				echo '
				<div class="sidebar-section-item">
					<i class="fadeinable fa fa-search text-info mr-n1"><!----></i>
					<div class="fadeable d-inline-flex align-items-center ml-3 ml-lg-0">
						<i class="fa fa-search mr-n3 text-info"><!----></i>
						';
						if($_GET['subpage']=='user')
						{
							echo '<form method="POST" action="index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'" class="form-search">';
						}elseif($_GET['page']=='asset_list' || $_GET['page']=='asset') {
							echo '<form method="POST" action="index.php?page=asset_list" class="form-search">';
						}elseif($_GET['page']=='admin' && $_GET['subpage']=='profile') {
							echo '<form method="POST" action="index.php?page=admin&amp;subpage=profile" class="form-search">';
						}elseif($_GET['page']=='procedure' && $rright['procedure']) {
							echo '<form method="POST" action="index.php?page=procedure" class="form-search">';
						}elseif($_GET['subpage']=='list' && ($rright['admin'] || $rright['admin_lists'] || $rright['admin_lists_category'] || $rright['admin_lists_subcat'] || $rright['admin_lists_criticality'] || $rright['admin_lists_priority'] || $rright['admin_lists_type'])) {
							echo '<form method="POST" action="index.php?page=admin&subpage=list&table='.$_GET['table'].'" class="form-search">';
						}elseif($_GET['subpage']=='log' && $rright['admin']) {
							echo '<form method="POST" action="index.php?page=admin&subpage=log&log='.$_GET['log'].'" class="form-search">';
						} else { 
							//replace "%" character with "%25"
							$get_userid=str_replace('%','%25',$_GET['userid']);
							$get_state=str_replace('%','%25',$_GET['state']);
							$get_companyview=str_replace('%','%25',$_GET['companyview']);
							echo '<form method="POST" action="index.php?page=dashboard&amp;userid='.$get_userid.'&amp;state='.$get_state.'&amp;companyview='.$get_companyview.'" class="form-search">';
						}
							echo '
									<span class="input-icon">
										';
										if($_GET['subpage']=='user')
										{
											echo '<input type="text" class="sidebar-search-input pl-4 pr-3 mr-n2" maxlength="60" title="'.T_("Lance une recherche dans la liste des utilisateurs").'" placeholder="'.T_('Recherche utilisateur').'" id="userkeywords" name="userkeywords" autocomplete="on" value="'.$userkeywords.'" />';
										} elseif($_GET['page']=='asset_list' || $_GET['page']=='asset' || $_GET['tab']=='asset') {
											echo '<input type="text" class="sidebar-search-input pl-4 pr-3 mr-n2" maxlength="60" title="'.T_("Lance une recherche dans la liste des équipements").'" placeholder="'.T_('Recherche équipement').'" id="assetkeywords" name="assetkeywords" autocomplete="on" value="'.$assetkeywords.'" />';
										} elseif($_GET['page']=='admin' && $_GET['subpage']=='profile' && $rright['admin']) {
											echo '<input type="text" class="sidebar-search-input pl-4 pr-3 mr-n2" maxlength="60" title="'.T_("Lance une recherche dans les droits").'" placeholder="'.T_('Recherche droit').'" id="rightkeywords" name="rightkeywords" autocomplete="on" value="'.$rightkeywords.'" />';
										}elseif($_GET['page']=='procedure' && $rright['procedure']) {
											echo '<input type="text" class="sidebar-search-input pl-4 pr-3 mr-n2" maxlength="60" title="'.T_("Lance une recherche dans les procédures").'" placeholder="'.T_('Recherche procédure').'" id="procedurekeywords" name="procedurekeywords" autocomplete="on" value="'.$procedurekeywords.'" />';
										}elseif($_GET['subpage']=='list' && ($rright['admin'] || $rright['admin_lists'] || $rright['admin_lists_category'] || $rright['admin_lists_subcat'] || $rright['admin_lists_criticality'] || $rright['admin_lists_priority'] || $rright['admin_lists_type'])) {
											echo '<input type="text" class="sidebar-search-input pl-4 pr-3 mr-n2" maxlength="60" title="'.T_("Lance une recherche dans les listes").'" placeholder="'.T_('Recherche liste').'" id="listkeywords" name="listkeywords" autocomplete="on" value="'.$listkeywords.'" />';
										} elseif($_GET['subpage']=='log' && $rright['admin']) {
											echo '<input type="text" class="sidebar-search-input pl-4 pr-3 mr-n2" maxlength="60" title="'.T_("Lance une recherche dans les logs").'" placeholder="'.T_('Recherche logs').'" id="logkeywords" name="logkeywords" autocomplete="on" value="'.$logkeywords.'" />';
										} else {
											echo '<input type="text" class="sidebar-search-input pl-4 pr-3 mr-n2" maxlength="60" title="'.T_("Lance une recherche dans la liste des tickets").'" placeholder="'.T_('Recherche ticket').'" id="keywords" name="keywords" autocomplete="on" value="'.$keywords.'" />';
										}
										echo '
									</span>
								</form>
					</div>
				</div>';
			} 
			echo '
		</div>
		<ul class="nav has-active-border" aria-label="Main">
		';
			if($rright['side_your'])
			{
				//special case to count technician ticket, included ticket where technician is sender 
				if($_SESSION['profile_id']==0 || $_SESSION['profile_id']==4 || ($_SESSION['profile_id']==3 && $rright['ticket_tech_super']) )
				{
					$where_profil="(user='$uid' OR technician='$uid')";
				} else {
					$where_profil="$profile='$uid'";
				}
				$query="SELECT count(*) FROM `tincidents` WHERE $where_profil $where_service_your $where_agency_your AND disable='0'";
				$query=$db->query($query);
				$cntall=$query->fetch();
				$query->closeCursor(); 
				if(($_GET['page']=='dashboard' || $_GET['page']=='ticket') && $_GET['userid']!='%' && $_GET['userid']!='0') {$selected_side_your=1;} else {$selected_side_your=0;}
				
				echo "
				<li "; if($selected_side_your) {echo 'class="nav-item active open"';} else {echo 'class="nav-item"';}  echo ">
					<a  href=\"./index.php?page=dashboard&amp;userid=$_SESSION[user_id]&amp;state=%25\" class=\"nav-link dropdown-toggle\" >
						<i class=\"nav-icon fa fa-ticket\"><!----></i>
						<span class=\"nav-text fadeable\">
							"; echo T_('Vos tickets');
								if($cnt3[0]>0 && $rright['side_your_not_read']!=0) echo '<span title="'.$cnt3[0].' '.T_('tickets non lus en attente').'" class="fas fa-exclamation-triangle text-110 text-warning-m2 ml-2"><!----></span>';
							echo '
						</span>
						<b class="caret fa fa-angle-left rt-n90"><!----></b>
					</a>
					<div class="hideable submenu collapse '; if($selected_side_your) {echo 'show';} echo '">
						<ul class="submenu-inner" >';
							//display all states link
							if($_GET['userid']!='%' && $_GET['state']=='%') {echo '<li class="nav-item active">';} else {echo '<li class="nav-item">';} echo '
									<a class="nav-link pl-4" href="./index.php?page=dashboard&amp;userid='.$_SESSION['user_id'].'&amp;state=%25&amp;ticket=%25&amp;category=%25&amp;subcat=%25&amp;title=%25&amp;date_create=%25&amp;priority=%25&amp;criticality=%25&amp;company=%25">
										<i class="nav-icon fa fa-angle-right pr-1"><!----></i>'.T_('Tous les états').' ('.$cntall[0].')
									</a>
							</li>';
							 //display meta states link
							if($rparameters['meta_state']==1 && $rright['side_your_meta']!=0)
							{
								if($_SESSION['profile_id']==0)  //modify counter for this state only
								{
									$where_profil="(technician='$uid')";
								} 
								$query=$db->query("SELECT COUNT(tincidents.id) FROM `tincidents`,`tstates` WHERE  $where_profil $where_service_your $where_agency_your AND `tincidents`.`state`=`tstates`.`id` AND tincidents.disable='0' AND tstates.meta='1'");
								$cntmeta=$query->fetch();
								$query->closeCursor();  
								if($_GET['userid']!='%' && $_GET['state']=='meta') {echo '<li class="nav-item active">';} else {echo '<li class="nav-item">';} echo '
									<a class="nav-link pl-4" title="'.$label_meta.'" href="./index.php?page=dashboard&amp;userid='.$_SESSION['user_id'].'&amp;state=meta&amp;ticket=%25&amp;category=%25&amp;subcat=%25&amp;title=%25&amp;date_create=%25&amp;priority=%25&amp;criticality=%25&amp;company=%25">
										<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
										'.T_('À traiter ').' ('.$cntmeta[0].')
									</a>
								</li>';
								if($_SESSION['profile_id']==0) //for technician count ticket where technician is user
								{
									$where_profil="(user='$uid' OR technician='$uid')";
								} 
							}
							//display unread ticket
							if($cnt3[0]>0 && $rright['side_your_not_read']!=0)
							{
								if($_GET['techread']!='' && $_GET['page']!='searchengine') echo '<li class="nav-item active">'; else echo '<li class="nav-item">'; echo '
									<a class="nav-link pl-4" href="./index.php?page=dashboard&amp;userid='.$_SESSION['user_id'].'&amp;techread=0">
										<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
										'.T_('Non lus').' ('.$cnt3[0].')&nbsp;&nbsp;&nbsp;<i title="'.T_('Tickets non lus sont en attente').'" class="fa fa-exclamation-triangle text-warning"><!----></i>
									</a>
								</li>';
								
							}
							//for each state display in sub-menu
							$qry=$db->prepare("SELECT `id`,`description`,`name`,`icon` FROM `tstates` WHERE id!=5 ORDER BY number");
							$qry->execute();
							while($row=$qry->fetch()) 
							{
								$query2=$db->query("SELECT count(id) FROM `tincidents` WHERE $where_profil $where_service_your $where_agency_your AND state='$row[id]' AND disable='0'");
								$cnt=$query2->fetch();
								$query2->closeCursor(); 
								echo '
								<li ';  
								if($_GET['userid']!='%' && $_GET['state']==$row['id']) {echo ' class="nav-item active"';} else {echo 'class="nav-item"';}
								echo '>
									<a class="nav-link pl-4" title="'.T_($row['description']).'" href="./index.php?page=dashboard&amp;userid='.$_SESSION['user_id'].'&amp;state='.$row['id'].'&amp;ticket=%25&amp;category=%25&amp;subcat=%25&amp;title=%25&amp;date_create=%25&amp;priority=%25&amp;criticality=%25&amp;company=%25">
										<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
										'.T_($row['name']).' ('.$cnt[0].') 
									</a>
								</li>';
							}
							$qry->closeCursor();
							
							//display technician group ticket
							if($rright['side_your_tech_group']!=0 && ($_SESSION['profile_id']==4 || $_SESSION['profile_id']==0 || $_SESSION['profile_id']==3) )
							{
								//check if technician have group
								$qry=$db->prepare("SELECT `group` FROM `tgroups_assoc`, `tgroups` WHERE `tgroups_assoc`.group=`tgroups`.id AND user=:user AND `tgroups`.disable=0");
								$qry->execute(array('user' => $_SESSION['user_id']));
								while($row=$qry->fetch()) 
								{
									//count number of tickets present in this group
									$qry2=$db->prepare("SELECT COUNT(id) FROM `tincidents` WHERE t_group=:t_group AND disable='0'");
									$qry2->execute(array('t_group' => $row['group']));
									$cntgrp=$qry2->fetch();
									$qry2->closeCursor();
									//get group name
									$qry2=$db->prepare("SELECT `name` FROM `tgroups` WHERE id=:id");
									$qry2->execute(array('id' => $row['group']));
									$group_name=$qry2->fetch();
									$qry2->closeCursor();
									if($row['group']==$_GET['techgroup']) echo '<li class="nav-item active">'; else echo '<li class="nav-item">'; echo '
										<a class="nav-link pl-4" href="./index.php?page=dashboard&amp;userid='.$_SESSION['user_id'].'&amp;techgroup='.$row['group'].'">
											<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
											[G] '.$group_name['name'].' ('.$cntgrp[0].')
										</a>
									</li>';
								}
								$qry->closeCursor();
							}

							//display observed tickets
							if($rparameters['ticket_observer'] && $rright['side_your_observer'])
							{
								//count observed ticket of current user
								$qry=$db->prepare("SELECT COUNT(`id`) as `counter` FROM `tincidents` WHERE `observer1`=:current_user OR `observer2`=:current_user OR `observer3`=:current_user AND `disable`='0'");
								$qry->execute(array('current_user' => $_SESSION['user_id']));
								$ticket_observed=$qry->fetch();
								$qry->closeCursor();
								if($ticket_observed['counter']>0)
								{
									if($_GET['view']=='observer') echo '<li class="nav-item active">'; else echo '<li class="nav-item">'; echo '
										<a class="nav-link pl-4" title="'.T_("Tickets sur lesquels vous êtes positionné en tant qu'observateur").'" href="./index.php?page=dashboard&amp;userid='.$_SESSION['user_id'].'&amp;view=observer">
											<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
											'.T_('Observé').' ('.$ticket_observed['counter'].')
										</a>
									</li>';
								}
							}
							echo "
						</ul>
					</div>
				</li>
				";
			}
			//display side menu for company view, all tickets of current connected user company
			if($rparameters['user_company_view']==1 && $rright['side_company'] && $ruser['company']!=0)
			{
				if($_GET['page']=='dashboard' && ($_GET['userid']=='%' || $_GET['userid']=='0') && $_GET['viewid']=='' && $_GET['companyview']) {$selected_company_view=1;} else {$selected_company_view=0;}
				//count all company tickets
				$qry=$db->prepare("SELECT COUNT(tincidents.id) FROM `tincidents`,`tusers` WHERE tincidents.user=tusers.id AND tincidents.disable='0' AND tusers.company=:company AND tincidents.disable='0'");
				$qry->execute(array('company' => $ruser['company']));
				$cntall=$qry->fetch();
				$qry->closeCursor();
				
				//count all ticket not attribute of current user company
				$qry=$db->prepare("SELECT COUNT(tincidents.id) FROM tincidents, tusers WHERE tincidents.user=tusers.id AND tusers.company=:company AND technician='0' AND t_group='0' AND tincidents.disable='0'");
				$qry->execute(array('company' => $ruser['company']));
				$cnt6=$qry->fetch();
				$qry->closeCursor();
				
				if($selected_company_view){echo '<li class="nav-item active open" >';} else {echo '<li class="nav-item" >';}  
				echo '
					<a href="./index.php?page=dashboard&amp;userid=%25&amp;state=%25" class="nav-link dropdown-toggle">
						<i class="nav-icon fa fa-ticket"><!----></i>
						<span class="nav-text fadeable"> 
							'.T_('Ma société').'
						</span>
						<b class="caret fa fa-angle-left rt-n90"></b>
					</a>
					<div class="hideable submenu collapse '; if($selected_company_view) {echo 'show';} echo ' ">
						<ul class="submenu-inner" >';
							if($_GET['page']=='dashboard' && $_GET['userid']=='%' && $_GET['state']=='%') {echo '<li class="nav-item active">';} else {echo '<li class="nav-item">';}
							echo '
								<a class="nav-link pl-4" href="./index.php?page=dashboard&amp;userid=%25&amp;state=%25&amp;ticket=%25&amp;technician=%25&amp;user=%25&amp;category=%25&amp;subcat=%25&amp;title=%25&amp;date_create=%25&amp;priority=%25&amp;criticality=%25&amp;companyview=1">
									<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
									'.T_('Tous les états').' ('.$cntall[0].')
								</a>
							</li>';
							 //display meta  states link
							if($rparameters['meta_state']==1  && $rright['side_all_meta']!=0)
							{
								$qry=$db->prepare("SELECT COUNT(tincidents.id) FROM `tincidents`,`tusers`,`tstates` WHERE tincidents.user=tusers.id AND tincidents.state=tstates.id AND tincidents.disable='0' AND tstates.meta=1 AND tusers.company=:company");
								$qry->execute(array('company' => $ruser['company']));
								$cntmetaall=$qry->fetch();
								$qry->closeCursor();
								
								if($_GET['page']=='dashboard' && $_GET['userid']=='%' && $_GET['state']=='meta') {echo '<li class="nav-item active">';} else {echo '<li lass="nav-item">';}
								echo '
									<a class="nav-link pl-4" title="'.$label_meta.'" href="index.php?page=dashboard&amp;userid=%25&amp;state=meta&amp;ticket=%25&amp;technician=%25&amp;user=%25&amp;category=%25&amp;subcat=%25&amp;title=%25&amp;date_create=%25&amp;priority=%25&amp;criticality=%25&amp;companyview=1">
										<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
										'; echo T_('À traiter'); echo' ('.$cntmetaall[0].')
									</a>
								</li>';
							}	
							//for each state display in sub-menu
							$qry=$db->prepare("SELECT `id`,`name`,`description` FROM `tstates` WHERE id!=5 ORDER BY number");
							$qry->execute();
							while($row=$qry->fetch()) 
							{
								$qry2=$db->prepare("SELECT COUNT(tincidents.id) FROM `tincidents`,`tusers` WHERE tincidents.user=tusers.id AND state LIKE :state AND tusers.company=:company AND tincidents.disable='0'");
								$qry2->execute(array('state' => $row['id'],'company' => $ruser['company']));
								$cnt=$qry2->fetch();
								$qry2->closeCursor();
								
								if($_GET['page']=='dashboard' && $_GET['userid']=='%' && $_GET['state']==$row['id']) {echo '<li class="nav-item active">';} else {echo '<li lass="nav-item">';} 
								echo '
									<a class="nav-link pl-4" title="'.T_($row['description']).'" href="index.php?page=dashboard&amp;userid=%25&amp;state='.$row['id'].'&amp;ticket=%25&amp;technician=%25&amp;user=%25&amp;category=%25&amp;subcat=%25&amp;title=%25&amp;date_create=%25&amp;priority=%25&amp;criticality=%25&amp;companyview=1">
										<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
										'.T_($row['name']).' ('.$cnt[0].')
									</a>
								</li>';
							}
							$qry->closeCursor();
							echo'
						</ul>
					</div>
				</li>';
			}
			//display side menu for all tickets of current connected user
			if(
				($rright['side_all'] && $rparameters['user_limit_service']==0) || 
				($rright['side_all'] && $rparameters['user_limit_service'] && ($cnt_service || $cnt_agency)) || 
				($rright['side_all'] && $rparameters['user_limit_service'] && $rright['admin']) ||
				($rright['side_all'] && $rparameters['user_limit_service'] && !$rright['admin'] && !$rright['dashboard_service_only'] && !$rright['dashboard_service_only']) #allow technician to view all tickets with user_limit_service parameter
			) //not display all tickets for supervisor without service or agency, without user_limit_service tech must view all tickets
			{
				if(($_GET['userid']=='%' || $_GET['userid']=='0') && $_GET['viewid']=='' && $_GET['companyview']=='') {$side_all_ticket_selected=1;} else {$side_all_ticket_selected=0;}
				$query="SELECT count(*) FROM `tincidents` WHERE disable='0' $where_agency $where_service $parenthese2";
				$query=$db->query($query);
				$cntall=$query->fetch();
				$query->closeCursor(); 
				if($side_all_ticket_selected) {echo '<li class="nav-item active open" >';} else {echo '<li class="nav-item" >';}  
				echo '
					<a href="./index.php?page=dashboard&amp;userid=%25&amp;state=2" class="nav-link dropdown-toggle">
						<i class="nav-icon fa fa-ticket"><!----></i>
							<span class="nav-text fadeable"> 
								'.T_('Tous les tickets');
									if($cnt5[0]>0 && $rright['side_your_not_attribute']!=0) echo '&nbsp;&nbsp;<span title="'.T_("De nouveaux tickets sont en attente d'attribution").'" class="fas fa-exclamation-triangle text-110 text-danger-m2"><!----></span>';
								echo '
							</span>
							<b class="caret fa fa-angle-left rt-n90"><!----></b>
					</a>
					<div class="hideable submenu collapse '; if($side_all_ticket_selected) {echo 'show';} echo' ">
						<ul class="submenu-inner" >';
							if($_GET['userid']=='%' && $_GET['state']=='%') {echo '<li class="nav-item active">';} else {echo '<li class="nav-item">';}
							echo '
									
								<a class="nav-link pl-4" href="./index.php?page=dashboard&amp;userid=%25&amp;state=%25&amp;ticket=%25&amp;technician=%25&amp;user=%25&amp;category=%25&amp;subcat=%25&amp;title=%25&amp;date_create=%25&amp;priority=%25&amp;criticality=%25&amp;company=%25">
									<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
									'.T_('Tous les états').' ('.$cntall[0].')
								</a>
							</li>';
							//display new tickets if exist
							if($cnt5[0]>0 && $rright['side_your_not_attribute']!=0)
							{
								if($_GET['userid']=='0' && $_GET['state']=='%') {echo '<li class="nav-item active">';} else {echo '<li class="nav-item">';} echo '
									<a class="nav-link pl-4" href="./index.php?page=dashboard&amp;userid=0&amp;t_group=0&amp;state=%25">
										<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
										'.T_('Nouveaux').' ('.$cnt5[0].')
										';if($cnt5[0]>0 && $rright['side_your_not_attribute']!=0) echo '&nbsp;&nbsp;<span title="'.T_("De nouveaux tickets sont en attente d'attribution").'" class="fas fa-exclamation-triangle text-120 text-danger-m2"><!----></span>'; echo '
									</a>
								</li>';
							}
							//display meta states link
							if($rparameters['meta_state']==1  && $rright['side_all_meta']!=0)
							{
								$query=$db->query("SELECT COUNT(`tincidents`.`id`) FROM `tincidents`,`tstates` WHERE `tincidents`.`state`=`tstates`.`id` AND `tincidents`.disable='0' AND `tstates`.`meta`='1' $where_agency $where_service $parenthese2");
								$cntmetaall=$query->fetch();
								$query->closeCursor(); 
								if($_GET['userid']=='%' && $_GET['state']=='meta') {echo '<li class="nav-item active">';} else {echo '<li class="nav-item">';}
								echo '
									<a class="nav-link pl-4" title="'.$label_meta.'" href="./index.php?page=dashboard&amp;userid=%25&amp;state=meta&amp;ticket=%25&amp;technician=%25&amp;user=%25&amp;category=%25&amp;subcat=%25&amp;title=%25&amp;date_create=%25&amp;priority=%25&amp;criticality=%25&amp;company=%25">
										<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
										'.T_('À traiter').' ('.$cntmetaall[0].')
									</a>
								</li>';
							}
							//for each state display in sub-menu
							$qry=$db->prepare("SELECT `id`,`name`,`description` FROM `tstates` WHERE id!=5 ORDER BY number");
							$qry->execute();
							while($row=$qry->fetch()) 
							{
								$query2=$db->query("SELECT count(id) FROM `tincidents` WHERE state='$row[id]' $where_agency $where_service $parenthese2 AND disable='0'");
								$cnt=$query2->fetch();
								$query2->closeCursor(); 
								if($_GET['userid']=='%' && $_GET['state']==$row['id']) {echo '<li class="nav-item active">';} else {echo '<li class="nav-item">';}
								echo '
									<a class="nav-link pl-4" title="'.T_($row['description']).'" href="./index.php?page=dashboard&amp;userid=%25&amp;state='.$row['id'].'&amp;ticket=%25&amp;technician=%25&amp;user=%25&amp;category=%25&amp;subcat=%25&amp;title=%25&amp;date_create=%25&amp;priority=%25&amp;criticality=%25&amp;company=%25">
										<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
										'.T_($row['name']).' ('.$cnt[0].')
									</a>
								</li>';
							}
							$qry->closeCursor();
							echo	'
						</ul>
					</div>
				</li>';
			}
			if($rright['side_view'])
			{
				if($_GET['viewid'] || $_GET['page']=='view') {$side_view_selected=1;} else {$side_view_selected=0;}
				//if exist view for connected user then display link view
				$qry=$db->prepare("SELECT `id` FROM `tviews` WHERE uid=:uid ORDER BY name");
				$qry->execute(array('uid' => $_SESSION['user_id']));
				$row=$qry->fetch();
				$qry->closeCursor();
				if(!empty($row['id']))
				{
					if($side_view_selected) {echo '<li class="nav-item active open" >';} else {echo '<li class="nav-item" >';}  
					echo '
						<a href="./index.php?page=dashboard&viewid=1" class="nav-link dropdown-toggle">
							<i class="nav-icon fa fa-eye"><!----></i>
							<span class="nav-text fadeable"> '.T_('Vos vues').' </span>
							<b class="caret fa fa-angle-left rt-n90"><!----></b>
						</a>
						<div class="hideable submenu collapse '; if($side_view_selected) {echo 'show';} echo '">
							<ul class="submenu-inner">';
							//get view of connected user
							$qry=$db->prepare("SELECT `id`,`name`,`category`,`subcat`,`technician` FROM `tviews` WHERE `uid`=:uid ORDER BY `name`");
							$qry->execute(array('uid' => $_SESSION['user_id']));
							while($view=$qry->fetch()) 
							{
								//selected view
								if($_GET['viewid']==$view['id'])  {echo '<li class="nav-item active">';} else {echo '<li class="nav-item">';}

								if($view['technician'] && $rparameters['meta_state'] && $rright['side_all_meta']) //case technician view
								{
									//count entries
									$query2="SELECT COUNT(`tincidents`.`id`) FROM `tincidents`,`tstates` WHERE `tincidents`.`state`=`tstates`.`id` AND `tincidents`.disable='0' AND `tstates`.`meta`='1' AND `technician`='$view[technician]' $where_agency $where_service $parenthese2";
									$query2=$db->query($query2);
									$view_count=$query2->fetch();
									$query2->closeCursor();

									//get technician name
									$qry2 = $db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
									$qry2->execute(array('id' => $view['technician']));
									$technician=$qry2->fetch();
									$qry2->closeCursor();
									if(!isset($technician['firstname'])) {$technician['firstname'];}
									if(!isset($technician['lastname'])) {$technician['lastname'];}
									$technician_name=substr($technician['firstname'],0,1).'. '.$technician['lastname'];

									if($_GET['viewid']==$view['id'] || $_GET['page']=='view') {$side_view_selected=1;} else {$side_view_selected=0;}
									if($side_view_selected) {echo '<li class="nav-item active open" >';} else {echo '<li class="nav-item" >';}  
										echo '
										<a title="'.T_('Vue technicien').'" class="nav-link pl-4" href="./index.php?page=dashboard&amp;userid=%25&amp;viewid='.$view['id'].'&amp;state=meta&amp;ticket=%25&amp;technician='.$view['technician'].'&amp;user=%25&amp;title=%25&amp;date_create=%25&amp;priority=%25&amp;criticality=%25&amp;company=%25">
											<i class="nav-icon fa fa-user pr-1"><!----></i>
											'.$technician_name.' ('.$view_count[0].')
										</a>
								
									</li>';

								} else { //case category view
									//case for no sub categories
									if($view['subcat']==0) {$subcat='%';} else {$subcat=$view['subcat'];}

									//count entries
									$query2="SELECT COUNT(`id`) FROM `tincidents` WHERE `category`='$view[category]' AND `subcat` LIKE '$subcat' $where_agency $where_service $parenthese2 AND `disable`='0'";
									$query2=$db->query($query2);
									$view_count=$query2->fetch();
									$query2->closeCursor();

									if($subcat=='%') {$subcat_url='%25';} else {$subcat_url=$subcat;}

									
									if($_GET['viewid']==$view['id'] || $_GET['page']=='view') {$side_view_selected=1;} else {$side_view_selected=0;}
									if($side_view_selected) {echo '<li class="nav-item active open" >';} else {echo '<li class="nav-item" >';}  
										echo '
										<a title="'.T_('Vue catégorie').'" class="nav-link pl-4 dropdown-toggle" href="./index.php?page=dashboard&amp;userid=%25&amp;viewid='.$view['id'].'&amp;category='.$view['category'].'&amp;subcat='.$subcat_url.'&amp;state=%25&amp;ticket=%25&amp;technician=%25&amp;user=%25&amp;title=%25&amp;date_create=%25&amp;priority=%25&amp;criticality=%25&amp;company=%25">
											<i class="nav-icon fa fa-square pr-1"><!----></i>
											'.$view['name'].' ('.$view_count[0].')
										</a>
										<div class="hideable submenu collapse '; if($side_view_selected) {echo 'show';} echo '">
											<ul class="submenu-inner">
												';
												//all state
												$query="SELECT count(*) FROM `tincidents` WHERE `disable`='0' AND `category`='$view[category]' AND `subcat` LIKE '$subcat' $where_agency $where_service $parenthese2";
												$query=$db->query($query);
												$cntall=$query->fetch();
												$query->closeCursor(); 
												if($_GET['userid']=='%' && $_GET['state']=='%' && $_GET['viewid']==$view['id']) {echo '<li class="nav-item active">';} else {echo '<li class="nav-item">';}
												echo '
													<a class="nav-link pl-5" href="./index.php?page=dashboard&amp;userid=%25&amp;state=%25&amp;ticket=%25&amp;viewid='.$view['id'].'&amp;technician=%25&amp;user=%25&amp;category='.$view['category'].'&amp;subcat='.$subcat_url.'&amp;title=%25&amp;date_create=%25&amp;priority=%25&amp;criticality=%25&amp;company=%25">
														<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
														'.T_('Tous les états').' ('.$cntall[0].')
													</a>
												</li>';
												//display meta states link
												if($rparameters['meta_state']  && $rright['side_all_meta']!=0)
												{
													$query=$db->query("SELECT COUNT(`tincidents`.`id`) FROM `tincidents`,`tstates` WHERE `tincidents`.`state`=`tstates`.`id` AND `tincidents`.disable='0' AND `tstates`.`meta`='1' AND `category`='$view[category]' AND `subcat` LIKE '$subcat' $where_agency $where_service $parenthese2");
													$cntmetaall=$query->fetch();
													$query->closeCursor(); 
													if($_GET['userid']=='%' && $_GET['state']=='meta' && $_GET['viewid']==$view['id']) {echo '<li class="nav-item active">';} else {echo '<li class="nav-item">';}
													echo '
														<a class="nav-link pl-5" title="'.$label_meta.'" href="./index.php?page=dashboard&amp;userid=%25&amp;state=meta&amp;viewid='.$view['id'].'&amp;ticket=%25&amp;technician=%25&amp;user=%25&amp;category='.$view['category'].'&amp;subcat='.$subcat_url.'&amp;title=%25&amp;date_create=%25&amp;priority=%25&amp;criticality=%25&amp;company=%25">
															<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
															'.T_('À traiter').' ('.$cntmetaall[0].')
														</a>
													</li>';
												}
												//for each state display in sub-menu
												$qry2=$db->prepare("SELECT `id`,`name`,`description` FROM `tstates` WHERE id!=5 ORDER BY number");
												$qry2->execute();
												while($row2=$qry2->fetch()) 
												{
													$qry3=$db->query("SELECT count(id) FROM `tincidents` WHERE `state`='$row2[id]' AND `category`='$view[category]' AND `subcat` LIKE '$subcat' $where_agency $where_service $parenthese2 AND `disable`='0'");
													$cnt=$qry3->fetch();
													$qry3->closeCursor(); 
													if($_GET['state']==$row2['id'] && $_GET['viewid']==$view['id']) {echo '<li class="nav-item active">';} else {echo '<li class="nav-item">';}
													echo '
														<a class="nav-link pl-5" title="'.T_($row2['description']).'" href="./index.php?page=dashboard&amp;userid=%25&amp;state='.$row2['id'].'&amp;viewid='.$view['id'].'&amp;ticket=%25&amp;technician=%25&amp;user=%25&amp;category='.$view['category'].'&amp;subcat='.$subcat_url.'&amp;title=%25&amp;date_create=%25&amp;priority=%25&amp;criticality=%25&amp;company=%25">
															<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
															'.T_($row2['name']).' ('.$cnt[0].')
														</a>
													</li>';
												}
												$qry2->closeCursor();
												echo '
											</ul>
										</div>
									</li>';
								}
							}
							$qry->closeCursor();
							echo '
							</ul>
						</div>
					</li>';
				}
			}
			if($rright['asset'] && $rparameters['asset']==1)
			{
				if($_GET['page']=='asset_list' || $_GET['page']=='asset_stock' || $_GET['page']=='asset') {$side_asset_selected=1;} else {$side_asset_selected=0;}
				if($side_asset_selected) {echo '<li class="nav-item active open" >';} else {echo '<li class="nav-item" >';}  
				echo '
					<a class="nav-link dropdown-toggle">
						<i class="nav-icon fa fa-desktop"><!----></i>
						<span class="nav-text fadeable">'.T_('Équipements').'</span>
						<b class="caret fa fa-angle-left rt-n90"><!----></b>
					</a>';
					if($rright['side_asset_all_state']!=0)
					{
						echo '
						<div class="hideable submenu collapse '; if($side_asset_selected) {echo 'show';} echo '">
							<ul class="submenu-inner">
								';
								//query count all assets or assets of company
								if($rright['asset_list_company_only'])
								{
									$qry=$db->prepare("SELECT COUNT(tassets.id) FROM `tassets`,`tusers` WHERE `tassets`.`user`=`tusers`.`id` AND `tassets`.`disable`='0' AND `tusers`.`company`=:company");
									$qry->execute(array('company' => $ruser['company']));
									$cnt=$qry->fetch();
									$qry->closeCursor();
									
								} elseif($rright['asset_list_department_only']!=0)
								{
									$qry=$db->prepare("SELECT COUNT(tassets.id) FROM `tassets` WHERE `disable`='0' AND `department`=(SELECT MAX(`service_id`) FROM `tusers_services` WHERE `user_id`=:user_id)");
									$qry->execute(array('user_id' => $_SESSION['user_id']));
									$cnt=$qry->fetch();
									$qry->closeCursor();
									
								} else {
									$qry=$db->prepare("SELECT COUNT(id) FROM `tassets` WHERE `disable`='0'");
									$qry->execute();
									$cnt=$qry->fetch();
									$qry->closeCursor();
								}
								if(($_GET['page']=='asset_list' || $_GET['page']=='asset') && $_GET['state']=='%') {echo '<li class="nav-item active">';} else {echo '<li class="nav-item">';}
									echo '
									<a class="nav-link pl-4" title="'.T_('Tous les équipements').'" href="./index.php?page=asset_list&amp;state=%25">
										<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
										'.T_('Tous').' ('.$cnt[0].')
									</a>
								</li>
								';
								//for each state display in sub-menu
								$qry=$db->prepare("SELECT `id`,`name`,`description` FROM `tassets_state` WHERE disable='0' ORDER BY `order`");
								$qry->execute();
								while($row=$qry->fetch()) 
								{
									//query count all assets or assets of company
									if($rright['asset_list_company_only']!=0)
									{
										$qry2=$db->prepare("SELECT COUNT(tassets.id) FROM `tassets`,`tusers` WHERE tassets.user=tusers.id AND tassets.state=:state AND tassets.disable='0' AND tusers.company=:company");
										$qry2->execute(array('state' => $row['id'],'company' => $ruser['company']));
										$cnt=$qry2->fetch();
										$qry2->closeCursor();
									} elseif($rright['asset_list_department_only'])
									{
										$qry2=$db->prepare("SELECT COUNT(tassets.id) FROM `tassets` WHERE `disable`='0' AND `state`=:state AND `department`=(SELECT MAX(`service_id`) FROM `tusers_services` WHERE `user_id`=:user_id)");
										$qry2->execute(array('state' => $row['id'],'user_id' => $_SESSION['user_id']));
										$cnt=$qry2->fetch();
										$qry2->closeCursor();
										
									}else {
										$qry2=$db->prepare("SELECT COUNT(id) FROM `tassets` WHERE `state`=:state AND `disable`='0'");
										$qry2->execute(array('state' => $row['id']));
										$cnt=$qry2->fetch();
										$qry2->closeCursor();
									}
									if(($_GET['page']=='asset_list' || $_GET['page']=='asset') && $_GET['state']==$row['id'] && $_GET['warranty']!=1) {echo '<li class="nav-item active">';} else {echo '<li class="nav-item">';}
									echo '
										<a class="nav-link pl-4" title="'.T_($row['description']).'" href="./index.php?page=asset_list&amp;state='.$row['id'].'">
											<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
											'.T_($row['name']).' ('.$cnt[0].')
										</a>
									</li>';
								}
								$qry->closeCursor();
								
								//display warranty link if parameter is enable
								if($rparameters['asset_warranty']==1)
								{
									$today=date('Y-m-d');
									//query count all assets or assets of company
									if($rright['asset_list_company_only']!=0)
									{
										$qry2=$db->prepare("SELECT COUNT(tassets.id) FROM `tassets`,`tusers` WHERE tassets.user=tusers.id AND tassets.state LIKE '2' AND tassets.date_end_warranty > :date_end_warranty AND tassets.disable='0' AND tusers.company=:company");
										$qry2->execute(array('date_end_warranty' => $today,'company' => $ruser['company']));
										$cnt=$qry2->fetch();
										$qry2->closeCursor();
									} else {
										$qry2=$db->prepare("SELECT count(id) FROM `tassets` WHERE state LIKE '2' AND date_end_warranty > :date_end_warranty AND disable='0'");
										$qry2->execute(array('date_end_warranty' => $today));
										$cnt=$qry2->fetch();
										$qry2->closeCursor();
									}
									if($_GET['page']=='asset_list' && $_GET['warranty']==1) {echo '<li class="nav-item active">';} else {echo '<li class="nav-item">';}
									echo '
										<a class="nav-link pl-4" title="'.T_('Liste des équipements en fonction de leurs garanties').'" href="./index.php?page=asset_list&amp;state=2&amp;warranty=1">
											<i class="nav-icon fa fa-angle-right pr-1"><!----></i>
											'.T_('Garanties').'  ('.$cnt[0].')
										</a>
									</li>';
								}
								echo'
							</ul>
						</div>';
					}
					echo '
				</li>';
			}
			if($rright['procedure'] && $rparameters['procedure'])
			{
				if($_GET['page']=='procedure') {echo '<li class="nav-item active" >';} else {echo '<li class="nav-item" >';} 
				 echo '
					<a class="nav-link" href="index.php?page=procedure" >
						<i class="nav-icon fa fa-book"><!----></i>
						<span class="nav-text fadeable">'.T_('Procédures').'</span>
					</a>
				</li>
				';
			}
			if($rright['planning'] && $rparameters['planning'])
			{
				if($_GET['page']=='calendar') {echo '<li class="nav-item active" >';} else {echo '<li class="nav-item" >';} 
				 echo '
					<a class="nav-link" href="./index.php?page=calendar" >
						<i class="nav-icon fa fa-calendar"><!----></i>
						<span class="nav-text fadeable">'.T_('Calendrier').'</span>
					</a>
				</li>';
			}
			if($rright['project'] && $rparameters['project'])
			{
				if($_GET['page']=='project') {echo '<li class="nav-item active" >';} else {echo '<li class="nav-item" >';} 
				 echo '
					<a class="nav-link" href="index.php?page=project">
						<i class="nav-icon fa fa-tasks"><!----></i>
						<span class="nav-text fadeable">'.T_('Projets').'</span>
					</a>
				</li>';
			}
			if($rright['contract'] && ($rparameters['company_limit_ticket'] || $rparameters['company_limit_hour']))
			{
				if($_GET['page']=='contract') {echo '<li class="nav-item active open" >';} else {echo '<li class="nav-item" >';} 
				 echo '
					<a class="nav-link dropdown-toggle" href="index.php?page=contract">
						<i class="nav-icon fa fa-file-contract"><!----></i>
						<span class="nav-text fadeable">'.T_('Contrats').'</span>
						<b class="caret fa fa-angle-left rt-n90"><!----></b>
					</a>
					<div class="hideable submenu collapse '; if($_GET['state']=='current' || $_GET['state']=='old') {echo 'show';} echo '">
						<ul class="submenu-inner">
							<li class="nav-item '; if($_GET['state']=='current') {echo 'active';} echo'">
								<a class="nav-link pl-5" href="index.php?page=contract&amp;state=current">
									<i class="nav-icon fa fa-calendar"><!----></i>&nbsp;
									'.T_('En cours').'
								</a>
							</li>
							<li class="nav-item '; if($_GET['state']=='old') {echo 'active';} echo'">
								<a class="nav-link pl-5" href="index.php?page=contract&amp;state=old">
									<i class="nav-icon fa fa-clock"><!----></i>&nbsp;
									'.T_('Passés').'
								</a>
							</li>
						</ul>
					</div>
				</li>';
			}
			//include plugin
			$section='menu';
			include('plugin.php');
			if($rright['stat'])
			{
				if($_GET['page']=='stat') {echo '<li class="nav-item active" >';} else {echo '<li class="nav-item" >';} 
				echo '
					<a class="nav-link" href="./index.php?page=stat&amp;tab=ticket">
						<i class="nav-icon fa fa-chart-line"><!----></i>
						<span class="nav-text fadeable">'.T_('Statistiques').'</span>
					</a>
				</li>';
			}
			if($rright['admin'] || $rright['admin_groups']!=0 || $rright['admin_lists']!=0 )
			{
				//select destination page by rights
				if($rright['admin']!=0) {$dest_subpage='parameters';}
				if($rright['admin_groups']!=0) {$dest_subpage='group';}
				if($rright['admin_lists']!=0) {$dest_subpage='list';}
				if($_GET['page']=='admin' || $_GET['page']=='changelog') {echo '<li class="nav-item active open" >';} else {echo '<li class="nav-item" >';}  
				echo '
					<a class="nav-link dropdown-toggle" href="./index.php?page=admin&amp;subpage='.$dest_subpage.'">
						<i class="nav-icon fa fa-cogs"><!----></i>
						<span class="nav-text fadeable"> '.T_('Administration').' 
							';
							if($rparameters['system_error']) {
								echo '&nbsp;&nbsp;<span title="'.T_("Des erreurs importantes sont détectées, corriger tous les points de couleur rouge").'" class="fas fa-exclamation-triangle text-110 text-danger-m2"><!----></span>';
							}elseif($rparameters['system_warning']) {
								echo '&nbsp;&nbsp;<span title="'.T_("Des avertissements ont été détectées, corriger tous les points de couleur jaune").'" class="fas fa-exclamation-triangle text-110 text-warning-m2"><!----></span>';
							}
							echo '
						</span>
						<b class="caret fa fa-angle-left rt-n90"><!----></b>
					</a>
					<div class="hideable submenu collapse '; if($_GET['page']=='admin' || $_GET['page']=='changelog') {echo 'show';} echo ' ">
						<ul class="submenu-inner">';
							if($rright['admin'])
							{
								if($_GET['page']=='admin' && $_GET['subpage']=='parameters') {echo '<li class="nav-item active" >';} else {echo '<li class="nav-item" >';} echo '
									<a class="nav-link pl-5" href="./index.php?page=admin&amp;subpage=parameters">
										<i class="nav-icon fa fa-cog"><!----></i>&nbsp;
										'.T_('Paramètres').'
									</a>
								</li>';
								if($_GET['page']=='admin' && $_GET['subpage']=='user') {echo '<li class="nav-item active" >';} else {echo '<li class="nav-item" >';} echo '
									<a class="nav-link pl-5" href="./index.php?page=admin&amp;subpage=user">
										<i class="nav-icon fa fa-user"><!----></i>&nbsp;
										'.T_('Utilisateurs').'
									</a>
								</li>';
							}
							if($rright['admin_groups'] || $rright['admin'])
							{
								if($_GET['page']=='admin' && $_GET['subpage']=='group') {echo '<li class="nav-item active" >';} else {echo '<li class="nav-item" >';} echo '
									<a class="nav-link pl-5" href="./index.php?page=admin&amp;subpage=group">
										<i class="nav-icon fa fa-users"><!----></i>&nbsp;
										'.T_('Groupes').'
									</a>
								</li>';
							}
							if($rright['admin'])
							{
								if($_GET['page']=='admin' && $_GET['subpage']=='profile') {echo '<li class="nav-item active" >';} else {echo '<li class="nav-item" >';} echo '
									<a class="nav-link pl-5" href="./index.php?page=admin&amp;subpage=profile">
										<i class="nav-icon fa fa-lock"><!----></i>&nbsp;
										'.T_('Droits').'
									</a>
								</li>';
							}
							if($rright['admin_lists'] || $rright['admin'])
							{
								if($_GET['page']=='admin' && $_GET['subpage']=='list') {echo '<li class="nav-item active" >';} else {echo '<li class="nav-item" >';} echo '
									<a class="nav-link pl-5" href="./index.php?page=admin&amp;subpage=list">
										<i class="nav-icon fa fa-list"><!----></i>&nbsp;
										'.T_('Listes').'
									</a>
								</li>';
							}
							if($rright['admin'])
							{
								if($rright['admin_backup'])
								{
									if($_GET['page']=='admin' && $_GET['subpage']=='backup') {echo '<li class="nav-item active" >';} else {echo '<li class="nav-item" >';} echo '
										<a class="nav-link pl-5" href="./index.php?page=admin&amp;subpage=backup">
											<i class="nav-icon fa fa-save"><!----></i>&nbsp;
											'.T_('Sauvegardes').'
										</a>
									</li>';
								}
								if($rparameters['update_menu'])
								{
									if($_GET['page']=='admin' && $_GET['subpage']=='update') {echo '<li class="nav-item active" >';} else {echo '<li class="nav-item" >';} echo '
									<a class="nav-link pl-5" href="./index.php?page=admin&amp;subpage=update">
										<i class="nav-icon fa fa-cloud-upload-alt"><!----></i>&nbsp;
										'.T_('Mise à jour').'
										';
										//display warning if new version
										if($rparameters['last_version'] && $rparameters['version']!=$rparameters['last_version'] && $rparameters['version']<$rparameters['last_version'])
										{
											//exclude dedicated
											if(substr_count($rparameters['version'], '.')==2)
											{
												echo '<span title="'.T_("Une version plus récente de l'application est disponible").'" class="fas fa-exclamation-triangle text-120 text-warning-m2 ml-2"><!----></span>';
											}
										}
										echo '
									</a>
									</li>';
								}
								if($_GET['page']=='admin' && $_GET['subpage']=='system') {echo '<li class="nav-item active" >';} else {echo '<li class="nav-item" >';} echo '
									<a class="nav-link pl-5" href="./index.php?page=admin&amp;subpage=system">
										<i class="nav-icon fa fa-desktop"><!----></i>&nbsp;
										'.T_('Système').'
										';
										
										if($rparameters['system_error']) {
											echo '&nbsp;&nbsp;<span title="'.T_("Des erreurs importantes sont détectées, corriger tous les points de couleur rouge").'" class="fas fa-exclamation-triangle text-110 text-danger-m2"><!----></span>';
										}elseif($rparameters['system_warning']) {
											echo '&nbsp;&nbsp;<span title="'.T_("Des avertissements ont été détectées, corriger tous les points de couleur jaune").'" class="fas fa-exclamation-triangle text-110 text-warning-m2"><!----></span>';
										}
										echo '
									</a>
								</li>';
								if($rparameters['log'])
								{
									if($_GET['page']=='admin' && $_GET['subpage']=='log') {echo '<li class="nav-item active" >';} else {echo '<li class="nav-item" >';} echo '
									<a class="nav-link pl-5" href="./index.php?page=admin&amp;subpage=log">
										<i class="nav-icon fa fa-clipboard-list"><!----></i>&nbsp;
										'.T_('Logs').'
									</a>
									</li>';
								}
								if(($_GET['page']=='admin' && $_GET['subpage']=='infos') || $_GET['page']=='changelog') {echo '<li class="nav-item active" >';} else {echo '<li class="nav-item" >';} echo '
									<a class="nav-link pl-5" href="./index.php?page=admin&amp;subpage=infos">
										<i class="nav-icon fa fa-info-circle"><!----></i>&nbsp;
										'.T_('Informations').'
									</a>
								</li>';
							}
							echo '
						</ul>
					</div>
				</li>';
			}
			echo '
		</ul>
	</div><!-- /.sidebar scroll -->
</div>
';
?>
