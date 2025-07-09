//################################################################################
// @Name : js/admin_parameters.js
// @Description : script to parameters page
// @call : ./admin/parameters.php
// @parameters : 
// @Author : Flox
// @Create : 06/11/2020
// @Update : 08/12/2023
// @Version : 3.2.45
//################################################################################

//get tab 
var tab = document.getElementById("tab").value;

//CTRL+S to save parameters 
$(document).keydown(function(e) {
    var key = undefined;
    var possible = [ e.key, e.keyIdentifier, e.keyCode, e.which ];
    while (key === undefined && possible.length > 0)
    {
        key = possible.pop();
    }
    if (key && (key == '115' || key == '83' ) && (e.ctrlKey || e.metaKey) && !(e.altKey))
    {
        e.preventDefault();
         $('#general_form #submit_general').click();
         $('#connector_form #submit_connector').click();
         $('#function_form #submit_function').click();
        return false;
    }
    return true;
}); 

//display sub-parameters on checkboxes
jQuery(function($) {
    //display smtp parameters
    var checkedValue = $("#mail").is(":checked");
    if(checkedValue == false) $("#smtp_parameters").addClass("d-none");
    $('#mail').change(function () {
        var checkedValue = $("#mail").is(":checked");
        if(checkedValue == false) {$("#smtp_parameters").addClass("d-none");} else {$("#smtp_parameters").removeClass("d-none");}
    });

    //display smtp credential fields on check smtp auth
    var checkedValue = $("#mail_auth").is(":checked");
    if(checkedValue == false) $("#smtp_credential").addClass("d-none"); 
    $('#mail_auth').change(function () {
        var checkedValue = $("#mail_auth").is(":checked");
        if(checkedValue == false) {$("#smtp_credential").addClass("d-none");} else {$("#smtp_credential").removeClass("d-none");}
    });

    //display ldap parameters
    var checkedValue = $("#ldap").is(":checked");
    if(checkedValue == false) $("#ldap_parameters").addClass("d-none");
    $('#ldap').change(function () {
        var checkedValue = $("#ldap").is(":checked");
        if(checkedValue == false) {$("#ldap_parameters").addClass("d-none");} else {$("#ldap_parameters").removeClass("d-none");}
    });

    //display azure_ad parameters
    var checkedValue = $("#azure_ad").is(":checked");
    if(checkedValue == false) $("#azure_ad_parameters").addClass("d-none");
    $('#azure_ad').change(function () {
        var checkedValue = $("#azure_ad").is(":checked");
        if(checkedValue == false) {$("#azure_ad_parameters").addClass("d-none");} else {$("#azure_ad_parameters").removeClass("d-none");}
    });

  
    //display ocs parameters
    var checkedValue = $("#ocs").is(":checked");
    if(checkedValue == false) $("#ocs_parameters").addClass("d-none");
    $('#ocs').change(function () {
        var checkedValue = $("#ocs").is(":checked");
        if(checkedValue == false) {$("#ocs_parameters").addClass("d-none");} else {$("#ocs_parameters").removeClass("d-none");}
    });

    //display api parameters
    var checkedValue = $("#api").is(":checked");
    if(checkedValue == false) $("#api_parameters").addClass("d-none");
    $('#api').change(function () {
        var checkedValue = $("#api").is(":checked");
        if(checkedValue == false) {$("#api_parameters").addClass("d-none");} else {$("#api_parameters").removeClass("d-none");}
    });

    //display imap parameters 
    var checkedValue = $("#imap").is(":checked");
    if(checkedValue == false) $("#imap_parameters").addClass("d-none");
    $('#imap').change(function () {
      var checkedValue = $("#imap").is(":checked");
     if(checkedValue == false) {$("#imap_parameters").addClass("d-none");} else {$("#imap_parameters").removeClass("d-none");}
    });

   
    //display login_message parameters 
    var checkedValue = $("#login_message").is(":checked");
    if(checkedValue == false) $("#login_message_parameters").addClass("d-none");
    $('#login_message').change(function () {
      var checkedValue = $("#login_message").is(":checked");
     if(checkedValue == false) {$("#login_message_parameters").addClass("d-none");} else {$("#login_message_parameters").removeClass("d-none");}
    });
    

    //display ticket_open_message parameters 
    var ticket_open_message = $("#ticket_open_message").is(":checked");
    if(ticket_open_message == false) $("#ticket_open_message_parameter").addClass("d-none");
    $('#ticket_open_message').change(function () {
      var ticket_open_message = $("#ticket_open_message").is(":checked");
     if(ticket_open_message == false) {$("#ticket_open_message_parameter").addClass("d-none");} else {$("#ticket_open_message_parameter").removeClass("d-none");}
    });

    //display ticket_autoclose  
    var checkedValue = $("#ticket_autoclose").is(":checked");
    if(checkedValue == false) $("#ticket_autoclose_parameters").addClass("d-none");
    $('#ticket_autoclose').change(function () {
      var checkedValue = $("#ticket_autoclose").is(":checked");
     if(checkedValue == false) {$("#ticket_autoclose_parameters").addClass("d-none");} else {$("#ticket_autoclose_parameters").removeClass("d-none");}
    });

    //display user_validation_parameters  
    var checkedValue = $("#user_validation").is(":checked");
    if(checkedValue == false) $("#user_validation_parameters").addClass("d-none");
    $('#user_validation').change(function () {
      var checkedValue = $("#user_validation").is(":checked");
     if(checkedValue == false) {$("#user_validation_parameters").addClass("d-none");} else {$("#user_validation_parameters").removeClass("d-none");}
    });

    //display user_disable_attempt_parameters  
    var checkedValue = $("#user_disable_attempt").is(":checked");
    if(checkedValue == false) $("#user_disable_attempt_parameters").addClass("d-none");
    $('#user_disable_attempt').change(function () {
      var checkedValue = $("#user_disable_attempt").is(":checked");
     if(checkedValue == false) {$("#user_disable_attempt_parameters").addClass("d-none");} else {$("#user_disable_attempt_parameters").removeClass("d-none");}
    });

    //display user_password_policy_parameters  
    var checkedValue = $("#user_password_policy").is(":checked");
    if(checkedValue == false) $("#user_password_policy_parameters").addClass("d-none");
    $('#user_password_policy').change(function () {
      var checkedValue = $("#user_password_policy").is(":checked");
     if(checkedValue == false) {$("#user_password_policy_parameters").addClass("d-none");} else {$("#user_password_policy_parameters").removeClass("d-none");}
    });

    //display mail_newticket_parameters  
    var checkedValue = $("#mail_newticket").is(":checked");
    if(checkedValue == false) $("#mail_newticket_parameters").addClass("d-none");
    $('#mail_newticket').change(function () {
      var checkedValue = $("#mail_newticket").is(":checked");
     if(checkedValue == false) {$("#mail_newticket_parameters").addClass("d-none");} else {$("#mail_newticket_parameters").removeClass("d-none");}
    });

    //display mail_auto_type_parameters  
    var ticket_type = $("#ticket_type").is(":checked");
    if(ticket_type == false) $("#mail_auto_type_parameters").addClass("d-none");
    $('#ticket_type').change(function () {
      var ticket_type = $("#ticket_type").is(":checked");
     if(ticket_type == false) {$("#mail_auto_type_parameters").addClass("d-none");} else {$("#mail_auto_type_parameters").removeClass("d-none");}
    });

    //display mail_link_parameters  
    var checkedValue = $("#mail_link").is(":checked");
    if(checkedValue == false) $("#mail_link_parameters").addClass("d-none");
    $('#mail_link').change(function () {
      var checkedValue = $("#mail_link").is(":checked");
     if(checkedValue == false) {$("#mail_link_parameters").addClass("d-none");} else {$("#mail_link_parameters").removeClass("d-none");}
    });

    //display asset_parameters  
    var checkedValue = $("#asset").is(":checked");
    if(checkedValue == false) $("#asset_parameters").addClass("d-none");
    $('#asset').change(function () {
      var checkedValue = $("#asset").is(":checked");
     if(checkedValue == false) {$("#asset_parameters").addClass("d-none");} else {$("#asset_parameters").removeClass("d-none");}
    });    


    if(tab == 'function')
    {
      //display planning_parameters  
      var checkedValue = $("#planning").is(":checked");
      if(checkedValue == false) $("#planning_parameters").addClass("d-none");
      $('#planning').change(function () {
        var checkedValue = $("#planning").is(":checked");
      if(checkedValue == false) {$("#planning_parameters").addClass("d-none");} else {$("#planning_parameters").removeClass("d-none");}

      }); 
      //display planning_ics_list_parameters  
      var checkedValue = document.getElementById('planning_ics1').checked
      console.log(checkedValue)
      if(checkedValue == false) $("#planning_ics_list_parameters").addClass("d-none");
      $('#planning_ics1').change(function () {
        var checkedValue = document.getElementById('planning_ics1').checked
      if(checkedValue == false) {$("#planning_ics_list_parameters").addClass("d-none");} else {$("#planning_ics_list_parameters").removeClass("d-none");}
      });  
      $('#planning_ics2').change(function () {
        var checkedValue = document.getElementById('planning_ics2').checked
      if(checkedValue == true) {$("#planning_ics_list_parameters").addClass("d-none");} else {$("#planning_ics_list_parameters").removeClass("d-none");}
      });
      //display survey_parameters  
      var checkedValue = $("#survey").is(":checked");
      if(checkedValue == false) $("#survey_parameters").addClass("d-none");
      $('#survey').change(function () {
        var checkedValue = $("#survey").is(":checked");
      if(checkedValue == false) {$("#survey_parameters").addClass("d-none");} else {$("#survey_parameters").removeClass("d-none");}
      });
    }
});

