<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="user-summary">
<?php
/**
views/users/summary

Shows a summary of a user: edit link, name, delete link, etc

Parameters:
    $user=>the User object that is to be summarized
    
access rights: we presume that this view is not loaded when the user doesn't have the read rights.
as for the edit rights: they determine which edit links are shown.
    
*/
$userlogin  = getUserLogin();
    if ($userlogin->hasRights('user_edit_all') || ($userlogin->hasRights('user_edit_all')&&$user->user_id==$userlogin->userId()))
    {
        echo '['.anchor('users/edit/'.$user->user_id,__('edit'))."]&nbsp;[";
        echo anchor('users/delete/'.$user->user_id,__('delete'))."]&nbsp;";
        if ($userlogin->hasRights('topic_subscription')) {
            echo '['.anchor('users/topicreview/'.$user->user_id,__('topic subscription'))."]&nbsp;";
        }
    }
    echo $user->login." (".$user->firstname." ".$user->betweenname." ".$user->surname.")";
    if ($user->type=='anon') {
        echo ' ('.__('guest user').')';
    } else if ($user->type=='external') {
        echo ' ('.__('externally managed account').')';
    } else if ($user->password_invalidated=='TRUE') {
        echo ' ('.__('disabled account').')';
    }
    if ($userlogin->hasRights('user_edit_all') || ($userlogin->hasRights('user_edit_all')&&$user->user_id==$userlogin->userId()))
    {
        if($user->type != 'anon' && $user->type !='external')
          echo '&nbsp;['.anchor('users/setpassword/'.$user->user_id,__('set password'))."]&nbsp;";
    }


?>
</div>