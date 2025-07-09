<?php
################################################################################
# @Name : pwd_recovery.php
# @Description : recover pwd
# @Call : 
# @Parameters : 
# @Author : Flox
# @Create : 19/03/2019
# @Update : 19/02/2021
# @Version : 3.2.9
################################################################################

//init var
if(!isset($_POST['login'])){$_POST['login']='';}

//secure posted data
$_POST['login']=htmlspecialchars($_POST['login'], ENT_QUOTES, 'UTF-8');

?>
<h1>GestSup password recovery</h1>
<form method="POST" action="">
	<label for="login">Login :</label>
	<input autocomplete="off" type="text" name="login" />
	<label for="password">password :</label>
	<input autocomplete="off" type="password" name="password" />
	<input type="submit" />
</form>
<?php
if(isset($_POST['password']) && isset($_POST['login']))
{
	$hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
	
	$query1="UPDATE `tusers` SET `password`='$hash', ldap_guid='', disable='0' WHERE `login`='$_POST[login]';";
	$query2="UPDATE `tparameters` SET `ldap_auth`='0';";
	echo '
		Follow this steps :
		<ul>
			<li>STEP 1 : Connect to database (PhpMyAdmin or command line)</li>
			<li>STEP 2 : Select GestSup database (default bsup)</li>
			<li>STEP 3 : Execute the following requests :</li>
			<br />
			<ul>
			 <li><b>'.$query1.'</b></li>
			 <li><b>'.$query2.'</b></li>
			 <ul>
		</ul>
	';
}
?>