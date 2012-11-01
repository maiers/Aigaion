<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php 
echo form_hidden('configformname','display');
?>
<!-- SITE ADMIN NAME -->
        <tr>
            <td colspan='2'><hr><p class='header2'><?php echo __('Site Admin:');?></p></td>
	    </tr>

	    <tr>
	        <td><label for='CFG_ADMIN'><?php echo __('Name of Aigaion administrator:');?></label></td>
	        <td align='left'><input type='text' cols='60' size=50 name='CFG_ADMIN' value='<?php echo $siteconfig->getConfigSetting("CFG_ADMIN"); ?>'></td>
	    </tr>

	    <tr>
	        <td><label for='CFG_ADMINMAIL'><?php echo __('Email of Aigaion administrator:');?></label></td>
	        <td align='left'><input type='text' cols='60' size=50 name='CFG_ADMINMAIL' value='<?php echo $siteconfig->getConfigSetting("CFG_ADMINMAIL"); ?>'></td>
	    </tr>
	    
<!-- DISPLAY SETTINGS -->
	    <tr>
	        <td colspan='2'><hr><p class='header2'><?php echo __('Some display settings:');?></p></td>
	    </tr>

        <tr>
	        <td><label for='WINDOW_TITLE'><?php echo __('Title of the site:');?></label></td>
	        <td align='left'><input type='text' cols='60' size=50 name='WINDOW_TITLE' 
<?php
	        echo "value='".$siteconfig->getConfigSetting("WINDOW_TITLE")."'>";
?>
	        </td>

        <tr>
	        <td><label><?php echo __('Display publications on single-topic page:');?></label></td>
	        <td align='left'>
<?php
            echo form_checkbox('ALWAYS_INCLUDE_PAPERS_FOR_TOPIC','ALWAYS_INCLUDE_PAPERS_FOR_TOPIC',$siteconfig->getConfigSetting("ALWAYS_INCLUDE_PAPERS_FOR_TOPIC")== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Checking this box means that the full list of publications for a topic is included below the topic description, to speed up browsing for papers. Turning this on might however slow down the loading of the topic pages.');?></td>
	    </tr>
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>
	
	    <tr>
	        <td><label><?php echo __('Merge crossreferenced publications in single publication view:');?></label></td>
	        <td align='left'>
<?php
            echo form_checkbox('PUBLICATION_XREF_MERGE','PUBLICATION_XREF_MERGE',$siteconfig->getConfigSetting("PUBLICATION_XREF_MERGE")== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Check to merge cross-referenced publications on a single publication page view.');?></td>
	    </tr>
	
	    <tr>
	        <td><label><?php echo __('Use TinyMCE note editor:');?></label></td>
	        <td align='left'>
<?php
            echo form_checkbox('ENABLE_TINYMCE','ENABLE_TINYMCE',$siteconfig->getConfigSetting("ENABLE_TINYMCE")== "TRUE");
?>
            </td>
        </tr>
	    <tr>
	        <td align='left' colspan='2'><img class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
	        <?php echo __('Check to enable the Tiny-MCE editor for editing notes.');?></td>
	    </tr>	    
	    <tr>
	        <td align='left' colspan='2'></td>
	    </tr>

<?php
#use uploaded logo?
$checked = "";
if ($siteconfig->getConfigSetting("USE_UPLOADED_LOGO")=="TRUE")
    $checked = "CHECKED";
?>
<!--
        <TR><TD>Use custom logo</TD>
	        <td align='left'>
<?php	            
    echo form_checkbox('USE_UPLOADED_LOGO','USE_UPLOADED_LOGO',$siteconfig->getConfigSetting("USE_UPLOADED_LOGO")== "TRUE");
?>
            </td>
        </TR>
        <tr><td align=left colspan=2><img border=0 class='icon' src='<?php echo getIconUrl("small_arrow.gif"); ?>'>If checked, you can 
            specify a custom logo (below) to be used on the front page.</td></tr>
        <tr><td colspan=2>Current logo:</td></tr>
        <tr><td colspan=2>
<?php
    if (file_exists(AIGAION_ROOT_DIR.'/custom_logo.jpg')) {
        echo '<img border=0 src="'.AIGAION_ROOT_URL.'/custom_logo.jpg">';
    } else {
        echo '&lt;no logo uploaded&gt;';
    }
?>
            </td>
        </tr>
        <tr><td><label for='CUSTOM_LOGO'>Select a new logo file...</label></td>
            <td><input type='file' name='new_logo' size='30'/></td>
        </tr>

-->