<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
This file defines the class Login that is used to regulate the login to the site.
The UserLogin object is used to start, stop and query a user login.
The UserLogin object retrieves relevant information about the current login session (rights-info; 
whether the current login is anonymous; the preferences of the current user; etc) and provides 
methods to log in or out (user or anonymous account) given the right info.

Note: Creating, changing and deleting ACCOUNTS (as opposed to a 'current login session') is NOT done in this class! 

The UserLogin class uses some external information from the site config settings: 
    -whether anonymous login is allowed, the id of anonymous user
    -settings about password checking delegates
    -settings about external login modules
    
A note on the anonymous access:
    - To use the anonymous access facilities, you must enable it in the Site configuration page, and choose
      a user account that will be used to login the anonymous user.
    - If anonymous access is enabled, and you are not logged in as a 'normal' user, you will automatically
      be logged in as the anonymous user with all rights assigned to that anonymous user. A button will
      appear in the menu that allows you to login as a 'normal' user through the login screen.
    - If anonymous access is enabled, you cannot login with the anonymous user account _through the login screen_

The UserLogin class assumes that the connection to the database has already been made 

Access is through the getUserLogin() function in the login helper
*/

//echo 'userlogin loaded';
class UserLogin {
    
    //note : all 'var' should actually be 'private', but PHP 4 doesn't support that :(
    
    /* ================ Class variables ================ */
    
    /** True if the user is currently logged in, anonymous or non-anonymous */
    var $bIsLoggedIn = False; 
    /** True if the user is currently logged in as anonymous user */
    var $bIsAnonymous = False;
    /** The user name of the user currently logged in */
    var $sLoginName = "";
    /** The user ID of the user currently logged in */
    var $iUserId = "";
    /** feedback on any errors that occurred */
    var $sNotice = "";
    /** A list of some preference settings. */
    var $preferences = array();
    /** the preferences as they are actually used, i.e. taking site defaults into account */
    var $effectivePreferences = array();
    /** A list of the assigned rights for this user. */
    var $rights = array();
    /** The configured menu for this user. */
    var $theMenu = "";
    /** If true, the user was logged in using the logintegration controller, 
     *  and the status of the login token needs to be regularly checked in the 
     *  logintegration table. */
    var $checkToken = False;
    /** The login token used to log this account in through the logintegration
     *  controller. Only relevant if checkToken is True. See isLoggedIn 
     *  function. */
    var $loginToken = '';    
    
    
    var $theUser = null;

    /** this var is set to True if the user just logged out. This fact needs to be remembered 
    because otherwise we run the risk of immedately loggin the user in again through the cookies 
    (cookies are not deleted properly in PHP4 when using a CI redirect after deleting the cookies :( ) */
    var $bJustLoggedOut = False;
        
    /* ================ Basic accessors ================ */
    
    /** This method is called for every controller access, thanks to the login_filter.
    This is also where we check for the schema updates.... so if the Aigaion engine is replaced
    with a new version, every user will get logged out upon the next page access. */
    function isLoggedIn() {
        if ($this->bIsLoggedIn) {
            //check login token?
            if ($this->checkToken) 
            {
              $CI = &get_instance();
              $res = $CI->db->get_where('logintegration',array('token'=>$this->loginToken));
              if ($res->num_rows() ==0) 
              {
                $this->logout();
                $this->sNotice = __("You have been logged out because your login token disappeared.")."<br/>";
              }
              $row = $res->row();                
              if ($row->status != 'loggedin')
              {
                $this->logout();
                $CI->db->delete('logintegration',array('token'=>$this->loginToken));
                $this->sNotice = __("You have been logged out because your login token expired.")."<br/>";
              }
              
            }
            
            //check schema
            if (checkSchema()) { 
              return True; //OK? return true;
            } else {
              
              log_message('error', 'checkSchema failed in isLoggedIn');
              
              $this->logout();
              $this->sNotice = __("You have been logged out because the Aigaion Engine is in the process of being updated.")
                                ."<br/>"
                                .__("If you are a user with database_manage rights, please login to complete the update.")
                                ."<br/>";
            }
        }            
        return False;
    }
    function isAnonymous() {
        return $this->bIsAnonymous;
    }
    function loginName() {
        return $this->sLoginName;
    }
    function userId() {
        return $this->iUserId;
    }    
    function notice() {
        $result = $this->sNotice;
        $this->sNotice = "";
        return $result;
    }
    function user() {
        return $this->theUser;
    }
    function getMenu() {
        return $this->theMenu;
    }
    
