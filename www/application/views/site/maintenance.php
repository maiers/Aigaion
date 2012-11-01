<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/site/maintenance

Shows a form for site maintenance.

Parameters:
    none

we assume that this view is not loaded if you don't have the appropriate database_manage rights

*/
?>
<p class='header'><?php echo __('Maintenance and checks');?></p>
<?php echo sprintf(__('There is a set of maintenance functions available. You can either perform the maintenance functions separately by selecting from the list, or you can %s'), anchor('site/maintenance/all',__('perform all checks at once')));?>.
<ul>
	<li><?php echo anchor('site/maintenance/attachments',__('Check attachments')); ?></li>
	<li><?php echo anchor('site/maintenance/topics',__('Check topics')); ?></li>
	<li><?php echo anchor('site/maintenance/notes',__('Check notes')); ?></li>
	<li><?php echo anchor('site/maintenance/authors',__('Check authors')); ?></li>
	<li><?php echo anchor('site/maintenance/keywords',__('Check keywords')); ?></li>
	<li><?php echo anchor('site/maintenance/passwords',__('Check passwords')); ?></li>
	<li><?php echo anchor('site/maintenance/cleannames',__('Check searchable names, keywords and titles')); ?></li>
	<li><?php echo anchor('site/maintenance/publicationmarks',__('Check publication marks')); ?></li>
	<li><?php echo anchor('site/maintenance/checkupdates',__('Check for updates')); ?></li>
</ul>
<p class='header'><?php echo __('Backup and restore');?></p>
<?php echo __('Making regular backups of the database is recommended. Collecting a complete bibliography takes a lot of time and a single server crash fades all these efforts away. Storing the backupfiles on another server or medium is recommended.');?>
<ul>
	<li><?php echo anchor('site/backup',__('Export database'), array('class'=>'open_extern')); ?></li>
	<li><?php echo anchor('site/restore',__('Restore database from backup')); ?></li>
	<!--<br/>-->
	<!--<li><a href='?page=maintenance&type=attachmentbackup'>Export attachments</a></li>-->
	<!--<li><a href='?page=maintenance&type=attachmentrestore'>Restore local attachments</a></li>-->
</ul>
