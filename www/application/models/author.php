<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** 
 * This class holds the data structure of an author.
 * 
 * Database access for Authors is done through the author_db library
 * a lot of function documentation can be found there 
 * 
 */
class Author extends CI_Model {

  //system vars
  var $author_id    = 0;
  var $specialchars = 'FALSE';
  var $cleanname    = '';
  var $synonym_of   = '0';
  var $synonym_count = '0';

  //user vars
  var $firstname    = '';
  var $von          = '';
  var $surname      = '';
  var $jr           = '';
  var $email        = '';
  var $url          = '';
  var $institute    = '';

  var $customfields = null; //NOTE: this array is NOT directly accessible, but should ALWAYS be accessed through getCustomFields()

  //class constructor
  function __construct()
  {
    parent::__construct();
    $CI =&get_instance();
  }

  //getName returns the author name, formatted according to the user's preference
  function getName($style = '')
  {
    //if no style is given, get style from user preference
    if ($style == '') {
        $userlogin = getUserLogin();
        $style = $userlogin->getPreference('authordisplaystyle');
    }
        
    switch($style) {
      case 'fvl':   //first von last
      $name = $this->firstname;
      if ($this->von != '')
      ($name != '') ? $name .= " ".$this->von : $name = $this->von;

      if ($this->surname != '')
      ($name != '') ? $name .= " ".$this->surname : $name = $this->surname;

      if ($this->jr != '')
      ($name != '') ? $name .= ", ".$this->jr : $name = $this->jr;

      return $name;
      break;

      case 'vlf':   //von last, first
      $name = $this->von;
      if ($this->surname != '')
      ($name != '') ? $name .= " ".$this->surname : $name = $this->surname;

      if ($this->jr != '') {
        ($name != '') ? $name .= ", ".$this->jr : $name = $this->jr;
        if ($this->firstname == '') $name .= ", {}"; //make sure that even without first name, the jr is preserved as jr and does not become a firstname on subsequent import
      }

      if ($this->firstname != '')
      ($name != '') ? $name .= ", ".$this->firstname : $name = $this->firstname;

      return $name;
      break;

      case 'vl':    //von last
      $name = $this->von;
      if ($this->surname != '')
      ($name != '') ? $name .= " ".$this->surname : $name = $this->surname;

      return $name;
      break;

      default:      //last, von, first
      $name = $this->surname;
      if ($this->von != '')
      ($name != '') ? $name .= ", ".$this->von : $name = $this->von;

      if ($this->firstname != '')
      ($name != '') ? $name .= ", ".$this->firstname : $name = $this->firstname;

      return $name;
      break;
    }
  }

/** [DR: is this function used anywhere? */
function format($formatStyle, $data='')
  {
    //if no data are passed, use $this->data
    if ($data == '')
      $data = $this->data;
    else
    {
      //cleanup and assign new data. $data should be of the Author class type.
      $this->_clearData();
      $this->data = $data;
    }
    
    //only format if there are special characters in the data
    //TODO: that's no longer true; everything is in UTF8 so there are no specialchars in that sense.
    if ($data->specialchars == 'TRUE')
    {  
      //the only fields where special characters should be formatted:
      $fields = array(  'firstname',
                        'von',
                        'jr',
                        'surname',
                        'institute'
                      );

      //TODO: FORMATTING, FOR DIFFERENT FORMATTING STYLES                      
      foreach ($fields as $field)
      {
        $this->data->$field = $data->$field;
      }
    }
  }
  
  /** Add a new author with the given data. Returns TRUE or FALSE depending on whether the operation was
  successfull. After a successfull 'add', $this->author_id contains the new author_id. */
  function add() {
    $this->author_id = $CI->author_db->add($this);
    if ($this->author_id > 0) {
      return True;
    }
    return False;
  }

  /** Update the changes in the data of this author. Returns TRUE or FALSE depending on whether the operation was
  successfull. */
  function update() {
      $CI = &get_instance();
    return $CI->author_db->update($this);
  }

  function delete() {
      $CI = &get_instance();
      return $CI->author_db->delete($this);
  }
  
  /** see author_db for documentation! */
  function getSimilarAuthors() {
      $CI = &get_instance();
      return $CI->author_db->getSimilarAuthors($this);
  }
  
  function merge($simauthor_id) {
      $CI = &get_instance();
      $CI->author_db->merge($this,$simauthor_id);
  }
  
  function getKeywords() {
      $CI = &get_instance();
      return $CI->author_db->getKeywordsForAuthor($this->author_id);
  }
  
  function getSynonyms($include_primary=false) {
      $CI = &get_instance();
      return $CI->author_db->getSynonymsForAuthor($this->author_id,$include_primary);
  }
  
  function setPrimary()
  {
      $CI = &get_instance();
      $CI->author_db->setPrimary($this);
  }
  
  function addSynonym($syn_author)
  {
      $CI = &get_instance();
      $CI->author_db->addSynonymForAuthor($this,$syn_author);
  }
  
  function hasSynonyms()
  {
      return $this->synonym_count > 0;
  }
  
  function getCustomFields()
  {
    $CI = &get_instance();
    if ($this->customfields == null)
    {
      $this->customfields = $CI->customfields_db->getForAuthor($this->author_id);
    }
    return $this->customfields;
  }

}
?>