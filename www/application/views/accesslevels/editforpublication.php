<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php 
$userlogin=getUserLogin();
$user_id = $userlogin->userId();
$this->load->helper('form');
?>
<div class='header'><?php echo __('Edit access levels')."</div>\n";
echo '['.anchor('publications/show/'.$publication->pub_id,__('Back to publication')).']'; ?>
<br/><br/>
<div style='border:1px solid grey'>
<table>
    <tr >
        <td colspan='2'>
            <img src='<?php echo getIconUrl('info.gif')."' title='".__('Effective access levels (after combining all relevant access levels)')."'";?> />
            <br/><?php echo __('Effective'); ?>
        </td>
        <td>
            <img src='<?php echo getIconUrl('info.gif')."' title='".__('Object')."'";?> />
            <br/><?php echo __('Object');?>
        </td>
        <td>
          <img src='<?php echo getIconUrl('info.gif')."' title='".__('Owner of object (only owner can change objects with private edit levels)')."'";?> />
            <br/><?php echo __('Owner'); ?>
            <br/><br/>
        </td>
        <td colspan='2'>
            <img src='<?php echo getIconUrl('info.gif')."' title='".__('Per-object access levels')."'";?> />
            <br/><?php echo __('Individual per-object access levels');?>
        </td>
        
    </tr>

    <tr >
        <td>
        </td>
        <td>
        </td>
        <td style='padding-left:0.5em;'>
           <i><br/><?php echo __('Publication');?>:</i>
        </td>
    </tr>
    <tr <?php
        if ($type=='publication')echo 'style="background:#dfdfff;" ';
        ?>>
        <td>
            r:<img class='rights_icon' src='<?php echo getIconurl('rights_'.$publication->derived_read_access_level.'_grey.gif'); ?>' />
        </td>
        <td>
            e:<img class='rights_icon' src='<?php echo getIconurl('rights_'.$publication->derived_edit_access_level.'_grey.gif'); ?>'/>
        </td>
        <td style='padding-left:0.5em;' class='header2'>
            <?php echo $publication->title; ?>
        </td>
        <td style='padding-left:0.5em;'>
            <?php 
            if ($publication->user_id==$user_id) {
                echo '<span class="owner_self">';
            } else {
                echo '<span class="owner_other">';
            }
            echo '['.getAbbrevForUser($publication->user_id).']</span>'; 
            ?>
        </td>
        <td>
            <?php
            echo $this->accesslevels_lib->getAccessLevelEditPanel($publication,'publication',$publication->pub_id);
            ?>
        </td>
    </tr>

    <tr>
        <td>
        </td>
        <td>
        </td>
        <td style='padding-left:2em;'>
           <i><br/><?php echo __('Attachments');?>:</i>
        </td>
    </tr>
<?php
foreach ($publication->getAttachments() as $attachment) {
?>
    <tr <?php
        if (($type=='attachment')&&($object_id==$attachment->att_id))echo 'style="background:#dfdfff;" ';
        ?>>
        <td>
            r:<img class='rights_icon' src='<?php echo getIconurl('rights_'.$attachment->derived_read_access_level.'_grey.gif'); ?>'/>
        </td>
        <td>
            e:<img class='rights_icon' src='<?php echo getIconurl('rights_'.$attachment->derived_edit_access_level.'_grey.gif'); ?>'/>
        </td>
        <td style='padding-left:2em;' class='header2'>
            <?php echo $attachment->name; ?>
        </td>
        <td style='padding-left:0.5em;'>
            <?php 
            if ($attachment->user_id==$user_id) {
                echo '<span class="owner_self">';
            } else {
                echo '<span class="owner_other">';
            }
            echo '['.getAbbrevForUser($attachment->user_id).']</span>'; 
            ?>
        </td>
        <td>
            <?php
            echo $this->accesslevels_lib->getAccessLevelEditPanel($attachment,'attachment',$attachment->att_id);
            ?>
        </td>
    </tr>
<?php  
}
?>

    <tr>
        <td>
        </td>
        <td>
        </td>
        <td style='padding-left:2em;'>
           <i><br/><?php echo __('Notes');?>:</i>
        </td>
    </tr>
<?php
foreach ($publication->getNotes() as $note) {
?>
    <tr <?php
        if (($type=='note')&&($object_id==$note->note_id))echo 'style="background:#dfdfff;" ';
        ?>>
        <td>
            r:<img class='rights_icon' src='<?php echo getIconurl('rights_'.$note->derived_read_access_level.'_grey.gif'); ?>'/>
        </td>
        <td>
            e:<img class='rights_icon' src='<?php echo getIconurl('rights_'.$note->derived_edit_access_level.'_grey.gif'); ?>'/>
        </td>
        <td style='padding-left:2em;'>
            <?php echo $note->text; ?>
        </td>
        <td style='padding-left:0.5em;'>
            <?php 
            if ($note->user_id==$user_id) {
                echo '<span class="owner_self">';
            } else {
                echo '<span class="owner_other">';
            }
            echo '['.getAbbrevForUser($note->user_id).']</span>'; 
            ?>
        </td>
        <td>
            <?php
            echo $this->accesslevels_lib->getAccessLevelEditPanel($note,'note',$note->note_id);
            ?>
        </td>
    </tr>
<?php  
}
?>


</table>
</div>
<br/><br/>
<div style='border:1px solid black;'>
    <div style='border:1px solid black;'>
        <b><?php echo __('Legend');?></b>
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
<?php
echo "
<p>".__("When you modify access levels of individual objects, this may have consequences for the final 'effective' access level of other objects. For example, when you set a publication to private, the effective access level of all objects belonging to that publication will be set to private as well.")."</p>
<p>".__("On the other hand, when you edit the read access level of for example an attachment, and the new level is higher than that of the publication it belongs to, the <b>actual</b> read level of the publication is updated as well!")."</p>
<p>".__("Note that the effective access levels are shown on the left; the access levels defined per individual object are shown on the right. Editing of access levels is done through the right column.")."</p>
<p>".__("<b>Unsure how the access levels turned out?</b> The column on the left shows which objects are effectively accessible with what levels!")."</p>
<p>".__("Example: Publication is 'intern'; attachment is 'intern'. SET attachment to 'public' &rarr; publication will become 'public' as well.")."</p>
<p>".__("Example: Publication is 'intern'; attachment is 'intern'. SET publication to 'private' &rarr; attachment stays 'intern', but EFFECTIVE access level of attachment becomes 'private'. When you set the publication to 'intern' again, the effective access level of the attachment reverts to 'intern'.")."</p>
<p>".__("Example: Attachment read is 'public', attachment edit is 'intern'. Set attachment read to 'private' &rarr; attachment edit will also change to 'private'.")."</p>
<p>".__("Example: A publication has edit level 'intern'. You are not the owner. You change the edit level to 'private'. &rarr; Subsequently, you can no longer edit that publication :o)")."</p>";