if(tab == 'connector')
{
  //display smtp oauth mail parameters
  var mail_auth_type = document.getElementById("mail_auth_type").value;
  if(mail_auth_type=='oauth_google')
  {
    $("#mail_oauth_parameters").removeClass("d-none");
    $("#mail_password_section").addClass("d-none");
    $("#oauth_microsoft_procedure").addClass("d-none");
    $("#mail_oauth_tenant_section").addClass("d-none");
    $("#oauth_google_procedure").removeClass("d-none");
  } else if (mail_auth_type=='oauth_microsoft' || mail_auth_type=='oauth_azure')
  {
    $("#mail_oauth_parameters").removeClass("d-none");
    $("#mail_password_section").addClass("d-none");
    $("#oauth_google_procedure").addClass("d-none");
    $("#oauth_microsoft_procedure").removeClass("d-none");
    $("#mail_oauth_tenant_section").removeClass("d-none");
  } else {
    $("#mail_oauth_parameters").addClass("d-none");
    $("#mail_password_section").removeClass("d-none");
  }



  //display imap oauth mail parameters
  var imap_auth_type = document.getElementById("imap_auth_type").value;
  if(imap_auth_type=='oauth_azure' || imap_auth_type=='oauth_google')
  {
    $("#imap_oauth_parameters").removeClass("d-none");
    $("#imap_oauth_azure_procedure").removeClass("d-none");
    $("#imap_password_section").addClass("d-none");
    if(imap_auth_type=='oauth_google') {
      $("#imap_oauth_tenant_id_section").addClass("d-none");
      $("#imap_oauth_azure_procedure").addClass("d-none");
    }
    if(imap_auth_type=='oauth_azure') {
      $("#imap_oauth_tenant_id_section").removeClass("d-none");
      $("#imap_oauth_azure_procedure").removeClass("d-none");
      $("#imap_oauth_google_procedure").addClass("d-none");
    }
  } else {
    $("#imap_oauth_parameters").addClass("d-none");
    $("#imap_oauth_azure_procedure").addClass("d-none");
    $("#imap_password_section").removeClass("d-none");
    $("#imap_mailbox_service_section").removeClass("d-none");
  }

  //onchange imap auth type
  $('#imap_auth_type').change(function(){ 
    var imap_auth_type = $(this).val();
    if(imap_auth_type=='oauth_azure' || imap_auth_type=='oauth_google')
    {
      $("#imap_oauth_parameters").removeClass("d-none");
      $("#imap_oauth_azure_procedure").removeClass("d-none");
      $("#imap_password_section").addClass("d-none");
     
      if(imap_auth_type=='oauth_google') {
        $("#imap_oauth_tenant_id_section").addClass("d-none");
        $("#imap_oauth_azure_procedure").addClass("d-none");
        $("#imap_oauth_google_procedure").removeClass("d-none");
      }
      if(imap_auth_type=='oauth_azure') {
        $("#imap_oauth_tenant_id_section").removeClass("d-none");
        $("#imap_oauth_azure_procedure").removeClass("d-none");
        $("#imap_oauth_google_procedure").addClass("d-none");
      }
    }else {
      $("#imap_oauth_parameters").addClass("d-none");
      $("#imap_oauth_azure_procedure").addClass("d-none");
      $("#imap_password_section").removeClass("d-none");
      $("#imap_mailbox_service_section").removeClass("d-none");
    }
  });	

  
  $("#mailbox_oauth_section").addClass("d-none");

  //onchange mailbox service imap auth type
  $('#mailbox_service_auth_type').change(function(){ 
    var mailbox_service_auth_type = $(this).val();
    if(mailbox_service_auth_type=='oauth_azure' || mailbox_service_auth_type=='oauth_google')
    {
      $("#mailbox_oauth_section").removeClass("d-none");
      $("#mailbox_password_section").addClass("d-none");
      
      if(mailbox_service_auth_type=='oauth_google') {
        $("#mailbox_tenant_id_section").addClass("d-none");
      }
      if(mailbox_service_auth_type=='oauth_azure') {
        $("#mailbox_tenant_id_section").removeClass("d-none");
      }
    }else {
      $("#mailbox_oauth_section").addClass("d-none");
      $("#mailbox_password_section").removeClass("d-none");
    }
  });	

}

