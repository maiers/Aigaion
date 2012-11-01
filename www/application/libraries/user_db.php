<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** This class regulates the database access for User's. Several accessors are present that return a User or 
array of User's. */
class User_db {
  
    function User_db()
    {
    }
    
    function getByID($user_id)
    {
        $CI = &get_instance();
        $Q = $CI->db->query("SELECT * from ".AIGAION_DB_PREFIX."users where user_id=".$CI->db->escape($user_id)." AND type<>'group'");
        if ($Q->num_rows() > 0)
        {
            return $this->getFromRow($Q->row());
        }  
    }
   
    function getByLogin($login)
    {
        $CI = &get_instance();
        $Q = $CI->db->query("SELECT * from ".AIGAION_DB_PREFIX."users where login=".$CI->db->escape($login)." AND type<>'group'");
        if ($Q->num_rows() > 0)
        {
            return $this->getFromRow($Q->row());
        }  
    }
   
    function getFromRow($R)
    {
        $CI = &get_instance();
        //no access rights check - for various reasons (e.g. finding abbreviations) we need
        //to be able to read all accounts.
        $user = new User;
        $user->user_id            = $R->user_id;
        $user->initials           = $R->initials;
        $user->firstname          = $R->firstname;
        $user->betweenname        = $R->betweenname;
        $user->surname            = $R->surname;
        $user->email              = $R->email;
        $user->lastreviewedtopic  = $R->lastreviewedtopic;
        $user->lastupdatecheck  = $R->lastupdatecheck;
        $user->abbreviation       = $R->abbreviation;
        $user->login              = $R->login;
        $user->password           = $R->password;
        $user->type               = $R->type;
        if (isset($R->password_invalidated)) //doesn't exist in earlier versions before 2.0.xx 
            $user->password_invalidated = $R->password_invalidated;
        //preferences: all other columns are preferences
        $user->preferences        = array();
        foreach ($R as $key => $value)
        {
            if (!isset($user->$key)) {
                $user->preferences[$key] = $value;
                //appendMessage($key.','.$value.'<br>');
            }
        }
        //assigned rights
        $user->assignedrights     = array();
        $query = $CI->db->get_where('userrights',array('user_id'=>$R->user_id));
        foreach ($query->result() as $row) {
            $user->assignedrights[] = $row->right_name;
        }
        //the ids of all groups that the user is a part of
        $user->group_ids            = array();
        $query = $CI->db->get_where('usergrouplink',array('user_id'=>$R->user_id));
        foreach ($query->result() as $row) {
            $user->group_ids[] = $row->group_id;
        }
        //return result
        return $user;
    }


    /** Construct a topic from the POST data present in the topics/edit view. 
    Return null if the POST data was not present. */
    function getFromPost()
    {
        $CI = &get_instance();
        //correct form?
        if ($CI->input->post('formname')!='user') {
            return null;
        }
        //get basic data
        $user = new User;
        $user->user_id            = $CI->input->post('user_id');
        $user->initials           = $CI->input->post('initials');
        $user->firstname          = $CI->input->post('firstname');
        $user->betweenname        = $CI->input->post('betweenname');
        $user->surname            = $CI->input->post('surname');
        $user->email              = $CI->input->post('email');
        $user->lastreviewedtopic  = $CI->input->post('lastreviewedtopic');
        $user->abbreviation       = $CI->input->post('abbreviation');
        $user->login              = $CI->input->post('login');
        if ($CI->input->post('password')=='') {
            $user->password       = '';
        } else {
            $user->password       = md5($CI->input->post('password'));
        }
        $user->type        = $CI->input->post('type');
        $user->password_invalidated    = $CI->input->post('password_invalidated');
        $user->toBeDisabled     = $CI->input->post('disableaccount')=='disableaccount';

        $user->preferences['theme']              = $CI->input->post('theme');
        $user->preferences['summarystyle']       = $CI->input->post('summarystyle');
        $user->preferences['authordisplaystyle'] = $CI->input->post('authordisplaystyle');
        $user->preferences['liststyle']          = $CI->input->post('liststyle');
        $user->preferences['similar_author_test']= $CI->input->post('similar_author_test');
        $user->preferences['newwindowforatt']    = $CI->input->post('newwindowforatt')=='newwindowforatt';
        $user->preferences['exportinbrowser']    = $CI->input->post('exportinbrowser')=='exportinbrowser';
        $user->preferences['utf8bibtex']         = $CI->input->post('utf8bibtex')=='utf8bibtex';
        $user->preferences['language']           = $CI->input->post('language');

        $user->assignedrights = array();
        foreach (getAvailableRights() as $right=>$description) {
            if ($CI->input->post($right)) {
                $user->assignedrights[] = $right;
            }
        }

        //the ids of all groups that the user is a part of
        foreach ($CI->group_db->getAllGroups() as $group) {
            if ($CI->input->post('group_'.$group->group_id)) {
                $user->group_ids[] = $group->group_id;
            }
        }

        return $user;
    }
        
