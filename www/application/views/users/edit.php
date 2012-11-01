<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/users/edit

Shows a form for editing or adding users.

Parameters:
    $user=>the User object to be edited
    
If $user is null, the edit for will be restyled as an 'add new user' form
if $user is not null, but $action == 'add', the edit form will be restyled as a
pre filled 'add new user' form

we assume that this view is not loaded if you don't have the appropriate read and edit rights

the rights-checkboxes and group assignment however are still visible only contingent on the appropriate rights
*/
$this->load->helper('form');
echo "<div class='editform'>";
echo form_open('users/commit');
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','user');
$isAddForm = False;
$userlogin  = getUserLogin();

if (!isset($user)||($user==null)||(isset($action)&&$action=='add')) {
    $isAddForm = True;
    echo form_hidden('action','add');
    if (!isset($action)||$action!='add')
        $user = new User;
} else {
    echo form_hidden('action','edit');
    //hidden fields which should be remembered for commit, but which are not modifyable:
    echo form_hidden('user_id',$user->user_id);
    echo form_hidden('lastreviewedtopic',$user->lastreviewedtopic);
}

if ($isAddForm) {
    echo "<p class='header2'>".__('Create a new user')."</p>";
} else {
    echo "<p class='header2'>".__('Edit user preferences')."</p>";
}

//validation feedback
echo $this->form_validation->error_string();

echo "
    <table width='100%'>

        <tr><td colspan='2'>
        <hr><b>".__('Account settings').":</b> (".__("'Account settings' is the only block of settings that is <i>mandatory</i>").")<hr>
        </td></tr>
        <tr>
        <td>".__('Login')." </td>
        <td>";

// DR 2008.08.29: no-one can change login names anymore in edit forms......
if ($isAddForm) {
    echo form_input(array('name'=>'login',
                   'size'=>'10',
                   'value'=>$user->login,
                   'AUTOCOMPLETE'=>'off'));
} else {
    echo form_hidden('login',$user->login);
    echo form_input(array('name'=>'login_disabled',
                   'size'=>'10',
                   'value'=>$user->login,
                   'disabled' => 'disabled',
                   'AUTOCOMPLETE'=>'off'));
}
        
        
echo "  </td>
        </tr>
        <tr>
	        <td align='left' colspan='2'><img class='icon' src='".getIconUrl("small_arrow.gif")."'>
	        ".__('Note: Login names, once assigned, cannot be changed.')."
	        </td>
	    </tr>";


echo form_hidden('password_invalidated',$user->password_invalidated);
if ($user->password_invalidated != 'TRUE') {
    
    //checkbox to disable account, with many warnings? always visible, for edit and add form
    if ($user->user_id != $userlogin->userId()) {
        //never disable own account
        echo "
            <tr>
            <td>".__('Disable account')."</td>
            <td>
         "
         .form_checkbox('disableaccount','disableaccount',false).
         "
            </td>
            </tr>
            <tr>
    	        <td align='left' colspan='2'><img class='icon' src='".getIconUrl("small_arrow.gif")."'>
    	        ".__('Note: when you disable this account, it can no longer be used to login, but the information associated to the account will remain in the database. You can re-enable the account in the future.')."
    	        </td>
    	    </tr>";
    }    
}
    
if ($isAddForm) //password fields only for add forms
{
    echo "
            <tr>
            <td>".__('Password')." (".__('leave blank for no change').")</td>
            <td>"
            .form_password(array('name'=>'password', /////
                                                     //one VERY annoying firefox feature is to 
                                                     //always autocomplete password fields. Even 
                                                     //ignoring the autocomplete=off attribute. So now
                                                     //we need to find another way to clean the field...
                                                     //see e.g.:
                                                     //http://www.verysimple.com/blog/2007/03/07/forcing-firefox-to-obey-autocompleteoff-for-password-fields/
                                 'id'=>'password',                       
                                 'size'=>'10',
                                 'value'=>'',
                                 'autocomplete'=>'off'))."
            </td>
            </tr>
            <tr>
            <td>".__('Re-type new password')."</td>
            <td>"
            .form_password(array('name'=>'password_check',
                              'size'=>'10',
                              'value'=>'',
                              'AUTOCOMPLETE'=>'off'))."
                <script language='JavaScript' type='text/javascript'>
                // this *brutally* clears a password field in firefox
                // compliments of verysimple.com, adapted to use the prototype framework
                // http://www.verysimple.com/blog/2007/03/07/forcing-firefox-to-obey-autocompleteoff-for-password-fields/
                window.setTimeout('$(\"password\").value = \"\";', 100);
                window.setTimeout('$(\"password_check\").value = \"\";', 100);
                </script>                          
            </td>
            </tr>";
} 
else
{
  echo form_hidden('password','');
  echo form_hidden('password_check','');
}


