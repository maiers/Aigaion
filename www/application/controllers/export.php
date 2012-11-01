<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Export extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
	}
	
	/** Pass control to the export/all/ */
	function index()
	{
		$this->all();
	}
	

    /** 
    export/all
    
    Export all (accessible) entries in the database
    
	Fails with error message when one of:
	    never
	    
	Parameters passed via URL segments:
	    3rd: type (bibtex|ris|email)
	         
    Returns:
        A clean text page with exported publications
    */
    function all() {
	    $type = $this->uri->segment(3,'');
	    if (!in_array($type,array('bibtex','ris','formatted','email'))) {
            $header ['title']       = __("Select export format");
            $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js','externallinks.js');
            
            //get output
            $output  = $this->load->view('header',        $header,  true);
            $output .= $this->load->view('export/chooseformat',  array('header'=>__('Export all publications'),'exportCommand'=>'export/all/'), true);
            $output .= $this->load->view('footer',        '',       true);
            
            //set output
            $this->output->set_output($output);
            return;
	    }
	    $exportdata = array();
        $userlogin = getUserLogin();
        //for export, bibtex should NOT merge crossrefs; ris and formatted SHOULD merge crossrefs
        switch ($type) {
            case 'bibtex':
                $this->publication_db->suppressMerge = True;
                break;
            case 'ris':
                $this->publication_db->enforceMerge = True;
                break;
            case 'formatted':
                $this->publication_db->enforceMerge = True;
                $exportdata['format'] = $this->input->post('format');
                $exportdata['sort'] = $this->input->post('sort');
                $exportdata['style'] = $this->input->post('style');
                break;
            case 'email':
            		redirect ('topics/exportEmail/1/');
            default:
                break;
                
        }
        #collect to-be-exported publications 
        $publicationMap = $this->publication_db->getAllPublicationsAsMap();
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
        
        #send to right export view
        $exportdata['nonxrefs'] = $pubs;
        $exportdata['xrefs']    = $xrefpubs;
        $exportdata['header']   = __('All publications');

        $output = $this->load->view('export/'.$type, $exportdata, True);

        //set output
        $this->output->set_output($output);        

    }    
    /** 
    export/topic
    
    Export all (accessible) entries from one topic
    
	Fails with error message when one of:
	    non existing topic_id requested
	    
	Parameters passed via URL segments:
	    3rd: topic_id
	    4rth: type (bibtex|ris|email)
	         
    Returns:
        A clean text page with exported publications
    */
    function topic() {
	    $topic_id = $this->uri->segment(3,-1);
	    $type = $this->uri->segment(4,'');
	    $config = array();
	    $topic = $this->topic_db->getByID($topic_id,$config);
	    if ($topic==null) {
	        appendErrorMessage(__('Export requested for non existing topic.').'<br/>');
	        redirect ('');
	    }
	    if (!in_array($type,array('bibtex','ris','formatted','email'))) {
            $header ['title']       = __("Select export format");
            $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js','externallinks.js');
            
            //get output
            $output  = $this->load->view('header',        $header,  true);
            $output .= $this->load->view('export/chooseformat',  array('header'=>sprintf(__('Export all for topic %s'),$topic->name),'exportCommand'=>'export/topic/'.$topic->topic_id.'/'), true);
            $output .= $this->load->view('footer',        '',       true);
            
            //set output
            $this->output->set_output($output);
            return;
	    }
	    $exportdata = array();
        $userlogin = getUserLogin();
        //for export, bibtex should NOT merge crossrefs; ris SHOULD merge crossrefs
        switch ($type) {
            case 'bibtex':
                $this->publication_db->suppressMerge = True;
                break;
            case 'ris':
                $this->publication_db->enforceMerge = True;
                break;
            case 'formatted':
                $this->publication_db->enforceMerge = True;
                $exportdata['format'] = $this->input->post('format');
                $exportdata['sort'] = $this->input->post('sort');
                $exportdata['style'] = $this->input->post('style');
                break;
            case 'email':
            		redirect ('topics/exportEmail/'.$topic_id.'/');
            		break;
            default:
                break;
                
        }

        #collect to-be-exported publications 
        $publicationMap = $this->publication_db->getForTopicAsMap($topic->topic_id);
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
        
        #send to right export view
        $exportdata['nonxrefs'] = $pubs;
        $exportdata['xrefs']    = $xrefpubs;
        $exportdata['header']   = sprintf(__('All publications for topic "%s"'),$topic->name);
        $output = $this->load->view('export/'.$type, $exportdata, True);

        //set output
        $this->output->set_output($output);        

    }        
    /** 
    export/author
    
    Export all (accessible) entries from one author
    
	Fails with error message when one of:
	    non existing author_id requested
	    
	Parameters passed via URL segments:
	    3rd: author_id
	    4rth: type (bibtex|ris|email)
	         
    Returns:
        A clean text page with exported publications
    */
    function author() {
	    $author_id = $this->uri->segment(3,-1);
	    $type = $this->uri->segment(4,'');
	    $author = $this->author_db->getByID($author_id);
	    if ($author==null) {
	        appendErrorMessage(__('Export requested for non existing author.').'<br/>');
	        redirect ('');
	    }
	    if (!in_array($type,array('bibtex','ris','formatted','email'))) {
            $header ['title']       = __("Select export format");
            $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js','externallinks.js');
            
            //get output
            $output  = $this->load->view('header',        $header,  true);
            $output .= $this->load->view('export/chooseformat',  array('header'=>sprintf(__('Export all for author %s'),$author->getName()),'exportCommand'=>'export/author/'.$author->author_id.'/'), true);
            $output .= $this->load->view('footer',        '',       true);
            
            //set output
            $this->output->set_output($output);
            return;
	    }
	    $exportdata = array();
        $userlogin = getUserLogin();
        //for export, bibtex should NOT merge crossrefs; ris SHOULD merge crossrefs
        switch ($type) {
            case 'bibtex':
                $this->publication_db->suppressMerge = True;
                break;
            case 'ris':
                $this->publication_db->enforceMerge = True;
                break;
            case 'formatted':
                $this->publication_db->enforceMerge = True;
                $exportdata['format'] = $this->input->post('format');
                $exportdata['sort'] = $this->input->post('sort');
                $exportdata['style'] = $this->input->post('style');
                break;
            case 'email':
            		redirect ('authors/exportEmail/'.$author_id);
            		break;
            default:
                break;
                
        }

        #collect to-be-exported publications 
        $publicationMap = $this->publication_db->getForAuthorAsMap($author->author_id,$author->synonym_of=='0');
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
        
        #send to right export view
        $exportdata['nonxrefs'] = $pubs;
        $exportdata['xrefs']    = $xrefpubs;
        $exportdata['header']   = sprintf(__('All publications for %s'),$author->getName());

        $output = $this->load->view('export/'.$type, $exportdata, True);

        //set output
        $this->output->set_output($output);        

    }       
    /** 
    export/bookmarklist
    
    Export all (accessible) entries from the bookmarklist of this user
    
	Fails with error message when one of:
	    insufficient rights
	    
	Parameters passed via URL segments:
	    3rth: type (bibtex|ris|email)
	         
    Returns:
        A clean text page with exported publications
    */
    function bookmarklist() {
	    $type = $this->uri->segment(3,'');
	    if (!in_array($type,array('bibtex','ris','formatted','email'))) {
            $header ['title']       = __("Select export format");
            $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js','externallinks.js');
            
            //get output
            $output  = $this->load->view('header',        $header,  true);
            $output .= $this->load->view('export/chooseformat',  array('header'=>__('Export all publications on bookmarklist'),'exportCommand'=>'export/bookmarklist/'), true);
            $output .= $this->load->view('footer',        '',       true);
            
            //set output
            $this->output->set_output($output);
            return;
	    }
	    $exportdata = array();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
	        appendErrorMessage(__('Export').': '.__('no bookmarklist rights').'.<br/>');
	        redirect ('');
	    }
        //for export, bibtex should NOT merge crossrefs; ris SHOULD merge crossrefs
        switch ($type) {
            case 'bibtex':
                $this->publication_db->suppressMerge = True;
                break;
            case 'ris':
                $this->publication_db->enforceMerge = True;
                break;
            case 'formatted':
                $this->publication_db->enforceMerge = True;
                $exportdata['format'] = $this->input->post('format');
                $exportdata['sort'] = $this->input->post('sort');
                $exportdata['style'] = $this->input->post('style');
                break;
            case 'email':
            		redirect ('bookmarklist/exportEmail/');
            		break;
            default:
                break;
                
        }
	    
        #collect to-be-exported publications 
        $publicationMap = $this->publication_db->getForBookmarkListAsMap();
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
        
        #send to right export view
        $exportdata['nonxrefs'] = $pubs;
        $exportdata['xrefs']    = $xrefpubs;
        $exportdata['header']   = __('Exported from bookmarklist');

        $output = $this->load->view('export/'.$type, $exportdata, True);

        //set output
        $this->output->set_output($output);        

    }        
        
    /** 
    export/publication
    
    Export one publication
    
	Fails with error message when one of:
	    non existing pub_id requested
	    
	Parameters passed via URL segments:
	    3rd: pub_id
	    4rth: type (bibtex|ris|email)
	         
    Returns:
        A clean text page with exported publications
    */
    function publication() {
	    $pub_id = $this->uri->segment(3,-1);
	    $type = $this->uri->segment(4,'');
        //for export, bibtex should NOT merge crossrefs; ris SHOULD merge crossrefs
	    $exportdata = array();
        switch ($type) {
            case 'bibtex':
                $this->publication_db->suppressMerge = True;
                break;
            case 'ris':
                $this->publication_db->enforceMerge = True; //although the crossreferenced publications are STILL exported...
                break;
            case 'formatted':
                $this->publication_db->enforceMerge = True;
                $exportdata['format'] = $this->input->post('format');
                $exportdata['sort'] = $this->input->post('sort');
                $exportdata['style'] = $this->input->post('style');
                break;
            default:
                break;
                
        }
	    $publication = $this->publication_db->getByID($pub_id);
	    if ($publication==null) {
	        appendErrorMessage(__('Export requested for non existing publication.').'<br/>');
	        redirect ('');
	    }
	    if (!in_array($type,array('bibtex','ris','formatted'))) {
            $header ['title']       = __("Select export format");
            $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js','externallinks.js');
            
            //get output
            $output  = $this->load->view('header',        $header,  true);
            $output .= $this->load->view('export/chooseformat',  array('header'=>__('Export one publication'),'exportCommand'=>'export/publication/'.$publication->pub_id.'/'), true);
            $output .= $this->load->view('footer',        '',       true);
            
            //set output
            $this->output->set_output($output);
            return;
	    }
        $userlogin = getUserLogin();

        #collect to-be-exported publications 
        $publicationMap = array($publication->pub_id=>$publication);
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
        
        #send to right export view
        $exportdata['nonxrefs'] = $pubs;
        $exportdata['xrefs']    = $xrefpubs;

        $output = $this->load->view('export/'.$type, $exportdata, True);

        //set output
        $this->output->set_output($output);        

    }    
}
?>