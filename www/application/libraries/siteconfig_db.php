<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** This class regulates the database access for a siteconfig.

Expected use: 
first get the site config for this site (getsiteconfig)
then get partial extra post data for it (when you committed some sub form of the site configuration)
then update the database with new settings

*/

class Siteconfig_db {
    
  
    function Siteconfig_db()
    {
    }
     
    /** returns the config object for the current site */
    function getSiteConfig() {
        $CI = &get_instance();
        $result = new Siteconfig();
        $result->configSettings = array();
        $Q = $CI->db->get('config');
        foreach ($Q->result() as $R) {
            //where needed, interpret setting as other than string
            if ($R->setting == "ALLOWED_ATTACHMENT_EXTENSIONS") {
                $value = explode(",",$R->value);
            } 
            else if ($R->setting=='language')
            {
              $value = $R->value;
              //check existence of language
              global $AIGAION_SUPPORTED_LANGUAGES;
              if (!in_array($value,$AIGAION_SUPPORTED_LANGUAGES))
              {
                appendErrorMessage(sprintf(__("Language '%s' no longer exists under that name. Please reset the relevant profile and site settings."),$val)."<br/>");
                $value = AIGAION_DEFAULT_LANGUAGE;
              }
            } 
            else 
            {
                $value = $R->value;
            }  
            $result->configSettings[$R->setting]=$value;
        }
        if (   (!isset($result->configSettings['LOGIN_HTTPAUTH_ENABLE']) || ($result->configSettings['LOGIN_HTTPAUTH_ENABLE']=='')) 
            &&
               (isset($result->configSettings['USE_EXTERNAL_LOGIN']))
            &&
               ($result->configSettings['USE_EXTERNAL_LOGIN'] == 'TRUE')
            &&
               (isset($result->configSettings['EXTERNAL_LOGIN_MODULE']))
            &&
               ($result->configSettings['EXTERNAL_LOGIN_MODULE'] == 'Httpauth')
            )
        {
          $result->configSettings['LOGIN_HTTPAUTH_ENABLE'] = 'TRUE';
        }

        return $result;
    }
    
    
    /** get the login config settings from post data and store them in given siteconfig object */
    function getLoginSettingsFromPost($siteconfig) {
        $CI = &get_instance();
        //correct form?
        if ($CI->input->post('formname')!='siteconfig') {
            return null;
        }

        //$siteconfig->configSettings['EXTERNAL_LOGIN_MODULE']            = $CI->input->post('EXTERNAL_LOGIN_MODULE');
        if ($CI->input->post('LOGIN_CREATE_MISSING_USER')=='LOGIN_CREATE_MISSING_USER') {
            $siteconfig->configSettings['LOGIN_CREATE_MISSING_USER']           = 'TRUE';
        } else {
            $siteconfig->configSettings['LOGIN_CREATE_MISSING_USER']           = 'FALSE';
        }
        
        //DISABLED DISABLED DISABLED 
        //please see comments in userlogin class, external login function
        if ($CI->input->post('LOGIN_HTTPAUTH_ENABLE')=='LOGIN_HTTPAUTH_ENABLE') {
            $siteconfig->configSettings['LOGIN_HTTPAUTH_ENABLE']           = 'TRUE';
            $siteconfig->configSettings['USE_EXTERNAL_LOGIN']              = 'TRUE';
            $siteconfig->configSettings['EXTERNAL_LOGIN_MODULE']           = 'Httpauth';
        } else {
            $siteconfig->configSettings['LOGIN_HTTPAUTH_ENABLE']           = 'FALSE';
            $siteconfig->configSettings['USE_EXTERNAL_LOGIN']              = 'FALSE';
            $siteconfig->configSettings['EXTERNAL_LOGIN_MODULE']           = 'Aigaion';
        }
        $siteconfig->configSettings['LOGIN_HTTPAUTH_GROUP']					= $CI->input->post('LOGIN_HTTPAUTH_GROUP');
//        if ($siteconfig->configSettings['EXTERNAL_LOGIN_MODULE']=='Aigaion') {
//            $siteconfig->configSettings['USE_EXTERNAL_LOGIN']           = 'FALSE';
//        } else {
//            
//        }
        $siteconfig->configSettings['LDAP_SERVER']                     = $CI->input->post('LDAP_SERVER');
        $siteconfig->configSettings['LDAP_BASE_DN']                    = $CI->input->post('LDAP_BASE_DN');
        $siteconfig->configSettings['LDAP_DOMAIN']                     = $CI->input->post('LDAP_DOMAIN');
        if ($CI->input->post('LDAP_IS_ACTIVE_DIRECTORY')=='LDAP_IS_ACTIVE_DIRECTORY') {
            $siteconfig->configSettings['LDAP_IS_ACTIVE_DIRECTORY']    = 'TRUE';
        } else {
            $siteconfig->configSettings['LDAP_IS_ACTIVE_DIRECTORY']    = 'FALSE';
        }
        if ($CI->input->post('LOGIN_ENABLE_ANON')=='LOGIN_ENABLE_ANON') {
            $siteconfig->configSettings['LOGIN_ENABLE_ANON']           = 'TRUE';
        } else {
            $siteconfig->configSettings['LOGIN_ENABLE_ANON']           = 'FALSE';
        }
        $siteconfig->configSettings['LOGIN_DEFAULT_ANON']              = $CI->input->post('LOGIN_DEFAULT_ANON');

        if ($CI->input->post('LOGIN_ENABLE_DELEGATED_LOGIN')=='LOGIN_ENABLE_DELEGATED_LOGIN') {
            $siteconfig->configSettings['LOGIN_ENABLE_DELEGATED_LOGIN']           = 'TRUE';
        } else {
            $siteconfig->configSettings['LOGIN_ENABLE_DELEGATED_LOGIN']           = 'FALSE';
        }
        $siteconfig->configSettings['LOGIN_DELEGATES']                 = $CI->input->post('LOGIN_DELEGATES');
        if ($CI->input->post('LOGIN_DISABLE_INTERNAL_LOGIN')=='LOGIN_DISABLE_INTERNAL_LOGIN') {
            $siteconfig->configSettings['LOGIN_DISABLE_INTERNAL_LOGIN']           = 'TRUE';
        } else {
            $siteconfig->configSettings['LOGIN_DISABLE_INTERNAL_LOGIN']           = 'FALSE';
        }
        return $siteconfig;
    }    
    
