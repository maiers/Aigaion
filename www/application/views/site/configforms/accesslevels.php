<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php 
echo form_hidden('configformname','accesslevels');
?>


<!-- DEFAULT ACCESS LEVELS -->
        <TR><TD colspan=2>
        <hr><p class=header2><?php echo __('Default access levels:');?></p>
        </TD></TR>
        <tr><td align=left colspan=2><img border=0 src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
        <?php echo __("Specify here the default access levels for different types of objects. A 'Default publication read level' of 'public' means, for example, that new publications are publicly accessible by default");?></td>
        </tr>
        <tr>
            <td><?php echo __('Type:');?></td>
            <td><?php echo __('Default access level:');?></td>
        </tr>
<?php
    $types = array('ATT_DEFAULT_READ'=>__('Default attachment read level'),
          'ATT_DEFAULT_EDIT'=>__('Default attachment edit level'),
          'PUB_DEFAULT_READ'=>__('Default publication read level'),
          'PUB_DEFAULT_EDIT'=>__('Default publication edit level'),
          'NOTE_DEFAULT_READ'=>__('Default note read level'),
          'NOTE_DEFAULT_EDIT'=>__('Default note edit level'),
          'TOPIC_DEFAULT_READ'=>__('Default topic read level'),
          'TOPIC_DEFAULT_EDIT'=>__('Default topic edit level'));
    
    $levels = array('private'=>__('private'),'intern'=>__('intern'),'public'=>__('public'));
    
    foreach ($types as $type=>$desc) {
?>        
        <tr>
        <td><label><?php echo $desc; ?></label></td>
        <td align=left>
<?php
        $level = 'intern';
        if ($siteconfig->getConfigSetting($type)!='') {
            $level = $siteconfig->getConfigSetting($type);
        }
        
        echo form_dropdown($type, $levels, $level);
?>
	    </td>
        </tr>
        </tr>
<?php
    }
?>