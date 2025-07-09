<?php
$modules = apache_get_modules();
if(in_array('mod_rewrite', $modules)) {echo 'mod_rewrite module : <span style="color:green">enabled</span><br />';} else {echo 'mod_rewrite module : <span style="color:red">disabled</span><br />';};
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {echo 'https : <span style="color:green">enabled</span><br />';} else {echo 'https : <span style="color:red">disabled</span><br />';};
?>
apache2.conf : check AllowOverride All
<br />
<a href="../../vendor/components/swagger-ui/" >API - Documentation</a><br />