    /** get the attachment config settings from post data and store them in given siteconfig object */
    function getAttachmentSettingsFromPost($siteconfig) {
        $CI = &get_instance();
        //correct form?
        if ($CI->input->post('formname')!='siteconfig') {
            return null;
        }
        $siteconfig->configSettings['ALLOWED_ATTACHMENT_EXTENSIONS']    = explode(',',$CI->input->post('ALLOWED_ATTACHMENT_EXTENSIONS'));
        if ($CI->input->post('ALLOW_ALL_EXTERNAL_ATTACHMENTS')=='ALLOW_ALL_EXTERNAL_ATTACHMENTS') {
            $siteconfig->configSettings['ALLOW_ALL_EXTERNAL_ATTACHMENTS'] = 'TRUE';
        } else {
            $siteconfig->configSettings['ALLOW_ALL_EXTERNAL_ATTACHMENTS'] = 'FALSE';
        }
        if ($CI->input->post('SERVER_NOT_WRITABLE')=='SERVER_NOT_WRITABLE') {
            $siteconfig->configSettings['SERVER_NOT_WRITABLE']          = 'TRUE';
        } else {
            $siteconfig->configSettings['SERVER_NOT_WRITABLE']          = 'FALSE';
        }
        
        return $siteconfig;
    }
    /** get the customfield config settings from post data and store them in given siteconfig object */
    function getCustomfieldSettingsFromPost($siteconfig) {
        $CI = &get_instance();
        //correct form?
        if ($CI->input->post('formname')!='siteconfig') {
            return null;
        }
        if ($CI->input->post('USE_CUSTOM_FIELDS') == 'USE_CUSTOM_FIELDS') {
          $siteconfig->configSettings['USE_CUSTOM_FIELDS']              = 'TRUE';
        } else {
          $siteconfig->configSettings['USE_CUSTOM_FIELDS']              = 'FALSE';
        }
        return $siteconfig;
    }    
    /** get the author synonym config settings from post data and store them in given siteconfig object */
    function getAuthorSynonymSettingsFromPost($siteconfig) {
        $CI = &get_instance();
        //correct form?
        if ($CI->input->post('formname')!='siteconfig') {
            return null;
        }
        if ($CI->input->post('USE_AUTHOR_SYNONYMS') == 'USE_AUTHOR_SYNONYMS') {
          $siteconfig->configSettings['USE_AUTHOR_SYNONYMS']              = 'TRUE';
        } else {
          $siteconfig->configSettings['USE_AUTHOR_SYNONYMS']              = 'FALSE';
        }
        return $siteconfig;
    }    
    /** get the cover image config settings from post data and store them in given siteconfig object */
    function getCoverImageSettingsFromPost($siteconfig) {
        $CI = &get_instance();
        //correct form?
        if ($CI->input->post('formname')!='siteconfig') {
            return null;
        }
        if ($CI->input->post('USE_BOOK_COVERS') == 'USE_BOOK_COVERS') {
          $siteconfig->configSettings['USE_BOOK_COVERS']              = 'TRUE';
        } else {
          $siteconfig->configSettings['USE_BOOK_COVERS']              = 'FALSE';
        }
        return $siteconfig;
    }    
    /** get the default user preferences config settings from post data and store them in given siteconfig object */
    function getUserDefaultsFromPost($siteconfig) {
        $CI = &get_instance();
        //correct form?
        if ($CI->input->post('formname')!='siteconfig') {
            return null;
        }
        
        $siteconfig->configSettings['DEFAULTPREF_THEME']              = $CI->input->post('DEFAULTPREF_THEME');
        $siteconfig->configSettings['DEFAULTPREF_LANGUAGE']           = $CI->input->post('DEFAULTPREF_LANGUAGE');
        $siteconfig->configSettings['DEFAULTPREF_SUMMARYSTYLE']       = $CI->input->post('DEFAULTPREF_SUMMARYSTYLE');
        $siteconfig->configSettings['DEFAULTPREF_AUTHORDISPLAYSTYLE'] = $CI->input->post('DEFAULTPREF_AUTHORDISPLAYSTYLE');
        $siteconfig->configSettings['DEFAULTPREF_LISTSTYLE']          = $CI->input->post('DEFAULTPREF_LISTSTYLE');
        $siteconfig->configSettings['DEFAULTPREF_SIMILAR_AUTHOR_TEST']          = $CI->input->post('DEFAULTPREF_SIMILAR_AUTHOR_TEST');
        if ($CI->input->post('DEFAULTPREF_NEWWINDOWFORATT')=='DEFAULTPREF_NEWWINDOWFORATT') {
            $siteconfig->configSettings['DEFAULTPREF_NEWWINDOWFORATT']       = 'TRUE';
        } else {
            $siteconfig->configSettings['DEFAULTPREF_NEWWINDOWFORATT']       = 'FALSE';
        }
        if ($CI->input->post('DEFAULTPREF_EXPORTINBROWSER')=='DEFAULTPREF_EXPORTINBROWSER') {
            $siteconfig->configSettings['DEFAULTPREF_EXPORTINBROWSER']       = 'TRUE';
        } else {
            $siteconfig->configSettings['DEFAULTPREF_EXPORTINBROWSER']       = 'FALSE';
        }
        if ($CI->input->post('DEFAULTPREF_UTF8BIBTEX')=='DEFAULTPREF_UTF8BIBTEX') {
            $siteconfig->configSettings['DEFAULTPREF_UTF8BIBTEX']       = 'TRUE';
        } else {
            $siteconfig->configSettings['DEFAULTPREF_UTF8BIBTEX']       = 'FALSE';
        }
        
        return $siteconfig;
    }        
    /** get the input/output config settings from post data and store them in given siteconfig object */
    function getInputOutputSettingsFromPost($siteconfig) {
        $CI = &get_instance();
        //correct form?
        if ($CI->input->post('formname')!='siteconfig') {
            return null;
        }
        if ($CI->input->post('CONVERT_BIBTEX_TO_UTF8')=='CONVERT_BIBTEX_TO_UTF8') {
            $siteconfig->configSettings['CONVERT_BIBTEX_TO_UTF8']       = 'TRUE';
        } else {
            $siteconfig->configSettings['CONVERT_BIBTEX_TO_UTF8']       = 'FALSE';
        }
        $siteconfig->configSettings['BIBTEX_STRINGS_IN']                = $CI->input->post('BIBTEX_STRINGS_IN');
        
        return $siteconfig;
    }        
            
