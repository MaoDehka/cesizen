<?php
################################################################################
# @Name : ./admin/log.php
# @Description : display security log
# @Call : /admin.php
# @Parameters : 
# @Author : Flox
# @Create : 24/04/2020
# @Update : 31/01/2022
# @Version : 3.2.20
################################################################################

//init var
if(!$_GET['cursor']) {$_GET['cursor']=0;}
if(!is_numeric($_GET['cursor'])) {$_GET['cursor']=0;}

//get type
$qry=$db->prepare("SELECT `type` FROM `tlogs` ORDER BY `type`");
$qry->execute();
$log=$qry->fetch();
$qry->closeCursor();
//head
echo '
	<div class="page-header position-relative">
		<h1 class="page-title text-primary-m2">
			<i class="fa fa-clipboard-list text-primary-m2"><!----></i> 
			'.T_('Logs').'
		</h1>
	</div>
';

if(empty($log[0])) //case no log
{
	echo DisplayMessage('info',T_("Aucune information n'est disponible dans les logs."));
}
elseif($rright['admin'])
{
	//init var
	if(!$_GET['log']) $_GET['log']=$log['type']; 
	//clear log
	if($_GET['clear'] && $_GET['log'])
	{
		$qry=$db->prepare("DELETE FROM `tlogs` WHERE type=:type");
		$qry->execute(array('type' => $_GET['log']));
	}
	echo '
	<div class="tabs-above shadow">
		<ul class="nav nav-tabs nav-justified" role="tablist">
			';

			//counter
			$qry=$db->prepare("SELECT COUNT(`id`) FROM `tlogs` WHERE `type`=:type AND `message` LIKE :message");
			$qry->execute(array('type' => $_GET['log'],'message' => "%$logkeywords%"));
			$counter=$qry->fetch();
			$qry->closeCursor();
			$counter=$counter[0];

			//display header
			$qry=$db->prepare("SELECT DISTINCT(`type`) FROM `tlogs` ORDER BY `type`");
			$qry->execute();
			while($log=$qry->fetch()) 
			{
				echo '
				<li class="nav-item mr-1px" >
					<a href="./index.php?page=admin&amp;subpage=log&amp;log='.$log['type'].'" class="nav-link '; if($_GET['log']==$log['type']) {echo 'active';} echo '">
						';
						//add icon
						switch ($log['type']) {
							case 'security':
								echo '<i class="fa fa-shield-alt text-primary"><!----></i> ';
								break;
							case 'user_validation':
								echo '<i class="fa fa-check text-primary"><!----></i> ';
								break;
							case 'error':
								echo '<i class="fa fa-exclamation-triangle text-primary"><!----></i> ';
								break;
							case 'admin':
								echo '<i class="fa fa-cog text-primary"><!----></i> ';
								break;
							case 'ticket':
								echo '<i class="fa fa-ticket text-primary"><!----></i> ';
								break;
							default:
								echo '<i class="fa fa-clipboard-list text-primary"><!----></i> ';
						}
						echo ucfirst($log['type']);
						if($_GET['log']==$log['type']) {echo '<span class="text-sm"> ('.$counter.')</span>';}
						echo '
					</a>
				</li>
				';
			}
			$qry->closeCursor();

			echo '
		</ul>
		<div class="tab-content" style="background-color:#FFF;">
			<div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
				<p>
					<button onclick=\'window.location.href="./index.php?page=admin&amp;subpage=log&amp;log='.$_GET['log'].'&amp;clear=1";\' class="btn btn-danger">
						<i class="fa fa-trash"><!----></i> '.T_('Effacer ce log').'
					</button>
				</p>
			</div>
			<div class="mt-4 mt-lg-0 card bcard h-auto overflow-hidden shadow border-0">
				<div class="card-body p-0 table-responsive-xl">
					<table id="sample-table-1" class="table text-dark-m1 brc-black-tp10 mb-1">
						<thead>
							<tr class="bgc-white text-secondary-d3 text-95">
								<th>'.T_('Date').'</th>
								<th>'.T_('Message').'</th>
								<th>'.T_('Utilisateur').'</th>
								<th>'.T_('IP').'</th>
							</tr>
						</thead>
						<tbody>
							';
						
							$qry=$db->prepare("SELECT * FROM `tlogs` WHERE `type`=:type AND `message` LIKE :message ORDER BY `date` DESC LIMIT :cursor,:maxline");
							$qry->bindValue(':type', $_GET['log']);
							$qry->bindValue(':message', "%$logkeywords%");
							$qry->bindValue(':cursor',  (int) trim($_GET['cursor']), PDO::PARAM_INT);
							$qry->bindValue(':maxline',  (int) trim($rparameters['maxline']), PDO::PARAM_INT);
							$qry->execute();
							
							while($log=$qry->fetch()) 
							{
								echo '<tr class="bgc-h-orange-l4">';
								//get user informations
								$qry2=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
								$qry2->execute(array('id' => $log['user']));
								$user=$qry2->fetch();
								$qry2->closeCursor();
								if(empty($user['firstname'])) {$user['firstname']='';}
								if(empty($user['lastname'])) {$user['lastname']='';}
								
								echo '<td>'.$log['date'].'</td>';
								echo '<td>'.T_($log['message']).'</td>';
								echo '<td>'.$user['firstname'].' '.$user['lastname'].'</td>';
								echo '<td>'.$log['ip'].'</td>';
								echo '</tr>';
							}
							$qry->closeCursor();
							echo '
						</tbody>
					</table>
				</div>
				
			</div>
		</div>
	</div>
	';
	if($counter>$rparameters['maxline'])
	{
		//count number of page
		$total_page=ceil($counter/$rparameters['maxline']);
		echo '
		<div class="row justify-content-center mt-4">
			<nav aria-label="Page navigation">
				<ul class="pagination nav-tabs-scroll is-scrollable mb-0">';
					//display previous button if it's not the first page
					if($_GET['cursor']!=0)
					{
						$cursor=$_GET['cursor']-$rparameters['maxline'];
						echo '<li class="page-item"><a class="page-link" title="'.T_('Page précédente').'" href="./index.php?page=admin&amp;subpage=log&amp;log='.$_GET['log'].'&amp;cursor='.$cursor.'"><i class="fa fa-arrow-left"><!----></i></a></li>';
					}
					//display first page
					if($_GET['cursor']==0){$active='active';} else {$active='';}
					echo '<li class="page-item '.$active.'"><a class="page-link" title="'.T_('Première page').'" href="./index.php?page=admin&amp;subpage=log&amp;log='.$_GET['log'].'&amp;cursor=0">&nbsp;1&nbsp;</a></li>';
					//calculate current page
					$current_page=($_GET['cursor']/$rparameters['maxline'])+1;
					//calculate min and max page 
					if(($current_page-3)<3) {$min_page=2;} else {$min_page=$current_page-3;}
					if(($total_page-$current_page)>3) {$max_page=$current_page+4;} else {$max_page=$total_page;}
					//display all pages links
					for ($page = $min_page; $page <= $total_page; $page++) {
						//display start "..." page link
						if(($page==$min_page) && ($current_page>5)){echo '<li class="page-item"><a class="page-link" title="'.T_('Pages masqués').'" href="index.php?page=admin&amp;subpage=log&amp;log='.$_GET['log'].'">&nbsp;...&nbsp;</a></li>';}
						//init cursor
						if($page==1) {$cursor=0;}
						$selectcursor=$rparameters['maxline']*($page-1);
						if($_GET['cursor']==$selectcursor){$active='active';} else	{$active='';}
						$cursor=(-1+$page)*$rparameters['maxline'];
						//display page link
						if($page!=$max_page) echo '<li class="page-item '.$active.'"><a class="page-link" title="'.T_('Page').' '.$page.'" href="./index.php?page=admin&amp;subpage=log&amp;log='.$_GET['log'].'&amp;cursor='.$cursor.'">&nbsp;'.$page.'&nbsp;</a></li>';
						//display end "..." page link
						if(($page==($max_page-1)) && ($page!=$total_page-1)) {
							echo '<li class="page-item"><a class="page-link" title="'.T_('Pages masqués').'" href="index.php?page=admin&amp;subpage=log&amp;log='.$_GET['log'].'&amp;userid='.$_GET['userid'].'&amp;state='.$_GET['state'].'">&nbsp;...&nbsp;</a></li>';
						}
						//cut if there are more than 3 pages
						if($page==($current_page+4)) {
							$page=$total_page;
						} 
					}
					//display last page
					$cursor=($total_page-1)*$rparameters['maxline'];
					if($_GET['cursor']==$selectcursor){$active='active';} else	{$active='';}
					echo '<li class="page-item '.$active.'"><a class="page-link" title="'.T_('Dernière page').'" href="./index.php?page=admin&amp;subpage=log&amp;log='.$_GET['log'].'&amp;cursor='.$cursor.'">&nbsp;'.$total_page.'&nbsp;</a></li>';
					//display next button if it's not the last page
					if($_GET['cursor']<($counter-$rparameters['maxline']))
					{
						$cursor=$_GET['cursor']+$rparameters['maxline'];
						echo '<li class="page-item"><a class="page-link" title="'.T_('Page suivante').'" href="./index.php?page=admin&amp;subpage=log&amp;log='.$_GET['log'].'&amp;cursor='.$cursor.'"><i class="fa fa-arrow-right"><!----></i></a></li>';
					}
					echo '
				</ul>
			</nav>
		</div>
		';
		if($rparameters['debug']){echo "<br /><b><u>DEBUG MODE</u></b><br />&nbsp;&nbsp;&nbsp;&nbsp;[Multi-page links] _GET[cursor]=$_GET[cursor] | current_page=$current_page | total_page=$total_page | min_page=$min_page | max_page=$max_page";}
	}

} else {
	echo DisplayMessage('error',T_("Vous n'avez pas accès à cette section, contactez votre administrateur"));
}