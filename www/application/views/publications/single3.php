<?php 

  // restrict direct script access
  if (!defined('BASEPATH')) exit('No direct script access allowed');
  
  // check required parameters
  if (!isset($publications) || !is_array($publications))
  {
    // @todo throw an error!
  }
  
  //first we get all necessary values required for displaying
  $userlogin  = getUserLogin();
  
  //Switch off the bookmarklist buttons by passing the parameter $noBookmarkList=True to this view, else default from rights  
  $useBookmarkList = (isset($noBookmarkList) && ($noBookmarkList == true)) ? false : $userlogin->hasRights('bookmarklist'); 
      
  //retrieve the publication summary and list stype preferences (author first or title first etc)
  $summarystyle = $userlogin->getPreference('summarystyle');
  $liststyle    = $userlogin->getPreference('liststyle');
      
    
  // --- AUTHORS ---
  // ---------------
  $authors = array();
  foreach ($publication->authors as $author)
  {
  	$authors[] = array('href' => site_url('authors/show/'.$author->author_id),
                       'text' => $author->getName(),
                       'title' => sprintf(__('All information on %s'),$author->cleanname));
  }
  $editors = array();
  foreach ($publication->editors as $editor) {
  	$editors[] = array('href' => site_url('authors/show/'.$editor->author_id),
  	                   'text' => $editor->getName(),
  	                   'title' => sprintf(__('All information on %s'),$editor->cleanname));
  }

  // --- ACTIONS ---
  // ---------------
  $actions = array();
  if ($useBookmarkList)
  {
  	$actions[] = array('title' 	=> ($publication->isBookmarked) ? __('Click to UnBookmark publication') : __('Click to Bookmark publication'),
                       'href' 	=> ($publication->isBookmarked) ? site_url('/bookmarklist/removepublication/'.$publication->pub_id) : site_url('/bookmarklist/addpublication/'.$publication->pub_id),
                       'text' 	=> ($publication->isBookmarked) ? __('UnBookmark publication') : __('Bookmark publication'));
  	$actions[] = array('href' 	=> '#',
                		 	 'text' 	=> __('Delete Publication'),
                		 	 'title'	=> __('Delete') . ' "' .  $publication->cleantitle . '"');
  	$actions[] = array('href' 	=> '#abstract',
                		 	 'text' 	=> __('Abstract'),
                		   'title' 	=> '');
  	$actions[] = array('href' 	=> '#details',
  	                	 'text' 	=> __('Details'),
  	                	 'title' 	=> '');
  	$actions[] = array('href' 	=> '#notes',
  	                	 'text' 	=> __('Notes'),
  	                	 'title' 	=> '');  	
  }
  
  // --- NOTES ---
  // -------------
  $this->load->model('note');
  $this->load->model('user');
  
  $notes = $publication->getNotes();
  $users = array();
  $outNotes = array();
  foreach ($notes as &$note) {
  	if (!array_key_exists($note->user_id, $users)) {
  		$users[$note->user_id] = $this->user_db->getByID($note->user_id);
  	}
  	$outNotes[] = array(
  		'id' => $note->note_id,
  		'user_id' => $note->user_id,
  		'group_id'=> $note->group_id,
  		'author' => $users[$note->user_id]->login,
  		'text' => $note->text,
  	);
  }
  
  // --- Attachments ---
  // -------------------
  $att = $publication->getAttachments();
  $attachments = array();
  foreach($att as &$attachment) {
  	$attachments[] = array(
  		'href' 	=> site_url('/attachments/single/' . $attachment->att_id),
  		'title' => __('Show') .' '. $attachment->name,
  		'text'	=> $attachment->name,
  		'note'	=> $attachment->note,
  		'id'		=> $attachment->att_id,
  		'ismain'=> ($attachment->ismain) ? '1' : '0'
  	);
  }
  
  // --- Keywords ---
  // ----------------
  $keys = $publication->getKeywords();
  $keywords = array();
  foreach ($keys as &$key) {
  	$keywords[] = array(
  		'href' 	=> site_url('/keywords/single/' . $key->keyword_id),
  		'text'	=> $key->keyword,
  		'title' => $key->keyword
  	);
  }
  
  // --- What to show ---
  // --------------------
  $view = array(
  	'Article' => array(
  				'mandatory' => array('author','title','journal','year'),
  				'optional' => array('volume','number','pages','month','note','key')),
  	'Book' => array(
    			'mandatory' => array('author','editor','title','publisher','year'),
    			'optional' => array('volume','series','address','edition','month','note','key')),
  	'Booklet' => array(
      		'mandatory' => array('title'),
      		'optional' => array('author', 'howpublished','address','month','year','note','key')),
  	'Conference' => array(
        	'mandatory' => array('author','title','booktitle','year'),
        	'optional' => array('editor','pages','organization','publisher','address','month','note','key')),
  	'Inbook' => array(
      		'mandatory' => array('author','editor','title','chapter','pages','publisher','year'),
      		'optional' => array('volume','series','address','edition','month','note','key')),
  	'Incollection' => array(
          'mandatory' => array('author','title','booktitle','year'),
          'optional' => array('editor','pages','organization','publisher','address','month','note','key')),
    'Inproceedings' => array(
      		'mandatory' => array('author','title','booktitle','year'),
      		'optional' => array('editor','pages','organization','publisher','address','month','note','key')),
	  'Manual' => array(
        	'mandatory' => array('title'),
        	'optional' => array('author','organization','address','edition','month','year','note','key')),
  	'Masterthesis' => array(
          'mandatory' => array('author','title','school','year'),
          'optional' => array('address','month','note','key')),
  	'Misc' => array(
          'mandatory' => array(),
          'optional' => array('author','title','howpublished','month','year','note','key')),
  	'Phdthesis' => array(
          'mandatory' => array('author','title','school','year'),
          'optional' => array('address','month','note','key')),
  	'Proceedings' => array(
        	'mandatory' => array('title','year'),
        	'optional' => array('editor','publisher','organization','address','month','note','key')),
    'Techreport' => array(
    			'mandatory' => array('author','title','institution','year'),
    			'optional' => array('type','number','address','month','note','key')),
  	'Unpublished' => array(
      		'mandatory' => array('author','title','note'),
      		'optional' => array('month','year','key'))
  );
  
  foreach ($view as &$v) {
  	$v['aigaion'] = array('pub_type', 'citation', 'issn', 'crossref', 'url', 'doi', 'userfields', 'status'); 
  }
  
  // @todo implement a sort order for all fields
  $sortOrder = array(
  	'author' => 0,
  	'title' => 1,
  	'journal' => 2,
  	'year' => 3,
  	'month' => 4
  );
    
  // --- Details ---
  // ---------------
  $details = array();  
  
  // mapping from publication class types to labels
  $mapping = array(
  	'pub_type' => 'Type of publication',
  	'title' => 'Title',
	  'journal' => 'Journal',
		'year' => 'Year',
	  'volume' => 'Volume',
		'number' => 'Number',
		'booktitle' => 'Booktitle',
		'edition' => 'Edition',
		'series' => 'Series',		
		'month' => 'Month',
		'pages' => 'Pages',
		'issn' => 'ISSN',
		'isbn' => 'ISBN',
		'doi' => 'DOI',
		'url' => 'URL',
		'crossref' => 'Crossref',
		'chapter' => 'Chapter',
		'publisher' => 'Publisher',
		'location' => 'Location',
		'institution' => 'Institution',
		'organization' => 'Organization',
		'school' => 'School',
		'address' => 'Address',
		'howpublished' => 'How Published'
  );
  
  // add an output field for each field of the current type
  $type = $publication->pub_type;
  foreach ($mapping as $name => $label) {
  	$mandatory = in_array($name, $view[$type]['mandatory']);
  	$optional = in_array($name, $view[$type]['optional']);
  	$aigaion = in_array($name, $view[$type]['aigaion']); 
  	
  	if ($mandatory) {
  		$class = 'c1_mandatory zebra';
  	} else if ($optional) {
  		$class = 'c2_optional zebra';
  	} else if ($aigaion) {
  		$class = 'c3_custom zebra';
  	} else {
  		$class = 'hidden';
  	}
  	
  	$details[] = array(
  			'label' 	=> __($label), 
  			'name'  	=> $name,
  			'value' 	=> $publication->$name,
  			'class'	 	=> $class,
  			'custom'	=> count($details)
  	);
  }

	// sort detail fields by class
	$sortByClass = array();
	$sortByCustom = array();
	foreach ($details as $key => $row) {
		$sortByClass[$key] = $row['class'];
		$sortByCustom[$key]  = $row['custom'];
	}	
	//array_multisort($sortByClass, SORT_ASC, $sortByCustom, SORT_ASC, $details);
	
	// get pubtypes
	$pubtypes = array_keys($view);
	foreach($pubtypes as &$value) {
		$value = array('title' => __($value), 'selected' => $value == $type);
	}
	
	// fix texts
	$language = array(
		'abstract' 		=> __('Abstract'),
		'details'			=> __('Details'),
		'notes'				=> __('Notes'),
		'topics'	 		=> __('Topics'),
		'attachments'	=> __('Attachments'),
		'edit'				=> __('Edit'),
		'delete'			=> __('Delete'),
		'add'					=> __('Add'),
		'setmain'			=> __('Set main'),
		'tsetmain'		=> __('Set as main attachment'),
		'keywords'		=> __('Keywords'),
		'url'					=> site_url('')
	);
		
  // merge variables
  $this->tbswrapper->tbsLoadTemplate(APPPATH . 'templates/publications/single.tpl.html');
  $this->tbswrapper->tbsMergeField('publication', $publication);
  $this->tbswrapper->tbsMergeBlock('pubtypes', $pubtypes);
  $this->tbswrapper->tbsMergeBlock('authors', $authors);
  $this->tbswrapper->tbsMergeField('editAuthors', __('Edit Authors'));
  $this->tbswrapper->tbsMergeField('editortext', __('Editors'));
  $this->tbswrapper->tbsMergeBlock('editors', $editors);
  $this->tbswrapper->tbsMergeField('editEditors', __('Edit Editors'));
  $this->tbswrapper->tbsMergeBlock('actions', $actions);
  $this->tbswrapper->tbsMergeBlock('details', $details);
  $this->tbswrapper->tbsMergeField('visibleFields', json_encode($view));
  $this->tbswrapper->tbsMergeField('lang', $language);
  $this->tbswrapper->tbsMergeField('editLink', site_url('publications/edit/' . $publication->pub_id));
  $this->tbswrapper->tbsMergeField('postLink', site_url('publications/post/' . $publication->pub_id));
  $this->tbswrapper->tbsMergeBlock('note', $outNotes);
	$this->tbswrapper->tbsMergeBlock('attachments', $attachments);
	$this->tbswrapper->tbsMergeBlock('keywords', $keywords);
  
  // render
  echo $this->tbswrapper->tbsRender();
  
?>