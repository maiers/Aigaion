<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/site/edit

Shows a form for editing the site configuration.

Parameters:
    $siteconfig     the site config object

we assume that this view is not loaded if you don't have the appropriate database_manage rights
*/
$this->load->helper('form');
$this->load->helper('translation');
echo "<div class='editform'>";
echo form_open_multipart('site/configure/commit');
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','siteconfig');

echo "<p class='header'>".utf8_strtoupper(__('Aigaion site configuration form'))."</p>";
echo "This edit form is deprecated and has been replaced by the site/configure controller and many sub forms for parts of the configuration settings.<br/>\n";
echo $this->form_validation->error_string();
?>
    <table width='100%'>
<!-- SITE ADMIN NAME -->
        <tr>
            <td colspan='2'><hr><p class='header2'><?php echo __('Site Admin:');?></p></td>
	    </tr>

	    <tr>
	        <td><label for='CFG_ADMIN'><?php echo __('Name of Aigaion administrator:');?></label></td>
	        <td align='left'><input type='text' cols='60' size=50 name='CFG_ADMIN' value='<?php echo $siteconfig->getConfigSetting("CFG_ADMIN"); ?>'></td>
	    </tr>

	    <tr>
	        <td><label for='CFG_ADMINMAIL'><?php echo __('Email of Aigaion administrator:');?></label></td>
	        <td align='left'><input type='text' cols='60' size=50 name='CFG_ADMINMAIL' value='<?php echo $siteconfig->getConfigSetting("CFG_ADMINMAIL"); ?>'></td>
	    </tr>

<!-- ALL LOGIN SETTINGS -->
<?php 
//[DR 2008.09.02] working on transforming the login modules to the new structure
?>
        <tr>
            <td colspan='2'><hr><p class='header2'><?php echo __('Login settings (Anonymous access):');?></p><p><?php echo __('These login settings determine how anonymous (guest) access to Aigaion is configured.');?></p></td>
        </tr>


        <tr>
	        <td><label><?php echo __('Enable anonymous access:');?></label></td>
	        <td align='left'>