    /** Return all Users (anon and normal) from the database. */
    function getAllUsers() {
        $CI = &get_instance();
        $result = array();
        $Q = $CI->db->get_where('users',array('type !='=>'group'));
        foreach ($Q->result() as $R) {
            $result[] = $this->getFromRow($R);
        }
        return $result;
    }
    /** Return all anonymous Users from the database. */
    function getAllAnonUsers() {
        $CI = &get_instance();
        $result = array();
        $Q = $CI->db->get_where('users',array('type'=>'anon'));
        foreach ($Q->result() as $R) {
            $result[] = $this->getFromRow($R);
        }
        return $result;
    }
    /** Return all non-anonymous Users from the database. */
    function getAllNormalUsers() {
        $CI = &get_instance();
        $result = array();
        $Q = $CI->db->get_where('users',array('type'=>'normal'));
        foreach ($Q->result() as $R) {
            $result[] = $this->getFromRow($R);
        }
        return $result;
    }
    /** Return all external Users from the database. */
    function getAllExternalUsers() {
        $CI = &get_instance();
        $result = array();
        $Q = $CI->db->get_where('users',array('type'=>'external'));
        foreach ($Q->result() as $R) {
            $result[] = $this->getFromRow($R);
        }
        return $result;
    }

    /** Add a new user with the given data. Returns the new user_id, or -1 on failure. */
    function add($user) {
        $CI = &get_instance();
        //add only allowed with right rights:
        $userlogin  = getUserLogin();
        if (!$userlogin->hasRights('user_edit_all')) {
            return -1;
        }
        //disable account?
        if (($user->type=='normal') && ($user->toBeDisabled == True))  {
            $user->password_invalidated = 'TRUE';
        }
        //anon and external accounts have disabled password (always)
        if (($user->type=='anon') || ($user->type=='external')) {
            $user->password_invalidated = 'TRUE';
        }
        $newwindowforatt ='FALSE';
        if ($user->preferences['newwindowforatt']) {
            $newwindowforatt ='TRUE';
        }
        $exportinbrowser ='FALSE';
        if ($user->preferences['exportinbrowser']) {
            $exportinbrowser ='TRUE';
        }
        $utf8bibtex ='FALSE';
        if ($user->preferences['utf8bibtex']) {
            $utf8bibtex ='TRUE';
        }
        //is language in supported list?
        if ($user->preferences['language'] != 'default') 
        {
          global $AIGAION_SUPPORTED_LANGUAGES;
          if (!in_array($user->preferences['language'],$AIGAION_SUPPORTED_LANGUAGES)) 
          {
            appendErrorMessage(__("Unknown language").": ".$user->preferences['language']."<br/>");
            $user->preferences['language'] = AIGAION_DEFAULT_LANGUAGE;
          }
        }
        $CI->db->insert("users",
                                         array('initials'           => $user->initials,
                                               'firstname'          => $user->firstname,
                                               'betweenname'        => $user->betweenname,
                                               'surname'            => $user->surname,
                                               'email'              => $user->email,
                                               'lastreviewedtopic'  => $user->lastreviewedtopic,
                                               'abbreviation'       => $user->abbreviation,
                                               'login'              => $user->login,
                                               'password'           => $user->password,
                                               'password_invalidated' => $user->password_invalidated,
                                               'type'               => $user->type,
                                               'theme'              => $user->preferences['theme'],
                                               'language'           => $user->preferences['language'],
                                               'summarystyle'       => $user->preferences['summarystyle'],
                                               'authordisplaystyle' => $user->preferences['authordisplaystyle'],
                                               'liststyle'          => $user->preferences['liststyle'],
                                               'similar_author_test'=> $user->preferences['similar_author_test'],
                                               'newwindowforatt'    => $newwindowforatt,
                                               'exportinbrowser'    => $exportinbrowser,
                                               'utf8bibtex'         => $utf8bibtex
                                               )
                              );
        $new_id = $CI->db->insert_id();                                   
        if ($userlogin->hasRights('user_assign_rights')) {
            //add rights
            foreach ($user->assignedrights as $right) {
                if ($user->type=='anon') {
                    if ($right=='bookmarklist') {
                        appendErrorMessage(__("Removed 'bookmarklist' right from anonymous user: it makes no sense to assign it since many people will be loggin on with that account simultaneously.")."<br/>");
                        continue;
                    }
                }
                    
                $CI->db->insert('userrights',array('user_id'=>$new_id,'right_name'=>$right));
            }
        }
        
        //add group links, and rightsprofiles for these groups, to the user
        foreach ($user->group_ids as $group_id) {
            $CI->db->insert('usergrouplink',array('user_id'=>$new_id,'group_id'=>$group_id));
            $group = $CI->group_db->getByID($group_id);
            foreach ($group->rightsprofile_ids as $rightsprofile_id) {
                $rightsprofile = $CI->rightsprofile_db->getByID($rightsprofile_id);
                foreach ($rightsprofile->rights as $right) {
                    $CI->db->delete('userrights',array('user_id'=>$new_id,'right_name'=>$right));
                    $CI->db->insert('userrights',array('user_id'=>$new_id,'right_name'=>$right));
                }
                
            }
        }
        $user->user_id = $new_id;
        
        $CI->topic_db->subscribeUser( $user,1);
        appendMessage(__("User added").".<br/>");
        return $new_id;
    }

