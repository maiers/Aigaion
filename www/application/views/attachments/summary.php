<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!-- Single attachment displays -->
<?php
/**
views/attachments/summary

Shows a summary of an attachment: download link, name, delete link, main or not, note, etc

Parameters:
    $attachment=>the Attachment object that is to be shown

access rights: we presume that this view is not loaded when the user doesn't have the read rights.
as for the edit rights: they determine which edit links are shown.
*/
$userlogin  = getUserLogin();
$user       = $this->user_db->getByID($userlogin->userID());
        
    if ($attachment->isremote) {
        echo "<a href='".prep_url($attachment->location)."' class='open_extern'><img title='".sprintf(__('Download %s'), htmlentities($attachment->name,ENT_QUOTES, 'utf-8'))."' class='large_icon' src='".getIconUrl("attachment_html.gif")."'/></a>\n";
    } else {
        $iconUrl = getIconUrl("attachment.gif");
        //might give problems if location is something containing UFT8 higher characters! (stringfunctions)
        //however, internal file names were created using transliteration, so this is not a problem
        $extension=strtolower(substr(strrchr($attachment->location,"."),1));
        if (iconExists("attachment_".$extension.".gif")) {
            $iconUrl = getIconUrl("attachment_".$extension.".gif");
        }
        $params = array('title'=>sprintf(__('Download %s'),$attachment->name));
        if ($userlogin->getPreference('newwindowforatt')=='TRUE')
            $params['class'] = 'open_extern';
        echo anchor('attachments/single/'.$attachment->att_id,"<img class='large_icon' src='".$iconUrl."'/>" ,$params)."\n";
    }
    $name = $attachment->name;
    $this->load->helper('utf8');
    if (utf8_strlen($name)>31) {
        $name = utf8_substr($name,0,30)."...";
    }
    echo $name;
        
    //the block of edit actions: dependent on user rights
    $userlogin = getUserLogin();
    if (    ($userlogin->hasRights('attachment_edit'))
         && 
            $this->accesslevels_lib->canEditObject($attachment)         
        ) 
    {
        echo "&nbsp;&nbsp;".anchor('attachments/delete/'.$attachment->att_id,"[".__('delete')."]",array('title'=>sprintf(__('Delete %s'), $attachment->name)));
        echo "&nbsp;".anchor('attachments/edit/'.$attachment->att_id,"[".__('edit')."]",array('title'=>sprintf(__('Edit information for %s'),$attachment->name)));
        if ($attachment->ismain) {
            echo "&nbsp;".anchor('attachments/unsetmain/'.$attachment->att_id,"[".__('unset main')."]",array('title'=>__('Unset as main attachment')));
        } else {
            echo "&nbsp;".anchor('attachments/setmain/'.$attachment->att_id,"[".__('set main')."]",array('title'=>__('Set as main attachment')));
        }
        
        $read_icon = $this->accesslevels_lib->getReadAccessLevelIcon($attachment);
        $edit_icon = $this->accesslevels_lib->getEditAccessLevelIcon($attachment);
        
        $readrights = $this->ajax->link_to_remote($read_icon,
                      array('url'     => site_url('/accesslevels/toggle/attachment/'.$attachment->att_id.'/read'),
                            'update'  => 'attachment_rights_'.$attachment->att_id
                           )
                      );
        $editrights = $this->ajax->link_to_remote($edit_icon,
                      array('url'     => site_url('/accesslevels/toggle/attachment/'.$attachment->att_id.'/edit'),
                            'update'  => 'attachment_rights_'.$attachment->att_id
                           )
                      );
        
        echo "[<span title='".__('attachment read / edit rights')."'><span id='attachment_rights_".$attachment->att_id."'>r:".$readrights."e:".$editrights."</span></span>]";
        
    }
    
    //always show note
    if ($attachment->note!='') {
        echo "<br/>&nbsp;&nbsp;&nbsp;(".$attachment->note.")";
    }
?>
<!-- End of single attachment displays -->
