//################################################################################
// @Name : js/preview_mail.js
// @Description : script to ticket page
// @call : preview_mail.php
// @parameters : 
// @Author : Flox
// @Create : 06/04/2021
// @Update : 06/04/2021
// @Version : 3.2.9
//################################################################################
jQuery(function($) {
    //hide all copy fields
    $("#user_copy2").hide();
    $("#user_copy3").hide();
    $("#user_copy4").hide();
    $("#user_copy5").hide();
    $("#user_copy6").hide();
    //display only on change
    $('#usercopy').change(function() {$("#user_copy2").show();});
    $('#usercopy2').change(function() {$("#user_copy3").show();});
    $('#usercopy3').change(function() {$("#user_copy4").show();});
    $('#usercopy4').change(function() {$("#user_copy5").show();});
    $('#usercopy5').change(function() {$("#user_copy6").show();});

     //hide all cci fields
     $("#user_copy2_cci").hide();
     $("#user_copy3_cci").hide();
     $("#user_copy4_cci").hide();
     $("#user_copy5_cci").hide();
     $("#user_copy6_cci").hide();
     //display only on change
     $('#usercopy_cci').change(function() {$("#user_copy2_cci").show();});
     $('#usercopy2_cci').change(function() {$("#user_copy3_cci").show();});
     $('#usercopy3_cci').change(function() {$("#user_copy4_cci").show();});
     $('#usercopy4_cci').change(function() {$("#user_copy5_cci").show();});
     $('#usercopy5_cci').change(function() {$("#user_copy6_cci").show();});
});
