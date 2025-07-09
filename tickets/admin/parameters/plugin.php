<?php
################################################################################
# @Name : /admin/plugin.php
# @Description : list / update / install / uninstall, available plugins
# @Call : /admin/parameters.php
# @Parameters : 
# @Author : Flox
# @Create : 12/01/2021
# @Update : 06/05/2024
# @Version : 3.2.50
################################################################################

//default variable
if(empty($_GET['subtab'])) {$_GET['subtab']='store';}

if(!$rright['admin']) {echo DisplayMessage('error',T_("Vous n'avez pas les droits nécessaire, contactez votre administrateur")); exit;}

//enable or disable plugin
if($_POST['submit_plugin'] && $rright['admin'])
{
    $plugins = array_diff(scandir('plugins'), array('..', '.'));
    foreach ($plugins as $plugin_scan)
    {
        $qry=$db->prepare("UPDATE `tplugins` SET `enable`=:enable WHERE `name`=:name");
        $qry->execute(array('enable' => isset($_POST[$plugin_scan]),'name' => $plugin_scan));
    }

    //redirect
     $www = './index.php?page=admin&subpage=parameters&tab=plugin&subtab='.$_GET['subtab'];
     echo '<script language="Javascript">
     <!--
     document.location.replace("'.$www.'");
     // -->
     </script>'; 
   
}

//uninstall plugin
if($_POST['uninstall_plugin'] && $rright['admin'])
{
    //check writeable file
    if(is_writable('./plugins/'.$_POST['uninstall_plugin'].'/_SQL/uninstall.sql')) 
    {   
        //delete SQL modifications
        $sql_file=file_get_contents('./plugins/'.$_POST['uninstall_plugin'].'/_SQL/uninstall.sql');
        $sql_file=explode(";", $sql_file);
        foreach ($sql_file as $value) {if($value!='') {$db->query($value);}} 

        //remove plugin directory
        function delete_recurse($path) {
            if (is_dir($path)) {
                $files = array_diff(scandir($path), array('.', '..'));
                foreach ($files as $file) {
                    delete_recurse("$path/$file");
                }
                chmod($path, 0777); // Change permissions before removing
                return rmdir($path);
            } elseif (is_file($path)) {
                chmod($path, 0777); // Change permissions before removing
                return unlink($path);
            } else {
                return false; // The path does not exist or is not accessible
            }
        }
        $dir='./plugins/'.$_POST['uninstall_plugin'].'';
	    delete_recurse($dir);
    } else {
        echo DisplayMessage('error',T_("Les droits d'écritures sont nécessaires sur le répertoire /plugins, pour supprimer ce plugin")); 
        exit;
    }
}

//install plugin from store
if(($_POST['install_store_plugin'] || $_POST['install_licenced_plugin']) && $rright['admin'])
{
    
    //list store plugins to get filename
    $url='https://gestsup.fr/available_plugins.php?server_private_key='.$rparameters['server_private_key'];
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
    $remote_plugin_list=json_decode(curl_exec($curl),true);
    if(curl_error($curl)) die(curl_error($curl));
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $plugin_remote_array = array();
    foreach($remote_plugin_list as $plugin){ array_push($plugin_remote_array, $plugin);}
    foreach($plugin_remote_array as $plugin)
    {
        if($plugin['code']==$_POST['install_store_plugin'] || $plugin['code']==$_POST['install_licenced_plugin'])
        {
            $plugin_filename=$plugin['code'].'_'.$plugin['version'].'.zip';
            $plugin_code=$plugin['code'];
            $plugin_licenced=$plugin['licenced'];
            break;
        }
    }

    if(isset($plugin_filename) && isset($plugin_code))
    {
        //download plugin
        if($plugin_licenced)
        {
            $url = "https://zensoft.fr/plugins/$plugin_filename";
        } else {
            $url = "https://gestsup.fr/downloads/plugins/$plugin_filename";
        }
        
        $file_local_url = __DIR__ ."/../../upload/$plugin_filename";
        $zipResource = fopen($file_local_url, "w");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER,true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($curl, CURLOPT_FILE, $zipResource);
        if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
        $download = curl_exec($curl);
        if(!$download) {echo "downlaod error :- ".curl_error($curl);}
        curl_close($curl);

        if(file_exists($file_local_url)) 
        {
            //extract data into temporary directory
            $zip = new ZipArchive;
            $res = $zip->open($file_local_url);
            if($res === TRUE) {
                $zip->extractTo(__DIR__ ."/../../plugins/");
                $zip->close();
            }
            //check extract
            if(file_exists(__DIR__ ."/../../plugins/$plugin_code/changelog.php"))
            {
                //remove download file
                unlink($file_local_url);

              	//redirect
                $www = "index.php?page=admin&subpage=parameters&tab=plugin&subtab=store";
                echo '<script language="Javascript">
                <!--
                document.location.replace("'.$www.'");
                // -->
                </script>';
               
            } else {
                echo DisplayMessage('error',T_("L'extraction du plugin à échoué"));
                //remove download file
                unlink($file_local_url);
            }
        }else{echo DisplayMessage('error',T_('Le téléchargement du plugin à échoué'));}
    }
}
?>

