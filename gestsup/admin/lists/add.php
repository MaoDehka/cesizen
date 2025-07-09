<?php
################################################################################
# @Name : ./admin/lists/add.php
# @Description : add value in table
# @Call : /admin/list.php
# @Parameters : 
# @Author : Flox
# @Create : 10/08/2021
# @Update : 26/03/2024
# @Version : 3.2.49
################################################################################

if($_GET['action']=="add")
{
	if($_GET['table']=='tcategory') //special case category
	{
		//secure string
		$_POST['category']=strip_tags($_POST['category']);
		//avoid double attribute problem
		if($_POST['technician'] && $_POST['technician_group']) {$_POST['technician']=0;$_POST['technician_group']=0;}
		$qry=$db->prepare("INSERT INTO `tcategory` (`name`,`service`,`technician`,`technician_group`) VALUES (:name,:service,:technician,:technician_group)");
        $qry->execute(array('name' => $_POST['category'],'service' => $_POST['service'],'technician' => $_POST['technician'],'technician_group' => $_POST['technician_group']));
	}
	elseif($_GET['table']=='ttemplates') //special case category
	{
         //convert date
         if($_POST['ticket_recurrent_date_start'] && !strpos($_POST['ticket_recurrent_date_start'], "-") && preg_match('~[0-9]~', $_POST['ticket_recurrent_date_start']))
         {
             $_POST['ticket_recurrent_date_start'] = DateTime::createFromFormat('d/m/Y', $_POST['ticket_recurrent_date_start']);
             $_POST['ticket_recurrent_date_start']=$_POST['ticket_recurrent_date_start']->format('Y-m-d');
         }
         
		//secure string
		$_POST['name']=strip_tags($_POST['name']);

        //check existing ticket
        $qry=$db->prepare("SELECT `id` FROM `tincidents` WHERE id=:id");
        $qry->execute(array('id' => $_POST['incident']));
        $row=$qry->fetch();
        $qry->closeCursor();

        if(!empty($row['id']))
        {   
            $qry=$db->prepare("INSERT INTO `ttemplates` (`name`,`incident`,`date_start`,`frequency`) VALUES (:name,:incident,:date_start,:frequency)");
		    $qry->execute(array('name' => $_POST['name'],'incident' => $_POST['incident'],'date_start' => $_POST['ticket_recurrent_date_start'],'frequency' => $_POST['ticket_recurrent_date_frequency']));
        } else {
            echo DisplayMessage('error',T_("Ce ticket n'existe pas")); 
            exit;
        }
	}
	elseif($_GET['table']=='tsubcat') //special case subcat 
	{
		//secure string
		$_POST['subcat']=strip_tags($_POST['subcat']);
		//avoid double attribute problem
		if($_POST['technician'] && $_POST['technician_group']) {$_POST['technician']=0;$_POST['technician_group']=0;}
		$qry=$db->prepare("INSERT INTO `tsubcat` (`cat`,`name`,`technician`,`technician_group`) VALUES (:cat,:name,:technician,:technician_group)");
		$qry->execute(array('cat' => $_POST['cat'],'name' => $_POST['subcat'],'technician' => $_POST['technician'],'technician_group' => $_POST['technician_group']));
	}
	elseif($_GET['table']=='tassets_model') //special case and asset model
	{
		//secure string
		$_POST['model']=strip_tags($_POST['model']);
		$_POST['warranty']=strip_tags($_POST['warranty']);
		
		///upload file
		if($_FILES['file1'])
		{
			//white list exclusion for extension
			$whitelist =  array('png','jpg','jpeg' ,'gif' ,'bmp','');
			$file_name = basename($_FILES['file1']['name']);
			//secure check for extension
			$ext = pathinfo($file_name, PATHINFO_EXTENSION);
			if(in_array($ext,$whitelist,true)) {
				$repertoireDestination = './upload/asset/model/'.$file_name;
				move_uploaded_file($_FILES['file1']['tmp_name'], $repertoireDestination);
				$qry=$db->prepare("INSERT INTO `tassets_model` (`type`,`manufacturer`,`image`,`name`,`ip`,`wifi`,`warranty`) VALUES (:type,:manufacturer,:image,:name,:ip,:wifi,:warranty)");
				$qry->execute(array(
					'type' => $_POST['type'],
					'manufacturer' => $_POST['manufacturer'],
					'image' => $file_name,
					'name' => $_POST['model'],
					'ip' => $_POST['ip'],
					'wifi' => $_POST['wifi'],
					'warranty' => $_POST['warranty']
					));
			} else {echo '<div class="alert alert-danger"><strong><i class="fa fa-remove"></i>'.T_('Blocage de sécurité').':</strong> '.T_('Type de fichier interdit').'.<br></div>';}
		} else{
			$qry=$db->prepare("INSERT INTO `tassets_model` (`type`,`manufacturer`,`name`,`ip`,`wifi`,`warranty`) VALUES (:type,:manufacturer,:name,:ip,:wifi,:warranty)");
			$qry->execute(array(
				'type' => $_POST['type'],
				'manufacturer' => $_POST['manufacturer'],
				'name' => $_POST['model'],
				'ip' => $_POST['ip'],
				'wifi' => $_POST['wifi'],
				'warranty' => $_POST['warranty']
				));
		}
	}elseif($_GET['table']=='tcompany') //special case datepicker
	{
        //convert date
        if($_POST['limit_hour_date_start'] && !strpos($_POST['limit_hour_date_start'], "-") && preg_match('~[0-9]~', $_POST['limit_hour_date_start']))
        {
            $_POST['limit_hour_date_start'] = DateTime::createFromFormat('d/m/Y', $_POST['limit_hour_date_start']);
            $_POST['limit_hour_date_start']=$_POST['limit_hour_date_start']->format('Y-m-d');
        }
        if($_POST['limit_ticket_date_start'] && !strpos($_POST['limit_ticket_date_start'], "-") && preg_match('~[0-9]~', $_POST['limit_ticket_date_start']))
        {
            $_POST['limit_ticket_date_start'] = DateTime::createFromFormat('d/m/Y', $_POST['limit_ticket_date_start']);
            $_POST['limit_ticket_date_start']=$_POST['limit_ticket_date_start']->format('Y-m-d');
        }
		
        $qry=$db->prepare("INSERT INTO `tcompany` (
            `name`,
            `address`,
            `zip`,
            `city`,
            `country`,
            `legal_status`,
            `SIRET`,
            `TVA`,
            `limit_ticket_number`,
            `limit_ticket_days`,
            `limit_ticket_date_start`,
            `limit_hour_number`,
            `limit_hour_days`,
            `limit_hour_date_start`,
            `information_message`
            ) VALUES (
                :name,
                :address,
                :zip,
                :city,
                :country,
                :legal_status,
                :SIRET,
                :TVA,
                :limit_ticket_number,
                :limit_ticket_days,
                :limit_ticket_date_start,
                :limit_hour_number,
                :limit_hour_days,
                :limit_hour_date_start,
                :information_message
            )");
        $qry->execute(array(
            'name' => $_POST['name'],
            'address' => $_POST['address'],
            'zip' => $_POST['zip'],
            'city' => $_POST['city'],
            'country' => $_POST['country'],
            'legal_status' => $_POST['legal_status'],
            'SIRET' => $_POST['SIRET'],
            'TVA' => $_POST['TVA'],
            'limit_ticket_number' => $_POST['limit_ticket_number'],
            'limit_ticket_days' => $_POST['limit_ticket_days'],
            'limit_ticket_date_start' => $_POST['limit_ticket_date_start'],
            'limit_hour_number' => $_POST['limit_hour_number'],
            'limit_hour_days' => $_POST['limit_hour_days'],
            'limit_hour_date_start' => $_POST['limit_hour_date_start'],
            'information_message' => $_POST['information_message']
            ));
	}
	else
	{
		//generate sql row name for selected table
		for ($i=1; $i <= $nbchamp; $i++)
		{
			if($i!="1") {$reqchamp="$reqchamp,{${'champ' . $i}}";} else {$reqchamp="`{${'champ' . $i}}`";}
		}
		//generate sql value for selected table
		for ($i=1; $i <= $nbchamp; $i++)
		{
			$nomchamp="{${'champ' . $i}}";
			if(!isset($_POST[$nomchamp])) $_POST[$nomchamp] = '';
			//secure string
            $_POST[$nomchamp]=htmlspecialchars($_POST[$nomchamp], ENT_QUOTES, 'UTF-8');
            $_POST[$nomchamp]=strip_tags($db->quote($_POST[$nomchamp])); 
			if($i!="1") {$reqvalue="$reqvalue,$_POST[$nomchamp]";} else {$reqvalue="$_POST[$nomchamp]";}
		}
		$db->exec("INSERT INTO $db_table ($reqchamp) VALUES ($reqvalue)");
	}

	$www = "./index.php?page=admin&subpage=list&table=$_GET[table]&action=disp_list";
	echo '<script language="Javascript">
	<!--
	document.location.replace("'.$www.'");
	// -->
	</script>';
	
}

