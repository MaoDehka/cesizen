<?php
################################################################################
# @Name : ./admin/users/list.php
# @Description : list users
# @Call : /admin/user.php
# @Parameters : 
# @Author : Flox
# @Create : 17/09/2021
# @Update : 10/10/2023
# @Version : 3.2.40
################################################################################

//init var for filter
if(!$_POST['lastname']) {$_POST['lastname']=$_GET['lastname'];}
if(!$_POST['login']) {$_POST['login']=$_GET['login'];}
if(!$_POST['company']) {$_POST['company']=$_GET['company'];}
if(!$_POST['agency']) {$_POST['agency']=$_GET['agency'];}
if(!$_POST['service']) {$_POST['service']=$_GET['service'];}
if(!$_POST['mail']) {$_POST['mail']=$_GET['mail'];}
if(!$_POST['phone']) {$_POST['phone']=$_GET['phone'];}
if(!$_POST['profile'] && $_POST['profile']!='0') {$_POST['profile']=$_GET['profile'];}
if(!$_POST['connexion']) {$_POST['connexion']=$_GET['connexion'];}

if($_POST['lastname']) {$_GET['lastname']=$_POST['lastname'];}
if($_POST['login']) {$_GET['login']=$_POST['login'];}
if($_POST['company']) {$_GET['company']=$_POST['company'];}
if($_POST['agency']) {$_GET['agency']=$_POST['agency'];}
if($_POST['service']) {$_GET['service']=$_POST['service'];}
if($_POST['mail']) {$_GET['mail']=$_POST['mail'];}
if($_POST['phone']) {$_GET['phone']=$_POST['phone'];}
if($_POST['profile']) {$_GET['profile']=$_POST['profile'];}
if($_POST['connexion']) {$_GET['connexion']=$_POST['connexion'];}

//init var
$user_disabled=0;
$user_enabled=0;
$user_deleted=0;