    /* ================ Constructor ================ */
    
    /** Initially, the user is NOT logged in. */
    function UserLogin() {
        //...no construction stuff needed, really. everything happens when the user logs in.
    }
    
    /* ================ Access methods for user rights and preferences ================ */
    
    /** Initializes the cached rights from the database. Always called just after login. */
    function initRights() {
        $CI = &get_instance();
        $this->rights = array();
        $Q = $CI->db->get_where('userrights',array('user_id'=>$this->iUserId));
        foreach ($Q->result() as $R) {
            $this->rights[] = $R->right_name;
        }
    }
    
    /** die() if the currently logged in user does not have the given right. Uses hasRights($right) */
    function checkRights($right) 
    {
        if ($this->hasRights($right)) {
            return true;
        } else {
            echo "<div class='errormessage'>"
                 .__("You do not have sufficient rights for the requested operation or page.")
                 ."<br/>"
                 .__("Sorry for the inconvenience.")
                 ."</div>";
            die();
            return false;
        }
    }

    /** die() due to rights problems. This function is provided to fail rights in a uniform wawy,
    even in cases where the checking of the right was done without resorting to checkRights
    (for example because the condition is more than just one simple boolean check). */
    function failRights() {
        echo "<div class='errormessage'>"
                 .__("You do not have sufficient rights for the requested operation or page.")
                 ."<br/>"
                 .__("Sorry for the inconvenience.")
                 ."</div>";
        die();
        return false;
    }

    /** Return True iff the current user has certain (named) rights, false otherwise 
     *  (or if no user is logged in). */
    function hasRights($right) {
        //no logged user: no right.
        if (!$this->bIsLoggedIn) return False;
        if ($right=="") return true;
        if (in_array($right,$this->rights)) {
            return true;
        } else {
            return false;
        }
    }
   
    /** Initialize the preferences. Note: this method should also be called if the preferences
    have changed. */
    function initPreferences() {
        $CI = &get_instance();
        //right now, I just enumerate all relevant preferences from the user-table
        $nonprefs=array("password");
        $this->preferences = array();
        $Q = $CI->db->get_where('users',array('user_id'=>$this->iUserId));
        if ($Q->num_rows()>0) {
            $R = $Q->row_array();
            //where needed, interpret setting as other than string
            foreach ($R as $key=>$val) {
                if (!in_array($key ,$nonprefs)) {
                    //some preferences must be slightly transformed here...
                    if ($key=='theme') {
                        if (!themeExists($val)) {
                            appendErrorMessage(sprintf(__("Theme '%s' no longer exists"),$val).".<br/>");
                            $val = "default";
                        }
                    }
                    if ($key=='language')
                    {
                      //check existence of language
                      if ($val != 'default') 
                      {
                        global $AIGAION_SUPPORTED_LANGUAGES;
                        if (!in_array($val,$AIGAION_SUPPORTED_LANGUAGES))
                        {
                          appendErrorMessage(sprintf(__("Language '%s' no longer exists under that name. Please reset the relevant profile and site settings."),$val)."<br/>");
                          $val = AIGAION_DEFAULT_LANGUAGE;
                        }
                      }
                    }
                    //store preference in object
                    $this->preferences[$key]=$val;
                    //if set to default, look up site default...
                    if ($val=='default') {
                        $val = getConfigurationSetting('DEFAULTPREF_'.strtoupper($key));
                    }
                    //and finally, store preference in effectivePreferences
                    $this->effectivePreferences[$key]=$val;
                }
            }
        }        
    }
    
    /** Return the value of a certain User Preference for the currently logged in user. */
    function getPreference($preferenceName) {
        if (array_key_exists($preferenceName,$this->effectivePreferences)) {
            return $this->effectivePreferences[$preferenceName]; 
        } else {
            return "";
        }
    }
    
    /* ================ login/logout methods ================ */
 
