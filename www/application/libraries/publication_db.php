<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** This class regulates the database access for Publications. Several accessors are present that return a Publication or
array of Publications. */
class Publication_db {

  /* if neither $suppressMerge is set nor $enforceMerge, merging is determined by the configuration setting */
  
  /** set this to true if you don't want any merges to occur. (Merge: copy crossref info into publication object) */
  var $suppressMerge = False;
  /** set this to true if you want to enforce merges. (Merge: copy crossref info into publication object) */
  var $enforceMerge = False;

  function Publication_db()
  {
  }

  /** Return the Publication object with the given id, or null if insufficient rights */
  function getByID($pub_id)
  {
        $CI = &get_instance();
    //retrieve one publication row
    $Q = $CI->db->get_where('publication',array('pub_id'=>$pub_id));

    if ($Q->num_rows() > 0)
    {
      //load the publication
      return $this->getFromRow($Q->row());
    }
    else
    return null;
  }
  
  /** Return the Publication object with the given bibtex_id, or null if insufficient rights */
  function getByBibtexID($bibtex_id)
  {
        $CI = &get_instance();
    //retrieve one publication row
    $Q = $CI->db->get_where('publication',array('bibtex_id'=>$bibtex_id));

    if ($Q->num_rows() > 0)
    {
      //load the publication
      return $this->getFromRow($Q->row());
    }
    else
    return null;
  }
  
  function getFromArray($pub_array)
  {
    //load publication, since an array handles the same as a row we call getFromRow
    return $this->getFromRow($pub_array);
  }

  /** Return the Publication object stored in the given database row, or null if insufficient rights. */
  function getFromRow($R)
  {
    $CI = &get_instance();
    $publication = new Publication;
    foreach ($R as $key => $value)
    {
      $publication->$key = $value;
    }
    
    $userlogin  = getUserLogin();
    //check rights; if fail: return null
    if ( !$CI->accesslevels_lib->canReadObject($publication))return null;
    

    //TODO: CHECK MERGE SETTING FOR PUBLICATIONS
    //check if we have to merge this publication with a crossref entry
    $do_merge = false;
    if ($R->crossref != "")
    {
      //there is a crossref in this publication. Check if we already have a crossref_cache
      //the crossref_cache is initialized in the publication_list model and is only relevant
      //in lists.
      $has_cache = isset($this->crossref_cache);
      if ($has_cache)
      {
        //there is a cache, check if we can merge from the cache.
        //we signal this by setting the $merge_row
        if (array_key_exists($R->crossref, $this->crossref_cache))
        {
          $merge_row = $this->crossref_cache[$R->crossref];
          $do_merge  = true;
        }
      }

      //check if we found the publication in the cache, if not, retrieve from db.
      if (!isset($merge_row))
      {
        $Q = $CI->db->get_where('publication',array('bibtex_id'=>$R->crossref));

        //if we retrieved one single row, we retrieve it and set the $do_merge flag
        if ($Q->num_rows() == 1)
        {
          $merge_row = $Q->row();

          //if we have a cache, store this row in the cache
          if ($has_cache)
          {
            $this->crossref_cache[$R->crossref] = $merge_row;
          }
          $do_merge     = true;
        }
      }
    } //end of crossref retrieval. If we need to merge, this is now signaled in $do_merge

    /* if neither $suppressMerge is set nor $enforceMerge, merging is determined by the configuration setting */
    $do_merge =    $do_merge 
                && !$this->suppressMerge
                && (    $this->enforceMerge 
                     || (getConfigurationSetting('PUBLICATION_XREF_MERGE')=='TRUE')
                    );
                    
    if ($do_merge)
    {
      //copy the row to the publication object. If the original row is empty, retrieve the info
      //from the crossref merge row.
      foreach ($R as $key => $value)
      {
        if ($value != '')
        {
          $publication->$key = $value;
        }
        else
        {
          if($key != 'bibtex_id') //don't copy the bibtex key
            $publication->$key = $merge_row->$key;
        }
      }
    }
    else //no merge
    {
      //copy the row to the publication object
      foreach ($R as $key => $value)
      {
        $publication->$key = $value;
      }
    }
    
    //change report_type in type
    if (isset($publication->report_type))
    {
      $publication->type = $publication->report_type;
      unset($publication->report_type);
    }

    ////////////// End of crossref merge //////////////


    //retrieve authors and editors
    $publication->authors = $CI->author_db->getForPublication($R->pub_id, 'N');
    $publication->editors = $CI->author_db->getForPublication($R->pub_id, 'Y');
    
    if (count($publication->authors)==0 && $do_merge) { //yes, those too...
        $publication->authors = $CI->author_db->getForPublication($merge_row->pub_id, 'N');
    }
    if (count($publication->editors)==0 && $do_merge) {
        $publication->editors = $CI->author_db->getForPublication($merge_row->pub_id, 'Y');
    }
        
        
    //check if this publication was bookmarked by the logged user
    //$Q = $CI->db->query("SELECT * FROM ".AIGAION_DB_PREFIX."userbookmarklists WHERE user_id=".$userlogin->userId()." AND pub_id=".$R->pub_id);
    $Q = $CI->db->get_where('userbookmarklists',array('user_id'=>$userlogin->userId(),'pub_id'=>$R->pub_id));
    if ($Q->num_rows()>0) {
        $publication->isBookmarked = True;
    }
    
    return $publication;
  }

  //if fromImport is true, the authors are availabele as text field instead of as collapsed set of author_ids!
  function getFromPost($suffix = "", $fromImport = False)
  {
    $CI = &get_instance();
    //we retrieve the following fields
    $fields = array('pub_id',
    'user_id',
    'pub_type',
    'bibtex_id',
    'title',
    'year',
    'month',
    //'firstpage',
    //'lastpage',
    'pages',
    'journal',
    'booktitle',
    'edition',
    'series',
    'volume',
    'number',
    'chapter',
    'publisher',
    'location',
    'institution',
    'organization',
    'school',
    'address',
    'type', //former report_type
    'howpublished',
    'note',
    'abstract',
    'issn',
    'isbn',
    'url',
    'doi',
    'crossref',
    'namekey',
    'userfields',
    'keywords',
    'status'
    //'authors',
    //'editors'
    );

    $publication = new Publication;


    foreach ($fields as $key)
    {
      $publication->$key = trim($CI->input->post($key.$suffix));
    }
    
    //fetch custom fields
    $publication->customfields = $CI->customfields_db->getFromPost('publication');

    //parse the keywords
    if ($publication->keywords)
    {
      $keywords = preg_replace('/ *([^,;]+)/',
  						                 "###\\1",
  						                 $publication->keywords);
  						
      $keywords = explode('###', $keywords);
      
        //NOTE: this will give problems when our data is in UTF8, due to substr and strlen. Don't forget to check!
      foreach ($keywords as $keyword)
      {
        if (trim($keyword) != '')
        {
          if ((substr($keyword, -1, 1) == ',') || (substr($keyword, -1, 1) == ';'))
            $keyword = substr($keyword, 0, strlen($keyword) - 1);
          
          $kw->keyword = $keyword;
          $keyword_array[] = $kw;
          unset($kw);
        }
      }
      $publication->keywords = $keyword_array;
    }
    if (isset($publication->month) && ($publication->month!="")) 
    {
    
      //parse month from bibtex
      $CI->load->library('parser_import');
      $CI->load->library('parseentries');
      $CI->parseentries->reset();//autsj... the parseentries remebered the old data, so when importing a long list of pubs it kept importing the month of the first one all the time!! :(
      //appendMessage("@article{,month=".$publication->month."}");
      $CI->parser_import->loadData("@article{,month=".$publication->month."}");
      //appendMessage($publication->month."_");
      $CI->parseentries->expandMacro = TRUE;
      $CI->parseentries->removeDelimit = TRUE;
      
      $CI->parser_import->parse($CI->parseentries);
      $pubs = $CI->parser_import->getPublications();
      if (isset($pubs[0])) 
      {
        $publication->month= $pubs[0]->month;
      }
      //appendMessage($publication->month."<br>");

    }    
    if (!$fromImport) {
        //parse the authors
        $selectedauthors = $CI->input->post('pubform_authors');
        $authors          = array();
        if (trim($selectedauthors)!='') {
            $author_ids = explode(',',$selectedauthors);
            foreach ($author_ids as $author_id) {
                if ($author_id==null || trim($author_id)=='')continue;
                $next = $CI->author_db->getByID($author_id);
                if ($next!=null)
                    $authors[] = $next;
            }
        }
        $publication->authors = $authors;
    
        //parse the editors
        $selectededitors = $CI->input->post('pubform_editors');
        $editors         = array();
        if (trim($selectededitors)!='') {
            $editor_ids = explode(',',$selectededitors);
            foreach ($editor_ids as $editor_id) {
                if ($editor_id==null || trim($editor_id)=='')continue;
                $next = $CI->author_db->getByID($editor_id);
                if ($next!=null)
                    $editors[] = $next;
            }
        }
        $publication->editors = $editors;
    } else {
        //data comes from import review, so authors and editors are present in a special way, as specified in the import/review.php view...
        //parse the authors
        //1) get authorcount
        $authorsCount = $CI->input->post('authorcount'.$suffix);
        $authors = array();
        //2) for each available author
        for ($j = 0; $j < $authorsCount; $j++) 
        {
            //a) get 'alternative' value
            $authorAlternativeRadio = $CI->input->post('author'.$suffix.'_'.$j.'_alternative');
            //b) determine whether to make new author or to use existing
            if ($authorAlternativeRadio == -1) {
                //c) create new from original input data
                $authors[] = $CI->author_db->setByName($CI->input->post('author'.$suffix.'_'.$j.'_inputfirst'), 
                                                       $CI->input->post('author'.$suffix.'_'.$j.'_inputvon'),
                                                       $CI->input->post('author'.$suffix.'_'.$j.'_inputlast'),
                                                       $CI->input->post('author'.$suffix.'_'.$j.'_inputjr'));
            } else {
                //use existing
                $authors[] = $CI->author_db->getByID($authorAlternativeRadio);
            }
        }
        $publication->authors = $authors;

        //parse the editors
        //1) get editorcount
        $editorsCount = $CI->input->post('editorcount'.$suffix);
        $editors = array();
        //2) for each available author
        for ($j = 0; $j < $editorsCount; $j++) 
        {
            //a) get 'alternative' value
            $editorAlternativeRadio = $CI->input->post('editor'.$suffix.'_'.$j.'_alternative');
            //b) determine whether to make new editor or to use existing
            if ($editorAlternativeRadio == -1) {
                //c) create new from original input data
                $editors[] = $CI->author_db->setByName($CI->input->post('editor'.$suffix.'_'.$j.'_inputfirst'), 
                                                       $CI->input->post('editor'.$suffix.'_'.$j.'_inputvon'),
                                                       $CI->input->post('editor'.$suffix.'_'.$j.'_inputlast'),
                                                       $CI->input->post('editor'.$suffix.'_'.$j.'_inputjr'));
            } else {
                //use existing
                $editors[] = $CI->author_db->getByID($editorAlternativeRadio);
            }
        }
        $publication->editors = $editors;


        $editorsFromForm = $CI->input->post('editors'.$suffix);
        if ($editorsFromForm)
        {
          $authors_array    = $CI->parsecreators->parse(preg_replace('/[\r\n\t]/', ' and ', $editorsFromForm));
          $authors          = array();
          foreach ($authors_array as $author)
          {
            $author_db      = $CI->author_db->getByExactName($author['firstname'], $author['von'], $author['surname'], $author['jr']);
            if ($author_db != null)
            {
              $authors[]      = $author_db;
            }
            else
            {
              $author_db     = $CI->author_db->setByName($author['firstname'], $author['von'], $author['surname'], $author['jr']);
              $authors[]  = $author_db;
            }
          }
    
          $publication->editors = $authors;
        }        
    }
    return $publication;
  }

