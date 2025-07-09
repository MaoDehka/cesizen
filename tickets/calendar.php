<?php
################################################################################
# @Name : calendar.php
# @Description : display calendar
# @Call : /menu.php
# @Parameters : 
# @Author : Flox
# @Create : 19/02/2018
# @Update : 10/04/2024
# @Version : 3.2.49
################################################################################

//secure direct access
if(!isset($_GET['page'])) {echo 'ERROR : invalid access'; exit;}

//default value
if(!$_POST['technician']) {$_POST['technician']=$_SESSION['user_id'];}

//select technician selection
if($_POST['technician']!='%')
{
	//select name of technician
	$qry=$db->prepare("SELECT `firstname`,`lastname` FROM `tusers` WHERE id=:id");
	$qry->execute(array('id' => $_POST['technician']));
	$row=$qry->fetch();
	$qry->closeCursor();
	$displaytech=T_('de').'  '.$row['firstname'].' '.$row['lastname'];
} else {
   $displaytech=T_('de tous les techniciens');
}

//include plugin
$section='calendar_start';
include('plugin.php');

//generate access token for ajax
$token = bin2hex(random_bytes(32));
$qry=$db->prepare("INSERT INTO `ttoken` (`date`,`token`,`action`,`user_id`,`ip`) VALUES (NOW(),:token,'calendar_access',:user_id,:ip)");
$qry->execute(array('token' => $token,'user_id' => $_SESSION['user_id'],'ip' => $_SERVER['REMOTE_ADDR']));

?>
<div class="page-header">
	<h1 class="page-title text-primary-m2">
		<i class="fa fa-calendar text-primary-m2"><!----></i> 
		<?php echo T_('Calendrier'); ?>
		<small class="page-info text-secondary-d2">
			<i class="fa fa-angle-double-right"><!----></i>
			&nbsp;<?php echo $displaytech; ?>
		</small>
	</h1>
</div>
<div class="row pr-4 pl-4">
	<div class="col-xs-12">
		<div class="row">
			<div class="card bcard shadow" id="card-1">
				<div class="card-header">
					<form id="technician" name="technician" method="post" action="" >
						<h5 class="card-title">
							<?php echo T_('Technicien'); ?> :
							<select style="width:auto;" class="form-control form-control-sm d-inline-block" name="technician" onchange="submit()">
								<?php
								$qry=$db->prepare("SELECT `id`,`firstname`,`lastname` FROM `tusers` WHERE (`profile`=0 OR `profile`=4) AND `disable`=0 ORDER BY `lastname`" );
								$qry->execute();
								while($row=$qry->fetch()) {
									if ($row['id'] == $_POST['technician']) 
									{ 
										echo '<option value="'.$row['id'].'" selected>'.$row['firstname'].' '.$row['lastname'].'</option>'; 
									} else {
										echo '<option value="'.$row['id'].'" >'.$row['firstname'].' '.$row['lastname'].'</option>'; 
									}
								}
								$qry->closeCursor();
								if ($_POST['technician']=='%') {echo '<option value="%" selected >'.T_('Tous').'</option>'; } else {echo '<option value="%">'.T_('Tous').'</option>'; }
								?>
							</select> 
							<!-- START plugins part --> 
							<?php
							$section='calendar_title';
							include('plugin.php');
							?>
							<!-- START plugins part --> 
						</h5>
					</form>
				</div><!-- /.card-header -->
				<div class="card-body p-0">
					<!-- to have smooth .card toggling, it should have zero padding -->
					<div class="p-3">
						<div  id="calendar"></div>
					</div>
				</div><!-- /.card-body -->
			</div>
		</div>
	</div>
</div>

<!-- Fullcalendar 6 scripts  -->
<script src='vendor/components/fullcalendar/dist/index.global.min.js'></script>
<script src='vendor/components/fullcalendar/packages/core/locales-all.global.min.js'></script>

