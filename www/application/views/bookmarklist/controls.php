<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/bookmarklist/controls

Shows the controls for using the bookmarklist

access rights: we presume that this view is not loaded when the user doesn't have the bookmarklist rights.
Some controls may be shown only dependent on other rights, though.

*/
$this->load->helper('form');
$userlogin = getUserLogin();
?>
<p class='header'><?php echo __('Bookmark list controls');?></p>

<?php     
//add to topic only if you are allowed to edit publications. Note that
//for some publicatibns in the bookmarklist the operation might still fail if the access levels are wrong.
//In that case the user will be notified after the (failed) attempts
if ($userlogin->hasRights('publication_edit')) {
    echo form_open('bookmarklist/addtotopic');
    $user = $this->user_db->getByID($userlogin->userId());
    $config = array('onlyIfUserSubscribed'=>True,
                    'includeGroupSubscriptions'=>True,
                    'user'=>$user);
    echo $this->load->view('topics/optiontree',
                       array('topics'   => $this->topic_db->getByID(1,$config),
                            'showroot'  => False,
                            'depth'     => -1,
                            'selected'  => -1,
                            'dropdownname' => 'topic_id',
                            'header'    => __('Add bookmarked to topic...')
                            ),  
                       true)."\n";
    echo form_submit(array('name'=>'addtotopic','title'=>__('Add all bookmarked publications to the selected topic')),__('Add all to topic'));
    echo form_close();
    echo "<br/>";
    
    echo form_open('bookmarklist/removefromtopic');
    $user = $this->user_db->getByID($userlogin->userId());
    $config = array('onlyIfUserSubscribed'=>True,
                    'includeGroupSubscriptions'=>True,
                    'user'=>$user);
    echo $this->load->view('topics/optiontree',
                       array('topics'   => $this->topic_db->getByID(1,$config),
                            'showroot'  => False,
                            'depth'     => -1,
                            'selected'  => -1,
                            'dropdownname' => 'topic_id',
                            'header'    => __('Remove bookmarked from topic...')
                            ),  
                       true)."\n";
    echo form_submit(array('name'=>'removefromtopic','title'=>__('Remove all bookmarked publications from the selected topic')),__('Remove all from topic'));
    echo form_close();
    echo "<br/>";
    
    if ($userlogin->hasRights('topic_edit')) {
        echo form_open('bookmarklist/maketopic');
        echo form_submit(array('name'=>'maketopic','title'=>__('Make a new topic from the bookmarked publications')),__('Make into new topic'));
        echo form_close();
    }

}
?>
<br/>
<?php
    echo form_open('bookmarklist/clear');
    echo form_submit(array('name'=>'clear','title'=>__('Clear the bookmarklist')),__('Clear bookmarklist'));
    echo form_close();
?>
<br/>
<?php
if ($userlogin->hasRights('publication_edit')) {
    echo form_open('bookmarklist/deleteall');
    echo form_submit(array('name'=>'deleteall','title'=>__('Delete all publications on the bookmarklist from the database')),__('Delete all bookmarked publications'));
    echo form_close();
}
?>
<br/>
<?php

if ($userlogin->hasRights('publication_edit')) {
    echo __('Set read access level for all bookmarked publications:');
    echo form_open('bookmarklist/setpubaccesslevel');
    echo form_dropdown('accesslevel',array('public'=>__('public'),'intern'=>__('intern'),'private'=>__('private')),'intern');
    echo form_submit(array('name'=>'setpubaccesslevel','title'=>__('Set the read  access levels for all publications on the bookmarklist')),__('Set publication access level'));
    echo form_close();
}
?>
<br/>
<?php
if ($userlogin->hasRights('publication_edit')) {
    echo __('Set read access level for all attachments of bookmarked publications:');
    echo form_open('bookmarklist/setattaccesslevel');
    echo form_dropdown('accesslevel',array('public'=>__('public'),'intern'=>__('intern'),'private'=>__('private')),'intern');
    echo form_submit(array('name'=>'setattaccesslevel','title'=>__('Set the read access levels for all attachments of publications on the bookmarklist')),__('Set attachment access level'));
    echo form_close();
}
?>

<br/>
<?php

if ($userlogin->hasRights('publication_edit')) {
    echo __('Set edit access level for all bookmarked publications:');
    echo form_open('bookmarklist/seteditpubaccesslevel');
    echo form_dropdown('accesslevel',array('public'=>__('public'),'intern'=>__('intern'),'private'=>__('private')),'intern');
    echo form_submit(array('name'=>'seteditpubaccesslevel','title'=>__('Set the edit  access levels for all publications on the bookmarklist')),__('Set publication edit access level'));
    echo form_close();
}
?>
<br/>
<?php
if ($userlogin->hasRights('publication_edit')) {
    echo __('Set edit access level for all attachments of bookmarked publications:');
    echo form_open('bookmarklist/seteditattaccesslevel');
    echo form_dropdown('accesslevel',array('public'=>__('public'),'intern'=>__('intern'),'private'=>__('private')),'intern');
    echo form_submit(array('name'=>'seteditattaccesslevel','title'=>__('Set the edit access levels for all attachments of publications on the bookmarklist')),__('Set attachment edit access level'));
    echo form_close();
}
?>
<br/>
