<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');


    function logintegrationHash($username,$token) {
        $CI = &get_instance();
        $secretword = getConfigurationSetting('LOGINTEGRATION_SECRETWORD');
        return md5(md5($username).md5($token).md5($secretword));
    }
    
    function logintegrationLogoutHash($sitename,$serial,$token) {
        $CI = &get_instance();
        $secretword = getConfigurationSetting('LOGINTEGRATION_SECRETWORD');
        return md5(md5($sitename).md5($serial).md5($token).md5($secretword));
    }

?>