<?php
################################################################################
# @Name : system.php
# @Description :  admin system
# @Call : admin.php
# @Parameters : 
# @Author : Flox
# @Create : 12/01/2011
# @Update : 22/11/2022
# @Version : 3.2.31
################################################################################ 
?>
<div class="page-header position-relative">
	<h1 class="page-title text-primary-m2">
		<i class="fa fa-desktop text-primary-m"><!----></i>  <?php echo T_('Système'); ?>
	</h1>
	<div class="page-tools">
		<button class="btn btn-xs btn-info" onclick="copyToClipBoard()">
			<i class="fa fa-clipboard"><!----></i>
			<?php echo T_('Copier dans le presse papier'); ?>
		</button>
		<a href="index.php?page=admin&subpage=system&action=download_configuration" >
			<button class="btn btn-xs btn-purple" onclick="copyToClipBoard()">
				<i class="fa fa-download"><!----></i>
				<?php echo T_('Télécharger la configuration'); ?>
			</button>
		</a>
	</div>
</div>
<?php
	//download configuration
	if($rright['admin'] && $_GET['action']=='download_configuration')
	{
		//generate date
		$date = date("Y_m_d_H_i_s");
		//generate token
		$token = bin2hex(random_bytes(32));
		//dump SQL
		$file='./backup/'.$date.'_configuration_gestsup_'.$rparameters['version'].'_'.$token.'.sql';
		include_once("./vendor/ifsnop/mysqldump-php/src/Ifsnop/Mysqldump/Mysqldump.php");
		if(!isset($port)) {
			//get current port
			$qry=$db->prepare("SHOW VARIABLES WHERE Variable_name = 'port'");
			$qry->execute();
			$variable=$qry->fetch();
			$qry->closeCursor();
			$port=$variable[1];
		}
		$dumpSettings = array('include-tables' => array('tparameters','trights','tlogs'));
		$dump = new Ifsnop\Mysqldump\Mysqldump("mysql:host=$host;port=$port;dbname=$db_name;charset=$charset","$user", "$password",$dumpSettings);
		$dump->start($file);

		//download redirect
		$url="./index.php?page=admin&subpage=system&action=download_configuration&download_file=$file";
		$url=preg_replace('/%/','%25',$url);
		$url=preg_replace('/%2525/','%25',$url);
		echo "
		<SCRIPT LANGUAGE='JavaScript'>
			function redirect(){window.location='$url'}
			setTimeout('redirect()',$rparameters[time_display_msg]);
		</SCRIPT>
		";
	}

	include('./system.php'); 
?>
<div class="text-center mt-4">
	<button onclick='window.open("./admin/phpinfos.php?key=<?php echo $rparameters['server_private_key']; ?>")' class="btn btn-primary">
		<i class="fa fa-cogs"><!----></i>
		 <?php echo T_('Tous les paramètres PHP'); ?>
	</button>
</div>

<!-- tickets scripts  -->
<script type="text/javascript" src="js/system.js"></script>