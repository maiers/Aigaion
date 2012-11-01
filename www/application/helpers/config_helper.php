<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for accessing the configuration settings of the system.
| -------------------------------------------------------------------
|
|   Provides access to the site configuration settings of the system.
|
|	Usage:
|       $this->load->helper('config'); //load this helper
|       $val = getSiteConfigSetting($settingName); //retrieve site configuration settings
|
|   Implementation:
|       The configuration settings are loaded from the database the first time a setting is 
|       requested; the settings are stored to be able to retrieve them faster on subsequent 
|       requests.
|
|       The reason for not initializing them upon loading the helper for the first time is
|       that this helper is autoloaded, and with autoload you're not sure in what order files
|       are autoloaded, so the database connection may not be ready yet.
|
|       
*/

    /** Return the value of a certain Site Configuration Setting. */
    function getConfigurationSetting($settingName) {
        $CI = &get_instance();
        $siteconfig = $CI->latesession->get('SITECONFIG');
        if (!isset($siteconfig)||($siteconfig==null)) {
            $siteconfig = $CI->siteconfig_db->getSiteConfig();
            $CI->latesession->set('SITECONFIG',$siteconfig);
        }
        if (!array_key_exists($settingName,$siteconfig->configSettings)) {
            return "";
        }
        return $siteconfig->configSettings[$settingName];
    }

?>