    /** get the default access levels config settings from post data and store them in given siteconfig object */
    function getDefaultAccessLevelsFromPost($siteconfig) {
        $CI = &get_instance();
        //correct form?
        if ($CI->input->post('formname')!='siteconfig') {
            return null;
        }
        
        $siteconfig->configSettings['ATT_DEFAULT_READ']                = $CI->input->post('ATT_DEFAULT_READ');
        $siteconfig->configSettings['ATT_DEFAULT_EDIT']                = $CI->input->post('ATT_DEFAULT_EDIT');
        $siteconfig->configSettings['PUB_DEFAULT_READ']               = $CI->input->post('PUB_DEFAULT_READ');
        $siteconfig->configSettings['PUB_DEFAULT_EDIT']               = $CI->input->post('PUB_DEFAULT_EDIT');
        $siteconfig->configSettings['NOTE_DEFAULT_READ']               = $CI->input->post('NOTE_DEFAULT_READ');
        $siteconfig->configSettings['NOTE_DEFAULT_EDIT']               = $CI->input->post('NOTE_DEFAULT_EDIT');
        $siteconfig->configSettings['TOPIC_DEFAULT_READ']                = $CI->input->post('TOPIC_DEFAULT_READ');
        $siteconfig->configSettings['TOPIC_DEFAULT_EDIT']                = $CI->input->post('TOPIC_DEFAULT_EDIT');

        return $siteconfig;
    }        
    /** get the site integration config settings from post data and store them in given siteconfig object */
    function getSiteIntegrationSettingsFromPost($siteconfig) {
        $CI = &get_instance();
        //correct form?
        if ($CI->input->post('formname')!='siteconfig') {
            return null;
        }
        
        $siteconfig->configSettings['EMBEDDING_SHAREDDOMAIN']                     = $CI->input->post('EMBEDDING_SHAREDDOMAIN');
        $siteconfig->configSettings['LOGINTEGRATION_SECRETWORD']                     = $CI->input->post('LOGINTEGRATION_SECRETWORD');
        
        return $siteconfig;
    }    
    /** get the display config settings from post data and store them in given siteconfig object */
    function getDisplaySettingsFromPost($siteconfig) {
        $CI = &get_instance();
        //correct form?
        if ($CI->input->post('formname')!='siteconfig') {
            return null;
        }
        $siteconfig->configSettings['CFG_ADMIN']                        = $CI->input->post('CFG_ADMIN');
        $siteconfig->configSettings['CFG_ADMINMAIL']                    = $CI->input->post('CFG_ADMINMAIL');
        $siteconfig->configSettings['WINDOW_TITLE']                     = $CI->input->post('WINDOW_TITLE');
        if ($CI->input->post('USE_UPLOADED_LOGO')=='USE_UPLOADED_LOGO') {
            $siteconfig->configSettings['USE_UPLOADED_LOGO']           = 'TRUE';
        } else {
            $siteconfig->configSettings['USE_UPLOADED_LOGO']           = 'FALSE';
        }
        if ($CI->input->post('ALWAYS_INCLUDE_PAPERS_FOR_TOPIC')=='ALWAYS_INCLUDE_PAPERS_FOR_TOPIC') {
            $siteconfig->configSettings['ALWAYS_INCLUDE_PAPERS_FOR_TOPIC'] ='TRUE';
        } else {
            $siteconfig->configSettings['ALWAYS_INCLUDE_PAPERS_FOR_TOPIC'] ='FALSE';
        }
        if ($CI->input->post('PUBLICATION_XREF_MERGE')=='PUBLICATION_XREF_MERGE') {
            $siteconfig->configSettings['PUBLICATION_XREF_MERGE']       = 'TRUE';
        } else {
            $siteconfig->configSettings['PUBLICATION_XREF_MERGE']       = 'FALSE';
        }
        
        if ($CI->input->post('ENABLE_TINYMCE')=='ENABLE_TINYMCE') {
            $siteconfig->configSettings['ENABLE_TINYMCE'] = 'TRUE';
        } else {
            $siteconfig->configSettings['ENABLE_TINYMCE'] = 'FALSE';
        }
        
        return $siteconfig;
    }            
               
