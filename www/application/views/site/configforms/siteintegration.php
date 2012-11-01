<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php 
echo form_hidden('configformname','siteintegration');
?>

      <tr>
            <td colspan='2'><hr><p class='header2'><?php echo __('Embedding options:');?></p></td>
	    </tr>

	    <tr>
	        <td><label for='EMBEDDING_SHAREDDOMAIN'><?php echo __('Shared domain for embedding:');?></label></td>
	        <td align='left'><input type='text' cols='60' size=50 name='EMBEDDING_SHAREDDOMAIN' value='<?php echo $siteconfig->getConfigSetting("EMBEDDING_SHAREDDOMAIN"); ?>'></td>
	    </tr>

      <tr>
            <td colspan='2'><hr><p class='header2'><?php echo __('Logintegration options:');?></p></td>
	    </tr>

	    <tr>
	        <td><label for='LOGINTEGRATION_SECRETWORD'><?php echo __('Shared secret phrase for integrated login:');?></label></td>
	        <td align='left'><input type='password' cols='60' size=50 name='LOGINTEGRATION_SECRETWORD'  id='LOGINTEGRATION_SECRETWORD' value='<?php echo $siteconfig->getConfigSetting("LOGINTEGRATION_SECRETWORD"); ?>'></td>
	    </tr>
