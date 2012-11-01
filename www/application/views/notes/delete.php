<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/notes/delete

Shows the confirm form for deleting a note.

Parameters:
    $note=>the note object that is to be deleted
*/
$this->load->helper('form');
echo "<div class='confirmform'>";
echo form_open('notes/delete/'.$note->note_id.'/commit');
echo __('Are you sure that you want to delete the note below?!')."<p>\n";
echo form_submit('confirm',__('Confirm'));
echo form_close();
echo form_open('publications/show/'.$note->pub_id);
echo form_submit('cancel',__('Cancel'));
echo form_close();
echo "</div>";
echo "<p class='header'>".__('Note text').":</p>";
echo $note->text;
?>