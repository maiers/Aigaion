<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** 
 * This class regulates the database access for Authors. 
 * Several accessors are present that return an Author or an array of Authors.
 * 
 * Authors can be known under more than one name, e.g. when they marry, 
 * or when they write their name differently in different papers. 
 * 
 * These aliases are known as "synonyms": 
 * an author can be synonym_of a primary author (stored in the synonym_of variable)
 * 
 * To ensure fast access to the synonym information (needed for large databases > 10k authors)
 * use the following statement as basis:
 * 
 * $CI->db->select('*, (SELECT count(*) FROM '.AIGAION_DB_PREFIX.'author s WHERE s.synonym_of = a.author_id AND s.synonym_of != 0) synonym_count');
 * $CI->db->get('author a');
 *  
 */
class Author_db {
  
  
  function Author_db()
  {
  }

  function getByID($author_id)
  {
	$CI = &get_instance();

	// create a query containing the synonym count for each author
	$CI->db->select('*, (SELECT count(*) FROM '.AIGAION_DB_PREFIX.'author s WHERE s.synonym_of = a.author_id AND s.synonym_of != 0) synonym_count');
	
	//retrieve one author row	  
    $Q = $CI->db->get_where('author a',array('a.author_id' =>$author_id));
    
    if ($Q->num_rows() == 1) 
    {
      //load the author
      return $this->getFromRow($Q->row());
    }
    else 
      return false;
  }
  
  function getByExactName($firstname = "", $von = "", $surname = "", $jr = '')
  {
    //this function cannot operate on the cleanname; because then we can never have two different authors that just differn in e.g. diacritics.
    $CI = &get_instance();
    $CI->load->library('bibtex2utf8');
    $CI->load->helper('utf8_to_ascii');
    //check if there is input, if not fail
    if (!($firstname || $von || $surname || $jr))
      return false;
    
    // create a query containing the synonym count for each author
    $CI->db->select('*, (SELECT count(*) FROM '.AIGAION_DB_PREFIX.'author s WHERE s.synonym_of = a.author_id AND s.synonym_of != 0) synonym_count');
    
    //do the query
    if (getConfigurationSetting('CONVERT_BIBTEX_TO_UTF8')!='FALSE') {
      $Q = $CI->db->get_where('author a',array('firstname' => $CI->bibtex2utf8->bibCharsToUtf8FromString($firstname)
                                         ,'von' => $CI->bibtex2utf8->bibCharsToUtf8FromString($von)
                                         ,'surname' => $CI->bibtex2utf8->bibCharsToUtf8FromString($surname)
                                         ,'jr' => $CI->bibtex2utf8->bibCharsToUtf8FromString($jr)));
    } else {
      $Q = $CI->db->get_where('author a',array('firstname' =>$firstname
                                         ,'von' => $von
                                         ,'surname' => $surname
                                         ,'jr' => $jr));
    }
    
    //only when a single result is found, load the result. Else fail
    if ($Q->num_rows() == 1) {
      return $this->getFromRow($Q->row());
    } else {
      return null;
    }
  }
  
  function setByName($firstname = "", $von = "", $surname = "", $jr = '')
  {
    $CI = &get_instance();
    $CI->load->library('bibtex2utf8');
    //check if there is input, if not fail
    if (!($firstname || $von || $surname || $jr))
      return null;
    
    //pack into array
    if (getConfigurationSetting('CONVERT_BIBTEX_TO_UTF8')!='FALSE') {
      $authorArray = array("firstname" => $CI->bibtex2utf8->bibCharsToUtf8FromString($firstname), "von" => $CI->bibtex2utf8->bibCharsToUtf8FromString($von), "surname" =>$CI->bibtex2utf8->bibCharsToUtf8FromString($surname), "jr" =>$CI->bibtex2utf8->bibCharsToUtf8FromString($jr));
    } else {
      $authorArray = array("firstname" => $firstname, "von" => $von, "surname" => $surname, "jr" => $jr);
    }
    
    //load from array
    return $this->getFromArray($authorArray);
  }
  
  function getFromArray($authorArray)
  {
    return $this->getFromRow($authorArray);
  }
  
  function getFromRow($R)
  {
    $author = new Author;
    foreach ($R as $key => $value)
    {
        $author->$key = $value;
    }
    return $author;
  }
  
