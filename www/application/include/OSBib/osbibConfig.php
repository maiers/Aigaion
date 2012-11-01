<?php
/********************************
OSBib:
A collection of PHP classes to create and manage bibliographic formatting for OS bibliography software 
using the OSBib standard.

Released through http://bibliophile.sourceforge.net under the GPL licence.
Do whatever you like with this -- some credit to the author(s) would be appreciated.

If you make improvements, please consider contacting the administrators at bibliophile.sourceforge.net 
so that your improvements can be added to the release package.

Adapted from WIKINDX: http://wikindx.sourceforge.net

Mark Grimshaw 2006
http://bibliophile.sourceforge.net
********************************/

/*****
* Configuration for OSBib
* @author Mark Grimshaw
*
*	$Header: /cvsroot/aigaion/webinterface/includes/OSBib/osbibConfig.php,v 1.4 2007/01/03 20:04:45 wietseb Exp $
*
*****/

/**
* For a variety of reasons (e.g. parts of OSBIB appeared in previous bibliophile releases), some of the classes here may be scattered in different directories.
* This config section allows developers to explicitly specify where classes may be found.  The default setting is the organization of classes within OSBIB as relative
* to this index.php file.
*/

$prefix = APPPATH."include/OSBib/";
$bibparseprefix = APPPATH."libraries/";
define("OSBIB__STYLEMAP", $prefix."STYLEMAP.php");
define("OSBIB__STYLEMAPBIBTEX", $prefix."STYLEMAPBIBTEX.php");
define("OSBIB__UTF8", $prefix."UTF8.php");
define("OSBIB__PARSEXML", $prefix."PARSEXML.php");
define("OSBIB__LOADSTYLE", $prefix."LOADSTYLE.php");
define("OSBIB__BIBLIOPHILECSS", $prefix."create/osbib.css");
define("OSBIB__BIBLIOPHILEGIF", $prefix."create/bibliophile.gif");
define("OSBIB__JAVASCRIPT", $prefix."create/common.js");
define("OSBIB__SESSION", $prefix."create/SESSION.php");
define("OSBIB__SUCCESS", $prefix."create/SUCCESS.php");
define("OSBIB__ERRORS", $prefix."create/ERRORS.php");
define("OSBIB__MESSAGES", $prefix."create/MESSAGES.php");
define("OSBIB__MISC", $prefix."create/MISC.php");
define("OSBIB__INIT", $prefix."create/INIT.php");
define("OSBIB__FORM", $prefix."create/FORM.php");
define("OSBIB__FORMMISC", $prefix."create/FORMMISC.php");
define("OSBIB__TABLE", $prefix."create/TABLE.php");
define("OSBIB__ADMINSTYLE", $prefix."create/ADMINSTYLE.php");
define("OSBIB__PREVIEWSTYLE", $prefix."create/PREVIEWSTYLE.php");
define("OSBIB__PREVIEWCITE", $prefix."create/PREVIEWCITE.php");
define("OSBIB__HELPSTYLE", $prefix."create/HELPSTYLE.php");
define("OSBIB__CLOSE", $prefix."create/CLOSE.php");
define("OSBIB__CLOSEPOPUP", $prefix."create/CLOSEPOPUP.php");
define("OSBIB__BIBSTYLE", $prefix."format/BIBSTYLE.php");
define("OSBIB__BIBFORMAT", $prefix."format/BIBFORMAT.php");
define("OSBIB__CITESTYLE", $prefix."format/CITESTYLE.php");
define("OSBIB__CITEFORMAT", $prefix."format/CITEFORMAT.php");
define("OSBIB__PARSESTYLE", $prefix."format/PARSESTYLE.php");
define("OSBIB__EXPORTFILTER", $prefix."format/EXPORTFILTER.php");
define("OSBIB__PARSECREATORS", $bibparseprefix."PARSECREATORS.php");
define("OSBIB__PARSEMONTH", $bibparseprefix."PARSEMONTH.php");
define("OSBIB__PARSEPAGE", $bibparseprefix."PARSEPAGE.php");

// Path to where the XML style files are kept.  No trailing '/'
define("OSBIB_STYLE_DIR", $prefix."styles/bibliography"); // CB
?>