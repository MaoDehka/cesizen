//################################################################################
// @Name : js/admin_user.js
// @Description : script to admin user page
// @call : admin/user.php
// @parameters : 
// @Author : Flox
// @Create : 13/07/2021
// @Update : 31/08/2023
// @Version : 3.2.38
//################################################################################

//CTRL+S to save ticket 
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
         $('#edit_user #modify').click();
         $('#add_user #add').click();
        return false;
    }
    return true;
}); 

//color picker 
$(function () {
    //for tech planning settings in user parameter tab
    $('#planning_color').colorpicker({useHashPrefix: true});
});
