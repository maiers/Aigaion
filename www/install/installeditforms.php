<?php


function getMainInstallForm($vars, $values, $is_configured) {
  $form_elements = array('migrate_from_old', 'install_step');
?>
<div class=editform>
  <p class=header>Welcome to Aigaion</p>
<?php
  if ($is_configured)
  {
?>
  <div class='errormessage'>This site already seems to be configured, please check if the data in '&lt;aigaion_root&gt;/index.php' are correct and then remove the '&lt;aigaion_root&gt;/install/' directory. When the data are not correct, and you want a fresh install of Aigaion, please remove '&lt;aigaion_root&gt;/index.php' and drop all tables from your aigaion2 database, then try again.</div>
</div>
<?php 
  }
  else
  {
?>
  <p>This site is not yet configured, in the next few steps we will make all settings required for installing Aigaion to your system.</p>
  <p>It may be possible that you also have to ask the administrator of the server on which this installation is running to provide you with information about your access to a mysql server.</p>
  
  <form name='installform' enctype='multipart/form-data' method='post' action='install.php'>
  <p>If you want to migrate from Aigaion 1.x please check the following checkbox, then click 'proceed'.<br/>
  <table>
    <tr>
      <td>Migrate from Aigaion 1.x</td>
      <td><input name='migrate_from_old' type='checkbox' value='true' <?php if ($values['migrate_from_old']) { echo " checked='checked'";} ?>/></td>
    </tr>
    <tr>
      <td colspan='2'><input name='proceed' type='submit' value='Proceed' /></td>
    </tr>
  </table>
  <input type='hidden' name='install_step' value='step2' />
<?php
  $unused_vars = array_diff($vars, $form_elements);
  foreach ($unused_vars as $var)
  {
    if (isset($values[$var]))
    {
      echo "  <input type='hidden' name='".$var."' value='".$values[$var]."' />\n";
    }
  }
?>
  </form>
</div>
<?php
  }
}