if($_GET['action']=="disp_add")
{
    //check right before display list
    if(
        $rright['admin']!='0' ||
        ($_GET['table']=='tcategory' && $rright['admin_lists_category']!='0') ||
        ($_GET['table']=='tsubcat' && $rright['admin_lists_subcat']!='0') ||
        ($_GET['table']=='tcriticality' && $rright['admin_lists_criticality']!='0') ||
        ($_GET['table']=='tpriority' && $rright['admin_lists_priority']!='0') ||
        ($_GET['table']=='ttypes' && $rright['admin_lists_type']!='0')
    )
    {
        echo '
            <div class="pr-4 pl-4">
                <div class="widget-box">
                    <div class="pt-4 pb-2">
                        <h5 class="text-primary-m2"><i class="fa fa-plus-circle"><!----></i> '.T_("Ajout d'une entrée").' :</h5>
                        <hr class="mb-3 border-dotted">
                    </div>
                    <div class="widget-body">
                        <div class="widget-main no-padding">
                            <form method="post" enctype="multipart/form-data" action="./index.php?page=admin&amp;subpage=list&amp;table='.$_GET['table'].'&amp;action=add" >';
                                if($_GET['table']=='tcategory') //special case for limit service parameters 
                                {
                                    echo'
                                        <div class="form-group">
                                            <label for="category">'.T_('Catégorie').'</label>
                                            <input style="width:450px" class="form-control" name="category" id="category" type="text" value="" />
                                        </div>
                                        ';
                                        if($rparameters['user_limit_service'])
                                        {
                                            if($cnt_service==1)
                                            {
                                                echo '<input type="hidden" name="service" value="'.$user_services[0].'" />'; 
                                            } else {
                                                echo '
                                                    <div class="form-group">
                                                        <label for="service">'.T_('Service').'</label>
                                                        <select style="width:450px" class="form-control" name="service" id="service" >
                                                        ';
                                                            if($rright['dashboard_service_only']!=0 && $rparameters['user_limit_service']==1) {
                                                                //display only service associated with this user
                                                                $qry=$db->prepare("SELECT `tservices`.`id`,`tservices`.`name` FROM `tservices`,`tusers_services` WHERE `tservices`.`id`=`tusers_services`.`service_id` AND `tusers_services`.`user_id`=:user_id AND `tservices`.`disable`='0' ORDER BY `tservices`.`name`");
                                                                $qry->execute(array('user_id' => $_SESSION['user_id']));
                                                            } else {
                                                                //display all services
                                                                $qry=$db->prepare("SELECT `id`,`name` FROM `tservices` WHERE `disable`='0' ORDER BY `id`!=0,`name`");
                                                                $qry->execute();
                                                            }
                                                            while ($row=$qry->fetch()) 
                                                            {
                                                                echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                                            }
                                                            $qry->closeCursor();
                                                        echo '
                                                        </select>
                                                    </div>
                                                ';
                                            }
                                        }
                                        //auto tech attribute case
                                        if($rparameters['ticket_cat_auto_attribute'])
                                        {
                                            //display technician list
                                            echo '
                                                <div class="form-group">
                                                    <label for="technician">'.T_('Attribution automatique ').'</label>
                                                    <br />
                                                    <select style="width:450px" class="form-control d-inline-block" name="technician" id="form-field-select-1" >
                                                        ';
                                                        $qry2=$db->prepare("SELECT `id`,`firstname`,`lastname` FROM `tusers` WHERE (`profile`='0' OR `profile`='4') AND `disable`='0' OR `id`='0' ORDER BY `id`!=0,`lastname`");
                                                        $qry2->execute();
                                                        while ($row2=$qry2->fetch()) 
                                                        {
                                                            echo '<option value="'.$row2['id'].'">'.$row2['firstname'].' '.$row2['lastname'].'</option>';
                                                        }
                                                        $qry2->closeCursor();
                                                        echo '
                                                    </select>
                                                    '.T_('ou').'
                                                    <select style="width:450px" class="form-control d-inline-block" name="technician_group" id="form-field-select-1" >
                                                        ';
                                                        $qry2=$db->prepare("SELECT `id`,`name` FROM `tgroups` WHERE `type`='1' AND `disable`='0' OR `id`='0' ORDER BY `id`!=0,`name`");
                                                        $qry2->execute();
                                                        while ($row2=$qry2->fetch()) 
                                                        {
                                                            echo '
                                                            <option value="'.$row2['id'].'">
                                                                '.$row2['name'].'
                                                            </option>';
                                                        }
                                                        $qry2->closeCursor();
                                                        echo '
                                                    </select>
                                                </div>
                                            ';
                                        }
                                   
                                }elseif($_GET['table']=='tsubcat') //special case subcat 
                                {
                                    echo '
                                        <div class="form-group">
                                            <label for="cat">'.T_('Catégorie').'</label>
                                            <select style="width:450px" class="form-control" name="cat" id="cat">
                                            ';
                                                if($rright['dashboard_service_only']!=0 && $rparameters['user_limit_service']==1) {
                                                    //display only category associated services of this current user
                                                    $qry=$db->prepare("SELECT `tcategory`.`id`,`tcategory`.`name` FROM `tcategory` WHERE `tcategory`.`service` IN (SELECT `service_id` FROM `tusers_services` WHERE user_id=:user_id) ORDER BY `tcategory`.`name`");
                                                    $qry->execute(array('user_id' => $_SESSION['user_id']));
                                                } else {
                                                    //display all category
                                                    $qry=$db->prepare("SELECT `id`,`name` FROM `tcategory` ORDER BY `name`");
                                                    $qry->execute();
                                                }
                                                while ($row=$qry->fetch()) 
                                                {
                                                    echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                                }
                                                echo '
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="subcat">'.T_('Sous-catégorie').'</label>
                                            <input style="width:450px" class="form-control" name="subcat" id="subcat" type="text" value="" />
                                        </div>
                                    
                                        ';
                                        //auto tech attribute case
                                        if($rparameters['ticket_cat_auto_attribute'])
                                        {
                                            //display technician list
                                            echo '
                                            <div class="form-group">
                                                <label for="technician">'.T_('Attribution automatique ').'</label>
                                                <br />
                                                <select style="width:450px" class="form-control" name="technician" id="technician" >
                                                    ';
                                                    $qry2=$db->prepare("SELECT `id`,`firstname`,`lastname` FROM `tusers` WHERE (`profile`='0' OR `profile`='4') AND `disable`='0' OR `id`='0' ORDER BY `id`!=0,`lastname`");
                                                    $qry2->execute();
                                                    while ($row2=$qry2->fetch()) 
                                                    {
                                                        echo '<option value="'.$row2['id'].'">'.$row2['firstname'].' '.$row2['lastname'].'</option>';
                                                    }
                                                    $qry2->closeCursor();
                                                    echo '
                                                </select>
                                                '.T_('ou').'
                                                <select style="width:450px" class="form-control" name="technician_group" id="technician_group" >
                                                    ';
                                                    $qry2=$db->prepare("SELECT `id`,`name` FROM `tgroups` WHERE `type`='1' AND `disable`='0' OR `id`='0' ORDER BY `id`!=0,`name`");
                                                    $qry2->execute();
                                                    while ($row2=$qry2->fetch()) 
                                                    {
                                                        echo '<option value="'.$row2['id'].'">'.$row2['name'].'</option>';
                                                    }
                                                    $qry2->closeCursor();
                                                    echo '
                                                </select>
                                            </div>
                                            ';
                                        }
                                  
                                }
                                elseif($_GET['table']=='tcriticality') //special case for limit service parameters 
                                {
                                    echo'
                                    <div class="form-group">
                                        <label for="number">'.T_('Numéro').'</label>
                                        <input style="width:450px" class="form-control" name="number" id="number" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label for="name">'.T_('Nom').'</label>
                                        <input style="width:450px" class="form-control" name="name" id="name" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label for="color">'.T_('Couleur').'</label>
                                        <input style="width:450px" class="form-control" name="color" id="color" type="text" value="" />
                                    </div>
                                    ';
                                    if($rparameters['user_limit_service'])
                                    {
                                        if($cnt_service==1)
                                        {
                                            echo '<input type="hidden" name="service" value="'.$user_services[0].'" />'; 
                                        } else {
                                            echo '
                                            <div class="form-group">
                                                <label for="service">'.T_('Service').'</label>
                                                <select style="width:450px" class="form-control" name="service" id="service">
                                                ';
                                                    if($rright['dashboard_service_only']!=0 && $rparameters['user_limit_service']==1 && $_SESSION['profile_id']!=4) {
                                                        //display only service associated with this user
                                                        $qry=$db->prepare("SELECT `tservices`.`id`,`tservices`.`name` FROM `tservices`,`tusers_services` WHERE `tservices`.`id`=`tusers_services`.`service_id` AND `tusers_services`.`user_id`=:user_id AND `tservices`.`disable`='0' ORDER BY `tservices`.`name`");
                                                        $qry->execute(array('user_id' => $_SESSION['user_id']));
                                                    } else {
                                                        //display all services
                                                        $qry=$db->prepare("SELECT `id`,`name` FROM `tservices` WHERE `disable`='0' ORDER BY `name`");
                                                        $qry->execute();
                                                    }
                                                    while ($row=$qry->fetch()) 
                                                    {
                                                        echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                                    }
                                                    $qry->closeCursor();
                                                echo '
                                                </select>
                                            </div>
                                            ';
                                        }
                                    }
                                       
                                }
                                elseif($_GET['table']=='tpriority') //special case for limit service parameters 
                                {
                                    echo'
                                    <div class="form-group">
                                        <label for="number">'.T_('Numéro').'</label>
                                        <input style="width:450px" class="form-control" name="number" id="number" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label for="name">'.T_('Nom').'</label>
                                        <input style="width:450px" class="form-control" name="name" id="name" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label for="color">'.T_('Couleur').'</label>
                                        <input style="width:450px" class="form-control" name="color" id="color" type="text" value="" />
                                    </div>
                                    ';
                                    if($rparameters['user_limit_service'])
                                    {
                                        if($cnt_service==1)
                                        {
                                            echo '<input type="hidden" name="service" value="'.$user_services[0].'" />'; 
                                            
                                        } else {
                                            echo '
                                            <div class="form-group">
                                                <label for="service">'.T_('Service').'</label>
                                                <select style="width:450px" class="form-control" name="service" id="service1">
                                                ';
                                                    if($rright['dashboard_service_only']!=0 && $rparameters['user_limit_service']==1 && $_SESSION['profile_id']!=4) {
                                                        //display only service associated with this user
                                                        $qry=$db->prepare("SELECT `tservices`.`id`,`tservices`.`name` FROM `tservices`,`tusers_services` WHERE `tservices`.`id`=`tusers_services`.`service_id` AND `tusers_services`.`user_id`=:user_id AND `tservices`.`disable`='0' ORDER BY `tservices`.`name`");
                                                        $qry->execute(array('user_id' => $_SESSION['user_id']));
                                                    } else {
                                                        //display all services
                                                        $qry=$db->prepare("SELECT `id`,`name` FROM `tservices` WHERE `disable`='0' ORDER BY `name`");
                                                        $qry->execute();
                                                    }
                                                    while ($row=$qry->fetch()) 
                                                    {
                                                        echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                                    }
                                                    $qry->closeCursor();
                                                echo '
                                                </select>
                                            </div>
                                            ';
                                        }
                                    }
                                }
                                elseif($_GET['table']=='ttypes') //special case for limit service parameters 
                                {
                                    echo'
                                    <div class="form-group">
                                        <label for="name">'.T_('Nom').'</label>
                                        <input style="width:450px" class="form-control" name="name" id="name" type="text" value="" />
                                    </div>
                                    ';
                                    if($rparameters['user_limit_service'])
                                    {
                                        if($cnt_service==1)
                                        {
                                            echo '<input type="hidden" name="service" value="'.$user_services[0].'" />'; 
                                            
                                        } else {
                                            echo '
                                            <div class="form-group">
                                                <label for="service">'.T_('Service').'</label>
                                                <select style="width:450px" class="form-control" name="service" id="service">
                                                ';
                                                    if($rright['dashboard_service_only']!=0 && $rparameters['user_limit_service']==1) {
                                                        //display only service associated with this user
                                                        $qry=$db->prepare("SELECT `tservices`.`id`,`tservices`.`name` FROM `tservices`,`tusers_services` WHERE `tservices`.`id`=`tusers_services`.`service_id` AND `tusers_services`.`user_id`=:user_id AND `tservices`.`disable`='0' ORDER BY `tservices`.`name`");
                                                        $qry->execute(array('user_id' => $_SESSION['user_id']));
                                                    } else {
                                                        //display all services
                                                        $qry=$db->prepare("SELECT `id`,`name` FROM `tservices` WHERE `disable`='0' ORDER BY `name`");
                                                        $qry->execute();
                                                    }
                                                    while ($row=$qry->fetch()) 
                                                    {
                                                        echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                                    }
                                                    $qry->closeCursor();
                                                echo '
                                                </select>
                                            </div>
                                            ';
                                        }
                                    }
                                    if($rparameters['mail_auto_type'] && $rparameters['ticket_type'])
                                    {
                                        echo '
                                        <div class="form-group">
                                            <label for="mail">'.T_('Adresses mails').'&nbsp;</label>
                                            <input style="width:300px;" type="text" class="form-control " name="mail" id="mail" value="" />
                                            <span style="font-size:14px" ><i>('.T_('Vous pouvez ajouter plusieurs adresses mails en les séparant par des points virgules').')<i></span>
                                        </div>
                                        ';
                                    }
                                }
                                elseif($_GET['table']=='tassets_model') //special case assets_model
                                {
                                    echo '
                                    <div class="form-group">
                                        <label for="type">'.T_('Type').'</label>
                                        <select style="width:450px" class="form-control" name="type" id="type">
                                        ';
                                            $qry=$db->prepare("SELECT `id`,`name` FROM `tassets_type` ORDER BY `name`");
                                            $qry->execute();
                                            while ($rtype=$qry->fetch()) 
                                            {
                                                echo '<option value="'.$rtype['id'].'">'.$rtype['name'].'</option>';
                                            }
                                            $qry->closeCursor();
                                            echo '
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="manufacturer">'.T_('Fabriquant').'</label>
                                        <select style="width:450px" class="form-control" name="manufacturer" id="manufacturer">
                                        ';
                                            $qry=$db->prepare("SELECT `id`,`name` FROM `tassets_manufacturer` ORDER BY `name`");
                                            $qry->execute();
                                            while ($rman=$qry->fetch()) 
                                            {
                                                echo '<option value="'.$rman['id'].'">'.$rman['name'].'</option>';
                                            }
                                            $qry->closeCursor();
                                            echo '
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="model">'.T_('Modèle').'</label>
                                        <input style="width:450px" class="form-control" name="model" id="model" type="text" value="" />
                                        <input type="hidden" name="MAX_FILE_SIZE" value="100000000" />
                                    </div>
                                    <div class="form-group">
                                        <label for="file1">'.T_('Image').' <span style="font-size: x-small;"><i>(250px x 250px max)</i></span></label>
                                        <input name="file1" id="file1" type="file" />
                                    </div>
                                        ';
                                        if($rparameters['asset_ip']==1)
                                        {
                                            echo '
                                            <div class="form-group">
                                                <label for="ip">'.T_('Équipement IP').'&nbsp;</label>
                                                <br />
                                                <input type="radio" class="ace" value="1" name="ip"> <span class="lbl"> '.T_('Oui').' </span>
                                                &nbsp;
                                                <input type="radio" class="ace" value="0" name="ip"> <span class="lbl"> '.T_('Non').' </span>
                                            </div>
                                            <div class="form-group">
                                                <label for="wifi">'.T_('Équipement WIFI').'&nbsp;</label>
                                                <br />
                                                <input type="radio" class="ace" value="1" name="wifi"> <span class="lbl"> '.T_('Oui').' </span>
                                                &nbsp;
                                                <input type="radio" class="ace" value="0" name="wifi"> <span class="lbl"> '.T_('Non').' </span>
                                            </div>
                                            ';
                                        } else {echo '<input type="hidden" name="ip" value="0" /><input type="hidden" name="wifi" value="0" />';}
                                        echo '
                                        <div class="form-group">
                                            <label for="warranty">'.T_("Nombre d'années de garantie").'</label>
                                            <input style="width:450px" class="form-control" name="warranty" id="warranty" type="text" size="2" value="0" />
                                        </div>
                                   
                                    ';
                                } elseif($_GET['table']=='ttemplates') //special case ttemplates
                                {
                                    echo '
                                        <div class="form-group">
                                            <label for="name">'.T_("Nom").'</label>
                                            <input style="width:450px" class="form-control" name="name" id="name" type="text" size="2"  />
                                        </div>
                                        <div class="form-group">
                                            <label for="incident">'.T_("Numéro de ticket").'</label>
                                            <input style="width:450px" class="form-control" name="incident" id="incident" type="text" size="2"  />
                                        </div>
                                   ';
                                   if($rparameters['ticket_recurrent_create'])
                                   {
                                       echo '
                                       <div class="form-group">
                                           <label for="name">'.T_('Tickets récurrents').' :</label>
                                           <ul>
                                               <li>
                                                   '.T_('Date de début').'
                                                   <input style="width:410px" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#ticket_recurrent_date_start" id="ticket_recurrent_date_start" name="ticket_recurrent_date_start" type="text" value="" />
                                               </li>
                                               <li>
                                                   '.T_('Fréquence').'
                                                   <select style="width:410px" id="ticket_recurrent_date_frequency" name="ticket_recurrent_date_frequency" class="form-control" >
                                                       <option value="daily">'.T_('Tous les jours').'</option>
                                                       <option value="weekly">'.T_('Tous les semaines').'</option>
                                                       <option value="monthly">'.T_('Tous les mois').'</option>
                                                       <option value="yearly">'.T_('Tous les ans').'</option>
                                                  </select>
                                               </li>
                                           </ul>
                                       </div>
                                   ';
                                   }
                                   
                                } elseif($_GET['table']=='tcompany') //special case datepicker
                                {
                                    echo '
                                    <div class="form-group">
                                        <label for="name">'.T_('Nom').'</label>
                                        <input style="width:450px" class="form-control" id="name" name="name" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label for="address">'.T_('Adresse').'</label>
                                        <input style="width:450px" class="form-control" id="address" name="address" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label for="zip">'.T_('Code postal').'</label>
                                        <input style="width:450px" class="form-control" id="zip" name="zip" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label for="city">'.T_('Ville').'</label>
                                        <input style="width:450px" class="form-control" id="city" name="city" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label for="country">'.T_('Pays').'</label>
                                        <input style="width:450px" class="form-control" id="country" name="country" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label for="legal_status">'.T_('Forme juridique').'</label>
                                        <input style="width:450px" class="form-control" id="legal_status" name="legal_status" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label for="SIRET">'.T_('SIRET').'</label>
                                        <input style="width:450px" class="form-control" id="SIRET" name="SIRET" type="text" value="" />
                                    </div>
                                    <div class="form-group">
                                        <label for="TVA">'.T_('TVA').'</label>
                                        <input style="width:450px" class="form-control" id="TVA" name="TVA" type="text" value="" />
                                    </div>
                                    ';
                                    if($rparameters['company_limit_ticket'])
                                    {
                                        echo '
                                        <div class="form-group">
                                            <label for="limit_ticket_number">'.T_('Nombre de limite de ticket').'</label>
                                            <input style="width:450px" class="form-control" id="limit_ticket_number" name="limit_ticket_number" type="text" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label for="limit_ticket_days">'.T_('Nombre de limite de jours').'</label>
                                            <input style="width:450px" class="form-control" id="limit_ticket_days" name="limit_ticket_days" type="text" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label for="limit_ticket_date_start">'.T_('Date de début de la limite de jours').'</label>
                                            <input style="width:450px" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#limit_ticket_date_start" id="limit_ticket_date_start" name="limit_ticket_date_start" type="text" value="" autocomplete="off" />
                                        </div>
                                        ';
                                    }
                                    if($rparameters['company_limit_hour'])
                                    {
                                        echo '
                                        <div class="form-group">
                                            <label for="limit_hour_number">'.T_("Nombre de limite d'heures").'</label>
                                            <input style="width:450px" class="form-control" id="limit_hour_number" name="limit_hour_number" type="text" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label for="limit_hour_days">'.T_('Nombre de limite de jours').'</label>
                                            <input style="width:450px" class="form-control" id="limit_hour_days" name="limit_hour_days" type="text" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label for="limit_hour_date_start">'.T_('Date de début de la limite de jours').'</label>
                                            <input style="width:450px" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#limit_hour_date_start" id="limit_hour_date_start" name="limit_hour_date_start" type="text" value="" autocomplete="off" />
                                        </div>
                                        ';
                                    }
                                    if($rparameters['company_message'])
                                    {
                                        echo '
                                        <div class="form-group">
                                            <label for="information_message">'.T_("Message d'information").'</label>
                                            <textarea style="width:450px" class="form-control" id="information_message" name="information_message"></textarea> 
                                        </div>
                                        ';
                                    }
                                   
                                } else
                                {
                                    for ($i=1; $i <= $nbchamp; $i++)
                                    {
                                        //translate label name
                                        $label_name=${'champ' . $i}; //default value
                                        if(${'champ' . $i}=='id') {$label_name=T_('Identifiant');}
                                        if(${'champ' . $i}=='name') {$label_name=T_('Libellé');}
                                        if(${'champ' . $i}=='name') {$label_name=T_('Libellé');}
                                        if(${'champ' . $i}=='cat') {$label_name=T_('Catégorie');}
                                        if(${'champ' . $i}=='disable') {$label_name=T_('Désactivé');}
                                        if(${'champ' . $i}=='number') {$label_name=T_('Ordre');}
                                        if(${'champ' . $i}=='color') {$label_name=T_('Couleur');}
                                        if(${'champ' . $i}=='description') {$label_name=T_('Description');}
                                        if(${'champ' . $i}=='mail_object') {$label_name=T_('Objet du mail');}
                                        if(${'champ' . $i}=='display') {$label_name=T_("Couleur d'affichage");}
                                        if(${'champ' . $i}=='icon') {$label_name=T_("Icône");}
                                        if(${'champ' . $i}=='incident') {$label_name=T_("Numéro ticket");}
                                        if(${'champ' . $i}=='address') {$label_name=T_("Adresse");}
                                        if(${'champ' . $i}=='zip') {$label_name=T_("Code postal");}
                                        if(${'champ' . $i}=='city') {$label_name=T_("Ville");}
                                        if(${'champ' . $i}=='country') {$label_name=T_("Pays");}
                                        if(${'champ' . $i}=='legal_status') {$label_name=T_("Forme juridique");}
                                        if(${'champ' . $i}=='limit_ticket_number') {$label_name=T_("Nombre de limite de ticket");}
                                        if(${'champ' . $i}=='limit_ticket_days') {$label_name=T_("Nombre de limite de jours");}
                                        if(${'champ' . $i}=='limit_ticket_date_start') {$label_name=T_("Date de début de la limite de jours");}
                                        if(${'champ' . $i}=='limit_hour_number') {$label_name=T_("Nombre de limite d'heures");}
                                        if(${'champ' . $i}=='limit_hour_days') {$label_name=T_("Nombre de limite de jours");}
                                        if(${'champ' . $i}=='limit_hour_date_start') {$label_name=T_("Date de début de la limite de jours");}
                                        if(${'champ' . $i}=='min') {$label_name=T_("Minutes");}
                                        if(${'champ' . $i}=='virtualization') {$label_name=T_("Virtualisation");}
                                        if(${'champ' . $i}=='manufacturer') {$label_name=T_("Fabricant");}
                                        if(${'champ' . $i}=='image') {$label_name=T_("Image");}
                                        if(${'champ' . $i}=='ip') {$label_name=T_("Équipement IP");}
                                        if(${'champ' . $i}=='type') {$label_name=T_("Type");}
                                        if(${'champ' . $i}=='wifi') {$label_name=T_("Équipement WIFI");}
                                        if(${'champ' . $i}=='warranty') {$label_name=T_("Années de garantie");}
                                        if(${'champ' . $i}=='order') {$label_name=T_("Ordre");}
                                        if(${'champ' . $i}=='block_ip_search') {$label_name=T_("Blocage de recherche IP");}
                                        if(${'champ' . $i}=='mail') {$label_name=T_("Adresse mail");}
                                        if(${'champ' . $i}=='service') {$label_name=T_("Service");}
                                        if(${'champ' . $i}=='network') {$label_name=T_("Réseau");}
                                        if(${'champ' . $i}=='netmask') {$label_name=T_("Masque");}
                                        if(${'champ' . $i}=='scan') {$label_name=T_("Scan");}
                                        if(${'champ' . $i}=='meta') {$label_name=T_("État à traiter");}
                                        if(${'champ' . $i}=='user_validation') {$label_name=T_("Validation demandeur");}
                                        
                                        if($_GET['table']=='tcompany' && !$rparameters['company_limit_ticket'] && ($i==9 || $i==10 || $i==11))
                                        {
                                            //hide field
                                        }elseif($_GET['table']=='tcompany' && !$rparameters['company_limit_hour'] && ($i==11 || $i==12 || $i==13))
                                        {
                                            //hide field
                                        }elseif(${'champ' . $i}=='ldap_guid')
                                        {
                                            //hide field
                                        } else {

                                            //hide disable field on create 
                                            if(${'champ' . $i}=='disable') {
                                                //hide
                                            } else {
                                                echo "
                                                <div class=\"form-group\">
                                                    <label for=\"{${'champ' . $i}}\">$label_name</label>
                                                    <input style=\"width:450px\" class=\"form-control\" name=\"{${'champ' . $i}}\" id=\"{${'champ' . $i}}\" type=\"text\" value=\"\" />
                                                </div>
                                                ";
                                            }
                                        }
                                    }
                                }
                                //display color informations and information on critical table
                                if(($_GET['table']=='tcriticality' || $_GET['table']=='tpriority') && ($_GET['action']=='disp_edit' || $_GET['action']=='disp_add'))
                                {
                                    echo T_('Liste des couleurs par défaut').' : ';
                                    echo '<b><span style="color:#82af6f">#82af6f</span></b>&nbsp;';
                                    echo '<b><span style="color:#f8c806">#f8c806</span></b>&nbsp;';
                                    echo '<b><span style="color:#f89406">#f89406</span></b>&nbsp;';
                                    echo '<b><span style="color:#d15b47">#d15b47</span></b>&nbsp;';
                                    echo '<br /><br /><i class="fa fa-question-circle text-primary-m2"></i> '.T_("Le numéro permet de sélectionner l'ordre de trie");
                                }
                                if(($_GET['table']=='tstates' || $_GET['table']=='tassets_state'))
                                {
                                    echo ''.T_('Liste des styles par défaut').' :<br />';
                                    echo '<span class="badge text-75 border-l-3 brc-black-tp8 bgc-success text-white">badge text-75 border-l-3 brc-black-tp8 bgc-success text-white</span><br />';
                                    echo '<span class="badge text-75 border-l-3 brc-black-tp8 bgc-danger text-white">badge text-75 border-l-3 brc-black-tp8 bgc-danger text-white</span><br />';
                                    echo '<span class="badge text-75 border-l-3 brc-black-tp8 bgc-warning text-white">badge text-75 border-l-3 brc-black-tp8 bgc-warning text-white</span><br />';
                                    echo '<span class="badge text-75 border-l-3 brc-black-tp8 bgc-primary text-white">badge text-75 border-l-3 brc-black-tp8 bgc-primary text-white</span><br />';
                                    echo '<span class="badge text-75 border-l-3 brc-black-tp8 bgc-secondary text-white">badge text-75 border-l-3 brc-black-tp8 bgc-secondary text-white</span><br />';
                                    echo '<span class="badge text-75 border-l-3 brc-black-tp8 bgc-info text-white">badge text-75 border-l-3 brc-black-tp8 bgc-info text-white</span><br />';
                                    echo '<span class="badge text-75 border-l-3 brc-black-tp8 bgc-dark text-white">badge text-75 border-l-3 brc-black-tp8 bgc-dark text-white</span><br />';
                                    echo '<span class="badge text-75 border-l-3 brc-black-tp8 bgc-pink text-white">badge text-75 border-l-3 brc-black-tp8 bgc-pink text-white</span><br />';
                                    echo '<span class="badge text-75 border-l-3 brc-black-tp8 bgc-purple text-white">badge text-75 border-l-3 brc-black-tp8 bgc-purple text-white</span><br />';
                                    echo '<span class="badge text-75 border-l-3 brc-black-tp8 bgc-yellow">badge text-75 border-l-3 brc-black-tp8 bgc-yellow</span><br />';
                                    echo '<span class="badge text-75 border-l-3 brc-black-tp8 bgc-grey text-white">badge text-75 border-l-3 brc-black-tp8 bgc-grey text-white</span><br />';
                                    echo '<span class="badge text-75 border-l-3 brc-black-tp8 bgc-light">badge text-75 border-l-3 brc-black-tp8 bgc-light</span><br />';
                                    echo '<span class="badge text-75 border-l-3 brc-black-tp8 bgc-default text-white">badge text-75 border-l-3 brc-black-tp8 bgc-default text-white</span><br />';
                                    echo '<span class="badge text-75 border-l-3 brc-black-tp8 bgc-brown text-white">badge text-75 border-l-3 brc-black-tp8 bgc-brown text-white</span><br />';
                                    echo '<br />';
                                    echo '<span class="badge text-75 badge-info arrowed-in arrowed-in-right">badge text-75 badge-info arrowed-in arrowed-in-right</span><br />';
                                    echo '<span class="badge bgc-secondary-l1 text-dark-tp4 border-1 brc-black-tp10">badge bgc-secondary-l1 text-dark-tp4 border-1 brc-black-tp10</span><br />';
                                    echo '<span class="badge badge-warning badge-pill px-25">badge badge-warning badge-pill px-25</span><br />';
                                    echo '<span class="badge badge-sm badge-light">badge badge-sm badge-light</span><br />';
                                    echo '<span class="badge badge-sm badge-dark">badge badge-sm badge-dark</span><br />';
                                }
                                echo '
                                <div class="border-t-1 brc-secondary-l1 bgc-secondary-l3 py-3 text-center mt-5">
                                    <button id="submit" type="submit" class="btn action-btn btn-success">
                                        <i class="fa fa-check"><!----></i>
                                        '.T_('Ajouter').'
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                        
                </div>
            </div>

            <!-- datetimepicker scripts for company table -->
            <script src="./vendor/moment/moment/min/moment.min.js" charset="UTF-8"></script>
            ';
                if($ruser['language']=='fr_FR') {echo '<script src="./vendor/moment/moment/locale/fr.js" charset="UTF-8"></script>';} 
                if($ruser['language']=='de_DE') {echo '<script src="./vendor/moment/moment/locale/de.js" charset="UTF-8"></script>';} 
                if($ruser['language']=='es_ES') {echo '<script src="./vendor/moment/moment/locale/es.js" charset="UTF-8"></script>';} 
                if($ruser['language']=='it_IT') {echo '<script src="./vendor/moment/moment/locale/it.js" charset="UTF-8"></script>';} 
            echo '
                <script src="./vendor/components/tempusdominus/bootstrap-4/build/js/tempusdominus-bootstrap-4.min.js" charset="UTF-8"></script> 
            ';
    } else  {
        echo DisplayMessage('error',T_("Vous n'avez pas le droit d'ajouter une entrée sur cette liste, contactez votre administrateur"));
    }
}
?>