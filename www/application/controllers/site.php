<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Site extends CI_Controller {

	function __construct()
	{
		parent::__construct();	
	}
	
	/** Pass control to the site/configure/ controller */
	function index()
	{
		$this->configure();
	}

  /** 
  site/configure
  
  Presents a page with links to several config edit pages. 
  Possibly includes some overview information about current settings for different settings-groups
  */
  function configure()
  {
    //check rights
    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('database_manage')) 
    {
      appendErrorMessage(__('Configure database').': '.__('insufficient rights').'.<br/>');
      redirect('');
    }

    //get info about current settings
    $siteconfig = $this->siteconfig_db->getSiteConfig();
    $customFieldsInfo = $this->customfields_db->getAllFieldsInfo();
    
    //get output
    $headerdata = array();
    $headerdata['title'] = __('Site configuration');
    $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
    
    $output = $this->load->view('header', $headerdata, true);

    
    $output .= $this->load->view('site/configoverview',
                                  array('siteconfig'=>$siteconfig, 'customFieldsInfo'=>$customFieldsInfo),  
                                  true);
    
    $output .= $this->load->view('footer','', true);

    //set output
    $this->output->set_output($output);    
  }

  /**
  site/configform
  
  Show one of the siteconfig forms
  
  3rd segment: name of the requested site config form
               (display|inputoutput|login|...)
  */
  function configform()
  {
    //check rights
    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('database_manage')) 
    {
      appendErrorMessage(__('Configure database').': '.__('insufficient rights').'.<br/>');
      redirect('');
    }

    //choose configform depending on 3rd segment
    $requestedform = $this->uri->segment(3,'display');
    $allowedconfigforms= array("display","inputoutput","attachments","content","login","userdefaults","accesslevels","siteintegration");
    if (!in_array($requestedform,$allowedconfigforms)) {
      $requestedform = 'display';
    }
    
    //get info about current settings
    $siteconfig = $this->siteconfig_db->getSiteConfig();
    $customFieldsInfo = $this->customfields_db->getAllFieldsInfo();
    
    //get output
    $headerdata = array();
    $headerdata['title'] = __('Site configuration');
    $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
    
    $output = $this->load->view('header', $headerdata, true);


    $anonUsers = $this->user_db->getAllAnonUsers();
    
    $output .= $this->load->view('site/configforms/header',array(),true);
    $output .= $this->load->view('site/configforms/'.$requestedform,
                                  array('siteconfig'=>$siteconfig, 'customFieldsInfo'=>$customFieldsInfo, 'anonUsers'=>$anonUsers),  
                                  true);
    $output .= $this->load->view('site/configforms/footer',array(),true);
    
    $output .= $this->load->view('footer','', true);

    //set output
    $this->output->set_output($output);        
  }

  /**
  site/commitconfigform
  
  Commit one of the siteconfig forms
  
  info about the form is stored in the POST.
  Which form was committed is stored in $this->input->post('configformname')
  
  
  */
  function commitconfigform()
  {
    //check rights
    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('database_manage')) 
    {
      appendErrorMessage(__('Configure database').': '.__('insufficient rights').'.<br/>');
      redirect('');
    }

    //choose configform depending on post setting segment
    $requestedform = $this->input->post('configformname');
    $allowedconfigforms= array("display","inputoutput","attachments","content","login","userdefaults","accesslevels","siteintegration");
    if (!in_array($requestedform,$allowedconfigforms)) {
      appendErrorMessage(__('Configure database').': '.__('commit of non-existing form').'.<br/>');
      redirect('site/configure');
    }
    
    //get info about current settings
    $siteconfig = $this->siteconfig_db->getSiteConfig();
    
    //get post info for requested form (siteconfig, and, if needed, customfields stuff)
    switch ($requestedform)
    {
      case "display":
        $siteconfig = $this->siteconfig_db->getDisplaySettingsFromPost($siteconfig);
        break;
      case "inputoutput":
        $siteconfig = $this->siteconfig_db->getInputOutputSettingsFromPost($siteconfig);
        break;
      case "attachments":
        $siteconfig = $this->siteconfig_db->getAttachmentSettingsFromPost($siteconfig);
        break;
      case "content":
        $siteconfig = $this->siteconfig_db->getCustomfieldSettingsFromPost($siteconfig);
        $siteconfig = $this->siteconfig_db->getAuthorSynonymSettingsFromPost($siteconfig);
        $siteconfig = $this->siteconfig_db->getCoverImageSettingsFromPost($siteconfig);
        $customFieldsInfo = $this->customfields_db->getSettingsFromPost();
        break;
      case "login":
        $siteconfig = $this->siteconfig_db->getLoginSettingsFromPost($siteconfig);
        break;
      case "userdefaults":
        $siteconfig = $this->siteconfig_db->getUserDefaultsFromPost($siteconfig);
        break;
      case "accesslevels":
        $siteconfig = $this->siteconfig_db->getDefaultAccessLevelsFromPost($siteconfig);
        break;
      case "siteintegration":
        $siteconfig = $this->siteconfig_db->getSiteIntegrationSettingsFromPost($siteconfig);
        break;
      default:
        //won't happen
        break;
    }
    //store new settings for siteconfig, and, if needed, customfields stuff
    $siteconfig->update();
    $siteconfig = $this->siteconfig_db->getSiteConfig();
    if ($requestedform == "content")
    {
      $customFieldsInfo = $this->customfields_db->updateSettingsFromPost($customFieldsInfo);
    }
    appendMessage(__('Configure database').': '.__('new settings stored').'.<br/>');
    redirect('site/configure');
  }
  
    /** 
    site/configureold
    
    the old massive configuration page. Will no longer be updated and extended.
    Fails when unsufficient user rights
    
    Paramaters:
        3rd segment: if 3rd segment is 'commit', site config data is expected in the POST
    
    Returns a full html page with a site configuration form. */
	function configureold()
	{
	    //check rights
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('database_manage')) 
        {
	        appendErrorMessage(__('Configure database').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }
        
	    $commit = $this->uri->segment(3,'');
	    
	    $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('<div class="errormessage">'.__('Changes not committed').': ', '</div>');
	    if ($commit=='commit') {
	        $siteconfig = $this->siteconfig_db->getSiteConfig();
	        $siteconfig = $this->siteconfig_db->getDisplaySettingsFromPost($siteconfig);
	        $siteconfig = $this->siteconfig_db->getAttachmentSettingsFromPost($siteconfig);
	        $siteconfig = $this->siteconfig_db->getLoginSettingsFromPost($siteconfig);
	        $siteconfig = $this->siteconfig_db->getUserDefaultsFromPost($siteconfig);
	        $siteconfig = $this->siteconfig_db->getCustomfieldSettingsFromPost($siteconfig);
	        $siteconfig = $this->siteconfig_db->getInputOutputSettingsFromPost($siteconfig);
	        $siteconfig = $this->siteconfig_db->getDefaultAccessLevelsFromPost($siteconfig);
	        $siteconfig = $this->siteconfig_db->getSiteIntegrationSettingsFromPost($siteconfig);
	        
	        $customFieldsInfo = $this->customfields_db->getSettingsFromPost();
	        if ($siteconfig!= null) {
    	        //do validation
                //----no validation rules are implemented yet. When validation rules are defined, see e.g. users/commit for
                //examples of validation code
            	//if ($this->form_validation->run() == TRUE) {
    	            //if validation successfull, set settings
    	            $siteconfig->update();
    	            $siteconfig = $this->siteconfig_db->getSiteConfig();
    	            
    	            $customFieldsInfo = $this->customfields_db->updateSettingsFromPost($customFieldsInfo);
    	        //}
    	    }
	    } else {
	        $siteconfig = $this->siteconfig_db->getSiteConfig();
	        $customFieldsInfo = $this->customfields_db->getAllFieldsInfo();
	    }
 	    $anonUsers = $this->user_db->getAllAnonUsers();
	    
	    
        //get output: always return to configuration page
        $headerdata = array();
        $headerdata['title'] = __('Site configuration');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
        
        $output = $this->load->view('header', $headerdata, true);

        
        $output .= $this->load->view('site/edit',
                                      array('siteconfig'=>$siteconfig, 'anonUsers'=>$anonUsers, 'customFieldsInfo'=>$customFieldsInfo),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}

	/** 
	site/maintenance
	
	Entry point for maintenance functions.
	
	Fails with error message when one of:
	    insufficient user rights
	    non-existing maintenance function given

	Paramaters:
	    3rd segment: name of the maintenance function to be executed (can be 'all')
	    
	Returns:
	    A full HTML page presenting 
	        the maintenance options
	        plus, if a maintenance function is given, the result of the chosen maintenance option 
	*/
	function maintenance()
	{
	    $this->load->helper('maintenance');
	    //check rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('database_manage'))
            ) 
        {
	        appendErrorMessage(__('Maintain database').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }

	    $maintenance = $this->uri->segment(3,'');

        $checkresult = "<table class='message' width='100%'>";
        
	    switch ($maintenance) {
	        case 'all':
	        case 'attachments':
	            $checkresult .= checkAttachments();
	            if ($maintenance != 'all') 
	                break;
	        case 'topics':
	            $checkresult .= checkTopics();
	            if ($maintenance != 'all') 
	                break;
	        case 'notes':
	            $checkresult .= checkNotes();
	            if ($maintenance != 'all') 
	                break;
	        case 'authors':
	            $checkresult .= checkAuthors();
	            if ($maintenance != 'all') 
	                break;
	        case 'keywords':
	            $checkresult .= checkKeywords();
	            if ($maintenance != 'all') 
	                break;
	        case 'passwords':
	            $checkresult .= checkPasswords();
	            if ($maintenance != 'all') 
	                break;
	        case 'cleannames':
	            $checkresult .= checkCleanNames();
	            if ($maintenance != 'all') 
	                break;
	        case 'publicationmarks':
	            $checkresult .= checkPublicationMarks();
	            if ($maintenance != 'all') 
	                break;
	        case 'checkupdates':
	            $this->load->helper('checkupdates');
                $checkresult .= "<tr><td colspan=2><p class='header1'>".__("Aigaion updates")."</p></td></tr>\n";
	            $checkresult .= "<tr><td>".__("Checking for updates")."...</td>";
//	            $updateinfo = '';
	            $updateinfo = checkUpdates();
	            if ($updateinfo == '') {
    		        $checkresult .= '<td><b>'.__('OK').'</b></td></tr>';
        			$checkresult .= '<tr><td colspan=2><div class="message">'.__('This installation of Aigaion is up-to-date.').'.</div></td></tr>';
	            } else {
        			$checkresult .= '<td><span class="errortext">'.utf8_strtoupper('Alert').'</span></td>';
        			$checkresult .= '</tr>';
        			$checkresult .= '<tr><td colspan=2>'.$updateinfo.'</td></tr>';
    	        }
	            //if ($maintenance != 'all') 
	            break;
	        //AND NOW THOSE THAT SHOULD NOT BE INCLUDED IN 'ALL'
	        case 'deletenonpublishingauthors':
	            $checkresult .= deleteNonPublishingAuthors();
              break;
	        case 'removeauthorsynonyms':
	            $checkresult .= removeAuthorSynonyms();
              break;
          case 'deleteunusedkeywords':
              $checkresult .= deleteUnusedKeywords();
              break;
	        case '':
	            break;
	        default:
    	        appendMessage(sprintf(__('Maintenance function "%s" not implemented.'),$maintenance).'<br>');
	            break;
	    }
	    
	    $checkresult .= "</table>";
        //get output
        $headerdata = array();
        $headerdata['title'] = __('Site maintenance');
        $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
        
        $output = $this->load->view('header', $headerdata, true);
        
        $output .= $checkresult;
        
        $output .= $this->load->view('site/maintenance',
                                      array(),
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
    }
    
	/** 
	site/backup
	
	Entry point for backup
	
	Fails with error message when one of:
	    insufficient user rights

	Paramaters:
	    3rd segment: win|unix|mac  determines linebreaks
	    
	Returns:
	    A sql file
	*/
	function backup()
	{
	    //check rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('database_manage'))
            ) 
        {
	        appendErrorMessage(__('Backup database').': '.__('insufficient rights').'.<br/>');
	        redirect('');
        }

	    $type = $this->uri->segment(3,'win');
	    if (!in_array($type,array('win','unix','mac'))) {
	        $type = 'unix';
	    }
		if ($type == "win")
			$linebreak = "\r\n";
		else if ($type == "unix")
			$linebreak = "\n";
		else if ($type == "mac")
			$linebreak = "\r";

        // Load the DB utility class
        $this->load->dbutil();
        
        //tables to backup: only those with right prefix, if prefix set (!)
        $tables=array();
        if (AIGAION_DB_PREFIX!='') {
            foreach ($this->db->list_tables() as $table) {
                $p = strpos($table,AIGAION_DB_PREFIX);
                if (!($p===FALSE)) {
                    if ($p==0) {
                        $tables[]=$table;
                    }
                }
            }
        }
        // Backup your entire database and assign it to a variable
        //note: we could make a site setting for whether a gz, zip or txt is returned. But gz is OK, I guess.
        $backup =$this->dbutil->backup(array('tables'=>$tables,'newline'=>$linebreak,'format'=>'txt'));
        
        
        header("Content-Type: charset=utf-8"); //we intentionally do not use the download helper, as we need to set some UTF* specific thingies here
        header('Content-Disposition: attachment; filename="'.AIGAION_DB_PREFIX.AIGAION_DB_NAME."_backup_".date("Y_m_d").'.sql'.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        //header("Content-Transfer-Encoding: binary");
        header('Pragma: public,no-cache');
        //header("Content-Length: ".strlen($backup));
        //echo " <html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /></head><body>";
        //we can send BOM, because otherwise not all editors will recognize this file as UTF8!!!
        //echo Chr(239).Chr(187).Chr(191); //unfortunately, other editors will NOT b able to handle the BOM :( Also, on the web I find suggestions that BOM breaks IE (http://trac.seagullproject.org/wiki/Misc/CharsetEncoding
        //so we choose another solution, see below
        //http://mailman.rfc-editor.org/pipermail/rfc-interest/2008-October/000771.html
        echo "-- Iñtërnâtiônàlizætiøn
        -- Aigaion developers: 
        -- Don't remove the above commented sentence with some UTF8 characters in it, 
        -- as it is needed to make sure that editors recognize the downloaded file as UTF8. (note that we could have
        --  added a BOM instead, but many editors choke on BOM, so we chose not to do this.)
        -- Refer to the following link for a discussion about this problem with editors 'guessing'
        --  the encoding.
        -- http://mailman.rfc-editor.org/pipermail/rfc-interest/2008-October/000771.html
        -- - Aigaion database: ".AIGAION_DB_NAME."
        -- - Export date: ".date("Y_m_d")."

        ";
        
        echo $backup;
        //echo "</body></html>";
        die();        
    }

	/** 
	site/restore
	
	Entry point for restoring from a backup file
	
	Fails with error message when one of:
	    insufficient user rights

	Parameters:
	   none
	    
	Returns:
	    a view is shown for uploading the backup file, or a text area for pasting the SQL directly
    
	*/
	function restore()
	{
    //check rights
    $userlogin = getUserLogin();
    if (    (!$userlogin->hasRights('database_manage'))
        ) 
    {
      appendErrorMessage(__('Restore database').': '.__('insufficient rights').'.<br/>');
      redirect('');
    }
    $headerdata = array();
    $headerdata['title'] = __('Restore database');
    $headerdata['javascripts'] = array('tree.js','prototype.js','scriptaculous.js','builder.js','externallinks.js');
    
    $output = $this->load->view('header', $headerdata, true);
    
    $output .= $this->load->view('site/restore',
                                  array(),
                                  true);
    
    $output .= $this->load->view('footer','', true);

    //set output
    $this->output->set_output($output);	    
    return;    
	}
	/** restore database from uploaded file
	 * parameters:
	 *   POST: the backup file
	 */      	
	function restorefromfile() 
	{
	  //check rights
    $userlogin = getUserLogin();
    if (    (!$userlogin->hasRights('database_manage'))
        ) 
    {
      appendErrorMessage(__('Restore database').': '.__('insufficient rights').'.<br/>');
      redirect('');
    }

  	$this->file_upload->the_file = $_FILES['backup_file']['name'];
  	$this->file_upload->http_error = $_FILES['backup_file']['error'];
  	$this->file_upload->extensions = array(".sql");
  
  	if (! $this->file_upload->validateExtension()) {
  		appendErrorMessage(__("The file appears not to be an SQL file. Please select a valid Aigaion backup file.")."<br />");
  		redirect('site/maintenance');
  	}
  	if ($this->file_upload->http_error > 0) {
  		appendErrorMessage($this->file_upload->error_text($this->file_upload->http_error));
  		redirect('site/maintenance');
  	}
  
  	//load the file into an array. Each line in one array element.
  	$sqlArray = array();
    $sqlArray = file($_FILES['backup_file']['tmp_name']);
    
    //drop all...
    $this->load->dbforge();
    $tables = $this->db->list_tables();
    foreach ($tables as $table)
    {
      //remopve bloody prefix, as listtables includes it, and drop_table includes it again...
       $this->dbforge->drop_table(substr($table,strlen(AIGAION_DB_PREFIX)));
    }     
    //start loading backup data
    $this->load->helper("utf8");
  	$report = "";
  	$query  = "";
  	appendMessage("<b>".__("Restored database").":</b><br/>\n");
  	foreach ($sqlArray as $part) 
    {

  		$complete = false;
  		$part = trim(rtrim($part,'\r\n'));
  		if ((utf8_substr($part, 0, 3) == "-- ")||(utf8_substr($part, 0, 1) == "#")) { # we have got a comment
  			if (utf8_substr($part, 3, 2) == "- ") # ... which is meant to be displayed to the user
  			{
  				//appendMessage(utf8_substr($part, 5, uft8_strlen($part) - 5)."<br/>");
  			}
  		} elseif (strlen($part) > 0) { // we have sql
  			$query .= $part." ";
  			if (substr($part, -1, 1) == ";") {
  				if (substr($part, -2, 1) != "\\")
  					$complete = true;
  			}
  		}
  
  		if ($complete) {
  			$this->db->query($query);
  			//appendMessage($query.'<hr>');
  			$err = mysql_error(); 
  			if ($err != null) appendErrorMessage($err); 
  			$query = "";
  			$complete = false;
  		}
  	}
    redirect('site/maintenance');
  }
	/** restore database from SQl in text area
	 * parameters:
	 *   POST: the backup data as a string
	 */      	
  function restorefromsql()
  {
    //check rights
    $userlogin = getUserLogin();
    if (    (!$userlogin->hasRights('database_manage'))
        ) 
    {
      appendErrorMessage(__('Restore database').': '.__('insufficient rights').'.<br/>');
      redirect('');
    }
    $this->load->helper("utf8");
    $data= $this->input->post('backup_data');
    if (trim($data) == '') 
    {
      appendErrorMessage(__("No data given to restore!"));
      redirect('site/maintenance');
    }
  	$sqlArray = explode("\n",$data);

    //drop all...
    $this->load->dbforge();
    $tables = $this->db->list_tables();
    foreach ($tables as $table)
    {
      //remopve bloody prefix, as listtables includes it, and drop_table includes it again...
       $this->dbforge->drop_table(substr($table,strlen(AIGAION_DB_PREFIX)));
    }     
    //start loading backup data
  	$report = "";
  	$query  = "";
  	appendMessage("<b>".__("Restored database").":</b><br/>\n");
  	foreach ($sqlArray as $part) 
    {

  		$complete = false;
  		$part = trim(rtrim($part,"\r\n"));
  		if ((utf8_substr($part, 0, 3) == "-- ")||(utf8_substr($part, 0, 1) == "#")) { # we have got a comment
  			if (utf8_substr($part, 3, 2) == "- ") # ... which is meant to be displayed to the user
  			{
  				appendMessage(substr($part, 5)."<br/>");
  			}
  		} elseif (strlen($part) > 0) { // we have sql
  			$query .= $part." ";
  			if (substr($part, -1, 1) == ";") {
  				if (substr($part, -2, 1) != "\\")
  					$complete = true;
  			}
  		}
  
  		if ($complete) {
  			mysql_query($query); //don't use this->db->query, as it would encapsulate everything too much, leadning to \\\'
  			//appendMessage($query.'<hr>');
  			$err = mysql_error(); 
  			if ($err != null) appendErrorMessage($err); 
  			$query = "";
  			$complete = false;
  		}
  	}
    redirect('site/maintenance');    
  }
}
?>