    /**
    a small method to check the password for the current user (some actions require the password to be given anew)
    
    can only be called if user is logged in, and can only check password for current user; and not for anonymous users
    */
    function checkPassword($pwd) 
    {
        $CI = &get_instance();
        if (!$this->isLoggedIn()) return false;
        if ($this->isAnonymous()) return false;
        if ($this->theUser->type == 'normal')
        {
            $Q = $CI->db->get_where('users',array('login'=>$this->theUser->login,'password'=>md5($pwd)));
            if ($Q->num_rows()<=0) {
                return false; //wrong pwd
            }
            return true;
        }
        else
        {   //external user? check the delegates
            if (getConfigurationSetting("LOGIN_ENABLE_DELEGATED_LOGIN") == 'TRUE') {
                $CI->load->library('Passwordchecker');
                $delegates = explode (',',getConfigurationSetting("LOGIN_DELEGATES"));
                foreach ($delegates as $delegate) {
                    //determine next delegate
                    $delegateLibrary = 'passwordchecker_'.$delegate;
                    $CI->load->library($delegateLibrary);
                    //check password
                    $loginInfo = $CI->$delegateLibrary->checkPassword($loginName, $loginPwd,false);
                    if (isset($loginInfo['uname']) && ($loginInfo['uname'] != null) && ($loginInfo['uname']!= '')) {
                        //password was OK
                        return true;
                    }
                }
                return false; // no delegate accepted the password
            } 
            else
            {   //external user, but delegates not allowed ?
                return false;
            }
        } //no one accepted the password
        return false;
    }
   
    /** This is the method that you call to perform the login
     *  If already logged in as non-anonymous, do nothing
     *  Else if external login in use: try to login from external module
     *  Else if password checking delegate in use: try to login from password checking delegate
     *  Else if login vars have been posted and internal login enabled: login from POST vars
     *  Else if cookies are available: login from cookies (delegate login needs be enabled if cookie is external account)
     *  Else if anonymous login allowed: login anonymously */
    function login() {
        $CI = &get_instance();
        //If already logged in as non-anonymous, do nothing
        if ($this->bIsLoggedIn && !$this->bIsAnonymous) return;
        //if logged in as anonymous: kill it, to be certain; it will be reestablished.
        $this->bIsLoggedIn = false;
        $this->bIsAnonymous = False;
        $this->sLoginName = "";
        $this->iUserId = "";                
        $CI->latesession->set('USERLOGIN', $this);
        
        //Maybe we can login from external module?
        if (getConfigurationSetting("USE_EXTERNAL_LOGIN") == 'TRUE') {
            //this part will change in a major way when mode 3 login is implemented
            $result = $this->loginFromExternalSystem();
            if ($this->bIsLoggedIn) {
                return;
            }
            if ($result == 1) {
                //report error and return
                $this->sNotice = __("Unknown user or wrong password from external login module");
                //return; don't return, but rather attempt to do the anonymous login later on
            }
            if ($result == 2) {
                //report error and return
                $this->sNotice = __("No login info available");
                //return; don't return, but rather attempt to do the anonymous login later on
            }
            if ($result == 3) { //critical fail
                return 1;
            }

        }
        $result = 0;
        //try to find login info from post vars (form) or from cookies
        $loginInfoAvailable = false;
        $loginName = '';
        $loginPwd = '';
        $remember=False;
        $pwdInMd5 = false;
        if (((isset($_POST["loginName"]))) && (isset($_POST['loginPass'])))    {
            //user tries to log in via login screen; get username & pwd
            //determine uname/pwd from POST vars
            $loginName = $_POST["loginName"];
            $loginPwd = $_POST['loginPass'];
            $remember=False;
            if (isset($_POST['remember']))$remember=True;
            
            $loginInfoAvailable = true;
            if ($loginPwd=='') {
                $this->sNotice=__('No password submitted');
                $loginInfoAvailable = false;
            }
        } else {
            //if none found, try to determine uname/pwd from COOKIE
            if ($this->bJustLoggedOut) { //not if we just logged out, because cookie needs to be completely destroyed
                $this->bJustLoggedOut = False; 
            } else if (isset($_COOKIE["loginname"])) {
                //user logs in via cookie
                $loginName = $_COOKIE["loginname"];
                $loginPwd = $_COOKIE["password"];
                $remember=True;
                $pwdInMd5 = true;
                $loginInfoAvailable = true;
            } 
        }
            
        //if no login info is available, delegates password checking and internal password checking make no sense and can be skipped;
        if ($loginInfoAvailable) {
            if (getConfigurationSetting("LOGIN_ENABLE_DELEGATED_LOGIN") == 'TRUE') {
                
                // if password checking delegate in use: try to login from password checking delegate
                $result = $this->loginFromPasswordDelegate($loginName,$loginPwd,$remember, $pwdInMd5);
                if ($this->bIsLoggedIn) {
                    return;
                }
                if ($result == 1) {
                    //report error 
                    $this->sNotice = __("Unknown user or wrong password")." (code b3.4).";
                    //don't return, but rather attempt to do the internal and anonymous login later on
                }
                if ($result == 3) { //3 signifies a critical fail after which continued login checking cannot proceed
                    return 1;
                }
            } 
            if (getConfigurationSetting("LOGIN_DISABLE_INTERNAL_LOGIN") != 'TRUE') {
                //Else if login vars available and internal login enabled: login internally from available vars
                $result = $this->internalLogin($loginName,$loginPwd,$remember, $pwdInMd5);
                if ($this->bIsLoggedIn) {
                    return;
                }
                if ($result == 1) {
                    //report error and return
                    $this->sNotice = __("Unknown user or wrong password");
                }
            }
            if ($result == 1) { 
                //apparently user tried to login, and failed both on password checking delegate and internal password checking.
                //fail and return, don't try anon login
                $this->sNotice = __("Unknown user or wrong password")." (code b4.15)";
                return;
            }
        }
        //If anonymous login allowed: login anonymously 
        $result = $this->loginAnonymous();
        if ($this->bIsLoggedIn) {
            return;
        }
        //ah well, after this, the options are exhausted, you're not logged in!
        //no reason to report anything, either.
        return;
    }

