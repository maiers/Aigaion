<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** This class regulates the access levels for reading and editing objects.
It provides methods such as canReadObject and canEditObject as well as a number
of methods to change access levels that take into account the cascaded dependencies
between objects such as publications and attachments.

NOTE: these methods only check access levels and overrides; they do not check whether 
the user has publication_edit or other necessary rights.

-topic
 |-subtopic

-publication        
 |-attachment1     
 |-attachment2...
 |
 |-note1
 |-note2...

*/
class Accesslevels_lib {
  
    function Accesslevels_lib()
    {
    }

    function canReadObject(&$object) {
        $userlogin=getUserLogin();
        if ($object->derived_read_access_level=='public') return true;  //public
        if ($userlogin->isAnonymous()) return false;                    //anonymous, not public
        return (
                (    ($object->derived_read_access_level != 'private') 
                  || ($object->user_id == $userlogin->userId()) 
                  || ($userlogin->hasRights('read_all_override'))
                 )
                ); //private or not
    }
    function canEditObject(&$object) {
        if ($object==null)return false;
        $userlogin=getUserLogin();
        if ($object->derived_edit_access_level=='public') return true;  //public
        if ($userlogin->isAnonymous())                    return false; //anonymous, not public
        return (
                (    ($object->derived_edit_access_level != 'private') 
                  || ($object->user_id == $userlogin->userId()) 
                  || ($userlogin->hasRights('edit_all_override'))
                 )
                ); //private or not
//                (    ($oldtune->edit_access_level == 'private') 
//                  && ($userlogin->userId() != $oldtune->user_id) 
//                  && (!$userlogin->hasRights('tune_edit_all'))
//                 )                
//             ||
//                (    ($oldtune->edit_access_level == 'group') 
//                  && (!in_array($oldtune->group_id,$user->group_ids) ) 
//                  && (!$userlogin->hasRights('tune_edit_all'))
//                 )                  
    }
    
    /** Returns a piece of html showing the derived access levels of the given object */
    function getAccessLevelSummary($object) {
        $result = " ";
        //if ($object->derived_read_access_level!='intern')
            $result .= "r:<img class='al_icon' src='".getIconurl('rights_'.$object->derived_read_access_level.'.gif')."' alt='".__("read level")."'/> ";
        //if ($object->derived_edit_access_level!='intern')
            $result .= "e:<img class='al_icon' src='".getIconurl('rights_'.$object->derived_edit_access_level.'.gif')."' alt='".__("edit level")."'/> ";
        return '<span title="'.__('effective access levels').'">'.$result.'</span>';
    }
    
    //new function for migration to toggling access levels
    function getReadAccessLevelIcon(&$object) {
        if ($object->derived_read_access_level!='group')
          return "<img class='al_icon' title='".__("read access")."' src='".getIconurl('rights_'.$object->derived_read_access_level.'.gif')."' alt='".__("read access level")."' /> ";
        else
          return "";
    }
    
    function getEditAccessLevelIcon(&$object) {
        if ($object->derived_edit_access_level!='group')
          return "<img class='al_icon' title='".__("edit access")."' src='".getIconurl('rights_'.$object->derived_edit_access_level.'.gif')."' alt='".__("Edit access level")."' /> ";
        else
          return "";
    }
    
    
    
    
    /** $type is topic, publication, attachment, or note
    Returns a span of html containing the appropriate access-level-edit panel for the given object,
    depending on the rights of the logged user, the current access levels of the object and the owner 
    of the object. */
    function getAccessLevelEditPanel($object,$type,$object_id) {
        $CI = &get_instance();
        $grey='_grey';
        $title=__('cannot edit access levels of this object');
        $editR = '';
        $editE = '';
        $userlogin = getUserLogin();
        if ($CI->accesslevels_lib->canEditObject($object)) {
            $grey='';
            $title='';
            $options = array('public'=>__('public'),'intern'=>__('intern'));
            if ($userlogin->userid()==$object->user_id)
                $options['private'] = __('private');
            $editR = form_dropdown('r_al_'.$type.'_'.$object_id, $options, $object->read_access_level, "onChange='submitAccessLevel(\"".site_url('accesslevels/set')."\", \"r\",\"".$type."\",\"".$object_id."\");' id='r_al_".$type."_".$object_id."'");
            $editE = form_dropdown('e_al_'.$type.'_'.$object_id, $options, $object->edit_access_level, "onChange='submitAccessLevel(\"".site_url('accesslevels/set')."\", \"e\",\"".$type."\",\"".$object_id."\");' id='e_al_".$type."_".$object_id."'");
        }
        $result = '<span title="'.$title.'">';
        if ($object->derived_read_access_level!=$object->read_access_level)
            $result .= '<span title="'.__('effective access level is different').'" style="color:red;font-weight:bold;">!</span>';
        $result .= "r:<img class='al_icon' src='".getIconurl('rights_'.$object->read_access_level.$grey.'.gif')."'/> ";
        $result .= $editR;
        $result .='</span>';
        $result .= '</td><td>';
        $result .= '<span title="'.$title.'">';
        if ($object->derived_edit_access_level!=$object->edit_access_level)
            $result .= '<span title="'.__('effective access level is different').'" style="color:red;font-weight:bold;">!</span>';
        $result .= "e:<img class='al_icon' src='".getIconurl('rights_'.$object->edit_access_level.$grey.'.gif')."'/> ";
        $result .= $editE;
        $result .='</span>';
        return $result;
    }
    