    /** Return an array of Publication objects that crossref the given publication. 
    Will return only accessible publications (i.e. wrt access_levels). This method can therefore
    not be used to e.g. update crossrefs for a changed bibtex id. */
    function getXRefPublicationsForPublication($bibtex_id) {
        $CI = &get_instance();
        $result = array();
        if (trim($bibtex_id)=='')return $result;
        $Q = $CI->db->get_where('publication', array('crossref' => $bibtex_id));
        foreach ($Q->result() as $row) {
            $next  =$this->getByID($row->pub_id);
            if ($next != null) {
                $result[] = $next;
            }
        }
        return $result;
    }

  
  function add($publication)
  {
    $CI = &get_instance();
    $CI->load->library('bibtex2utf8');
    $CI->load->helper('cleanname');
    //check access rights (!)
    $userlogin = getUserLogin();
    if (    (!$userlogin->hasRights('publication_edit'))
        ) 
    {
        appendErrorMessage(__('Add publication').': '.__('insufficient rights').'.<br/>');
        return;
    }        
    
    //insert all publication data in the publication table
    $fields = array(
                    'pub_type',
                    'bibtex_id',
                    'title',
                    'year',
                    'month',
                    //'firstpage',
                    //'lastpage',
                    'pages',
                    'journal',
                    'booktitle',
                    'edition',
                    'series',
                    'volume',
                    'number',
                    'chapter',
                    'publisher',
                    'location',
                    'institution',
                    'organization',
                    'school',
                    'address',
                    'report_type',
                    'howpublished',
                    'note',
                    'abstract',
                    'issn',
                    'isbn',
                    'url',
                    'doi',
                    'crossref',
                    'namekey',
                    'userfields',
                    'cleantitle',
                    'cleanauthor',
                    'cleanjournal',
                    'actualyear',
                    'mark', //always 0 by default; mark value will only be changed in a separate method so it doesn't need to get a value ehre or in the update method
                    'specialchars',
                    'status'
    );
    
    //change type in report_type
    if (isset($publication->type))
    {
      $publication->report_type = $publication->type;
    }


    $specialfields = array(
                    'title',
                    'journal',
                    'booktitle',
                    'series',
                    'publisher',
                    'location',
                    'institution',
                    'organization',
                    'school',
                    'note',
                    'abstract'
    );
  
    //check for specialchars
    if (getConfigurationSetting('CONVERT_BIBTEX_TO_UTF8')!='FALSE') {
      foreach ($specialfields as $field)
      {
        //remove bibchars
        $publication->$field = $CI->bibtex2utf8->bibCharsToUtf8FromString($publication->$field);
      }
      foreach($publication->customfields as $field => $value)
      {
        $publication->customfields[$field]['value'] = $CI->bibtex2utf8->bibCharsToUtf8FromString($value['value']);
      }
    }

    //create cleantitle and cleanjournal
    $publication->cleantitle    = cleanTitle($publication->title);
    $publication->cleanjournal    = cleanTitle($publication->journal);
    $publication->cleanauthor = ""; //will be filled later, after authors have been done
    
    //get actual year
    if (trim($publication->year) == '')
    {
      if (trim($publication->crossref) != '')
      {
        $xref_pub = $CI->publication_db->getByBibtexID($publication->crossref);
        if ($xref_pub!=null) //otherwise, the crossref doesn't exist in the database. If it's an entry being imported, the actualyear should have been set in another way, by the parser
          $publication->actualyear = $xref_pub->year;
                     
      }
    }
    else
    {
      $publication->actualyear = $publication->year;
    }
    
    //get the data to store in the database
    $data = array();
    foreach($fields as $field) 
      $data[$field] = $publication->$field;
    
    $data['user_id'] = $userlogin->userId();
  

    //insert into database using active record helper
    $CI->db->insert('publication', $data);
    
    //update this publication's pub_id
    $publication->pub_id = $CI->db->insert_id();
    
    //add custom fields
    $CI->customfields_db->addForID($publication->pub_id, $publication->getCustomFields());
    
    //check whether Keywords are already available, if not, add them to the database
    //keywords are in an array, the keys are the keyword_id.
    //If no key the keyword still has to be added.
    if (is_array($publication->keywords)) //we bypass the ->getKeywords() function here, it would try to retrieve from DB.
    {
      $publication->keywords  = $CI->keyword_db->ensureKeywordsInDatabase($publication->keywords);
      $inserted = array(); //to avoid duplicates
      foreach ($publication->keywords as $keyword)
      {
        if (in_array($keyword->keyword_id,$inserted)) continue;
        $data = array('pub_id' => $publication->pub_id, 'keyword_id' => $keyword->keyword_id);
        $inserted[] = $keyword->keyword_id;
        $CI->db->insert('publicationkeywordlink', $data);
      }
    }

    //start building up clean author value
    $publication->cleanauthor = "";
    
    //add authors
    if (is_array($publication->authors)) {
      $publication->authors   = $CI->author_db->ensureAuthorsInDatabase($publication->authors);

      $rank = 1;
      foreach ($publication->authors as $author)
      {
        $data = array('pub_id'    => $publication->pub_id,
                      'author_id' => $author->author_id,
                      'rank'      => $rank,
                      'is_editor' => 'N');
        $CI->db->insert('publicationauthorlink', $data);
        $rank++;
        $publication->cleanauthor .= ' '.$author->cleanname;
      }
    }
    
    //add editors
    if (is_array($publication->editors)) {
      $publication->editors   = $CI->author_db->ensureAuthorsInDatabase($publication->editors);
    
      $rank = 1;
      foreach ($publication->editors as $author)
      {
        $data = array('pub_id'    => $publication->pub_id,
                      'author_id' => $author->author_id,
                      'rank'      => $rank,
                      'is_editor' => 'Y');
        $CI->db->insert('publicationauthorlink', $data);
        $rank++;
        $publication->cleanauthor .= ' '.$author->cleanname;
      }
    }
    
    //update cleanauthor value
    $CI->db->where('pub_id', $publication->pub_id);
    $CI->db->update('publication', array('cleanauthor'=>trim($publication->cleanauthor)));
    
    //subscribe to topic 1
    $data = array('pub_id'      => $publication->pub_id,
                  'topic_id'    => 1);
    $CI->db->insert('topicpublicationlink', $data);

    //also fix bibtex_id mappings
	  refreshBibtexIdLinks();
    $CI->accesslevels_lib->initPublicationAccessLevels($publication);
    
    //change report_type in type
    if (isset($publication->report_type))
    {
      $publication->type = $publication->report_type;
      unset($publication->report_type);
    }

    return $publication;
  }
  
