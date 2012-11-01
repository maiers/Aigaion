<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div style='border:1px solid black;'>
    <div style='border:1px solid black;'>
        <b><?php echo __('Legenda');?></b>
    </div>
    <?php
        echo "
    r:<img class='rights_icon' src='".getIconurl('rights_public.gif')."'/> ".__('read public')."<br/> 
    r:<img class='rights_icon' src='".getIconurl('rights_intern.gif')."'/> ".__('read intern')."<br/> 
    r:<img class='rights_icon' src='".getIconurl('rights_private.gif')."'/> ".__('read private')."<br/> 
    e:<img class='rights_icon' src='".getIconurl('rights_public.gif')."'/> ".__('edit public')."<br/> 
    e:<img class='rights_icon' src='".getIconurl('rights_intern.gif')."'/> ".__('edit intern')."<br/> 
    e:<img class='rights_icon' src='".getIconurl('rights_private.gif')."'/> ".__('edit private')."<br/> 
    - ".__("If nothing is shown, access level is 'intern'")."<br/>
    ";
    ?>
</div>
<br/>