//color picker 
$(function () {
    //for mail settings in general parameter form
    $('#mail_color_title').colorpicker({useHashPrefix: false});
    $('#mail_color_bg').colorpicker({useHashPrefix: false});
    $('#mail_color_text').colorpicker({useHashPrefix: false});
});

//tooltips
jQuery(function($) {
  $('#tooltip1').tooltip()
  $('#tooltip2').tooltip()
  $('#tooltip3').tooltip()
  $('#tooltip4').tooltip()
  $('#tooltip5').tooltip()
  $('#tooltip6').tooltip()
  $('#tooltip7').tooltip()
  $('#tooltip8').tooltip()
  $('#tooltip9').tooltip()  
  $('#tooltip10').tooltip()
  $('#tooltip11').tooltip()
  $('#tooltip12').tooltip()
  $('#tooltip13').tooltip()
  $('#tooltip14').tooltip()
  $('#tooltip15').tooltip()
  $('#tooltip16').tooltip()
  $('#tooltip17').tooltip()
  $('#tooltip18').tooltip()
  $('#tooltip19').tooltip()
  $('#tooltip20').tooltip()
  $('#tooltip21').tooltip()
  $('#tooltip22').tooltip()
  $('#tooltip23').tooltip()
  $('#tooltip24').tooltip()
  $('#tooltip25').tooltip()
  $('#tooltip26').tooltip()
  $('#tooltip27').tooltip()
  $('#tooltip28').tooltip()
  $('#tooltip29').tooltip()
  $('#tooltip30').tooltip()
  $('#tooltip31').tooltip()
  $('#tooltip32').tooltip()
  $('#tooltip33').tooltip()
  $('#tooltip34').tooltip()
  $('#tooltip35').tooltip()
  $('#tooltip36').tooltip()
  $('#tooltip37').tooltip()
  $('#tooltip38').tooltip()
  $('#tooltip39').tooltip()
  $('#tooltip40').tooltip()
  $('#tooltip41').tooltip()
  $('#tooltip42').tooltip()
  $('#tooltip43').tooltip()
  $('#tooltip44').tooltip()
  $('#tooltip45').tooltip()
  $('#tooltip46').tooltip()
  $('#tooltip47').tooltip()
  $('#tooltip48').tooltip()
  $('#tooltip49').tooltip()
  $('#tooltip50').tooltip()
  $('#tooltip51').tooltip()
  $('#tooltip52').tooltip()
  $('#tooltip53').tooltip()
  $('#tooltip54').tooltip()
  $('#tooltip55').tooltip()
  $('#tooltip56').tooltip()
  $('#tooltip57').tooltip()
  $('#tooltip58').tooltip()
  $('#tooltip59').tooltip()
  $('#tooltip60').tooltip()
  $('#tooltip61').tooltip()
  $('#tooltip62').tooltip()
  $('#tooltip63').tooltip()
  $('#tooltip64').tooltip()
  $('#tooltip65').tooltip()
  $('#tooltip66').tooltip()
  $('#tooltip67').tooltip()
  $('#tooltip68').tooltip()
  $('#tooltip69').tooltip()
  $('#tooltip70').tooltip()
  $('#tooltip71').tooltip()
  $('#tooltip72').tooltip()
  $('#tooltip73').tooltip()
  $('#tooltip74').tooltip()
})

//ClipboardCopy
function setClipboard(value) {
  var tempInput = document.createElement("input");
  tempInput.style = "position: absolute; left: -1000px; top: -1000px";
  tempInput.value = value;
  document.body.appendChild(tempInput);
  tempInput.select();
  document.execCommand("copy");
  document.body.removeChild(tempInput);
}
