<?php

//get necessary includes:
include_once ("installactions.php");
include_once ("installfunctions.php");
include_once ("installeditforms.php");

//specify the variables we are going to use:
$script_variables  = array('install_step',
                           'migrate_from_old');

$install_variables = array('AIGAION1_DB_HOST',
                           'AIGAION1_DB_USER',
                           'AIGAION1_DB_PWD',
                           'AIGAION1_DB_NAME',
                           'AIGAION2_DB_HOST',
                           'AIGAION2_DB_USER',
                           'AIGAION2_DB_PWD',
                           'AIGAION2_DB_NAME',
                           'AIGAION2_DB_PREFIX',
                           'AIGAION_ROOT_URL',
                           'AIGAION_ROOT_DIR',
                           'AIGAION_SITEID',
                           'AIGAION_WEBCONTENT_URL',
                           'AIGAION_WEBCONTENT_DIR',
                           'AIGAION_ATTACHMENT_DIR');

$vars   = array_merge($script_variables, $install_variables);;

//retrieve variables from post:
foreach ($vars as $var)
{
  $values[$var] = NULL;
  if (isset($_REQUEST[$var])) {
    $values[$var] = $_REQUEST[$var];
  }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Aigaion 2.5 - A multi user annotated bibliography - Installer</title>
    <link href="../webcontent/themes/default/css/styling.css"     rel="stylesheet" type="text/css" media="screen,projection,tv" />
  </head>
  <body>
<?php

//we have the install steps in reverse order, for easy switching when some parameter is wrong.
$error_step_4 = false;
$error_step_3 = false;
$error_step_2 = false;

///////////////////////////////////////////
// Step 4
// - Check if all data are correct
// - if so, write the new index file and provide the user with instructions for logging in.
// - if not, redirect the user to the previous step.
///////////////////////////////////////////
if ($values['install_step'] == 'step4')
{
  $error_step_4 = false;
  if (count(explode(' ', $values['AIGAION_SITEID'])) > 1)
  {
    $error_step_4 = true;
    $values['install_step'] = 'step3';
    echo "<div class='errormessage'>The aigaion site ID cannot contain spaces!</div>\n";
  }
  if (($values['AIGAION_ROOT_URL']   == "") ||
      ($values['AIGAION_ROOT_DIR']   == "") ||
      ($values['AIGAION_WEBCONTENT_URL'] == "") ||
      ($values['AIGAION_WEBCONTENT_DIR'] == "")
     )
  {
    $error_step_4 = true;
    $values['install_step'] = 'step3';
    echo "<div class='errormessage'>Please fill in the Aigaion URL and DIR settings!</div>\n";
  }
  if (!$error_step_4)
  {
    writeIndexFile($values['AIGAION2_DB_HOST'], 
                   $values['AIGAION2_DB_USER'],
                   $values['AIGAION2_DB_PWD'], 
                   $values['AIGAION2_DB_NAME'], 
                   $values['AIGAION2_DB_PREFIX'], 
                   $values['AIGAION_SITEID'],
                   $values['AIGAION_ROOT_URL'],
                   $values['AIGAION_ROOT_DIR'], 
                   $values['AIGAION_WEBCONTENT_URL'], 
                   $values['AIGAION_WEBCONTENT_DIR'], 
                   $values['AIGAION_ATTACHMENT_DIR']);
                   
?>
<div class=editform>
  <p class=header>Installing Aigaion - Installation complete</p>
  <p>That's it, Aigaion is now installed and ready to use.
<?php
  if ($values['migrate_from_old'])
  {
?>
  <p>The accounts from your old Aigaion installation are also available in this new installation.
  Please <b>copy the attachments</b> of your old Aigaion installation (located in the '&lt;aigaion1_root&gt;/documents' folder) to the attachments folder of this new installation ('&lt;aigaion2_root&gt;/attachments by default)</p>
  <p>Please run the maintenance checks after logging in!</p>
  <p>We hope you enjoy using Aigaion 2 even more than using the old version!</p>
<?php
  }  
  else
  {
    echo "You can login using the admin account:<br/>";
    echo "Username: 'admin'<br/>";
    echo "Password: 'admin'</br/>";
    echo "Please change this account name and password after logging in.";
  }
?>
  </p>
  <p><b>IMPORTANT:</b> please remove the /install directory completely to prevent other people accessing your system.</p>
  <p><a href="../">Proceed to login!</a></p>
</div>
<?php
  }
}

///////////////////////////////////////////
// Step 3
// - Check if all data are correct
// If so:
// - migrate or create database
// - get site URL and DIR settings form
// If not, return to Step 2
///////////////////////////////////////////
if ($values['install_step'] == 'step3')
{
  if (!$error_step_4)
  {
    if (($values['AIGAION2_DB_HOST'] == "") ||
        ($values['AIGAION2_DB_USER'] == "") ||
        ($values['AIGAION2_DB_PWD']  == "") ||
        ($values['AIGAION2_DB_NAME'] == "") ||
        ($values['migrate_from_old'] && ($values['AIGAION1_DB_HOST'] == "")) ||
        ($values['migrate_from_old'] && ($values['AIGAION1_DB_USER'] == "")) ||
        ($values['migrate_from_old'] && ($values['AIGAION1_DB_PWD']  == "")) ||
        ($values['migrate_from_old'] && ($values['AIGAION1_DB_NAME'] == ""))
       )
    {
      $error_step_3 = true;
      $values['install_step'] = 'step2';
      echo "<div class='errormessage'>Please fill in all database information!</div>";
    }
    if (!$error_step_3)
    {
      if ($values['migrate_from_old'])
      {
        //migrate database
        migrateOldDatabase($values['AIGAION1_DB_HOST'],
                           $values['AIGAION1_DB_USER'], 
                           $values['AIGAION1_DB_PWD'], 
                           $values['AIGAION1_DB_NAME'], 
                           $values['AIGAION2_DB_HOST'], 
                           $values['AIGAION2_DB_USER'], 
                           $values['AIGAION2_DB_PWD'], 
                           $values['AIGAION2_DB_NAME'], 
                           $values['AIGAION2_DB_PREFIX']);    
      }
      else
      {
        //create new database
        installNewDatabase($values['AIGAION2_DB_HOST'], 
                           $values['AIGAION2_DB_USER'], 
                           $values['AIGAION2_DB_PWD'], 
                           $values['AIGAION2_DB_NAME'], 
                           $values['AIGAION2_DB_PREFIX']);
      }
      getSiteConfigurationForm($vars, $values);
    }
  }
  else //error_step_4
    getSiteConfigurationForm($vars, $values);
}

///////////////////////////////////////////
// Step 2
// - Check if we are migrating from 1.x
// - If so, get migration form
// - If not, get new install form
///////////////////////////////////////////
if ($values['install_step'] == 'step2')
{
  if ($values['migrate_from_old'])
  {
    //call migration form
    getMigrationForm($vars, $values);
  }
  else
  {
    //call install form
    getNewInstallForm($vars, $values);
  }
}

if (!isset($values['install_step']))
{
  $is_configured = siteIsConfigured();
  getMainInstallForm($vars, $values, $is_configured);
}

?>
  </body>
</html>