  function update($publication)
  {
    $CI = &get_instance();
    $CI->load->library('bibtex2utf8');
    $CI->load->helper('cleanname');
    //check access rights (by looking at the original publication in the database, as the POST
    //data might have been rigged!)
    $userlogin  = getUserLogin();
    $oldpublication = $this->getByID($publication->pub_id);
    if (($oldpublication == null) ||
       (!$userlogin->hasRights('publication_edit')) || 
			 (!$CI->accesslevels_lib->canEditObject($oldpublication))) 
    {
        appendErrorMessage('Edit publication: insufficient rights. publication_db.update<br/>');
        return $oldpublication;
    }

    //insert all publication data in the publication table
    $fields = array(
                    'pub_type',
                    'bibtex_id',
                    'title',
                    'year',
                    'month',
                    //'firstpage',
                    //'lastpage',
                    'pages',
                    'journal',
                    'booktitle',
                    'edition',
                    'series',
                    'volume',
                    'number',
                    'chapter',
                    'publisher',
                    'location',
                    'institution',
                    'organization',
                    'school',
                    'address',
                    'report_type',
                    'howpublished',
                    'note',
                    'abstract',
                    'issn',
                    'isbn',
                    'url',
                    'doi',
                    'crossref',
                    'namekey',
                    'userfields',
                    'cleantitle',
                    'cleanjournal',
                    'actualyear',
                    'specialchars',
                    'status',
                    'coverimage'
    ); //'mark' doesn't need to get updated as that is done through other methods.
  
    $specialfields = array(
                    'title',
                    'journal',
                    'booktitle',
                    'series',
                    'publisher',
                    'location',
                    'institution',
                    'organization',
                    'school',
                    'note',
                    'abstract'
    );
  
    //change type in report_type
    if (isset($publication->type))
    {
      $publication->report_type = $publication->type;
    }
  
    //check for specialchars
    if (getConfigurationSetting('CONVERT_BIBTEX_TO_UTF8')!='FALSE') {
      foreach ($specialfields as $field)
      {
        //remove bibchars
        $publication->$field = $CI->bibtex2utf8->bibCharsToUtf8FromString($publication->$field);
      }
      foreach($publication->customfields as $field => $value)
      {
        $publication->customfields[$field]['value'] = $CI->bibtex2utf8->bibCharsToUtf8FromString($value['value']);
      }
    }
    
    //create cleantitle and cleanjournal
    $publication->cleantitle    = cleanTitle($publication->title);
    $publication->cleanjournal  = cleanTitle($publication->journal);
    
    //get actual year
    if (trim($publication->year) == '')
    {
      if (trim($publication->crossref) != '')
      {
        $xref_pub = $this->getByBibtexID($publication->crossref);
        if ($xref_pub != null)
          $publication->actualyear = $xref_pub->year;
      }
    }
    else
    {
      $publication->actualyear = $publication->year;
    }
    
    //get the data to store in the database
    $data = array();
    foreach($fields as $field) 
      $data[$field] = $publication->$field;

    //[DR:] line below commented out: the user id should not change when updating! the owner always stays the same!
    //$data['user_id'] = $userlogin->userId();
  
    //insert into database using active record helper. 
    $CI->db->where('pub_id', $publication->pub_id);
    $CI->db->update('publication', $data);

    //update custom fields
    $CI->customfields_db->updateForID($publication->pub_id, $publication->getCustomFields());
    
    //remove old keyword links
    $CI->db->delete('publicationkeywordlink', array('pub_id' => $publication->pub_id)); 
    
    //check whether Keywords are already available, if not, add them to the database
    //keywords are in an array, the keys are the keyword_id.
    //If no key the keyword still has to be added.
    if (is_array($publication->keywords)) //we bypass the ->getKeywords() function here, it would try to retrieve from DB.
    {
      $publication->keywords  = $CI->keyword_db->ensureKeywordsInDatabase($publication->keywords);
      $inserted = array(); //to avoid duplicates
      foreach ($publication->keywords as $keyword)
      {
        if (in_array($keyword->keyword_id,$inserted)) continue;
        $data = array('pub_id' => $publication->pub_id, 'keyword_id' => $keyword->keyword_id);
        $inserted[] = $keyword->keyword_id;
        $CI->db->insert('publicationkeywordlink', $data);
      }
    }
    //remove old author and editor links
    $CI->db->delete('publicationauthorlink', array('pub_id' => $publication->pub_id)); 
    
    //start building up clean author value
    $publication->cleanauthor = "";
    
    //add authors
    if (is_array($publication->authors))
    {
      $publication->authors   = $CI->author_db->ensureAuthorsInDatabase($publication->authors);
      
      $rank = 1;
      foreach ($publication->authors as $author)
      {
        $data = array('pub_id'    => $publication->pub_id,
                      'author_id' => $author->author_id,
                      'rank'      => $rank,
                      'is_editor' => 'N');
        $CI->db->insert('publicationauthorlink', $data);
        $rank++;
        $publication->cleanauthor .= ' '.$author->cleanname;
      }
    }
    
    //add editors
    if (is_array($publication->editors))
    {
      $publication->editors   = $CI->author_db->ensureAuthorsInDatabase($publication->editors);
    
      $rank = 1;
      foreach ($publication->editors as $author)
      {
        $data = array('pub_id'    => $publication->pub_id,
                      'author_id' => $author->author_id,
                      'rank'      => $rank,
                      'is_editor' => 'Y');
        $CI->db->insert('publicationauthorlink', $data);
        $rank++;
        $publication->cleanauthor .= ' '.$author->cleanname;
      }
    }

    //update cleanauthor value
    $CI->db->where('pub_id', $publication->pub_id);
    $CI->db->update('publication', array('cleanauthor'=>trim($publication->cleanauthor)));

    //changed bibtex_id?
    if ($oldpublication->bibtex_id != $publication->bibtex_id) {
        //fix all crossreffing notes
        $CI->note_db->changeAllCrossrefs($publication->pub_id, $publication->bibtex_id);
        //fix all crossreffing pubs
        $this->changeAllCrossrefs($publication->pub_id, $oldpublication->bibtex_id, $publication->bibtex_id);
		    refreshBibtexIdLinks();
    }

    //change report_type in type
    if (isset($publication->report_type))
    {
      $publication->type = $publication->report_type;
      unset($publication->report_type);
    }    
    
    return $publication;
  }

    /** delete given object. where necessary cascade. Checks for edit and read rights on this object and all cascades
    in the _db class before actually deleting. */
    function delete($publication) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        //collect all cascaded to-be-deleted-id's: none
        //check rights
        //check, all through the cascade, whether you can read AND edit that object
        if (!$userlogin->hasRights('publication_edit')
            ||
            !$CI->accesslevels_lib->canEditObject($publication)
            ) {
            //if not, for any of them, give error message and return
            appendErrorMessage(__('Cannot delete publication').': '.__('insufficient rights').'.<br/>');
            return false;
        }
        if (empty($publication->pub_id)) {
            appendErrorMessage(__('Cannot delete publication').': '.__('erroneous ID').'.<br/>');
            return false;
        }
        //no delete for object with children. check through tables, not through object
        #NOTE: if we want to allow delete of publications with notes and attachments, we should make sure
        #that current user can edit/delete all those notes and attachments!
        $Q = $CI->db->get_where('attachments',array('pub_id'=>$publication->pub_id));
        if ($Q->num_rows()>0) {
            //check if you can delete attachments 
            foreach ($Q->result() as $row) {
                $attachment = $CI->attachment_db->getByID($row->att_id);
                if ($attachment == null) {
                    appendErrorMessage(__('Cannot delete publication').': '.__('publication contains some attachments that you do not have permission to delete.').'<br/>');
                    return false;
                }
                if (!$CI->accesslevels_lib->canEditObject($attachment)) {
                    appendErrorMessage(__('Cannot delete publication').': '.__('publication contains some attachments that you do not have permission to delete.').'<br/>');
                    return false;
                }
            }
        }
        $Q = $CI->db->get_where('notes',array('pub_id'=>$publication->pub_id));
        if ($Q->num_rows()>0) {
            //check if you can delete notes
            foreach ($Q->result() as $row) {
                $note = $CI->note_db->getByID($row->note_id);
                if ($note == null) {
                    appendErrorMessage(__('Cannot delete publication').': '.__('publication contains some notes that you do not have permission to delete.').'<br/>');
                    return false;
                }
                if (!$CI->accesslevels_lib->canEditObject($note)) {
                    appendErrorMessage(__('Cannot delete publication').': '.__('publication contains some notes that you do not have permission to delete.').'<br/>');
                    return false;
                }
            }
        }
        $Q = $CI->db->get_where('attachments',array('pub_id'=>$publication->pub_id));
        if ($Q->num_rows()>0) {
            //do actual delete of attachments, AFTER you know it is OK to proceed with delete
            foreach ($Q->result() as $row) {
                $attachment = $CI->attachment_db->getByID($row->att_id);
                $attachment->delete();
            }
        }
        $Q = $CI->db->get_where('notes',array('pub_id'=>$publication->pub_id));
        if ($Q->num_rows()>0) {
            //do actual delete of notes, AFTER you know it is OK to proceed with delete
            foreach ($Q->result() as $row) {
                $note = $CI->note_db->getByID($row->note_id);
                $note->delete();
            }
        }
        
