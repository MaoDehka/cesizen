<?php 
################################################################################
# @Name : ticket_mandatory.php
# @Description : modify input color for mandatory field
# @call : ticket.php
# @parameters : 
# @Author : Flox
# @Create : 12/02/2020
# @Update : 17/01/2024
# @Version : 3.2.47
################################################################################

//init and secure var
require_once(__DIR__.'/../core/init_post.php');
require_once(__DIR__.'/../core/init_get.php');

//prevent direct access
if(!isset($_SESSION['user_id'])) {header('HTTP/1.0 403 Forbidden'); exit;}

echo '
<script type="text/javascript">
	function CheckMandatory(){
';
		if($rright['ticket_service_mandatory'])
		{
			echo '
				var u_service = document.getElementById("u_service");
				if(u_service != null)
				{
					var service=document.getElementById("u_service").value;
					if(service){document.getElementById("u_service").style.borderColor = "#73bd73";} else {document.getElementById("u_service").style.borderColor = "#dd6a57";}
				} 
			';
		}
		if($rright['ticket_type_mandatory'])
		{
			echo '
				var type = document.getElementById("type");
				if(type != null)
				{
					var type=document.getElementById("type").value;
					if(type!=0){document.getElementById("type").style.borderColor = "#73bd73";} else {document.getElementById("type").style.borderColor = "#dd6a57";}
				}
			';
		}
		if($rright['ticket_user_mandatory'])
		{
			echo '
				var user = document.getElementById("user");
				if(user != null)
				{
					var user = document.getElementById("user");
					var user_selected = user.options[user.selectedIndex].value;
					if(user_selected!=""){
						stylesheet = document.styleSheets[0]
						stylesheet.removeRule(".selectize-input {border-color: #dd6a57 !important;}", 0);
						stylesheet.insertRule(".selectize-input {border-color: #73bd73 !important;}", 0);
					} else {
						stylesheet = document.styleSheets[0]
						stylesheet.removeRule(".selectize-input {border-color: #73bd73 !important;}", 0);
						stylesheet.insertRule(".selectize-input {border-color: #dd6a57 !important;}", 0);
					}
				}
			';
		}
		if($rright['ticket_tech_mandatory'])
		{
			echo '
				var technician = document.getElementById("technician");
				if(technician != null)
				{
					var technician=document.getElementById("technician").value;
					if(technician!=0){document.getElementById("technician").style.borderColor = "#73bd73";} else {document.getElementById("technician").style.borderColor = "#dd6a57";}
				}
			';
		}
		if($rright['ticket_asset_mandatory'])
		{
			echo '
				var asset_id = document.getElementById("asset_id");
				if(asset_id != null)
				{
					var asset_id=document.getElementById("asset_id").value;
					if(asset_id!=0){document.getElementById("asset_id").style.borderColor = "#73bd73";} else {document.getElementById("asset_id").style.borderColor = "#dd6a57";}
				}
			';
		}
		if($rright['ticket_cat_mandatory'])
		{
			echo '
			var category = document.getElementById("category");
			if(category != null)
			{
				var category=document.getElementById("category").value;
				if(category!=0){document.getElementById("category").style.borderColor = "#73bd73";} else {document.getElementById("category").style.borderColor = "#dd6a57";}
				var subcat=document.getElementById("subcat").value;
				if(subcat!=0){document.getElementById("subcat").style.borderColor = "#73bd73";} else {document.getElementById("subcat").style.borderColor = "#dd6a57";}
			}
			';
		}
		if($rright['ticket_agency_mandatory'])
		{
			echo '
				var u_agency = document.getElementById("u_agency");
				if(u_agency != null)
				{
					var u_agency=document.getElementById("u_agency").value;
					if(u_agency!=0){document.getElementById("u_agency").style.borderColor = "#73bd73";} else {document.getElementById("u_agency").style.borderColor = "#dd6a57";}
				}
			';
		}
		if($rright['ticket_title_mandatory'])
		{
			echo '
				var title = document.getElementById("title");
				if(title != null)
				{
					var title=document.getElementById("title").value;
					if(title!=0){document.getElementById("title").style.borderColor = "#73bd73";} else {document.getElementById("title").style.borderColor = "#dd6a57";}
				}
			';
		}
		if($rright['ticket_description_mandatory'])
		{
			echo '
			var editor=document.getElementById("editor");
			if(editor != null)
			{
				var editor=editor.innerHTML;
				if(editor!=0){document.getElementById("description").style.borderColor = "#73bd73";} else {document.getElementById("description").style.borderColor = "#dd6a57";}
			}
			';
		}
		if($rright['ticket_date_hope_mandatory'])
		{
			echo '
				var date_hope = document.getElementById("date_hope");
				if(date_hope != null)
				{
					var date_hope=document.getElementById("date_hope").value;
					if(date_hope){document.getElementById("date_hope").style.borderColor = "#73bd73";} else {document.getElementById("date_hope").style.borderColor = "#dd6a57";}
				}
			';
		}
		if($rright['ticket_criticality_mandatory'])
		{
			echo '
			var criticality = document.getElementById("criticality");
			if(criticality != null)
			{
				var criticality=document.getElementById("criticality").value;
				if(criticality){document.getElementById("criticality").style.borderColor = "#73bd73";} else {document.getElementById("criticality").style.borderColor = "#dd6a57";}
			}
			';
		}
		if($rright['ticket_priority_mandatory'])
		{
			echo '
			var priority = document.getElementById("priority");
			if(priority != null)
			{
				var priority=document.getElementById("priority").value;
				if(priority){document.getElementById("priority").style.borderColor = "#73bd73";} else {document.getElementById("priority").style.borderColor = "#dd6a57";}
			}
			';
		}
		if($rright['ticket_place_mandatory'])
		{
			echo '
				var ticket_places = document.getElementById("ticket_places");
				if(ticket_places != null)
				{
					var ticket_places=document.getElementById("ticket_places").value;
					if(ticket_places!=0){document.getElementById("ticket_places").style.borderColor = "#73bd73";} else {document.getElementById("ticket_places").style.borderColor = "#dd6a57";}
				}
			';
		}
echo '
	}
	window.onload = CheckMandatory;
</script>
';


?>