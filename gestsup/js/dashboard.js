//################################################################################
// @Name : js/dashboard.js
// @Description : script to dashboard page
// @call : dashboard.php
// @parameters : 
// @Author : Flox
// @Create : 26/08/2022
// @Update : 26/08/2022
// @Version : 3.2.26
//################################################################################

//get theme color
var theme = document.getElementById("theme").value;

//date picker
new tempusDominus.TempusDominus(document.getElementById('date_start'), {
    display: {
        components: {
            clock: false
        },
        theme: theme
    },
    localization: {
        startOfTheWeek: 1
    },
});

new tempusDominus.TempusDominus(document.getElementById('date_end'), {
    display: {
        components: {
            clock: false
        }, 
        theme: theme
    },
    localization: {
        startOfTheWeek: 1
    },
});