    /** Commit the changes in the data of the given user. Returns TRUE or FALSE depending on 
    whether the operation was successfull. */
    function update($user) {
        $CI = &get_instance();
        //check rights
        $userlogin = getUserLogin();
        if (     !$userlogin->hasRights('user_edit_all')
             &&  
                (!$userlogin->hasRights('user_edit_self') || ($userlogin->userId() != $user->user_id))
            ) {
                return False;
        }
        //check whether this is the correct user...
        $user_test = $CI->user_db->getByID($user->user_id);
        if ($user_test == null) {
            return False;
        }
        // DR 2008.08.29: no-one can change login names anymore in edit forms......        
        if ($user_test->login != $user->login) {
            appendErrorMessage(__("Login names cannot be changed. Login name has been reset to old value. Other changes have been saved."));
            $user->login = $user_test->login;
        }
        //DR 2008.10.10: if this is the current user, set 'type' back to what it was
        if ($user->user_id == $userlogin->userId()) {
          $user->type = $user_test->type;
        }
        //disable account?
        if (($user->type=='normal') && ($user->toBeDisabled == True))  {
            $user->password_invalidated = 'TRUE';
        }
        if (($user->type=='anon') || ($user->type=='external')) {
            //always invalidate password for anon and external accounts
            $user->password_invalidated = 'TRUE';
        } else if ($user->password_invalidated == 'TRUE') {
            appendMessage(__('The account does not have a valid password. It has been disabled. You can ask an admin to re-enable it.').'<br/>');
        }
        //you are not alowed to ENABLE the account here, only DISABLE it. ENABLING goes through the setpassword method: setting a password enables the account as well.
        if ($user_test->password_invalidated == 'TRUE' && $user->password_invalidated != 'TRUE')
        {
            appendErrorMessage(__("You cannot enable an invalidated account through this method. Instead, enable it by setting a password. Other changes have been saved."));
            $user->password_invalidated = 'TRUE';
        }

        $newwindowforatt ='FALSE';
        if ($user->preferences['newwindowforatt']) {
            $newwindowforatt ='TRUE';
        }
        $exportinbrowser ='FALSE';
        if ($user->preferences['exportinbrowser']) {
            $exportinbrowser ='TRUE';
        }
        $utf8bibtex ='FALSE';
        if ($user->preferences['utf8bibtex']) {
            $utf8bibtex ='TRUE';
        }
        //is language in supported list?
        if ($user->preferences['language'] != 'default') 
        {
          global $AIGAION_SUPPORTED_LANGUAGES;
          if (!in_array($user->preferences['language'],$AIGAION_SUPPORTED_LANGUAGES)) 
          {
            appendErrorMessage(__("Unknown language").": ".$user->preferences['language']."<br/>");
            $user->preferences['language'] = AIGAION_DEFAULT_LANGUAGE;
          }
        }
        $updatefields =  array('initials'           => $user->initials,
                               'firstname'          => $user->firstname,
                               'betweenname'        => $user->betweenname,
                               'surname'            => $user->surname,
                               'email'              => $user->email,
                               'lastreviewedtopic'  => $user->lastreviewedtopic,
                               'abbreviation'       => $user->abbreviation,
                               'login'              => $user->login,
                               'password_invalidated' => $user->password_invalidated,
                               'type'               => $user->type,
                               'theme'              => $user->preferences['theme'],
                               'language'           => $user->preferences['language'],
                               'summarystyle'       => $user->preferences['summarystyle'],
                               'authordisplaystyle' => $user->preferences['authordisplaystyle'],
                               'liststyle'          => $user->preferences['liststyle'],
                               'similar_author_test'=> $user->preferences['similar_author_test'],
                               'newwindowforatt'    => $newwindowforatt,
                               'exportinbrowser'    => $exportinbrowser,
                               'utf8bibtex'         => $utf8bibtex
                               ); //password is NOT updated here! ue setPassword for that!

        $CI->db->update('users', $updatefields,array('user_id'=>$user->user_id));
        //if the user is NOT anonymous, but it is the 'DEFAULT ANONYMOUS ACCOUNT from the site config settings, 
        //turn off the anonymous access and give a message warning
        if ($user->type!='anon') {
            if ($user->user_id==getConfigurationSetting("LOGIN_DEFAULT_ANON")) {
                $siteconfig = $CI->siteconfig_db->getSiteConfig();
                $siteconfig->configSettings['LOGIN_DEFAULT_ANON'] = '';
                $siteconfig->update();
                appendMessage(__("You just set the default anonymous user to non-anonymous. Therefore the default anonymous user configuration setting has been cleared.")."<br/>");
            }
        }

        if ($userlogin->hasRights('user_assign_rights')) {
            //remove all rights, then add the right ones again
            $CI->db->delete('userrights',array('user_id'=>$user->user_id));
            //add rights
            foreach ($user->assignedrights as $right) {
                if ($user->type=='anon') {
                    if ($right=='bookmarklist') {
                        appendErrorMessage(__("Removed 'bookmarklist' right from anonymous user: it makes no sense to assign it since many people will be loggin on with that account simultaneously.")."<br/>");
                        continue;
                    }
                }
                $CI->db->insert('userrights',array('user_id'=>$user->user_id,'right_name'=>$right));
            }
        }

        //groups assignment 
        if ($userlogin->hasRights('user_edit_all')) {
            //add group links, and rightsprofiles for these groups, to the user
            //BUT ONLY FOR GROUPS THAT WERE NOT YET LINKED TO THIS USER
            $oldgroups = array();
            $oldgrQ = $CI->db->get_where('usergrouplink',array('user_id'=>$user->user_id));
            foreach($oldgrQ->result() as $row) {
                $oldgroups[] = $row->group_id;
            }
            $CI->db->delete('usergrouplink',array('user_id'=>$user->user_id));
            foreach ($user->group_ids as $group_id) {
                //add group (anew)
                $CI->db->insert('usergrouplink',array('user_id'=>$user->user_id,'group_id'=>$group_id));
                //skip rights if already member of this group..
                if (in_array($group_id,$oldgroups))continue;
                //else add pertaining rights as well
                $group = $CI->group_db->getByID($group_id);
                foreach ($group->rightsprofile_ids as $rightsprofile_id) {
                    $rightsprofile = $CI->rightsprofile_db->getByID($rightsprofile_id);
                    foreach ($rightsprofile->rights as $right) {
                        $CI->db->delete('userrights',array('user_id'=>$user->user_id,'right_name'=>$right));
                        $CI->db->insert('userrights',array('user_id'=>$user->user_id,'right_name'=>$right));
                    }
                    
                }
            }
        }
        
        //if was this user: update preferences, check if user_assign_rights was removed from self...
        if ($user->user_id == $userlogin->userId()) {
            $userlogin->initPreferences();
            $CI = &get_instance();
            $CI->latesession->set('USERLOGIN', $userlogin);
            if ($userlogin->hasRights("user_assign_rights")) {
    	        if (!in_array("user_assign_rights",$user->assignedrights)) {
    	            appendErrorMessage("<b>".__("You just removed your own right to assign user rights! Are you sure that this is correct? If not, re-assign this right before logging out!")."</b><br/>");
    	        }
    	        appendMessage(__("Profile updated, changes to rights of users are applied after the user has logged in again.")."<br/>");
    	    }
        }
        appendMessage(__("User data changed. Changed access rights will be valid upon next login.")."<br/>");
        return True;
    }

