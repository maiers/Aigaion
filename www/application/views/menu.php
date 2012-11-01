<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!-- Aigaion menu -->
<?php
  //view parameter: if $sortPrefix is set, the sort options will be shown in the menu as links to $sortPrefix.'title' etc
  //view parameter: if $exportCommand is set, the export block will include an export command for the browse list
  //view parameter: if $exportName is set, this determines the text for the exportCommand menu option
  
  $userlogin = getUserLogin();
?>
<div id="menu_holder">
  <ul class="mainmenu">
    <li class="mainmenu-header"><?php echo utf8_strtoupper(__('Browse')); ?></li>
    <li><ul class="mainmenu">
    <li class="mainmenu"><?php echo anchor('topics', __('My Topics')); ?></li>
    <?php
    if ($userlogin->hasRights('bookmarklist')) 
    {
      ?>
      <li class="mainmenu"><?php echo anchor('bookmarklist', __('My Bookmarks')); ?></li>
      <?php
    }
    ?>
    <li class="mainmenu"><?php echo anchor('topics/all', __('All Topics')); ?></li>
    <li class="mainmenu"><?php echo anchor('publications', __('All Publications')); ?></li>
    <li class="mainmenu"><?php echo anchor('authors', __('All Authors')); ?></li>
    <li class="mainmenu"><?php echo anchor('keywords', __('All Keywords')); ?></li>
    <li class="mainmenu"><?php echo anchor('publications/unassigned', __('Unassigned')); ?></li>
    <li class="mainmenu"><?php echo anchor('publications/showlist/recent', __('Recent')); ?></li>
    <li class="mainmenu"><?php echo anchor('search', __('Search')); ?></li>

    <?php
    //the export option is slightly dependent on the view parameter 'exportCommand'
    //
    ?>
</ul></li>
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header"><?php echo utf8_strtoupper(__('Export')); ?></li>
    <li><ul class="mainmenu">
    <li class="mainmenu"><?php echo anchor('export', __('Export all publications')); ?></li>
    <?php
    if (isset($exportCommand)&&($exportCommand!=''))
    {
      ?>
      <li class="mainmenu"><?php echo anchor($exportCommand, $exportName); ?></li>
      <?php
    }
    ?>
    </ul></li>

    <?php
    //the sort options are only available if the view is called with a 'sortPrefix' option that is not ''
    //
    if (isset($sortPrefix)&&($sortPrefix!=''))
    {
      ?>
      <li class="mainmenu-spacer"></li>
      <li class="mainmenu-header"><?php echo utf8_strtoupper(__('Sort by')); ?></li>
      <li><ul class="mainmenu">
      <li class="mainmenu"><?php echo anchor($sortPrefix.'author', __('Author')); ?></li>
      <li class="mainmenu"><?php echo anchor($sortPrefix.'title',  __('Title')); ?></li>
      <li class="mainmenu"><?php echo anchor($sortPrefix.'type',   __('Type/Journal')); ?></li>
      <li class="mainmenu"><?php echo anchor($sortPrefix.'year',   __('Year')); ?></li>
      <li class="mainmenu"><?php echo anchor($sortPrefix.'recent', __('Recently added')); ?></li>
      </ul></li>
      <?php
    }
    ?>
    <?php

    //you need the proper userrrights to create new items
    if ($userlogin->hasRights('publication_edit'))
    {
      ?>  
      <li class="mainmenu-spacer"></li>
      <li class="mainmenu-header"><?php echo utf8_strtoupper(__('New Data')); ?></li>
      <li><ul class="mainmenu">
      <li class='mainmenu'><?php echo anchor('publications/add', __('New Publication')); ?></li>
      <li class='mainmenu'><?php echo anchor('authors/add', __('New Author')); ?></li>
      <?php
        if ($userlogin->hasRights('topic_edit'))
        {
          ?>
          <li class='mainmenu'><?php echo anchor('topics/add', __('New Topic')); ?></li>
          <?php
        } 
      ?>
      <li class='mainmenu'><?php echo anchor('import', __('Import')); ?></li>
      </ul></li>
      <?php
    }

?>

    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header"><?php echo utf8_strtoupper(__('Site')); ?></li>
    <li><ul class="mainmenu">
    <li class="mainmenu"><?php echo anchor('help/', __('Help')); ?></li>
    <li class="mainmenu"><?php echo anchor('help/viewhelp/about', __('About this site')); ?></li>