  function getFromPost()
  {
    $CI = &get_instance();
    $CI->load->helper('cleanname');
    $CI->load->library('bibtex2utf8');
    //create the array with variables to retrieve
    $fields = array('author_id',
                    //'specialchars', no! specialchars var is not set in edit form.
                    'cleanname',
                    'firstname',
                    'von',
                    'surname',
                    'jr',
                    'email',
                    'url',
                    'institute',
                    'synonym_of'
                   );
    
    $author = new Author;
    
    //retrieve all fields
    foreach ($fields as $key)
    {
      $author->$key = trim($CI->input->post($key));
    }
    
    //fetch custom fields
    $author->customfields = $CI->customfields_db->getFromPost('author');

    //check for specialchars
    $specialfields = array('firstname', 'von', 'surname', 'jr', 'institute');
    if (getConfigurationSetting('CONVERT_BIBTEX_TO_UTF8')!='FALSE') {
      foreach ($specialfields as $field)
      {
        //remove bibchars
        $author->$field = $CI->bibtex2utf8->bibCharsToUtf8FromString($author->$field);
      }
      foreach($author->customfields as $field => $value)
      {
        $author->customfields[$field]['value'] = $CI->bibtex2utf8->bibCharsToUtf8FromString($value['value']);
      }
    }
    
    $author->cleanname = authorCleanName($author);
    return $author;
  }
  
  function getAuthorCount() {
  	$CI = &get_instance();
  	return $CI->db->count_all("author");

  }
  function add($author)
  {
        $CI = &get_instance();
        $CI ->load->library('bibtex2utf8');
        $CI ->load->helper('cleanname');
    //fields that are to be submitted
    $fields = array('specialchars',
                    'cleanname',
                    'firstname',
                    'von',
                    'surname',
                    'jr',
                    'email',
                    'url',
                    'institute',
                    'synonym_of'
                   );
    
    //check for specialchars
    $specialfields = array('firstname', 'von', 'surname', 'jr', 'institute');
    if (getConfigurationSetting('CONVERT_BIBTEX_TO_UTF8')!='FALSE') {
      foreach ($specialfields as $field)
      {
        //remove bibchars
        $author->$field = $CI->bibtex2utf8->bibCharsToUtf8FromString($author->$field);
      }
      foreach($author->getCustomFields() as $field => $value)
      {
        $author->customfields[$field]['value'] = $CI->bibtex2utf8->bibCharsToUtf8FromString($value['value']);
      }
    }
    
    //create cleanname
    $author->cleanname = authorCleanName($author);
    
    //get the data to store in the database
    $data = array();
    foreach($fields as $field)
      $data[$field] = $author->$field;
    
    //insert into database using active record helper
    $CI->db->insert('author', $data);
    
    //update this author's author_id
    $author->author_id = $CI->db->insert_id();
    
    //add custom fields
    $CI->customfields_db->addForID($author->author_id, $author->getCustomFields());

    return $author;
  }
  
  function update($author)
  {
    $CI = &get_instance();
    $CI->load->library('bibtex2utf8');
    $CI->load->helper('cleanname');
    //fields that are to be updated
    $fields = array('specialchars',
                    'cleanname',
                    'firstname',
                    'von',
                    'surname',
                    'jr',
                    'email',
                    'url',
                    'institute'
                   );
    //synonym data only allowed for authors that are not a primary themselves:
    if (!$this->hasSynonyms($author)) $fields[] = 'synonym_of';
    //check for specialchars
    $specialfields = array('firstname', 'von', 'surname', 'jr', 'institute');
    if (getConfigurationSetting('CONVERT_BIBTEX_TO_UTF8')!='FALSE') {
      foreach ($specialfields as $field)
      {
        $author->$field = $CI->bibtex2utf8->bibCharsToUtf8FromString($author->$field);
      }
      foreach($author->getCustomFields() as $field => $value)
      {
        $author->customfields[$field]['value'] = $CI->bibtex2utf8->bibCharsToUtf8FromString($value['value']);
      }
  }
    
    //create cleanname
    $author->cleanname = authorCleanName($author);
    
    //get the data to store in the database
    $data = array();
    foreach($fields as $field)
      $data[$field] = $author->$field;
    
    //update database using active record helper
    $CI->db->where('author_id', $author->author_id);
    $CI->db->update('author', $data);
    
    //update custom fields
    $CI->customfields_db->updateForID($author->author_id, $author->getCustomFields());

    //update 'cleanauthor' for all publications where this author is author
    $relevantPubsQ = $CI->db->get_where('publicationauthorlink',array('author_id'=>$author->author_id));
    foreach ($relevantPubsQ->result() as $relevantPubR)
    {
      //start building up clean author value
      $pubcleanauthor = "";

      $CI->db->order_by('rank');
      $authorsQ = $CI->db->get_where('publicationauthorlink',array('pub_id'=>$relevantPubR->pub_id,'is_editor'=>'N'));
    
      //add authors
      foreach ($authorsQ->result() as $authorsR) 
      {
        $nextAuthor = $this->getByID($authorsR->author_id);
        $pubcleanauthor .= ' '.$nextAuthor->cleanname;
      }

      $CI->db->order_by('rank');
      $editorsQ = $CI->db->get_where('publicationauthorlink',array('pub_id'=>$relevantPubR->pub_id,'is_editor'=>'Y'));
    
      //add editors
      foreach ($editorsQ->result() as $editorsR) 
      {
        $nextEditor = $this->getByID($editorsR->author_id);
        $pubcleanauthor .= ' '.$nextEditor->cleanname;
      }

      //update cleanauthor value
      $CI->db->where('pub_id', $relevantPubR->pub_id);
      $CI->db->update('publication', array('cleanauthor'=>trim($pubcleanauthor)));
    }
    return $author;
  }
  
