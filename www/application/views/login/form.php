<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<body onload="$('loginName').focus();">
  <div id="login_holder">
<?php  
    $userlogin=getUserLogin();
    $notice = $userlogin->notice();
    if ($notice!="") {
      echo "    
      <table width='100%'>
        <tr>
            <td><div class='errormessage'>".$notice."</div></td>
        </tr>
      </table>\n";
    }
    $err = getErrorMessage();
    if ($err != "") {
        echo "<div class='errormessage' width='100%'>".$err."</div>";
        clearErrorMessage();
    }
    $formtitle = __('Welcome to the Aigaion bibliography system, please login');
    if ($this->latesession->get('FORMREPOST')==True) {
        echo "<div class='errormessage' width='100%'>".sprintf(__('You just submitted a form named %s, but it seems that you have been logged out. To proceed with submitting the information, please log in again, then confirm that you want to re-submit the data.'), $this->latesession->get('FORMREPOST_formname'))
			   ." </div>";
        $formtitle = __('Login to proceed with form submission');
    }

    //the login form is NOT shown if 'external login module' is activated
    //however, external login is killed, for now!
        $formAttributes = array('id' => 'loginForm');
        echo form_open_multipart('login/dologin/'.implode('/',$segments),$formAttributes);
    ?>
        <table cellspacing="3" cellpadding="3" width="100%">
          <tr>
            <td colspan='2'><div class='header'><?php echo $formtitle; ?></div></td>
          </tr>
          <tr>
            <td><?php echo __('Name');?>:</td>
            <td><?php
              $data = array(
              'name'        => 'loginName',
              'id'          => 'loginName',
              'maxlength'   => '100',
              'size'        => '50'
              );
              echo form_input($data);
            ?></td>
          </tr>
          <tr>
            <td><?php echo __('Password')?>:</td>
            <td><?php
              $data = array(
              'name'        => 'loginPass',
              'id'          => 'loginPass',
              'maxlength'   => '100',
              'size'        => '50'
              );
              echo form_password($data);
            ?></td>
          </tr>
          <tr>
            <td></td>
            <td><?php
              $data = array(
              'name'        => 'remember',
              'id'          => 'remember',
              'title'       => 'Remember me',
              'checked'     => FALSE
              );
              echo form_checkbox($data);
              echo '&nbsp;'.__('Remember me').'.';
              echo '<p class="alignright">';
              echo form_submit('submitlogin', __('Login'));
              echo '</p>';
            ?></td>
          </tr>
          <tr>
            <td colspan='2'>
              <?php echo sprintf(__('If you want a password, please mail to %s.'), "<a href='mailto: ". getConfigurationSetting("CFG_ADMIN")." <".getConfigurationSetting("CFG_ADMINMAIL").">?subject=Registration request for ".getConfigurationSetting("WINDOW_TITLE")." Aigaion database'>".getConfigurationSetting("CFG_ADMIN")."</a>");?>
              <br/>
            </td>
          </tr>
          <tr>
            <td colspan='2'>
              <?php echo sprintf(__('For more information about the Aigaion bibliography system visit %s'), '<a href="http://www.aigaion.nl/" class="external">Aigaion.nl</a>');?>
            </td>
          </tr>
        </table>
    <?php
        echo form_close();
    ?>
  </div>
</body>