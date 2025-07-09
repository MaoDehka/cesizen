<?php
################################################################################
# @Name : ./admin/lists/display.php
# @Description : display tables
# @Call : /admin/list.php
# @Parameters : 
# @Author : Flox
# @Create : 10/08/2021
# @Update : 26/03/2024
# @Version : 3.2.49
################################################################################

//delete values
if(($rright['admin'] || $rright['admin_groups'] || $rright['admin_lists'] || $rright['admin_lists_category'] || $rright['admin_lists_subcat'] || $rright['admin_lists_criticality'] || $rright['admin_lists_priority']) && $_GET['action']=="delete") 
{
	if($_GET['table']=='tstates' && $_GET['id'])
	{
		//update state on tickets  before delete state
		$qry=$db->prepare("UPDATE `tincidents` SET `state`='1' WHERE `state`=:state");
		$qry->execute(array('state' => $_GET['id']));
	}
	if($_GET['table']=='tcompany' && $_GET['id'])
	{
		//update company on user table before delete company
		$qry=$db->prepare("UPDATE `tusers` SET `company`='0' WHERE `company`=:company");
		$qry->execute(array('company' => $_GET['id']));
	}
	if($_GET['table']=='tservices' && $_GET['id'])
	{
		//delete user association before delete service
		$qry=$db->prepare("DELETE FROM `tusers_services` WHERE `service_id`=:service_id");
		$qry->execute(array('service_id' => $_GET['id']));

		//update on category table
		$qry=$db->prepare("UPDATE `tcategory` SET `service`='0' WHERE `service`=:service");
		$qry->execute(array('service' => $_GET['id']));
	}
	if($_GET['table']=='tagencies' && $_GET['id'])
	{
		//delete user association before delete agency
		$qry=$db->prepare("DELETE FROM `tusers_agencies` WHERE `agency_id`=:agency_id");
		$qry->execute(array('agency_id' => $_GET['id']));
	}
	if($_GET['table']=='tcategory' && $_GET['id'])
	{
		//update user validation exclusion before delete category
		$qry=$db->prepare("DELETE FROM `tparameters_user_validation_exclusion` WHERE `category`=:category");
		$qry->execute(array('category' => $_GET['id']));

		//update procedure association before delete category
		$qry=$db->prepare("UPDATE `tprocedures` SET `category`='0' WHERE `category`=:category");
		$qry->execute(array('category' => $_GET['id']));

		//update subcat association before delete category
		$qry=$db->prepare("UPDATE `tsubcat` SET `cat`='0' WHERE `cat`=:cat");
		$qry->execute(array('cat' => $_GET['id']));

		//update tincidents association before delete category
		$qry=$db->prepare("UPDATE `tincidents` SET `category`='0' WHERE `category`=:category");
		$qry->execute(array('category' => $_GET['id']));
	}
	if($_GET['table']=='tsubcat' && $_GET['id'])
	{
		//update user validation exclusion before delete category
		$qry=$db->prepare("DELETE FROM `tparameters_user_validation_exclusion` WHERE `subcat`=:subcat");
		$qry->execute(array('subcat' => $_GET['id']));

		//update procedure association before delete category
		$qry=$db->prepare("UPDATE `tprocedures` SET `subcat`='0' WHERE `subcat`=:subcat");
		$qry->execute(array('subcat' => $_GET['id']));

		//update tincidents association before delete subcategory
		$qry=$db->prepare("UPDATE `tincidents` SET `subcat`='0' WHERE `subcat`=:subcat");
		$qry->execute(array('subcat' => $_GET['id']));
	}
	if($_GET['table']=='tcriticality' && $_GET['id'])
	{
		//update tincidents value before delete criticality
		$qry=$db->prepare("UPDATE `tincidents` SET `criticality`='0' WHERE `criticality`=:criticality");
		$qry->execute(array('criticality' => $_GET['id']));
	}

	//log
	LogIt('admin','Delete entry from admin list '.$db_table.' id '.$db_id.' deleted',$_SESSION['user_id']);
	
	$db->exec("DELETE FROM $db_table WHERE id = $db_id");
	$www = "./index.php?page=admin&subpage=list&table=$_GET[table]&action=disp_list";
	echo '<script language="Javascript">
	<!--
	document.location.replace("'.$www.'");
	// -->
	</script>';
}