    /** delete given object. where necessary cascade. Checks for edit and read rights on this object and all cascades
    in the _db class before actually deleting. 
    Returns TRUE or FALSE depending on whether the operation was successful. */
    function delete($author) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        //collect all cascaded to-be-deleted-id's: none
        //check rights
        //check, all through the cascade, whether you can read AND edit that object
        if (!$userlogin->hasRights('publication_edit')) {
            //if not, for any of them, give error message and return
            appendErrorMessage(__('Cannot delete author').': '.__('insufficient rights').'.<br/>');
            return;
        }
        if (empty($author->author_id)) {
            appendErrorMessage(__('Cannot delete author').': '.__('erroneous ID').'.<br/>');
            return;
        }
        //no delete for authors with publications. check through tables, not through object
        $Q = $CI->db->get_where('publicationauthorlink',array('author_id'=>$author->author_id));
        if ($Q->num_rows()>0) {
            appendErrorMessage(__('Cannot delete author').': '.__('still has publications (possibly invisible...). Do you need a quick way to delete all publications for an author? Add them to the bookmarklist, then delete them from there...').'.<br/>');
            return false;
        }
        
        //delete customfields
        $CI->customfields_db->deleteForAuthor($author->author_id);
        
        //otherwise, delete all dependent objects by directly accessing the rows in the table 
        $CI->db->delete('author',array('author_id'=>$author->author_id));
        //delete links
        $CI->db->delete('publicationauthorlink',array('author_id'=>$author->author_id));
        //add the information of the deleted rows to trashcan(time, data), in such a way that at least manual reconstruction will be possible
    }    
     
    
  function validate($author)
  {
        $CI = &get_instance();
    $validate_conditional = array();
    
    //we require at least the first or the surname
    $validate_conditional[] = 'firstname';
    $validate_conditional[] = 'surname';

    $validation_message   = '';
    $conditional_field_text = '';
    $conditional_validation = false;
    foreach ($validate_conditional as $key)
    {
      if (trim($author->$key) != '')
      {
        $conditional_validation = true;
      }
      $conditional_field_text .= $key.", ";
    }
    if (!$conditional_validation)
    {
      $validation_message .= sprintf(__("One of the fields %s is required"),$conditional_field_text).".<br/>\n";
    }
  
    if ($validation_message != '')
    {
      appendErrorMessage(__("Changes not committed").":<br/>\n".$validation_message);
      return false;
    }
    else
      return true;
  }

  function deleteAuthor($author)
  {
        $CI = &get_instance();
    //only delete a valid object
    if ($author->author_id == 0)
      return false;
      
    //remove all links to this author
/*
TODO:
- remove publicationauthorlinks
- remove other (new?) authorlinks
*/
    //remove the actual author
    $this->db->where('author_id', $author->author_id);
    $this->db->delete('author');
    
    //if the delete was succesful, only one single row is affected
    //please note: mysql returns 0 affected rows, CI has a work-around
    //in the db class.
    if ($this->db->affected_rows() == 1)
      return true;
    else
      return false;
  }
  
  function getAllAuthors($include_synonyms = true) 
  {
    $CI = &get_instance();
    $result = array();
    
    //get all authors from the database, order by cleanname
    $CI->db->order_by('cleanname');
    if (!$include_synonyms) {
      $CI->db->where('synonym_of','0');
    }
    
    // create a query containing the synonym count for each author
	$CI->db->select('*, (SELECT count(*) FROM '.AIGAION_DB_PREFIX.'author s WHERE s.synonym_of = a.author_id AND s.synonym_of != 0) synonym_count');
    $Q = $CI->db->get('author a');
    
    //retrieve results or fail
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }
    return $result;
  }
  
  //returns all authors that are visible to the user
  function getAllVisibleAuthors()
  {
    $CI = &get_instance();
    $userlogin=getUserLogin();
    
    if ($userlogin->hasRights('read_all_override'))
      return $this->getAllAuthors();
      
    if ($userlogin->isAnonymous()) //get only public authors
    {
      # query optimized by KK (works when public publications are much fewer than non-public ones)
      $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."author.* FROM ".AIGAION_DB_PREFIX."author
                           INNER JOIN (SELECT ".AIGAION_DB_PREFIX."publicationauthorlink.author_id FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."publicationauthorlink
                                       WHERE ".AIGAION_DB_PREFIX."publication.derived_read_access_level = 'public'
                                       AND ".AIGAION_DB_PREFIX."publication.pub_id = ".AIGAION_DB_PREFIX."publicationauthorlink.pub_id
                           ) AS ".AIGAION_DB_PREFIX."publicauthors USING (author_id)
                           ORDER BY ".AIGAION_DB_PREFIX."author.cleanname;");
    }
    else //get all non-private authors and authors for publications that belong to the user
    {
      $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."author.* FROM ".AIGAION_DB_PREFIX."author, ".AIGAION_DB_PREFIX."publicationauthorlink, ".AIGAION_DB_PREFIX."publication
                           WHERE (".AIGAION_DB_PREFIX."publication.derived_read_access_level != 'private' 
                                  OR ".AIGAION_DB_PREFIX."publication.user_id = ".$userlogin->userId().")
                           AND ".AIGAION_DB_PREFIX."publicationauthorlink.pub_id = ".AIGAION_DB_PREFIX."publication.pub_id 
                           AND ".AIGAION_DB_PREFIX."author.author_id = ".AIGAION_DB_PREFIX."publicationauthorlink.author_id
                           ORDER BY ".AIGAION_DB_PREFIX."author.cleanname");
    }
    
    //retrieve results or fail                       
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }
    return $result;
    
  }  
  
  function getAuthorsLike($cleanname)
  {
    $CI = &get_instance();
    //select all authors from the database where the cleanname begins with the characters
    //as given in $cleanname
    $CI->db->order_by('cleanname');
    $CI->db->like('cleanname',$cleanname);
    $Q = $CI->db->get('author');
    
    //retrieve results or fail
    $result = array();
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }
    
    return $result;

  }
  function getVisibleAuthorsLike($cleanname)
  {
    $CI = &get_instance();
    $userlogin = getUserLogin();
    
    //select all authors from the database where the cleanname begins with the characters
    //as given in $cleanname
    if ($userlogin->hasRights('read_all_override'))
      return $this->getAuthorsLike($cleanname);
      
    if ($userlogin->isAnonymous()) //get only public authors
    {
      $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."author.* FROM ".AIGAION_DB_PREFIX."author, ".AIGAION_DB_PREFIX."publicationauthorlink, ".AIGAION_DB_PREFIX."publication
                           WHERE ".AIGAION_DB_PREFIX."author.cleanname LIKE ".$CI->db->escape('%'.$cleanname.'%')."
                           AND ".AIGAION_DB_PREFIX."publication.derived_read_access_level = 'public'
                           AND ".AIGAION_DB_PREFIX."publicationauthorlink.pub_id = ".AIGAION_DB_PREFIX."publication.pub_id 
                           AND ".AIGAION_DB_PREFIX."author.author_id = ".AIGAION_DB_PREFIX."publicationauthorlink.author_id
                           ORDER BY ".AIGAION_DB_PREFIX."author.cleanname");
    }
    else //get all non-private authors and authors for publications that belong to the user
    {
      $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."author.* FROM ".AIGAION_DB_PREFIX."author, ".AIGAION_DB_PREFIX."publicationauthorlink, ".AIGAION_DB_PREFIX."publication
                           WHERE ".AIGAION_DB_PREFIX."author.cleanname LIKE ".$CI->db->escape('%'.$cleanname.'%')."
                           AND (".AIGAION_DB_PREFIX."publication.derived_read_access_level != 'private' 
                                  OR ".AIGAION_DB_PREFIX."publication.user_id = ".$userlogin->userId().")
                           AND ".AIGAION_DB_PREFIX."publicationauthorlink.pub_id = ".AIGAION_DB_PREFIX."publication.pub_id 
                           AND ".AIGAION_DB_PREFIX."author.author_id = ".AIGAION_DB_PREFIX."publicationauthorlink.author_id
                           ORDER BY ".AIGAION_DB_PREFIX."author.cleanname");
    }
    
    //retrieve results or fail
    $result = array();
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }
    
    return $result;
  }
  
  function getForPublication($pub_id, $is_editor = 'N')
  {
    $CI = &get_instance();
    $result = array();
    
    //retrieve authors and editors
    $Q = $CI->db->query("SELECT ".AIGAION_DB_PREFIX."author.* FROM ".AIGAION_DB_PREFIX."author, ".AIGAION_DB_PREFIX."publicationauthorlink 
                           WHERE ".AIGAION_DB_PREFIX."author.author_id = ".AIGAION_DB_PREFIX."publicationauthorlink.author_id
                           AND ".AIGAION_DB_PREFIX."publicationauthorlink.pub_id = ".$CI->db->escape($pub_id)."
                           AND ".AIGAION_DB_PREFIX."publicationauthorlink.is_editor = ".$CI->db->escape($is_editor)."
                           ORDER BY ".AIGAION_DB_PREFIX."publicationauthorlink.rank");
    
    //retrieve results or fail                       
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }

    return $result;
  }
  
  function getPublicationCount($author_id) {
    $CI = &get_instance();
    $CI->db->select("author_id");
    $CI->db->distinct();
    $CI->db->where(array('author_id'=>$author_id));
    $CI->db->from("publicationauthorlink");
    return $CI->db->count_all_results();
  } 

  /** ensure that all authors in this list are in the database. Also, remove duplicate authors from the list. */  
  function ensureAuthorsInDatabase($authors)
  {
    $CI = &get_instance();
    if (!is_array($authors))
      return null;
      
    $tempresult = array();
    foreach ($authors as $author)
    {
      $current      = $this->getByExactName($author->firstname, $author->von, $author->surname, $author->jr);
      if ($current == null)
        $current    = $this->add($author);
      
      $tempresult[] = $current;
    }
    //silently drop duplicate authors from list
    $authorids = array();
    $authors = array();
    foreach ($tempresult as $a)
    {
      if (!in_array($a->author_id,$authorids))
      {
        $authorids[] = $a->author_id;
        $authors[] = $a;
      }
      else
      {
        appendMessage(__("Dropped duplicate author")."<br/>");
      }
    }

    return $authors;
  }
  
  /** if no similar authors found, returns null.
  else, returns an array with two items:
   [] a review message
   [] an array of arrays of similar author ids (for each author)
  */
  function review($authors)
  {
    $CI = &get_instance();
    $CI->load->helper('utf8_to_ascii');
    if (!is_array($authors))
      return null;
    
    $result_message   = "";
    $all_similar_authors = array();
    
    //get database author array
    $CI->db->select('author_id, cleanname');
    $CI->db->order_by('cleanname');
    $Q = $CI->db->get('author');
    
    $db_cleanauthors = array();
    //retrieve results or fail                       
    foreach ($Q->result() as $R)
    {
      $db_cleanauthors[$R->author_id] = strtolower($R->cleanname); //why strtolower? because we want to check case insensitive.
    }
    
    
    //check availability of the authors in the database
    foreach ($authors as $author)
    {
      $similar_authors = array();
      if ($this->getByExactName($author->firstname, $author->von, $author->surname, $author->jr) == null)
      {
        //no exact match, or more than one authors exist in the database
        
        //check on cleanname
        //create cleanname
        $author->cleanname = strtolower(authorCleanName($author));
        $db_distances = array();
        foreach ($db_cleanauthors as $author_id => $db_author)
        {
          $distance = levenshtein($db_author, $author->cleanname);
          if (($distance < 3) && ($author_id != $author->author_id))
            $db_distances[$author_id] = $distance;
        }
        
        //sort while keeping key relationship
        asort($db_distances, SORT_NUMERIC);
        //are there similar authors?
        if (count($db_distances) > 0)
        {
          $authorname = $author->getName('lvf');
          if ($author->institute!='')
          {
            $authorname .= ', '.addslashes ($author->institute);
          }
          if ($author->email!='')
          {
            $authorname .= ', '.addslashes ($author->institute);
          }

          $result_message .= __("Found similar authors for")." <b>&quot;".$authorname."&quot;</b>:<br/>\n";
          $result_message .= "<ul>\n";
          foreach($db_distances as $key => $value)
          {
            $otherauthor = $this->getByID($key);
            $otherauthorname = $otherauthor->getName('lvf');
            if ($otherauthor->institute!='')
            {
              $otherauthorname .= ', '.addslashes ($otherauthor->institute);
            }
            if ($otherauthor->email!='')
            {
              $otherauthorname .= ', '.addslashes ($otherauthor->institute);
            }
            $result_message .= "<li>".$otherauthorname."</li>\n";
            $similar_authors[] = $otherauthor->author_id;
          }
          $result_message .= "</ul>\n";
        }
      } else {
        //exact match! this author exists!
      }
      $all_similar_authors[] = $similar_authors;
    }
    if ($result_message != "")
    {
      $result_message .= __("Please review the entered authors").".<br/>\n";
      return array($result_message,$all_similar_authors);
    }
    else
      return null;
  }

  /** returns a list of similar authors (possibly empty), method depends on site setting */
  //The method does only check main authors, synonyms are not checked.
  function getSimilarAuthors($author) {
    $userlogin = getUserLogin();
    if ($userlogin->getPreference('similar_author_test')=='il') 
    {
    //appendMessage('il');
      return $this->getSimilarAuthors2($author); //on initial lastname "il" 
    }
    else
    {
    //appendMessage('c');
      return $this->getSimilarAuthors1($author); //on cleanname "c"
    }
    
  }
  /** returns a list of similar authors (possibly empty), on cleanname */
  //The method does only check main authors, synonyms are not checked.
  function getSimilarAuthors1($author) {
    $result = array();
    
    //return when this is a synonym of a main author.
    if ($author->synonym_of != '0')
      return $result;
      
    $CI = &get_instance();
    $CI->load->helper('utf8_to_ascii');
    
    //get database author array
    $CI->db->select('author_id, cleanname');
    
    //do not return synonyms of this author
    $CI->db->where('synonym_of !=', $author->author_id);
    $CI->db->order_by('cleanname');
    $Q = $CI->db->get('author');
    
    $db_cleanauthors = array();
    //retrieve results or fail    
    foreach ($Q->result() as $R)
    {
      $db_cleanauthors[$R->author_id] = strtolower($R->cleanname); //why strtolower? because we want to check case insensitive.
    }
    //check on cleanname
    //create cleanname
    
    $cleanAuthorName = strtolower($author->cleanname);
    
    if (sizeof($cleanAuthorName) < 4)
      $dist_threshold = 2;
    else if (sizeof($cleanAuthorName) < 8)
      $dist_threshold = 3;
    else
      $dist_threshold = 4;
    
    $db_distances = array();
    foreach ($db_cleanauthors as $author_id => $db_author)
    {
      $distance = levenshtein($db_author, $cleanAuthorName);
      if (($distance < $dist_threshold) && ($author_id != $author->author_id))
        $db_distances[$author_id] = $distance;
    }
    
    //sort while keeping key relationship
    asort($db_distances, SORT_NUMERIC);
    
    foreach($db_distances as $key => $value)
    {
      $result[]= $this->getByID($key);
    }
    return $result;
  }
  /** returns a list of similar authors (possibly empty), on first initial.
   * By Øyvind.   */
  //The method does only check main authors, synonyms are not checked.
  function getSimilarAuthors2($author) {
    $result = array();
    
    //Return when this is a synonym of a main author
    if ($author->synonym_of != '0')
      return $result;
    
    
    $CI = &get_instance();
    $CI->load->helper('utf8_to_ascii');



    //get database author array
    $CI->db->select('author_id, cleanname, surname, firstname');
    //do not return synonyms of this author
    $CI->db->where('synonym_of !=', $author->author_id);
    
    $CI->db->order_by('surname');
    $Q = $CI->db->get('author');

    $db_cleanauthors = array();
    //retrieve results or fail
    foreach ($Q->result() as $R)
    {
      $db_cleanauthors[$R->author_id] = array();
      $db_cleanauthors[$R->author_id][0] = strtolower($R->surname); //why strtolower? because we want to check case insensitive.
      $db_cleanauthors[$R->author_id][1] = strtolower($R->firstname);
      $db_cleanauthors[$R->author_id][2] = strtolower($R->surname) . ", " . strtolower($R->firstname);
    }
    //check on cleanname
    //create cleanname

    $cleanAuthorName = strtolower($author->cleanname);
    
    $db_distances = array();
    foreach ($db_cleanauthors as $author_id => $db_author)
    {
      $distance = levenshtein($db_author[2], $cleanAuthorName);
      if (($distance < 3) && ($author_id != $author->author_id) && substr($db_author[1],0,1) == strtolower(substr($author->firstname,0,1)))
      {
				$db_distances[$author_id] = $distance;
      }
    }

    //sort while keeping key relationship
    asort($db_distances, SORT_NUMERIC);

    foreach($db_distances as $key => $value)
    {
      $result[]= $this->getByID($key);
    }
    return $result;
  }
  //this function steals the publications and kills the similar author.
  //note that we should NOT steal the publications of any SYNONYMS of the source, but the synonyms should be retargeted to the target
  function merge($author, $simauthor_id) {
    $CI = &get_instance();
    //0) get author and simauthor, to verify synonym information! 
    //a synonym can ONLY be merged with its primary as TARGET.
    $simauthor = $CI->author_db->getByID($simauthor_id);
    if ($author->synonym_of != '0')
    {
      appendErrorMessage(__("You cannot merge authors with a synonym author as target").'.<br/>');
      return;
    }
    if (($simauthor->synonym_of != '0') && ($simauthor->synonym_of != $author->author_id))
    {
      appendErrorMessage(__("An author synonym can only be merged with the corresponding primary author").'.<br/>');
      return;
    }
    
    //1) get all publications of old, similar author, one by one.
    $pubs = $CI->publication_db->getForAuthor($simauthor_id);
    //2) reassign the publication, if appropriate rights 
    foreach ($pubs as $pub) {
        if ($CI->accesslevels_lib->canEditObject($pub)) { //reasign this publication, directly in database tables
            $transfer = true;
            //was the new author not already an author for this publications?
            foreach($pub->authors as $a) {
                if ($a->author_id == $author->author_id) {
                    $transfer = false;
                }
            }
            if (!$transfer) {
                //remove link for similarauthor
                $CI->db->delete('publicationauthorlink',array('author_id'=>$simauthor_id,'pub_id'=>$pub->pub_id));
            } else {
                //transfer link for similarauthor
                $CI->db->update('publicationauthorlink',array('author_id'=>$author->author_id),array('author_id'=>$simauthor_id,'pub_id'=>$pub->pub_id));
            }
        }
    }
    //3) look if table contains any publication for old author (might have been inaccessible for you!)
    //   if all publications successfully reassigned, kill old similar author, else give warning
    $remainingPubsQ = $CI->db->get_where('publicationauthorlink',array('author_id'=>$simauthor_id));
    if ($remainingPubsQ->num_rows() > 0) {
        appendErrorMessage(__('There are some publications that could not be reassigned due to access rights'));
    } else {
        $CI->db->delete('author',array('author_id'=>$simauthor_id));
    }
    //4) reassign all synonyms of old author  to new author
    $CI->db->update('author',array('synonym_of'=>$author->author_id),array('synonym_of'=>$simauthor_id));
  }
  
  function getKeywordsForAuthor($author_id) {
    # get keywords for this topic
    $CI = &get_instance();
    $query = "SELECT DISTINCT ".AIGAION_DB_PREFIX."keywords.keyword_id, COUNT(".AIGAION_DB_PREFIX."keywords.keyword_id) AS sum
    FROM ".AIGAION_DB_PREFIX."keywords, ".AIGAION_DB_PREFIX."publicationauthorlink, ".AIGAION_DB_PREFIX."publicationkeywordlink
    WHERE ".AIGAION_DB_PREFIX."publicationauthorlink.author_id = ".$CI->db->escape($author_id)."
    AND ".AIGAION_DB_PREFIX."publicationauthorlink.pub_id = ".AIGAION_DB_PREFIX."publicationkeywordlink.pub_id
    AND ".AIGAION_DB_PREFIX."publicationkeywordlink.keyword_id = ".AIGAION_DB_PREFIX."keywords.keyword_id
    GROUP BY ".AIGAION_DB_PREFIX."keywords.keyword_id ORDER BY ".AIGAION_DB_PREFIX."keywords.cleankeyword";

    $Q = $CI->db->query($query);
    $result = array();
    foreach ($Q->result() as $R) {
        $keyword = $CI->keyword_db->getByID($R->keyword_id);
        $keyword->count = $R->sum;
        $result[] = $keyword;
        //$result[] = $CI->keyword_db->getByID($R->keyword_id);
    }        
    return $result;
  }
  
  //this function returns a list of synonyms for a given author.
  //when the given author is a synonym itself, first the main author id is found.
  //the main author itself is not part of the result list, unless $includePrimary==true.
  function getSynonymsForAuthor($author_id, $include_primary=false) {
    $CI = &get_instance();
    $result = array();
    
    #check whether this is the primary author:
    $author = $this->getByID($author_id);
    
    #check if this is the primary author. If not, fetch the primary author first.
    if ($author->synonym_of != '0') { 
      $author_id  = $author->synonym_of;
      $author    = $this->getByID($author_id);
    }

    #add primary to list only if requested
    if ($include_primary)
    {
      $result[] = $author;
    }
    
    
    //now we have the primary author id, we check if it has any synonyms
    $query = "SELECT DISTINCT ".AIGAION_DB_PREFIX."author.author_id
    FROM ".AIGAION_DB_PREFIX."author
    WHERE ".AIGAION_DB_PREFIX."author.synonym_of = ".$CI->db->escape($author_id);

    $Q = $CI->db->query($query);
    
    foreach ($Q->result() as $R) {
        $author = $this->getByID($R->author_id);
        $result[] = $author;
    }        
    return $result;
  }
  
  /**
   set given author as primary of a set of synonyms; old primary (if any) becomes synonym of given author
   */
  function setPrimary($author) 
  {
    $CI = &get_instance();
    if ($author == null) return;
    if ($author->synonym_of=='0') return;
    $syns = $this->getSynonymsForAuthor($author->author_id, false);
    $prim = $this->getByID($author->synonym_of);
    //for new primary: set synonym-link to 0
    $author->synonym_of = '0';
    $author->update();
    foreach ($syns as $syn)
    {
      //for all other synonyms: set link to new primary and update
      if ($syn->author_id != $author->author_id)
      {
        $syn->synonym_of = $author->author_id;
        $syn->update();
      }
    }
    //and finaly, set old primary as alias for new primary (cannot be done earlier, since primary authors cannot be set to be an alias)
    $prim->synonym_of = $author->author_id;
    $prim->update();

  }
  
  /** set syn_author to be a synonym of $author
  BUT:
  - if author and syn_author are the same, refuse
  - if syn_author is a primary itself, refuse
  - if author is a syn itself -> redirect to its primary
  */
  function addSynonymForAuthor($author, $syn_author)
  {
    $CI = &get_instance();
    if ($author == null) return;
    if ($author->author_id==$syn_author->author_id)
    {
      appendErrorMessage(__("Cannot set author as synonym of itself").'.<br/>');
      return;
    }
    if ($this->hasSynonyms($syn_author)) 
    {
      appendErrorMessage(__("Cannot set author as synonym, because it already has synonyms itself").'.<br/>');
      return;
    }
    if ($author->synonym_of!='0')
    {
      $this->addSynonymForAuthor($CI->author_db->getByID($author->synonym_of),$syn_author);
    }
    $syn_author->synonym_of = $author->author_id;
    $syn_author->update();
  }
  
  /** return true if this author is set as synonym for other authors */
  function hasSynonyms($author)
  {
    $CI = &get_instance();
    $CI->db->select("*");
    $CI->db->distinct();
    $CI->db->where(array('synonym_of'=>$author->author_id));
    $CI->db->from("author");
	  return ($CI->db->count_all_results() > 0);
  }
}
?>
