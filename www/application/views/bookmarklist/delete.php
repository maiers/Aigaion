<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/bookmarklist/delete

Shows the confirm form for deleting all publications on the bookmarklist

Parameters:

*/
$this->load->helper('form');
echo "<div class='confirmform'>";
echo form_open('bookmarklist/deleteall/commit');
echo __('Are you sure that you want to delete all publications on the bookmarklist from your database?')."<p>\n";
echo form_submit('confirm',__('Confirm'));
echo form_close();
echo form_open('bookmarklist');
echo form_submit('cancel',__('Cancel'));
echo form_close();
echo "</div>";
?>