    /** set the new access level. set feedback in a message. all cascades are taken care of. 
    Read inline comments for explanations.
    */
    function setReadAccessLevel($type,$object_id,$newlevel,$bSilent = false) {
        $CI = &get_instance();
        $userlogin=getUserLogin();
        $publication = null;
        switch ($type) {
            case 'topic':
                $config=array();
                $topic = $CI->topic_db->getByID($object_id,$config);
                if (!$this->canEditObject($topic)||!$userlogin->hasRights('topic_edit')) {
                    if (!$bSilent)
                      appendErrorMessage(__('Edit access level').': '.__('insufficient rights').'.<br/>');
                    return;
                }
                $CI->db->update("topics",array('read_access_level'=>$newlevel,'derived_read_access_level'=>$newlevel),array("topic_id"=>$object_id));
                //if edit access level too high compared to new read level, adapt edit level to new read level
                if (($newlevel=='private') && ($topic->edit_access_level!='private')) {
                    $CI->db->update("topics",array('edit_access_level'=>'private','derived_edit_access_level'=>'private'),array("topic_id"=>$object_id));
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                }
                if (($newlevel=='intern') && ($topic->edit_access_level=='public')) {
                    $CI->db->update("topics",array('edit_access_level'=>'intern','derived_edit_access_level'=>'intern'),array("topic_id"=>$object_id));
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                }
                //and fix all derived access levels 
                $this->cascadeAccessLevelsForTopics();
                break;
            case 'publication':
                $publication = $CI->publication_db->getByID($object_id);
                if (!$this->canEditObject($publication)||!$userlogin->hasRights('publication_edit')) {
                    if (!$bSilent)
                      appendErrorMessage(__('Edit access level').': '.__('insufficient rights').'.<br/>');
                    return;
                }
                $CI->db->update("publication",array('read_access_level'=>$newlevel),array("pub_id"=>$object_id));
                //if edit access level too high compared to new read level, adapt edit level to new read level
                if (($newlevel=='private') && ($publication->edit_access_level!='private')) {
                    $CI->db->update("publication",array('edit_access_level'=>'private'),array("pub_id"=>$object_id));
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                }
                if (($newlevel=='intern') && ($publication->edit_access_level=='public')) {
                    $CI->db->update("publication",array('edit_access_level'=>'intern'),array("pub_id"=>$object_id));
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                }
                //and fix all derived access levels again
                $this->cascadeAccessLevelsForPublication($publication->pub_id);
                break;
            case 'attachment':
                $attachment = $CI->attachment_db->getByID($object_id);
                if (!$this->canEditObject($attachment)||!$userlogin->hasRights('attachment_edit')) {
                    if (!$bSilent)
                      appendErrorMessage(__('Edit access level').': '.__('insufficient rights').'.<br/>');
                    return;
                }
                $CI->db->update("attachments",array('read_access_level'=>$newlevel),array("att_id"=>$object_id));
                //if edit access level too high compared to new read level, adapt edit level to new read level
                if (($newlevel=='private') && ($attachment->edit_access_level!='private')) {
                    $CI->db->update("attachments",array('edit_access_level'=>'private'),array("att_id"=>$object_id));
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                }
                if (($newlevel=='intern') && ($attachment->edit_access_level=='public')) {
                    $CI->db->update("attachments",array('edit_access_level'=>'intern'),array("att_id"=>$object_id));
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                }
                //find publication.
                $publication = $CI->publication_db->getByID($attachment->pub_id);
                if ($this->canEditObject($publication)&&$userlogin->hasRights('publication_edit')) {
                    //if attachment set to higher read access level than publication, publication must be updated as well
                    if (($newlevel=='public') && ($publication->read_access_level!='public')) {
                        $CI->db->update("publication",array('read_access_level'=>'public'),array("pub_id"=>$publication->pub_id));
                        if (!$bSilent)
                          appendMessage(__('Modify access level').': '.__('Increased read level for publication to match new read level for attachment').'.<br/>');
                    }
                    if (($newlevel=='intern') && ($publication->read_access_level=='private')) {
                        $CI->db->update("publication",array('read_access_level'=>'intern'),array("pub_id"=>$publication->pub_id));
                        if (!$bSilent)
                          appendMessage(__('Modify access level').': '.__('Increased read level for publication to match new read level for attachment').'.<br/>');
                    }
                } else {
                    if (!$bSilent)
                      appendMessage(__("Couldn't propagate new level all the way up").".<br/>");
                }
                //and fix all derived access levels again
                $this->cascadeAccessLevelsForPublication($publication->pub_id);
                break;
            case 'note':
                $note = $CI->note_db->getByID($object_id);
                if (!$this->canEditObject($note)||!$userlogin->hasRights('note_edit')) {
                    if (!$bSilent)
                      appendErrorMessage(__('Edit access level').': '.__('insufficient rights').'.<br/>');
                    return;
                }
                $CI->db->update("notes",array('read_access_level'=>$newlevel),array("note_id"=>$object_id));
                //if edit access level too high compared to new read level, adapt edit level to new read level
                if (($newlevel=='private') && ($note->edit_access_level!='private')) {
                    $CI->db->update("notes",array('edit_access_level'=>'private'),array("note_id"=>$object_id));
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                }
                if (($newlevel=='intern') && ($note->edit_access_level=='public')) {
                    $CI->db->update("notes",array('edit_access_level'=>'intern'),array("note_id"=>$object_id));
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                }
                //find publication.
                $publication = $CI->publication_db->getByID($note->pub_id);
                if ($this->canEditObject($publication)&&$userlogin->hasRights('publication_edit')) {
                    //if note set to higher read access level than publication, publication must be updated as well
                    if (($newlevel=='public') && ($publication->read_access_level!='public')) {
                        $CI->db->update("publication",array('read_access_level'=>'public'),array("pub_id"=>$publication->pub_id));
                        if (!$bSilent)
                          appendMessage(__('Modify access level').': '.__('Increased read level for publication to match new read level for note').'.<br/>');
                    }
                    if (($newlevel=='intern') && ($publication->read_access_level=='private')) {
                        $CI->db->update("publication",array('read_access_level'=>'intern'),array("pub_id"=>$publication->pub_id));
                        if (!$bSilent)
                          appendMessage(__('Modify access level').': '.__('Increased read level for publication to match new read level for note').'.<br/>');
                    }
                } else {
                    if (!$bSilent)
                      appendMessage(__("Couldn't propagate new level all the way up").".<br/>");
                }
                //and fix all derived access levels again
                $this->cascadeAccessLevelsForPublication($publication->pub_id);
                break;

        }
    }
    
