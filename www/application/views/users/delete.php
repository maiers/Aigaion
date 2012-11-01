<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/users/delete

Shows the confirm form for deleting a user.

Parameters:
    $user=>the User object that is to be deleted
we assume that this view is not loaded if you don't have the appropriate read and edit rights
*/
$this->load->helper('form');
echo "<div class='confirmform'>";
echo form_open('users/delete/'.$user->user_id.'/commit');
echo sprintf(__('Are you sure that you want to delete user "%s"?'),$user->login)."<p>\n";
echo form_submit('confirm',__('Confirm'));
echo form_close();
echo form_open('');
echo form_submit('cancel',__('Cancel'));
echo form_close();
echo "</div>";

?>