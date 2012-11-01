<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** This class holds the data structure of a site configuration. 

*/

class SiteConfig {
  
    //don't access directly!
    var $configSettings = array();
    
    function SiteConfig()
    {
    }
    
    /** commit the config settings embodied in the given data */
    function update() {
        $CI = &get_instance();
        $CI->siteconfig_db->update($this);
    }
    
    function getConfigSetting($name) {
        if (!isset($this->configSettings[$name])) {
            $this->configSettings[$name] = '';
        }
        return $this->configSettings[$name];
    }
    
}
?>