if($_GET['action']=="disp_list")
{
	//check right before display list
	if(
		($rright['admin']!='0') ||
		(
			($_GET['subpage']=='group' && $rright['admin_groups']!=0) ||
			($_GET['subpage']=='list' && $_GET['table']=='tcategory' && $rright['admin_lists_category']!='0') ||
			($_GET['subpage']=='list' && $_GET['table']=='tsubcat' && $rright['admin_lists_subcat']!='0') ||
			($_GET['subpage']=='list' && $_GET['table']=='tcriticality' && $rright['admin_lists_criticality']!='0') ||
			($_GET['subpage']=='list' && $_GET['table']=='tpriority' && $rright['admin_lists_priority']!='0') ||
			($_GET['subpage']=='list' && $_GET['table']=='ttypes' && $rright['admin_lists_type']!='0')
		)
	)
	{
		//hide disable entries button
		$qry=$db->query("SHOW COLUMNS FROM $db_table LIKE 'disable'");
		$row=$qry->fetch();
		$qry->closeCursor();
		if(!empty($row[0]))
		{
			if($_GET['hide_disabled_values'])
			{
				$hide_disable_button='
				<button onclick=\'window.location.href="./index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_list&amp;hide_disabled_values=0";\' class="btn action-btn btn-info">
						<i class="fa fa-eye"><!----></i> '.T_('Afficher les entrées désactivées').'
				</button>
				';
			} else {
				$hide_disable_button='
				<button onclick=\'window.location.href="./index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_list&amp;hide_disabled_values=1";\' class="btn action-btn btn-info ml-4">
						<i class="fa fa-eye"><!----></i> '.T_('Masquer les entrées désactivées').'
				</button>
				';
			}
		} else {$hide_disable_button='';}

		echo '
		<div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
			<p>
				<button onclick=\'window.location.href="./index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_add";\' class="btn action-btn btn-success">
					<i class="fa fa-plus"><!----></i> '.T_('Ajouter une entrée').'
				</button>
				'.$hide_disable_button.'
			</p>
		</div>
		<div class="table-responsive">
			<table id="sample-table-1" class="table table-striped table-bordered table-hover">
				<thead>
					<tr>';
						//build title line
						$query = $db->query("DESC $db_table");
						while ($row=$query->fetch())
						{	
							if(($_GET['table']=='tcategory' || $_GET['table']=='tcriticality' || $_GET['table']=='tpriority' || $_GET['table']=='ttypes') && $rparameters['user_limit_service']==0 && $row['Field']=='service') {} else 
							{
								if($row['Field']!='ldap_guid')
								{
									//translate column name
									$col_name=$row['Field']; //default value
									if($row['Field']=='id') {$col_name=T_('Identifiant');}
									if($row['Field']=='name') {$col_name=T_('Libellé');}
									if($row['Field']=='cat') {$col_name=T_('Catégorie');}
									if($row['Field']=='disable') {$col_name=T_('Désactivé');}
									if($row['Field']=='number') {$col_name=T_('Ordre');}
									if($row['Field']=='color') {$col_name=T_('Couleur');}
									if($row['Field']=='description') {$col_name=T_('Description');}
									if($row['Field']=='mail_object') {$col_name=T_('Objet du mail');}
									if($row['Field']=='display') {$col_name=T_("Couleur d'affichage");}
									if($row['Field']=='icon') {$col_name=T_("Icône");}
									if($row['Field']=='incident') {$col_name=T_("Numéro ticket");}
									if($row['Field']=='address') {$col_name=T_("Adresse");}
									if($row['Field']=='zip') {$col_name=T_("Code postal");}
									if($row['Field']=='city') {$col_name=T_("Ville");}
									if($row['Field']=='country') {$col_name=T_("Pays");}
									if($row['Field']=='legal_status') {$col_name=T_("Forme juridique");}
									if($row['Field']=='limit_ticket_number') {$col_name=T_("Nombre de limite de ticket");}
									if($row['Field']=='limit_ticket_days') {$col_name=T_("Nombre de limite de jours");}
									if($row['Field']=='limit_ticket_date_start') {$col_name=T_("Date de début de la limite de jours");}
									if($row['Field']=='limit_hour_number') {$col_name=T_("Nombre de limite d'heures");}
									if($row['Field']=='limit_hour_days') {$col_name=T_("Nombre de limite de jours");}
									if($row['Field']=='limit_hour_date_start') {$col_name=T_("Date de début de la limite de jours");}
									if($row['Field']=='min') {$col_name=T_("Minutes");}
									if($row['Field']=='virtualization') {$col_name=T_("Virtualisation");}
									if($row['Field']=='manufacturer') {$col_name=T_("Fabricant");}
									if($row['Field']=='image') {$col_name=T_("Image");}
									if($row['Field']=='ip') {$col_name=T_("Équipement IP");}
									if($row['Field']=='type') {$col_name=T_("Type");}
									if($row['Field']=='wifi') {$col_name=T_("Équipement WIFI");}
									if($row['Field']=='warranty') {$col_name=T_("Années de garantie");}
									if($row['Field']=='order') {$col_name=T_("Ordre");}
									if($row['Field']=='block_ip_search') {$col_name=T_("Blocage de recherche IP");}
									if($row['Field']=='mail') {$col_name=T_("Adresse mail");}
									if($row['Field']=='service') {$col_name=T_("Service");}
									if($row['Field']=='network') {$col_name=T_("Réseau");}
									if($row['Field']=='netmask') {$col_name=T_("Masque");}
									if($row['Field']=='scan') {$col_name=T_("Scan");}
									if($row['Field']=='technician') {$col_name=T_("Affectation automatique technicien");}
									if($row['Field']=='technician_group') {$col_name=T_("Affectation automatique groupe de techniciens");}
									if($row['Field']=='meta') {$col_name=T_("État à traiter");}
									if($row['Field']=='user_validation') {$col_name=T_("Validation demandeur");}
									if($row['Field']=='date_start') {$col_name=T_("Date début");}
									if($row['Field']=='frequency') {$col_name=T_("Fréquence");}
									if($row['Field']=='information_message') {$col_name=T_("Message d'information");}
									
									if($_GET['table']=='tcompany' && !$rparameters['company_limit_ticket'] && ($row['Field']=='limit_ticket_number' || $row['Field']=='limit_ticket_days' || $row['Field']=='limit_ticket_date_start')) 
									{
										//hide col if parameter is off
									}elseif($_GET['table']=='tcompany' && ($row['Field']=='address' || $row['Field']=='zip' || $row['Field']=='city' || $row['Field']=='country' || $row['Field']=='comment') ) 
									{
										//hide col if parameter is off
									}elseif($_GET['table']=='tcompany' && !$rparameters['company_message'] && ($row['Field']=='information_message')) 
									{
										//hide col if parameter is off
									}elseif($_GET['table']=='ttemplates' && (!$rparameters['ticket_recurrent_create'] && ($row['Field']=='frequency' || $row['Field']=='date_start'))|| $row['Field']=='last_execution_date') 
									{
										//hide col if parameter is off
									}elseif($_GET['table']=='ttypes' && $row['Field']=='user_validation' && !$rparameters['user_validation'])
									{
										//hide col if parameter is off
									}elseif($_GET['table']=='ttypes' && $row['Field']=='mail' && !$rparameters['mail_auto_type'])
									{
										//hide col if parameter is off
									}elseif($_GET['table']=='tcompany' && !$rparameters['company_limit_hour'] && ($row['Field']=='limit_hour_number' || $row['Field']=='limit_hour_days' || $row['Field']=='limit_hour_date_start')) 
									{
										//hide col if parameter is off
									}elseif(!$rparameters['ticket_cat_auto_attribute'] && ($row['Field']=='technician' || $row['Field']=='technician_group')) { 
										//hide col for tech cat auto attribute is disabled
									} else {
										echo '<th>'.$col_name.'</th>';
									}
								}
							}
						}
						$query->closeCursor();
						echo '
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>';
				
				//define order
				if($_GET['table']=='tassets_model'){$order='ORDER BY tassets_model.type,tassets_model.manufacturer ';} 
				elseif($_GET['table']=='tcategory'){$order='ORDER BY number,service,name';} 
				elseif($_GET['table']=='tsubcat'){$order='ORDER BY cat,name';} 
				elseif($_GET['table']=='tcriticality'){$order='ORDER BY service,number';} 
				elseif($_GET['table']=='tstates'){$order='ORDER BY number';} 
				elseif($_GET['table']=='tpriority'){$order='ORDER BY number';} 
				elseif($_GET['table']=='tassets_state'){$order='ORDER BY `order`';} 
				elseif($_GET['table']=='tassets_network'){$order='ORDER BY `network`';} 
				elseif($_GET['table']=='ttime'){$order='ORDER BY min';} 
				else {$order='ORDER BY name';}
				
				if($_GET['hide_disabled_values']){$disable=" AND disable='0' ";} else {$disable='';}
				if($listkeywords){$listkeywords=' AND name LIKE "%'.$listkeywords.'%"';} else {$listkeywords='';}
				
				
				if($rright['dashboard_service_only']!=0 && $rparameters['user_limit_service']==1 &&  $_SESSION['profile_id']!=4){
					$where_service_list=str_replace('tincidents.u_service','service',$where_service);
					if($_GET['table']=='tsubcat') {
						$query="SELECT tsubcat.id,tsubcat.cat,tsubcat.name FROM `tsubcat`,`tcategory` WHERE tsubcat.cat=tcategory.id $where_service_list $disable ORDER BY tsubcat.name";
					} else {
						$query="SELECT * FROM $db_table WHERE 1=1 $where_service_list $disable $order";
					}
				} else {$query="SELECT * FROM $db_table WHERE id!=0 $disable $listkeywords $order";} 
				
				//build each line
				if($rparameters['debug']) {echo 'QRY : '.$query;}
				$query = $db->query($query);
				while ($row=$query->fetch()) 
				{
					echo '
					<tr >
					';
					for($i=0; $i < $nbchamp1; ++$i)
					{
						//special case to customize table display, $i var represent column
						if($_GET['table']=='tcompany' && !$rparameters['company_message'] && $i==16)
						{
							//hide
						}elseif($_GET['table']=='tcompany' && !$rparameters['company_limit_ticket'] && ($i==9 || $i==10 || $i==11))
						{
							//hide
						}elseif($_GET['table']=='tcompany' && !$rparameters['company_limit_hour'] && ($i==12 || $i==13 || $i==14))
						{
							//hide
						}elseif($_GET['table']=='tcompany' && ($i==2 || $i==3|| $i==4|| $i==5|| $i==15))
						{
							//hide
						}elseif($_GET['table']=='ttemplates' && ((!$rparameters['ticket_recurrent_create'] && ($i==3 || $i==4 || $i==5)) || ($rparameters['ticket_recurrent_create']&&$i==5)))
						{
							//hide
						}elseif($_GET['table']=='ttemplates' && $rparameters['ticket_recurrent_create'] && $i==3)
						{
							//convert date to display
							echo '<td onclick=\'window.location.href="index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_edit&amp;id='.$row['id'].'&amp;hide_disabled_values='.$_GET['hide_disabled_values'].'";\' >'.DateToDisplay($row[$i]).'</td>';
						}elseif($_GET['table']=='ttemplates' && $rparameters['ticket_recurrent_create'] && $i==4)
						{
							//convert to display
							$name='';
							if($row[$i]=='yearly') {$name=T_('Tous les ans');}
							if($row[$i]=='monthly') {$name=T_('Tous les mois');}
							if($row[$i]=='weekly') {$name=T_('Tous les semaines');}
							if($row[$i]=='daily') {$name=T_('Tous les jours');}
							echo '<td onclick=\'window.location.href="index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_edit&amp;id='.$row['id'].'&amp;hide_disabled_values='.$_GET['hide_disabled_values'].'";\' >'.$name.'</td>';
						}elseif($_GET['table']=='tcompany' && $i==11) //convert date to FR format
						{
							echo '<td onclick=\'window.location.href="index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_edit&amp;id='.$row['id'].'&amp;hide_disabled_values='.$_GET['hide_disabled_values'].'";\' >'.DateToDisplay($row['limit_ticket_date_start']).'</td>';
						}elseif($_GET['table']=='tcompany' && $i==14) //convert date to FR format
						{
							echo '<td onclick=\'window.location.href="index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_edit&amp;id='.$row['id'].'&amp;hide_disabled_values='.$_GET['hide_disabled_values'].'";\' >'.DateToDisplay($row['limit_hour_date_start']).'</td>';
						}elseif($_GET['table']=='tsubcat' && $i==1)
						{
							$qry2 = $db->prepare("SELECT `name` FROM `tcategory` WHERE id=:id");
							$qry2->execute(array('id' => $row[$i]));
							$rcat=$qry2->fetch();
							$qry2->closeCursor();
							if(empty($rcat['name'])) {$rcat['name']=T_('Inconnue');}
							echo '<td onclick=\'window.location.href="index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_edit&amp;id='.$row['id'].'&amp;hide_disabled_values='.$_GET['hide_disabled_values'].'";\' >'.$rcat['name'].'</td>';
						}elseif($_GET['table']=='tsubcat' && ($i==3 || $i==4) && !$rparameters['ticket_cat_auto_attribute'])
						{
							//hide cols
						} elseif($_GET['table']=='ttypes' && $i==3 && !$rparameters['user_validation'])
						{
							//hide cols
						}elseif($_GET['table']=='ttypes' && $i==4 && !$rparameters['mail_auto_type'])
						{
							//hide cols
						} elseif(
							($_GET['table']=='ttypes' && $i==3 && $rparameters['user_validation']) ||
							($_GET['table']=='tservices' && $i==3) ||
							($_GET['table']=='tcompany' && $i==17) ||
							($_GET['table']=='tassets_state' && $i==5) ||
							($_GET['table']=='tassets_location' && $i==2) ||
							($_GET['table']=='tassets_iface_role' && $i==2) ||
							($_GET['table']=='tassets_network' && $i==5) 
						)
						{
							//replace value by word
							if($row[$i]) {$value_to_display=T_('Oui');} else {$value_to_display=T_('Non');}
							echo '<td onclick=\'window.location.href="index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_edit&amp;id='.$row['id'].'&amp;hide_disabled_values='.$_GET['hide_disabled_values'].'";\' >'.$value_to_display.'</td>';
						} 
						elseif($_GET['table']=='tassets_model' && $i==1)
						{
							$qry2 = $db->prepare("SELECT `name` FROM `tassets_type` WHERE id=:id");
							$qry2->execute(array('id' => $row[$i]));
							$ratype=$qry2->fetch();
							$qry2->closeCursor();
							if(empty($ratype['name'])){$ratype=array();$ratype['name']='';}
							echo '<td onclick=\'window.location.href="index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_edit&amp;id='.$row['id'].'&amp;hide_disabled_values='.$_GET['hide_disabled_values'].'";\' >'.$ratype['name'].'</td>';
						} 
						elseif($_GET['table']=='tassets_model' && $i==2)
						{
							$qry2 = $db->prepare("SELECT `name` FROM `tassets_manufacturer` WHERE id=:id");
							$qry2->execute(array('id' => $row[$i]));
							$raman=$qry2->fetch();
							$qry2->closeCursor();
							if(empty($raman['name'])){$raman['name']='';}
							echo '<td onclick=\'window.location.href="index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_edit&amp;id='.$row['id'].'&amp;hide_disabled_values='.$_GET['hide_disabled_values'].'";\' >'.$raman['name'].'</td>';
						}elseif((($_GET['table']=='tcategory' && $i==3) || ($_GET['table']=='tcriticality' && $i==4) || ($_GET['table']=='tpriority' && $i==4) || ($_GET['table']=='ttypes' && $i==2)) && $rparameters['user_limit_service']==0)
						{
						}elseif((($_GET['table']=='tcategory' && $i==3) || ($_GET['table']=='tcriticality' && $i==4) || ($_GET['table']=='tpriority' && $i==4) || ($_GET['table']=='ttypes' && $i==2)) && $rparameters['user_limit_service']==1)
						{
							$qry2 = $db->prepare("SELECT `name` FROM `tservices` WHERE id=:id");
							$qry2->execute(array('id' => $row[$i]));
							$row2=$qry2->fetch();
							$qry2->closeCursor();
							if(empty($row2['name'])){$row2['name']='';}
							echo '<td onclick=\'window.location.href="index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_edit&amp;id='.$row['id'].'&amp;hide_disabled_values='.$_GET['hide_disabled_values'].'";\' >'.$row2['name'].'</td>';
						}elseif($_GET['table']=='tcategory' && ($i==4 || $i==5) && !$rparameters['ticket_cat_auto_attribute'])
						{
							//hide row if disable function
						}elseif(($_GET['table']=='tcategory' && $i==4) || ($_GET['table']=='tsubcat' && $i==3)  && $rparameters['ticket_cat_auto_attribute'])
						{
							$qry2 = $db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
							$qry2->execute(array('id' => $row[$i]));
							$row2=$qry2->fetch();
							$qry2->closeCursor();
							if(empty($row2['firstname'])){$row2['firstname']='';}
							if(empty($row2['lastname'])){$row2['lastname']='';}
							echo '<td onclick=\'window.location.href="index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_edit&amp;id='.$row['id'].'&amp;hide_disabled_values='.$_GET['hide_disabled_values'].'";\' >'.$row2['firstname'].' '.$row2['lastname'].'</td>';
						}elseif(($_GET['table']=='tcategory' && $i==5) || ($_GET['table']=='tsubcat' && $i==4) && $rparameters['ticket_cat_auto_attribute'])
						{
							$qry2 = $db->prepare("SELECT `name` FROM `tgroups` WHERE id=:id");
							$qry2->execute(array('id' => $row[$i]));
							$row2=$qry2->fetch();
							$qry2->closeCursor();
							if(empty($row2['name'])){$row2['name']='';}
							echo '<td onclick=\'window.location.href="index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_edit&amp;id='.$row['id'].'&amp;hide_disabled_values='.$_GET['hide_disabled_values'].'";\' >'.$row2['name'].'</td>';
						}elseif(($_GET['table']=='tagencies' && $i==3) || ($_GET['table']=='tservices' && $i==2)) //hide ldap_guid
						{
						}else{
							
							echo '<td onclick=\'window.location.href="index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_edit&amp;id='.$row['id'].'&amp;hide_disabled_values='.$_GET['hide_disabled_values'].'";\' >';
							//echo $i;
							if($row[$i]!='') {echo T_($row[$i]);} else {echo $row[$i];}
							echo '</td>';
						}
					}
					echo '
						<td style="width:104px">
							<a class="btn action-btn btn-sm btn-warning" href="index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=disp_edit&amp;id='.$row['id'].'&amp;hide_disabled_values='.$_GET['hide_disabled_values'].'"  title="'.T_('Éditer cette ligne').'" ><i style="color:#FFF;"  class="fa fa-pencil-alt"><!----></i></a>&nbsp;';
							if(($_GET['table']!='tstates' || $row['id']>6) && $row['id']!=0 && ($_GET['table']!='tassets_iface_role' || $row['id']>2) && ($_GET['table']!='tassets_state' || $row['id']>4)) 
							{
								echo '<a class="btn action-btn btn-sm btn-danger" onClick="javascript: return confirm(\''.T_('Êtes-vous sur de vouloir supprimer cette ligne ?').'\');" href="./index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;id='.$row['id'].'&amp;action=delete"  title="'.T_('Supprimer cette ligne').'" ><i class="fa fa-trash"><!----></i></a>&nbsp;';
							}							
							echo "
						</td>
					</tr>";
				}
				$query->closeCursor();
				echo '
				</tbody>
			</table>
		</div>
		';
	} else {
		echo DisplayMessage('error',T_("Vous n'avez pas accès à cette liste, contactez votre administrateur"));
	}
}
?>