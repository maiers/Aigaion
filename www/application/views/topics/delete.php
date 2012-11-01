<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/topics/delete

Shows the confirm form for deleting a topic.

Parameters:
    $topic=>the topic object that is to be deleted
*/
$this->load->helper('form');
echo "<div class='confirmform'>";
echo form_open('topics/delete/'.$topic->topic_id.'/commit');
echo sprintf(__('Are you sure that you want to delete topic "%s"?'),$topic->name)."<p>\n";
echo form_submit('confirm',__('Confirm'));
echo form_close();
echo form_open('');
echo form_submit('cancel',__('Cancel'));
echo form_close();
echo "</div>";

?>