    /** THIS FUNCTION IS BEING REPLACED! ASK DENNIS FOR MORE INFO!
     *  Attempts to login as user specified by some external module (e.g. provided by a CMS)
     *  returns one of following:
     *      0 - success
     *      1 - unknown user or wrong password
     *      2 - no relevant login info available */
    function loginFromExternalSystem() {
        
        if (getConfigurationSetting("USE_EXTERNAL_LOGIN") != 'TRUE') {
            return 2;
        }
        $loginName = '';
        $loginGroups = array();
        $CI = &get_instance();
        //depending on the external login settings, choose module and obtain the loginName of the logged user in the external module
        switch (getConfigurationSetting("EXTERNAL_LOGIN_MODULE")) {
            case "Httpauth":
                $CI->load->library('login_httpauth');
                //attempt to get loginname from external system
                $loginInfo = $CI->login_httpauth->getLoginInfo();
                $loginName = $loginInfo['login'];
                $loginGroups = $loginInfo['groups'];
                break;
//            case "LDAP":
//                appendErrorMessage('testing for ldap login...');
//                $CI->load->library('login_ldap');
//                $CI->load->library('authldap');
//                //attempt to get loginname from external system
//                $loginInfo = $CI->login_ldap->getLoginInfo();
//                $loginName = $loginInfo['login'];
//                $loginGroups = $loginInfo['groups'];
//                appendErrorMessage('<br/>LDAP login says: '.$loginName.'<br/>');
//                break;
            //case "drupal":
                //$CI->load->library('login_drupal');
                //attempt to get loginname from external system.
                //this module probably needs some extra info, such as the URL of the DRUPAL site?
                //
                //$loginName = $CI->login_drupal->getLoginName();
                //$loginGroups = $CI->login_drupal->getLoginGroups();
                //break;
        }
        if ($loginName == '') {
            //no login info could be found
            return 1;
        }
        
        //login name was found. Now try to login that person
        $Q = $CI->db->get_where('users',array('login'=>$loginName));
        if ($Q->num_rows()>0) { //user found
            $row = $Q->row();
            //internal account?
            if ($row->type=='normal') {
                $this->sNotice = sprintf(__('The username / password combination is valid according to the login module, but the corresponding Aigaion account is an %s account and cannot be logged in by the %s module. Please contact your database admin for assistance.'),'<i>'.__('internal').'</i>',$delegateLibrary).' (Code b3.52 pwd ok / wrong account).<br/>';
                return 3; //critical fail, no other login should be attempted
            }
            //anonymous account?
            if ($row->type=='anon') {
                $this->sNotice = sprintf(__('The username / password combination is valid according to the login module, but the corresponding Aigaion account is an %s account and cannot be logged in by the %s module. Please contact your database admin for assistance.'),'<i>'.__('anonymous').'</i>',$delegateLibrary).' (Code b3.52 pwd ok / wrong account).<br/>';
                return 3; //critical fail, no other login should be attempted
            }
            //group account?
            if ($row->type=='group') {
                $this->sNotice = sprintf(__('The username / password combination is valid according to the login module, but the corresponding Aigaion account is an %s account and cannot be logged in by the %s module. Please contact your database admin for assistance.'),'<i>'.__('group').'</i>',$delegateLibrary).' (Code b3.52 pwd ok / wrong account).<br/>';
                return 3; //critical fail, no other login should be attempted
            }
            
            $loginPwd = $row->password;
            if ($this->_login($loginName,$loginPwd,False,True,False)==0) { //never remember external login; that's a task for the external module
                //$this->sNotice = 'logged from httpauth';
                //appendErrorMessage('<br/>LDAP login says: known user, logged in');
                return 0; // success
            }
        } 
        //appendErrorMessage('<br/>LDAP login says: unknown user, make?');
        if (getConfigurationSetting("LOGIN_CREATE_MISSING_USER") == 'TRUE') {
            //appendErrorMessage('<br/>LDAP login says: unknown user, make!');
            //no such user found. Make user on the fly. Don't use the user_db class for this, as 
            // we would run into problems with the checkrights performed in user_db->add(...)
            $chars = "abcdefghijkmnopqrstuvwxyz023456789";
            srand((double)microtime()*1000000);
            $i = 0;
            $pass = '' ;
            while ($i <= 7) {
                $num = rand() % 33;
                $tmp = substr($chars, $num, 1);
                $pass = $pass . $tmp;
                $i++;
            }            
            $group_ids = array();
            foreach ($loginGroups as $groupname) {
                $groupQ = $CI->db->get_where('users',array('type'=>'group','abbreviation'=>$groupname));
                if ($groupQ->num_rows()>0) {
                    $R = $groupQ->row();
                    $group_ids[] = $R->user_id;
                } else {
                    //group must also be created...
                    $CI->db->insert("users", array('surname'=>$groupname,'abbreviation'=>$groupname,'type'=>'group'));
                    $new_id = $CI->db->insert_id();
                    //subscribe group to top topic
                    $CI->db->insert('usertopiclink', array('user_id' => $new_id, 'topic_id' => 1)); 
                    $group_ids[] = $new_id;
                }
            }
            //appendErrorMessage('<br/>LDAP login says: now add user...');
            //add user....
            $CI->db->insert("users",     array('initials'           => '',
                                               'firstname'          => '',
                                               'betweenname'        => '',
                                               'surname'            => $loginName,
                                               'email'              => '',
                                               'lastreviewedtopic'  => 1,
                                               'abbreviation'       => '',
                                               'login'              => $loginName,
                                               'password'           => md5($pass),
                                               'password_invalidated'           => 'TRUE',
                                               'type'               => 'external',
                                               'theme'              => 'default',
                                               'summarystyle'       => 'author',
                                               'authordisplaystyle' => 'fvl',
                                               'liststyle'          => '0',
                                               'newwindowforatt'    => 'FALSE',
                                               'exportinbrowser'    => 'TRUE',
                                               'utf8bibtex'         => 'FALSE'
                                               )
                              );
            $new_id = $CI->db->insert_id();
            //add group links, and rightsprofiles for these groups, to the user
            foreach ($group_ids as $group_id) {
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
            //subscribe new user to top topic
            $CI->db->insert('usertopiclink', array('user_id' => $new_id, 'topic_id' => 1)); 
            //after adding the new user, log in as that new user
            if ($this->_login($loginName,md5($pass),False, True, False)==0) { //never remember external login; that's a task for the external module
                //$this->sNotice = 'logged from httpauth';
                appendMessage(sprintf(__('Created missing user %s  as member of groups %s'),$loginName,implode(',',$loginGroups)));
                return 0; // success
            } else {
                echo "Serious error: a new user was created and could not be logged in. ".md5($pass)." ";die();
            }
        } else {
            return 1;
        }
        return 2;
    }
        
    /** Attempts to login as user using a password checking delegate
     *  returns one of following:
     *      0 - success
     *      1 - unknown user or wrong password 
     *      3 - critical fail, no other login should be attempted    */
    function loginFromPasswordDelegate($loginName,$loginPwd,$remember,$pwdInMd5) {
        $CI = &get_instance();
        $CI->load->library('Passwordchecker');
        $delegates = explode (',',getConfigurationSetting("LOGIN_DELEGATES"));
        foreach ($delegates as $delegate) {
            //determine next delegate
            $delegateLibrary = 'passwordchecker_'.$delegate;
            $CI->load->library($delegateLibrary);
            //check password
            $loginInfo = $CI->$delegateLibrary->checkPassword($loginName, $loginPwd,$pwdInMd5);
            //OK: possibly create account; login; return 0
            if (isset($loginInfo['uname']) && ($loginInfo['uname'] != null) && ($loginInfo['uname']!= '')) {
                //password was OK
                $Q = $CI->db->get_where('users',array('login'=>$loginInfo['uname']));
                if ($Q->num_rows()==0) {
                    //pwd was OK but account did not exist. Create and try again?
                    if (getConfigurationSetting("LOGIN_CREATE_MISSING_USER")=='TRUE') {
                        //create user
                        //no such user found. Make user on the fly. Don't use the user_db class for this, as 
                        // we would run into problems with the checkrights performed in user_db->add(...)
                        $chars = "abcdefghijkmnopqrstuvwxyz023456789";
                        srand((double)microtime()*1000000);
                        $i = 0;
                        $pass = '' ;
                        while ($i <= 7) {
                            $num = rand() % 33;
                            $tmp = substr($chars, $num, 1);
                            $pass = $pass . $tmp;
                            $i++;
                        }
                        
                        //add user.... to database
                        $CI->db->insert("users",     array('initials'           => '',
                                                           'firstname'          => '',
                                                           'betweenname'        => '',
                                                           'surname'            => $loginName,
                                                           'email'              => '',
                                                           'lastreviewedtopic'  => 1,
                                                           'abbreviation'       => '',
                                                           'login'              => $loginName,
                                                           'password'           => md5($pass),
                                                           'password_invalidated'           => 'TRUE',
                                                           'type'               => 'external',
                                                           'theme'              => 'default',
                                                           'summarystyle'       => 'author',
                                                           'authordisplaystyle' => 'fvl',
                                                           'liststyle'          => '0',
                                                           'newwindowforatt'    => 'FALSE',
                                                           'exportinbrowser'    => 'TRUE',
                                                           'utf8bibtex'         => 'FALSE'
                                                           )
                                          );
                        $new_id = $CI->db->insert_id();
                        //subscribe new user to top topic
                        $CI->db->insert('usertopiclink', array('user_id' => $new_id, 'topic_id' => 1)); 
                        //after adding the new user, log in as that new user                        
                        //get user again, so we can continue loggin in
                        $Q = $CI->db->get_where('users',array('login'=>$loginInfo['uname']));
                        //login
                        if ($Q->num_rows()>0) {
                            $row = $Q->row();
                            $loginName = $row->login;
                            $loginPwd = $row->password;
                            if ($this->_login($loginName,$loginPwd,$remember,True,False)==0) { 
                                //set some message 'new Aigaion account created, please feel welcome'
                                appendMessage("<p>".sprintf(__("A new Aigaion account has been created for you, user '%s'. Please enjoy your stay here."),$loginInfo['uname'])."<br/>");
                                return 0; // success
                            }
                        } 
                        //At this point,  we should have been logged in
                        //if we end up here, apparently something went major wrong (pwd was ok, accou nt did not exist, it should have been created and logged in)
                        //this really should not happen :(
                        $this->sNotice= __('Serious error in login tables');
                        return 3;
                    }
                }
                //password was OK, try to find Aigaion account. Must be external.
                if ($Q->num_rows()>0) {
                    $row = $Q->row();
                    //internal account?
                    if ($row->type=='normal') {
                        $this->sNotice = sprintf(__('The username / password combination is valid according to %s, but the corresponding Aigaion account is an %s account and cannot be logged in by the %s module. Please contact your database admin for assistance.'),'<i>'.__('internal').'</i>',$delegateLibrary,$delegateLibrary).' (Code b3.52 pwd ok / wrong account).<br/>';
                        return 3; //critical fail, no other login should be attempted
                    }
                    //anonymous account?
                    if ($row->type=='anon') {
                        $this->sNotice = sprintf(__('The username / password combination is valid according to %s, but the corresponding Aigaion account is an %s account and cannot be logged in by the %s module. Please contact your database admin for assistance.'),'<i>'.__('anonymous').'</i>',$delegateLibrary,$delegateLibrary).' (Code b3.52 pwd ok / wrong account).<br/>';
                        return 3; //critical fail, no other login should be attempted
                    }
                    //group account?
                    if ($row->type=='group') {
                        $this->sNotice = sprintf(__('The username / password combination is valid according to %s, but the corresponding Aigaion account is an %s account and cannot be logged in by the %s module. Please contact your database admin for assistance.'),'<i>'.__('group').'</i>',$delegateLibrary,$delegateLibrary).' (Code b3.52 pwd ok / wrong account).<br/>';
                        return 3; //critical fail, no other login should be attempted
                    }
                    //yay! type is external! --> do the login, finally!
                    $loginName = $row->login;
                    $loginPwd = $row->password;
                    if ($this->_login($loginName,$loginPwd,$remember,True,False)==0) { 
                        return 0; // success
                    }
                } else {
                    $this->sNotice = __('The username / password combination is valid, but has no Aigaion account associated with it yet. Please contact your database admin for assistance').' (Code b3.67 pwd ok / no account).<br/>';
                    return 3; //critical fail, no other login should be attempted
                }
                //else $result was 1; try next delegate...
            }
        }
        //no delegate was successfull? return 1 for fail
        return 1;
        
        
    }
    
    /** Attempts to login as user using the internal password checking mechanism
     *  returns one of following:
     *      0 - success
     *      1 - unknown user or wrong password     */
    function internalLogin($loginName,$loginPwd,$remember, $pwdInMd5) {
        //check whether that login is internal or not...
        return $this->_login($loginName,$loginPwd,$remember, $pwdInMd5,True);
    }
    
    /** Attempts to login as the given anonymous user
     *  returns one of following:
     *      0 - success
     *      1 - unknown user or wrong password (no or incorrect anonymous account defined)
     *      2 - no login info available */
    function loginAnonymous($user_id = -1) {
        $CI = &get_instance();
        if (getConfigurationSetting("LOGIN_ENABLE_ANON")!="TRUE") {
            return 1; //no anon accounts allowed
        }
        if ($user_id==-1) {
            $user_id = getConfigurationSetting("LOGIN_DEFAULT_ANON");
        }
        $Q = $CI->db->get_where('users',array('user_id'=>$user_id,'type'=>'anon'));
        if ($Q->num_rows()>0) {
            $row = $Q->row();
            $loginName = $row->login;
            $loginPwd = $row->password;
            if ($this->_login($loginName,$loginPwd,False,True,False)==0) { //never remember anon login :)
                $this->bIsAnonymous=True;
                return 0; // success
            }
        } else {
            $this->sNotice = __('Anonymous (guest) access to this database has been enabled. However, no default anonymous account has been assigned, so anonymous access is unfortunately not yet possible.').'<br/>';
        }
        return 1; //no or incorrect anonymous account defined
    }
    
    /** Attempts to login as the given user. Called by the other login methods.
     *  returns one of following:
     *      0 - success
     *      1 - unknown user or wrong password 
     $internal is true iff this account is an attempt to log in as a 'normal' internal account. These accounts can not login if it has password_invalidated set, because it was anon or external*/
    function _login($userName, $pwdHash, $remember, $pwdInMd5 = False, $internal = False) {
        $CI = &get_instance();
        //md5 pwd if it was not already done
        if (!$pwdInMd5) $pwdHash = md5($pwdHash);
        //check username / password in user-table
        $Q = $CI->db->get_where('users',array('login'=>$userName));
        if ($Q->num_rows()<=0) {
            return 1; //no such user error
        }
        $R = $Q->row();
        $failForPwdInvalidated = False; 
        
        if (isset($R->password_invalidated)) {//necessary because older versions of database do not have this column
            $failForPwdInvalidated  = $internal && ($R->password_invalidated=='TRUE'); 
        }
        if (($pwdHash != $R->password) || ($failForPwdInvalidated )) {
            //($internal && ($R->password_invalidated=='TRUE')) but password OK?
            //Then someone tried to login through the internal login mechanism using an account 
            //that is anon or external or internal_disabled.
            //There is a strong reason why we don't give an error message when a password_invalidated
            //account is logged in here as internal: 
            //it would give someone the opportunity to fish for login names!

            //So, anyhow, this was not a successful login: reset all class vars, return error
            //reset class vars
            $this->bIsLoggedIn = False; 
            $this->bIsAnonymous = False;
            $this->sLoginName = "";
            $this->iUserId = "";                
            $CI->latesession->set('USERLOGIN', $this);
            return 1; //user/password error
        } else {
            clearErrorMessage();
            clearMessage();
            //successful login: perform login, store cookies, etc
            //check if people changed the default account!
            if ($userName == "admin") {
                if ($pwdHash == md5("admin")) {
                    appendErrorMessage(__("Your admin account password still has the default value, please change it on the 'profile' page.")."<br/>");
                }
            }

            //login OK
            $this->bIsLoggedIn = True;
            $this->sLoginName = $R->login;
            $this->iUserId = $R->user_id;       
            $this->bIsAnonymous = False;
            $this->bJustLoggedOut = False;  
            
            
            //create the User object for this logged user
            $CI = &get_instance();
            $this->theUser = $CI->user_db->getByID($this->iUserId);

            //make sure that the anonymous user is ALWAYS logged in as anonymous user
            if (   (getConfigurationSetting("LOGIN_ENABLE_ANON")=="TRUE")
                && ($this->theUser->type=='anon')
                ) {
                $this->bIsAnonymous=True;
            }

            //store cookies after login was checked
            if ($remember)
            {
                setcookie("loginname", $R->login   ,(3*24*60*60)+time(), '/');
                setcookie("password",  $R->password,(3*24*60*60)+time(), '/');
            }

            #init rights and preferences
            $this->initRights();
            

            #set a welcome message/advertisement after login
            if ($this->bIsAnonymous) {
              appendMessage(sprintf(__("Dear guest, welcome to this publication database. As an anonymous user, you will probably not have edit rights. Also, the collapse status of the topic tree will not be persistent. If you like to have these and other options enabled, you might ask %s for a login account."),"<a href='mailto: \"".getConfigurationSetting("CFG_ADMIN")."\" ".'<'.getConfigurationSetting("CFG_ADMINMAIL").'>'."?subject=Access account for ".getConfigurationSetting("WINDOW_TITLE")." Aigaion database'>".getConfigurationSetting("CFG_ADMIN")."</a>"));
            }
            appendMessage("<table>\n<tr>
                          <td>"
                          .__("This site is powered by Aigaion - A PHP/Web based management system for shared and annotated bibliographies.")
                          ." "
                          .sprintf(__("For more information visit %s"),"<a href='http://www.aigaion.de/' class='open_extern'>www.aigaion.de</a>.")
                          ."</td>
                          <td>
                          <a href='http://sourceforge.net/projects/aigaion' class='open_extern'>
                                <img src='http://sflogo.sourceforge.net/sflogo.php?group_id=109910&type=12' 
                                     width='120' 
                                     height='30' 
                                     border='0'
                                     alt='Get Web based bibliography management system at SourceForge.net. Fast, secure and Free Open Source software downloads' />
                          </a>
                          </td></tr>\n</table>
              ");
            

            #SO. Here, if login was successful, we will check the database structure once.
            $this->initPreferences();
            $CI->latesession->set('USERLOGIN', $this);
            if (!checkSchema()) { //checkSchema will also attempt to login...
                
              log_message('error', 'checkSchema failed after login');
              
              $this->logout();
              $this->sNotice = __("You have been logged out because the Aigaion Engine is in the process of being updated.")
                               ."<br/>"
                               .__("If you are a user with database_manage rights, please login to complete the update.")
                               ."<br/>";
              return 2;
            }
            
            #once every day (i.e. depending on when last up-to-date-check was performed), for
            #database_manage users, an up-to-date-check is performed
            #do this AFTER possible updating of the database ;-)
            if (!$this->bIsAnonymous && $this->hasRights('database_manage') && ($this->theUser->lastupdatecheck+48*60*60 < time())) {
                $CI->load->helper('checkupdates');
	            $checkresult = __("Checking for updates...");
	            $updateinfo = checkUpdates();
	            if ($updateinfo == '') {
    		        $checkresult .= '<b>'.__('OK').'</b><br/>';
        			$checkresult .= __('This installation of Aigaion is up-to-date');
	            } else {
        			$checkresult .= '<span class="errortext">'.utf8_strtoupper(__('Alert')).'</span><br/>';
        			$checkresult .= $updateinfo;
    	        }
    	        appendMessage($checkresult);
                $CI->db->update('users',array('lastupdatecheck'=>time()),array('user_id'=>$this->iUserId));
            }
            $this->sNotice = ''; //clean up irrelevant notices; login was successfull
            return 0;
        } 
    }

    /** Logout any active session. */
    function logout() {
        //session_destroy();
        //reset class vars
        $this->bIsLoggedIn = False; 
        $this->bIsAnonymous = False;
        $this->sLoginName = "";
        $this->iUserId = "";
        $this->rights = array();
        $this->preferences = array();
        $this->bJustLoggedOut = True;
        $this->checkToken = False;
        $this->loginToken = '';
        //Delete cookie values
        setcookie("loginname",FALSE,0,'/');
        setcookie("password",FALSE,0,'/');
        $CI = &get_instance();
        $CI->latesession->set('USERLOGIN', $this);
    }

}