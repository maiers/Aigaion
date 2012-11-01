<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

<!-- ALL LOGIN SETTINGS -->
<?php 
echo form_hidden('configformname','login');
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
            <td colspan='2'><p class='header2'><?php echo __('Login settings (LDAP):')."</p>".__("If you use LDAP authentication, you should set the LDAP server and the base DN. (e.g. server: ldap.aigaion.nl, base dn: dc=dev,dc=aigaion,dc=nl) (That's just an example! We don't really have an LDAP server at Aigaion.nl!)");?>.
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
	        <?php echo __('The LDAP server (like: ldap.aigaion.nl).');?></td>
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
	        <?php echo __('The domain for logging in to the LDAP server (like: dev.aigaion.nl).');?></td>
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
	    