function getMigrationForm($vars, $values){
  $form_elements = array('install_step',
                         'AIGAION1_DB_HOST',
                         'AIGAION1_DB_USER',
                         'AIGAION1_DB_PWD',
                         'AIGAION1_DB_NAME',
                         'AIGAION2_DB_HOST',
                         'AIGAION2_DB_USER',
                         'AIGAION2_DB_PWD',
                         'AIGAION2_DB_NAME',
                         'AIGAION2_DB_PREFIX');
                         
  //set default values
  if (!isset($values['AIGAION1_DB_HOST']))
    $AIGAION1_DB_HOST = 'localhost';
  else
    $AIGAION1_DB_HOST = $values['AIGAION1_DB_HOST'];
  
  if (!isset($values['AIGAION1_DB_USER']))
    $AIGAION1_DB_USER = 'username';
  else
    $AIGAION1_DB_USER = $values['AIGAION1_DB_USER'];
  
  if (!isset($values['AIGAION1_DB_PWD']))
    $AIGAION1_DB_PWD = 'password';
  else
    $AIGAION1_DB_PWD = $values['AIGAION1_DB_PWD'];
  
  if (!isset($values['AIGAION1_DB_NAME']))
    $AIGAION1_DB_NAME = 'aigaion';
  else
    $AIGAION1_DB_NAME = $values['AIGAION1_DB_NAME'];
  
  if (!isset($values['AIGAION2_DB_HOST']))
    $AIGAION2_DB_HOST = 'localhost';
  else
    $AIGAION2_DB_HOST = $values['AIGAION2_DB_HOST'];
  
  if (!isset($values['AIGAION2_DB_USER']))
    $AIGAION2_DB_USER = 'username';
  else
    $AIGAION2_DB_USER = $values['AIGAION2_DB_USER'];
  
  if (!isset($values['AIGAION2_DB_PWD']))
    $AIGAION2_DB_PWD = 'password';
  else
    $AIGAION2_DB_PWD = $values['AIGAION2_DB_PWD'];
  
  if (!isset($values['AIGAION2_DB_NAME']))
    $AIGAION2_DB_NAME = 'aigaion2';
  else
    $AIGAION2_DB_NAME = $values['AIGAION2_DB_NAME'];    
?>
<div class=editform>
  <p class=header>Installing Aigaion - Migration from existing Aigaion installation</p>
  <p>Before migrating from your previous version of Aigaion, we need a few data.</p>
  
  <form name='installform' enctype='multipart/form-data' method='post' action='install.php'>
  <table>
    <tr>
      <td colspan='2'>
        <hr><p class='header2'>MySql Database Information:</p>
        <p>In this section you specify the relevant information about the MySql server(s). Please change the default values when necessary.
      </td>
    </tr>

    <tr>
      <td colspan='2'><hr><p class='header2'>Database info regarding your <b>OLD</b> Aigaion 1.x installation</p>
      </td>
    </tr>
    <tr>
      <td>Hostname of MySQL server:</td>
      <td align='right'><input type='text' cols='30' name='AIGAION1_DB_HOST' value='<?php echo $AIGAION1_DB_HOST;?>' /></td>
    </tr>
    <tr>
      <td colspan='2'><p>When your MySQL server runs on a non-default portnumber, please add the correct portnumber after the hostname (e.g. localhost:8888).</p>
      </td>
    </tr>
    <tr>
      <td>Database name:</td>
      <td align='right'><input type='text' cols='30' name='AIGAION1_DB_NAME' value='<?php echo $AIGAION1_DB_NAME;?>' /></td>
    </tr>
    <tr>
      <td>MySQL user name:</td>
      <td align='right'><input type='text' cols='30' name='AIGAION1_DB_USER' value='<?php echo $AIGAION1_DB_USER;?>' /></td>
    </tr>
    <tr>
      <td>MySQL user password:</td>
      <td align='right'><input type='password' cols='30' name='AIGAION1_DB_PWD' value='<?php echo $AIGAION1_DB_PWD;?>' /></td>
    </tr>
    
    <tr>
      <td colspan='2'><hr><p class='header2'>Database info regarding your <b>NEW</b> Aigaion installation</p>
      </td>
    </tr>
    <tr>
      <td>Hostname of MySQL server:</td>
      <td align='right'><input type='text' cols='30' name='AIGAION2_DB_HOST' value='<?php echo $AIGAION2_DB_HOST;?>' /></td>
    </tr>
    <tr>
      <td colspan='2'><p>When your MySQL server runs on a non-default portnumber, please add the correct portnumber after the hostname (e.g. localhost:8888).</p>
      </td>
    </tr>
    <tr>
      <td>Database name:</td>
      <td align='right'><input type='text' cols='30' name='AIGAION2_DB_NAME' value='<?php echo $AIGAION2_DB_NAME;?>' /></td>
    </tr>
    <tr>
      <td>MySQL user name:</td>
      <td align='right'><input type='text' cols='30' name='AIGAION2_DB_USER' value='<?php echo $AIGAION2_DB_USER;?>' /></td>
    </tr>
    <tr>
      <td>MySQL user password:</td>
      <td align='right'><input type='password' cols='30' name='AIGAION2_DB_PWD' value='<?php echo $AIGAION2_DB_PWD;?>' /></td>
    </tr>
    
    <tr>
      <td colspan='2'><hr><p class='header2'>Aigaion 2 supports table prefixes, this is optional, leave blank for no prefix.</p>
      </td>
    </tr>
    <tr>
      <td>MySQL table prefix:</td>
      <td align='right'><input type='text' cols='30' name='AIGAION2_DB_PREFIX' value='<?php echo $values['AIGAION2_DB_PREFIX'];?>' /></td>
    </tr>

    <tr>
      <td colspan='2'><input name='proceed' type='submit' value='Proceed' /></td>
    </tr>
  </table>
  <input type='hidden' name='install_step' value='step3' />
<?php
  $unused_vars = array_diff($vars, $form_elements);
  foreach ($unused_vars as $var)
  {
    if (isset($values[$var]))
    {
      echo "  <input type='hidden' name='".$var."' value='".$values[$var]."' />\n";
    }
  }
?>
  </form>
</div>
<?php  
}