if ($userlogin->hasRights('user_edit_all')) {
    if ($user->user_id == $userlogin->userId()) {
        echo "
	    <tr>
	        <td align='left' colspan='2' class='message'>
	        ".__('It is impossible to change the type of your own account (normal, anon or external). This would lead to problems if you accidentally disable it.')."
	        </td>
	    </tr>";
    } else {
        echo "        
            <tr>
            <td>".__('Account type').":</td>
            <td>"
            .form_dropdown('type',
                            array('normal'=>__('Normal'),'anon'=>__('Anonymous'),'external'=>__('Managed by external module')),
                            $user->type)."
            </td>
            </tr>
    	    <tr>
    	        <td align='left' colspan='2'><img class='icon' src='".getIconUrl("small_arrow.gif")."'>
    	        ".__('Note: when you set this account to be anonymous, you should not forget to enable anonymous access to this database on the site configuration page!')."<br/>
    	        ".__('Note2: when you set this account to be externally managed, you must be certain that the external login module is working; otherwise this account can no longer login! If you are unsure, please read the documentation on external login modules.')."
    	        </td>
    	    </tr>";
    }
}

echo "        
        <tr><td colspan='2'>
        <hr><b>".__('Person details').":</b><hr>
        </td></tr>

        <tr>
        <td>".__('Initials')."</td>
        <td>"
        .form_input(array('name'=>'initials',
                          'size'=>'5',
                          'value'=>$user->initials))."
        </td>
        </tr>
        <tr>
        <td>".__('First name')."</td>
        <td>"
        .form_input(array('name'=>'firstname',
                          'size'=>'10',
                          'value'=>$user->firstname))."
        </td>
        </tr>
        <tr>
        <td>".__('Middle name')."</td>
        <td>"
        .form_input(array('name'=>'betweenname',
                          'size'=>'5',
                          'value'=>$user->betweenname))."
        </td>
        </tr>
        <tr>
        <td>".__('Surname')."</td>
        <td>"
        .form_input(array('name'=>'surname',
                          'size'=>'15',
                          'value'=>$user->surname))."
        </td>
        </tr>
        <tr>
        <td>".__('Abbreviation (about three characters)')."</td>
        <td>"
        .form_input(array('name'=>'abbreviation',
                          'size'=>'5',
                          'value'=>$user->abbreviation))."
        </td>
        </tr>
        <tr>
        <td>".__('Email address')."</td>
        <td>"
        .form_input(array('name'=>'email',
                          'size'=>'20',
                          'value'=>$user->email))."
        </td>
        </tr>
        ";
        



