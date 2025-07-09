<?php
################################################################################
# @Name : contract.php
# @Description : display current company contract when company user or ticket limit is enable
# @Call : /index.php
# @Parameters : 
# @Author : Flox
# @Create : 16/09/2022
# @Update : 16/09/2022
# @Version : 3.2.27
################################################################################

if(!$rright['contract']) {echo DisplayMessage('error',T_("Vous n'avez pas les droits d'acceder aux contrats")); exit;}

if($rparameters['company_limit_hour'])
{
    //contract counter
    if($_GET['state']=='current')
    {
        $qry=$db->prepare("SELECT COUNT(`id`) 
        FROM `tcompany` 
        WHERE 
        `disable`=0 AND 
        `limit_hour_date_start`!='0000-00-00' AND
        DATE_ADD(`limit_hour_date_start`, INTERVAL limit_hour_days DAY)>NOW() 
        ");
    } else {
        $qry=$db->prepare("SELECT COUNT(`id`) 
        FROM `tcompany` 
        WHERE 
        `disable`=0 AND 
        `limit_hour_date_start`!='0000-00-00' AND
        DATE_ADD(`limit_hour_date_start`, INTERVAL limit_hour_days DAY)<NOW() 
        ");
    }

    $qry->execute();
    $counter=$qry->fetch();
    $qry->closeCursor();

    echo '
        <div class="page-header position-relative">
            <h1 class="page-title text-primary-m2">
                <i class="fa fa-file-contract text-primary-m2"><!----></i> 
                ';
                if($_GET['state']=='current') 
                {
                    echo  T_('Liste des contrats par heures en cours');
                } else {
                    echo  T_('Liste des contrats par heures passés');
                }
                echo '
                <small class="page-info text-secondary-d2">
                    <i class="fa fa-angle-double-right text-80"><!----></i>
                    &nbsp;'.T_('Nombre').' : '.$counter[0].'
                </small>
            </h1>
        </div>
        <div class="mt-4 mt-lg-0 card bcard h-auto overflow-hidden shadow border-0">
            <div class="card-body p-0 table-responsive-xl">
                <table id="sample-table-1" class="table table-bordered table-bordered table-striped table-hover text-dark-m2 ">
                    <thead>
                        <tr>
                            <th><i class="fa fa-building"><!----></i> '.T_('Société').'</th>
                            <th><i class="fa fa-calendar"><!----></i> '.T_('Date de début de période').'</th>
                            <th><i class="fa fa-calendar"><!----></i> '.T_('Durée').'</th>
                            <th><i class="fa fa-calendar"><!----></i> '.T_('Jours restants').'</th>
                            <th><i class="fa fa-clock"><!----></i> '.T_('Heures restantes').'</th>
                            <th><i class="fa fa-ticket"><!----></i> '.T_('Tickets').'</th>
                        </tr>
                    </thead>
                    <tbody>
                        ';
                        if($_GET['state']=='current')
                        {
                            $qry=$db->prepare("SELECT `id`, `name`,`limit_hour_number`,`limit_hour_days`,`limit_hour_date_start`, DATEDIFF(DATE_ADD(`limit_hour_date_start`, INTERVAL limit_hour_days DAY),NOW()) AS remain_day
                            FROM `tcompany` 
                            WHERE 
                            `disable`=0 AND 
                            `limit_hour_date_start`!='0000-00-00' AND
                            DATE_ADD(`limit_hour_date_start`, INTERVAL limit_hour_days DAY)>NOW() 
                            ORDER BY `limit_hour_date_start`");
                        } else {
                            $qry=$db->prepare("SELECT `id`, `name`,`limit_hour_number`,`limit_hour_days`,`limit_hour_date_start`, DATEDIFF(DATE_ADD(`limit_hour_date_start`, INTERVAL limit_hour_days DAY),NOW()) AS remain_day 
                            FROM `tcompany` 
                            WHERE 
                            `disable`=0 AND 
                            `limit_hour_date_start`!='0000-00-00' AND
                            DATE_ADD(`limit_hour_date_start`, INTERVAL limit_hour_days DAY)<NOW() 
                            ORDER BY `limit_hour_date_start`");
                        }
                       
                        $qry->execute();
                        while($company=$qry->fetch()) 
                        {
                            //calc hour remaining
                            $date_start=$company['limit_hour_date_start'];
                            $date_start_conv = date_create($company['limit_hour_date_start']);
                            date_add($date_start_conv, date_interval_create_from_date_string("$company[limit_hour_days] days"));
                            $date_end=date_format($date_start_conv, 'Y-m-d');

                            $qry2=$db->prepare("SELECT SUM(tincidents.time)/60 FROM `tincidents`,`tusers` WHERE tusers.id=tincidents.user AND tusers.company=:company AND date_create BETWEEN :date_start AND :date_end AND tincidents.disable='0'");
                            $qry2->execute(array('company' => $company['id'],'date_start' => $date_start,'date_end' => $date_end));
                            $nbhourused=$qry2->fetch();
                            $qry2->closeCursor();

                            $nbhourremaining=$company['limit_hour_number']-$nbhourused[0];

                            //define color
                            if($nbhourremaining<0) {$nbhourremaining_color='danger';}
                            if($nbhourremaining>0 && $nbhourremaining<=1) {$nbhourremaining_color='warning';}
                            if($nbhourremaining>1) {$nbhourremaining_color='success';}
                            
                            if($company['remain_day']<0) {$remain_day_color='danger';}
                            if($company['remain_day']>0 && $nbhourremaining<=30) {$remain_day_color='warning';}
                            if($company['remain_day']>30) {$remain_day_color='success';}


                            echo '
                            <tr class="bgc-h-default-l3 d-style"  >
                                <td onclick="document.location = \'index.php?page=admin&subpage=list&table=tcompany&action=disp_edit&id='.$company['id'].'\';" ><b>'.mb_strtoupper($company['name']).'</b></td>
                                <td onclick="document.location = \'index.php?page=admin&subpage=list&table=tcompany&action=disp_edit&id='.$company['id'].'\';">'.DateToDisplay($company['limit_hour_date_start']).'</td>
                                <td onclick="document.location = \'index.php?page=admin&subpage=list&table=tcompany&action=disp_edit&id='.$company['id'].'\';">'.$company['limit_hour_days'].'j</td>
                                <td onclick="document.location = \'index.php?page=admin&subpage=list&table=tcompany&action=disp_edit&id='.$company['id'].'\';">
                                    <span class="badge text-75 border-l-3 brc-black-tp8 bgc-'.$remain_day_color.' text-white">
                                        '.$company['remain_day'].'j
                                    </span>
                                </td>
                                <td onclick="document.location = \'index.php?page=admin&subpage=list&table=tcompany&action=disp_edit&id='.$company['id'].'\';">
                                    <span class="badge text-75 border-l-3 brc-black-tp8 bgc-'.$nbhourremaining_color.' text-white">
                                        '.round($nbhourremaining,1).'h / '.$company['limit_hour_number'].'h
                                    </span>
                                </td>
                                <td>
                                    ';
                                    $qry2=$db->prepare("SELECT `tincidents`.`id`,`tincidents`.`time` FROM `tincidents`,`tusers` WHERE `tusers`.`id`=`tincidents`.`user` AND `tusers`.`company`=:company AND `date_create` BETWEEN :date_start AND :date_end AND `tincidents`.`disable`='0' ORDER BY `time` DESC");
                                    $qry2->execute(array('company' => $company['id'],'date_start' => $date_start,'date_end' => $date_end));
                                    while($ticket=$qry2->fetch()) 
                                    {
                                        echo '
                                        <a title="'.T_('Ouvrir le ticket').'" target="_blank" href="index.php?page=ticket&id='.$ticket['id'].'">
                                            <i class="fa fa-ticket"></i> n°'.$ticket['id'].' ('.MinToHour($ticket['time']).')
                                        </a>
                                        <br />';
                                    }
                                    $qry2->closeCursor();
                                  echo '
                                </td>
                            </tr>
                            ';
                        }
                        $qry->closeCursor();
                        echo '
                    </tbody>
                </table>
            </div>	
        </div>
    ';
}

//////////////////////////////////////////////////////////////// limit by ticket
if($rparameters['company_limit_hour'] && $rparameters['company_limit_ticket']) { echo '<hr>';}

if($rparameters['company_limit_ticket'])
{
    //contract counter
    if($_GET['state']=='current')
    {
        $qry=$db->prepare("SELECT COUNT(`id`) 
        FROM `tcompany` 
        WHERE 
        `disable`=0 AND 
        `limit_ticket_date_start`!='0000-00-00' AND
        DATE_ADD(`limit_ticket_date_start`, INTERVAL limit_ticket_days DAY)>NOW() 
        ");
    } else {
        $qry=$db->prepare("SELECT COUNT(`id`) 
        FROM `tcompany` 
        WHERE 
        `disable`=0 AND 
        `limit_ticket_date_start`!='0000-00-00' AND
        DATE_ADD(`limit_ticket_date_start`, INTERVAL limit_ticket_days DAY)<NOW() 
        ");
    }

    $qry->execute();
    $counter=$qry->fetch();
    $qry->closeCursor();

    echo '
        <div class="page-header position-relative">
            <h1 class="page-title text-primary-m2">
                <i class="fa fa-file-contract text-primary-m2"><!----></i> 
                ';
                if($_GET['state']=='current') 
                {
                    echo  T_('Liste des contrats par tickets en cours');
                } else {
                    echo  T_('Liste des contrats par tickets passés');
                }
                echo '
                <small class="page-info text-secondary-d2">
                    <i class="fa fa-angle-double-right text-80"><!----></i>
                    &nbsp;'.T_('Nombre').' : '.$counter[0].'
                </small>
            </h1>
        </div>
        <div class="mt-4 mt-lg-0 card bcard h-auto overflow-hidden shadow border-0">
            <div class="card-body p-0 table-responsive-xl">
                <table id="sample-table-1" class="table table-bordered table-bordered table-striped table-hover text-dark-m2 ">
                    <thead>
                        <tr>
                            <th><i class="fa fa-building"><!----></i> '.T_('Société').'</th>
                            <th><i class="fa fa-calendar"><!----></i> '.T_('Date de début de période').'</th>
                            <th><i class="fa fa-calendar"><!----></i> '.T_('Durée').'</th>
                            <th><i class="fa fa-calendar"><!----></i> '.T_('Jours restants').'</th>
                            <th><i class="fa fa-clock"><!----></i> '.T_('Tickets restants').'</th>
                            <th><i class="fa fa-ticket"><!----></i> '.T_('Tickets').'</th>
                        </tr>
                    </thead>
                    <tbody>
                        ';
                        if($_GET['state']=='current')
                        {
                            $qry=$db->prepare("SELECT `id`, `name`,`limit_ticket_number`,`limit_ticket_days`,`limit_ticket_date_start`, DATEDIFF(DATE_ADD(`limit_ticket_date_start`, INTERVAL limit_ticket_days DAY),NOW()) AS remain_day
                            FROM `tcompany` 
                            WHERE 
                            `disable`=0 AND 
                            `limit_ticket_date_start`!='0000-00-00' AND
                            DATE_ADD(`limit_ticket_date_start`, INTERVAL limit_ticket_days DAY)>NOW() 
                            ORDER BY `limit_ticket_date_start`");
                        } else {
                            $qry=$db->prepare("SELECT `id`, `name`,`limit_ticket_number`,`limit_ticket_days`,`limit_ticket_date_start`, DATEDIFF(DATE_ADD(`limit_ticket_date_start`, INTERVAL limit_ticket_days DAY),NOW()) AS remain_day 
                            FROM `tcompany` 
                            WHERE 
                            `disable`=0 AND 
                            `limit_ticket_date_start`!='0000-00-00' AND
                            DATE_ADD(`limit_ticket_date_start`, INTERVAL limit_ticket_days DAY)<NOW() 
                            ORDER BY `limit_ticket_date_start`");
                        }
                       
                        $qry->execute();
                        while($company=$qry->fetch()) 
                        {
                            //calc hour remaining
                            $date_start=$company['limit_ticket_date_start'];
                            $date_start_conv = date_create($company['limit_ticket_date_start']);
                            date_add($date_start_conv, date_interval_create_from_date_string("$company[limit_ticket_days] days"));
                            $date_end=date_format($date_start_conv, 'Y-m-d');

                            $qry2=$db->prepare("SELECT COUNT(tincidents.id) FROM `tincidents`,`tusers` WHERE tusers.id=tincidents.user AND tusers.company=:company AND date_create BETWEEN :date_start AND :date_end AND tincidents.disable='0'");
                            $qry2->execute(array('company' => $company['id'],'date_start' => $date_start,'date_end' => $date_end));
                            $nb_ticket_used=$qry2->fetch();
                            $qry2->closeCursor();

                            $nb_ticket_remaining=$company['limit_ticket_number']-$nb_ticket_used[0];

                            //define color
                            if($nb_ticket_remaining<0) {$nb_ticket_remaining_color='danger';}
                            if($nb_ticket_remaining>0 && $nb_ticket_remaining<=2) {$nb_ticket_remaining_color='warning';}
                            if($nb_ticket_remaining>2) {$nb_ticket_remaining_color='success';}
                            
                            if($company['remain_day']<0) {$remain_day_color='danger';}
                            if($company['remain_day']>0 && $nb_ticket_remaining<=30) {$remain_day_color='warning';}
                            if($company['remain_day']>30) {$remain_day_color='success';}


                            echo '
                            <tr class="bgc-h-default-l3 d-style"  >
                                <td onclick="document.location = \'index.php?page=admin&subpage=list&table=tcompany&action=disp_edit&id='.$company['id'].'\';" ><b>'.mb_strtoupper($company['name']).'</b></td>
                                <td onclick="document.location = \'index.php?page=admin&subpage=list&table=tcompany&action=disp_edit&id='.$company['id'].'\';">'.DateToDisplay($company['limit_ticket_date_start']).'</td>
                                <td onclick="document.location = \'index.php?page=admin&subpage=list&table=tcompany&action=disp_edit&id='.$company['id'].'\';">'.$company['limit_ticket_days'].'j</td>
                                <td onclick="document.location = \'index.php?page=admin&subpage=list&table=tcompany&action=disp_edit&id='.$company['id'].'\';">
                                    <span class="badge text-75 border-l-3 brc-black-tp8 bgc-'.$remain_day_color.' text-white">
                                        '.$company['remain_day'].'j
                                    </span>
                                </td>
                                <td onclick="document.location = \'index.php?page=admin&subpage=list&table=tcompany&action=disp_edit&id='.$company['id'].'\';">
                                    <span class="badge text-75 border-l-3 brc-black-tp8 bgc-'.$nb_ticket_remaining_color.' text-white">
                                        '.round($nb_ticket_remaining,1).' / '.$company['limit_ticket_number'].'
                                    </span>
                                </td>
                                <td>
                                    ';
                                    $qry2=$db->prepare("SELECT `tincidents`.`id`,`tincidents`.`time` FROM `tincidents`,`tusers` WHERE `tusers`.`id`=`tincidents`.`user` AND `tusers`.`company`=:company AND `date_create` BETWEEN :date_start AND :date_end AND `tincidents`.`disable`='0' ORDER BY `time` DESC");
                                    $qry2->execute(array('company' => $company['id'],'date_start' => $date_start,'date_end' => $date_end));
                                    while($ticket=$qry2->fetch()) 
                                    {
                                        echo '
                                        <a title="'.T_('Ouvrir le ticket').'" target="_blank" href="index.php?page=ticket&id='.$ticket['id'].'">
                                            <i class="fa fa-ticket"></i> n°'.$ticket['id'].'
                                        </a>
                                        <br />';
                                    }
                                    $qry2->closeCursor();
                                  echo '
                                </td>
                            </tr>
                            ';
                        }
                        $qry->closeCursor();
                        echo '
                    </tbody>
                </table>
            </div>	
        </div>
    ';
}

?>