<?php
if ($userlogin->hasRights('database_manage')) {
?>
    <li class="mainmenu"><?php echo anchor('site/configure', __('Site Configuration')); ?></li>
    <li class="mainmenu"><?php echo anchor('site/maintenance', __('Site Maintenance')); ?></li>
<?php
}
if ($userlogin->hasRights('user_edit_all')) {
    echo "    <li class='mainmenu'>".anchor('users/manage', __('Manage All Accounts'))."</li>\n";
}
?>
    </ul></li>

    <li class="mainmenu-spacer"></li>
<?php
  if ($userlogin->isAnonymous()) {
    $anonusers = $this->user_db->getAllAnonUsers();
?>	    
      <li class="mainmenu-spacer"></li>
      <li class="mainmenu-header"><?php echo utf8_strtoupper(__('Guest User')); ?></li>
<?php
    if (count($anonusers)>1) {
      //more than one anonymous user: show a dropdown where you can choose between the different guest users
      $options = array();
      foreach ($anonusers as $anon) {
          $options[$anon->user_id] = $anon->login;
      }
      echo  "    <li class='mainmenu'>"
             .form_dropdown('anonlogin', 
                            $options, 
                            $userlogin->userId(),
                            "OnChange='var url=\"".site_url('/login/anonymous/')."\";window.document.location=(url+\"/\"+$(\"anonlogin\").value);' id='anonlogin'")
             ."</li>";
    } else {
      echo "<li class='mainmenu'>".$anonusers[0]->login."</li>";
    }

//probably no-one would ever assign these two rights to the anon user, but nevertheless....:
if ($userlogin->hasRights('user_edit_self')) {
    echo "    <li class='mainmenu'>".anchor('users/edit/'.$userlogin->userId(), utf8_strtoupper(__('My Profile')))."</li>\n";
}
if ($userlogin->hasRights('topic_subscription')) {
    echo "    <li class='mainmenu'>".anchor('users/topicreview/', __('Topic Subscribe'))."</li>\n";
}
        
?>	    
    <li class="mainmenu-spacer"></li>
    <li class="mainmenu-header"><?php echo utf8_strtoupper(__('Login')); ?></li>
<?php
    $this->load->helper('form');
    echo '<li>';
    $postfix = $this->uri->uri_string();
    if ((strlen($postfix)>0) && ($postfix[0]!='/')) $postfix = '/'.$postfix;
    echo form_open('login/dologin'.$postfix);
?>
      <table class='loginbox'>
        <tr>
          <td><?php _e('Name'); ?>:</td>
        </tr>
        <tr>
          <td><input type=text name=loginName size=10></td>
        </tr>
        <tr>
          <td><?php _e('Password'); ?>:</td>
        </tr>
        <tr>
          <td><input type=password name=loginPass size=10></td>
        </tr>
        <tr>
          <td><input title='<?php _e('Remember me'); ?>' name=remember type=checkbox><p align=right><input type=submit value='<?php _e('Login'); ?>'></td>
        </tr>
      </table>
<?php
        echo form_close();
        echo '</li>';
  } else {
?>
    <li class="mainmenu-header"><?php echo utf8_strtoupper(__('Logged In')).":"; ?></li>
    <li class="mainmenu"><?php echo $userlogin->loginName(); ?></li>
<?php
if ($userlogin->hasRights('user_edit_self')) {
    echo "    <li class='mainmenu'>".anchor('users/edit/'.$userlogin->userId(), __('My Profile'))."</li>\n";
    echo "    <li class='mainmenu'>".anchor('users/setpassword/'.$userlogin->userId(), __('Set password'))."</li>\n";
}
if ($userlogin->hasRights('topic_subscription')) {
    echo "    <li class='mainmenu'>".anchor('users/topicreview/', __('Topic Subscribe'))."</li>\n";
}
?>
    <li class="mainmenu"><?php echo anchor('login/dologout', __('Logout')); ?></li>
<?php
  }
?>
  </ul>
<br/><br/>
<div style='float:bottom;font-size:90%;'>
<?php 
global $AIGAION_SHORTLIST_LANGUAGES;
foreach ($AIGAION_SHORTLIST_LANGUAGES as $lang)
{
  echo anchor('language/set/'.$lang.'/'.implode('/',$this->uri->segment_array()),$this->userlanguage->getLanguageName($lang)).', ';
}
echo anchor('language/choose/',"&lt;".__('more')."...&gt;");
?>
</div>
</div>

<!-- End of menu -->