        //remove customfields
        $CI->customfields_db->deleteForPublication($publication->pub_id);
        
        //otherwise, delete all dependent objects by directly accessing the rows in the table 
        $CI->db->delete('publication',array('pub_id'=>$publication->pub_id));
        //delete links
        $CI->db->delete('topicpublicationlink',array('pub_id'=>$publication->pub_id));
        $CI->db->delete('publicationauthorlink',array('pub_id'=>$publication->pub_id));
        $CI->db->delete('publicationkeywordlink',array('pub_id'=>$publication->pub_id));
        $CI->db->delete('userbookmarklists',array('pub_id'=>$publication->pub_id));
        $CI->db->delete('userpublicationmark',array('pub_id'=>$publication->pub_id));
        $CI->db->delete('notecrossrefid',array('xref_id'=>$publication->pub_id));
        //add the information of the deleted rows to trashcan(time, data), in such a way that at least manual reconstruction will be possible
        
        //refresh the bibtex ID links
        refreshBibtexIdLinks();
        return true;
    }
    
    function commitcoverimage($publication)
    {
        $CI = &get_instance();
        //check access rights (!)
        $userlogin    = getUserLogin();
        $user         = $CI->user_db->getByID($userlogin->userID());
        if (    ($publication == null) 
             ||
                (!$userlogin->hasRights('publication_edit'))
             || 
                (!$CI->accesslevels_lib->canEditObject($publication))
            ) 
        {
	        appendErrorMessage(__('Upload cover image').': '.__('insufficient rights').'.<br/>');
	        return;
        }

        	# upload not possible: return with error
        	if (getConfigurationSetting("SERVER_NOT_WRITABLE") == "TRUE") {
        		appendErrorMessage(__("You cannot upload cover image files to this server (the server is declared write-only).")."<br/>");
        		return;
        	}
        
        	$CI->file_upload->http_error = $_FILES['upload']['error'];
        
        	if ($CI->file_upload->http_error > 0) {
        		appendErrorMessage(__("Error while uploading").": ".$CI->file_upload->error_text($CI->file_upload->http_error).'<br/>');
        		return;
        	}
        
        	# prepare upload of file from temp to permanent location
        	$CI->file_upload->the_file = $_FILES['upload']['name'];
        	$CI->file_upload->the_temp_file = $_FILES['upload']['tmp_name'];
        	$CI->file_upload->extensions = array('.jpg','.jpeg');  // specify the allowed extensions here
        	$CI->file_upload->upload_dir = AIGAION_ATTACHMENT_DIR."/";  // is the folder for the uploaded files (you have to create this folder)
        	$CI->file_upload->max_length_filename = 255; // change this value to fit your field length in your database (standard 100)
        	$CI->file_upload->rename_file = true;
        	$CI->file_upload->replace = "n"; 
        	$CI->file_upload->do_filename_check = "n"; // use this boolean to check for a valid filename
        
        	# determine storename (the one
        	# used for storage) of file, from alternative name or from original name
        	$realname=$_FILES['upload']['name'];
        	$ext = $CI->file_upload->get_extension($realname);
        	$CI->load->helper('filename');
        	$storename = toCleanName($realname)."-".$this->generateUniqueSuffix();
        
        	# execute the actual upload
        	if ($CI->file_upload->upload($storename)) {  
        	    // storename is an additional filename information, use this to rename the uploaded file
        		//echo "mime:".$attachment->mime.".";
        		# upload was succesful:

        		# add appropriate info about new attachment to database
            $publication->coverimage = $storename.$ext;
            $publication->update();
        		# check if file is really there
        		if (!is_file(AIGAION_ATTACHMENT_DIR."/".$storename.$ext))
        		{
        	        appendErrorMessage(__("Error uploading. The file was not written to disk.")
        	          ."<br/>"
                    .__("Is this error entirely unexpected? You might want to check whether the php settings 'upload_max_filesize', 'post_max_size' and 'max_execution_time' are all large enough for uploading your attachments... Please check this with your administrator.")
                    ."<br/>");
        		}
        		
        		return;
        	} else {
        		appendErrorMessage(utf8_strtoupper(__("Error while uploading")).": ".$CI->file_upload->show_error_string()."<br/>".sprintf(__("Is the error due to allowed file types? Ask %s for more types."),"<a href='mailto:".getConfigurationSetting("CFG_ADMINMAIL")."'>".getConfigurationSetting("CFG_ADMIN")."</a>")."<br/>");
        		return;
        	}
        
        appendErrorMessage("GENERIC ERROR UPLOADING. THIS SHOULD NOT HAVE BEEN LOGICALLY POSSIBLE. PLEASE CONTACT YOUR DATABASE ADMINISTRATOR.<br/>"); 
        //but nevertheless,  murphy's law dicates that we add an error feedback message here :)
        return;
    }    
    function deletecoverimage($publication)
    {
        $CI = &get_instance();
        //check access rights (!)
        $userlogin    = getUserLogin();
        $user         = $CI->user_db->getByID($userlogin->userID());
        if (    ($publication == null) 
             ||
                (!$userlogin->hasRights('publication_edit'))
             || 
                (!$CI->accesslevels_lib->canEditObject($publication))
            ) 
        {
	        appendErrorMessage(__('Delete cover image').': '.__('insufficient rights').'.<br/>');
	        return;
        }
        if (is_file(AIGAION_ATTACHMENT_DIR.'/'.$publication->coverimage)) {
          unlink(AIGAION_ATTACHMENT_DIR.'/'.$publication->coverimage);
        }
        $publication->coverimage = '';
        $publication->update();
        
    }
  function validate($publication)
  {
    //DR: when crossref set, nothing is required :) (see end of function)
    $CI = &get_instance();
    
    $CI->load->helper('publication');
    
    $validate_required    = array();
    $validate_conditional = array();
    $fields               = getPublicationFieldArray($publication->pub_type);
    foreach ($fields as $field => $value)
    {
      if ($value == 'required')
      {
        $validate_required[$field] = 'required';
      }
      else if ($value == 'conditional')
      {
        $validate_conditional[$field] = 'conditional';
      }
    }
    
    $validation_message   = '';
    foreach ($validate_required as $key => $value)
    {
      if (trim($publication->$key) == '')
      {
        $validation_message .=sprintf( __("The %s field is required"),$key).".<br/>\n";
      }
    }
    
    if (count($validate_conditional) > 0)
    {
      $conditional_validation = false;
      $conditional_field_text = '';
      
      foreach ($validate_conditional as $key => $value)
      {
        if (trim($publication->$key) != '')
        {
          $conditional_validation = true;
        }
        $conditional_field_text .= $key.", ";
      }
      if (!$conditional_validation)
      {
        $validation_message .= sprintf(__("One of the fields %s is required"),$conditional_field_text).".<br/>\n";
      }
    }
    
    if ($validation_message != '' && (trim($publication->crossref)=='')) //when crossref set, nothing is required :)
    {
      appendErrorMessage(__("Validation error").":<br/>\n".$validation_message);
      return false;
    }
    else
      return true;
  }


  function getCountForTopic($topic_id)
  {
  	$CI = &get_instance();
  	$Q = $CI->db->query("SELECT DISTINCT count(*) c FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."topicpublicationlink
  	    WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
  	    AND ".AIGAION_DB_PREFIX."publication.pub_id = ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id;");
  	
  	foreach ($Q->result() as $row)
  	{
  		return $row->c;
  	}

  	return 0;  	  	
  }
  
  function getVisibleCountForTopic($topic_id)
  {
  	$CI = &get_instance();
    $userlogin=getUserLogin();
    
    if ($userlogin->hasRights('read_all_override'))
      return $this->getCountForTopic($topic_id);
    
    if ($userlogin->isAnonymous()) //get only public publications
    {
  	$Q = $CI->db->query("SELECT DISTINCT count(*) c FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."topicpublicationlink
  	    WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
  	    AND   ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id   = ".AIGAION_DB_PREFIX."publication.pub_id
        AND   ".AIGAION_DB_PREFIX."publication.derived_read_access_level = 'public'");
    }
    else //get all non-private publications and publications that belong to the user
    {
        $Q = $CI->db->query("SELECT DISTINCT count(*) c FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."topicpublicationlink
  	    WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
  	    AND   ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id   = ".AIGAION_DB_PREFIX."publication.pub_id
        AND  (".AIGAION_DB_PREFIX."publication.derived_read_access_level != 'private' 
           OR ".AIGAION_DB_PREFIX."publication.user_id = ".$userlogin->userId().")");
    }
  	
  	foreach ($Q->result() as $row)
  	{
  		return $row->c;
  	}

  	return 0;  	  	
  }
  
  /**
   * Get information about the number of publications
   * for each criterion: year/type/author(first letter)/rating
   * Returning all distinct values in the group. Example: 2010:4,2011:9,null:1
   * @param int $topic_id
   * @return array(array(group,value,count))
   */
  function getPubStructForTopic($topic_id)
  {
  	$CI = &get_instance();
  	
  	$sql = array();
    # @TODO: (KK) needs only to search for what is wanted to be sorted by; this has been done in getVisiblePubStructForTopic
  	$sql[] = "(SELECT 'year' grp, p.year value, count(*) c FROM ".AIGAION_DB_PREFIX."publication p, ".AIGAION_DB_PREFIX."topicpublicationlink t
  	  	  	    WHERE t.topic_id = ".$CI->db->escape($topic_id)."
  	  	  	    AND p.pub_id = t.pub_id ".
  	  					" GROUP BY p.year)";
  	
  	$sql[] = "(SELECT 'type', p.pub_type type, count(*) c FROM ".AIGAION_DB_PREFIX."publication p, ".AIGAION_DB_PREFIX."topicpublicationlink t
  	  	  	    WHERE t.topic_id = ".$CI->db->escape($topic_id)."
  	  	  	    AND p.pub_id = t.pub_id ".
  	  					" GROUP BY p.pub_type)";
  	
  	$sql[] = "(SELECT 'author', UPPER(LEFT(cleanauthor,1)) author, count(*) c FROM ".AIGAION_DB_PREFIX."publication p, ".AIGAION_DB_PREFIX."topicpublicationlink t
  	  	  	    WHERE t.topic_id = ".$CI->db->escape($topic_id)."
  	  	  	    AND p.pub_id = t.pub_id ".
  	  					" GROUP BY UPPER(LEFT(cleanauthor,1)))";
  	
  	$sql[] = "(SELECT 'title', UPPER(LEFT(cleantitle,1)) title, count(*) c FROM ".AIGAION_DB_PREFIX."publication p, ".AIGAION_DB_PREFIX."topicpublicationlink t
  	  	  	  	    WHERE t.topic_id = ".$CI->db->escape($topic_id)."
  	  	  	  	    AND p.pub_id = t.pub_id ".
  	  	  					" GROUP BY UPPER(LEFT(cleantitle,1)))";
  	
  	$sql[] = "(SELECT 'rating', mark, count(*) c FROM ".AIGAION_DB_PREFIX."publication p, ".AIGAION_DB_PREFIX."topicpublicationlink t
  	  	  	    WHERE t.topic_id = ".$CI->db->escape($topic_id)."
  	  	  	    AND p.pub_id = t.pub_id ".
  	  					" GROUP BY mark) ORDER BY grp, value";
  	
  	$Q = $CI->db->query(join($sql, ' UNION '));
  	
  	$result = array();
    foreach ($Q->result() as $row)
    {
        $result[] = array('group' => $row->grp, 'value' => $row->value, 'count' => $row->c);
    }
      
    return $result;
  	
  }
 
  function getVisiblePubStructForTopic($topic_id, $order='year')
  {
  	$CI = &get_instance();
    $userlogin = getUserLogin();
    if ($userlogin->hasRights('read_all_override'))
      return $this->getPubStructForTopic($topic_id, $order);
  	
  	$sql = array();
    if ($userlogin->isAnonymous()) //get only public publications
    {
        switch($order) {
            case 'year':
            case 'recent':
  	$sql = "(SELECT 'year' grp, p.year value, count(*) c FROM ".AIGAION_DB_PREFIX."publication p, ".AIGAION_DB_PREFIX."topicpublicationlink t
  	  	  	    WHERE t.topic_id = ".$CI->db->escape($topic_id)."
  	  	  	    AND p.pub_id = t.pub_id 
                AND p.derived_read_access_level = 'public'".
  	  					" GROUP BY p.year)";
            break;
            case 'type':
  	$sql = "(SELECT 'type' grp, p.pub_type value, count(*) c FROM ".AIGAION_DB_PREFIX."publication p, ".AIGAION_DB_PREFIX."topicpublicationlink t
  	  	  	    WHERE t.topic_id = ".$CI->db->escape($topic_id)."
  	  	  	    AND p.pub_id = t.pub_id 
                AND p.derived_read_access_level = 'public'".
  	  					" GROUP BY p.pub_type)";
            break;
            case 'author':
  	$sql = "(SELECT 'author' grp, UPPER(LEFT(cleanauthor,1)) value, count(*) c FROM ".AIGAION_DB_PREFIX."publication p, ".AIGAION_DB_PREFIX."topicpublicationlink t
  	  	  	    WHERE t.topic_id = ".$CI->db->escape($topic_id)."
  	  	  	    AND p.pub_id = t.pub_id 
                AND p.derived_read_access_level = 'public'".
  	  					" GROUP BY UPPER(LEFT(cleanauthor,1)))";
            break;
            case 'title':
  	$sql = "(SELECT 'title' grp, UPPER(LEFT(cleantitle,1)) value, count(*) c FROM ".AIGAION_DB_PREFIX."publication p, ".AIGAION_DB_PREFIX."topicpublicationlink t
  	  	  	  	WHERE t.topic_id = ".$CI->db->escape($topic_id)."
                AND p.pub_id = t.pub_id 
                AND p.derived_read_access_level = 'public'".
  	  	  					" GROUP BY UPPER(LEFT(cleantitle,1)))";
            break;
            case 'rating':
  	$sql = "(SELECT 'rating' grp, mark value, count(*) c FROM ".AIGAION_DB_PREFIX."publication p, ".AIGAION_DB_PREFIX."topicpublicationlink t
  	  	  	    WHERE t.topic_id = ".$CI->db->escape($topic_id)."
  	  	  	    AND p.pub_id = t.pub_id 
                AND p.derived_read_access_level = 'public'".
  	  					" GROUP BY mark)";
            break;
        }
    }
    else //get all non-private publications and publications that belong to the user
    {
        switch($order) {
            case 'year':
    $sql = "(SELECT 'year' grp, p.year value, count(*) c FROM ".AIGAION_DB_PREFIX."publication p, ".AIGAION_DB_PREFIX."topicpublicationlink t
  	  	  	    WHERE t.topic_id = ".$CI->db->escape($topic_id)."
  	  	  	    AND p.pub_id = t.pub_id 
                AND (p.derived_read_access_level != 'private' 
                     OR p.user_id = ".$userlogin->userId().")".
  	  					" GROUP BY p.year)";
            break;
            case 'type':
  	$sql = "(SELECT 'type' grp, p.pub_type value, count(*) c FROM ".AIGAION_DB_PREFIX."publication p, ".AIGAION_DB_PREFIX."topicpublicationlink t
  	  	  	    WHERE t.topic_id = ".$CI->db->escape($topic_id)."
  	  	  	    AND p.pub_id = t.pub_id 
                AND (p.derived_read_access_level != 'private' 
                     OR p.user_id = ".$userlogin->userId().")".
  	  					" GROUP BY p.pub_type)";
            break;
            case 'author':
  	$sql = "(SELECT 'author' grp, UPPER(LEFT(cleanauthor,1)) value, count(*) c FROM ".AIGAION_DB_PREFIX."publication p, ".AIGAION_DB_PREFIX."topicpublicationlink t
  	  	  	    WHERE t.topic_id = ".$CI->db->escape($topic_id)."
  	  	  	    AND p.pub_id = t.pub_id 
                AND (p.derived_read_access_level != 'private' 
                     OR p.user_id = ".$userlogin->userId().")".
  	  					" GROUP BY UPPER(LEFT(cleanauthor,1)))";
            break;
            case 'title':
  	$sql = "(SELECT 'title' grp, UPPER(LEFT(cleantitle,1)) value, count(*) c FROM ".AIGAION_DB_PREFIX."publication p, ".AIGAION_DB_PREFIX."topicpublicationlink t
  	  	  	  	    WHERE t.topic_id = ".$CI->db->escape($topic_id)."
  	  	  	  	    AND p.pub_id = t.pub_id 
                AND (p.derived_read_access_level != 'private' 
                     OR p.user_id = ".$userlogin->userId().")".
  	  	  					" GROUP BY UPPER(LEFT(cleantitle,1)))";
            break;
            case 'rating':
  	$sql = "(SELECT 'rating' grp, mark value, count(*) c FROM ".AIGAION_DB_PREFIX."publication p, ".AIGAION_DB_PREFIX."topicpublicationlink t
  	  	  	    WHERE t.topic_id = ".$CI->db->escape($topic_id)."
  	  	  	    AND p.pub_id = t.pub_id 
                AND (p.derived_read_access_level != 'private' 
                     OR p.user_id = ".$userlogin->userId().")".
  	  					" GROUP BY mark)";
            break;
        }
    }
  	
    $Q = $CI->db->query($sql);
  	
  	$result = array();
    foreach ($Q->result() as $row)
    {
        $result[] = array('group' => $row->grp, 'value' => $row->value, 'count' => $row->c);
    }
      
    return $result;
  	
  }
    
///////publication list functions

  function getForTopic($topic_id,$order='',$page=0)
  {
    $orderby='actualyear DESC, cleantitle';
    switch ($order) {
      case 'year':
        $orderby='actualyear DESC, cleantitle';
        break;
      case 'type':
        $orderby='pub_type ASC, cleanjournal ASC, actualyear DESC, cleantitle'; //funny thing: article is lowest in alphabetical order, so this ordering is enough...
        break;
      case 'recent':
        $orderby='pub_id DESC';
        break;
      case 'title':
        $orderby='cleantitle';
        break;
      case 'author':
        $orderby='cleanauthor, actualyear DESC';
        break;
      case 'rating':
      	$orderby='mark DESC, actualyear DESC';
        break;
    }
    $CI = &get_instance();
    
    //do we need multipage output / use limit statement
    $userlogin = getUserLogin();
    $liststyle = $userlogin->getPreference('liststyle');
    $limit = "";
    if ($page!=-1)
    {
      if ($liststyle > 0)
      {
        $limitOffset = $liststyle * $page;
        $limit = "LIMIT ".$limitOffset.",".$liststyle;
      }
    }
    //we need merge functionality here, so initialze a merge cache
    $this->crossref_cache = array();
    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication
    INNER JOIN (SELECT ".AIGAION_DB_PREFIX."publication.pub_id FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."topicpublicationlink
      WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
      AND   ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id   = ".AIGAION_DB_PREFIX."publication.pub_id
      ORDER BY ".$orderby." ".$limit.")
    AS lim USING (pub_id)");

    $result = array();
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }

    unset($this->crossref_cache);
    return $result;
  }
  
  function getVisibleForTopic($topic_id,$order='',$page=0)
  {
    $CI = &get_instance();
    $userlogin=getUserLogin();
    
    if ($userlogin->hasRights('read_all_override'))
      return $this->getForTopic($topic_id,$order,$page);
      
    $orderby='actualyear DESC, cleantitle';
    switch ($order) {
      case 'year':
        $orderby='actualyear DESC, cleantitle';
        break;
      case 'type':
        $orderby='pub_type ASC, cleanjournal ASC, actualyear DESC, cleantitle'; //funny thing: article is lowest in alphabetical order, so this ordering is enough...
        break;
      case 'recent':
        $orderby='pub_id DESC';
        break;
      case 'title':
        $orderby='cleantitle';
        break;
      case 'author':
        $orderby='cleanauthor, actualyear DESC';
        break;
      case 'rating':
      	$orderby='mark DESC, actualyear DESC';
        break;
    }
    
    //do we need multipage output / use limit statement
    $limit = "";
    $liststyle = $userlogin->getPreference('liststyle');
    if ($page!=-1)
    {
      if ($liststyle > 0)
      {
        $limitOffset = $liststyle * $page;
        $limit = "LIMIT ".$limitOffset.",".$liststyle;
      }
    }
    //we need merge functionality here, so initialze a merge cache
    $this->crossref_cache = array();
    
    if ($userlogin->isAnonymous()) //get only public publications
    {
    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication
    INNER JOIN (SELECT ".AIGAION_DB_PREFIX."publication.pub_id FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."topicpublicationlink
      WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
      AND   ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id   = ".AIGAION_DB_PREFIX."publication.pub_id
      AND   ".AIGAION_DB_PREFIX."publication.derived_read_access_level = 'public'
      ORDER BY ".$orderby." ".$limit.")
    AS lim USING (pub_id)");
     }
    else //get all non-private publications and publications that belong to the user
    {
    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication
    INNER JOIN (SELECT ".AIGAION_DB_PREFIX."publication.pub_id FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."topicpublicationlink
      WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
      AND   ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id   = ".AIGAION_DB_PREFIX."publication.pub_id
      AND ( ".AIGAION_DB_PREFIX."publication.derived_read_access_level != 'private' 
         OR ".AIGAION_DB_PREFIX."publication.user_id = ".$userlogin->userId().")
      ORDER BY ".$orderby." ".$limit.")
    AS lim USING (pub_id)");
    }

    $result = array();
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }

    unset($this->crossref_cache);
    return $result;
  }
  
  function getUnassigned($order='',$page=0)
  {
    $orderby='actualyear DESC, cleantitle';
    switch ($order) {
      case 'year':
        $orderby='actualyear DESC, cleantitle';
        break;
      case 'type':
        $orderby='pub_type, cleanjournal ASC, actualyear  DESC, cleantitle'; //funny thing: article is lowest in alphabetical order, so this ordering is enough...
        break;
      case 'recent':
        $orderby='pub_id DESC';
        break;
      case 'title':
        $orderby='cleantitle';
        break;
      case 'author':
        $orderby='cleanauthor, actualyear  DESC';
        break;
    }
    $CI = &get_instance();
    //we need merge functionality here, so initialze a merge cache
    $this->crossref_cache = array();
    
    //do we need multipage output / use limit statement
    $limit = "";
    $userlogin = getUserLogin();
    $liststyle = $userlogin->getPreference('liststyle');
    $limit = "";
    if ($page!=-1)
    {
      if ($liststyle > 0)
      {
        $limitOffset = $liststyle * $page;
        $limit = "LIMIT ".$limitOffset.", ".$liststyle;
      }
    }
    ///////////////////
    //DR: this query is copied from another method - needs to be modified to retrieve all unassigned papers.
    ///////////////////
    $Q = $CI->db->query("SELECT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication 
                                    LEFT JOIN ".AIGAION_DB_PREFIX."topicpublicationlink
                        			       ON (".AIGAION_DB_PREFIX."publication.pub_id = ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id AND (".AIGAION_DB_PREFIX."topicpublicationlink.topic_id != 1))
                          WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id IS NULL
                       ORDER BY ".$orderby." ".$limit);

    $result = array();
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }

    unset($this->crossref_cache);
    return $result;
  }
  
  /** Use page==-1 to get ALL publications 
  default ordering '' is by year */
  function getForAuthor($author_id,$order='',$page=0,$include_synonyms=false)
  {
    $CI = &get_instance();
    
    $result = array();
    $author_cond = AIGAION_DB_PREFIX."publicationauthorlink.author_id = ".$CI->db->escape($author_id);
    #if synonynms must be included: get the list of synonyms and build a new author-sql-condition
    if ($include_synonyms)
    {
      $author = $CI->author_db->getByID($author_id);
      if ($author==null) return $result;
      //get synonyms; make list of author_ids... INCLUDING PRIMARY AUTHOR
      $authors = $author->getSynonyms(true);
      $author_cond = AIGAION_DB_PREFIX."publicationauthorlink.author_id IN (".$authors[0]->author_id;
      for ($i = 1; $i < count($authors); $i++) 
      {
        $author_cond .= ",".$authors[$i]->author_id;
      }
      $author_cond .= ")";
    }
    # build order-by clause
    $orderby='actualyear DESC, cleantitle';
    switch ($order) {
      case 'year':
        $orderby='actualyear DESC, cleantitle';
        break;
      case 'type':
        $orderby='pub_type, cleanjournal ASC, actualyear DESC, cleantitle'; //funny thing: article is lowest in alphabetical order, so this ordering is enough...
        break;
      case 'recent':
        $orderby='pub_id DESC';
        break;
      case 'title':
        $orderby='cleantitle';
        break;
      case 'author':
        $orderby='cleanauthor, actualyear DESC';
        break;
    }
    
    //do we need multipage output / use limit statement
    $limit = "";
    $userlogin = getUserLogin();
    $liststyle = $userlogin->getPreference('liststyle');
    if ($page!=-1)
    {
      if ($liststyle > 0)
      {
        $limitOffset = $liststyle * $page;
        $limit = "LIMIT ".$limitOffset.", ".$liststyle;
      }
    }
    
    //we need merge functionality here, so initialze a merge cache
    $this->crossref_cache = array();
    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."publicationauthorlink
    WHERE ".$author_cond."
    AND ".AIGAION_DB_PREFIX."publication.pub_id = ".AIGAION_DB_PREFIX."publicationauthorlink.pub_id
    ORDER BY ".$orderby." ".$limit);

    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }

    unset($this->crossref_cache);
    return $result;
  }
 
  function getForKeyword($keyword,$order='',$page=0)
  {
    $orderby='actualyear DESC, cleantitle';
    switch ($order) {
      case 'year':
        $orderby='actualyear DESC, cleantitle';
        break;
      case 'type':
        $orderby='pub_type, cleanjournal ASC, actualyear DESC, cleantitle'; //funny thing: article is lowest in alphabetical order, so this ordering is enough...
        break;
      case 'recent':
        $orderby='pub_id DESC';
        break;
      case 'title':
        $orderby='cleantitle';
        break;
      case 'author':
        $orderby='cleanauthor, actualyear DESC';
        break;
    }
    
    //do we need multipage output / use limit statement
    $limit = "";
    $userlogin = getUserLogin();
    $liststyle = $userlogin->getPreference('liststyle');
    if ($page!=-1)
    {
      if (($liststyle > 0) && ($page != -1))
      {
        $limitOffset = $liststyle * $page;
        $limit = "LIMIT ".$limitOffset.", ".$liststyle;
      }
    }
    
    $CI = &get_instance();
    //we need merge functionality here, so initialze a merge cache
    $this->crossref_cache = array();
    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication
    INNER JOIN (SELECT ".AIGAION_DB_PREFIX."publication.pub_id FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."publicationkeywordlink
      WHERE ".AIGAION_DB_PREFIX."publicationkeywordlink.keyword_id = ".$CI->db->escape($keyword->keyword_id)."
      AND   ".AIGAION_DB_PREFIX."publicationkeywordlink.pub_id   = ".AIGAION_DB_PREFIX."publication.pub_id
      ".$limit.")
    AS lim USING (pub_id)
    ORDER BY ".$orderby);



    $result = array();
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }

    unset($this->crossref_cache);
    return $result;
  }  
  /** Return a list of publications for the bookmark list of the logged user */
  function getForBookmarkList($order='',$page=0)
  {
    $orderby='actualyear DESC, cleantitle';
    switch ($order) {
      case 'year':
        $orderby='actualyear DESC, cleantitle';
        break;
      case 'type':
        $orderby='pub_type, actualyear DESC, cleanjournal, cleantitle'; //funny thing: article is lowest in alphabetical order, so this ordering is enough...
        break;
      case 'recent':
        $orderby='pub_id DESC';
        break;
      case 'title':
        $orderby='cleantitle';
        break;
      case 'author':
        $orderby='cleanauthor, actualyear DESC';
        break;
    }
    
    //do we need multipage output / use limit statement
    $limit = "";
    $userlogin = getUserLogin();
    $liststyle = $userlogin->getPreference('liststyle');
    if ($page!=-1)
    {
      if ($liststyle > 0)
      {
        $limitOffset = $liststyle * $page;
        $limit = "LIMIT ".$limitOffset.", ".$liststyle;
      }
    }
    
    $CI = &get_instance();
    //$userlogin = getUserLogin();
    
    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."userbookmarklists
    WHERE ".AIGAION_DB_PREFIX."userbookmarklists.user_id=".$CI->db->escape($userlogin->userId())."
    AND   ".AIGAION_DB_PREFIX."userbookmarklists.pub_id=".AIGAION_DB_PREFIX."publication.pub_id
    ORDER BY ".$orderby);

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
  
  //contributed by Andreas Bulling
  function getForYear($year_id,$order='',$page=0)
  {
    $orderby='actualyear DESC, cleantitle';
    switch ($order) {
      case 'year':
        $orderby='actualyear DESC, cleantitle';
        break;
      case 'type':
        $orderby='pub_type, actualyear DESC, cleanjournal, cleantitle'; //funny thing: article is lowest in alphabetical order, so this ordering is enough...
        break;
      case 'recent':
        $orderby='pub_id DESC';
        break;
      case 'title':
        $orderby='cleantitle';
        break;
      case 'author':
        $orderby='cleanauthor, actualyear DESC';
        break;
    }
    
    //do we need multipage output / use limit statement
    $limit = "";
    $userlogin = getUserLogin();
    $liststyle = $userlogin->getPreference('liststyle');
    if ($page!=-1)
    {
      if ($liststyle > 0)
      {
        $limitOffset = $liststyle * $page;
        $limit = "LIMIT ".$limitOffset.", ".$liststyle;
      }
    }
    
    $CI = &get_instance();
    //we need merge functionality here, so initialze a merge cache
    $this->crossref_cache = array();
    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."publicationauthorlink
                                   WHERE ".AIGAION_DB_PREFIX."publication.actualyear = ".$CI->db->escape($year_id)."
                                   AND ".AIGAION_DB_PREFIX."publication.pub_id = ".AIGAION_DB_PREFIX."publicationauthorlink.pub_id
                                   ORDER BY ".$orderby." ".$limit);

    $result = array();
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }

    unset($this->crossref_cache);
    return $result;
  }
  
    /** change the crossref of all affected publications to reflect a change of the bibtex_id of the given publication.
    Note: this method does NOT make use of getByID($pub_id), because one should also change the referring 
    crossref field of all publications that are inaccessible through getByID($pub_id) due to access level 
    limitations. */
    function changeAllCrossrefs($pub_id, $old_bibtex_id, $new_bibtex_id) 
    {
        $CI = &get_instance();
        if (trim($old_bibtex_id) == '')return;
        $Q = $CI->db->get_where('publication',array('crossref'=>$old_bibtex_id));
        //update is done here, instead of using the update function, as some of the affected publications
        // may not be accessible for this user
        foreach ($Q->result() as $R) {
            $updatefields =  array('crossref'=>$new_bibtex_id);
            $CI->db->update('publication', $updatefields, array('pub_id'=>$R->pub_id));
    		if (mysql_error()) {
    		    appendErrorMessage(sprintf(__("Failed to update the bibtex-id in publication %s"),$R->pub_id).".<br/>");
        	}
        }
    }

    /** returns all accessible publications, as a map (id=>publication) */
    function getAllPublicationsAsMap() {
        $CI = &get_instance();
        $result = array();
        $CI->db->order_by('bibtex_id');
        $Q = $CI->db->get('publication');
        foreach ($Q->result() as $R) {
            $next = $this->getFromRow($R);
            if ($next != null) {
                $result[$next->pub_id] = $next;
            }
        }
        return $result;
    }
    /** returns all accessible publications from a topic, as a map (id=>publication), for export purposes */
    function getForTopicAsMap($topic_id) {
        $CI = &get_instance();
        $result = array();
        $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."topicpublicationlink
        WHERE ".AIGAION_DB_PREFIX."topicpublicationlink.topic_id = ".$CI->db->escape($topic_id)."
        AND ".AIGAION_DB_PREFIX."publication.pub_id = ".AIGAION_DB_PREFIX."topicpublicationlink.pub_id
        ORDER BY bibtex_id");
    
        foreach ($Q->result() as $row)
        {
          $next = $this->getFromRow($row);
          if ($next != null)
          {
            $result[$next->pub_id] = $next;
          }
        }
        return $result;
    }
  /** Return a list of publications for the bookmark list of the logged user, as a map (id=>publication), for export purposes  */
  function getForBookmarkListAsMap()
  {
    $CI = &get_instance();
    $userlogin = getUserLogin();
    
    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."userbookmarklists
    WHERE ".AIGAION_DB_PREFIX."userbookmarklists.user_id=".$CI->db->escape($userlogin->userId())."
    AND   ".AIGAION_DB_PREFIX."userbookmarklists.pub_id=".AIGAION_DB_PREFIX."publication.pub_id
    ORDER BY bibtex_id");

    $result = array();
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[$next->pub_id] = $next;
      }
    }
    return $result;
  }    
  function getForAuthorAsMap($author_id,$include_synonyms=false)
  {
    $CI = &get_instance();
    
    $result = array();
    
    $author_cond = AIGAION_DB_PREFIX."publicationauthorlink.author_id = ".$CI->db->escape($author_id);
    #if synonynms must be included: get the list of synonyms and build a new author-sql-condition
    if ($include_synonyms)
    {
      $author = $CI->author_db->getByID($author_id);
      if ($author==null) return $result;
      //get synonyms; make list of author_ids... INCLUDING PRIMARY AUTHOR
      $authors = $author->getSynonyms(true);
      $author_cond = AIGAION_DB_PREFIX."publicationauthorlink.author_id IN (".$authors[0]->author_id;
      for ($i = 1; $i < count($authors); $i++) 
      {
        $author_cond .= ",".$authors[$i]->author_id;
      }
      $author_cond .= ")";
    }
    //we need merge functionality here, so initialze a merge cache
    $this->crossref_cache = array();
    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."publication.* FROM ".AIGAION_DB_PREFIX."publication, ".AIGAION_DB_PREFIX."publicationauthorlink
    WHERE ".$author_cond."
    AND ".AIGAION_DB_PREFIX."publication.pub_id = ".AIGAION_DB_PREFIX."publicationauthorlink.pub_id
    ORDER BY bibtex_id");

    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[$next->pub_id] = $next;
      }
    }

    return $result;
  }
  
  
    /** splits the given publication map (id=>publication) into two maps [normal,xref],
    where xref is the map with all crossreffed publications (including those not present
    in the original map), and normal is the map with all other publications. 
    If $merge is true, all crossref entries will additionally be merged into their referring entries.
    */
    function resolveXref($publicationMap, $merge=false) {
        $normal=$publicationMap;
        $xref=array();
        foreach ($publicationMap as $pub_id=>$publication) {
            //$publication null? then it was apparently a crossref that was moved to the xref array; skip
            if ($publication==null) {
                continue;
            }
            //has crossref? 
            if (trim($publication->crossref)!='') {
                //get publication for crossref
                $xrefpub = $this->getByBibtexID($publication->crossref);
                if ($xrefpub!=null) {
                    //  find crossref in xref; 
                    if (!array_key_exists($xrefpub->pub_id,$xref)) {
                        if (array_key_exists($xrefpub->pub_id,$normal)) {
                            //  if not exists in xref find in and move from $normal to $xref; 
                            $xref[$xrefpub->pub_id]=$normal[$xrefpub->pub_id];
                            $normal[$xrefpub->pub_id] = null;
                        } else {
                            //  if not there either get from database and add to $xref
                            $xref[$publication->crossref] = $xrefpub;
                        }
                        if ($merge) {
                            appendMessage(__('resolveXref: merge xref into publication!').'<br/>');
                        }
                    }
                } //else: don't do a thing; leave the publication in $normal where it was put in the first place
            }
        }
        //finally, remove all entries from $normal that were set to null
        $finalnormal=array();
        foreach ($normal as $pub_id=>$publication) {
            if ($publication!=null) {
                $finalnormal[$pub_id] = $publication;
            }
        }
        return array($finalnormal,$xref);
    }
    
    /*
    reviewTitle($publication) -> checks for duplicate titles.
    */
    function reviewTitle($publication) {
      $CI = &get_instance();
      $CI->load->library('bibtex2utf8');
      $CI->load->helper('cleanname');
      
      $publication->cleantitle = $publication->title;
      if (getConfigurationSetting('CONVERT_BIBTEX_TO_UTF8')!='FALSE') {
        $publication->cleantitle = $CI->bibtex2utf8->bibCharsToUtf8FromString($publication->title);
      }
      $publication->cleantitle = cleanTitle($publication->cleantitle);

      $Q = $CI->db->query("SELECT DISTINCT cleantitle FROM ".AIGAION_DB_PREFIX."publication
                           WHERE cleantitle = ".$CI->db->escape($publication->cleantitle));
  
      $num_rows = $Q->num_rows();
      if ($num_rows > 0)
      {
        return __("A publication with the same title exists. Please make sure that the publication you are importing is not already in the database.");
      }
      else return null;
    }
    
    /*
    reviewBibtexID($publication) -> checks for duplicate cite_id. If the publication ID is set, one duplicate is allowed.
    */
    function reviewBibtexID($publication) {
      $CI = &get_instance();
      if (trim($publication->bibtex_id)=='') return null;
      $Q = $CI->db->query("SELECT pub_id,title FROM ".AIGAION_DB_PREFIX."publication
                           WHERE bibtex_id = ".$CI->db->escape($publication->bibtex_id));
  
      $num_rows = $Q->num_rows();
      if ($num_rows > 0)
      {
        foreach ($Q->result() as $row)
        {
          if ($row->pub_id != $publication->pub_id)
          {
            $message = __("The cite id is not unique, please choose another cite id.")
                       ."<br/>"
                       .__("Publication with same cite id")
                       .": \"".$row->title."\"";
            
            $Q2 = $CI->db->query("SELECT bibtex_id,pub_id FROM ".AIGAION_DB_PREFIX."publication
                                 WHERE bibtex_id LIKE ".$CI->db->escape($publication->bibtex_id."%"));
            $num_rows2 = $Q2->num_rows();
            
            if ($num_rows2 > 1)
            {
              $list = "";
              foreach ($Q2->result() as $row2)
              {
                if ($row2->pub_id != $publication->pub_id)
                  $list .= "<li>".$row2->bibtex_id."</li>\n";
              }
              if ($list != "")
                $message .= "<br/>".__("Similar cite ids").":<br/><ul>\n".$list."</ul>\n";
              
            }
            return $message;  
          }
        }
      }
      return null;
    }
    
    /** return the mark given to the publication by the user, or '' if the publication was read but not marked, 
    or -1 if the publication wasn't read */
    function getUserMark($pub_id,$user_id) {
        $CI = &get_instance();
        if (trim($pub_id)=='') return;
        $Q = $CI->db->get_where('userpublicationmark',array('pub_id'=>$pub_id,'user_id'=>$user_id));
        if ($Q->num_rows()==0) {
            return -1;
        }
        $R = $Q->row();
        if ($R->hasread == 'n') {
            return -1;
        }
        return $R->mark;
    }
    function read($mark,$oldmark,$pub_id,$user_id) {
        $CI = &get_instance();
        if (trim($pub_id)=='') return;
        //set proper mark for user
        $Q = $CI->db->delete("userpublicationmark",array('pub_id'=>$pub_id,'user_id'=>$user_id));
        $Q = $CI->db->query("INSERT INTO ".AIGAION_DB_PREFIX."userpublicationmark 
                                (`user_id`,`pub_id`,`hasread`,`mark`)
                                VALUES
                                (".$user_id.",".$pub_id.",'y','".$mark."')");
        //and now fix total mark
        $this->recalcTotalMark($pub_id);
    }
    function unread($oldmark,$pub_id,$user_id) {
        $CI = &get_instance();
        if (trim($pub_id)=='') return;
        //set proper mark for user
        $Q = $CI->db->query("UPDATE ".AIGAION_DB_PREFIX."userpublicationmark 
                                SET `hasread`='n' 
                              WHERE pub_id=".$pub_id." 
                                    AND user_id=".$user_id);
        //and now fix total mark
        $this->recalcTotalMark($pub_id);
    }
    //returns new mark
    function recalcTotalMark($pub_id) {
        $CI = &get_instance();
        if (trim($pub_id)=='') return;
        $Q = $CI->db->get_where('userpublicationmark',array('pub_id'=>$pub_id));
        $totalmark = 0;
        $count = 0;
        foreach ($Q->result() as $R) {
            if ($R->hasread=='y') {
                $count++;
                $totalmark += $R->mark;
            }
        }
        $newmark = 0;
        if ($count!=0) {
            $newmark = $totalmark / $count;
        }
        $CI->db->where('pub_id', $pub_id);
        $CI->db->update('publication',array('mark'=>$newmark));
        return $newmark;
    }

    /** reorder authorlist based on given map from new rank to old rank */
//    function reorderauthors($pub_id, $reorder, $editors='n') {
//        $CI = &get_instance();
//        $userlogin  = getUserLogin();
//        
//        $CI->db->select('MAX(rank)');
//        $Q = $CI->db->get_where('publicationauthorlink', array('is_editor'=>$editors,'pub_id'=>$pub_id));
//        $R = $Q->row_array();
//        $maxrank = $R['MAX(rank)'];
//        $Q = $CI->db->get_where('publicationauthorlink', array('is_editor'=>$editors,'pub_id'=>$pub_id));
//        foreach ($Q->result() as $row) {
//            $CI->db->query('UPDATE '.AIGAION_DB_PREFIX.'publicationauthorlink SET rank='.($row->rank+$maxrank).' WHERE pub_id='.$pub_id.' AND rank='.$row->rank." AND is_editor='".$editors."'");
//        }
//        foreach ($reorder as $newrank => $oldrank) {
//            //$newrank starts at 0, but in table should start at 1
//            $CI->db->query('UPDATE '.AIGAION_DB_PREFIX.'publicationauthorlink SET rank='.($newrank+1).' WHERE pub_id='.$pub_id.' AND rank='.($oldrank+$maxrank+1)." AND is_editor='".$editors."'");
//        }
//    }    

    function generateUniqueSuffix()
    {
    	$suffix = md5(time());
    	while (file_exists($suffix)) {
    		$suffix= md5(time());
    	}
    	return $suffix;
    }

}

?>