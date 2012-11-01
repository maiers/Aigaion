<?php 

  // restrict direct script access
  if (!defined('BASEPATH')) exit('No direct script access allowed');
  
  // check required parameters
  if (!isset($publications) || !is_array($publications))
  {
    // @todo throw an error!
  }
  if (!isset($available_order) || !is_array($available_order))
  {
    // @todo throw an error!
  }  
  
  //first we get all necessary values required for displaying
  $userlogin  = getUserLogin();
  
  //Switch off the bookmarklist buttons by passing the parameter $noBookmarkList=True to this view, else default from rights  
  $useBookmarkList = (isset($noBookmarkList) && ($noBookmarkList == true)) ? false : $userlogin->hasRights('bookmarklist'); 
    
  // determain the number of publications in the list
  if (!isset($pubCount) || ($pubCount==0))
  {
    $pubCount = sizeof($publications);
  }
  
  //retrieve the publication summary and list stype preferences (author first or title first etc)
  $summarystyle = $userlogin->getPreference('summarystyle');
  $liststyle    = $userlogin->getPreference('liststyle');
  
  // create pagination
  $pagination = array();
  if ($page != -1 && $liststyle > 0) {

  	// goto first
  	$pagination[] = array('href' => site_url('/publications/showlist/' . $order . '/'), 'text' => __('First'), 'active' => 0 == $page);
  	// goto previous
  	if ($page > 0) {
  		$pagination[] = array('href' => site_url('/publications/showlist/' . $order . '/' . ($page - 1)), 'text' => __('Previous'), 'active' => false);
  	}
	 	$pageCount = floor($pubCount / $liststyle);
  	for ($i = 0; $i <= $pageCount; $i++) {
			
	  		if ($pageCount <= 7 || ($i >= $page-2 && $i <= $page+2))
	  		{
	  			$pagination[] = array('href' => site_url('/publications/showlist/' . $order . '/' . $i), 'text' => $i+1, 'active' => $i == $page);
	  		}
	  		else if ($pagination[count($pagination)-1]['text'] != '...') { 			
	  			$pagination[] = array('href' => null, 'text' => '...', 'active' => false);
	  		}
			
  	}
  	
  	// goto next
  	if ($page < $pageCount) {
  		$pagination[] = array('href' => site_url('/publications/showlist/' . $order . '/' . ($page + 1)), 'text' => __('Next'), 'active' => false);
  	}
  	// goto last
  	$pagination[] = array('href' => site_url('/publications/showlist/' . $order . '/' . $pageCount), 'text' => __('Last'), 'active' => $pageCount == $page);
  }
  
  // calculate jump marks
  $orderByIndex = array();
  $sumCount = 0;
  // we cannot mix up the sorting in the pub structure, so we need to reverse it
  // depending on the sort order. default will be ASC
  if ($order == 'year' || $order == 'rating')
  {
  	$pubStruct = array_reverse($pubStruct);
  }
  foreach($pubStruct as &$item)
  {
  	if ($item['group'] == $order)
  	{
  		// get a default text for null values
  		$text = ($item['value'] != null) ? $item['value'] : 'none';
  		// build the anchor link for the item
			$href = '/publications/showlist/' . $order;
  		if ($liststyle > 0)
  		{
  			$indexPage = floor($sumCount / $liststyle);
  			$href .= '/'. $indexPage;
  		}  		
  		$href .= '#ix' . $text;
  		// add to output array  		
  		$orderByIndex[] = array(
    			'text' => $text, 
    			'title' => __('Jump to index item') .' '. $text, 
    			'href' => site_url($href), 
    			'count' => $item['count']
  		);
  		$sumCount += $item['count'];
  	}
  }
  
  // get header title
  switch ($order) {
    case 'type':
      $header_title = __('All publications').' '.__('sorted by journal and type');
      break;
    case 'recent':
      $header_title = __('All publications').' '.__('sorted by recency');
      break;
    case 'title':
      $header_title = __('All publications').' '.__('sorted by title');
      break;
    case 'author':
      $header_title = __('All publications').' '.__('sorted by author');
      break;
    default:
      $header_title = __('All publications');
  }
  
  // get available sort orders
  $sort_order = array();
  foreach ($available_order as $a_order) {
    if ($a_order != $order) {
      $sort_order[] = array('text' => $a_order,
      			    		'href' => site_url('publications/showlist/' . $a_order),
      					  	'title' => __('Sort by ' . $a_order));
    }
  }
  
  // create output format
  $pubs = array();
  $subheader = null;
  foreach ($publications as &$publication) {

    // --- HEADER ---
    // --------------
    //check whether we should display a new header/subheader, depending on the $order parameter
    switch ($order) {
      case 'year':
        if ($subheader != $publication->actualyear || $subheader == null)
        {
          $subheader = $publication->actualyear;
        }
        break;
      case 'title':
        if ($subheader != $publication->cleantitle[0] || $subheader == null)
        {
          $subheader = $publication->cleantitle[0];
        }
        break;
      case 'author':
        $tmpCleanAuthor = (strlen($publication->cleanauthor) > 0) ? $publication->cleanauthor[0] : '?';
        if ($subheader != $tmpCleanAuthor || $subheader == null)
        {
          $subheader = $tmpCleanAuthor;
        }
        break;
      case 'type':
        if ($subheader != $publication->pub_type || $subheader == null)
        {
          $subheader = $publication->pub_type;
          //sprintf(__('Publications of type %s'),$subheader)
        }
        break;
      case 'rating':
      	if ($subheader != $publication->mark || $subheader == null)
        {
          $subheader = $publication->mark;
        }
        break;
      case 'recent':
        // @todo add update/creation date to publication model and use this field for sorting
        break;
      default:
        break;
    }
        
    // --- TITLE ---
    // -------------
    //remove braces in list display
    $displayTitle = $publication->title;
    //insert here condition that says 'no replacing if latex code' (i.e. any remaining backslash)
    if ((strpos($displayTitle,'$')===false) && (strpos($displayTitle,"\\") === false))
    {
      $displayTitle = str_replace(array('{','}'),'',$displayTitle);
    }
    
    // --- AUTHORS ---
    // ---------------
    $authors = array();
    foreach ($publication->authors as $author)
    {
      $authors[] = array('href' => site_url('/authors/show/' . $author->author_id),
                         'text' => $author->getName(),
                         'title' => sprintf(__('All information on %s'), $author->cleanname));
    }
		$editors = array();
	    // show editors only if no authors have been found
	    if (count($authors) == 0) {
	    foreach ($publication->editors as $editor) {
	    	$editors[] = array('href' => site_url('authors/show/'.$editor->author_id),
	      	                   'text' => $editor->getName(),
	      	                   'title' => sprintf(__('All information on %s'),$editor->cleanname));
	    }
    }
    
    // --- ACTIONS ---
    // ---------------
    $actions = array();
    if ($useBookmarkList)
    {
      $actions[] = array('title' => ($publication->isBookmarked) ? __('Click to UnBookmark publication') : __('Click to Bookmark publication'),
                         'href' => ($publication->isBookmarked) ? site_url('/bookmarklist/removepublication/'.$publication->pub_id) : site_url('/bookmarklist/addpublication/'.$publication->pub_id),
                         'text' => ($publication->isBookmarked) ? __('UnBookmark publication') : __('Bookmark publication'));
      $actions[] = array('href' => '#',
          			   	 'text' => __('Show Notes'),
          			   	 'title' => __('Show Notes'));
      $actions[] = array('href' => '#',
                		 'text' => __('Delete Publication'),
                		 'title' => __('Delete "') . ' ' . $publication->cleantitle . '"');
    }
    
    // --- NOTES ---
    // -------------
    

    $pubs[] = array('group' => $subheader,
   					'title' => $displayTitle,
                   	'year' => $publication->actualyear,
                   	'href' => site_url('/publications/show/' . $publication->pub_id),
                   	'authors' => $authors,
                   	'editors' => $editors,
                   	'actions' => $actions);
  }
  
  unset($publications);
    
  // merge variables
  $this->tbswrapper->tbsLoadTemplate(APPPATH . 'templates/publications/list.tpl.html');
  $this->tbswrapper->tbsMergeField('editortext', __('Editors'));
  $this->tbswrapper->tbsMergeBlock('publication', $pubs);
  $this->tbswrapper->tbsMergeField('header', $header_title);
  $this->tbswrapper->tbsMergeField('orderBy', __('Sort by'));
  $this->tbswrapper->tbsMergeBlock('order', $sort_order);
  $this->tbswrapper->tbsMergeField('jumpTo', __('Jump to'));
  $this->tbswrapper->tbsMergeBlock('orderByIndex', $orderByIndex);
  $this->tbswrapper->tbsMergeField('page', __('Browse pages'));
  $this->tbswrapper->tbsMergeBlock('pagination_top', $pagination);
  $this->tbswrapper->tbsMergeBlock('pagination_bottom', $pagination);
  
  // render
  echo $this->tbswrapper->tbsRender();
  
?>