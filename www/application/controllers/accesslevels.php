<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Accesslevels extends CI_Controller {

  function __construct()
  {
    parent::__construct();
  }

  /** There is no default controller . */
  function index()
  {
    redirect('');
  }



  /**
  accesslevels/edit

  access point to start editing the access levels of an object

  Fails (with error message) when one of:
  non existing object
  insufficient user rights

  Information passed through segments:
  3rd: object type
  4rth: object id

  Returns:
  a complete edit GUI for access levels

  */
  function edit() {
    $type      = $this->uri->segment(3);
    $object_id = $this->uri->segment(4);
    if ($type=='topic') {
      $this->_edittopic();
      return;
    }
    //determine publication
    $publication = null;
    switch ($type) {
      case 'publication':
      $publication = $this->publication_db->getByID($object_id);
      break;
      case 'attachment':
      $attachment = $this->attachment_db->getByID($object_id);
      if ($attachment!=null)$publication = $this->publication_db->getByID($attachment->pub_id);
      break;
      case 'note':
      $note= $this->note_db->getByID($object_id);
      if ($note!=null)$publication = $this->publication_db->getByID($note->pub_id);
      break;
    }
    //publication null: redirect with error
    if ($publication==null) {
      appendErrorMessage(__("Couldn't find publication to edit access levels")."<br/>");
      redirect('');
    }

    $headerdata = array();
    $headerdata['title'] = __('Edit access levels');
    $headerdata['javascripts'] = array('accesslevels.js','prototype.js','scriptaculous.js','builder.js');

    $output = $this->load->view('header', $headerdata, true);

    $output .= $this->load->view('accesslevels/editforpublication',
    array('publication'=>$publication,'type'=>$type,'object_id'=>$object_id),
    true);

    $output .= $this->load->view('footer','', true);

    //set output
    $this->output->set_output($output);
  }


  /**
  accesslevels/toggle
  toggles the access rights of an object (public -> intern -> private -> public ...)

  Information passed through segments:
  3rd: object type
  4rth: object id
  5th: read or edit rights

  Returns:
  an object rights summary <span>

  */
  function toggle()
  {
    $userlogin=getUserLogin();
    $type             = $this->uri->segment(3);  //type of object
    $object_id        = $this->uri->segment(4);  //ID of object
    $rights_type      = $this->uri->segment(5);  //read or edit rights, can either be 'read' or 'edit'
    $available_rights = array('public', 'intern', 'private');
    $read_icon = "";
    $edit_icon = "";

    $rw = $rights_type."_access_level";
    
    
    //determine object type and check access level
    switch ($type) {
      case 'publication':
      $publication = $this->publication_db->getByID($object_id);
      
      //check if we retrieved the object
      if ($publication != null)
      {
        //get old rights summary in case we fail the type change
          $read_icon = $this->accesslevels_lib->getReadAccessLevelIcon($publication);
          $edit_icon = $this->accesslevels_lib->getEditAccessLevelIcon($publication);
        
        //check if the user has the required rights
        if ($this->accesslevels_lib->canEditObject($publication))
        {
          //determine current and new access level
          $currentLevel = $publication->$rw;
          $newlevel     = $currentLevel;
          if ($currentLevel == 'public')
          $newlevel = "intern";
          if ($currentLevel == 'intern')
          {
            if ($userlogin->userid()==$publication->user_id)
            {
              $newlevel = "private";
            } 
            else 
            {
              $newlevel = "public";
            }
          }
          if ($currentLevel == 'private')
          $newlevel = "public";
        }
        if ($rights_type == 'read')
        $this->accesslevels_lib->setReadAccessLevel($type,$object_id,$newlevel,true);
        if ($rights_type == 'edit')
        $this->accesslevels_lib->setEditAccessLevel($type,$object_id,$newlevel,true);

        $publication = $this->publication_db->getByID($object_id);
        
        $read_icon = $this->accesslevels_lib->getReadAccessLevelIcon($publication);
        $edit_icon = $this->accesslevels_lib->getEditAccessLevelIcon($publication);
        
      }
      break;
      case 'attachment':
      $attachment = $this->attachment_db->getByID($object_id);
      
      //check if we retrieved the object
      if ($attachment!=null)
      {
        //get old rights summary in case we fail the type change
        $read_icon = $this->accesslevels_lib->getReadAccessLevelIcon($attachment);
        $edit_icon = $this->accesslevels_lib->getEditAccessLevelIcon($attachment);
        
        //check if the user has the required rights
        if ($this->accesslevels_lib->canEditObject($attachment))
        {
          //determine current and new access level
          $currentLevel = $attachment->$rw;
          $newlevel     = $currentLevel;
          if ($currentLevel == 'public')
          $newlevel = "intern";
          if ($currentLevel == 'intern')
          {
            if ($userlogin->userid()==$attachment->user_id)
            {
              $newlevel = "private";
            } 
            else 
            {
              $newlevel = "public";
            }
          }
          if ($currentLevel == 'private')
          $newlevel = "public";
        }
        if ($rights_type == 'read')
          $this->accesslevels_lib->setReadAccessLevel($type,$object_id,$newlevel);
          
        if ($rights_type == 'edit')
        $this->accesslevels_lib->setEditAccessLevel($type,$object_id,$newlevel);

        $attachment = $this->attachment_db->getByID($object_id);

        $read_icon = $this->accesslevels_lib->getReadAccessLevelIcon($attachment,true);
        $edit_icon = $this->accesslevels_lib->getEditAccessLevelIcon($attachment,true);
      }
      break;
      case 'note':
      $note= $this->note_db->getByID($object_id);
      //check if we retrieved the object
      if ($note!=null)
      {
        //get old rights summary in case we fail the type change
        $read_icon = $this->accesslevels_lib->getReadAccessLevelIcon($note);
        $edit_icon = $this->accesslevels_lib->getEditAccessLevelIcon($note);
        
        
        //check if the user has the required rights
        if ($this->accesslevels_lib->canEditObject($note))
        {
          //determine current and new access level
          $currentLevel = $note->$rw;
          $newlevel     = $currentLevel;
          if ($currentLevel == 'public')
          $newlevel = "intern";
          if ($currentLevel == 'intern') 
          {
            if ($userlogin->userid()==$note->user_id)
            {
              $newlevel = "private";
            } 
            else 
            {
              $newlevel = "public";
            }
          }
          if ($currentLevel == 'private')
          $newlevel = "public";
        }
        if ($rights_type == 'read')
        $this->accesslevels_lib->setReadAccessLevel($type,$object_id,$newlevel,true);
        if ($rights_type == 'edit')
        $this->accesslevels_lib->setEditAccessLevel($type,$object_id,$newlevel,true);

        $note = $this->note_db->getByID($object_id);
        $read_icon = $this->accesslevels_lib->getReadAccessLevelIcon($note);
        $edit_icon = $this->accesslevels_lib->getEditAccessLevelIcon($note);
        
      }
      break;
    }
    
    $readrights = $this->ajax->link_to_remote($read_icon,
                  array('url'     => site_url('/accesslevels/toggle/'.$type.'/'.$object_id.'/read'),
                        'update'  => $type."_rights_".$object_id
                       )
                  );
    $editrights = $this->ajax->link_to_remote($edit_icon,
                  array('url'     => site_url('/accesslevels/toggle/'.$type.'/'.$object_id.'/edit'),
                        'update'  => $type."_rights_".$object_id
                       )
                  );


    $this->output->set_output("<span title='".sprintf(__("%s read / edit rights"),__($type))."'>r:".$readrights."e:".$editrights."</span>");
  }

  function _edittopic() {
    $object_id = $this->uri->segment(4);

    $headerdata = array();
    $headerdata['title'] = __('Edit access levels');
    $headerdata['javascripts'] = array('accesslevels.js','prototype.js','scriptaculous.js','builder.js');

    $output = $this->load->view('header', $headerdata, true);

    $output .= $this->load->view('accesslevels/editfortopic',
    array('topic_id'=>$object_id),
    true);

    $output .= $this->load->view('footer','', true);

    //set output
    $this->output->set_output($output);
  }

  /**
  accesslevels/set

  access point to actually new access levels for an object

  Fails (with error message) when one of:
  non existing object
  insufficient user rights

  Information passed through segments:
  3rd: object type
  4rth: object id
  5th: e or r (edit or read access level)
  6th: new level

  Returns:
  to the accesslevels/edit controller, with a feedback message saying what other access levels were affected

  */
  function set() {
    $type      = $this->uri->segment(3);
    $object_id = $this->uri->segment(4);
    $eorr      = $this->uri->segment(5);
    $newlevel  = $this->uri->segment(6);
    if ($eorr=='r') {
      $this->accesslevels_lib->setReadAccessLevel($type,$object_id,$newlevel);
      } else {
        $this->accesslevels_lib->setEditAccessLevel($type,$object_id,$newlevel);
      }
      redirect('accesslevels/edit/'.$type.'/'.$object_id);
    }
  }


  ?>
