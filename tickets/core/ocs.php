<?php
################################################################################
# @Name : ocs.php
# @Call : connector.php
# @Description : synchronize assets from OCS Inventory
# @Author : Flox
# @Create : 18/08/2023
# @Update : 24/08/2023
# @Version : 3.2.38
################################################################################

if(php_sapi_name() == "cli")
{
	//database connection
	require_once(__DIR__."/../vendor/autoload.php");
	require_once(__DIR__."/../connect.php");
    require_once(__DIR__."/../core/functions.php");
	
	//switch SQL MODE to allow empty values with latest version of MySQL
	$db->exec('SET sql_mode = ""');
	
	//load parameters table
	$qry=$db->prepare("SELECT * FROM `tparameters`");
	$qry->execute();
	$rparameters=$qry->fetch();
	$qry->closeCursor();
		
	//locales
	$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	if($lang=='fr') {$_GET['lang'] = 'fr_FR';}
	else {$_GET['lang'] = 'en_US';}
	define('PROJECT_DIR', realpath('../'));
	define('LOCALE_DIR', PROJECT_DIR .'/locale');
	define('DEFAULT_LOCALE', '($_GET[lang]');
	require_once(__DIR__.'/../vendor/components/php-gettext/gettext.inc');
	$encoding = 'UTF-8';
	$locale = (isset($_GET['lang']))? $_GET['lang'] : DEFAULT_LOCALE;
	T_setlocale(LC_MESSAGES, $locale);
	T_bindtextdomain($_GET['lang'], LOCALE_DIR);
	T_bind_textdomain_codeset($_GET['lang'], $encoding);
	T_textdomain($_GET['lang']);

    //run on cli
    $_GET['action']='run';

    //display php.ini location
    echo "php.ini location : ".php_ini_loaded_file();

} elseif(!$rright['admin']) {echo DisplayMessage('error',T_("Accès interdit")); exit;}

