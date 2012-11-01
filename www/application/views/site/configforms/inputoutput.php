<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php 
echo form_hidden('configformname','inputoutput');
?>

<!-- INPUT/OUTPUT SETTINGS -->
	    <tr>
	        <td colspan='2'><hr><p class='header2'><?php echo __('In- and output settings:');?></p></td>
	    </tr>
	    <tr>
	        <td valign='top'><label for='BIBTEX_STRINGS_IN'><?php echo __('BibTeX strings:');?></label></td>
	        <td><textarea name='BIBTEX_STRINGS_IN' wrap='virtual' cols='50' rows='10'><?php echo $siteconfig->getConfigSetting("BIBTEX_STRINGS_IN"); ?></textarea></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo sprintf(__('BibTeX allows definition of strings. Strings that are defined here are converted when importing BibTeX. The correct syntax for strings is: %s'), '@string {AIG = "Aigaion bibliography System"}');?><br/><br/></td>
	    </tr>
	    <tr>
	        <td><label><?php echo __('Convert BibTeX to UTF8 upon input');?></label></td>
	        <td align='left'>
	        <?php
            echo form_checkbox('CONVERT_BIBTEX_TO_UTF8','CONVERT_BIBTEX_TO_UTF8',$siteconfig->getConfigSetting("CONVERT_BIBTEX_TO_UTF8")!= "FALSE");
          ?>
        </td>
      </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Turn this off if you want special BibTeX character codes to be stored as such in the database, without converting them into utf8. This is useful if you find that you have a lot of BibTeX that is converted wrongly.');?></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>	    
