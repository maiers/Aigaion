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
* index.php
* @author Mark Grimshaw
*
*	$Header: /cvsroot/aigaion/webinterface/includes/OSBib/index.php,v 1.3 2006/12/01 14:27:48 reidsma Exp $
*
*****/

// Load configuration file
include_once("osbibConfig.php");


/**
* Initialise
*/
	include_once(OSBIB__ERRORS);
	$errors = new ERRORS();
	include_once(OSBIB__INIT);
	$init = new INIT();
// Get user input in whatever form
	$vars = $init->getVars();
// start the session
	$init->startSession();
	
	if(!$vars)
	{
		include_once(OSBIB__ADMINSTYLE);
		$admin = new ADMINSTYLE($vars);
		$pString = $admin->gateKeep('display');
	}
	else if($vars["action"] == 'adminStyleAddInit')
	{
		include_once(OSBIB__ADMINSTYLE);
		$admin = new ADMINSTYLE($vars);
		$pString = $admin->gateKeep('addInit');
	}
	else if($vars["action"] == 'adminStyleAdd')
	{
		include_once(OSBIB__ADMINSTYLE);
		$admin = new ADMINSTYLE($vars);
		$pString = $admin->gateKeep('add');
	}
	else if($vars["action"] == 'adminStyleEditInit')
	{
		include_once(OSBIB__ADMINSTYLE);
		$admin = new ADMINSTYLE($vars);
		$pString = $admin->gateKeep('editInit');
	}
	else if($vars["action"] == 'adminStyleEditDisplay')
	{
		include_once(OSBIB__ADMINSTYLE);
		$admin = new ADMINSTYLE($vars);
		$pString = $admin->gateKeep('editDisplay');
	}
	else if($vars["action"] == 'adminStyleEdit')
	{
		include_once(OSBIB__ADMINSTYLE);
		$admin = new ADMINSTYLE($vars);
		$pString = $admin->gateKeep('edit');
	}
	else if($vars["action"] == 'adminStyleCopyInit')
	{
		include_once(OSBIB__ADMINSTYLE);
		$admin = new ADMINSTYLE($vars);
		$pString = $admin->gateKeep('copyInit');
	}
	else if($vars["action"] == 'adminStyleCopyDisplay')
	{
		include_once(OSBIB__ADMINSTYLE);
		$admin = new ADMINSTYLE($vars);
		$pString = $admin->gateKeep('copyDisplay');
	}

	else if($vars["action"] == 'previewStyle')
	{
		include_once(OSBIB__PREVIEWSTYLE);
		$preview = new PREVIEWSTYLE($vars);
		$pString = $preview->display();
		include_once(OSBIB__CLOSEPOPUP);
		new CLOSEPOPUP($pString);
	}
// Bibliographic style creation/editing preview
	else if($vars["action"] == 'previewStyleFields')
	{
		include_once(OSBIB__PREVIEWSTYLE);
		$obj = new PREVIEWSTYLE($vars);
		$pString = $obj->display(TRUE);
		include_once(OSBIB__CLOSEPOPUP);
		new CLOSEPOPUP($pString);
	}
// In-text citation creation/editing preview
	else if($vars["action"] == 'previewCite')
	{
		include_once(OSBIB__PREVIEWCITE);
		$obj = new PREVIEWCITE($vars);
		$pString = $obj->display();
		include_once(OSBIB__CLOSEPOPUP);
		new CLOSEPOPUP($pString);
	}
	else if($vars["action"] == 'help')
	{
		include_once(OSBIB__HELPSTYLE);
		$help = new HELPSTYLE();
		$pString = $help->display();
		include_once(OSBIB__CLOSE);
		new CLOSE($pString, FALSE);
	}
	else
		$pString = $errors->text("inputError", "invalid");
/*****
*	Close the HTML code by calling the constructor of CLOSE which also 
*	prints the HTTP header, body and flushes the print buffer.
*****/
	include_once(OSBIB__CLOSE);
	new CLOSE($pString);


?>