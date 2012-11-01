<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Search extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
	}
	
	/** Default: advanced search form */
	function index()
	{
		$this->advanced();
	}

    /** 
    search/quicksearch
        
    Fails not
    
    Parameters:
        search query through form value
    
    Returns a full html page with a search result. */
	function quicksearch()
	{
        $query = $this->input->post('searchstring');
	    if (trim($query)=='') {
	        appendErrorMessage(__('Search').': '.__('no query').'.<br/>');
	        redirect('');
	    }
	    $this->load->library('search_lib');
	    $searchresults = $this->search_lib->simpleSearch($query,null);
	    
        //get output: search result page
        $headerdata = array();
        $headerdata['title'] = __('Search results');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= "<div class=optionbox>[".anchor('search/advanced',__('Advanced search'))."]</div><div class=header>".__("Quicksearch results")."</div>";
        $output .= $this->load->view('search/results',
                                      array('searchresults'=>$searchresults, 'query'=>$query),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
    /** 
    search/advanced
    */
	function advanced()
	{
        //get output: advanced earch interface
        $headerdata = array();
        $headerdata['title'] = __('Advanced search');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
        
        $output = $this->load->view('header', $headerdata, true);

        
        $output .= $this->load->view('search/advanced',
                                      array(),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
    /** 
    search/advancedresults
        
    Fails not
    
    Parameters:
        search query through form values
    
    Returns a full html page with a search result. */
	function advancedresults()
	{
        if ($this->input->post('formname')!='advancedsearch') {
            $this->advanced();
            return;
        }
      //process query
      $query = $this->input->post('searchstring');
      $anyAll = $this->input->post('anyAll');
      $doConditions = array();
      $dontConditions = array();
      $config = array('onlyIfUserSubscribed'=>False,
                'includeGroupSubscriptions'=>False);
      for ($i = 1; $i <= $this->input->post('numberoftopicconditions'); $i++) {
          //parse condition. they start with 1!
          $do = $this->input->post('doOrNot'.$i);
          $topic_id = $this->input->post('topicSelection'.$i);
          if ($topic_id=='header')continue;
          $topic = $this->topic_db->getById($topic_id,$config);
          if ($topic==null) {
            appendMessage(__('Nonexisting topic_id in advanced search condition'));
            continue;
          }
          if ($do=='True') {
            $doConditions[] = $topic; 
          } else {
            $dontConditions[] = $topic;
          }
      }
      if (($query == '')&& ((count($doConditions)>0)||(count($dontConditions)>0))) {
        //appendMessage("No query, but some topic restrictions: interpret as a search for ALL publications within topics; don't query for all authors, topics or keywords");
        $query="*";
      } else if ($query == '') {
        appendMessage(__("No query at all: please give at least a search term or a topic condition"));
        $this->advanced();
        return;
      }
      $searchoptions = array('advanced');
      if ($this->input->post('return_authors')=='return_authors') 
        $searchoptions[] = 'authors';
      if ($this->input->post('return_topics')=='return_topics') 
        $searchoptions[] = 'topics';
      if ($this->input->post('return_keywords')=='return_keywords') 
        $searchoptions[] = 'keywords';
      if ($this->input->post('return_publications')=='return_publications') {
        $searchoptions[] = 'publications';
        if ($this->input->post('search_publications_titles')=='search_publications_titles') 
          $searchoptions[] = 'publications_titles';
        if ($this->input->post('search_publications_notes')=='search_publications_notes') 
          $searchoptions[] = 'publications_notes';
        if ($this->input->post('search_publications_bibtex_id')=='search_publications_bibtex_id') 
          $searchoptions[] = 'publications_bibtex_id';
        if ($this->input->post('search_publications_abstracts')=='search_publications_abstracts') 
          $searchoptions[] = 'publications_abstracts';
      }
      
      
      $this->load->library('search_lib');
      if ((count($doConditions>0))||(count($dontConditions>0))) {
        $searchresults = $this->search_lib->topicConditionSearch($query,$searchoptions,$doConditions,$dontConditions,$anyAll);
      } else {
	      $searchresults = $this->search_lib->simpleSearch($query,$searchoptions,"");
	    }
	    
        //get output: search result page
        $headerdata = array();
        $headerdata['title'] = __('Advanced search results');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js');
        
        $output = $this->load->view('header', $headerdata, true);

        
        $output .= "<div class=optionbox>(".__("Search form below").")</div><div class=header>".__("Advanced search results")."</div>";

        $output .= $this->load->view('search/results',
                                      array('searchresults'=>$searchresults, 'query'=>$query),  
                                      true);
                                      
        $output .= $this->load->view('search/advanced',
                                      array('query'=>$query,'options'=>$searchoptions),  
                                      true);
                                      
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
	function preview()
	{
		$query = trim($_REQUEST['term']);
		
		$this->load->library('search_lib');
	  $searchresults = $this->search_lib->simpleSearch($query, null);
	  
	  /* at least available keys
	  [0] => authors
	  [1] => topics
	  [2] => keywords
	  [3] => publications_titles
	  [4] => publications_bibtex_id
	  [5] => publications_notes
	  [6] => publications_abstracts
	  */
	  	  
	  $this->load->helper('search_result_helper');
	  $sr = new SearchResultHelper($searchresults, 5, true);
	  $sr->filterSearch('publications_titles', __('Publications'), 
	  			'cleantitle', 'cleantitle', 'pub_id', '/publications/show/');
	  $sr->filterSearch('publications_bibtex_id', __('Publications'),
	  	  			'cleantitle', 'cleantitle', 'pub_id', '/publications/show/');
	  $sr->filterSearch('publications_abstracts', __('Publications'),
	  	  	  			'cleantitle', 'cleantitle', 'pub_id', '/publications/show/');
	  $sr->filterSearch('publications_notes', __('Publications (Notes)'),
	  	  			'cleantitle', 'cleantitle', 'pub_id', '/publications/show/');	  
	  $sr->filterSearch('authors', __('Authors'),
	  	  	'cleanname', 'cleanname', 'author_id', '/authors/show/');
	  $sr->filterSearch('topics', __('Topics'),
	  	  	'cleanname', 'cleanname', 'topic_id', '/topics/single/');
	  $sr->filterSearch('keywords', __('Keywords'),
	  	  	'keyword', 'keyword', 'keyword_id', 'keywords/single/');
	  $sr->clean();
	  
	  echo json_encode($sr->getResult());
	}
}
?>