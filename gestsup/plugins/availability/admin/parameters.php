<?php
################################################################################
# @Name : ./plugins/availability/admin/parameters.php
# @Description : admin parameters for availability plugins
# @Call : /admin/parameters/plugins.php
# @Author : Flox
# @Create : 28/04/2015
# @Update : 24/08/2023
# @Version : 3.2.38
################################################################################

if($plugin['enable'] && $rright['admin'])
{
	//initialize variables 
	if(!isset($_POST['subcat'])) $_POST['subcat']= ''; 
	if(!isset($_POST['depsubcat'])) $_POST['depsubcat']= ''; 
	if(!isset($_POST['depcategory'])) $_POST['depcategory']= ''; 
	if(!isset($_POST['category'])) $_POST['category']= ''; 
	if(!isset($_POST['availability'])) $_POST['availability']= ''; 
	if(!isset($_POST['availability_all_cat'])) $_POST['availability_all_cat']= ''; 
	if(!isset($_POST['availability_condition_type'])) $_POST['availability_condition_type']= ''; 
	if(!isset($_POST['availability_condition_value'])) $_POST['availability_condition_value']= ''; 
	if(!isset($_POST['availability_dep'])) $_POST['availability_dep']= ''; 
	if(!isset($_GET['deleteavailability'])) $_GET['deleteavailability']= ''; 
	if(!isset($_GET['deleteavailabilitydep'])) $_GET['deleteavailabilitydep']= ''; 

	//secure inputs
	$_POST['subcat']=htmlspecialchars($_POST['subcat'], ENT_QUOTES, 'UTF-8');
	$_POST['depsubcat']=htmlspecialchars($_POST['depsubcat'], ENT_QUOTES, 'UTF-8');
	$_POST['depcategory']=htmlspecialchars($_POST['depcategory'], ENT_QUOTES, 'UTF-8');
	$_POST['category']=htmlspecialchars($_POST['category'], ENT_QUOTES, 'UTF-8');
	$_POST['availability_all_cat']=htmlspecialchars($_POST['availability_all_cat'], ENT_QUOTES, 'UTF-8');
	$_POST['availability_condition_type']=htmlspecialchars($_POST['availability_condition_type'], ENT_QUOTES, 'UTF-8');
	$_POST['availability_condition_value']=htmlspecialchars($_POST['availability_condition_value'], ENT_QUOTES, 'UTF-8');
	$_POST['availability_dep']=htmlspecialchars($_POST['availability_dep'], ENT_QUOTES, 'UTF-8');

	$_GET['deleteavailability']=htmlspecialchars($_GET['deleteavailability'], ENT_QUOTES, 'UTF-8');
	$_GET['deleteavailabilitydep']=htmlspecialchars($_GET['deleteavailabilitydep'], ENT_QUOTES, 'UTF-8');

	//db delete
	if($_GET['deleteavailability']) //remove cat from availability list
	{
		$qry=$db->prepare("DELETE FROM tavailability WHERE id=:id");
		$qry->execute(array('id' => $_GET['deleteavailability']));
		
	}
	if($_GET['deleteavailabilitydep']) //remove dep cat from availability dependency list
	{
		$qry=$db->prepare("DELETE FROM tavailability_dep WHERE id=:id");
		$qry->execute(array('id' => $_GET['deleteavailabilitydep']));
	}

	//db update
	if($_POST['submit_plugin'])
	{
		$qry=$db->prepare("UPDATE `tparameters` SET `availability`=:availability,`availability_all_cat`=:availability_all_cat,`availability_condition_type`=:availability_condition_type,`availability_condition_value`=:availability_condition_value,`availability_dep`=:availability_dep WHERE `id`=:id");
		$qry->execute(array('availability' => $_POST['availability'],'availability_all_cat' => $_POST['availability_all_cat'],'availability_condition_type' => $_POST['availability_condition_type'],'availability_condition_value' => $_POST['availability_condition_value'],'availability_dep' => $_POST['availability_dep'],'id' => '1'));

		//add cat to availability list
		if($_POST['category'] && $_POST['category']!='0' && $_POST['category']!='%')
		{
			$qry=$db->prepare("INSERT INTO `tavailability` (`category`,`subcat`) VALUES (:category,:subcat)");
			$qry->execute(array('category' => $_POST['category'],'subcat' => $_POST['subcat']));
		}
		//add dependency cat to availability list
		if($_POST['depcategory'] && $_POST['depcategory']!='0' && $_POST['depcategory']!='%')
		{
			$qry=$db->prepare("INSERT INTO `tavailability_dep` (`category`,`subcat`) VALUES (:category,:subcat)");
			$qry->execute(array('category' => $_POST['depcategory'],'subcat' => $_POST['depsubcat']));
		}
		 
		//find input name for target values
		$qry = $db->prepare("SELECT DISTINCT YEAR(date_create) FROM `tincidents`");
		$qry->execute(array());
		while ($rowyear=$qry->fetch())
		{
			$qry2 = $db->prepare("SELECT `subcat` FROM `tavailability`");
			$qry2->execute();
			while ($rowsubcat=$qry2->fetch())
			{
				$inputname="target_$rowyear[0]_$rowsubcat[subcat]";
				if(!isset($_POST[$inputname])) $_POST[$inputname] = '';
				if($_POST[$inputname]) {
					//check existing values
					$qry3 = $db->prepare("SELECT * FROM `tavailability_target` WHERE year=:year AND subcat=:subcat");
					$qry3->execute(array('year' => $rowyear[0],'subcat' => $rowsubcat['subcat'],));
					$check= $qry3->fetch();
					if(!empty($check[0]))
					{
						$qry4=$db->prepare("UPDATE tavailability_target SET target=:target WHERE year=:year AND subcat=:subcat");
						$qry4->execute(array('target' => $_POST[$inputname],'year' => $rowyear[0],'subcat' => $rowsubcat['subcat']));
					} else {
						$qry4=$db->prepare("INSERT INTO tavailability_target (year,subcat,target) VALUES (:year,:subcat,:target)");
						$qry4->execute(array('year' => $rowyear[0],'subcat' => $rowsubcat['subcat'],'target' => $_POST[$inputname]));
					}
				}
			}
			$qry2->closecursor();
		}
		$qry->closecursor();

		//reload page 
		$www = "index.php?page=admin&subpage=parameters&tab=plugin";
		echo '<script language="Javascript">
		<!--
		document.location.replace("'.$www.'");
		// -->
		</script>';
	} 

	echo '
		<div class="space-4"></div>
		&nbsp; &nbsp; &nbsp;<i class="fa fa-circle text-success"></i> 1 - '.T_('Surveiller toutes les catégories').' :&nbsp;
		<label for="availability_all_cat1">
			<input type="radio"  value="1" name="availability_all_cat" id="availability_all_cat1" '; if($rparameters['availability_all_cat']) {echo "checked";} echo '> <span class="lbl"> '.T_('Oui').' </span>
			<input type="radio"  value="0" name="availability_all_cat" id="availability_all_cat2" '; if(!$rparameters['availability_all_cat']) {echo "checked";} echo '  > <span class="lbl"> '.T_('Non').' </span>
		</label>
	';
	if(!$rparameters['availability_all_cat'])
	{
		echo '
			<div class="space-4"></div>
			&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
			<i class="fa fa-caret-right text-primary-m2"></i> '.T_('Sélection des catégories ou sous-catégories à surveiller').' :<br />
			';
			//display availability list
			$qry=$db->prepare("SELECT * FROM `tavailability`");
			$qry->execute();
			while($row=$qry->fetch()) 
			{
				$qry2=$db->prepare("SELECT `name` FROM `tcategory` WHERE id=:id");
				$qry2->execute(array('id' => $row['category']));
				$cname=$qry2->fetch();
				$qry2->closeCursor();
				
				if($row['subcat'])
				{
					$qry2=$db->prepare("SELECT `name` FROM `tsubcat` WHERE id=:id");
					$qry2->execute(array('id' => $row['subcat']));
					$sname=$qry2->fetch();
					$qry2->closeCursor();
				} else {$sname[0]='';}
				echo '
					&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;
					<i class="fa fa-caret-right text-success"></i> ('.$cname['name'].' > '.$sname[0].') 
					<a title="'.T_('Supprimer cette catégorie').'" href="./index.php?page=admin&subpage=parameters&tab=plugin&deleteavailability='.$row['id'].'"><i class="fa fa-trash text-danger"></i></a>
					<br />
				';
			}
			$qry->closeCursor();
			//display add category form
			echo'
			&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
			<i class="fa fa-caret-right text-primary-m2"></i> 
			'.T_('Ajouter la catégorie').' : 
			<select name="category" style="width:auto" class="form-control form-control-sm d-inline-block" >
				<option value="%"></option>';
				$qry=$db->prepare("SELECT * FROM `tcategory` ORDER BY name");
				$qry->execute();
				while($row=$qry->fetch()) 
				{
					if($_POST['category']==$row['id']) {
						echo "<option selected value=\"$row[id]\">$row[name]</option>";
					} else {
						echo "<option value=\"$row[id]\">$row[name]</option>";
					}
				}
				$qry->closeCursor();
			echo'
			</select>
			&nbsp;'.T_('et la Sous-Catégorie').' :
			<select name="subcat" style="width:auto" class="form-control form-control-sm d-inline-block">
				<option value="%"></option>';
				echo "-$_POST[category]-";
				if($_POST['category']!='%' && $_POST['category'])
				{
					$qry=$db->prepare("SELECT `id`,`name` FROM `tsubcat` WHERE cat LIKE :cat ORDER BY name");
					$qry->execute(array('cat' => $_POST['category']));
					while($row=$qry->fetch())
					{
						if($_POST['subcat']==$row['id']) {echo "<option selected value=\"$row[id]\">$row[name]</option>";} else {echo "<option value=\"$row[id]\">$row[name]</option>";}
					}
					$qry->closeCursor();
				} else {
					$qry=$db->prepare("SELECT `id`,`name` FROM `tsubcat` ORDER BY name");
					$qry->execute();
					while($row=$qry->fetch())
					{
						if($_POST['subcat']==$row['id']) {echo "<option selected value=\"$row[id]\">$row[name]</option>";} else {echo "<option value=\"$row[id]\">$row[name]</option>";}
					}
					$qry->closeCursor();
				}
				echo '
			</select>
			<div class="space-4"></div>
			';
	}
	echo '
	<div class="space-4"></div>
	&nbsp; &nbsp; &nbsp;<i class="fa fa-circle text-success"></i>
	2 - '.T_("Condition de prise en compte d'un ticket").' :&nbsp;
	<select name="availability_condition_type" style="width:auto" class="form-control form-control-sm d-inline-block">
		<option value="" >'.T_('Aucune').'</option>
		<option value="types" '; if($rparameters['availability_condition_type']=='types') {echo 'selected';} echo ' >'.T_('Type').'</option>
		<option value="criticality" '; if($rparameters['availability_condition_type']=='criticality') {echo 'selected';} echo '>'.T_('Criticité').'</option>
	</select> 
	';
	if($rparameters['availability_condition_type'])
	{
		if($_POST['availability_condition_type']) 
		{
			if($_POST['availability_condition_type']) {$table="$_POST[availability_condition_type]";} else {$table='criticality';}
		} else {
			if($rparameters['availability_condition_type']) {$table="$rparameters[availability_condition_type]";} else {$table='criticality';}
		}
		//check $table value
		if($table!='criticality' && $table!='types') {$table='criticality';}
		
		$query = $db->query("SELECT * FROM t$table ORDER BY name");
		echo 'est ';
		echo '<select style="width:auto" class="form-control form-control-sm d-inline-block" name="availability_condition_value" >';
			while ($row = $query->fetch())
			{
				echo '<option '; if($rparameters['availability_condition_value']==$row['id']) {echo 'selected';} echo ' value="'.$row['id'].'">'.T_($row['name']).'</option>';
			} 
			$query->closeCursor();
		echo '</select>';
	}
	echo'
	<div class="space-4"></div>
	&nbsp; &nbsp; &nbsp;<i class="fa fa-circle text-success"></i>
	3 - '.T_('Dépendances').' :&nbsp;
	<label for="availability_dep1">
			<input type="radio"  value="1" name="availability_dep" id="availability_dep1" '; if($rparameters['availability_dep']) {echo "checked";} echo '> <span class="lbl"> '.T_('Oui').' </span>
			<input type="radio"  value="0" name="availability_dep" id="availability_dep2" '; if(!$rparameters['availability_dep']) {echo "checked";} echo '  > <span class="lbl"> '.T_('Non').' </span>
	</label>
	<i title="'.T_("Permet de définir des sous-catégories qui seront comptabilisées dans toutes les statistiques si elles possèdent la même condition. (ex: un ticket réseau critique, entraîne une indisponibilité d'une application)").'" class="fa fa-question-sign blue"></i>
	';
	if($rparameters['availability_dep'])
	{
		echo '
			<div class="space-4"></div>
			&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
			<i class="fa fa-caret-right text-primary-m2"></i> '.T_('Liste des sous-catégories impactant toutes les catégories surveillées').':<br />
			';
			//display availability dependency list
			$qry=$db->prepare("SELECT * FROM `tavailability_dep`");
			$qry->execute();
			while($row=$qry->fetch()) 
			{
				$qry2=$db->prepare("SELECT `name` FROM `tcategory` WHERE id=:id");
				$qry2->execute(array('id' => $row['category']));
				$cname=$qry2->fetch();
				$qry2->closeCursor();
				if($row['subcat'])
				{
					$qry2=$db->prepare("SELECT `name` FROM `tsubcat` WHERE id=:id");
					$qry2->execute(array('id' => $row['subcat']));
					$sname=$qry2->fetch();
					$qry2->closeCursor();
					
				} else {$sname[0]='';}
				echo '
					&nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;
					<i class="fa fa-caret-right text-success"></i> ('.$cname['name'].' > '.$sname[0].') 
					<a title="'.T_('Supprimer cette catégorie').'" href="./index.php?page=admin&subpage=parameters&tab=plugin&deleteavailabilitydep='.$row['id'].'"><i class="fa fa-trash text-danger"></i></a>
					<br />
				';
			}
			$qry->closeCursor();
			//display add cat form
			echo'
			&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
			<i class="fa fa-caret-right text-primary-m2"></i> 
			'.T_('Ajouter la catégorie').' :
			<select name="depcategory" onchange="submit()" style="width:auto" class="form-control form-control-sm d-inline-block" >
				<option value="%"></option>';
				$qry=$db->prepare("SELECT `id`,`name` FROM `tcategory` ORDER BY name");
				$qry->execute();
				while($row=$qry->fetch()) 
				{
					if($_POST['depcategory']==$row['id']) {echo "<option selected value=\"$row[id]\">$row[name]</option>";} else {echo "<option value=\"$row[id]\">$row[name]</option>";}
				}
				$qry->closeCursor();
			echo'
			</select>
				&nbsp;'.T_('et la Sous-Catégorie').':
			<select name="depsubcat" onchange="submit()" style="width:auto" class="form-control form-control-sm d-inline-block">
				<option value="%"></option>';
				if($_POST['depcategory'])
				{
					$qry=$db->prepare("SELECT `id`,`name` FROM `tsubcat` WHERE cat LIKE :cat ORDER BY name");
					$qry->execute(array('cat' => $_POST['depcategory']));
					while($row=$qry->fetch()) 
					{
						if($_POST['depsubcat']==$row['id']){echo "<option selected value=\"$row[id]\">$row[name]</option>";} else {echo "<option value=\"$row[id]\">$row[name]</option>";}
					}
					$qry->closeCursor();
				} else {
					$qry=$db->prepare("SELECT `id`,`name` FROM `tsubcat` ORDER BY name");
					$qry->execute();
					while($row=$qry->fetch()) 
					{
						if($_POST['depsubcat']==$row['id']){echo "<option selected value=\"$row[id]\">$row[name]</option>";} else {echo "<option value=\"$row[id]\">$row[name]</option>";}
					}
					$qry->closeCursor();
				}
				echo '
			</select>
			<div class="space-4"></div>
			';
	}
	//target tx part
	echo'
	<div class="space-4"></div>
	&nbsp; &nbsp; &nbsp;<i class="fa fa-circle text-success"></i>
	4 - '.T_('Définition des taux de disponibilités cible par années, par sous-catégories').' :&nbsp;
	<i title="'.T_('Permet de fixer des objectifs de disponibilité, pour chaque sous catégorie, qui peuvent fluctuer chaque année').'." class="fa fa-question-sign blue"></i>
	<br />
	';
	//find tickets year and display subcat 
	$qry=$db->prepare("SELECT DISTINCT YEAR(date_create) FROM `tincidents` ORDER BY YEAR(date_create) DESC");
	$qry->execute();
	while($rowyear=$qry->fetch()) 
	{
		echo ' &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <i class="fa fa-caret-right text-primary-m2"></i> <b>'.$rowyear[0].'</b> <br />';
		
		$qry2=$db->prepare("SELECT `subcat` FROM `tavailability`");
		$qry2->execute();
		while ($rowsubcat=$qry2->fetch())
		{
			//get subcat name
			$qry3=$db->prepare("SELECT `name` FROM `tsubcat` WHERE id=:id");
			$qry3->execute(array('id' => $rowsubcat['subcat']));
			$sname=$qry3->fetch();
			$qry3->closeCursor();
			
			//get target tx data from tavailability_target table
			$qry3=$db->prepare("SELECT `target` FROM `tavailability_target` WHERE subcat=:subcat AND year=:year");
			$qry3->execute(array('subcat' => $rowsubcat['subcat'],'year' => $rowyear[0]));
			$targettx=$qry3->fetch();
			$qry3->closeCursor();
			if(empty($targettx['target'])) {$targettx=array(); $targettx['target']='0';}
			
			echo ' &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; <i class="fa fa-caret-right text-success"></i> <u>'.$sname['name'].'</u> 
			'.T_('taux de disponibilité cible').': 
			<input style="width:auto" class="form-control form-control-sm d-inline-block" type="text" size="4" name="target_'.$rowyear[0].'_'.$rowsubcat['subcat'].'" value="'.$targettx['target'].'" />
			%
			<br />';
		}
		$qry2->closeCursor();
	} 
	$qry->closeCursor();
}
?>