function getNewInstallForm($vars, $values){
  $form_elements = array('install_step',
                         'AIGAION2_DB_HOST',
                         'AIGAION2_DB_USER',
                         'AIGAION2_DB_PWD',
                         'AIGAION2_DB_NAME',
                         'AIGAION2_DB_PREFIX');
                         
  //set default values
  if (!isset($values['AIGAION2_DB_HOST']))
    $AIGAION2_DB_HOST = 'localhost';
  else
    $AIGAION2_DB_HOST = $values['AIGAION2_DB_HOST'];
  
  if (!isset($values['AIGAION2_DB_USER']))
    $AIGAION2_DB_USER = 'username';
  else
    $AIGAION2_DB_USER = $values['AIGAION2_DB_USER'];
  
  if (!isset($values['AIGAION2_DB_PWD']))
    $AIGAION2_DB_PWD = 'password';
  else
    $AIGAION2_DB_PWD = $values['AIGAION2_DB_PWD'];
  
  if (!isset($values['AIGAION2_DB_NAME']))
    $AIGAION2_DB_NAME = 'aigaion2';
  else
    $AIGAION2_DB_NAME = $values['AIGAION2_DB_NAME'];    
?>
<div class=editform>
  <p class=header>Installing Aigaion - New Installation</p>
  <p>Before creating the database structure for Aigaion, we need a few data.</p>
  
  <form name='installform' enctype='multipart/form-data' method='post' action='install.php'>
  <table>
    <tr>
      <td colspan='2'>
        <hr><p class='header2'>MySql Database Information:</p>
        <p>In this section you specify the relevant information about the MySql server(s). Please change the default values when necessary.
      </td>
    </tr>
    <tr>
      <td>Hostname of MySQL server:</td>
      <td align='right'><input type='text' cols='30' name='AIGAION2_DB_HOST' value='<?php echo $AIGAION2_DB_HOST;?>' /></td>
    </tr>
    <tr>
      <td colspan='2'><p>When your MySQL server runs on a non-default portnumber, please add the correct portnumber after the hostname (e.g. localhost:8888).</p>
      </td>
    </tr>
    <tr>
      <td>Database name:</td>
      <td align='right'><input type='text' cols='30' name='AIGAION2_DB_NAME' value='<?php echo $AIGAION2_DB_NAME;?>' /></td>
    </tr>
    <tr>
      <td>MySQL user name:</td>
      <td align='right'><input type='text' cols='30' name='AIGAION2_DB_USER' value='<?php echo $AIGAION2_DB_USER;?>' /></td>
    </tr>
    <tr>
      <td>MySQL user password:</td>
      <td align='right'><input type='password' cols='30' name='AIGAION2_DB_PWD' value='<?php echo $AIGAION2_DB_PWD;?>' /></td>
    </tr>
    
    <tr>
      <td colspan='2'><hr><p class='header2'>Aigaion 2 supports table prefixes, this is optional, leave blank for no prefix.</p>
      </td>
    </tr>
    <tr>
      <td>MySQL table prefix:</td>
      <td align='right'><input type='text' cols='30' name='AIGAION2_DB_PREFIX' value='<?php echo $values['AIGAION2_DB_PREFIX'];?>' /></td>
    </tr>

    <tr>
      <td colspan='2'><input name='proceed' type='submit' value='Proceed' /></td>
    </tr>
  </table>
  <input type='hidden' name='install_step' value='step3' />
<?php
  $unused_vars = array_diff($vars, $form_elements);
  foreach ($unused_vars as $var)
  {
    if (isset($values[$var]))
    {
      echo "  <input type='hidden' name='".$var."' value='".$values[$var]."' />\n";
    }
  }
?>
  </form>
</div>
<?php  
}

function getSiteConfigurationForm($vars, $values) {
  $form_elements = array('install_step',
                         'AIGAION_ROOT_DIR',
                         'AIGAION_ROOT_URL',
                         'AIGAION_WEBCONTENT_URL',
                         'AIGAION_WEBCONTENT_DIR',
                         'AIGAION_ATTACHMENT_DIR',
                         'AIGAION_SITEID'
                         );

                         
  //set default values
  if (!isset($values['AIGAION_ROOT_DIR']))
  {
    //Retrieve script name and cut everything before the install/ folder
    $AIGAION_ROOT_DIR = substr($_SERVER['SCRIPT_FILENAME'], 0, -strlen("install/install.php"));
  }
  else
    $AIGAION_ROOT_DIR = $values['AIGAION_ROOT_DIR'];
    
  if (!isset($values['AIGAION_ROOT_URL']))
  {
    //Retrieve script name and cut everything before the install/ folder
    $url_script_name  = $_SERVER["SCRIPT_NAME"];
    $aigaion_url      = substr($url_script_name, 0, -strlen("install/install.php"));
    $server_url       = "http".( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!='off')?'s':'')."://".$_SERVER["SERVER_NAME"];
    
    $AIGAION_ROOT_URL = $server_url.$aigaion_url;
  }
  else
    $AIGAION_ROOT_URL = $values['AIGAION_ROOT_URL'];
  
  if (!isset($values['AIGAION_WEBCONTENT_URL']))
    $AIGAION_WEBCONTENT_URL = $AIGAION_ROOT_URL."webcontent/";
  else
    $AIGAION_WEBCONTENT_URL = $values['AIGAION_WEBCONTENT_URL'];

  if (!isset($values['AIGAION_WEBCONTENT_DIR']))
    $AIGAION_WEBCONTENT_DIR = $AIGAION_ROOT_DIR."webcontent/";
  else
    $AIGAION_WEBCONTENT_DIR = $values['AIGAION_WEBCONTENT_DIR'];

  if (!isset($values['AIGAION_SITEID']))
    $AIGAION_SITEID = 'AigaionInstance1';
  else
    $AIGAION_SITEID = $values['AIGAION_SITEID'];
  
  if (!isset($values['AIGAION_ATTACHMENT_DIR']))
  {
    $AIGAION_ATTACHMENT_DIR = '';
  }
  else
    $AIGAION_ATTACHMENT_DIR = $values['AIGAION_ATTACHMENT_DIR'];
      
   
  