<!-- /////////////////////////////////////////////////////////////// plugins part /////////////////////////////////////////////////////////////// -->
<input type="hidden" name="tab" id="tab" value="plugin" />
<div id="plugin" class="tab-pane <?php if($_GET['tab']=='plugin') echo 'active'; ?>">
    <form id="plugin_form" name="plugin_form" enctype="multipart/form-data" method="POST" action="">
        <div class="table-responsive">
            <div class="tab-content" style="background-color:#FFF;">
                <div class="card bcard bgc-transparent shadow-none">
                    <div class="card-body tabs-left p-0">
                        <ul class="nav nav-tabs align-self-start" role="tablist">
                            
                            <li class="nav-item brc-purple shadow-sm">
                                <a class="nav-link text-left py-3 <?php if($_GET['subtab']=='store') {echo 'active';} ?>" href="index.php?page=admin&subpage=parameters&tab=plugin&subtab=store">
                                <i class="fa fa-store text-purple pr-1"><!----></i><b>Store</b>
                                </a>
                            </li>
                                        
                            <?php
                                //scan local plugins
                                $plugins = array_diff(scandir('plugins'), array('..', '.'));
                                foreach ($plugins as $plugin_scan)
                                {
                                    //check if plugin is installed
                                    $qry=$db->prepare("SELECT * FROM `tplugins` WHERE name=:name");
                                    $qry->execute(array('name' => $plugin_scan));
                                    $plugin=$qry->fetch();
                                    $qry->closeCursor();
                                    
                                    //display tab
                                    if(isset($plugin['id'])) 
                                    {
                                        echo '
                                            <li class="nav-item brc-purple shadow-sm">
                                                <a class="nav-link text-left py-3 '; if($_GET['subtab']==$plugin['name']) {echo 'active';} echo ' " href="index.php?page=admin&subpage=parameters&tab=plugin&subtab='.$plugin['name'].'">
                                                <i class="fa fa-'.$plugin['icon'].' text-purple pr-1"><!----></i>'.$plugin['label'].'
                                                </a>
                                            </li>
                                        ';
                                    }
                                }
                            ?>
                        </ul>

                        <div class="tab-content p-35 border-1 brc-grey-l1 shadow-sm bgc-white">
                            <?php
                                //store pan
                                echo '
                                    <div class="tab-pane fade text-95 '; if($_GET['subtab']=='store') {echo 'show active';} echo ' ">
                                        <div class="text-primary-d3 text-110 mb-2">
                                            <h2 class="page-title text-purple ml-3">
                                                <i class="fa fa-store"><!----></i> '.T_('Plugins disponibles').'
                                            </h2>
                                        </div>
                                        ';
                                        //check gestsup.fr connexion
                                        if(!CheckConnection('gestsup.fr',443))
                                        {
                                            echo DisplayMessage('error',T_('Accès à gestsup.fr:433 impossible'));
                                            exit;
                                        }

                                        if(!is_writable('./plugins/index.htm')){
                                            echo DisplayMessage('warning',T_("Droits d'écriture verrouillé sur le répertoire plugins, téléchargement et installation impossible"));
                                        }

                                        //scan local plugins to install or update
                                        $plugins = array_diff(scandir('plugins'), array('..', '.'));
                                        foreach ($plugins as $plugin_scan)
                                        {
                                            //check if plugin is installed
                                            $qry=$db->prepare("SELECT * FROM `tplugins` WHERE name=:name");
                                            $qry->execute(array('name' => $plugin_scan));
                                            $plugin=$qry->fetch();
                                            $qry->closeCursor();
                                            
                                            if(empty($plugin['id'])) //install plugin
                                            {
                                                //sql insert
                                                if(file_exists('./plugins/'.$plugin_scan.'/_SQL/install.sql'))
                                                {
                                                    $sql_file_install=file_get_contents('./plugins/'.$plugin_scan.'/_SQL/install.sql');
                                                    $sql_file_install=explode(";", $sql_file_install);
                                                    foreach ($sql_file_install as $query) {
                                                        if($query!='') {$db->query($query);}
                                                    } 
                                                    echo DisplayMessage('success',T_('Installation du plugin').' '.$plugin_scan.' '.T_('réalisée avec succès'));
                                                }
                                            } else {  //existing plugin
                                                //update plugin db
                                                $sql_files = array_diff(scandir('plugins/'.$plugin['name'].'/_SQL'), array('..', '.','install.sql','uninstall.sql'));
                                                foreach ($sql_files as $sql_file)
                                                {
                                                    $update_version=explode('_',$sql_file);
                                                    $update_version=explode('.sql',$update_version[1]);
                                                    $update_version=$update_version[0];
                                                    if($update_version>$plugin['version'])
                                                    {
                                                        $sql_file_install=file_get_contents('plugins/'.$plugin['name'].'/_SQL/'.$sql_file);
                                                        $sql_file_install=explode(";", $sql_file_install);
                                                        foreach ($sql_file_install as $query) {
                                                            if($query!='') {$db->query($query);}
                                                        } 
                                                        echo DisplayMessage('success',T_('Version').' '.$update_version.' '.T_('du plugin').' '.mb_strtolower($plugin['label']).' '.T_('installé avec succès').' ('.$sql_file.')');
                                                    }
                                                }
                                            }
                                        }

                                        //get plugins from store 
                                        $url='https://gestsup.fr/available_plugins.php?server_private_key='.$rparameters['server_private_key'];
                                        $curl = curl_init($url);
                                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                                        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                                        if($rparameters['server_proxy_url']) {curl_setopt($curl, CURLOPT_PROXY, $rparameters['server_proxy_url']);}
                                        $remote_plugin_list=json_decode(curl_exec($curl),true);
                                        if(curl_error($curl)) die(curl_error($curl));
                                        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                                        curl_close($curl);

                                        //put plugins in array
                                        $plugin_remote_array = array();
                                        foreach($remote_plugin_list as $plugin){
                                            if($plugin['private'] && $plugin['licenced']==0)
                                            {
                                                //hide private plugin
                                            } else {
                                                array_push($plugin_remote_array, $plugin);
                                            }
                                        }

                                        //for each store plugin
                                        foreach($plugin_remote_array as $plugin)
                                        {
                                            //check if plugin already installed
                                            $qry=$db->prepare("SELECT `id`,`version` FROM `tplugins` WHERE `name`=:name");
                                            $qry->execute(array('name' => $plugin['code']));
                                            $plugin_install=$qry->fetch();
                                            $qry->closeCursor();
                                            if(isset($plugin_install['id'])) {$plugin_installed=1;} else {$plugin_installed=0;}
                                            echo '
                                                <div class="shadow-sm mt-3 bgc-purple-l4 pos-rel overflow-hidden radius-2 pt-25 pb-1 px-4 border-1 brc-purple-l2">
                                                    <div class="bgc-primary-l4 opacity-5 position-tl h-100 w-100"></div>
                                                    ';
                                                    if($plugin_installed){
                                                        echo '
                                                            <span class="position-tr text-white bgc-success-d2 mr-2 radius-b-1 py-15 px-1">
                                                                <i title="'.T_('Installé').'" class="fa fa-check"></i>
                                                            </span>
                                                        ';
                                                    } else {
                                                        echo '
                                                            <span class="position-tr text-white bgc-warning-d2 mr-2 radius-b-1 py-15 px-1">
                                                                <i title="'.T_('Disponible').'" class="fa fa-star"></i>
                                                            </span>
                                                        ';
                                                    }
                                                    echo '
                                                    <div class="pos-rel d-flex align-items-center">
                                                        <h1>
                                                            <i class="fa text-purple fa-'.$plugin['icon'].'"></i>
                                                        </h1>
                                                        <div class="pl-3">
                                                            <h6 class="text-120 text-600 text-dark-tp3 mb-1">'.$plugin['name'].'
                                                            
                                                            ';
                                                            if(is_writable('./plugins/index.htm'))
                                                            {
                                                                //check compatibility version
                                                                $gestsup_version=explode('.',$rparameters['version']);
                                                                $plugin_version=explode('.',$plugin['compatibility']);

                                                                if($plugin['free']==0)
                                                                {
                                                                    //check licence
                                                                    if($plugin['licenced'])
                                                                    {
                                                                        if(!$plugin_installed)
                                                                        {
                                                                            echo '
                                                                                <button  title="'.T_('Installe le plugin depuis le store').'" name="install_licenced_plugin" value="'.$plugin['code'].'" type="submit" class="ml-2 btn btn-xs btn-success">
                                                                                    <i class="fa fa-download"><!----></i>
                                                                                    '.T_('Installer').'
                                                                                </button>
                                                                            ';
                                                                            if($plugin['private'])
                                                                            {
                                                                                echo ' <i title="'.T_('Plugin privé, visible uniquement pour votre serveur GestSup').'" class="fa fa-eye-slash text-danger"><!----></i>';
                                                                            }
                                                                        } else {
                                                                            if($plugin_install['version']!=$plugin['version'])
                                                                            {
                                                                                echo '
                                                                                    <button  title="'.T_('Mise à jour du plugin depuis le store').'" name="install_licenced_plugin" value="'.$plugin['code'].'" type="submit" class="ml-2 btn btn-xs btn-success">
                                                                                        <i class="fa fa-cloud-upload-alt"><!----></i>
                                                                                        '.T_('Mise à jour').'
                                                                                    </button>
                                                                                ';
                                                                            } else {
                                                                                echo '
                                                                                <button   title="'.T_('Supprime tous les fichiers et les champs en base de données du plugin').'" name="uninstall_plugin" value="'.$plugin['code'].'" type="submit" class="ml-2 btn btn-xs btn-danger">
                                                                                    <i class="fa fa-trash"><!----></i>
                                                                                    '.T_('Désinstaller').'
                                                                                </button>
                                                                                ';
                                                                                
                                                                            }
                                                                        }
                                                                    } else {
                                                                        echo '
                                                                            <button title="'.T_('Ouvre le site gestsup.fr').'" onclick="window.open(\'https://www.gestsup.fr/index.php?page=plugins\',\'_blank\')" class="ml-2 btn btn-xs btn-success">
                                                                                <i class="fa fa-file"><!----></i>
                                                                                '.T_('Devis').'
                                                                            </button>
                                                                        ';
                                                                    }

                                                                } elseif($gestsup_version[0]>=$plugin_version[0] && $gestsup_version[1]>=$plugin_version[1] && $gestsup_version[2]>=$plugin_version[2])
                                                                {   
                                                                    if(!$plugin_installed)
                                                                    {
                                                                        echo '
                                                                            <button  title="'.T_('Installe le plugin depuis le store').'" name="install_store_plugin" value="'.$plugin['code'].'" type="submit" class="ml-2 btn btn-xs btn-success">
                                                                                <i class="fa fa-download"><!----></i>
                                                                                '.T_('Installer').'
                                                                            </button>
                                                                        ';
                                                                    } else {
                                                                        if($plugin_install['version']!=$plugin['version'])
                                                                        {
                                                                            echo '
                                                                                <button  title="'.T_('Mise à jour du plugin depuis le store').'" name="install_store_plugin" value="'.$plugin['code'].'" type="submit" class="ml-2 btn btn-xs btn-success">
                                                                                    <i class="fa fa-cloud-upload-alt"><!----></i>
                                                                                    '.T_('Mise à jour').'
                                                                                </button>
                                                                            ';
                                                                        } else {
                                                                            
                                                                                echo '
                                                                                <button   title="'.T_('Supprime tous les fichiers et les champs en base de données du plugin').'" name="uninstall_plugin" value="'.$plugin['code'].'" type="submit" class="ml-2 btn btn-xs btn-danger">
                                                                                    <i class="fa fa-trash"><!----></i>
                                                                                    '.T_('Désinstaller').'
                                                                                </button>
                                                                                ';
                                                                        }
                                                                    }
                                                                } else {
                                                                    echo '
                                                                    <button  title="'.T_('Mettez à jour votre application pour installer ce plugin').'" name="" value="'.$plugin['code'].'" type="submit" class="ml-2 btn btn-xs btn-danger">
                                                                        <i class="fa fa-download"><!----></i>
                                                                        '.T_('Version incompatible').'
                                                                    </button>
                                                                    ';
                                                                } 
                                                            }
                                                            echo '

                                                            </h6>
                                                            <div class="text-95 text-dark-tp3">v'.$plugin['version'].' </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3 pos-rel">
                                                        '.$plugin['description'].'
                                                    </div>
                                                    <div class="mt-3 pos-rel">
                                                        <span class="badge radius-round bgc-purple-l1 text-dark-tp3 text-85 px-25 py-1 m-2px overflow-hidden">
                                                            <span class="pos-rel">'.T_('Auteur').' : '.$plugin['author'].'</span>
                                                        </span>
                                                        <span class="badge radius-round bgc-purple-l1 text-dark-tp3 text-85 px-25 py-1 m-2px overflow-hidden">
                                                            <span class="pos-rel">'.T_('Compatibilité').' GestSup '.$plugin['compatibility'].'</span>
                                                        </span>
                                                        <span class="badge radius-round bgc-purple-l1 text-dark-tp3 text-85 px-25 py-1 m-2px overflow-hidden">
                                                            <span class="pos-rel">'; if($plugin['free']) {echo T_('Gratuit');} else {echo T_('Payant');} echo '</span>
                                                        </span>
                                                        <span class="badge radius-round bgc-purple-l1 text-dark-tp3 text-85 px-25 py-1 m-2px overflow-hidden">
                                                            <span class="pos-rel"><a target="about_blank" href="'.$plugin['url'].'">'.T_('Informations').'</a> </span>
                                                        </span>
                                                        <span class="badge radius-round bgc-purple-l1 text-dark-tp3 text-85 px-25 py-1 m-2px overflow-hidden">
                                                            <span class="pos-rel"><a target="about_blank" href="https://gestsup.fr/img/plugins/'.$plugin['code'].'.webp">'.T_('Image').'</a> </span>
                                                        </span>
                                                       
                                                    </div>
                                                </div>
                                            ';
                                        }
                                        echo '
                                   </div>
                                ';

                                //plugin pans scan available plugins
                                $plugins = array_diff(scandir('plugins'), array('..', '.'));
                                foreach ($plugins as $plugin_scan)
                                {
                                    //check if plugin is installed
                                    $qry=$db->prepare("SELECT * FROM `tplugins` WHERE name=:name");
                                    $qry->execute(array('name' => $plugin_scan));
                                    $plugin=$qry->fetch();
                                    $qry->closeCursor();

                                    if(!empty($plugin['id'])) //install plugin
                                    {
                                    echo '
                                        <div class="tab-pane fade text-95 '; if($_GET['subtab']==$plugin['name']) {echo 'show active';} echo ' ">
                                            <div class="text-primary-d3 text-110 mb-2">
                                                <input type="checkbox" name="'.$plugin['name'].'" '; if ($plugin['enable']) {echo "checked";} echo ' value="1" />
                                                <input type="hidden" name="plugin" value="'.$plugin['name'].'" />
                                                <span class="lbl">'.T_('Activer le plugin').' '.mb_strtolower($plugin['label']).' v'.$plugin['version'].'</span>
                                                <i title="'.T_($plugin['description']).'" class="fa fa-question-circle text-primary-m2"><!----></i>
                                                <button title="'.T_('Supprime tous les fichiers et les champs en base de données du plugin').'" name="uninstall_plugin" value="'.$plugin['name'].'" type="submit" class="ml-2 btn btn-xs btn-danger">
                                                    <i class="fa fa-trash"><!----></i>
                                                    '.T_('Désinstaller').'
                                                </button>
                                                ';
                                                //if plugin is enabled add specific parameters
                                                if($plugin['enable'])
                                                {
                                                    //display parameters
                                                    if(file_exists('plugins/'.$plugin['name'].'/admin/parameters.php'))
                                                    {
                                                        echo '<div class="mt-2"></div>';
                                                        include('plugins/'.$plugin['name'].'/admin/parameters.php');
                                                    }
                                                }
                                                echo '
                                            </div>
                                        </div>
                                    ';
                                    }
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="border-t-1 brc-secondary-l1 bgc-secondary-l4 py-3 text-center">
            <button name="submit_plugin" id="submit_plugin" value="submit_plugin" type="submit" class="btn btn-success">
                <i class="fa fa-check"><!----></i>
                <?php echo T_('Valider'); ?>
            </button>
        </div>
    </form>
</div>