<?php	            
    echo form_checkbox('LOGIN_ENABLE_ANON','LOGIN_ENABLE_ANON',$siteconfig->getConfigSetting("LOGIN_ENABLE_ANON")== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Check this box to enable anonymous (guest) access.');?></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>

        <tr>
            <td><?php echo __('Default anonymous user account');?></td>
            <td>
<?php              
            $options = array(''=>'');
            foreach ($anonUsers as $anonUser) {
                $options[$anonUser->user_id] = $anonUser->login;
            }
            echo form_dropdown('LOGIN_DEFAULT_ANON', $options,$siteconfig->getConfigSetting("LOGIN_DEFAULT_ANON"));
?>
            </td>                
        </tr>
	    <tr>
	        <td align='left' colspan='2'>
	        <p><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __("Select the user account that will be used by default for logging in anonymous users. Only users that are marked 'anonymous' on the edit user page can be selected here!")."
	        <p>".__("Note: Be careful in assigning user rights to anonymous accounts!"); ?></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>

        <tr>
            <td colspan='2'><p class='header2'><?php echo __('Login settings (Delegation of password checking to external module):')."</p><p>".__('These login settings determine whether the login password checking is delegated to some external module, and if so, how this is configured.');?></p></td>
        </tr>

        <tr>
	        <td><label><?php echo __('Delegate password checking:');?></label></td>
	        <td align='left'>
<?php	            
    echo form_checkbox('LOGIN_ENABLE_DELEGATED_LOGIN','LOGIN_ENABLE_DELEGATED_LOGIN',$siteconfig->getConfigSetting("LOGIN_ENABLE_DELEGATED_LOGIN")== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Check this box to delegate password checking to external modules.');?></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>
        <tr>
            <td><?php echo __('Password checking module');?></td>
            <td>
<?php              
            //[DR 2008.09.03] While I'm writing this, everything that's needed to allow more than one delegate at a time is in place, except for this piece of interface in which you can only select one delegate at a time... "LOGIN_DELEGATES" can be a comma separate list of module names
            $options = array(''=>'','hardcoded'=>__('Test delegate'),'ldap'=>__('LDAP Password checking'),'pam'=>__('PAM Password checking').'. '.__('Uses php_pam_auth module'),'pwauth'=>__('pwauth Password checking').'. '.__('Directly from /usr/bin/pwauth'));
            echo form_dropdown('LOGIN_DELEGATES', $options,$siteconfig->getConfigSetting("LOGIN_DELEGATES"));
?>
            </td>                
        </tr>
	    <tr>
	        <td align='left' colspan='2'>
	        <p><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Select the module to which the password checking is to be delegated. Be sure to also configure that login module properly, before you turn off the internal login modules!')."
	        <p>".__("Note: check 'Disable internal login' to disallow access to any account not verified using the above module.");?></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>

        <tr>
            <td colspan='2'><p class='header2'><?php echo __('Login settings (Special settings):');?></p><p></p></td>
        </tr>

        <tr>
	        <td><label><?php echo __('Create missing users:');?></label></td>
	        <td align='left'>
<?php	            
    echo form_checkbox('LOGIN_CREATE_MISSING_USER','LOGIN_CREATE_MISSING_USER',$siteconfig->getConfigSetting("LOGIN_CREATE_MISSING_USER")== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __("Check this box to force the system to create users that are logged in using an external account/password, but do not have an internal Aigaion user account yet. Note that this setting only has an effect when 'delegated password checking' or one of the external login modules have been enabled.");?>
	        </td>
	    </tr>
	    
        <tr>
	        <td><label><?php echo __('Disable internal login:');?></label></td>
	        <td align='left'>
<?php	            
    echo form_checkbox('LOGIN_DISABLE_INTERNAL_LOGIN','LOGIN_DISABLE_INTERNAL_LOGIN',$siteconfig->getConfigSetting("LOGIN_DISABLE_INTERNAL_LOGIN")== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Check this box to disable internal login facilities. If internal login is disabled, users can only login using one of the delegated password checking modules such as IMAP or LDAP or one of the external login modules such as the httpauth login.');?> 
	        </td>
	    </tr>

        <tr>
            <td colspan='2'><p class='header2'><?php echo __('Login settings (LDAP):')."</p>".__("If you use LDAP authentication, you should set the LDAP server and the base DN. (e.g. server: ldap.aigaion.de, base dn: dc=dev,dc=aigaion,dc=nl) (That's just an example! We don't really have an LDAP server at Aigaion.de!)");?>.
            </td>
        </tr>
	    
        <tr>
            <td colspan='2'>
                <?php echo "<b>".__('Note').":</b> ".sprintf(__('If you want to use the LDAP authentication, you need to have the LDAP modules of your PHP server activated. Explaining how to install that is well outside the scope of Aigaion documentation. See the LDAP documentation at %s for more information. Take special note of the dependencies of this module: for Windows you need e.g. libeay32.dll and ssleay32.dll and msvcr71.dll to be available somewhere...'), "<a href='http://www.php.net/' class='open_extern'>www.php.net</a>");?>
            </td>
        </tr>
	    <tr>    
	        <td><label><?php echo __('LDAP server:');?></label></td>
	        <td align='left'><input type='text' cols='100' size=50 name='LDAP_SERVER'	
<?php
             echo "value='".$siteconfig->getConfigSetting("LDAP_SERVER")."'>";
?>
	        </td>
        </tr>
        <tr>
            <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('The LDAP server (like: ldap.aigaion.de).');?></td>
	    </tr>
	    <tr>    
	        <td><label><?php echo __('LDAP base DN:');?></label></td>
	        <td align='left'><input type='text' cols='100' size=50 name='LDAP_BASE_DN'	
<?php
             echo "value='".$siteconfig->getConfigSetting("LDAP_BASE_DN")."'>";
?>
	        </td>
        </tr>
        <tr>
            <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('The base DN for loggin in to the LDAP server (like: dc=dev,dc=aigaion,dc=nl).');?></td>
	    </tr>

	    <tr>    
	        <td><label><?php echo __('Login domain:');?></label></td>
	        <td align='left'><input type='text' cols='100' size=50 name='LDAP_DOMAIN'	
<?php
             echo "value='".$siteconfig->getConfigSetting("LDAP_DOMAIN")."'>";
?>
	        </td>
        </tr>
        <tr>
            <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('The domain for logging in to the LDAP server (like: dev.aigaion.de).');?></td>
	    </tr>
         <tr>
	        <td><label><?php echo __("Server type is 'ActiveDirectory':");?></label></td>
	        <td align='left'>
<?php	            
    echo form_checkbox('LDAP_IS_ACTIVE_DIRECTORY','LDAP_IS_ACTIVE_DIRECTORY',$siteconfig->getConfigSetting("LDAP_IS_ACTIVE_DIRECTORY")!= "FALSE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Check this box if your LDAP server is an Active Directory server.');?>
	        </td>
	    </tr>


        <tr>
            <td colspan='2'><p class='header2'><?php echo __('Login settings (HTTP Authentication):');?></p>
			<?php echo __("'.htpasswd' is a module that uses the .htaccess and .htpasswd login system to determine the name of the logged user, instead of a login form.")."<br/><br/><b>".__('Note').":</b> ".__('If you select this, be sure that you have the httpauth correctly configured - otherwise you may have problems logging in and then you can also not turn the httpauth login module off without directly accessing the Aigaion database.');?></td>
        </tr>
        
        <tr>
	        <td><label><?php echo __('Use HTTPAUTH login modules:');?></label></td>
	        <td align='left'>
<?php	            
    echo form_checkbox('LOGIN_HTTPAUTH_ENABLE','LOGIN_HTTPAUTH_ENABLE',$siteconfig->getConfigSetting("LOGIN_HTTPAUTH_ENABLE")== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Check this box to enable the httpauth login module.');?></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>

        <tr>
	        <td><label><?php echo __('Add autocreated users to group:');?></label></td>
	        <td align='left'>
<?php	            
    echo form_input(array('name'=>'LOGIN_HTTPAUTH_GROUP','id'=>'LOGIN_HTTPAUTH_GROUP','value'=>$siteconfig->getConfigSetting("LOGIN_HTTPAUTH_GROUP")));
?>
            </td>
        </tr>

	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
			<?php echo __('If "Create missing users" is enabled, new users will automatically be added to this group. Use the group name!');?>
	        </td>
	    </tr>
	    
<!-- ATTACHMENT SETTINGS -->
        <tr>
            <td colspan='2'><hr><p class='header2'><?php echo __('Attachment settings:');?></p></td>
        </tr>
	    <tr>    
	        <td><label><?php echo __('Allowed extensions for attachments:');?></label></td>
	        <td align='left'><input type='text' cols='100' size=50  name='ALLOWED_ATTACHMENT_EXTENSIONS'	
<?php
             echo "value='".implode(",",$siteconfig->getConfigSetting("ALLOWED_ATTACHMENT_EXTENSIONS"))."'>";
?>
	        </td>
        </tr>
        <tr>
            <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('The list of allowed extensions for attachments. Attachments that do not have an extension from this list can not be uploaded.');?></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>
	    <tr>
	        <td><label><?php echo __('Allow all remote attachments:');?></label></td>
	        <td align='left'>
<?php
            echo form_checkbox('ALLOW_ALL_EXTERNAL_ATTACHMENTS','ALLOW_ALL_EXTERNAL_ATTACHMENTS',$siteconfig->getConfigSetting("ALLOW_ALL_EXTERNAL_ATTACHMENTS")== "TRUE");
?>
	        </td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __("Check this box if you want to allow all external attachment names, instead of just those ending in one of the 'allowed extensions' specified above. This may be useful because external attachments are often to sites such as portal.acm or doi, with link names ending in meaningless numbers instead of a proper file name. This only affects *remote* attachments.");?></td>
	    </tr>
	
	    <tr>
	        <td><label><?php echo __('The server is read only:');?></label></td>
	        <td align='left'>
<?php 
            echo form_checkbox('SERVER_NOT_WRITABLE','SERVER_NOT_WRITABLE',$siteconfig->getConfigSetting("SERVER_NOT_WRITABLE")== "TRUE");
?>
	        </td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Check this box if the server is read-only, i.e. if you cannot write files such as attachments to the server.');?></td>
	    </tr>

<!-- CUSTOM FIELDS SETTINGS -->
	    <tr>
	        <td colspan='2'><hr>
	          <p class='header2'><?php echo __('Custom fields settings:');?></p>
	          <p><?php echo __('Aigaion allows adding installation-specific custom fields to publications, authors and topics. You can create these custom fields here.');?></p>
<?php	            
    echo form_hidden('customfield_count', sizeof($customFieldsInfo))."\n";
?>
	        </td>
	    </tr>
          </td>
      </tr>
      <tr>
        <td colspan = '2' align='center'>
          <table>
            <tr>
              <td><p class='header2'><?php echo __('Type');?></p></td>
              <td><p class='header2'><?php echo __('Name');?></p></td>
              <td><p class='header2'><?php echo __('Order');?></p></td>
              <td><p class='header2'><?php echo __('Keep');?></p></td>
            </tr>
            <?php
            $count = 0;
            //generate type count arrays
            $authorTypeCount = $publicationTypeCount = $topicTypeCount = 0;
            $authorTypeCountArray = $publicationTypeCountArray = $topicTypeCountArray = array();
            $authorTypeCountArray[] = 0;
            $publicationTypeCountArray[] = 0;
            $topicTypeCountArray[] = 0;
            
            foreach ($customFieldsInfo as $customField)
            {
              if ($customField['type'] == 'author') {
                $authorTypeCount++;
                $authorTypeCountArray[] = $authorTypeCount;
              }
              else if ($customField['type'] == 'publication') {
                $publicationTypeCount++;
                $publicationTypeCountArray[] = $publicationTypeCount;
              }
              else if ($customField['type'] == 'topic') {
                $topicTypeCount++;
                $topicTypeCountArray[] = $topicTypeCount;
              }
            }
            $countArrays = array('author' => $authorTypeCountArray, 'publication' => $publicationTypeCountArray, 'topic' => $topicTypeCountArray);
            foreach ($customFieldsInfo as $customField)
            {
              ?>
            <tr>
              <td><?php 
                echo form_hidden('CUSTOM_FIELD_ID_'.$count, $customField['type_id'])."\n";
                echo form_hidden('CUSTOM_FIELD_TYPE_'.$count, $customField['type'])."\n";
                echo translateCustomFieldsType($customField['type']); ?></td>
              <td><?php echo form_input(array('name'=>'CUSTOM_FIELD_NAME_'.$count,'id'=>'CUSTOM_FIELD_NAME_'.$count,'value'=>$customField['name'], 'size'=>30)); ?></td>
              <td><?php echo form_dropdown('CUSTOM_FIELD_ORDER_'.$count,$countArrays[$customField['type']],$customField['order']); ?></td>
              <td><?php echo form_checkbox('CUSTOM_FIELD_KEEP_'.$count,'CUSTOM_FIELD_KEEP_'.$count,true)."\n"; ?></td>
            </tr>
              <?php
              $count++;
            }
            //show empty row for adding new custom fields
            ?>
            <tr>
              <td><?php 
                echo form_hidden('CUSTOM_FIELD_ID_'.$count, '')."\n";
                echo form_dropdown('CUSTOM_FIELD_TYPE_'.$count,array(''=>'','publication' => __('Publication'),'author' => __('Author'),'topic' => __('Topic')),''); ?></td>
              <td><?php echo form_input(array('name'=>'CUSTOM_FIELD_NAME_'.$count,'id'=>'CUSTOM_FIELD_NAME_'.$count,'value'=>'', 'size'=>30)); ?></td>
              <td><?php echo form_dropdown('CUSTOM_FIELD_ORDER_'.$count,max($countArrays),''); ?></td>
              <td><?php echo form_checkbox('CUSTOM_FIELD_KEEP_'.$count,'CUSTOM_FIELD_KEEP_'.$count,false)."\n"; ?></td>
            </tr>
          </table>
        </td>
      </tr>


<!-- INPUT/OUTPUT SETTINGS -->
	    <tr>
	        <td colspan='2'><hr><p class='header2'><?php echo __('In- and output settings:');?></p></td>
	    </tr>
	    <tr>
	        <td valign='top'><label for='BIBTEX_STRINGS_IN'><?php echo __('BibTeX strings:');?></label></td>
	        <td><textarea name='BIBTEX_STRINGS_IN' wrap='virtual' cols='50' rows='10'><?php echo $siteconfig->getConfigSetting("BIBTEX_STRINGS_IN"); ?></textarea></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo sprintf(__('BibTeX allows definition of strings. Strings that are defined here are converted when importing BibTeX. The correct syntax for strings is: %s'), '@string {AIG = "Aigaion bibliography System"}');?><br/><br/></td>
	    </tr>
	    <tr>
	        <td><label><?php echo __('Convert BibTeX to UTF8 upon input');?></label></td>
	        <td align='left'>
	        <?php
            echo form_checkbox('CONVERT_BIBTEX_TO_UTF8','CONVERT_BIBTEX_TO_UTF8',$siteconfig->getConfigSetting("CONVERT_BIBTEX_TO_UTF8")!= "FALSE");
          ?>
        </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Turn this off if you want special BibTeX character codes to be stored as such in the database, without converting them into utf8. This is useful if you find that you have a lot of BibTeX that is converted wrongly.');?></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>	    
	    
<!-- DISPLAY SETTINGS -->
	    <tr>
	        <td colspan='2'><hr><p class='header2'><?php echo __('Some display settings:');?></p></td>
	    </tr>

        <tr>
	        <td><label for='WINDOW_TITLE'><?php echo __('Title of the site:');?></label></td>
	        <td align='left'><input type='text' cols='60' size=50 name='WINDOW_TITLE' 
<?php
	        echo "value='".$siteconfig->getConfigSetting("WINDOW_TITLE")."'>";
?>
	        </td>

        <tr>
	        <td><label><?php echo __('Display publications on single-topic page:');?></label></td>
	        <td align='left'>
<?php
            echo form_checkbox('ALWAYS_INCLUDE_PAPERS_FOR_TOPIC','ALWAYS_INCLUDE_PAPERS_FOR_TOPIC',$siteconfig->getConfigSetting("ALWAYS_INCLUDE_PAPERS_FOR_TOPIC")== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Checking this box means that the full list of publications for a topic is included below the topic description, to speed up browsing for papers. Turning this on might however slow down the loading of the topic pages.');?></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>
	
	    <tr>
	        <td><label><?php echo __('Merge crossreferenced publications in single publication view:');?></label></td>
	        <td align='left'>
<?php
            echo form_checkbox('PUBLICATION_XREF_MERGE','PUBLICATION_XREF_MERGE',$siteconfig->getConfigSetting("PUBLICATION_XREF_MERGE")== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Check to merge cross-referenced publications on a single publication page view.');?></td>
	    </tr>
	
	    <tr>
	        <td><label><?php echo __('Use TinyMCE note editor:');?></label></td>
	        <td align='left'>
<?php
            echo form_checkbox('ENABLE_TINYMCE','ENABLE_TINYMCE',$siteconfig->getConfigSetting("ENABLE_TINYMCE")== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Check to enable the Tiny-MCE editor for editing notes.');?></td>
	    </tr>	    
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>

<?php
#use uploaded logo?
$checked = "";
if ($siteconfig->getConfigSetting("USE_UPLOADED_LOGO")=="TRUE")
    $checked = "CHECKED";
?>
<!--
        <TR><TD>Use custom logo</TD>
	        <td align='left'>
<?php	            
    echo form_checkbox('USE_UPLOADED_LOGO','USE_UPLOADED_LOGO',$siteconfig->getConfigSetting("USE_UPLOADED_LOGO")== "TRUE");
?>
            </td>
        </TR>
        <tr><td align=left colspan=2><img border=0 class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>If checked, you can 
            specify a custom logo (below) to be used on the front page.</td></tr>
        <tr><td colspan=2>Current logo:</td></tr>
        <tr><td colspan=2>
<?php
    if (file_exists(AIGAION_ROOT_DIR.'/custom_logo.jpg')) {
        echo '<img border=0 src="'.AIGAION_ROOT_URL.'/custom_logo.jpg">';
    } else {
        echo '&lt;no logo uploaded&gt;';
    }
?>
            </td>
        </tr>
        <tr><td><label for='CUSTOM_LOGO'>Select a new logo file...</label></td>
            <td><input type='file' name='new_logo' size='30'/></td>
        </tr>

-->
<!-- USER PREFERENCE DEFAULTS -->
	    <tr>
	        <td colspan='2'><hr><p class='header2'><?php echo __('Defaults for user preferences:');?></p></td>
	    </tr>
        <tr><td align=left colspan=2><img class='icon' border=0 src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
        <?php echo __('Several user preferences can be given a default value here, to be overridden as wished by users who can change their profile.');?></td>
        </tr>
<?php
$theme_array = array();
$availableThemes = getThemes();
foreach ($availableThemes as $theme)
{
  $theme_array[$theme] = $theme;
}
$lang_array = array();
global $AIGAION_SUPPORTED_LANGUAGES;
foreach ($AIGAION_SUPPORTED_LANGUAGES as $lang)
{
  $lang_array[$lang] = $this->userlanguage->getLanguageName($lang);
}
echo "
        <tr>
        <td>".__('Default theme')."</td>
        <td>
        ".form_dropdown('DEFAULTPREF_THEME',
                        $theme_array,
                        $siteconfig->getConfigSetting("DEFAULTPREF_THEME"))."
        </td>
        </tr>

        <td>".__('Default language')."</td>
        <td>
        ".form_dropdown('DEFAULTPREF_LANGUAGE',
                        $lang_array,
                        $siteconfig->getConfigSetting("DEFAULTPREF_LANGUAGE"))."
        </td>
        </tr>

        <tr>
        <td>".__('Default publication summary style')."</td>
        <td>
        ".form_dropdown('DEFAULTPREF_SUMMARYSTYLE',
                        array('author'=>__('Author first'),'title'=>__('Title first')),
                        $siteconfig->getConfigSetting("DEFAULTPREF_SUMMARYSTYLE"))."
        </td>
        </tr>
        <tr>
        <td>".__('Default author display style')."</td>
        <td>
        ".form_dropdown('DEFAULTPREF_AUTHORDISPLAYSTYLE',
                        array('fvl'=>__('First [von] Last'),'vlf'=>__('[von] Last, First'),'vl'=>__('[von] Last')),
                        $siteconfig->getConfigSetting("DEFAULTPREF_AUTHORDISPLAYSTYLE"))."
        </td>
        </tr>
        <tr>
        <td>".__('Default number of publications per page')."</td>
        <td>
        ".form_dropdown('DEFAULTPREF_LISTSTYLE',
                        array('0'=>__("All"), "10"=>"10", '15'=>"15", '20'=>"20", '25'=>"25", '50'=>"50", '100'=>"100"),
                        $siteconfig->getConfigSetting("DEFAULTPREF_LISTSTYLE"))."
        </td>
        </tr>
        <tr>
        <td>".__("'Similar author' check")."</td>
        <td>
        ".form_dropdown('DEFAULTPREF_SIMILAR_AUTHOR_TEST',
                        array('il'=>__("Last names, then initials"), "c"=>__("Full name")),
                        $siteconfig->getConfigSetting("DEFAULTPREF_SIMILAR_AUTHOR_TEST"))."
        </td>
        </tr>
        <tr>
	        <td align='left' colspan='2'><img class='icon' src='".getIconUrl("small_arrow.gif")."'>
	        ".__("Select the method for checking whether two author names are counted as 'similar'.")."
	        </td>
	      </tr>
        ";
?>

<!-- DEFAULT ACCESS LEVELS -->
        <TR><TD colspan=2>
        <hr><p class=header2><?php echo __('Default access levels:');?></p>
        </TD></TR>
        <tr><td align=left colspan=2><img border=0 src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
        <?php echo __("Specify here the default access levels for different types of objects. A 'Default publication read level' of 'public' means, for example, that new publications are publicly accessible by default");?></td>
        </tr>
        <tr>
            <td><?php echo __('Type:');?></td>
            <td><?php echo __('Default access level:');?></td>
        </tr>
<?php
    $types = array('ATT_DEFAULT_READ'=>__('Default attachment read level'),
          'ATT_DEFAULT_EDIT'=>__('Default attachment edit level'),
          'PUB_DEFAULT_READ'=>__('Default publication read level'),
          'PUB_DEFAULT_EDIT'=>__('Default publication edit level'),
          'NOTE_DEFAULT_READ'=>__('Default note read level'),
          'NOTE_DEFAULT_EDIT'=>__('Default note edit level'),
          'TOPIC_DEFAULT_READ'=>__('Default topic read level'),
          'TOPIC_DEFAULT_EDIT'=>__('Default topic edit level'));
    
    $levels = array('private'=>__('private'),'intern'=>__('intern'),'public'=>__('public'));
    
    foreach ($types as $type=>$desc) {
?>        
        <tr>
        <td><label><?php echo $desc; ?></label></td>
        <td align=left>
<?php
        $level = 'intern';
        if ($siteconfig->getConfigSetting($type)!='') {
            $level = $siteconfig->getConfigSetting($type);
        }
        
        echo form_dropdown($type, $levels, $level);
?>
	    </td>
        </tr>
        </tr>
<?php
    }
?>

      <tr>
            <td colspan='2'><hr><p class='header2'><?php echo __('Embedding options:');?></p></td>
	    </tr>

	    <tr>
	        <td><label for='EMBEDDING_SHAREDDOMAIN'><?php echo __('Shared domain for embedding:');?></label></td>
	        <td align='left'><input type='text' cols='60' size=50 name='EMBEDDING_SHAREDDOMAIN' value='<?php echo $siteconfig->getConfigSetting("EMBEDDING_SHAREDDOMAIN"); ?>'></td>
	    </tr>

      <tr>
            <td colspan='2'><hr><p class='header2'><?php echo __('Logintegration options:');?></p></td>
	    </tr>

	    <tr>
	        <td><label for='LOGINTEGRATION_SECRETWORD'><?php echo __('Shared secret phrase for integrated login:');?></label></td>
	        <td align='left'><input type='password' cols='60' size=50 name='LOGINTEGRATION_SECRETWORD' value='<?php echo $siteconfig->getConfigSetting("LOGINTEGRATION_SECRETWORD"); ?>'></td>
	    </tr>
	    
<!-- EXTERNAL LOGIN MODULES -->

<!--
external login modules are disabled. The password checker of LDAP is moved to the delegate section, and httauth is too much trouble. It is not secure, doesn't work well, and if it is ever re-enabled it will be as mode-3 login module

        <tr>
            <td colspan='2'><hr><p class='header2'>Login modules:</p></td>
        </tr>

        <tr>
            <td>Use the following login module:</td>
            <td>
<?php              
            $options = array('Aigaion'=>__('Aigaion login module'),
                             'Httpauth'=>__('.htpasswd file'),
                             'LDAP'=>__('LDAP based authentication'));
            $selected = $siteconfig->getConfigSetting("EXTERNAL_LOGIN_MODULE");
            if ($selected == '') {
                $selected = 'Aigaion';
            }
            echo form_dropdown('EXTERNAL_LOGIN_MODULE', $options,$selected);
?>
            </td>                
        </tr>
	    <tr>
	        <td align='left' colspan='2'>
	        <p><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        Select the login module to be used. 
	        <br/>- 'Aigaion' is the default built-in login system.
	        <br/>- '.htpasswd' is a module that uses the .htaccess and .htpasswd login system to determine 
	        the name of the logged user, instead of a login form.
	        <br/>- 'LDAP' uses a connection to a LDAP server to verify the credentials filled in in the login form.
	        <br/><br/><b>Note:</b> If you select a login module different from 'Aigaion', be sure to have that 
	        module correctly configured below in this form - otherwise you may have problems logging in and then you can also 
	        not turn the external login module off without directly accessing the Aigaion database :)</p></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>

-->

	    
	    <tr>
	        <td align='left' colspan='2'><hr></td>
	    </tr>
        <tr><td>
<?php
    echo form_submit('submit',__('Store new settings'));
?>
        </td>
        </tr>
    </table>
<?php
echo form_close();
echo form_open('');
echo form_submit('cancel',__('Cancel'));
echo form_close();
?>
</div>