?>
<div class=editform>
  <p class=header>Installing Aigaion - Almost complete</p>
  <p>If no errors showed up, we are now ready to finalize the installation. Please check if the following data are correct, if necessary, adapt the data.</p>
  
  <form name='installform' enctype='multipart/form-data' method='post' action='install.php'>
  <table>
    <tr>
      <td colspan='2'>
        <hr><b>Aigaion Address Information</b>
        <p>In this section you specify the path to your Aigaion installation. Please check if the default values are correct.</p>
      </td>
    </tr>
    <tr>
      <td>Aigaion root URL:</td>
      <td align='right'><input type='text' size='50' name='AIGAION_ROOT_URL' value='<?php echo $AIGAION_ROOT_URL;?>' /></td>
    </tr>
    <tr>
      <td>Aigaion web content URL:</td>
      <td align='right'><input type='text' size='50' name='AIGAION_WEBCONTENT_URL' value='<?php echo $AIGAION_WEBCONTENT_URL;?>' /></td>
    </tr>
    <tr>
      <td colspan='2'><p>When your webserver runs on a non-default portnumber, please add the correct portnumber after the hostname (e.g. http://localhost:8888/aigaion2root). This holds both for the Aigaion root and the Aigaion engine URL.</p>
      </td>
    </tr>
    <tr>
      <td>Aigaion root DIR:</td>
      <td align='right'><input type='text' size='50' name='AIGAION_ROOT_DIR' value='<?php echo $AIGAION_ROOT_DIR;?>' /></td>
    </tr>
    <tr>
      <td>Aigaion webcontent DIR:</td>
      <td align='right'><input type='text' size='50' name='AIGAION_WEBCONTENT_DIR' value='<?php echo $AIGAION_WEBCONTENT_DIR;?>' /></td>
    </tr>
    
    <tr>
      <td colspan='2'>
        <hr>
      </td>
    </tr>
    <tr>
      <td>Aigaion site ID:</td>
      <td align='right'><input type='text' size='50' name='AIGAION_SITEID' value='<?php echo $AIGAION_SITEID;?>' /></td>
    </tr>
    <tr>
      <td colspan='2'>
        <p>When running multiple instances of Aigaion on the same server, choose an unique site ID for each instance.</p>
      </td>
    </tr>
    
    <tr>
      <td colspan='2'>
        <hr>
        <p>The following values are optional, leave blank for using the default values (recommended).</p>
      </td>
    </tr>
    <tr>
      <td>Attachment directory:</td>
      <td align='right'><input type='text' size='50' name='AIGAION_ATTACHMENT_DIR' value='<?php echo $AIGAION_ATTACHMENT_DIR;?>' /></td>
    </tr>
    <tr>
      <td colspan='2'>
        <p>The attachment directory should <b>not</b> end with a slash!</p>
      </td>
    </tr>
    <tr>
      <td colspan='2'><p>When your webserver runs on a non-default portnumber, please add the correct portnumber after the host name (e.g. http://localhost:8888/aigaion2root/attachments).</p>
      </td>
    </tr>
    
    <tr>
      <td colspan='2'><input name='proceed' type='submit' value='Proceed' /></td>
    </tr>
  </table>
  <input type='hidden' name='install_step' value='step4' />
<?php
  $unused_vars = array_diff($vars, $form_elements);
  
  foreach ($unused_vars as $var)
  {
    if (isset($values[$var]))
    {
      echo "  <input type='hidden' name='".$var."' value='".$values[$var]."' />\n";
    }
  }
?>
  </form>
</div>
<?php  
}
?>
