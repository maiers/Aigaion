<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div class="rightsprofile-summary">
<?php
/**
views/rightsprofiles/summary

Shows a summary of a rightsprofile: edit link, name, delete link, etc

Parameters:
    $rightsprofile=>the Rightsprofile object that is to be summarized
we assume that this view is not loaded if you don't have the appropriate read and edit rights

*/
    echo '['.anchor('rightsprofiles/edit/'.$rightsprofile->rightsprofile_id,__('edit'))."]&nbsp;["
    .anchor('rightsprofiles/delete/'.$rightsprofile->rightsprofile_id,__('delete'))."]&nbsp;"
    .$rightsprofile->name;
?>
</div>