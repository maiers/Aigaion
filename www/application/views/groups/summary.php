<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="group-summary">
<?php
/**
views/groups/summary

Shows a summary of a group: edit link, name, delete link, etc

Parameters:
    $group=>the group object that is to be summarized

access rights: we presume that this view is not loaded when the user doesn't have the user_edit_all rights.

*/
    echo '['.anchor('groups/edit/'.$group->group_id,__('edit'))."]&nbsp;"
    .'['.anchor('groups/delete/'.$group->group_id,__('delete'))."]&nbsp;";
    
    $userlogin = getUserLogin();
    if ($userlogin->hasRights('topic_subscription')) {
        echo '['.anchor('groups/topicreview/'.$group->group_id,__('topic subscription'))."]&nbsp;";
    }
    echo $group->name;

?>
</div>