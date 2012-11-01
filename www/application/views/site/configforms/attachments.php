<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php 
echo form_hidden('configformname','attachments');
?>

<!-- ATTACHMENT SETTINGS -->
        <tr>
            <td colspan='2'><hr><p class='header2'><?php echo __('Attachment settings:');?></p></td>
        </tr>
	    <tr>    
	        <td><label><?php echo __('Allowed extensions for attachments:');?></label></td>
	        <td align='left'><input type='text' cols='100' size=50  name='ALLOWED_ATTACHMENT_EXTENSIONS'	
<?php
             echo "value='".implode(",",$siteconfig->getConfigSetting("ALLOWED_ATTACHMENT_EXTENSIONS"))."'>";
?>
	        </td>
        </tr>
        <tr>
            <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('The list of allowed extensions for attachments. Attachments that do not have an extension from this list can not be uploaded.');?></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>
	    <tr>
	        <td><label><?php echo __('Allow all remote attachments:');?></label></td>
	        <td align='left'>
<?php
            echo form_checkbox('ALLOW_ALL_EXTERNAL_ATTACHMENTS','ALLOW_ALL_EXTERNAL_ATTACHMENTS',$siteconfig->getConfigSetting("ALLOW_ALL_EXTERNAL_ATTACHMENTS")== "TRUE");
?>
	        </td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __("Check this box if you want to allow all external attachment names, instead of just those ending in one of the 'allowed extensions' specified above. This may be useful because external attachments are often to sites such as portal.acm or doi, with link names ending in meaningless numbers instead of a proper file name. This only affects *remote* attachments.");?></td>
	    </tr>
	
	    <tr>
	        <td><label><?php echo __('The server is read only:');?></label></td>
	        <td align='left'>
<?php 
            echo form_checkbox('SERVER_NOT_WRITABLE','SERVER_NOT_WRITABLE',$siteconfig->getConfigSetting("SERVER_NOT_WRITABLE")== "TRUE");
?>
	        </td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Check this box if the server is read-only, i.e. if you cannot write files such as attachments to the server.');?></td>
	    </tr>
