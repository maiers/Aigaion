<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Import extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        
        $this->load->helper('publication');
    }
    
  /** Default function: list publications */
  function index()
    {
    $this->viewform();
    }

  function viewform($import_data = '')
  {
    $this->load->library('import_lib');
    
    $userlogin  = getUserLogin();
    $user       = $this->user_db->getByID($userlogin->userID());
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage(__('Import').': '.__('insufficient rights').'.<br/>');
      redirect('');
    }
    
    $header ['title']       = __("Import publications");
    $header ['javascripts'] = array();
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('import/importform', array('content'=>$import_data), true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);
  }
  
    
  /**
   * import/submit - Submit a posted publication to the database
   *
   * POST['format']: Type of input data. If unspecified or unknown or "auto", Aigaion attempts
   *                 to determine type automatically
   *
   * POST['import_data']: the import data as string
   *
   * POST['markasread']: true iff all imported entries should be marked as 'read' for the user
   */
  function submit()
  {
    $this->load->library('parser_import');
    $this->load->library('import_lib');

    $markasread   = $this->input->post('markasread')=='markasread'; // true iff all imported entries should be marked as 'read' for the user
    
    $fromFile = False;
    
    $import_data  = '';
    
    //first attempt whether the data was submitted as file
    //if so, get data from file, and remember that it was from file, as we can use the extension later on to guess import data type
    if (isset($_FILES['import_file']) && ($_FILES['import_file']['error']==0)) {
      $fromFile = True;
      $import_data=file_get_contents($_FILES['import_file']['tmp_name']);
    }
    else
    {
      //if not a file, get import data from post
      $import_data  = $this->input->post('import_data');    
    }
    if (trim($import_data) == '') 
    {
        appendErrorMessage(__("Import").": ".__("no import data entered.")."<br/>");
        $this->viewform();
        return;
    }
    //Determine type. Was it set explicitly?
    $type = $this->input->post('format');
    if (!isset($type)||($type==null))$type="auto";
    //is the type known?
    if (($type!="auto") && ($type!="") && !in_array($type,$this->import_lib->getAvailableImportTypes()))
    {
      appendErrorMessage(sprintf(__("Unknown import format specified (\"%s\")."),$type)." ".__("Attempting to automatically identify proper format.")."<br/>");
      $type = "auto";
    }
    //try to determine type automatically
    if ($type=="auto") 
    {
      //first try from data -- the content is more important than the file extension
      $type = $this->import_lib->determineImportType($import_data);
      if ($type == "unknown") 
      {
        if ($fromFile) 
        {
          $type = $this->import_lib->determineImportTypeFromFilename($_FILES['import_file']['name']);
          if ($type == "unknown") 
          {
            appendErrorMessage(__("Import").": ".__("can't automatically figure out import data format; please specify correct format.")."<br/>");
            $this->viewform($import_data);
            return;
          }
        }
      }
      appendMessage(sprintf(__("Import").": ".__("Data automatically identified as format \"%s\"."),$type)."<br/>");
    }
    
    //depending on the type, use the right parser to get the publications from the data
    switch ($type) {
      case 'BibTeX':
        $this->load->library('parseentries');
        $this->parser_import->loadData(getConfigurationSetting('BIBTEX_STRINGS_IN')."\n".$import_data);
        $this->parser_import->parse($this->parseentries);
        $publications = $this->parser_import->getPublications();
        break;
      case 'ris':
        $this->load->library('parseentries_ris');
        $this->parser_import->loadData($import_data);
        $this->parser_import->parse($this->parseentries_ris);
        $publications = $this->parser_import->getPublications();
        break;
      case 'refer':
        $this->load->library('parseentries_refer');
        $this->parser_import->loadData($import_data);
        $this->parser_import->parse($this->parseentries_refer);
        $publications = $this->parser_import->getPublications();
        break;
      default:
    }

    if (count($publications)==0)
    {
      appendErrorMessage("
      <b>".__("Import").": ".__("Could not extract any valid publication entries from the import data.")."</b> 
      <ul>
       <li>".__("Please verify the input.")."</li>
       <li>".__("If the input is correct, please verify the contents of the \"BibTeX strings\" setting under \"In- and output settings\", in the site configuration screen.")."</li>
       <li>".__("If that setting is correct, too, please submit a bug report at http://aigaion.de/")."</li>
      </ul><br/>");
      if (!$fromFile) {
        $this->viewform($import_data);
      }
      else
      {
        $this->viewform('');
      }
      return;
    }
    
    //so. Now we have the publications. Either commit them, or review them...
    $noreview		= $this->input->post('noreview')=='noreview';
    $markasread		= $this->input->post('markasread')=='markasread';
    if ($noreview) 
    {
      //do whatever is also done in commit. To this end, split commit in "getting the data" and "doing he commit"
      foreach ($publications as $pub_to_import)
      {
        $pub_to_import = $this->publication_db->add($pub_to_import);
        if ($markasread)$pub_to_import->read('');
        $last_id = $pub_to_import->pub_id;
        if (!ini_get('safe_mode'))set_time_limit(2); // give an additional 2 seconds for every entry to be displayed
      }
      $count = count($publications);
      appendMessage(sprintf(__('Succesfully imported %s publications.'),$count)."<br/>");
      if ($count == 1) {
        redirect('publications/show/'.$last_id);
      } else {
        redirect('publications/showlist/recent');
      }
    }
    else
    {
      $reviewed_publications  = array();
      $review_messages        = array();
      $count                  = 0;
      foreach ($publications as $publication) {
        //get review messages
        
        //review title
        $review['title']     = $this->publication_db->reviewTitle($publication);
        
        //review bibtex_id
        $review['bibtex_id'] = $this->publication_db->reviewBibtexID($publication);
        
        //review keywords
        $review['keywords']  = $this->keyword_db->review($publication->keywords);
        
        //review authors and editors
        $review['authors']   = $this->author_db->review($publication->authors); //each item consists of an array A with A[0] a review message, and A[1] an array of arrays of the similar author IDs
        $review['editors']   = $this->author_db->review($publication->editors); //each item consists of an array A with A[0] a review message, and A[1] an array of arrays of the similar author IDs
        
        $reviewed_publications[$count] = $publication;
        $review_messages[$count]       = $review;
        $count++;
        unset($review);
      }
      $this->review($reviewed_publications, $review_messages,$markasread);
    }
  }

    
  /**
   * import/commit - Commit the (parsed & reviewed) publication(s) to the database
   *
   * POST['import_count']: number of publications that are posted
   *
   * POST['markasread']: true iff all imported entries should be marked as 'read' for the user
   * 
   * POST: all publication data
   */
  function commit()
  {
    $this->load->library('import_lib');

    $import_count = $this->input->post('import_count');
    if ($import_count===False)$import_count = 0;
    
    $markasread   = $this->input->post('markasread')=='markasread'; // true iff all imported entries should be marked as 'read' for the user

    if ($import_count == 0) 
    {
      appendErrorMessage(__("Import")."/".__("Commit").": ".__("no publications committed.")."<br/>");
      $this->viewform();
      return;
    }

    $to_import = array();
    $old_bibtex_ids = array();
    $count = 0;
    for ($i = 0; $i < $import_count; $i++)
    {

      if ($this->input->post('do_import_'.$i) == 'CHECKED')
      {
        $count++;
        $publication = $this->publication_db->getFromPost("_".$i,True);
        $publication->actualyear = $this->input->post('actualyear_'.$i); //note that the actualyear is a field that normally is derived on update or add, but in the case of import, it has been set through the review form!
        $to_import[] = $publication;
        $old_bibtex_ids[$this->input->post('old_bibtex_id_'.$i)] = $count-1;
      }
    }
    $last_id = -1;
    foreach ($to_import as $pub_to_import) {
      //if necessary, change crossref (if reffed pub has changed bibtex_id)
      if (trim($pub_to_import->crossref)!= '') {
	        if (array_key_exists($pub_to_import->crossref,$old_bibtex_ids)) {
	            $pub_to_import->crossref = $to_import[$old_bibtex_ids[$pub_to_import->crossref]]->bibtex_id;
	            //appendMessage('changed crossref entry:'.$publication->bibtex_id.' crossref:'.$publication->crossref);
	        }
      }            
      $pub_to_import = $this->publication_db->add($pub_to_import);
      if ($markasread)$pub_to_import->read('');
      $last_id = $pub_to_import->pub_id;
      if (!ini_get('safe_mode'))set_time_limit(2); // give an additional 2 seconds for every entry to be displayed
    }
    appendMessage(sprintf(__('Succesfully imported %s publications.'),$count)."<br/>");
    if ($count == 1) {
      redirect('publications/show/'.$last_id);
    } else {
      redirect('publications/showlist/recent');
    }
  }
  
  
  function review($publications, $review_data,$markasread)
  {
    $userlogin      = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage(__('Review publication').': '.__('insufficient rights').'.<br/>');
      redirect('');
    }

    $header ['title']       = __("Review publication");
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
    $content['publications'] = $publications;
    $content['reviews']      = $review_data;
    $content['markasread']   = $markasread;
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('import/review',       $content, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);
  }

 
}
?>