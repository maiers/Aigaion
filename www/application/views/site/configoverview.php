<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/site/configoverview

a page with links to all configuration settings

Parameters:
    $siteconfig     the site config object

we assume that this view is not loaded if you don't have the appropriate database_manage rights
*/
$this->load->helper('translation');

echo "<p class='header'>".__('Aigaion site configuration')."</p>";

echo __("Choose one of the links to change settings")."<br/>\n";
echo "<br/>\n";

//note, we can have some overview of current settings here as well!
echo "<ul>";
//interface&appearance
echo "<li>".anchor('site/configform/display',__('General display settings'))."</li>\n";
echo "<br/>";
//content, inpupt, and output
echo "<li>".anchor('site/configform/inputoutput',__('Import and export settings'))."</li>\n";
echo "<li>".anchor('site/configform/attachments',__('Attachment settings'))."</li>\n";
echo "<li>".anchor('site/configform/content',__('Content settings: Custom fields, author synonyms, cover images'))."</li>\n";
echo "<br/>";
//login, users, and access levels
echo "<li>".anchor('site/configform/login',__('Login settings'))."</li>\n";
echo "<li>".anchor('site/configform/userdefaults',__('Default user preferences'))."</li>\n";
echo "<li>".anchor('site/configform/accesslevels',__('Default accesslevels'))."</li>\n";
echo "<br/>";
//integration&embedding
echo "<li>".anchor('site/configform/siteintegration',__('Site integration settings'))."</li>\n";
echo "</ul>";

