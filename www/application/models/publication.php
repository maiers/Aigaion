<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php

/*
  Publication class
  Class for storing data of one single publication. The class is used
  in the Publication_model and Publication_list_model classes.
  
  Besides data storage, some publication handling functions are available.
  
  -- Functions --
  
*/

class Publication extends CI_Model {
  //one var for each publication table field
  //system vars
  var $pub_id       = 0;
  var $user_id	    = '';
  var $group_id     = 0; //group to which access is restricted
  var $specialchars = 'FALSE';
  var $cleantitle   = '';
  var $cleanauthor  = '';
  var $cleanjournal = '';
  var $actualyear   = '';
  var $isBookmarked = False;
  var $mark         = 0;
  var $coverimage   = ''; //null or '' means none. Set by uploading image through edit form
  
  //user vars
  var $pub_type     = '';
  var $bibtex_id		= '';
  var $title        = '';
  var $year         = '';
  var $month        = '';
  var $firstpage    = '';
  var $lastpage     = '';
  var $pages		    = ''; 
  var $journal      = '';
  var $booktitle    = '';
  var $edition      = '';
  var $series       = '';
  var $volume       = '';
  var $number       = '';
  var $chapter      = '';
  var $publisher    = '';
  var $location     = '';
  var $institution  = '';
  var $organization = '';
  var $school       = '';
  var $address      = '';
  var $type	        = ''; //note: report_type in DB
  var $howpublished = '';
  var $note         = '';
  var $abstract     = '';
  var $issn         = '';
  var $isbn         = '';
  var $url          = '';
  var $doi          = '';
  var $crossref     = '';
  var $namekey      = '';
  var $userfields   = '';
  var $status       = '';
  var $read_access_level  = 'intern';
  var $edit_access_level  = 'intern';
  var $derived_read_access_level  = 'intern';
  var $derived_edit_access_level  = 'intern';
   
  var $authors      = array(); //array of plain author class
  var $editors      = array(); //array of plain author class
  
  var $keywords     = null; //NOTE: this array is NOT directly accessible, but should ALWAYS be accessed through getKeywords()
  var $attachments  = null; //NOTE: this array is NOT directly accessible, but should ALWAYS be accessed through getAttachments()
  var $notes        = null; //NOTE: this array is NOT directly accessible, but should ALWAYS be accessed through getNotes()
  var $customfields = null; //NOTE: this array is NOT directly accessible, but should ALWAYS be accessed through getCustomFields()
  
  //class constructor
  function __construct()
  {
    parent::__construct();
    //set default publication type
    $this->pub_type = 'Article';
    $this->customfields = array(); // else publication_db->update() would not work sometimes
  }
  
  /** tries to add this publication to the database. may give error message if unsuccessful, e.g. due
    insufficient rights. */
  function add() 
  {
        $CI = &get_instance();
    $result_id = $CI->publication_db->add($this);
    return ($result_id > 0);
  }
  
  /** tries to commit this publication to the database. Returns TRUE or FALSE depending 
      on whether the operation was operation was successfull. */
  function update() 
  {
    $CI = &get_instance();
    return $CI->publication_db->update($this);
  }
  
  /** Deletes this publication. Returns TRUE or FALSE depending on whether the operation was
   successful. */
  function delete() {
  	$CI = &get_instance();
  	return $CI->publication_db->delete($this);
  }
  
  function getKeywords()
  {
        $CI = &get_instance();
    if ($this->keywords == null)
    {
      $this->keywords = $CI->keyword_db->getKeywordsForPublication($this->pub_id);
    }
    return $this->keywords;
  }
  
  function getAttachments() 
  {
        $CI = &get_instance();
    if ($this->attachments == null) 
    {
        $this->attachments = $CI->attachment_db->getAttachmentsForPublication($this->pub_id);
    }
    return $this->attachments;
  }
  
  function getNotes() 
  {
    $CI = &get_instance();
    if ($this->notes == null) 
    {
        $this->notes = $CI->note_db->getNotesForPublication($this->pub_id);
    }
    return $this->notes;
  }
  
  function getCustomFields()
  {
    $CI = &get_instance();
    if ($this->customfields == null)
    {
      $this->customfields = $CI->customfields_db->getForPublication($this->pub_id);
    }
    return $this->customfields;
  }
  
  /** transparently returns the value of a field. If the given key is a customfield, return the value of this custom field.
  If not, return the field as basic field.... */
  function getFieldValue($key)
  {
    $CI = &get_instance();
    $allkeys = $CI->customfields_db->getCustomFieldKeys('publication');
    if (in_array($key,$allkeys))
    {
      $cfields = $this->getCustomFields();
      foreach ($cfields as $cfield)
      {
        if ($cfield['fieldname']==$key)
          return $cfield['value']; 
      }
    }
    else
    {
      return $this->$key;
    }
    return null;
  }
  
  function getUserMark() 
  {
    $CI = &get_instance();
    $userlogin = getUserLogin();
    return $CI->publication_db->getUserMark($this->pub_id,$userlogin->userId());
  }
  /** read & mark for this user */
  function read($mark) {
    $CI = &get_instance();
    $userlogin = getUserLogin();
    $CI->publication_db->read($mark,$this->getUserMark(),$this->pub_id,$userlogin->userId());
  }
  function unread() {
    $CI = &get_instance();
    $userlogin = getUserLogin();
    $CI->publication_db->unread($this->getUserMark(),$this->pub_id,$userlogin->userId());
  }
  
  function getCleanSource() {
    $summaryfields = getPublicationSummaryFieldArray($this->pub_type);
    foreach ($summaryfields as $key => $prefix) {
      $val = ($key=="month")? formatMonthText($val) : utf8_trim($publication->getFieldValue($key));
      $postfix='';
      if (is_array($prefix)) {
        $postfix = $prefix[1];
        $prefix = $prefix[0];
      }
    }
    return ($val) ? $val : '';
  }
}
?>