<script src='./vendor/moment/moment/min/moment.min.js'></script>
<script src="./vendor/components/bootbox/dist/bootbox.all.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var calendarEl = document.getElementById('calendar');
  var calendar = new FullCalendar.Calendar(calendarEl, {
	<?php
	if($ruser['language']=='fr_FR') {echo "locale:'fr',";}
	if($ruser['language']=='de_DE') {echo "locale:'de',";}
	if($ruser['language']=='es_ES') {echo "locale:'es',";}
	if($ruser['language']=='it_IT') {echo "locale:'it',";}
	?>
    timeZone: 'UTC',
    initialView: 'timeGridWeek',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    editable: true,
	droppable: false,
	selectable: true,
    firstDay: 1,
	weekNumbers: 'true',
	displayEventEnd : 'true',
	aspectRatio: 1.35,
	slotMinTime: '07:00:00',
	slotMaxTime: '21:00:00',
	height: 'auto',
	timeZone: 'local',
	
	events:
	<?php
		//list events backgroundColor
		$json = array();
		
		//colorize background with technician color
		if($rright['planning_tech_color'])
		{
			$qry=$db->prepare("SELECT `tevents`.`id`, `tevents`.`title`, `tevents`.`date_start` AS start, `tevents`.`date_end` AS end, `tevents`.`allday` as allDay, `tevents`.`classname` AS className, `tusers`.`planning_color` AS backgroundColor, 'white' AS textColor, `tevents`.`incident` AS borderColor FROM `tevents`,`tusers` WHERE `tevents`.`technician`=`tusers`.`id` AND `tevents`.`technician` LIKE :technician AND (`tevents`.`type`=1 OR `tevents`.`disable`=0) ORDER BY `tevents`.`id`");
			$qry->execute(array('technician' => $_POST['technician']));
		} else {
			$qry=$db->prepare("SELECT `id`, `title`, `date_start` AS start, `date_end` AS end, `allday` as allDay, `classname` AS className, 'white' AS textColor, `incident` AS borderColor FROM `tevents` WHERE `technician` LIKE :technician AND (`type`=1 OR `disable`=0) ORDER BY `id`");
			$qry->execute(array('technician' => $_POST['technician']));
		}

		$calendar=json_encode($qry->fetchAll(PDO::FETCH_ASSOC)); 
		$calendar=str_replace('"false"', 'false',$calendar);
		$calendar=str_replace('"true"', 'true',$calendar);
		if($calendar!='[]') {$calendar=str_replace("]",",{daysOfWeek: [0,6],rendering: 'background',color: '#d9d9d9',overLap: false,allDay: true}]",$calendar);} //colorize WeekEnd

		//include plugin
		$section='calendar_event';
		include('plugin.php');

		if(!preg_match('/<script/',strtolower($calendar))){echo $calendar;} else {echo 'ERROR : invalid content'; exit;}
	?>
	,
	eventContent: function(arg) {
		let divEl = document.createElement('div')
		let htmlTitle = arg.event._def.extendedProps['html'];

		if(arg.event.borderColor!=0)
		{
			if (arg.event.extendedProps.isHTML) {
				divEl.innerHTML = '<a style="color:#FFF;" title="<?php echo T_('Ouvre le ticket associé'); ?>" href="index.php?page=ticket&id='+arg.event.borderColor+'">'+htmlTitle+'</a>'
			} else {
				divEl.innerHTML = '<a style="color:#FFF;" title="<?php echo T_('Ouvre le ticket associé'); ?>" href="index.php?page=ticket&id='+arg.event.borderColor+'">'+arg.event.title+'</a>'
			}
		} else {divEl.innerHTML = arg.event.title}
		
		let arrayOfDomNodes = [ divEl ]
		return { domNodes: arrayOfDomNodes }
	}
	,
	eventResize: function(info) {
		start=moment(info.event.start).format('YYYY/MM/DD HH:mm:ss');
		end=moment(info.event.end).format('YYYY/MM/DD HH:mm:ss');
		$.ajax({
			url: 'ajax/calendar.php',
			data: 'action=resize_event&token=<?php echo $token; ?>&title='+info.event.title+'&start='+start+'&end='+end+'&id='+info.event.id +'&technician='+ <?php echo $_SESSION['user_id']; ?>,
			type: "POST",
			success: function(json) {
				//alert("json resize");
			}
		});
	}
	,
	eventDrop: function(info, delta) {
		start=moment(info.event.start).format('YYYY/MM/DD HH:mm:ss');
		end=moment(info.event.end).format('YYYY/MM/DD HH:mm:ss');
		allDay=info.event.allDay;
		$.ajax({
			url: 'ajax/calendar.php',
			data: 'action=move_event&token=<?php echo $token; ?>&title='+info.event.title+'&start='+start+'&end='+end+'&allday='+allDay+'&id='+info.event.id+'&technician='+ <?php echo $_SESSION['user_id']; ?> ,
			type: "POST",
			success: function(json) {
				//alert("event move : id="+info.event.id+" start"+start+"start="+end+"allday="+allDay);
			}
		});
	}
	,
	<?php
		//allow to add event on click on calendar
		if($rright['planning_direct_event'])
		{
			echo '
				select: function(info) {
					start=info.startStr;
					end=info.endStr;
					allDay=info.allDay;
					bootbox.prompt({ 
						title: "'.T_("Nouvel événement :").'", 
						';
							if($ruser['language']=='fr_FR') {echo "locale:'fr',";}
							if($ruser['language']=='de_DE') {echo "locale:'de',";}
							if($ruser['language']=='es_ES') {echo "locale:'es',";}
							if($ruser['language']=='it_IT') {echo "locale:'it',";}
						echo '
						callback: function (title) {
							if (title !== null) {
								start=moment(start).format("YYYY/MM/DD HH:mm:ss");
								end=moment(end).format("YYYY/MM/DD HH:mm:ss");
								$.ajax({
									url: "ajax/calendar.php",
									data: "action=add_event&title="+title+"&start="+start+"&end="+end+"&allday="+allDay+"&technician='; if($_POST['technician']=='%') {echo $_POST['technician']=$_SESSION['user_id'];} else {echo $_POST['technician'];}; echo '&token='.$token.'",
									type: "POST",
									success: function(result) {
										var data = JSON.parse(result);
										//alert("event create");
										//render event
										calendar.addEvent({
											id: data.event_id,
											title: title,
											start: info.startStr,
											end: info.endStr,
											allDay: info.allDay
										});
									}
								});
							}
						}
					});
				}
				,
			';
		}
	?>
	eventClick: function(info,calEvent) {
		//get ticket id 
		ticket=info.event.borderColor
		var title = info.event.title.replace(/"/g, '&quot;');
		//display a modal
		var modal = 
		'<div class="modal fade">\
		  <div class="modal-dialog">\
		   <div class="modal-content">\
			 <div class="modal-body">\
			   <button type="button" class="close" data-dismiss="modal" style="margin-top:-10px;">&times;</button>\
			   <div class="pt-3"></div>\
			   <form class="no-margin">\
				  <label><?php echo T_('Changer le nom :'); ?> &nbsp;</label>\
				  <input style="width:auto;" class="form-control form-control-sm d-inline-block" autocomplete="off" type="text" value="' + title + '" />\
				 &nbsp;&nbsp;<button type="submit" class="btn btn-sm btn-success"><i class="fa fa-check text-120"><!----></i></button>\
			   </form>\
			 </div>\
			 <div class="modal-footer">';
				if(ticket!=0) {var modal = modal+'<button type="button" class="btn btn-sm btn-info" data-action="openlink"><i class="fa fa-ticket"><!----></i> <?php echo T_("Ouvrir le ticket"); ?></button>'}
				var modal = modal + '<button type="button" class="btn btn-sm btn-danger" data-action="delete"><i class="fa fa-trash pr-1"><!----></i> <?php echo T_("Supprimer"); ?></button>\
				<button type="button" class="btn btn-sm btn-primary" data-dismiss="modal"><i class="fa fa-times pr-1"><!----></i> <?php echo T_("Annuler"); ?></button>\
			 </div>\
		  </div>\
		 </div>\
		</div>';
	
		var modal = $(modal).appendTo('body');
		modal.find('form').on('submit', function(ev){
			ev.preventDefault();
			newtitle = $(this).find("input[type=text]").val();
			//calendar.updateEvent', info.event);
			modal.modal("hide");
			$.ajax({
				url: 'ajax/calendar.php',
				data: 'action=update_title&title='+newtitle+'&id='+ info.event.id+'<?php echo '&token='.$token; ?>' ,
				type: "POST",
				success: function(json) {
					//alert("event updated"+info.event.id);
					info.event.setProp('title', newtitle);
				}
			});
		});

		modal.find('button[data-action=delete]').on('click', function() {
			var decision = confirm("<?php echo T_('Voulez-vous supprimer cet événement ?'); ?>"); 
			if (decision) {
				$.ajax({
					type: "POST",
					url: 'ajax/calendar.php',
					data: 'action=delete_event&token=<?php echo $token; ?>&id='+info.event.id ,
					type: "POST",
					success: function(json) {
						//alert("event deleted"+calEvent.id);
						info.event.remove();
					}
				});
				modal.modal("hide");
			} 
		});
		
		modal.modal('show').on('hidden', function(){
			modal.remove();
		});
		
		modal.find('button[data-action=openlink]').on('click', function() {
		window.open("./index.php?page=ticket&id="+ticket)
		});
	}
  });
  calendar.render();
});
</script>
<?php 
if($rparameters['debug']) {echo $calendar;}



?>