//display users list
if($_GET['action']=='')
{
	//display buttons
	echo '
		<div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
			<p>
				<button onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;action=add";\' class="btn btn-success">
					<i class="fa fa-plus"><!----></i> '.T_('Ajouter un utilisateur').'
				</button>
				';
				

		if($rparameters['ldap'] && $rparameters['ldap_agency']==0)
		{
			echo '
				<button id="ldap_button" onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;ldap=1";\' class="btn btn-info">
					<i class="fa fa-sync"><!----></i> '.T_('Synchronisation LDAP').'
				</button>
			';
		}
		if($rparameters['ldap'] && $rparameters['ldap_agency'])
		{
			echo '
				<button onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;ldap=agencies";\' class="btn btn-info">
					<i class="fa fa-sync"><!----></i> '.T_('Synchronisation des agences LDAP').'
				</button>
			';
		}
		if($rparameters['ldap'] && $rparameters['ldap_service'])
		{
			echo '
				<button onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;ldap=services";\' class="btn  btn-info">
					<i class="fa fa-sync"><!----></i> '.T_('Synchronisation des services LDAP').'
				</button>
			';
		}
		if($rparameters['azure_ad'])
		{
			echo '
				<button onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;azure_ad=1";\' class="btn  btn-info">
					<i class="fa fa-sync"><!----></i> '.T_('Synchronisation Entra ID').'
				</button>
			';
		}
		//include plugin
		$section='user_list_btn';
		include('./plugin.php');

	echo'
			</p>
		</div>
	';
	//Display user table
	if($_GET['way']=='DESC') $nextway='ASC'; else $nextway='DESC'; //find next way

	//build query case filter
	if($_POST['lastname'] || $_POST['login'] || $_POST['company'] || $_POST['agency'] || $_POST['service'] || $_POST['mail'] || $_POST['phone'] ||$_POST['profile'] || $_POST['connexion'])
	{
		
		//init values
		if(!$_POST['lastname']) {$_POST['lastname']='%';}
		if(!$_POST['login']) {$_POST['login']='%';}
		if(!$_POST['company']) {$_POST['company']='%';}
		if(!$_POST['agency']) {$_POST['agency']='%';}
		if(!$_POST['service']) {$_POST['service']='%';}
		if(!$_POST['mail']) {$_POST['mail']='%';}
		if(!$_POST['phone']) {$_POST['phone']='%';}
		if(!$_POST['profile'] && $_POST['profile']!='0') {$_POST['profile']='%';}
		if(!$_POST['connexion']) {$_POST['connexion']='%';}

		$from='tusers';
		$where='';
		$join='';
		if($rparameters['user_agency']) {
			if($_POST['agency']!='%')
			{
				$join="
				RIGHT JOIN tusers_agencies ON tusers_agencies.user_id=tusers.id 
				RIGHT JOIN tagencies ON (tagencies.id=tusers_agencies.agency_id AND tagencies.id LIKE :agency_id)
				";
			} else {
				$join="
				LEFT JOIN tusers_agencies ON tusers_agencies.user_id=tusers.id 
				LEFT JOIN tagencies ON (tagencies.id=tusers_agencies.agency_id AND tagencies.id LIKE :agency_id)
				";
			}
		}

		if($_POST['service']!='%')
		{
			$join.="
			RIGHT JOIN tusers_services ON tusers_services.user_id=tusers.id 
			RIGHT JOIN tservices ON (tservices.id=tusers_services.service_id AND tservices.id LIKE :service_id)
			";
		} else {
			$join.="
			LEFT JOIN tusers_services ON tusers_services.user_id=tusers.id 
			LEFT JOIN tservices ON (tservices.id=tusers_services.service_id AND tservices.id LIKE :service_id)
			";
		}
		$join.="		
		LEFT OUTER JOIN tcompany ON tcompany.id=tusers.company ";
		$where.="
		profile LIKE :profile AND
		tusers.id!=:id AND
		tusers.disable=:disable AND
		tusers.login!='delete_user_gs' AND
		(tusers.lastname LIKE :lastname OR tusers.firstname LIKE :firstname) AND
		(tusers.phone LIKE :phone OR tusers.mobile LIKE :mobile ) AND 
		tusers.mail LIKE :mail AND 
		tusers.login LIKE :login AND
		tusers.last_login LIKE :connexion AND
		tcompany.id LiKE :company_id
	
		ORDER BY $db_order $db_way
		LIMIT $db_cursor,$maxline ";

	} else { //build query case search
		$from='tusers';
		$where='';
		$join='';
		if($rparameters['user_agency']) {$join="LEFT OUTER JOIN tusers_agencies ON tusers_agencies.user_id=tusers.id LEFT OUTER JOIN tagencies ON tagencies.id=tusers_agencies.agency_id ";}
		$join.="
		LEFT OUTER JOIN tusers_services ON tusers_services.user_id=tusers.id 
		LEFT OUTER JOIN tservices ON tservices.id=tusers_services.service_id
		LEFT OUTER JOIN tcompany ON tcompany.id=tusers.company ";
		$where.="
		profile LIKE :profile AND
		tusers.id!=:id AND
		tusers.disable=:disable AND
		tusers.login!='delete_user_gs' AND
		(
			";
			if($rparameters['user_agency']) {$where.="tagencies.name LIKE :agency_name OR ";}
			$where.="
			tusers.lastname LIKE :lastname OR
			tusers.firstname LIKE :firstname OR
			tusers.mail LIKE :mail OR
			tusers.phone LIKE :phone OR
			tusers.mobile LIKE :mobile OR
			tusers.login LIKE :login OR
			tservices.name LIKE :service_name  OR
			tcompany.name LIKE :company
		)
		ORDER BY $db_order $db_way
		LIMIT $db_cursor,$maxline ";
	}

	if($rparameters['debug']) {
		$where_debug=str_replace('AND','AND <br>',$where);
		$join_debug=str_replace('LEFT',' <br>LEFT',$join);
		$join_debug=str_replace('RIGHT',' <br>RIGHT',$join);
		echo "
		<b><u>DEBUG MODE:</u></b><br />
		VAR post_profile=$_POST[profile]<br />
		SELECT distinct tusers.* 
		FROM $from
		$join_debug
		<br>
		WHERE<br> $where_debug
		";
	}

	//display list
	echo '
		<ul class="nav nav-tabs bgc-secondary-l3 border-y-1 brc-secondary-l3" role="tablist">
			<li class="nav-item mr-2px">
				<a href="index.php?page=admin&subpage=user&disable=0" id="home1-tab-btn" class="'; if($_GET['disable']==0) {echo 'active';} echo ' d-style btn btn-tp btn-light-success btn-h-white btn-a-text-dark btn-a-white text-95 px-3 px-sm-4 py-25 radius-0 border-0" >
					<span class="v-active position-tl w-100 border-t-3 brc-success mt-n2px"></span>
					<i class="fa fa-check-circle text-success"><!-- --></i> '.T_('Utilisateurs activés').' ('.$active_users[0].')
				</a>
			</li>
			<li class="nav-item mr-2px">
				<a href="index.php?page=admin&subpage=user&disable=1" id="profile1-tab-btn" class="'; if($_GET['disable']==1) {echo 'active';} echo ' d-style btn btn-tp btn-light-secondary btn-h-white btn-a-text-dark btn-a-white text-95 px-3 px-sm-4 py-25 radius-0 border-0" >
					<span class="v-active position-tl w-100 border-t-3 brc-danger mt-n2px"></span>
					<i class="fa fa-ban text-danger"><!-- --></i> '.T_('Utilisateurs désactivés').' ('.$inactive_users[0].')
				</a>
			</li>
		</ul>
		
			<div class="mt-0 mt-lg-0 card bcard h-auto overflow-hidden shadow border-0">
				<div class="table-responsive">
					<table id="sample-table-1" class="table text-dark-m1 brc-black-tp10 mb-1 table-hover">
						<thead>
							<tr class="bgc-white text-secondary-d3 text-95">
								<th class="py-3 pl-35" style="min-width:220px;">
									<a class="text-primary-m2" href="index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;order=lastname&amp;way='.$nextway.'">
										<i class="fa fa-male"><!----></i> '.T_('Nom Prénom').'
										';
											if($_GET['order']=='lastname')
											{
											if($_GET['way']=='ASC')  echo '<i class="fa fa-sort-up"><!----></i>';
											if($_GET['way']=='DESC') echo '<i class="fa fa-sort-down"><!----></i>';
											}
										echo '
									</a>
								</th>
								<th class="py-3 pl-35" style="min-width:140px;">
									<a class="text-primary-m2" href="index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;order=login&amp;way='.$nextway.'">
										<i class="fa fa-user"><!----></i> '.T_('Identifiant').'
										';
											if($_GET['order']=='login')
											{
											if($_GET['way']=='ASC')  echo '<i class="fa fa-sort-up"><!----></i>';
											if($_GET['way']=='DESC') echo '<i class="fa fa-sort-down"><!----></i>';
											}
										echo '
									</a>
								</th>
								';
								if($rparameters['user_advanced'])
								{
									echo '
									<th class="py-3 pl-35" style="min-width:120px;">
										<a class="text-primary-m2" href="index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;order=company&amp;way='.$nextway.'">
											<i class="fa fa-building "><!----></i> '.T_('Société').'
											';
												if($_GET['order']=='company')
												{
												if($_GET['way']=='ASC')  echo '<i class="fa fa-sort-up"><!----></i>';
												if($_GET['way']=='DESC') echo '<i class="fa fa-sort-down"><!----></i>';
												}
											echo '
										</a>
									</th>
									';
								}
								if($rparameters['user_agency'])
								{
									echo '
									<th class="py-3 pl-35" style="min-width:130px;">
										<a class="text-primary-m2" href="index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;order=tagencies.name&amp;way='.$nextway.'">
											<i class="fa fa-globe "><!----></i> '.T_('Agences').'
											';
												if($_GET['order']=='tagencies.name')
												{
												if($_GET['way']=='ASC')  echo '<i class="fa fa-sort-up"><!----></i>';
												if($_GET['way']=='DESC') echo '<i class="fa fa-sort-down"><!----></i>';
												}
											echo '
										</a>
									</th>
									';
								}
								echo '
								<th class="py-3 pl-35" style="min-width:120px;">
									<a class="text-primary-m2"href="index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;order=tservices.name&amp;way='.$nextway.'">
										<i class="fa fa-users"><!----></i> '.T_('Services').'
										';
											if($_GET['order']=='tservices.name')
											{
											if($_GET['way']=='ASC')  echo '<i class="fa fa-sort-up"><!----></i>';
											if($_GET['way']=='DESC') echo '<i class="fa fa-sort-down"><!----></i>';
											}
										echo '
									</a>
								</th>
								<th class="py-3 pl-35">
									<a class="text-primary-m2" href="index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;order=tusers.mail&amp;way='.$nextway.'">
										<i class="fa fa-envelope"><!----></i> '.T_('Mail').'
										';
											if($_GET['order']=='tusers.mail')
											{
											if($_GET['way']=='ASC')  echo '<i class="fa fa-sort-up"><!----></i>';
											if($_GET['way']=='DESC') echo '<i class="fa fa-sort-down"><!----></i>';
											}
										echo '
									</a>
								</th>
								<th class="py-3 pl-35" style="min-width:140px;">
									<a class="text-primary-m2" href="index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;order=phone&amp;way='.$nextway.'">
										<i class="fa fa-phone"><!----></i> '.T_('Téléphone').'
										';
											if($_GET['order']=='phone')
											{
											if($_GET['way']=='ASC')  echo '<i class="fa fa-sort-up"><!----></i>';
											if($_GET['way']=='DESC') echo '<i class="fa fa-sort-down"><!----></i>';
											}
										echo '
									</a>
								</th>
								<th class="py-3 pl-35" style="min-width:100px;">
									<a class="text-primary-m2" href="index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;order=profile&amp;way='.$nextway.'">
										<i class="fa fa-lock"><!----></i> '.T_('Profil').'
										';
											if($_GET['order']=='profile')
											{
											if($_GET['way']=='ASC')  echo '<i class="fa fa-sort-up"><!----></i>';
											if($_GET['way']=='DESC') echo '<i class="fa fa-sort-down"><!----></i>';
											}
										echo '
									</a>
								</th>
								<th class="py-3 pl-35" style="min-width:160px;">
									<a class="text-primary-m2" href="index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;order=last_login&amp;way='.$nextway.'">
										<i class="fa fa-key"><!----></i> '.T_('Connexion').'
										';
											if($_GET['order']=='last_login')
											{
											if($_GET['way']=='ASC')  echo '<i class="fa fa-sort-up"><!----></i>';
											if($_GET['way']=='DESC') echo '<i class="fa fa-sort-down"><!----></i>';
											}
										echo '
									</a>
								</th>
								<th class="py-3 pl-35" style="min-width:110px;">
									<a class="text-primary-m2" href="index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;order=lastname&amp;way='.$nextway.'">
										<i class="fa fa-play"><!----></i> '.T_('Actions').'&nbsp;&nbsp;
									</a>
								</th>
							</tr>
							<!-- filter line -->
							<tr>
								<form name="user_filter" id="user_filter" method="POST" >
									<th>
										<input name="lastname" id="lastname" class="form-control form-control-sm" onchange="submit();" value="'; if($_POST['lastname']!='%') {echo $_POST['lastname'];} echo'" >		
									</th>
									<th>
										<input name="login" id="login" class="form-control form-control-sm" onchange="submit();" value="'; if($_POST['login']!='%') {echo $_POST['login'];} echo'" >		
									</th>
									';
									if($rparameters['user_advanced'])
									{
										echo '
										<th>
											<select form="user_filter" class="form-control form-control-sm" name="company" id="company" onchange="submit();" autocomplete="off">
												<option value=""></option>
												<option value="%">&nbsp;</option>
												';
												$qry=$db->prepare("SELECT `id`,`name` FROM `tcompany` WHERE id!=0 ORDER BY `name`");
												$qry->execute();
												while($row=$qry->fetch()) 
												{
													if($_POST['company']==$row['id']) {
														echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
													} else {
														echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
													}
												}
												$qry->closeCursor();
												echo ' 
											</select>		
										</th>
										';
									}
									if($rparameters['user_agency'])
									{
										echo '
										<th>
											<select class="form-control form-control-sm" name="agency" id="agency" onchange="submit();">
												<option value="%">'.T_('Toutes').'</option>
												';
												$qry=$db->prepare("SELECT `id`,`name` FROM `tagencies` WHERE id!=0 ORDER BY `name`");
												$qry->execute();
												while($row=$qry->fetch()) 
												{
													if($_POST['agency']==$row['id']) {
														echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
													} else {
														echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
													}
												}
												$qry->closeCursor();
												echo ' 
											</select>		
										</th>
										';
									}
									echo '
									<th>
										<select class="form-control form-control-sm" name="service" id="service" onchange="submit();">
											<option value="%">'.T_('Tous').'</option>
											';
											$qry=$db->prepare("SELECT `id`,`name` FROM `tservices` WHERE id!=0 ORDER BY `name` ");
											$qry->execute();
											while($row=$qry->fetch()) 
											{
												if($_POST['service']==$row['id']) {
													echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
												} else {
													echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
												}
											}
											$qry->closeCursor();
											echo ' 
										</select>		
									</th>
									<th>
										<input name="mail" id="mail" class="form-control form-control-sm" onchange="submit();" value="'; if($_POST['mail']!='%') {echo $_POST['mail'];} echo'" >		
									</th>
									<th>
										<input name="phone" id="phone" class="form-control form-control-sm" onchange="submit();" value="'; if($_POST['phone']!='%') {echo $_POST['phone'];} echo'" autocomplete="on">		
									</th>
									<th>
										<select class="form-control form-control-sm" name="profile" id="profile" onchange="submit();">
											<option value="%" '; if($_POST['profile']=='%') {echo 'selected';} echo ' >'.T_('Tous').'</option>
											<option value="2" '; if($_POST['profile']=='2') {echo 'selected';} echo ' >'.T_('Utilisateur').'</option>
											<option value="1" '; if($_POST['profile']=='1') {echo 'selected';} echo ' >'.T_('Utilisateur avec pouvoir').'</option>
											<option value="0" '; if($_POST['profile']=='0') {echo 'selected';} echo ' >'.T_('Technicien').'</option>
											<option value="3" '; if($_POST['profile']=='3') {echo 'selected';} echo ' >'.T_('Superviseur').'</option>
											<option value="4" '; if($_POST['profile']=='4') {echo 'selected';} echo ' >'.T_('Administrateur').'</option>
										</select>
									</th>
									<th>
										<input name="connexion" id="connexion" class="form-control form-control-sm" onchange="submit();" value="'; if($_POST['connexion']!='%') {echo $_POST['connexion'];} echo'" >		
									</th>
									<th>
											<!-- action col -->
									</th>
								</form>
							</tr>
						</thead>
						<tbody>
							<form name="actionlist" id="actionlist" method="POST"> 
							';
							
							//build each line masterquery 
							$qry = $db->prepare("
								SELECT distinct tusers.* 
								FROM $from
								$join
								WHERE $where
							");

							//exec qry filter case			
							if($_POST['lastname'] || $_POST['login'] || $_POST['company'] || $_POST['agency'] || $_POST['service'] || $_POST['mail'] || $_POST['phone'] || $_POST['profile'] || $_POST['profile']=='0' || $_POST['connexion'] )
							{
								//remove space of single word filter
								if(str_word_count($_POST['lastname'],0)==1) {$_POST['lastname']=trim($_POST['lastname']);}

								if($_POST['profile'] || $_POST['profile']=='0') {$db_profile=$_POST['profile'];} else {$db_profile='%';}
								if($_POST['agency']) {$db_agency=$_POST['agency'];} else {$db_agency='%';}
								if($_POST['lastname']) {$db_lastname='%'.$_POST['lastname'].'%';} else {$db_lastname='%';}
								if($_POST['lastname']) {$db_firstname='%'.$_POST['lastname'].'%';} else {$db_firstname='%';}
								if($_POST['mail']) {$db_mail=$_POST['mail'];} else {$db_mail='%';}
								if($_POST['phone']) {$db_phone=$_POST['phone'];} else {$db_phone='%';}
								if($_POST['phone']) {$db_mobile=$_POST['phone'];} else {$db_mobile='%';}
								if($_POST['login']) {$db_login=$_POST['login'];} else {$db_login='%';}
								if($_POST['connexion']) {$db_connexion=$_POST['connexion'];} else {$db_connexion='%';}
								if($_POST['service']) {$db_service=$_POST['service'];} else {$db_service='%';}
								if($_POST['company']) {$db_company=$_POST['company']; $company_name='%';} else {$db_company='%';}

								if($rparameters['user_agency']) //agency case add name in searchengine
								{
									$qry->execute(array(
										'profile' => $db_profile,
										'id' => 0,
										'disable' => $_GET['disable'],
										'agency_id' => $db_agency,
										'lastname' => $db_lastname,
										'firstname' => $db_firstname,
										'mail' => $db_mail,
										'phone' => $db_phone,
										'mobile' => $db_mobile,
										'login' => $db_login,
										'connexion' => $db_connexion,
										'service_id' => $db_service,
										'company_id' => $db_company,
									));

									if($rparameters['debug'])
									{
										echo "VAR: profile=$db_profile _GET['disable']=$_GET[disable] db_agency=$db_agency db_lastname=$db_lastname db_firstname=$db_firstname db_mail=$db_mail db_phone=$db_phone db_mobile=$db_mobile db_login=$db_login db_service=$db_service db_company=$db_company ";
									}
								} else {
									$qry->execute(array(
										'profile' => $db_profile,
										'id' => 0,
										'disable' => $_GET['disable'],
										'lastname' => $db_lastname,
										'firstname' => $db_firstname,
										'mail' => $db_mail,
										'phone' => $db_phone,
										'mobile' => $db_mobile,
										'login' => $db_login,
										'connexion' => $db_connexion,
										'service_id' => $db_service,
										'company_id' => $db_company,
									));
								}
							} else { //exec qry search case
								//remove space when one single word search
								if(str_word_count($userkeywords,0)==1) {$userkeywords=trim($userkeywords);}

								if($rparameters['user_agency']) //agency case add name in searchengine
								{
									$qry->execute(array(
										'profile' => $_GET['profileid'],
										'id' => 0,
										'disable' => $_GET['disable'],
										'agency_name' => "%$userkeywords%",
										'lastname' => "%$userkeywords%",
										'firstname' => "%$userkeywords%",
										'mail' => "%$userkeywords%",
										'phone' => "%$userkeywords%",
										'mobile' => "%$userkeywords%",
										'login' => "%$userkeywords%",
										'service_name' => "%$userkeywords%",
										'company' => "%$userkeywords%",
									));
								} else {
									
									$qry->execute(array(
										'profile' => $_GET['profileid'],
										'id' => 0,
										'disable' => $_GET['disable'],
										'lastname' => "%$userkeywords%",
										'firstname' => "%$userkeywords%",
										'mail' => "%$userkeywords%",
										'phone' => "%$userkeywords%",
										'mobile' => "%$userkeywords%",
										'login' => "%$userkeywords%",
										'service_name' => "%$userkeywords%",
										'company' => "%$userkeywords%",
									));
								}
							}

							while ($row = $qry->fetch()) 
							{
								//check box selection SQL updates
								if($_POST['selectrow'] && $rright['admin'])
								{
									if(!isset($_POST['checkbox'.$row["id"]])) $_POST['checkbox'.$row["id"]] = ''; 
									$user_selected=$_POST['checkbox'.$row['id']];

									if($user_selected!='') 
									{
										//disable users
										if($_POST['selectrow']=='disable')
										{
											$qry2=$db->prepare("UPDATE `tusers` SET `disable`='1' WHERE `id`=:id");
											$qry2->execute(array('id' => $user_selected));
											if($rparameters['log'])
											{
												if(is_numeric($_GET['userid']))
												{
													$qry2=$db->prepare("SELECT `login` FROM `tusers` WHERE id=:id");
													$qry2->execute(array('id' => $user_selected));
													$row2=$qry2->fetch();
													$qry2->closeCursor();
													
													require_once('core/functions.php');
													LogIt('security', 'User '.$row2['login'].' disabled',$_SESSION['user_id']);
												}
											}
											$user_disabled=1;
										}
										//enable users
										if($_POST['selectrow']=='enable')
										{
											$qry2=$db->prepare("UPDATE `tusers` SET `disable`='0' WHERE `id`=:id");
											$qry2->execute(array('id' => $user_selected));
											if($rparameters['log'])
											{
												if(is_numeric($_GET['userid']))
												{
													$qry2=$db->prepare("SELECT `login` FROM `tusers` WHERE id=:id");
													$qry2->execute(array('id' => $user_selected));
													$row2=$qry2->fetch();
													$qry2->closeCursor();
													
													require_once('core/functions.php');
													LogIt('security', 'User '.$row2['login'].' enabled',$_SESSION['user_id']);
												}
											}
											$user_enabled=1;
										}
										//delete users
										if($_POST['selectrow']=='delete')
										{
											DeleteUser($user_selected);
											$user_deleted=1;
										}
									}
								}

								//find profile name
								$qry2 = $db->prepare("SELECT `name` FROM `tprofiles` WHERE level=:level");
								$qry2->execute(array('level' => $row['profile']));
								$r=$qry2->fetch();
								$qry2->closeCursor();
								//display last login if exist
								if($row['last_login']=='0000-00-00 00:00:00') {$lastlogin='';} else {$lastlogin=substr($row['last_login'],0,16);}
								//first letter of lastname
								$firstname_letter=mb_strtoupper(mb_substr($row['firstname'],0,1));
								if(empty($firstname_letter)) {$firstname_letter='';}
								$lastname_letter=mb_strtoupper(mb_substr($row['lastname'],0,1));
								if(empty($lastname_letter)) {$lastname_letter='';}
								if($row['profile']==0)$lastname_letter_color='bgc-grey';
								if($row['profile']==1)$lastname_letter_color='bgc-green';
								if($row['profile']==2)$lastname_letter_color='bgc-primary';
								if($row['profile']==3)$lastname_letter_color='bgc-orange';
								if($row['profile']==4)$lastname_letter_color='bgc-dark';
								
								echo '
									<tr class="bgc-h-orange-l4">
										<td class="text-secondary-d2 text-95 text-600" >
											';
											//checkbox
											if($_POST['selectrow']=='selectall') {$checked='checked';} else {$checked='';}

											echo '<input '.$checked.' form="actionlist" class="mt-1" type="checkbox" name="checkbox'.$row['id'].'" value="'.$row['id'].'">';

											echo '
											<span onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;action=edit&amp;userid='.$row['id'].'&amp;tab=infos&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;cursor='.$_GET['cursor'].'";\' class="d-inline-block text-center mr-2 pt-1 w-4 h-4 radius-round '.$lastname_letter_color.' text-white font-bolder text-100">'.$firstname_letter.$lastname_letter.'</span>
											<span onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;action=edit&amp;userid='.$row['id'].'&amp;tab=infos&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;cursor='.$_GET['cursor'].'";\' >'.mb_strtoupper($row['lastname']).' '.ucfirst(mb_substr($row['firstname'],0,1)).mb_strtolower(mb_substr($row['firstname'],1)).'</span>
										</td>
										<td class="text-dark-m3" onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;action=edit&amp;userid='.$row['id'].'&amp;tab=infos&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;cursor='.$_GET['cursor'].'";\' >'.$row['login'].'</td>
										';
										if($rparameters['user_advanced']==1) {
											//get company name
											$qry2 = $db->prepare("SELECT `name` FROM `tcompany` WHERE id=:id");
											$qry2->execute(array('id' => $row['company']));
											$row2=$qry2->fetch();
											$qry2->closeCursor();
											if(empty($row2['name'])) {$row2['name']='';}
											echo '<td class="text-dark-m3" onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;action=edit&amp;userid='.$row['id'].'&amp;tab=infos&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;cursor='.$_GET['cursor'].'";\' >'.$row2['name'].'</td>';
										}
										if($rparameters['user_agency']==1) {
											//get agencies name
											echo '<td class="text-dark-m3" onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;action=edit&amp;userid='.$row['id'].'&amp;tab=infos&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;cursor='.$_GET['cursor'].'";\' >';
											$qry2 = $db->prepare("SELECT `agency_id` FROM `tusers_agencies` WHERE user_id=:user_id");
											$qry2->execute(array('user_id' => $row['id']));
											while ($row2=$qry2->fetch())
											{
												$qry3 = $db->prepare("SELECT `name` FROM `tagencies` WHERE id=:id");
												$qry3->execute(array('id' => $row2['agency_id']));
												$row3 = $qry3->fetch();
												echo "$row3[name]<br />";
												$qry3->closecursor();
											}
											$qry2->closecursor();
											echo '</td>';
										}
										echo '
										<td class="text-dark-m3" onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;action=edit&amp;userid='.$row['id'].'&amp;tab=infos&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;cursor='.$_GET['cursor'].'";\' >
										';
											$qry2 = $db->prepare("SELECT `service_id` FROM `tusers_services` WHERE user_id=:user_id");
											$qry2->execute(array('user_id' => $row['id']));
											while ($row2=$qry2->fetch())
											{
												$qry3 = $db->prepare("SELECT `name` FROM `tservices` WHERE id=:id");
												$qry3->execute(array('id' => $row2['service_id']));
												$row3 = $qry3->fetch();
												if(empty($row3['name'])) {$row3['name']='';}
												echo "$row3[name]<br />";
												$qry3->closecursor();
											}
											$qry2->closecursor();
										echo'
										</td>
										<td class="text-dark-m3" onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;action=edit&amp;userid='.$row['id'].'&amp;tab=infos&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;cursor='.$_GET['cursor'].'";\' >'.$row['mail'].'</td>
										<td class="text-dark-m3" onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;action=edit&amp;userid='.$row['id'].'&amp;tab=infos&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;cursor='.$_GET['cursor'].'";\' >'.$row['phone'].' '.$row['mobile'].'</td>
										<td class="text-dark-m3" onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;action=edit&amp;userid='.$row['id'].'&amp;tab=infos&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;cursor='.$_GET['cursor'].'";\' >'.T_($r['name']).'</td>
										<td class="text-dark-m3" onclick=\'window.location.href="index.php?page=admin&amp;subpage=user&amp;action=edit&amp;userid='.$row['id'].'&amp;tab=infos&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;cursor='.$_GET['cursor'].'";\' >'.$lastlogin.'</td>
										<td class="text-dark-m3">
										
											<a class="action-btn btn btn-sm btn-warning mr-1" href="index.php?page=admin&amp;subpage=user&amp;action=edit&amp;userid='.$row['id'].'&amp;tab=infos&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;cursor='.$_GET['cursor'].'"  title="'.T_("Modifier l'utilisateur").'" ><i style="color:#FFF;" class="fa fa-pencil-alt"><!----><!----></i></a>';
											if(($row['disable']!=1) && ($row['id']!=$_SESSION['user_id']))
											{
												echo '<a class="action-btn btn btn-sm btn-danger" href="index.php?page=admin&amp;subpage=user&amp;userid='.$row['id'].'&amp;action=disable"  title="'.T_("Désactiver l'utilisateur").'" ><i class="fa fa-ban "><!----><!----></i></a>';
											} elseif($row['id']!=$_SESSION['user_id'])
											{
												echo '<a class="action-btn btn btn-sm btn-success mr-1" href="index.php?page=admin&amp;subpage=user&amp;userid='.$row['id'].'&amp;action=enable"  title="'.T_("Activer l'utilisateur").'" ><i class="fa fa-check"><!----><!----></i></a>';
											}
											if($rright['admin'] && $row['disable']==1)
											{
												echo '<a class="action-btn btn btn-sm btn-danger" onClick="javascript: return confirm(\''.T_('Êtes-vous sur de vouloir supprimer définitivement cet utilisateur ? information également supprimée sur tous les tickets et dans tous le logiciel').'\');" href="index.php?page=admin&amp;subpage=user&amp;userid='.$row['id'].'&amp;action=delete&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;cursor='.$_GET['cursor'].'"  title="'.T_("Supprimer l'utilisateur").'" ><i class="fa fa-trash"><!----></i></a>';
											}
											echo '
										
										</td>
									</tr>
								';
							}
							$qry->closecursor();
							echo '
							</form>
						</tbody>
					</table>
				</div>
			</div>
	';

	//redirect if user disable checkbox
	if($user_disabled || $user_enabled || $user_deleted)
	{
		//redirect
		$url=$_SERVER['QUERY_URI'];
		$url=preg_replace('/%/','%25',$url);
		echo '<script language="Javascript">
		<!--
		document.location.replace("'.$url.'");
		// -->
		</script>';
	}

	//display action list
	echo '
		<div class="row">
			<i class="fa fa-level-down-alt fa-rotate-180 text-130 mb-3 ml-2 mr-2 pr-4 text-secondary-d2 "><!----></i>
			<select form="actionlist" style="width:auto" class="form-control form-control-sm mt-4" title="'.T_('Effectue des actions pour les utilisateurs sélectionnés').'" name="selectrow" onchange="if(confirm(\'Êtes-vous sûr de réaliser cette opération ?\')) this.form.submit();">
				<option value=""></option>
				<option value="selectall">'.T_('Sélectionner tout les utilisateurs de la page').'</option>
				';
				if($_GET['disable']!=1) {echo '<option value="disable">'.T_('Désactiver les utilisateurs sélectionnés').'</option>';}
				if($_GET['disable']==1) {echo '<option value="enable">'.T_('Activer les utilisateurs sélectionnés').'</option>';}
				if($_GET['disable']==1) {echo '<option value="delete">'.T_('Supprimer définitivement les utilisateurs sélectionnés').'</option>';}
				echo '
			</select>
		</div>
	';

	//multi-pages link
	if(!$_GET['cursor'])
	{
		$qry = $db->prepare("
			SELECT COUNT(DISTINCT tusers.id)
			FROM $from
			$join
			WHERE $where
		");

		//exec qry filter case			
		if($_POST['lastname'] || $_POST['login'] || $_POST['company'] || $_POST['agency'] || $_POST['service'] || $_POST['mail'] || $_POST['phone'] ||$_POST['profile'] || $_POST['connexion'] )
		{
			if($_POST['profile']) {$db_profile=$_POST['profile'];} else {$db_profile='%';}
			if($_POST['agency']) {$db_agency=$_POST['agency'];} else {$db_agency='%';}
			if($_POST['lastname']) {$db_lastname="%$_POST[lastname]%";} else {$db_lastname='%';}
			if($_POST['lastname']) {$db_firstname="%$_POST[lastname]%";} else {$db_firstname='%';}
			if($_POST['mail']) {$db_mail=$_POST['mail'];} else {$db_mail='%';}
			if($_POST['phone']) {$db_phone=$_POST['phone'];} else {$db_phone='%';}
			if($_POST['phone']) {$db_mobile=$_POST['phone'];} else {$db_mobile='%';}
			if($_POST['login']) {$db_login=$_POST['login'];} else {$db_login='%';}
			if($_POST['connexion']) {$db_connexion=$_POST['connexion'];} else {$db_connexion='%';}
			if($_POST['service']) {$db_service=$_POST['service'];} else {$db_service='%';}
			if($_POST['company']) {$db_company=$_POST['company']; $company_name='%';} else {$db_company='%';}

			if($rparameters['user_agency']) //agency case add name in searchengine
			{
				$qry->execute(array(
					'profile' => $db_profile,
					'id' => 0,
					'disable' => $_GET['disable'],
					'agency_id' => $db_agency,
					'lastname' => $db_lastname,
					'firstname' => $db_firstname,
					'mail' => $db_mail,
					'phone' => $db_phone,
					'mobile' => $db_mobile,
					'login' => $db_login,
					'connexion' => $db_connexion,
					'service_id' => $db_service,
					'company_id' => $db_company,
				));
			} else {
				$qry->execute(array(
					'profile' => $db_profile,
					'id' => 0,
					'disable' => $_GET['disable'],
					'lastname' => $db_lastname,
					'firstname' => $db_firstname,
					'mail' => $db_mail,
					'phone' => $db_phone,
					'mobile' => $db_mobile,
					'login' => $db_login,
					'connexion' => $db_connexion,
					'service_id' => $db_service,
					'company_id' => $db_company,
				));
			}
		} else { //exec qry search case
			if($rparameters['user_agency']) //agency case add name in searchengine
			{
				$qry->execute(array(
					'profile' => $_GET['profileid'],
					'id' => 0,
					'disable' => $_GET['disable'],
					'agency_name' => "%$userkeywords%",
					'lastname' => "%$userkeywords%",
					'firstname' => "%$userkeywords%",
					'mail' => "%$userkeywords%",
					'phone' => "%$userkeywords%",
					'mobile' => "%$userkeywords%",
					'login' => "%$userkeywords%",
					'service_name' => "%$userkeywords%",
					'company' => "%$userkeywords%",
				));
			} else {
				$qry->execute(array(
					'profile' => $_GET['profileid'],
					'id' => 0,
					'disable' => $_GET['disable'],
					'lastname' => "%$userkeywords%",
					'firstname' => "%$userkeywords%",
					'mail' => "%$userkeywords%",
					'phone' => "%$userkeywords%",
					'mobile' => "%$userkeywords%",
					'login' => "%$userkeywords%",
					'service_name' => "%$userkeywords%",
					'company' => "%$userkeywords%",
				));
			}
		}
	}
	else
	{
		//exec qry filter case			
		if($_POST['lastname'] || $_POST['login'] || $_POST['company'] || $_POST['agency'] || $_POST['service'] || $_POST['mail'] || $_POST['phone'] ||$_POST['profile'] || $_POST['connexion'] )
		{
			if($rparameters['debug'])
			{
				$where_debug=str_replace('AND','AND<br />',$where);
				echo "
				SELECT COUNT(DISTINCT tusers.id)
				FROM $from
				$join
				WHERE $where_debug
				";
			}
			$qry = $db->prepare("
			SELECT COUNT(DISTINCT tusers.id)
			FROM $from
			$join
			WHERE 
			profile LIKE :profile AND
			tusers.id!=:id AND tusers.disable=:disable AND
			tusers.login!='delete_user_gs' AND
			(tusers.lastname LIKE :lastname OR tusers.firstname LIKE :firstname) AND
			(tusers.phone LIKE :phone OR tusers.mobile LIKE :mobile ) AND
			tusers.mail LIKE :mail AND
			tusers.login LIKE :login AND
			tusers.last_login LIKE :connexion AND
			tcompany.id LiKE :company_id
			");

			if($_POST['profile']) {$db_profile=$_POST['profile'];} else {$db_profile='%';}
			if($_POST['agency']) {$db_agency=$_POST['agency'];} else {$db_agency='%';}
			if($_POST['lastname']) {$db_lastname="%$_POST[lastname]%";} else {$db_lastname='%';}
			if($_POST['lastname']) {$db_firstname="%$_POST[lastname]%";} else {$db_firstname='%';}
			if($_POST['mail']) {$db_mail=$_POST['mail'];} else {$db_mail='%';}
			if($_POST['phone']) {$db_phone=$_POST['phone'];} else {$db_phone='%';}
			if($_POST['phone']) {$db_mobile=$_POST['phone'];} else {$db_mobile='%';}
			if($_POST['login']) {$db_login=$_POST['login'];} else {$db_login='%';}
			if($_POST['connexion']) {$db_connexion=$_POST['connexion'];} else {$db_connexion='%';}
			if($_POST['service']) {$db_service=$_POST['service'];} else {$db_service='%';}
			if($_POST['company']) {$db_company=$_POST['company']; $company_name='%';} else {$db_company='%';}

			if($rparameters['debug'])
			{
				echo "<br />VAR: profile=$db_profile _GET['disable']=$_GET[disable] db_agency=$db_agency db_lastname=$db_lastname db_firstname=$db_firstname db_mail=$db_mail db_phone=$db_phone db_mobile=$db_mobile db_login=$db_login db_service=$db_service db_company=$db_company ";
			}

			if($rparameters['user_agency']) //agency case add name in searchengine
			{
				$qry->execute(array(
					'profile' => $db_profile,
					'id' => 0,
					'disable' => $_GET['disable'],
					'agency_id' => $db_agency,
					'lastname' => $db_lastname,
					'firstname' => $db_firstname,
					'mail' => $db_mail,
					'phone' => $db_phone,
					'mobile' => $db_mobile,
					'login' => $db_login,
					'connexion' => $db_connexion,
					'service_id' => $db_service,
					'company_id' => $db_company,
				));
			} else {
				$qry->execute(array(
					'profile' => $db_profile,
					'id' => 0,
					'disable' => $_GET['disable'],
					'lastname' => $db_lastname,
					'firstname' => $db_firstname,
					'mail' => $db_mail,
					'phone' => $db_phone,
					'mobile' => $db_mobile,
					'login' => $db_login,
					'connexion' => $db_connexion,
					'service_id' => $db_service,
					'company_id' => $db_company,
				));
			}
		} else { //exec qry search case
			$qry = $db->prepare("
			SELECT COUNT(DISTINCT tusers.id)
			FROM $from
			$join
			WHERE 
			tusers.disable=:disable
			");
			$qry->execute(array('disable' => $_GET['disable']));
		}
	}
				
	$resultcount = $qry->fetch();
	
	//multi-pages link
	if($resultcount[0]>$rparameters['maxline'])
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
						echo '<li class="page-item"><a class="page-link" title="'.T_('Page précédente').'" href="./index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor='.$cursor.'"><i class="fa fa-arrow-left"><!----></i></a></li>';
					}
					//display first page
					if($_GET['cursor']==0){$active='active';} else {$active='';}
					echo '<li class="page-item '.$active.'"><a class="page-link" title="'.T_('Première page').'" href="./index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor=0">&nbsp;1&nbsp;</a></li>';
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
						if($page!=$max_page) echo '<li class="page-item '.$active.'"><a class="page-link" title="'.T_('Page').' '.$page.'" href="./index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor='.$cursor.'">&nbsp;'.$page.'&nbsp;</a></li>';
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
					echo '<li class="page-item '.$active.'"><a class="page-link" title="'.T_('Dernière page').'" href="./index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor='.$cursor.'">&nbsp;'.$total_page.'&nbsp;</a></li>';
					//display next button if it's not the last page
					if($_GET['cursor']<($resultcount[0]-$rparameters['maxline']))
					{
						$cursor=$_GET['cursor']+$rparameters['maxline'];
						echo '<li class="page-item"><a class="page-link" title="'.T_('Page suivante').'" href="./index.php?page=admin&amp;subpage=user&amp;disable='.$_GET['disable'].'&amp;lastname='.$_GET['lastname'].'&amp;login='.$_GET['login'].'&amp;company='.$_GET['company'].'&amp;agency='.$_GET['agency'].'&amp;service='.$_GET['service'].'&amp;mail='.$_GET['mail'].'&amp;phone='.$_GET['phone'].'&amp;profile='.$_GET['profile'].'&amp;connexion='.$_GET['connexion'].'&amp;order='.$_GET['order'].'&amp;way='.$_GET['way'].'&amp;cursor='.$cursor.'"><i class="fa fa-arrow-right"><!----></i></a></li>';
					}
					echo '
				</ul>
			</nav>
		</div>
	';
	}
}
?>