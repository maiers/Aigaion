<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

if ( ! class_exists('Tbswrapper'))
{
     require_once(APPPATH.'libraries/tbswrapper'.EXT);
}

$obj =& get_instance();
$obj->tbswrapper = new Tbswrapper();
$obj->ci_is_loaded[] = 'tbswrapper';

?> 