    /** commit the config settings embodied in the given data.
    Update the complete object, no matter whether all of it was changed or not :) */
    function update($siteconfig) {
        $CI = &get_instance();
        $CI->load->library('file_upload');
        //check rights
        $userlogin = getUserLogin();
        if (     !$userlogin->hasRights('database_manage')
            ) {
                return;
        }
        //check some of the settings on impossible combinations
        //-delegate password checking can only be enabled if some delegates are specified. Otherwise, disable again.
        if (   $siteconfig->configSettings['LOGIN_ENABLE_DELEGATED_LOGIN']=='TRUE' 
            && $siteconfig->configSettings['LOGIN_DELEGATES']==''
           ) {
            appendErrorMessage(__('Delegated password checking can only be enabled when some password checking module was specified! Since this was not the case, delegated password checking has been disabled.').'<br/>');
            $siteconfig->configSettings['LOGIN_ENABLE_DELEGATED_LOGIN']='FALSE';
        }
        //-at least one of internal or external login must be enabled. If not, enable internal login again
        if (   $siteconfig->configSettings['LOGIN_DISABLE_INTERNAL_LOGIN']=='TRUE' //no internal login
            && 
               $siteconfig->configSettings['LOGIN_ENABLE_DELEGATED_LOGIN']!='TRUE' //no delegate login or no delegates
           ) {
            appendErrorMessage(__('At least one of internal login or delegated password checking must be enabled! Since this was not the case, internal login has been re-enabled.').'<br/>');
            $siteconfig->configSettings['LOGIN_DISABLE_INTERNAL_LOGIN']='FALSE';
        }
        //-Anon access enabled, but no default anon account specified? give warning, but do not bother changing the settings
        if ($siteconfig->configSettings['LOGIN_ENABLE_ANON']=='TRUE') { //anon access enabled?
            $anonAcc = $siteconfig->configSettings['LOGIN_DEFAULT_ANON'];
            $anonUser = $CI->user_db->getByID($anonAcc);
            if ($anonUser == NULL || $anonUser->type!='anon') {//no valid default anon account
                appendMessage(__('Anonymous guest access has been enabled, but no valid anonymous account was specified. Note that anonymous login will not work until such an anonymous account has been created, and assigned as default anonymous account.').'<br/>');
            }
           
        }
        //check whether author synonyms are enabled. If not, but synonyms exist in the database, report this as an error and suggest the user to repair this on the maintenance page.
        if (isset($siteconfig->configSettings['USE_AUTHOR_SYNONYMS'] ) && $siteconfig->configSettings['USE_AUTHOR_SYNONYMS'] != 'TRUE')
        {
          
          $CI->db->where('synonym_of !=', '0');
          //$res = $CI->db->get('author');
          if ($CI->db->count_all_results('author') > 0) //synonyms exist, remove them (!)
          { 
            appendMessage(__("Some authors have synonym names associated to them.")." ".__("These synonyms should been merged into the primary authors, and the synonym names themselves should be removed.")." ".__("Please go to the site maintenance page to do this.")."<br/>");
          }
        }
        //start to update
        foreach ($siteconfig->configSettings as $setting=>$value) {
            
            if ($setting == 'ALLOWED_ATTACHMENT_EXTENSIONS') {
            	#check allowed extensions: all extensions should be prefixed with a . and should be trimmed of spaces
            	$templist = array();
            	foreach ($value as $ext) {
            		$ext = trim($ext);
            		if (($ext=="") || ($ext==".")) {
            			continue;
            		}
            		if (strpos($ext,".") === FALSE) {
            			$ext = ".".$ext;
            		}
            		//disallow a specific class of attachments permanently
            		if (!in_array(strtolower(substr($ext,-4)),array('.php','php3','php4','.exe','.bat'))) {
            		    $templist[] = $ext;
            		} else {
            		    appendErrorMessage(sprintf(__("The extension '%s' is never allowed for Aigaion attachments, and has been removed from the list of allowed attachments."),$ext));
            		}
            	}
            	if (sizeof($templist)==0) {
            		$templist[] = ".pdf";
            	}                
            	$value = implode(',',$templist);
            }
        	#check existence of setting
        	$CI->db->query("INSERT IGNORE INTO ".AIGAION_DB_PREFIX."config (setting) VALUES (".$CI->db->escape($setting).")");
        	#update value
            $CI->db->where('setting', $setting);
            $CI->db->update('config', array('value'=>$value));
        	if (mysql_error()) {
        		appendErrorMessage(__("Error updating config").": <br/>");
        		appendErrorMessage(mysql_error()."<br/>");
        	}
        }
    	#upload (from post) new custom logo, if available
        if (  ($siteconfig->configSettings['USE_UPLOADED_LOGO']=='TRUE') 
            || (
                isset($_FILES['new_logo'])
                &&
                $_FILES['new_logo']['error']==0
                ) ) {
            $siteconfig->configSettings['USE_UPLOADED_LOGO']='TRUE';
            $max_size = 1024*10; // the max. size for uploading
            	
            $my_upload = new File_upload;
            $my_upload->upload_dir = AIGAION_ATTACHMENT_DIR.'/'; // "files" is the folder for the uploaded files (you have to create this folder)
            $my_upload->extensions = array('.jpg');
            $my_upload->max_length_filename = 100; // change this value to fit your field length in your database (standard 100)
            $my_upload->rename_file = true;
        	$my_upload->the_temp_file = $_FILES['new_logo']['tmp_name'];
        	$my_upload->the_file = $_FILES['new_logo']['name'];
        
        	$my_upload->http_error = $_FILES['new_logo']['error'];
        	if ($my_upload->http_error > 0) {
        		//appendErrorMessage("Error while uploading custom logo: ".$my_upload->error_text($my_upload->http_error));
        	} else {
    
            	$my_upload->replace = "y";
            	$my_upload->do_filename_check = "n"; // use this boolean to check for a valid filename
            
                if (!$my_upload->upload("custom_logo")) {
                    //if failed: set to false again and give message? no, cause maybe there just was no file uploaded :)
                    appendErrorMessage(__("Failed to upload custom logo.")." ".$my_upload->show_error_string().'<br/>' );
                    //$USE_UPLOADED_LOGO = "FALSE";
                } else {
                    appendMessage(__("New logo uploaded").".<br/>");
                }
            }
        } else {
            //appendMessage("No new logo<br/>".$siteconfig->configSettings['USE_UPLOADED_LOGO']);
        }
        #reset cached config settings
        $CI = &get_instance();
        $CI->latesession->set('SITECONFIG',null);
        #reset profile settings (to account for possibly changed preference defaults)
        $userlogin->initPreferences();
    }
}
?>