//security check
if($rparameters['ocs'])
{
   //display headers
   echo '
      <div class="page-header position-relative">
         <h1 class="page-title text-primary-m2">
            <i class="fa fa-sync"></i>   
            '.T_('Synchronisation').' : OCS > GestSup 
         </h1>
      </div>
         
      <button onclick=\'window.location.href="index.php?page=core/ocs&amp;action=simul"\' type="submit" class="btn btn-success">
         <i class="fa fa-flask"></i>
      '.T_('Lancer une simulation').'
      </button>
      <button onclick=\'window.location.href="index.php?page=core/ocs&amp;action=run"\' type="submit" class="btn btn-warning">
         <i class="fa fa-bolt text-white"></i>
         <span class="text-white">'.T_('Lancer la synchronisation').'</span>
      </button>
      <button onclick=\'window.location.href="index.php?page=admin&subpage=parameters&tab=connector"\' type="submit" class="btn btn-primary btn-danger">
         <i class="fa fa-reply"></i>
         '.T_('Retour').'
      </button>	
      <br />
      <br />
   ';

   if($_GET['action']=='simul' || $_GET['action']=='run')
   {
    //init var
    $cnt=0;

    //get all asset id
    $api_url = $rparameters['ocs_server_url'].'/ocsapi/v1/computers/listID';
    $json_data = file_get_contents($api_url);
    $response_data_list = json_decode($json_data);
    foreach ($response_data_list as $ocs_uid) {
        $cnt++;
        //get information about this asset
        $api_url = $rparameters['ocs_server_url'].'/ocsapi/v1/computer/'.$ocs_uid->ID.'/bios';
        $json_data = file_get_contents($api_url);
        $response_data = json_decode($json_data, true);

        /*
        //dump all var
        echo '<pre>';
        echo print_r($response_data[$ocs_uid->ID]['hardware']);
        echo '</pre>';
        */

        $ocs_id=$ocs_uid->ID;
        $ocs_netbios=$response_data[$ocs_uid->ID]['hardware']['NAME'];
        $ocs_ip=$response_data[$ocs_uid->ID]['hardware']['IPSRC'];
        $ocs_type=$response_data[$ocs_uid->ID]['bios'][0]['TYPE'];
        $ocs_manufacturer=$response_data[$ocs_uid->ID]['bios'][0]['MMANUFACTURER'];
        $ocs_sn_manufacturer=$response_data[$ocs_uid->ID]['bios'][0]['MSN'];
        $ocs_model=$response_data[$ocs_uid->ID]['bios'][0]['SMODEL'];
        $ocs_uuid=$response_data[$ocs_uid->ID]['hardware']['UUID'];
        $ocs_mac='';
        
        //get mac address
        $api_url = $rparameters['ocs_server_url'].'/ocsapi/v1/computer/'.$ocs_uid->ID.'/networks';
        $json_data = file_get_contents($api_url);
        $response_data_network = json_decode($json_data, true);
        foreach ($response_data_network[$ocs_uid->ID]['networks'] as $ocs_network) {
            if($ocs_network['IPADDRESS']==$ocs_ip){$ocs_mac=str_replace(':','',$ocs_network['MACADDR']);}
        }

        /*
        //dump all var
        echo '<pre>';
        echo print_r($response_data_network);
        echo '</pre>';
        */

        //secure check
        $ocs_id=htmlspecialchars("$ocs_id", ENT_QUOTES, 'UTF-8');
        $ocs_netbios=htmlspecialchars("$ocs_netbios", ENT_QUOTES, 'UTF-8');
        $ocs_ip=htmlspecialchars("$ocs_ip", ENT_QUOTES, 'UTF-8');
        $ocs_type=htmlspecialchars("$ocs_type", ENT_QUOTES, 'UTF-8');
        $ocs_manufacturer=htmlspecialchars("$ocs_manufacturer", ENT_QUOTES, 'UTF-8');
        $ocs_sn_manufacturer=htmlspecialchars("$ocs_sn_manufacturer", ENT_QUOTES, 'UTF-8');
        $ocs_model=htmlspecialchars("$ocs_model", ENT_QUOTES, 'UTF-8');
        $ocs_uuid=htmlspecialchars("$ocs_uuid", ENT_QUOTES, 'UTF-8');
        $ocs_mac=htmlspecialchars("$ocs_mac", ENT_QUOTES, 'UTF-8');
        
        if($rparameters['debug']) {
            echo '[DEBUG] OCS DATA : ';
            echo "ocs_id=$ocs_id | ";
            echo "uuid=$ocs_uuid | ";
            echo "type=$ocs_type | ";
            echo "manufacturer=$ocs_manufacturer | ";
            echo "sn_manufacturer=$ocs_sn_manufacturer | ";
            echo "model=$ocs_model | ";
            echo "netbios=$ocs_netbios | ";
            echo "ip=$ocs_ip | ";
            echo "mac=$ocs_mac | ";
        }

        //check existing ocs asset in gestsup db
        $qry=$db->prepare("SELECT `tassets`.`id`,`tassets`.`uuid`,`tassets`.`netbios` ,`tassets`.`sn_manufacturer` ,`tassets`.`type` ,`tassets`.`manufacturer` ,`tassets`.`model` 
        FROM `tassets`
        LEFT JOIN tassets_iface ON `tassets`.`id`=`tassets_iface`.`asset_id`
        WHERE 
        (
            `tassets`.`uuid`=:uuid OR 
            `tassets`.`netbios`=:netbios OR 
            `tassets`.`sn_manufacturer`=:sn_manufacturer OR
            `tassets_iface`.`mac`=:mac
        ) AND `tassets`.`disable`=0
        ");
        $qry->execute(array(
            'uuid' => $ocs_uuid,
            'netbios' => $ocs_netbios,
            'sn_manufacturer' => $ocs_sn_manufacturer,
            'mac' => $ocs_mac
        ));
        $gs_asset=$qry->fetch();
        $qry->closeCursor();

        if(isset($gs_asset['id']))
        {
            if($rparameters['debug']) {echo 'found in gs db (gs_id='.$gs_asset['id'].')<br />';}

            //update asset uuid
            if($ocs_uuid && !$gs_asset['uuid'] && $gs_asset['uuid']!=$ocs_uuid)
            {
                echo '<i class="fa fa-sync text-warning"></i> '.T_("Mise à jour de l'UUID").' '.$ocs_uuid.' pour '.$gs_asset['netbios'].' (id='.$gs_asset['id'].')<br />';
                if($_GET['action']=='run')
                {
                    $qry=$db->prepare("UPDATE `tassets` SET `uuid`=:uuid WHERE `id`=:id");
                    $qry->execute(array('uuid' => $ocs_uuid,'id' => $gs_asset['id']));
                }
            }

            //update asset netbios
            if($ocs_netbios && $gs_asset['netbios']!=$ocs_netbios)
            {
                echo '<i class="fa fa-sync text-warning"></i> '.T_("Mise à jour de NOM").' '.$ocs_netbios.' pour '.$gs_asset['netbios'].' (id='.$gs_asset['id'].')<br />';
                if($_GET['action']=='run')
                {
                    $qry=$db->prepare("UPDATE `tassets` SET `netbios`=:netbios WHERE `id`=:id");
                    $qry->execute(array('netbios' => $ocs_netbios,'id' => $gs_asset['id']));
                }
            }

            //update asset sn_manufacturer
            if($ocs_sn_manufacturer && $gs_asset['sn_manufacturer']!=$ocs_sn_manufacturer)
            {
                echo '<i class="fa fa-sync text-warning"></i> '.T_("Mise à jour de Numéro de série constructeur").' '.$ocs_sn_manufacturer.' pour '.$gs_asset['netbios'].' (id='.$gs_asset['id'].')<br />';
                if($_GET['action']=='run')
                {
                    $qry=$db->prepare("UPDATE `tassets` SET `sn_manufacturer`=:sn_manufacturer WHERE `id`=:id");
                    $qry->execute(array('sn_manufacturer' => $ocs_sn_manufacturer,'id' => $gs_asset['id']));
                }
            }

            //update type 
            if($ocs_type){
                $qry=$db->prepare("SELECT `id` FROM `tassets_type` WHERE name=:name");
                $qry->execute(array('name' => $ocs_type));
                $type=$qry->fetch();
                $qry->closeCursor();
                if(!isset($type['id']))
                {
                    //create type
                    echo '<i class="fa fa-plus-circle text-success"></i> '.T_("Création du type d'équipement").' '.$ocs_type.' pour '.$gs_asset['netbios'].' (id='.$gs_asset['id'].')<br />';
                    if($_GET['action']=='run')
                    {
                        $qry=$db->prepare("INSERT INTO `tassets_type` (`name`) VALUES (:name)");
                        $qry->execute(array('name' => $ocs_type));
                        $type_id=$db->lastInsertId();
                    } else {$type_id=0;}

                    //update type on asset
                    echo '<i class="fa fa-sync text-warning"></i> '.T_("Mise à jour du type ").' '.$ocs_type.' pour '.$gs_asset['netbios'].' (id='.$gs_asset['id'].')<br />';
                    if($_GET['action']=='run' && $type_id)
                    {
                        $qry=$db->prepare("UPDATE `tassets` SET `type`=:type WHERE `id`=:id");
                        $qry->execute(array('type' => $type_id,'id' => $gs_asset['id']));
                    }
                } elseif($gs_asset['type']!=$type['id']) {
                  
                    //update type on asset
                    echo '<i class="fa fa-sync text-warning"></i> '.T_("Mise à jour du type ").' '.$ocs_type.' pour '.$gs_asset['netbios'].' (id='.$gs_asset['id'].')<br />';
                    if($_GET['action']=='run' && $type['id'])
                    {
                        $qry=$db->prepare("UPDATE `tassets` SET `type`=:type WHERE `id`=:id");
                        $qry->execute(array('type' => $type['id'],'id' => $gs_asset['id']));
                    }
                    $type_id=$type['id'];
                } else {$type_id=$type['id'];}
            }

            //update manufacturer 
            if($ocs_manufacturer){
                $qry=$db->prepare("SELECT `id` FROM `tassets_manufacturer` WHERE name=:name");
                $qry->execute(array('name' => $ocs_manufacturer));
                $manufacturer=$qry->fetch();
                $qry->closeCursor();
                if(!isset($manufacturer['id']))
                {
                    //create manufacturer
                    echo '<i class="fa fa-plus-circle text-success"></i> '.T_("Création du fabricant d'équipement").' '.$ocs_manufacturer.' pour '.$gs_asset['netbios'].' (id='.$gs_asset['id'].')<br />';
                    if($_GET['action']=='run')
                    {
                        $qry=$db->prepare("INSERT INTO `tassets_manufacturer` (`name`) VALUES (:name)");
                        $qry->execute(array('name' => $ocs_manufacturer));
                        $manufacturer_id=$db->lastInsertId();
                    }  else {$manufacturer_id=0;}

                    //update manufacturer on asset
                    echo '<i class="fa fa-sync text-warning"></i> '.T_("Mise à jour du fabricant ").' '.$ocs_manufacturer.' pour '.$gs_asset['netbios'].' (id='.$gs_asset['id'].')<br />';
                    if($_GET['action']=='run' && $manufacturer_id)
                    {
                        $qry=$db->prepare("UPDATE `tassets` SET `manufacturer`=:manufacturer WHERE `id`=:id");
                        $qry->execute(array('manufacturer' => $manufacturer_id,'id' => $gs_asset['id']));
                    }
                } elseif($gs_asset['manufacturer']!=$manufacturer['id']) {
                    //update manufacture on asset
                    echo '<i class="fa fa-sync text-warning"></i> '.T_("Mise à jour du fabricant ").' '.$ocs_manufacturer.' pour '.$gs_asset['netbios'].' (id='.$gs_asset['id'].')<br />';
                    if($_GET['action']=='run' && $manufacturer['id'])
                    {
                        $qry=$db->prepare("UPDATE `tassets` SET `manufacturer`=:manufacturer WHERE `id`=:id");
                        $qry->execute(array('manufacturer' => $manufacturer['id'],'id' => $gs_asset['id']));
                    } 
                    $manufacturer_id=$manufacturer['id'];
                } else {$manufacturer_id=$manufacturer['id'];}
            }

            //update model 
            if($ocs_model){
                if($ocs_ip) {$ip_asset='1';} else {$ip_asset='0';}
                $qry=$db->prepare("SELECT `id` FROM `tassets_model` WHERE name=:name");
                $qry->execute(array('name' => $ocs_model));
                $model=$qry->fetch();
                $qry->closeCursor();
                if(!isset($model['id']))
                {
                    //create model
                    echo '<i class="fa fa-plus-circle text-success"></i> '.T_("Création du modèle d'équipement").' '.$ocs_model.' pour '.$gs_asset['netbios'].' (id='.$gs_asset['id'].')<br />';
                    if($_GET['action']=='run')
                    {
                        $qry=$db->prepare("INSERT INTO `tassets_model` (`name`,`type`,`manufacturer`,`ip`) VALUES (:name,:type,:manufacturer,:ip)");
                        $qry->execute(array('name' => $ocs_model,'type' => $type_id,'manufacturer' => $manufacturer_id,'ip' => $ip_asset));
                        $model_id=$db->lastInsertId();
                    }

                    //update model on asset
                    echo '<i class="fa fa-sync text-warning"></i> '.T_("Mise à jour du modèle ").' '.$ocs_model.' pour '.$gs_asset['netbios'].' (id='.$gs_asset['id'].')<br />';
                    if($_GET['action']=='run' && $model_id)
                    {
                        $qry=$db->prepare("UPDATE `tassets` SET `model`=:model WHERE `id`=:id");
                        $qry->execute(array('model' => $model_id,'id' => $gs_asset['id']));
                    }
                } elseif($gs_asset['model']!=$model['id']) {
                    //update ocs_model on asset
                    echo '<i class="fa fa-sync text-warning"></i> '.T_("Mise à jour du fabricant ").' '.$ocs_model.' pour '.$gs_asset['netbios'].' (id='.$gs_asset['id'].')<br />';
                    if($_GET['action']=='run' && $model['id'])
                    {
                        $qry=$db->prepare("UPDATE `tassets` SET `model`=:model WHERE `id`=:id");
                        $qry->execute(array('model' => $model['id'],'id' => $gs_asset['id']));
                    }
                }
            }

            //update ip
            if($ocs_ip){
                $qry=$db->prepare("SELECT `id` FROM `tassets_iface` WHERE `asset_id`=:asset_id AND `ip`=:ip AND `disable`=0");
                $qry->execute(array('asset_id' => $gs_asset['id'],'ip' => $ocs_ip));
                $ip=$qry->fetch();
                $qry->closeCursor();
                if(!isset($ip['id']))
                {
                    //create iface with ip
                    echo '<i class="fa fa-plus-circle text-success"></i> '.T_("Création de l'interface IP").' '.$ocs_ip.' pour '.$gs_asset['netbios'].' (id='.$gs_asset['id'].')<br />';
                    if($_GET['action']=='run')
                    {
                        $qry=$db->prepare("INSERT INTO `tassets_iface` (`asset_id`,`ip`,`mac`,`role_id`,`netbios`) VALUES (:asset_id,:ip,:mac,1,:netbios)");
                        $qry->execute(array('asset_id' => $gs_asset['id'],'ip' => $ocs_ip,'mac' => $ocs_mac,'netbios' => $ocs_netbios));
                        $iface_id=$db->lastInsertId();
                    }
                }
            }
        } else {
            if($rparameters['debug']) {echo 'not found in gs db <br />';}
            //create asset
            echo '<i class="fa fa-plus-circle text-success"></i> '.T_("Création de l'équipement").' '.$ocs_netbios.'<br />';
            if($_GET['action']=='run')
            {
                $qry=$db->prepare("INSERT INTO `tassets` (`sn_manufacturer`,`netbios`,`uuid`,`state`) VALUES (:sn_manufacturer,:netbios,:uuid,2)");
                $qry->execute(array('sn_manufacturer' => $ocs_sn_manufacturer,'netbios' => $ocs_netbios,'uuid' => $ocs_uuid));
                $gs_asset=array();
                $gs_asset['id']=$db->lastInsertId();
            } else {
                $gs_asset=array();
                $gs_asset['id']=0;
            }

             //update type 
             if($ocs_type){
                $qry=$db->prepare("SELECT `id` FROM `tassets_type` WHERE name=:name");
                $qry->execute(array('name' => $ocs_type));
                $type=$qry->fetch();
                $qry->closeCursor();
                if(!isset($type['id']))
                {
                    //create type
                    echo '<i class="fa fa-plus-circle text-success"></i> '.T_("Création du type d'équipement").' '.$ocs_type.' pour '.$ocs_netbios.' <br />';
                    if($_GET['action']=='run')
                    {
                        $qry=$db->prepare("INSERT INTO `tassets_type` (`name`) VALUES (:name)");
                        $qry->execute(array('name' => $ocs_type));
                        $type_id=$db->lastInsertId();
                    } else {$type_id=0;}

                    //update type on asset
                    if($_GET['action']=='run' && $type_id)
                    {
                        $qry=$db->prepare("UPDATE `tassets` SET `type`=:type WHERE `id`=:id");
                        $qry->execute(array('type' => $type_id,'id' => $gs_asset['id']));
                    }
                }else{
                    //update type on asset
                    if($_GET['action']=='run' && $type['id'])
                    {
                        $qry=$db->prepare("UPDATE `tassets` SET `type`=:type WHERE `id`=:id");
                        $qry->execute(array('type' => $type['id'],'id' => $gs_asset['id']));
                    }
                    $type_id=$type['id'];
                }
            }

            //update manufacturer 
            if($ocs_manufacturer){
                $qry=$db->prepare("SELECT `id` FROM `tassets_manufacturer` WHERE name=:name");
                $qry->execute(array('name' => $ocs_manufacturer));
                $manufacturer=$qry->fetch();
                $qry->closeCursor();
                if(!isset($manufacturer['id']))
                {
                    //create manufacturer
                    echo '<i class="fa fa-plus-circle text-success"></i> '.T_("Création du fabricant d'équipement").' '.$ocs_manufacturer.' pour '.$ocs_netbios.' <br />';
                    if($_GET['action']=='run')
                    {
                        $qry=$db->prepare("INSERT INTO `tassets_manufacturer` (`name`) VALUES (:name)");
                        $qry->execute(array('name' => $ocs_manufacturer));
                        $manufacturer_id=$db->lastInsertId();
                    }  else {$manufacturer_id=0;}

                    //update manufacturer on asset
                    if($_GET['action']=='run' && $manufacturer_id)
                    {
                        $qry=$db->prepare("UPDATE `tassets` SET `manufacturer`=:manufacturer WHERE `id`=:id");
                        $qry->execute(array('manufacturer' => $manufacturer_id,'id' => $gs_asset['id']));
                    }
                } else {
                    //update manufacture on asset
                    if($_GET['action']=='run' && $manufacturer['id'])
                    {
                        $qry=$db->prepare("UPDATE `tassets` SET `manufacturer`=:manufacturer WHERE `id`=:id");
                        $qry->execute(array('manufacturer' => $manufacturer['id'],'id' => $gs_asset['id']));
                    } 
                    $manufacturer_id=$manufacturer['id'];
                } 
            }

            //update model 
            if($ocs_model){
                if($ocs_ip) {$ip_asset='1';} else {$ip_asset='0';}
                $qry=$db->prepare("SELECT `id` FROM `tassets_model` WHERE name=:name");
                $qry->execute(array('name' => $ocs_model));
                $model=$qry->fetch();
                $qry->closeCursor();
                if(!isset($model['id']))
                {
                    //create model
                    echo '<i class="fa fa-plus-circle text-success"></i> '.T_("Création du modèle d'équipement").' '.$ocs_model.' pour '.$ocs_netbios.' <br />';
                    if($_GET['action']=='run')
                    {
                        $qry=$db->prepare("INSERT INTO `tassets_model` (`name`,`type`,`manufacturer`,`ip`) VALUES (:name,:type,:manufacturer,:ip)");
                        $qry->execute(array('name' => $ocs_model,'type' => $type_id,'manufacturer' => $manufacturer_id,'ip' => $ip_asset));
                        $model_id=$db->lastInsertId();
                    }

                    //update model on asset
                    if($_GET['action']=='run' && $model_id)
                    {
                        $qry=$db->prepare("UPDATE `tassets` SET `model`=:model WHERE `id`=:id");
                        $qry->execute(array('model' => $model_id,'id' => $gs_asset['id']));
                    }
                } else {
                    //update ocs_model on asset
                    if($_GET['action']=='run' && $model['id'])
                    {
                        $qry=$db->prepare("UPDATE `tassets` SET `model`=:model WHERE `id`=:id");
                        $qry->execute(array('model' => $model['id'],'id' => $gs_asset['id']));
                    }
                }
            }

            //update ip
            if($ocs_ip && $gs_asset['id']){
                $qry=$db->prepare("SELECT `id` FROM `tassets_iface` WHERE `asset_id`=:asset_id AND `ip`=:ip AND `disable`=0");
                $qry->execute(array('asset_id' => $gs_asset['id'],'ip' => $ocs_ip));
                $ip=$qry->fetch();
                $qry->closeCursor();
                if(!isset($ip['id']))
                {
                    //create iface with ip
                    if($_GET['action']=='run')
                    {
                        $qry=$db->prepare("INSERT INTO `tassets_iface` (`asset_id`,`ip`,`mac`,`role_id`,`netbios`) VALUES (:asset_id,:ip,:mac,1,:netbios)");
                        $qry->execute(array('asset_id' => $gs_asset['id'],'ip' => $ocs_ip,'mac' => $ocs_mac,'netbios' => $ocs_netbios));
                        $iface_id=$db->lastInsertId();
                    }
                }
            }
        }
        echo "<hr />";
    }
    echo T_("Nombre d'équipements dans OCS").' : '.$cnt;
   }
} else {
   echo DisplayMessage('error',T_("Le connecteur OCS est désactivé"));
}
?>