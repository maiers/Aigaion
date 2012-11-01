<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for checking, and possibly updating, the database schema.
| -------------------------------------------------------------------
|
|   Provides information whether the version of the code matches the version of the database schema.
|   Provides methods to update the database schema if needed.
|   Used by the login module.
|
|	Usage:
|       $this->load->helper('schema'); //load this helper
|       $schemaIsOK = checkSchema(); //is schema up to date? If not, and current user has sufficient rights, 
|                       a database update is executed. Return true if in the end the schema is up to date.
|
|   Implementation:
|       If a schema update has been committed, you should 
|           - change the checkSchema method to check for the new schema version
|           - upon fail, make the checkSchema method call the appropriate update function 
|             if the current user has sufficient rights.
|       The login module is implemented in such a way that the user is logged out with an appropriate message 
|       if this check fails.
|
|       
*/

    /** 
    This method checks for the latest schema version. If you make a schema update, change this method to
    check for the new version number. Also change this method to call the correct new schema update function.
    */
    function checkSchema() {

      $CI = &get_instance();

    	$bSilent = false;
    	
  	  if (!checkVersion('V2.50')) {
  	    
  	    log_message('debug', 'schema not at version 2.50');
  	    
        $userlogin = getUserLogin(); //note: a not logged in user has no rights :)
        if ($userlogin->hasRights("database_manage")) {
            //sufficient rights: attempt to update schema
            //but first: push a database backup to the user? or save one in a safe place?
            if (getConfigurationSetting('SERVER_NOT_WRITABLE')!='TRUE') {
                //do backup, store in attachment dir
               // appendErrorMessage("Actually, we should still do a forced database backup saved in a 
                                    //safe place on the server before performing the actual update code.<br/>");
            }
            $CI->load->helper('schema_updates_v2');
            if (updateSchemaV2_50()) {
              //clear config settings cache, because settings may have been changed by the schema update
              $siteconfig = $CI->siteconfig_db->getSiteConfig();
              $CI->latesession->set('SITECONFIG',$siteconfig);
              return True;
            } else {
              log_message('error', 'could not update schema to latest version');
              return False;
            }
        }
        
        log_message('error', 'user has insufficent rights to update schema');
        return False;
        
      } else {
        return True;
      }
      return False;
    }


//================================================================
//  INTERNAL HELPER METHODS
//================================================================

//returns true if version number exists and is correct
//also display debug information
function checkVersion($v, $bSilent=false) {
    $CI = &get_instance();
    $Q = $CI->db->get('aigaiongeneral');
    if ($Q->num_rows()>0) {
        $row = $Q->row();
		if ($row->version==$v) { //if version == latest version number, return true
			return true;
		}
	}
	if (!$bSilent)
	{
		appendMessage(sprintf(__("Checking database schema version %s"),$v)."... ".__("update needed").".<br/>");
	}
	return false;
}

//set the version of the database to the given version; show some debug information.
function setVersion($v, $bSilent=false) {
    $CI = &get_instance();
    $CI->db->query("UPDATE ".AIGAION_DB_PREFIX."aigaiongeneral SET version='".$v."'");
	if (mysql_error()) {
		dbError(mysql_error());
		return false;
	}
	if (!$bSilent)
	{
		appendMessage(sprintf(__("Update database schema version %s"),$v)." ".__("Succeeded").".<br/>");
	}
	return true;
}

//set the version of the release to the given version; show some debug information.
//NOTE: no html or xml in description allowed!
function setReleaseVersion($v, $type, $description, $bSilent=false) {
    $CI = &get_instance();
    $CI->load->helper('utf8');
    include_once(APPPATH.'/include/utf8/str_ireplace.php');
    $description = utf8_ireplace('<','',$description);
    $CI->db->insert('changehistory',array('version'=>$v,'type'=>$type,'description'=>$description));
	if (mysql_error()) {
		dbError(mysql_error());
		return false;
	}
	if (!$bSilent)
	{
		appendMessage(sprintf(__("Update to release version %s"),$v)." ".__("Succeeded").".<br/>");
	}
	return true;
}

//error function
function dbError($mysqlerror)
{
    appendErrorMessage("
	    <br/>".$mysqlerror."<br/>".__("UPDATE WAS NOT SUCCESSFUL")."<br/>
	    ".__("Some database operations require mysql root privileges. Please ensure that the mysql user in your index.php file has sufficient rights.")."<br/>
	    ");
}
?>