    /** set the new access level. set feedback in a message. all cascades are taken care of. 
    Read inline comments for explanations.
    Note assumption: the derived access levels make sure that you will not be able to read
    */
    function setEditAccessLevel($type,$object_id,$newlevel,$bSilent = false) {
        //don't allow to set edit level higher than read level - user must set new read level first
        $CI = &get_instance();
        $userlogin=getUserLogin();
        $publication = null;
        switch ($type) {
            case 'topic':
                $config = array();
                $topic = $CI->topic_db->getByID($object_id,$config);
                if (!$this->canEditObject($topic)||!$userlogin->hasRights('topic_edit')) {
                    if (!$bSilent)
                      appendErrorMessage(__('Edit access level').': '.__('insufficient rights').'.<br/>');
                    return;
                }
                if ($object_id == 1) {
                    if (!$bSilent)
                      appendErrorMessage(__('Edit access level').': '.__('You cannot change the access levels of the top topic').'.<br/>');
                    return;
                }
                //if edit access level too high compared to new read level, adapt edit level to new read level
                if (($newlevel=='public') && ($topic->read_access_level!='public')) {
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                    $newlevel = $topic->read_access_level;
                }
                if (($newlevel!='private') && ($topic->read_access_level=='private')) {
                    $newlevel = $topic->read_access_level;
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                }
                //set (possibly modified) edit access level
                $CI->db->update("topics",array('edit_access_level'=>$newlevel,'derived_edit_access_level'=>$newlevel),array("topic_id"=>$object_id));
                break;
            case 'publication':
                $publication = $CI->publication_db->getByID($object_id);
                if (!$this->canEditObject($publication)||!$userlogin->hasRights('publication_edit')) {
                    if (!$bSilent)
                      appendErrorMessage(__('Edit access level').': '.__('insufficient rights').'.<br/>');
                    return;
                }
                //if edit access level too high compared to new read level, adapt edit level to new read level
                if (($newlevel=='public') && ($publication->read_access_level!='public')) {
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                    $newlevel = $publication->read_access_level;
                }
                if (($newlevel!='private') && ($publication->read_access_level=='private')) {
                    $newlevel = $publication->read_access_level;
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                }
                //set (possibly modified) edit access level
                $CI->db->update("publication",array('edit_access_level'=>$newlevel),array("pub_id"=>$object_id));
                //and fix all derived access levels again
                $this->cascadeAccessLevelsForPublication($publication->pub_id);
                break;
            case 'attachment':
                $attachment = $CI->attachment_db->getByID($object_id);
                if (!$this->canEditObject($attachment)||!$userlogin->hasRights('attachment_edit')) {
                    if (!$bSilent)
                      appendErrorMessage(__('Edit access level').': '.__('insufficient rights').'.<br/>');
                    return;
                }
                //if edit access level too high compared to new read level, adapt edit level to new read level
                if (($newlevel=='public') && ($attachment->read_access_level!='public')) {
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                    $newlevel = $attachment->read_access_level;
                }
                if (($newlevel!='private') && ($attachment->read_access_level=='private')) {
                    $newlevel = $attachment->read_access_level;
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                }
                //set (possibly modified) edit access level
                $CI->db->update("attachments",array('edit_access_level'=>$newlevel),array("att_id"=>$object_id));
                //no need to cascade upwards, as opposed to with read access levels
                //find publication.
                $publication = $CI->publication_db->getByID($attachment->pub_id);
                //and fix all derived access levels again
                $this->cascadeAccessLevelsForPublication($publication->pub_id);
                break;
            case 'note':
                $note = $CI->note_db->getByID($object_id);
                if (!$this->canEditObject($note)||!$userlogin->hasRights('note_edit')) {
                    if (!$bSilent)
                      appendErrorMessage(__('Edit access level').': '.__('insufficient rights').'.<br/>');
                    return;
                }
                //if edit access level too high compared to new read level, adapt edit level to new read level
                if (($newlevel=='public') && ($note->read_access_level!='public')) {
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                    $newlevel = $note->read_access_level;
                }
                if (($newlevel!='private') && ($note->read_access_level=='private')) {
                    $newlevel = $note->read_access_level;
                    if (!$bSilent)
                      appendMessage(__('Modify access level').': '.__('Restricted edit level to match read level').'.<br/>');
                }
                //set (possibly modified) edit access level
                $CI->db->update("notes",array('edit_access_level'=>$newlevel),array("note_id"=>$object_id));
                //no need to cascade upwards, as opposed to with read access levels
                //find publication.
                $publication = $CI->publication_db->getByID($note->pub_id);
                //and fix all derived access levels again
                $this->cascadeAccessLevelsForPublication($publication->pub_id);
                break;        
        }
    }
    /**
    cascade: restriction propagates down to derived levels of children; 
    */
    function cascadeAccessLevelsForTopics() {
        appendMessage(__('Cascade of access levels not yet implemented').'.<br/>');
    }
    /**     Long method... as it defines all dependencies for derived access levels.
    Note: the cascades are done directly on table queries, and not on publication->getAttachments etc, 
    as you need to affect 'invisible' objects as well!!!!! 
    Cascade rules for effective access levels documented inline.
    */
    function cascadeAccessLevelsForPublication($pub_id) {
        $CI = &get_instance();
        //1) set derived access levels for publication to same as normal access levels
        $readPublicationQ = $CI->db->get_where('publication',array('pub_id'=>$pub_id));
        $pubrow = $readPublicationQ->row();
        $CI->db->where('pub_id', $pub_id);
        $CI->db->update('publication', array('derived_read_access_level'=>$pubrow->read_access_level,'derived_edit_access_level'=>$pubrow->edit_access_level));
        //2) for attachments, maximize derived read at publication's read; maximize derived edit at attachment's derived read
        $readAttQ = $CI->db->get_where('attachments',array('pub_id'=>$pub_id));
        foreach ($readAttQ->result() as $attrow) {
            $att_der_read = $this->minAccessLevel(array($pubrow->read_access_level,$attrow->read_access_level));
            $att_der_edit = $this->minAccessLevel(array($att_der_read,$attrow->edit_access_level));
            $CI->db->where('att_id', $attrow->att_id);
            $CI->db->update('attachments', array('derived_read_access_level'=>$att_der_read,'derived_edit_access_level'=>$att_der_edit));
        }
        //3) for notes, maximize derived read at publication's read; maximize derived edit at note's derived read
        $readNoteQ = $CI->db->get_where('notes',array('pub_id'=>$pub_id));
        foreach ($readNoteQ->result() as $noterow) {
            $note_der_read = $this->minAccessLevel(array($pubrow->read_access_level,$noterow->read_access_level));
            $note_der_edit = $this->minAccessLevel(array($note_der_read,$noterow->edit_access_level));
            $CI->db->where('note_id', $noterow->note_id);
            $CI->db->update('notes', array('derived_read_access_level'=>$note_der_read,'derived_edit_access_level'=>$note_der_edit));
        }
        //don't forget to note which objects have become inaccessible......
        //appendMessage('Recalculated all effective access levels<br/>');
    }
    
