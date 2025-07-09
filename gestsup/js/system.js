//################################################################################
// @Name : js/system.js
// @Description : script to system page
// @call : admin/system.php
// @parameters : 
// @Author : Flox
// @Create : 04/01/2022
// @Update : 03/02/2023
// @Version : 3.2.33
//################################################################################

copyToClipBoard = () => {
    var server = document.getElementById("server");
    server=jQuery(server).text();
    server = server.replace(/\s/g, '');
    server = server.replaceAll(")", ")\n");
    server = "Server :\n"+server

    var client = document.getElementById("client");
    client=jQuery(client).text();
    client = client.replace(/\s/g, '');
    client = client.replaceAll("Navigateur", "\nNavigateur");
    client = client.replaceAll("IP", "\nIP");
    client = "\nClient :\n"+client

    var network = document.getElementById("network");
    network=jQuery(network).text();
    network = network.replace(/\s/g, '');
    network = network.replaceAll(")", ")\n");
    network = "\n\nNetwork :\n"+network

    var conf_error = document.getElementById("conf_error");
    if(jQuery(conf_error).text())
    {
        conf_error=jQuery(conf_error).text();
        conf_error = conf_error.replace(/\s/g, '');
        conf_error = conf_error.replaceAll(")", ")\n");
        conf_error = "\nConfig Error :\n"+conf_error
    } else {conf_error='';}
    
    var php_parameters = document.getElementById("php_parameters");
    php_parameters=jQuery(php_parameters).text();
    php_parameters = php_parameters.replace(/\s/g, '');
    php_parameters = php_parameters.replaceAll("memory_limit", "\nmemory_limit");
    php_parameters = php_parameters.replaceAll("upload_max_filesize", "\nupload_max_filesize");
    php_parameters = php_parameters.replaceAll("post_max_size", "\npost_max_size");
    php_parameters = php_parameters.replaceAll("max_execution_time", "\nmax_execution_time");
    php_parameters = php_parameters.replaceAll("date.timezone", "\ndate.timezone");
    php_parameters = "\n\nPHP parameters :\n"+php_parameters

    var php_extensions = document.getElementById("php_extensions");
    php_extensions=jQuery(php_extensions).text();
    php_extensions = php_extensions.replace(/\s/g, '');
    php_extensions = php_extensions.replaceAll("php_fileinfo", "\nphp_fileinfo");
    php_extensions = php_extensions.replaceAll("php_gd", "\nphp_gd");
    php_extensions = php_extensions.replaceAll("php_iconv", "\nphp_iconv");
    php_extensions = php_extensions.replaceAll("php_imap", "\nphp_imap");
    php_extensions = php_extensions.replaceAll("php_intl", "\nphp_intl");
    php_extensions = php_extensions.replaceAll("php_json", "\nphp_json");
    php_extensions = php_extensions.replaceAll("php_ldap", "\nphp_ldap");
    php_extensions = php_extensions.replaceAll("php_mbstring", "\nphp_mbstring");
    php_extensions = php_extensions.replaceAll("php_openssl", "\nphp_openssl");
    php_extensions = php_extensions.replaceAll("php_pdo_mysql", "\nphp_pdo_mysql");
    php_extensions = php_extensions.replaceAll("php_xml", "\nphp_xml");
    php_extensions = php_extensions.replaceAll("php_zip", "\nphp_zip");
    php_extensions = "\n\nPHP extensions :\n"+php_extensions

    var security = document.getElementById("security");
    security=jQuery(security).text();
    security = security.replace(/\s/g, '');
    security = security.replaceAll(")", ")\n");
    security = security.replaceAll("Droits", "\nDroits");
    security = security.replaceAll(".SMTP", "SMTP");
    security = security.replaceAll("IMAP", "\nIMAP");
    security = security.replaceAll(".Motsdepasse", "Mot de passe");
    security = security.replaceAll(".Motdepasseadmin", "Mot de passe admin");
    security = security.replaceAll("Logs", "\nLogs");
    security = security.replaceAll("RestrictionIP", "\nRestrictionIP");
    security = security.replaceAll(".Listingdesrépertoires", "Listing des répertoires");
    security = security.replaceAll("Droitsd'écriture", "Droitsécriture");
    security = security.replaceAll("d'apache", "apache");
    security = security.replaceAll("l'accès", "acces");
    security = security.replaceAll('"ServerTokens"', 'ServerTokens');
    security = security.replaceAll('"Prod"', 'Prod');
    security = security.replaceAll('"expose_php"', 'expose_php');
    security = security.replaceAll('"Off"', 'Off');
    security = "\n\nSecurity :\n"+security

    var components = document.getElementById("components");
    components=jQuery(components).text();
    components = components.replace(/\s/g, '');
    components = components.replaceAll("makeusabrew/bootbox", "\nmakeusabrew/bootbox");
    components = components.replaceAll("twbs/bootstrap", "\ntwbs/bootstrap");
    components = components.replaceAll("itsjavi/bootstrap-colorpicker", "\nitsjavi/bootstrap-colorpicker");
    components = components.replaceAll("steveathon/bootstrap-wysiwyg", "\nsteveathon/bootstrap-wysiwyg");
    components = components.replaceAll("selectize/selectize.js", "\nselectize/selectize.js");
    components = components.replaceAll("FortAwesome/Font-Awesome", "\nFortAwesome/Font-Awesome");
    components = components.replaceAll("fullcalendar/fullcalendar", "\nfullcalendar/fullcalendar");
    components = components.replaceAll("highcharts/highcharts", "\nhighcharts/highcharts");
    components = components.replaceAll("jquery/jquery", "\njquery/jquery");
    components = components.replaceAll("jeresig/jquery.hotkeys", "\njeresig/jquery.hotkeys");
    components = components.replaceAll("thephpleague/oauth2-client", "\nthephpleague/oauth2-client");
    components = components.replaceAll("thephpleague/oauth2-google", "\nthephpleague/oauth2-google");
    components = components.replaceAll("stevenmaguire/oauth2-microsoft", "\nstevenmaguire/oauth2-microsoft");
    components = components.replaceAll("greew/oauth2-azure-provider", "\ngreew/oauth2-azure-provider");
    components = components.replaceAll("microsoftgraph/msgraph-sdk-php", "\nmicrosoftgraph/msgraph-sdk-php");
    components = components.replaceAll("moment/moment", "\nmoment/moment");
    components = components.replaceAll("PHPMailer/PHPMailer", "\nPHPMailer/PHPMailer");
    components = components.replaceAll("barbushin/php-imap", "\nbarbushin/php-imap");
    components = components.replaceAll("inetsys/phpgettext", "\ninetsys/phpgettext");
    components = components.replaceAll("ifsnop/mysqldump-php", "\nifsnop/mysqldump-php");
    components = components.replaceAll("FezVrasta/popper.js", "\nFezVrasta/popper.js");
    components = components.replaceAll("tempusdominus/bootstrap-4", "\ntempusdominus/bootstrap-4");
    components = components.replaceAll("Webklex/php-imap", "\nWebklex/php-imap");
    components = components.replaceAll("WOL", "\nWOL");
    components = "\n\nComponents :\n"+components

    var plugins = document.getElementById("plugins");
    plugins=jQuery(plugins).text();
    plugins = plugins.replace(/\s/g, '');
    plugins = "\n\nPlugins :\n"+plugins

    console.log(plugins)
    
    var textArea = document.createElement("textarea");
    textArea.value = server+client+network+conf_error+php_parameters+php_extensions+plugins+security+components;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand("Copy");
    textArea.remove();
}