    /** Set a new password for user, and, if needed, enable it */
    function setPassword($user, $pwd)
    {
        $CI = &get_instance();
        
        if (($user->type=='anon') || ($user->type=='external')) 
        {
          // cannot change password for anon or external account
          appendErrorMessage(__("You cannot change the password for anonymous accounts or externally managed accounts"));
          return;
        }
        //check user rights:
        // either own pwd and user_edit_self, or user_edit_all
        $userlogin = getUserLogin();
        if (   (!($userlogin->hasRights('user_edit_self') && $user->user_id==$userlogin->userId()))
            &&   
               (!$userlogin->hasRights('user_edit_all')) )
        {
          appendErrorMessage(__('Set password').': '.__('insufficient rights').'.<br/>');
          return;
        }        
        if ($pwd == "")
        {
          appendErrorMessage(__('Set password').': '.__('no empty passwords allowed').'.<br/>');
          return;
        }
        $user->password = $pwd;
        $user->password_invalidated = 'FALSE';
        //store password, enable user 
        $CI->db->update('users',array('password'=>md5($pwd),'password_invalidated'=>'FALSE'),array('user_id'=>$user->user_id));
        appendMessage(__('Password updated and user account enabled'));
    }

    /** delete given object. where necessary cascade. Checks for edit and read rights on this object and all cascades
    in the _db class before actually deleting. */
    function delete($user) {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        //collect all cascaded to-be-deleted-id's: none
        //check rights
        //check, all through the cascade, whether you can read AND edit that object
        if (!$userlogin->hasRights('user_edit_all')) {
            //if not, for any of them, give error message and return
            appendErrorMessage(__('Cannot delete user').': '.__('insufficient rights').'.<br/>');
            return;
        }
        if (empty($user->user_id)) {
            appendErrorMessage(__('Cannot delete user').': '.__('erroneous ID').'.<br/>');
            return;
        }
        //otherwise, delete all dependent objects by directly accessing the rows in the table 
        $CI->db->delete('users',array('user_id'=>$user->user_id));
        //delete links
        $CI->db->delete('usergrouplink',array('user_id'=>$user->user_id));
        $CI->db->delete('userrights',array('user_id'=>$user->user_id));
        $CI->db->delete('userpublicationmark',array('user_id'=>$user->user_id));
        $CI->db->delete('userbookmarklists',array('user_id'=>$user->user_id));
        $CI->db->delete('usertopiclink',array('user_id'=>$user->user_id));
        //add the information of the deleted rows to trashcan(time, data), in such a way that at least manual reconstruction will be possible
    }    
    
}
?>