$theme_array = array();
$availableThemes = getThemes();
foreach ($availableThemes as $theme)
{
  $theme_array[$theme] = $theme;
}
$lang_array = array();
$lang_array['default'] = 'default ('.getConfigurationSetting('DEFAULTPREF_LANGUAGE').')';
global $AIGAION_SUPPORTED_LANGUAGES;
foreach ($AIGAION_SUPPORTED_LANGUAGES as $lang)
{
  $lang_array[$lang] = $this->userlanguage->getLanguageName($lang);
}
echo "
        
        <tr><td colspan='2'>
        <hr><b>".__('Display preferences').":</b><hr/>
        </td></tr>
        
        <tr>
        <td>".__('Theme')."</td>
        <td>
        ".form_dropdown('theme',
                        $theme_array,
                        $user->preferences["theme"])."
        </td>
        </tr>

        <td>".__('Language')."</td>
        <td>
        ".form_dropdown('language',
                        $lang_array,
                        $user->preferences["language"])."
        </td>
        </tr>

        <tr>
        <td>".__('Publication summary style')."</td>
        <td>
        ".form_dropdown('summarystyle',
                        array('default'=>'default ('.getConfigurationSetting('DEFAULTPREF_SUMMARYSTYLE').')','author'=>__('author first'),'title'=>__('title first')),
                        $user->preferences["summarystyle"])."
        </td>
        </tr>
        <tr>
        <td>".__('Author display style')."</td>
        <td>
        ".form_dropdown('authordisplaystyle',
                        array('default'=>'default ('.getConfigurationSetting('DEFAULTPREF_AUTHORDISPLAYSTYLE').')','fvl'=>__('First [von] Last'),'vlf'=>__('[von] Last, First'),'vl'=>__('[von] Last')),
                        $user->preferences["authordisplaystyle"])."
        </td>
        </tr>
        <tr>
        <td>".__('Number of publications per page')."</td>
        <td>
        ".form_dropdown('liststyle',
                        array(/* 'default'=>'default ('.getConfigurationSetting('DEFAULTPREF_LISTSTYLE').')',*/'0'=>__("All"), "10"=>"10", '15'=>"15", '20'=>"20", '25'=>"25", '50'=>"50", '100'=>"100"),
                        $user->preferences["liststyle"])."
        </td>
        </tr>
        <tr>
        <td>".__("'Similar author' check")."</td>
        <td>
        ".form_dropdown('similar_author_test',
                        array('default'=>__('Site default'),'il'=>__("Last names, then initials"), "c"=>__("Full name")),
                        $user->preferences["similar_author_test"])."
        </td>
        </tr>
        <tr>
	        <td align='left' colspan='2'><img class='icon' src='".getIconUrl("small_arrow.gif")."'>
	        ".__('Select the method for checking whether two author names are counted as "similar".')."
	        </td>
	      </tr>
        <tr>
        <td>".__('Open attachments in new browser window')."</td>
        <td>
        ". //to do this, we need to rewrite many things, among which: the database should allow for 'default' beside TRUE and FALSE; the getfrompost, add and update in user_db should allow for a dropdown valued 'TRUE', 'FALSE' or 'default', ...
        form_checkbox('newwindowforatt','newwindowforatt',$user->preferences['newwindowforatt']=="TRUE")."
        </td>
        </tr>


        <tr>
        <td>".__('Open export data in browser')."</td>
        <td>
        ".form_checkbox('exportinbrowser','exportinbrowser',$user->preferences['exportinbrowser']=="TRUE")."
        </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='".getIconUrl("small_arrow.gif")."'>
	        ".__('Check this box to force the system to show export data such as BibTeX or RIS directly in a browser window instead of downloading it as a file.')."
	        </td>
	    </tr>

        <tr>
        <td>".__('Export BibTeX as UTF8')."</td>
        <td>
        ".form_checkbox('utf8bibtex','utf8bibtex',$user->preferences['utf8bibtex']=="TRUE")."
        </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='".getIconUrl("small_arrow.gif")."'>
	        ".sprintf(__('Check this box if you want all BibTeX output to be in UTF8, i.e. when you do NOT want Aigaion to convert special characters to BibTeX codes such as %s'),"{\\'e}")."
	        </td>
	    </tr>
";

if ($userlogin->hasRights('user_edit_all')) {
    echo "   
        <tr><td colspan='2'>
        <hr><b>".__('Groups').":</b><hr>
        ".__('The groups to which this user belongs. When you add this user to a group that it was previously not a member of, all rights associated with that group will be appended to the user rights of this user upon commit.')."
        </td></tr>
        ";
        
        //list all groups as checkboxes
        foreach ($this->group_db->getAllGroups() as $group) {
            $checked = FALSE;
            if (in_array($group->group_id,$user->group_ids)) $checked=TRUE;
            echo "<tr><td>".$group->name."</td><td>".form_checkbox('group_'.$group->group_id, 'group_'.$group->group_id, $checked)."</td></tr>";
        }
}

        
if ($userlogin->hasRights('user_assign_rights')) {
    $rightsprofiles = $this->rightsprofile_db->getAllRightsprofiles();
        echo "   
    
            <tr><td colspan='2'>
            <hr><b>".__('User rights').":</b><hr>
            </td></tr>
            
            <tr>
            <td>".__('Check all rights').":</td>
            <td>
            ";
    echo $this->ajax->button_to_function(__('Check all'), "selectAllRights();");
    
    echo "
            </td>
            </tr>

            <tr>
            <td>".__('Uncheck all rights').":</td>
            <td>
            ";
    echo $this->ajax->button_to_function(__('Uncheck all'), "deselectAllRights();");
    
    echo "
            </td>
            </tr>

            <tr>
            <td>".__('Check all rights from').":</td>
            <td>
            ";
    $options = array(''=>'');
    foreach ($rightsprofiles as $profile) {
        $options[$profile->name] = $profile->name;
    }
    echo form_dropdown('checkrightsprofile', $options,null,"onchange='selectProfile();' id='checkrightsprofile'");
    
    echo "
            </td>
            </tr>
            
            <tr>
            <td>".__('Uncheck all rights from').":</td>
            <td>
            ";
            
    echo form_dropdown('uncheckrightsprofile', $options,null,"onchange='deselectProfile();'  id='uncheckrightsprofile'");
    
    echo "
            </td>
            </tr>
            
            <tr>
            <td>".__('Restore old state').":</td>
            <td>
            ";
    echo $this->ajax->button_to_function(__('Restore'), "restoreRights();");
    
    echo "
            </td>
            </tr>
            
            <tr><td colspan=2>
            <hr>
            </td></tr>
            ";
            
            //list all userrights as checkboxes
            foreach (getAvailableRights() as $right=>$description) {
                $checked = FALSE;
                if (in_array($right,$user->assignedrights)) {
                    $checked=TRUE;
                }
                $classes = 'rightbox';
                foreach ($rightsprofiles as $profile) {
                    if (in_array($right,$profile->rights)) {
                        $classes .= ' '.$profile->name;
                    }
                }
                if ($checked) {
                    $classes .= ' rightbox_on';
                } else {
                    $classes .= ' rightbox_off';
                }
                $data=array('name'=>$right,'id'=>$right,'value'=>$right,'checked'=>$checked,'class'=>$classes);
                echo "<tr><td>".form_checkbox($data).$right."</td><td>".$description."</td></tr>";
            }
}

echo "

        <tr>
        <td colspan=2><hr></td>
        </tr>
        <tr><td>";
if ($isAddForm) {
    echo form_submit('submit',__('Add'));
} else {
    echo form_submit('submit',__('Store new settings'));
}
echo "
        </td>
        </tr>
    </table>
     ";
echo form_close();
if ($userlogin->hasRights('user_edit_all')) {
    echo form_open('users/manage');
} else {
    echo form_open('');
}
echo form_submit('cancel',__('Cancel'));
echo form_close();
echo "</div>";

?>

