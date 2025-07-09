<?php
################################################################################
# @Name : backup.php
# @Description : save and restore page
# @Call : /admin/admin.php
# @Parameters : 
# @Author : Flox
# @Create : 25/04/2013
# @Update : 27/11/2023
# @Version : 3.2.45
################################################################################

//initialize variables 
if(!isset($action)) $action = '';
if(!isset($command)) $command = '';
if(!isset($date)) $date = '';
if(!isset($_FILES['restore']['name'])) $_FILES['restore']['name'] = '';
if(!isset($_FILES['logo']['name'])) $_FILES['logo']['name'] = '';
if(!isset($_POST['upload'])) $_POST['upload'] = '';

//generate token
$token = bin2hex(random_bytes(32));

//export backup
if($_GET['action']=='backup' && $rright['admin'] && $rright['admin_backup'])
{
	//generate date
	$date = date("Y_m_d_H_i_s");
	//generate token
	$token = bin2hex(random_bytes(32));
	//dump SQL
	$file='./_SQL/'.$date.'-backup-gestsup-'.$rparameters['version'].'-'.$token.'.sql';
	include_once("./vendor/ifsnop/mysqldump-php/src/Ifsnop/Mysqldump/Mysqldump.php");
	if(!isset($port)) {
		//get current port
		$qry=$db->prepare("SHOW VARIABLES WHERE Variable_name = 'port'");
		$qry->execute();
		$variable=$qry->fetch();
		$qry->closeCursor();
		$detected_port=$variable[1];
		
		$dump = new Ifsnop\Mysqldump\Mysqldump("mysql:host=$host;port=$detected_port;dbname=$db_name;charset=$charset","$user", "$password");
	} else {
		$dump = new Ifsnop\Mysqldump\Mysqldump("mysql:host=$host;port=$port;dbname=$db_name;charset=$charset","$user", "$password");
	}
	$dump->start($file);
	
	//backup files
	function Zip($source, $destination)
	{
		if(!extension_loaded('zip') || !file_exists($source)) {
			return false;
		}

		$zip = new ZipArchive();
		if(!$zip->open($destination, ZIPARCHIVE::CREATE)) {
			return false;
		}

		$source = str_replace('\\', '/', realpath($source));

		if(is_dir($source) === true)
		{
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

			foreach ($files as $file)
			{
			    if(strpos($file,'backup_gestsup') == false) {
    				$file = str_replace('\\', '/', $file);
    
    				// Ignore "." and ".." folders
    				if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
    					continue;
    
    				$file = realpath($file);
                    
    				if(is_dir($file) === true)
    				{
    					$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
    				}
    				else if(is_file($file) === true)
    				{
    					$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
    				}
			    }
			}
		}
		else if(is_file($source) === true)
		{
			$zip->addFromString(basename($source), file_get_contents($source));
		}

		return $zip->close();
	}
	Zip('./', './backup/'.$date.'_backup_gestsup_'.$rparameters['version'].'_'.$token.'.zip');	
	
	//check SQL dump
	$check_sql_dump=0;
	$pattern='./_SQL/'.date('Y').'_'.date('m').'_'.date('d').'_*-backup-gestsup-'.$rparameters['version'].'-'.$token.'.sql';
	foreach (glob($pattern) as $filename) {$check_sql_dump=1;}
	
	//check backup
	if(file_exists('./backup/'.$date.'_backup_gestsup_'.$rparameters['version'].'_'.$token.'.zip') && $check_sql_dump==1) 
	{
		echo DisplayMessage('success',T_("La sauvegarde à été réalisée avec succès, aucune copie n'est conservé sur le serveur"));
		$step=5;
	} else {
		if(!file_exists('./backup/'.$date.'_backup_gestsup_'.$rparameters['version'].'_'.$token.'.zip'))
		{
			echo DisplayMessage('error',T_("La sauvegarde de l'application à échouée (Aucun fichier zip détecté dans ./backup)"));
		} elseif($check_sql_dump==0) {
			echo DisplayMessage('error',T_("La sauvegarde de la base de données de l'application à échouée (Aucun fichier SQL détecté dans ./_SQL)"));
		}
		$error=1;
	}
	
	//redirect
	$www = './index.php?page=admin&subpage=backup&download_backup='.$date.'_backup_gestsup_'.$rparameters['version'].'_'.$token.'.zip';
	echo '<script language="Javascript">
		<!--
		document.location.replace("'.$www.'");
		// -->
		</script>';
}

//clean local backup
if($_GET['action']!='backup' && !$_GET['download_backup'])
{
	$backup_zip_file  = glob('backup/*backup_gestsup*.{zip}', GLOB_BRACE);
	$backup_sql_file  = glob('_SQL/*backup-gestsup*.{sql}', GLOB_BRACE);
	//test if backup files exist
	if($backup_zip_file || $backup_sql_file)
	{
		//check if folder is writeable delete files
		if(is_writable('backup') && is_writable('_SQL'))
		{
			foreach($backup_zip_file as $file){unlink($file);}
			foreach($backup_sql_file as $file){unlink($file);}
		} else {
			echo DisplayMessage('error',T_('Les repertoires "backup" et "_SQL" ne sont pas accèssible en écriture et contiennent des sauvegardes locales à supprimer. Réaliser la suppression manuellement'));
		}
	}
}


?>
<div class="page-header position-relative">
	<h1 class="page-title text-primary-m2">
		<i class="fa fa-save text-primary-m2"><!----></i>  <?php echo T_('Sauvegardes de GestSup'); ?>
	</h1>
</div>
<?php
	echo DisplayMessage('info',T_("Il est recommandé d'automatiser la sauvegarde depuis le serveur, vous trouverez plus d'informations dans la ").'<a target="_blank" href="https://doc.gestsup.fr/backup/">'.T_('documentation').'</a>');
	echo DisplayMessage('info',T_("Les fichiers de sauvegardes ne sont pas conservés localement sur le serveur"));
?>
<br />
<div class="text-center">
	<button title="<?php echo T_("Télécharge une archive contenant les fichiers et la base de données de l'application"); ?>"  onclick='window.location.href="./index.php?page=admin&amp;subpage=backup&amp;action=backup"' type="submit" class="btn btn-primary">
		<i class="fa fa-download"><!----></i>
		<?php echo T_('Lancer la sauvegarde'); ?>
	</button>
</div>