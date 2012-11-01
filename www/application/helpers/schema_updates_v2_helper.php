<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for updating the database schema for Aigaion 2.x database versions.
| -------------------------------------------------------------------
|
|   Contains a cascaded set of database update methods. Every new update script should check older versions
|   before continuing.
|
|	Usage:
|       $this->load->helper('schema_updates_v2'); //load this helper
|       $success = updateSchemaV2_x(); 
|           call the 'latest' schema update number 2.x (with x depending on what the latest update is....)
|
|   Implementation:
|       See also the schema_helper (!)
|       Note to developers: DOCUMENT YOUR DATABASE UPDATE CODE
|       
|       
*/

  function updateSchemaV2_50() {
    
    // check if schema version already set
    if (checkVersion('V2.50', true)) {
      log_message('debug', 'already at schema version 2.50');
      return true;
    }

    // execute older updates first
    if (!updateSchemaV2_23()) {
      log_message('error', 'could not update to schema version 2.23');      
      return false;
    }

    /*
     * BEGIN schema updates
     */
    
    mysql_query("ALTER TABLE `". AIGAION_DB_PREFIX ."author`
    	DROP PRIMARY KEY,
    	ADD PRIMARY KEY (`author_id`) USING HASH,
    	ADD INDEX `author_cleanname` (`cleanname`) USING BTREE,
    	ADD INDEX `author_synonym_of` (`synonym_of`) USING HASH;");
    
    if ($err = mysql_error()) {
      log_message('error', "could not add author indexes ($err)");
      return False;
    }
    
    mysql_query("ALTER TABLE `". AIGAION_DB_PREFIX ."publication`
			DROP PRIMARY KEY,
			ADD PRIMARY KEY (`pub_id`) USING HASH,
    	ADD INDEX `publication_derived_read_access_level` (`derived_read_access_level`) USING HASH,
    	ADD INDEX `publication_group_id` (`group_id`) USING HASH,
    	ADD INDEX `publication_user_id` (`user_id`) USING HASH,
    	ADD INDEX `publication_title` (`title`(255)) USING BTREE,
    	ADD INDEX `publication_pub_type` (`pub_type`) USING HASH,
    	ADD INDEX `publication_cleantitle` (`cleantitle`) USING BTREE;");
    
    if ($err = mysql_error()) {
      log_message('error', "could not add publication indexes ($err)");
      return False;
    }
    
    mysql_query("ALTER TABLE `". AIGAION_DB_PREFIX ."publicationauthorlink`
    	DROP INDEX `pub_id`,
      DROP PRIMARY KEY,
      ADD PRIMARY KEY (`pub_id`, `author_id`, `is_editor`) USING HASH,
      ADD INDEX `pal_author_id` (`author_id`) USING HASH;");
    
    if ($err = mysql_error()) {
      log_message('error', "could not add publicationauthorlink indexes ($err)");
      return False;
    }
    
    mysql_query("ALTER TABLE `". AIGAION_DB_PREFIX ."userpublicationmark`
			ADD INDEX `pub_id_mark` (`pub_id`, `mark`) USING HASH;");
    
    if ($err = mysql_error()) {
      log_message('error', "could not add userpublicationmark indexes ($err)");
      return False;
    }
    
    /*
     * END schema updates
     */
        
    // set release version
    if (!setReleaseVersion('2.5', 'bugfix,features', "
    	- new CodeIgniter version 
    	- added db indexes
		")) { 
      log_message('error', 'could not setReleaseVersion');
      return false;
    }
    
    // set major version
    return setVersion('V2.50');    
  }

    /** 
    The first proper multilang release!
    */
    function updateSchemaV2_23() {
        if (checkVersion('V2.23', true)) {
            return True;
        }
        if (!updateSchemaV2_22()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        if (!setReleaseVersion('2.2','bugfix,features,layout',"
Introduction of custom fields for authors, topics and publications
Introduction of author aliases
New languages
New CodeIgniter version 
Customizable publication summary
        ")) 
            return False;
        
        return setVersion('V2.23');
    }

    /** 
    / * Add field for cover image in publication table
    */
    
    function updateSchemaV2_22() {
      $CI = &get_instance();
        if (checkVersion('V2.22', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_21()) { //FIRST CHECK OLDER VERSION
            return False;
        }
             
        //insert type_id column
        mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."publication` 
                      ADD `coverimage` varchar(255) NOT NULL default '';");

        if (mysql_error()) 
            return False;
        
        return setVersion('V2.22');
    }
    /** 
    / * Add customfieldInfo table
    */
    
    function updateSchemaV2_21() {
      $CI = &get_instance();
        if (checkVersion('V2.21', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_20()) { //FIRST CHECK OLDER VERSION
            return False;
        }
             
        //add customfieldsinfo table
        mysql_query("CREATE TABLE `".AIGAION_DB_PREFIX."customfieldsinfo` 
                        (  `type_id` int(10) unsigned NOT NULL auto_increment,
                        `type` enum('publication','author','topic') NOT NULL default 'publication',
                        `order` int(10) unsigned NOT NULL default '0',
                        `name` VARCHAR(255) NOT NULL default '',
                        PRIMARY KEY  (`type_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        
        if (mysql_error()) 
            return False;
            
        //drop "type" column of customfields table since it is successed by customfiledsinfo table
        mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."customfields` 
                      DROP COLUMN `type`;");
                      
        if (mysql_error()) 
            return False;

        //insert type_id column
        mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."customfields` 
                      ADD `type_id` int(10) unsigned NOT NULL AFTER `entry_id`;");

        if (mysql_error()) 
            return False;
        
        return setVersion('V2.21');
    }

    /** 
    / * Add 'publicaton status' column to publication table.
    / * Add customfields table
    / * Add author "synonym of" column for enabling multiple instances of the same author.
    */
    function updateSchemaV2_20() {
        $CI = &get_instance();
        if (checkVersion('V2.20', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_19()) { //FIRST CHECK OLDER VERSION
            return False;
        }
             
        //insert publicationstatus field to the publiation table
        mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."publication` 
                      ADD COLUMN `status` varchar(255) NOT NULL default '';");

        if (mysql_error()) 
            return False;

        //add customfields table
        mysql_query("CREATE TABLE `".AIGAION_DB_PREFIX."customfields` 
                        (  `entry_id` int(10) unsigned NOT NULL auto_increment,  
                        `type` enum('publication','author','topic') NOT NULL default 'publication', 
                        `object_id` int(10) unsigned NOT NULL,
                        `value` TEXT NOT NULL,  PRIMARY KEY  (`entry_id`)) ENGINE=MyISAM CHARACTER SET utf8;");
        
        if (mysql_error()) 
            return False;
            
        //add "synonym of" column
        mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."author` 
                      ADD COLUMN `synonym_of` int(10) unsigned NOT NULL default '0';");

        if (mysql_error()) 
            return False;
        
        return setVersion('V2.20');
    }


    /** 
    The first proper multilang release!
    */
    function updateSchemaV2_19() {
        if (checkVersion('V2.19', true)) {
            return True;
        }
        if (!updateSchemaV2_18()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        if (!setReleaseVersion('2.1.2','bugfix,features,layout',"
==================
MAJOR IMPROVEMENTS
==================
Backup restore function is back  -- It is now possible again to restore
old backups by entering the originally exported backup data into the
restore function from the maintenance page.
===
Performance improvement -- several libraries have been improved to
optimize the database access and speed up paginated display of large
lists of publications.
===
Internationalisation -- Aigaion now officialy has multi-language
support. Four languages are immediately available in this release,
thanks to the hard work of Manuel Strehl, and the translators mentioned 
on the translation wiki: Norwegian, German, English and Dutch.
==================
MINOR IMPROVEMENTS
==================
Keyword clouds -- Topic and author pages now show keyword clouds, with different
font sizes for the keywords depending on how important they are in the
publications under a certain topic or for a certain author.
===
BibTeX to UTF8 conversion -- We have extended and improved the
BibTeX2UTF8 conversions (and the other way around). In addition, we have
written a set of testing functions to spot recurring bugs easier. 
This was supported by heavy testing from Peter Mosses.
===
Import: better feedback and input checking -- The input data entered in
the import screen is tested more thoroughly for certain problems, and if
the import fails, a better feedback message is given.
===
TinyMCE editor for editing notes -- It is now possible to edit notes
using the great editor TinyMCE, which allows a WYSIWYG basic formatting
interface. To enable it, check the box in the configuration screen.
===
IE display cleanup -- Many display issues in Internet Explorer were fixed
===
Documentation -- We have extended the in-code documentation and the
documentation on the Wiki pages
===
New CodeIgniter version -- Thanks to the help of Dennis Allerkamp,
Aigaion now runs on CodeIgniter version 1.7
==================
        ")) 
            return False;
        
        return setVersion('V2.19');
    }

    /** 
    Add 'clean keyword' column to keywords table, to facilitate searching of accented letters
    */
    function updateSchemaV2_18() {
        $CI = &get_instance();
        if (checkVersion('V2.18', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_17()) { //FIRST CHECK OLDER VERSION
            return False;
        }
             
        mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."keywords` 
                      ADD COLUMN `cleankeyword` 
                                 TEXT NOT NULL;");

        
        if (mysql_error()) 
            return False;

        $Q = $CI->db->get('keywords');
        $CI->load->helper('utf8_to_ascii');
        foreach ($Q->result() as $row) { 
            $cleankeyword =  utf8_to_ascii($row->keyword);
            $CI->db->update('keywords',array('cleankeyword'=>$cleankeyword),array('keyword_id'=>$row->keyword_id));
        }
        
        
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.18');
    }
       

    /** 
    Conversion of userlanguage setting to the i18n codes
    */
    function updateSchemaV2_17() {
        if (checkVersion('V2.17', true)) {
            return True;
        }
        if (!updateSchemaV2_16()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        
        $CI = &get_instance();
        $conversions = array("english"=>"en","nederlands"=>"nl","francais"=>"fr","deutsch"=>"de","polski"=>"pl");
        foreach ($conversions as $from=>$to)
        {
          $CI->db->update('users',array('language'=>$to),array('language'=>$from));
          $CI->db->update('config',array('value'=>$to),array('setting'=>'DEFAULTPREF_LANGUAGE','value'=>$from));
        }
        
        return setVersion('V2.17');
    }
        

    /** 
    intermediate release with some bug fixes
    */
    function updateSchemaV2_16() {
        if (checkVersion('V2.16', true)) {
            return True;
        }
        if (!updateSchemaV2_15()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        if (!setReleaseVersion('2.1.1','bugfix',"
        Aigaion 2.1.1 is a bug fix release.
        ")) 
            return False;
        
        return setVersion('V2.16');
    }
        

    /** 
    Extend size of topic name field
    */
    function updateSchemaV2_15() {
        $CI = &get_instance();
        if (checkVersion('V2.15', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_14()) { //FIRST CHECK OLDER VERSION
            return False;
        }
       
        //extend fields in user table
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."topics` 
                                  CHANGE `name` `name`  varchar(255);");
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."topics` 
                                  CHANGE `cleanname` `cleanname`  varchar(255);");
                                  
       
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.15');
    }
    
    /** 
    Introduce Logintegration table
    */
    function updateSchemaV2_14() {
        if (checkVersion('V2.14', true)) {
            return True;
        }
        if (!updateSchemaV2_13()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        
        $CI = &get_instance();
        $CI->db->query("CREATE TABLE `".AIGAION_DB_PREFIX."logintegration` 
                               (  `token` varchar(30) NOT NULL default '',  
                                  `time` INT NOT NULL default 0,  
                                  `serial` INT NOT NULL default 0, 
                                  `keepchecking` enum('TRUE','FALSE') NOT NULL default 'FALSE', 
                                  `status` enum('active','loggedout','loggedin') NOT NULL default 'active', 
                                  `sitename` varchar(255) NOT NULL default '',  
                                  PRIMARY KEY  (`token`)
                                ) ENGINE=MyISAM CHARACTER SET utf8;");
        
        
        return setVersion('V2.14');
    }
    
    /** 
    */
    function updateSchemaV2_13() {
        if (checkVersion('V2.13', true)) {
            return True;
        }
        if (!updateSchemaV2_12()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        if (!setReleaseVersion('2.1.0','bugfix,features,layout,security',"
        Aigaion 2.1 is the first non-beta version of Aigaion.
        
        Besides solving a few security issues, it contains a multitude of new features and existing features have been improved in many ways.
        Some examples are:
        - export via email
        - major improvements in the bibtex import facilities
        - new handling of bibtex special characters, allowing many new characters and making it very simple to add new characters in the future
        - an improved and more stable login module
        - advanced search capabilities
        
        But there are also countless other improvements both small and large!
         
        Many thanks to our users who contributed ideas, code and extensive testing sessions!
        ")) 
            return False;
        
        return setVersion('V2.13');
    }
    
    /** 
    Reshape the month field into a free string, by replacing the numbers (now 0..12) with bibtex abbrevs in the new format -- 3letter abbrev enclosed in double quotes
    */
    function updateSchemaV2_12() {
        $CI = &get_instance();
        if (checkVersion('V2.12', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_11()) { //FIRST CHECK OLDER VERSION
            return False;
        }
         
        $CI->db->update('publication',array('month'=>''),array('month'=>'0'));
        $CI->db->update('publication',array('month'=>'"jan"'),array('month'=>'1'));
        $CI->db->update('publication',array('month'=>'"feb"'),array('month'=>'2'));
        $CI->db->update('publication',array('month'=>'"mar"'),array('month'=>'3'));
        $CI->db->update('publication',array('month'=>'"apr"'),array('month'=>'4'));
        $CI->db->update('publication',array('month'=>'"may"'),array('month'=>'5'));
        $CI->db->update('publication',array('month'=>'"jun"'),array('month'=>'6'));
        $CI->db->update('publication',array('month'=>'"jul"'),array('month'=>'7'));
        $CI->db->update('publication',array('month'=>'"aug"'),array('month'=>'8'));
        $CI->db->update('publication',array('month'=>'"sep"'),array('month'=>'9'));
        $CI->db->update('publication',array('month'=>'"oct"'),array('month'=>'10'));
        $CI->db->update('publication',array('month'=>'"nov"'),array('month'=>'11'));
        $CI->db->update('publication',array('month'=>'"dec"'),array('month'=>'12'));
            
            
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.12');
    }
    
    /** 
    Extend length of a few more fields in the publication table
    */
    function updateSchemaV2_11() {
        $CI = &get_instance();
        if (checkVersion('V2.11', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_10()) { //FIRST CHECK OLDER VERSION
            return False;
        }
             
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."publication` 
                                  CHANGE `year` `year`  varchar(127) NOT NULL DEFAULT '';");
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."publication` 
                                  CHANGE `actualyear` `actualyear`  varchar(127) NOT NULL DEFAULT '';");
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."publication` 
                                  CHANGE `series` `series`  varchar(127) NOT NULL DEFAULT '';");
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."publication` 
                                  CHANGE `volume` `volume`  varchar(127) NOT NULL DEFAULT '';");
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."publication` 
                                  CHANGE `chapter` `chapter`  varchar(127) NOT NULL DEFAULT '';");
        
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.11');
    }
    
    /** 
    Add preference for how to calculate similar author distances
    */
    function updateSchemaV2_10() {
        $CI = &get_instance();
        if (checkVersion('V2.10', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_9()) { //FIRST CHECK OLDER VERSION
            return False;
        }
             
        mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."users` 
                      ADD COLUMN `similar_author_test` 
                                 VARCHAR(20) NOT NULL DEFAULT 'default';");
        $CI->db->insert('config',array('setting'=>'DEFAULTPREF_SIMILAR_AUTHOR_TEST','value'=>'c'));
        
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.10');
    }
       

    /** 
    Add some userrights for email export and for requesting copies of a paper from the authors
    */
    function updateSchemaV2_9() {
        $CI = &get_instance();
        if (checkVersion('V2.9', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_8()) { //FIRST CHECK OLDER VERSION
            return False;
        }
       
        //add 'jr' column for authors
        $res = mysql_query("INSERT INTO `".AIGAION_DB_PREFIX."availablerights` 
                                        (`name`,`description`) 
                                 VALUES ('export_email','export publications through email'),
                                        ('request_copies','request copies of a publication from the author');");
                                  
       
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.9');
    }
    
    /** 
    Add jr-part for authors
    */
    function updateSchemaV2_8() {
        $CI = &get_instance();
        if (checkVersion('V2.8', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_7()) { //FIRST CHECK OLDER VERSION
            return False;
        }
       
        //add 'jr' column for authors
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."author` 
                                  ADD `jr` varchar(255) default '';");
                                  
       
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.8');
    }
    
    /** 
    Extend size of fields in user table
    */
    function updateSchemaV2_7() {
        $CI = &get_instance();
        if (checkVersion('V2.7', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_6()) { //FIRST CHECK OLDER VERSION
            return False;
        }
       
        //extend fields in user table
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."users` 
                                  CHANGE `firstname` `firstname`  varchar(255);");
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."users` 
                                  CHANGE `surname` `surname`  varchar(255);");
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."users` 
                                  CHANGE `email` `email`  varchar(255);");
                                  
       
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.7');
    }
 
    /** 
    CHange 'pages' field -- instead of first and last page we now simply have a string field allowing all formats
    (e.g., multiple ranges)
    */
    function updateSchemaV2_6() {
        $CI = &get_instance();
        if (checkVersion('V2.6', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_5()) { //FIRST CHECK OLDER VERSION
            return False;
        }
       
        //add 'pages' column.
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."publication` 
                                  ADD `pages` VARCHAR(255)
                                  NOT NULL 
                                  default '';");
                                  
        $Q = $CI->db->get('publication');
        
        foreach ($Q->result() as $R) {
            $pages = "";
            if (($R->firstpage != "0") || ($R->lastpage != "0")) {
            	if ($R->firstpage != "0") {
            		$pages = $R->firstpage;
            	}
            	if (($R->firstpage != $R->lastpage)&& (trim($R->lastpage) != "0")&& (trim($R->lastpage) != "")) {
            		if ($pages != "") {
            			$pages .= "--";
            		}
            		$pages .= $R->lastpage;
            	}
            }
            $R->pages = $pages;
            $CI->db->update('publication',$R,array('pub_id'=>$R->pub_id));
            
        }
        
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.6');
    }

 
    /** 
    add account capabilities: external accounts, disabled accounts, and several new or transformed config settings related to login.
    Mostly, see userlogin.php for how this is used.
    */
    function updateSchemaV2_5() {
        $CI = &get_instance();
        if (checkVersion('V2.5', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_4()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        //authordisplaystyle gets extra option 'default' 
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."users` 
                                  CHANGE `authordisplaystyle` `authordisplaystyle`  varchar(255) NOT NULL default 'vlf'");
                                  //note: can it be that MODIFY COLUMN needs to be CHANGE for some MYSQL versions? :(

        //account types is extended with 'external'
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."users` 
                                  CHANGE `type` `type` enum('group','anon','normal','external') NOT NULL default 'normal'");
                                  //note: can it be that MODIFY COLUMN needs to be CHANGE for some MYSQL versions? :(

        //password_invalidated (TRUE|FALSE) column for user table
        $res = mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."users` 
                                  ADD `password_invalidated` enum('TRUE','FALSE') 
                                  NOT NULL 
                                  default 'FALSE' 
                                  AFTER `theme`");
       
        //if aigaion was configured to use external login, set all 'normal' accounts to be 'external'
        if (getConfigurationSetting('USE_EXTERNAL_LOGIN')=='TRUE') {
          $CI->db->update('users', array('type'=>'external'),array('type'=>'normal'));
        }
        
        //all anonymous accounts are 'password_invalidated'
        $CI->db->update('users', array('password_invalidated'=>'TRUE'),array('type'=>'anon'));
        
        //change names of some config settings. Set setting name to X where setting name was Y
        //$CI->db->update('config', array('setting'=>'X'),array('setting'=>'Y'));
        $CI->db->update('config', array('setting'=>'LOGIN_ENABLE_ANON'),array('setting'=>'ENABLE_ANON_ACCESS'));
        $CI->db->update('config', array('setting'=>'LOGIN_DEFAULT_ANON'),array('setting'=>'ANONYMOUS_USER'));
        $CI->db->update('config', array('setting'=>'LOGIN_CREATE_MISSING_USER'),array('setting'=>'CREATE_MISSING_USERS'));

        //new config settings introduced 
        $CI->db->insert('config',array('setting'=>'LOGIN_ENABLE_DELEGATED_LOGIN','value'=>'FALSE'));
        $CI->db->insert('config',array('setting'=>'LOGIN_DELEGATES','value'=>''));
        $CI->db->insert('config',array('setting'=>'LOGIN_DISABLE_INTERNAL_LOGIN','value'=>'FALSE'));
        $CI->db->insert('config',array('setting'=>'LOGIN_MANAGE_GROUPS_THROUGH_EXTERNAL_MODULE','value'=>'FALSE'));

        
        
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.5');
    }


    /** 
    Note: add default preferences
    */
    function updateSchemaV2_4() {
        $CI = &get_instance();
        if (checkVersion('V2.4', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_3()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        $CI->db->insert('config',array('setting'=>'DEFAULTPREF_THEME','value'=>'default'));
        $CI->db->insert('config',array('setting'=>'DEFAULTPREF_LANGUAGE','value'=>'english'));
        $CI->db->insert('config',array('setting'=>'DEFAULTPREF_SUMMARYSTYLE','value'=>'author'));
        $CI->db->insert('config',array('setting'=>'DEFAULTPREF_AUTHORDISPLAYSTYLE','value'=>'fvl'));
        $CI->db->insert('config',array('setting'=>'DEFAULTPREF_LISTSTYLE','value'=>'50'));
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.4');
    }

    /** 
    Note: add language preference
    */
    function updateSchemaV2_3() {
        if (checkVersion('V2.3', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_2()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        //ATTEMPT TO RUN DATABASE UPDATING CODE FOR THIS VERSION... if fail, rollback?
        mysql_query("ALTER TABLE `".AIGAION_DB_PREFIX."users` ADD COLUMN `language` VARCHAR(20) NOT NULL DEFAULT 'english';");
        if (mysql_error()) 
            return False;
        
        return setVersion('V2.3');
    }
    
    /** 
    Note: set release to 2.0.2.beta
    */
    function updateSchemaV2_2() {
        if (checkVersion('V2.2', true)) { // silent check
            return True;
        }
        if (!updateSchemaV2_1()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        //ATTEMPT TO RUN DATABASE UPDATING CODE FOR THIS VERSION... if fail, rollback?
        if (!setReleaseVersion('2.0.2.beta','bugfix','Many bug fixes.')) 
            return False;
        
        return setVersion('V2.2');
    }
    
    /** 
    Initial schema update, bugfixes and install scripts
    */
    function updateSchemaV2_1() {
        if (checkVersion('V2.1', true)) {
            return True;
        }
        if (!updateSchemaV2_0()) { //FIRST CHECK OLDER VERSION
            return False;
        }
        //ATTEMPT TO RUN DATABASE UPDATING CODE FOR THIS VERSION... if fail, rollback?
        if (!setReleaseVersion('2.0.1.beta','bugfix,features','The first bug reports on the beta release are fixed. Furthermore, automated install scripts have been added to the release.')) 
            return False;
        
        return setVersion('V2.1');
    }
    
    /** 
    Note: this is the first schema check for Aigaion 2.0
    
    In contrast to higher schema check versions, this schema check does NOT execute any
    database modifying code. It only checks whether the database is currently version 2.0...
    And if not, it gives a warning message that you should run the Aigaion 1.x => 2.0 
    migration scripts.
    
    This is because we decided to keep all code transforming an 
    Aigaion 1.x database into an Aigaion 2.0 database out of these files: that transformation
    is done in an update/install/migration script for Aigaion 2.0 that is not part of the main 
    Aigaion 2 engine.
    */
    function updateSchemaV2_0() {
        $CI = &get_instance();
        $Q = $CI->db->get('aigaiongeneral');
        if ($Q->num_rows()>0) {
            $R = $Q->row();
            $version = $R->version;
            if ($version != 'V2.0') {
                appendErrorMessage("The database has not been migrated from Aigaion 1.x towards Aigaion 2.0. <br/>
                                    Automatic update is not possible, even from an account with sufficient rights.<br/><br/> 
                                    PLEASE ASK YOUR ADMINISTRATOR TO RUN THE MIGRATION SCRIPTS.<br/>");
                return False;
            } else {
                return True;
            }
        }
        return False;
    }

?>