    /** return the minimum access level from the given list */
    function minAccessLevel($levels) {
        
        if (in_array('private',$levels))return 'private';
        if (in_array('intern',$levels))return 'intern';
        return 'public';
    }
    /** initialize the access levels for the given publication. Note: the publication is in the database; both the access
    levels of the publication object, and in the database table, are set. 
    All levels set to intern. */
    function initPublicationAccessLevels($publication) {
        $CI = &get_instance();
        $publication->read_access_level = getConfigurationSetting("PUB_DEFAULT_READ");
        $publication->derived_read_access_level = getConfigurationSetting("PUB_DEFAULT_READ");
        $publication->edit_access_level = getConfigurationSetting("PUB_DEFAULT_EDIT");
        $publication->derived_edit_access_level = getConfigurationSetting("PUB_DEFAULT_EDIT");
        $CI->db->where('pub_id', $publication->pub_id);
        $CI->db->update('publication', array('read_access_level'=>$publication->read_access_level,
                                             'derived_read_access_level'=>$publication->derived_read_access_level,
                                             'edit_access_level'=>$publication->edit_access_level,
                                             'derived_edit_access_level'=>$publication->derived_edit_access_level));
    }
    /** initialize the access levels for the given attachment. Note: the object is in the database; both the access
    levels of the object, and in the database table, are set. 
    All levels set to 'intern'. */
    function initAttachmentAccessLevels($attachment) {
        $CI = &get_instance();
        
        $attachment->read_access_level = getConfigurationSetting("ATT_DEFAULT_READ");
        $attachment->edit_access_level = getConfigurationSetting("ATT_DEFAULT_EDIT");
        $CI->db->where('att_id', $attachment->att_id);
        $CI->db->update('attachments', array('read_access_level'=>$attachment->read_access_level,
                                             'edit_access_level'=>$attachment->edit_access_level));
        $this->cascadeAccessLevelsForPublication($attachment->pub_id);
    }
    /** initialize the access levels for the given note. Note: the object is in the database; both the access
    levels of the object, and in the database table, are set. 
    All levels set to 'intern'. */
    function initNoteAccessLevels($note) {
        $CI = &get_instance();
        
        $note->read_access_level = getConfigurationSetting("NOTE_DEFAULT_READ");
        $note->edit_access_level = getConfigurationSetting("NOTE_DEFAULT_EDIT");
        $CI->db->where('note_id', $note->note_id);
        $CI->db->update('notes', array('read_access_level'=>$note->read_access_level,
                                       'edit_access_level'=>$note->edit_access_level));
        $this->cascadeAccessLevelsForPublication($note->pub_id);
    }

    /** initialize the access levels for the given topic. Note: the object is in the database; both the access
    levels of the object, and in the database table, are set. 
    All levels set to that of the parent. */
    function initTopicAccessLevels($topic) {
        $CI = &get_instance();
        $parent = $topic->getParent();
        $topic->read_access_level = getConfigurationSetting("TOPIC_DEFAULT_READ");
        $topic->derived_read_access_level = $this->minAccessLevel(array($parent->derived_read_access_level,$topic->read_access_level));
        $topic->edit_access_level = getConfigurationSetting("TOPIC_DEFAULT_EDIT");
        $topic->derived_edit_access_level = $this->minAccessLevel(array($parent->derived_edit_access_level,$topic->edit_access_level));
        $CI->db->where('topic_id', $topic->topic_id);
        $CI->db->update('topics', array('read_access_level'=>$topic->read_access_level,
                                        'derived_read_access_level'=>$topic->derived_read_access_level,
                                        'edit_access_level'=>$topic->edit_access_level,
                                        'derived_edit_access_level'=>$topic->derived_edit_access_level));
    }
}
?>