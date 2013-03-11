<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Publications extends CI_Controller {

  var $available_sort_options;
  
  function __construct()
  {
    parent::__construct();
    $this->load->model('publication');
    $this->available_sort_options = array('year','type','recent','title','author','rating');
  }

  /** Default function: list publications */
  function index()
  {
    $this->showlist();
  }
  
  function post() {
  	
  	$this->load->model('publication');
  	$pub_id   = $this->uri->segment(3);
  	
  	// load publication
    $publication = $this->publication_db->getByID($pub_id);
  	
    $dirty = false;
    foreach ($_POST as $key => $value) {
    	if (property_exists($publication, $key)) {
    		if ($publication->$key != trim($value)) {
    			$publication->$key = trim($value);
    			$dirty = true;
    		} 
    	}
    }
    
    // validate changed publication
    if (!$this->publication_db->validate($publication)) {
    	die(json_encode(array('type' => 'failure', 'code' => '10', 'message' => __('No valid change'))));
    }
        
    // write back if something needs to be changed
    if ($dirty) {
    	// try writing to the db
    	if ($this->publication_db->update($publication)) {
    		// successfully written
    		die(json_encode(array('type' => 'success', 'code' => '-1', 'message' => null)));
    	} else {
    		// could not write
    		die(json_encode(array('type' => 'failure', 'code' => '20', 'message' => __('Could not write to database'))));
    	}
    } else {
    	// nothing changed
    	die(json_encode(array('type' => 'success', 'code' => '0', 'message' => null)));
    }
  	
  }
  
  /**
   publications/show

   Calls single publication view

   Fails with error message when one of:
   insufficient user rights
   publication does not exist

   Parameters passed via URL segments:
   pub_id

   Returns:
   A single publication overview
   */
  function show($pub_id)
  {

    $this->load->library('tbswrapper');
    $this->load->helper('publication');

    if (!is_numeric($pub_id))
    {
      //retrieve publication ID
      $pub_id   = $this->uri->segment(3);
    }
    $categorize = $this->uri->segment(4,'');

    // invalid pub_id
    if (!$pub_id) {
      redirect('');
    }


    //load publication
    $publication = $this->publication_db->getByID($pub_id);
    if ($publication == null)
    {
      //attempt to retrieve by bibtex_id
      if ($pub_id == 'bibtex_id') {
        $bibtex_id = $this->uri->segment(4,'');
        $categorize = $this->uri->segment(5,'');
        if ($bibtex_id!='')
        $publication = $this->publication_db->getByBibtexID($bibtex_id);
      }
      if ($publication == null)
      {
        appendErrorMessage(__("View publication").": ".__("non-existing id passed").".<br/>");
        redirect('');
      }
    }

    //set header data
    $header ['title']       = $publication->title;
    $header ['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
    $content['publication'] = $publication;
    $content['categorize']  = $categorize=='categorize';

    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('publications/single', $content, true);
    $output .= $this->load->view('footer',              '',       true);

    //set output
    $this->output->set_output($output);
  }


  /**
   publications/showcite

   Calls single publication view for bibtex cite_id rather than pub_id
   No other parameters besides bibtex_id are possible (such as e.g. 'categorize')
   because bibtex_id may contain slashes so we need to take all of the URI-remainder
   For such, use 'show' controller.


   Fails with error message when one of:
   insufficient user rights
   publication does not exist

   Parameters passed via URL segments:
   bibtex_id

   Returns:
   A single publication overview
   */
  function showcite()
  {
    $segments = $this->uri->segment_array();
    //remove first two elements
    array_shift($segments);
    array_shift($segments);

    $bibtex_id   = implode('/',$segments);


    //load publication
    $publication = $this->publication_db->getByBibtexID($bibtex_id);
    if ($publication == null)
    {
      appendErrorMessage(__("View publication").": ".sprintf(__("non-existing BibTeX id \"%s\" was passed"),$bibtex_id));
      redirect('');
    }

    //set header data
    $header ['title']       = $publication->title;
    $header ['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
    $content['publication'] = $publication;

    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('publications/single', $content, true);
    $output .= $this->load->view('footer',              '',       true);

    //set output
    $this->output->set_output($output);
  }

  /**
   publications/showlist

   Entry point for showing a list of publications.

   fails with error message when one of:
   insufficient user rights

   Parameters passed via URL segments:
   3rd: order by info
   4rth: page number

   Returns:
   A full HTML page with all a list of all publications
   */
  function showlist()
  {
  	$this->load->helper('publication');
  	$order   = $this->uri->segment(3,'year');
  	if (!in_array($order,array('year','type','recent','title','author'))) {
  		$order='';
  	}
  	$page   = $this->uri->segment(4,0);
  	//get output
  	$headerdata                 = array();
  	$headerdata['title']        = __('Publication list');
  	$headerdata ['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');//, 'publicationlists.js');
  	$headerdata['sortPrefix']        = 'publications/showlist/';
  	
  	$userlogin = getUserLogin();
  	$content['header']          = __('All publications');
  	switch ($order) {
  		case 'type':
  			$content['header']          = __('All publications').' '.__('sorted by journal and type');
  			break;
  		case 'recent':
  			$content['header']          = __('All publications').' '.__('sorted by recency');
  			break;
  		case 'title':
  			$content['header']          = __('All publications').' '.__('sorted by title');
  			break;
  		case 'author':
  			$content['header']          = __('All publications').' '.__('sorted by author');
  			break;
  	}
  	
  	
  	if ($userlogin->getPreference('liststyle')>0) {
  		//set these parameters when you want to get a good multipublication list display
  		$content['multipage']       = True;
  		$content['pubCount']        = $this->topic_db->getVisiblePublicationCountForTopic('1');
  		$content['currentpage']     = $page;
  		$content['multipageprefix'] = 'publications/showlist/'.$order.'/';
  	}
  	$content['publications']    = $this->publication_db->getVisibleForTopic('1',$order,$page);
  	$content['order'] = $order;
  	
  	$output = $this->load->view('header', $headerdata, true);
  	$output .= $this->load->view('publications/list', $content, true);
  	
  	
  	
  	$output .= $this->load->view('footer','', true);
  	
  	//set output
  	$this->output->set_output($output);

  }
  /**
   publications/unassigned

   Entry point for showing a list of publications that are not assigned to a topic.

   fails with error message when one of:
   insufficient user rights

   Parameters passed via URL segments:
   3rd: order by info
   4rth: page number

   Returns:
   A full HTML page with all a list of all publications that are not assigned to a topic.
   */
  function unassigned()
  {
    $this->load->helper('publication');
    $order   = $this->uri->segment(3,'year');
    if (!in_array($order,array('year','type','recent','title','author'))) {
      $order='';
    }
    $page   = $this->uri->segment(4,0);
    //get output
    $headerdata                 = array();
    $headerdata['title']        = __('Publication list');
    $headerdata ['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');//, 'publicationlists.js');
    $headerdata['sortPrefix']        = 'publications/unassigned/';

    $userlogin = getUserLogin();
    $content['header']          = __('All publications not assigned to a topic');
    switch ($order) {
      case 'type':
        $content['header']          = __('All publications not assigned to a topic').' '.__('sorted by journal and type');
        break;
      case 'recent':
        $content['header']          = __('All publications not assigned to a topic').' '.__('sorted by recency');
        break;
      case 'title':
        $content['header']          = __('All publications not assigned to a topic').' '.__('sorted by title');
        break;
      case 'author':
        $content['header']          = __('All publications not assigned to a topic').' '.__('sorted by author');
        break;
    }


    if ($userlogin->getPreference('liststyle')>0) {
      //set these parameters when you want to get a good multipublication list display
      $content['multipage']       = True;
      $content['currentpage']     = $page;
      $content['multipageprefix'] = 'publications/unassigned/'.$order.'/';
    }
    $content['publications']    = $this->publication_db->getUnassigned($order);
    $content['order'] = $order;

    $output = $this->load->view('header', $headerdata, true);
    $output .= $this->load->view('publications/list', $content, true);



    $output .= $this->load->view('footer','', true);

    //set output
    $this->output->set_output($output);

  }

  /** download the cover image of this publication */
  function coverimage()
  {
    $pub_id = $this->uri->segment(3,-1);

    $publication = $this->publication_db->getByID($pub_id);
    if ($publication == null) {
      appendErrorMessage(__('Show cover image').': '.__('non-existing id passed').'.<br/>');
      return;
    }
    $output = $this->load->view('publications/coverimage',array('publication'=>$publication), true);

    //set output
    $this->output->set_output($output);
  }
  /** edit form to upload new cover images */
  function uploadcoverimage()
  {
    $pub_id = $this->uri->segment(3,-1);

    $publication = $this->publication_db->getByID($pub_id);
    if ($publication == null) {
      appendErrorMessage(__('Upload cover image').': '.__('non-existing id passed').'.<br/>');
      redirect('');
    }

    $userlogin  = getUserLogin();
    if (    (!$userlogin->hasRights('publication_edit'))
    || !$this->accesslevels_lib->canEditObject($publication)
    )
    {
      appendErrorMessage(__('Upload cover image').': '.__('insufficient rights').'.<br/>');
      redirect('publications/show/'.$publication->pub_id);
    }

    //set header data
    $header ['title']       = __('Upload cover image');
    $header ['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
    $content['publication'] = $publication;

    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('publications/uploadcoverimage', $content, true);
    $output .= $this->load->view('footer',              '',       true);


    //set output
    $this->output->set_output($output);
  }
  /** edit form to commit new cover images */
  function commitcoverimage()
  {
    $pub_id = $this->input->post('pub_id');

    $publication = $this->publication_db->getByID($pub_id);
    if ($publication == null) {
      appendErrorMessage(__('Upload cover image').': '.__('non-existing id passed').'.<br/>');
      redirect('');
    }

    $userlogin  = getUserLogin();
    if (    (!$userlogin->hasRights('publication_edit'))
    || !$this->accesslevels_lib->canEditObject($publication)
    )
    {
      appendErrorMessage(__('Upload cover image').': '.__('insufficient rights').'.<br/>');
      redirect('publications/show/'.$publication->pub_id);
    }

    $this->publication_db->commitcoverimage($publication);

    redirect('publications/show/'.$publication->pub_id);
  }
  /** edit form to delete cover images */
  function deletecoverimage()
  {
    $pub_id = $this->uri->segment(3,-1);

    $publication = $this->publication_db->getByID($pub_id);
    if ($publication == null) {
      appendErrorMessage(__('Delete cover image').': '.__('non-existing id passed').'.<br/>');
      redirect('');
    }

    $userlogin  = getUserLogin();
    if (    (!$userlogin->hasRights('publication_edit'))
    || !$this->accesslevels_lib->canEditObject($publication)
    )
    {
      appendErrorMessage(__('Delete cover image').': '.__('insufficient rights').'.<br/>');
      redirect('publications/show/'.$publication->pub_id);
    }

    //set header data
    $header ['title']       = __('Delete cover image');
    $header ['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
    $content['publication'] = $publication;

    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('publications/deletecoverimage', $content, true);
    $output .= $this->load->view('footer',              '',       true);


    //set output
    $this->output->set_output($output);
  }
  /** delete cover images */
  function commitdeletecoverimage()
  {
    $pub_id = $this->input->post('pub_id');

    $publication = $this->publication_db->getByID($pub_id);
    if ($publication == null) {
      appendErrorMessage(__('Delete cover image').': '.__('non-existing id passed').'.<br/>');
      redirect('');
    }

    $userlogin  = getUserLogin();
    if (    (!$userlogin->hasRights('publication_edit'))
    || !$this->accesslevels_lib->canEditObject($publication)
    )
    {
      appendErrorMessage(__('Delete cover image').': '.__('insufficient rights').'.<br/>');
      redirect('publications/show/'.$publication->pub_id);
    }

    $this->publication_db->deletecoverimage($publication);

    redirect('publications/show/'.$publication->pub_id);
  }
  //Calls an empty publication edit form
  function add()
  {

    $this->edit();
  }

  //edit() - Call publication edit form. When no ID is given: new publicationform
  function edit($publication = "")
  {
    $this->load->helper('publication');
    $this->publication_db->suppressMerge = True;//note: in the edit form, we should NOT see the data from the crossreferenced publication, so suppress merging
    if (is_numeric($publication))
    {
      $pub_id = $publication;
      $publication = $this->publication_db->getByID($pub_id);
      $publication->getKeywords();
      $publication->getCustomFields();

      //set header data
      $edit_type = "edit";
    }
    else if (empty($publication))
    {
      //php4 compatiblity: new $this->publication won't work
      $publication = $this->publication;
      $edit_type = "add";
    }
    else
    {
      //there was a publication post, retrieve the edit type from the post.
      $edit_type = $this->input->post('edit_type');
    }

    $userlogin  = getUserLogin();
    $user       = $this->user_db->getByID($userlogin->userID());
    if (    (!$userlogin->hasRights('publication_edit'))
    || !$this->accesslevels_lib->canEditObject($publication)
    )
    {
      appendErrorMessage(__('Edit publication').': '.__('insufficient rights').'.<br/>');
      redirect('');
    }

    switch ($edit_type)
    {
      case 'add':
        $header ['title']       = __("Add publication");
        break;
      case 'edit':
        $header ['title']       = __("Edit publication");
        break;
    }
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js' , 'publications.js','externallinks.js');
    $content['edit_type']   = $edit_type;
    $content['publication'] = $publication;

    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('publications/edit',   $content, true);
    $output .= $this->load->view('footer',              '',       true);

    //set output
    $this->output->set_output($output);
  }

  //import() - Call publication import page
  //DR: is this controller ever called?
  function import()
  {
    $userlogin  = getUserLogin();
    $user       = $this->user_db->getByID($userlogin->userID());
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage(__('Import publication').': '.__('insufficient rights').'.<br/>');
      redirect('');
    }

    $header ['title']       = __("Import publications");
    $header ['javascripts'] = array();

    $content = "";
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('publications/import', $content, true);
    $output .= $this->load->view('footer',              '',       true);

    //set output
    $this->output->set_output($output);
  }

  /**
   publications/delete

   Entry point for deleting a publication.
   Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
   deleting.

   Fails with error message when one of:
   delete requested for non-existing publication
   insufficient user rights

   Parameters passed via URL segments:
   3rd: pub_id, the id of the to-be-deleted-publication
   4th: if the 4th segment is the string 'commit', no confirmation is requested.
   if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be
   called with 'commit' specified

   Returns:
   A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
   Redirects somewhere (?) after deleting, if 'commit' was specified
   */
  function delete()
  {
    $pub_id = $this->uri->segment(3);
    $publication = $this->publication_db->getByID($pub_id);
    $commit = $this->uri->segment(4,'');

    if ($publication==null) {
      appendErrorMessage(__('Delete publication').': '.__('non-existing id passed').'.<br/>');
      redirect('');
    }

    //besides the rights needed to READ this publication, checked by publication_db->getByID, we need to check:
    //edit_access_level and the user edit rights
    $userlogin  = getUserLogin();

    if (    (!$userlogin->hasRights('publication_edit'))
    ||
    !$this->accesslevels_lib->canEditObject($publication)
    )
    {
      appendErrorMessage(__('Delete publication').': '.__('insufficient rights').'.<br/>');
      redirect('');
    }

    if ($commit=='commit') {
      //do delete, redirect somewhere
      if ($publication->delete()) {
        redirect('');
      } else {
        redirect('publications/show/'.$publication->pub_id);
      }
    } else {
      //get output
      $headerdata = array();
      $headerdata['title'] = __('Delete publication');
      $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');

      $output = $this->load->view('header', $headerdata, true);

      $output .= $this->load->view('publications/delete',
      array('publication'=>$publication),
      true);

      $output .= $this->load->view('footer','', true);

      //set output
      $this->output->set_output($output);
    }
  }

  //commit() - Commit the posted publication to the database
  function commit()
  {

    $publication = $this->publication_db->getFromPost();

    $oldpublication = $this->publication_db->getByID($publication->pub_id); //needed to check access levels, as post data may be rigged
    //check the submit type, if 'type_change', we redirect to the edit form
    $submit_type = $this->input->post('submit_type');

    if ($submit_type == 'type_change')
    {
      $this->edit($publication);
    }
    else
    {
      if (!$this->publication_db->validate($publication))
      {
        //there were validation errors
        appendErrorMessage(__("There are validation errors with this entry. You may want to correct them.")."\n");
      }
      $edit_type = $this->input->post('edit_type');
      $bReview = false;
      if ($submit_type != 'review')
      {
        //review cite id
        $review['bibtex_id']   = $this->publication_db->reviewBibtexID($publication);

        //review keywords
        $review['keywords']  = $this->keyword_db->review($publication->keywords);

        if (($review['bibtex_id']   != null) ||
        ($review['keywords']  != null))
        {
          $bReview = true;
          $review['edit_type'] = $edit_type;
          //month: the field has been parsed to internal format, but the review form needs to contain the month in bibtex format
          $publication->month = formatMonthBibtexForEdit($publication->month);
          $this->review($publication, $review);
        }
      }
      if (!$bReview)
      {

        //do actual commit, depending on the edit_type, choose add or update
        $userlogin  = getUserLogin();
        $user       = $this->user_db->getByID($userlogin->userID());
        if ( (!$userlogin->hasRights('publication_edit'))
        || (($oldpublication == null) && ($edit_type != 'add'))
        || (!$this->accesslevels_lib->canEditObject($oldpublication) && ($oldpublication != null))
        )
        {
          appendErrorMessage(__('Commit publication').': '.__('insufficient rights').'.<br/>');
          redirect('');
        }

        if ($edit_type == 'add')
        $publication = $this->publication_db->add($publication);
        else
        $publication = $this->publication_db->update($publication);

        //show publication
        redirect('publications/show/'.$publication->pub_id);
      }
    }
  }

  function review($publication, $review_data)
  {
    $oldpublication = $this->publication_db->getByID($publication->pub_id); //needed to check access levels, as post data may be rigged
    $userlogin      = getUserLogin();
    $user           = $this->user_db->getByID($userlogin->userID());
    if ((!$userlogin->hasRights('publication_edit'))
    || (($oldpublication == null) && ($review_data['edit_type']!='add'))
    || (!$this->accesslevels_lib->canEditObject($oldpublication) && ($oldpublication != null))
    )
    {
      appendErrorMessage(__('Review publication').': '.__('insufficient rights').'.<br/>');
      redirect('');
    }

    $header ['title']       = __("Review publication");
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
    $content['publication'] = $publication;
    $content['review']      = $review_data;

    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('publications/review', $content, true);
    $output .= $this->load->view('footer',              '',       true);

    //set output
    $this->output->set_output($output);
  }

  /**
   publications/subscribe

   Susbcribes a publication to a topic. Is normally called async, without processing the
   returned partial, by clicking a subscribe link in a topic tree rendered by
   subview 'publicationsubscriptiontreerow'

   Fails with error message when one of:
   susbcribe requested for non-existing topic or publication
   insufficient user rights

   Parameters passed via URL:
   3rd segment: topic_id
   4rd segment: publication_id

   Returns a partial html fragment:
   an empty div if successful
   an div containing an error message, otherwise

   */
  function subscribe() {
    $topic_id = $this->uri->segment(3,-1);
    $pub_id = $this->uri->segment(4,-1);

    $publication = $this->publication_db->getByID($pub_id);
    if ($publication == null) {
      echo "<div class='errormessage'>".__("Subscribe topic").": ".__("non-existing id passed").".</div>";
    }

    $config = array('publicationId'=>$pub_id);
    $topic = $this->topic_db->getByID($topic_id,$config);

    if ($topic == null) {
      echo "<div class='errormessage'>".__("Subscribe topic").": ".__("non-existing id passed").".</div>";
    }
    //do subscribe
    $topic->subscribePublication();

    echo "<div/>";
  }


  /**
   publications/unsubscribe

   Unsusbcribes a publication from a topic. Is normally called async, without processing the
   returned partial, by clicking a subscribe link in a topic tree rendered by
   subview 'publicationsubscriptiontreerow'

   Fails with error message when one of:
   unsusbcribe requested for non-existing topic or publication
   insufficient user rights

   Parameters passed via URL:
   3rd segment: topic_id
   4rd segment: publication_id

   Returns a partial html fragment:
   an empty div if successful
   an div containing an error message, otherwise

   */
  function unsubscribe() {
    $topic_id = $this->uri->segment(3,-1);
    $pub_id = $this->uri->segment(4,-1);

    $publication = $this->publication_db->getByID($pub_id);
    if ($publication == null) {
      echo "<div class='errormessage'>".__("Unsubscribe topic").": ".__("non-existing id passed").".</div>";
    }
    $config = array('publicationId'=>$pub_id);
    $topic = $this->topic_db->getByID($topic_id,$config);

    if ($topic == null) {
      echo "<div class='errormessage'>".__("Unsubscribe topic").": ".__("non-existing id passed").".</div>";
    }
    //do subscribe
    $topic->unsubscribePublication();

    echo "<div/>";
  }

  /**
   publications/read

   marks a publication as read

   Fails with error message when one of:
   read requested for non-existing publication
   insufficient user rights

   Parameters passed via URL:
   3rd segment: publication_id
   possibly through post: mark

   Redirects to publication view
   */
  function read() {
    $pub_id = $this->uri->segment(3,-1);

    $publication = $this->publication_db->getByID($pub_id);
    if ($publication == null) {
      appendErrorMessage(__('Mark publication').': '.__('non-existing id passed').'.<br/>');
      redirect ('');
    }
    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('note_edit')) {
      appendErrorMessage(__('Mark publication').': '.__('insufficient rights').'.<br/>');
      redirect ('publications/show/'.$publication->pub_id);
    }
    $mark = $this->input->post('mark','');
    if ($mark==0)$mark='';
    $publication->read($mark);
    redirect ('publications/show/'.$publication->pub_id);
  }
  /**
   publications/unread

   marks a publication as not-read

   Fails with error message when one of:
   unread requested for non-existing publication
   insufficient user rights

   Parameters passed via URL:
   3rd segment: publication_id

   Redirects to publication view
   */
  function unread() {
    $pub_id = $this->uri->segment(3,-1);

    $publication = $this->publication_db->getByID($pub_id);
    if ($publication == null) {
      appendErrorMessage(__('Mark publication').': '.__('non-existing id passed').'.<br/>');
      redirect ('');
    }
    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('note_edit')) {
      appendErrorMessage(__('Mark publication').': '.__('insufficient rights').'.<br/>');
      redirect ('publications/show/'.$publication->pub_id);
    }
    $publication->unread();
    redirect ('publications/show/'.$publication->pub_id);
  }

  /**
   publications/exportEmail

   Sends the selected publication to the spesified email address(es).

   Fails with error message when one of:
   no publication selected

   Parameters passed via POST segments:
   email_pdf
   email_bibtex
   email_ris
   email_address
   email_formatted

   pub_id by url

   */

  function exportEmail()
  {
    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('export_email')) {
      appendErrorMessage(__('Export through email').': '.__('insufficient rights').'.<br/>');
      redirect('');
    }
    $this->load->library('email_export');
     
    $email_pdf = $this->input->post('email_pdf');
    $email_bibtex = $this->input->post('email_bibtex');
    $email_ris = $this->input->post('email_ris');
    $email_address = $this->input->post('email_address');
    $email_formatted = $this->input->post('email_formatted');
    $order='year';
    $recipientaddress   = $this->uri->segment(4,-1);
    $pub_id   = $this->uri->segment(3,-1);
    $publications = array($this->publication_db->getByID($pub_id));


    if (!isset($pub_id) || $pub_id == -1)
    {
      appendErrorMessage(__('Export publication').': '.__('non-existing id passed').'.<br/>');
      redirect('');
    }
    if ($this->publication_db->getByID($pub_id)==null) //Unexpected construction. we already need the publicatino for later, no? Why not store it?
    {
      appendErrorMessage(__('Export publication').': '.__('non-existing id passed').'.<br/>');
      redirect('');
    }


    /*
     IF the recipient's address is missing or if none of the data formats are selected THEN show the format selection form.
    */
    if(!(($email_pdf !='' || $email_bibtex !='' || $email_ris!='' || $email_formatted!='') && $email_address != ''))
    {
      $header ['title']       = __("Select export format");
      $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js','externallinks.js');


      $content['attachmentsize']  = $this->email_export->attachmentSize($publications);
      $content['controller']	='publications/exportEmail/'.$pub_id;
      if(isset($recipientaddress))
      {
        $replace = array("AROBA", "KOMMA");
        $with   = array("@", ",");
        $content['recipientaddress'] = str_replace($replace, $with, $recipientaddress);;
      }

      //get output
      $output = $this->load->view('header',        $header,  true);
      $output .= $this->load->view('export/chooseformatEmail', $content, true);
      $output .= $this->load->view('footer',        '',       true);

      //set output
      $this->output->set_output($output);
      return;
    }
    /*
     ELSE process the request and send the email.
    */
    else
    {

      //get output
      $this->load->helper('publication');

      $headerdata = array();
      $headerdata['title'] = __('Export publication');
      $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
      $headerdata['exportCommand']    = 'publications/exportEmail';
      $headerdata['exportName']    = __('Export publication');

      $content['header']          = __('Export by email');
      $output = $this->load->view('header', $headerdata, true);
      $content['publications']    = $publications;

      $content['order'] = $order;




      $messageBody = __('Export from Aigaion');

      if($email_formatted || $email_bibtex)
      {
        $this->publication_db->enforceMerge = True;
        $publications = array($this->publication_db->getByID($pub_id));
        $splitpubs = $this->publication_db->resolveXref($publications,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];

        $exportdata['nonxrefs'] = $pubs;
        $exportdata['xrefs']    = $xrefpubs;
        $exportdata['header']   = __('Exported publication');
        $exportdata['exportEmail']   = true;
      }


      /*
       FORMATTED text is added first. HTML format is selected because this gave nice readable text without having to change or make any views.
      */
      if($email_formatted)
      {
        $messageBody .= "\n";
        $messageBody .= __('Formatted');
        $messageBody .= "\n";

        $exportdata['format'] = 'html';
        $exportdata['sort'] = $this->input->post('sort');
        $exportdata['style'] = $this->input->post('style');
        $messageBody .= strip_tags($this->load->view('export/'.'formattedEmail', $exportdata, True));
      }

      /*
       BIBTEX added.
      */
      if($email_bibtex)
      {
        $messageBody .= "\n";
        $messageBody .= 'BibTeX';
        $messageBody .= "\n";
        $messageBody .= strip_tags($this->load->view('export/'.'bibtexEmail', $exportdata, True));
      }
      /*
       RIS added.
      */
      if($email_ris)
      {
        $messageBody .= "\n";
        $messageBody .= 'RIS';
        $messageBody .= "\n";

        $this->publication_db->suppressMerge = False;
        $publications = array($this->publication_db->getByID($pub_id));
        $splitpubs = $this->publication_db->resolveXref($publications,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];

        #send to right export view
        $exportdata['nonxrefs'] = $pubs;
        $exportdata['xrefs']    = $xrefpubs;
        $exportdata['header']   = __('Exported publication');
        $exportdata['exportEmail']   = true;

        $messageBody .= strip_tags($this->load->view('export/'.'risEmail', $exportdata, True));

      }


      /*
       If PDFs are not selected the publication array is removed and no attachments will be added.
      */
      if(!$email_pdf)
      {
        $publications = array();
      }

      /*
       Sending MAIL.
      */
      if($this->email_export->sendEmail($email_address, $messageBody, $publications))
      {
        $output .= __('Mail sent successfully');
      }
      else
      {
        appendErrorMessage(__('Something went wrong when exporting the publications. Did you input a correct email address?').' <br />');
        redirect('');
      }

      $output .= $this->load->view('footer','', true);

      //set output
      $this->output->set_output($output);
    }
  }
}
?>