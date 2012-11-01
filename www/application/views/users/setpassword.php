<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
echo "<div class='editform'>";
echo form_open('users/setpassword/'.$user->user_id.'/commit');
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','setpassword');
echo "
    <table width='100%'>

        <tr><td colspan='2'>
        <hr><b>".__('Account settings').":</b> ".
        sprintf(__("Change password for user %s"),$user->login)."
        <hr>
        </td></tr>";

if ($user->type!='external' && $user->type!='anon')
{ //only password setting on normal accounts
    echo "
            <tr>
            <td>".__('Your current password (for verifying that you are allowed to change passwords)')."</td>
            <td>"
            .form_password(array('name'=>'password_old', /////
                                                     //one VERY annoying firefox feature is to 
                                                     //always autocomplete password fields. Even 
                                                     //ignoring the autocomplete=off attribute. So now
                                                     //we need to find another way to clean the field...
                                                     //see e.g.:
                                                     //http://www.verysimple.com/blog/2007/03/07/forcing-firefox-to-obey-autocompleteoff-for-password-fields/
                                 'id'=>'password_old',                       
                                 'size'=>'10',
                                 'value'=>'',
                                 'autocomplete'=>'off'))."
            </td>
            </tr>
            <tr>
            <td>".__('New password')." (".__('leave blank for no change').")</td>
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
                window.setTimeout('$(\"password_old\").value = \"\";', 100);
                window.setTimeout('$(\"password\").value = \"\";', 100);
                window.setTimeout('$(\"password_check\").value = \"\";', 100);
                </script>                          
            </td>
            </tr>";
}
else 
{
    
    //anon or external: give remark on pwd changing
    echo form_hidden('password_old','');
    echo form_hidden('password','');
    echo form_hidden('password_check','');
    if ($user->type=='anon') {
        echo "
            <tr>
            <td>".__('Password').":</td>
            <td class='message'>
            ".__('Cannot change password on anonymous accounts; they do not have a password.')."
            </td>
            </tr>
            <tr>";
        
    } else if ($user->type=='external'){
        echo "
            <tr>
            <td>".__('Password').":</td>
            <td class='message'>
            ".__('Cannot change password on this account. It has a password which is externally managed by some other system.')."
            </td>
            </tr>
            <tr>";
    }  
}

echo "
        <tr><td>";
echo form_submit('submit',__('Change password'));
echo "
        </td>
        </tr>
        </table>";
echo form_close();
echo form_open('');
echo form_submit('cancel',__('Cancel'));
echo form_close();
echo "</div>";
