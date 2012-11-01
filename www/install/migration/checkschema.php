<?php
/*
Web based document management system
Copyright (C) 2003-2006 (in alphabetical order):
 Wietse Balkema, Gerbert ten Brinke, Arthur van Bunningen, Hendri Hondorp, Dennis Reidsma

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/
include_once("upload_class.php");

//The method that is called externally. See _DATABASEVERSIONMANAGEMENT.txt
//for what to do if you introduce a new version.
//Pre: the database is connected.
//In this first function you should adapt the latest version to check..

//WARNING: THIS FILE IS ONLY SUITABLE FOR THE PURPOSE OF CONVERTING AIGAION 1.X TO 2.0
function checkDatabase($AIGAION2_DB_PREFIX) {

	//delegate check to checkschemafile for latest release...
	include("checkschema_v1.php");

	//check whether this is the first time install, if so: be silent!
	$bSilent = true;
	//$R = mysql_query("SELECT version FROM ".$AIGAION2_DB_PREFIX."aigaiongeneral");
	//if (!$R) {
	//	$bSilent = true;
	//}

	//if ($bSilent)
	//{
  //  echo "<div class=message>";
	//	echo ("Welcome to Aigaion, we are setting up the database structure for first use.<br/>");
	//	echo "</div>";
	//}

	if (!(checkDatabaseV1_19($bSilent, $AIGAION2_DB_PREFIX)))
		return false;

	//if ($bSilent)
	//{
	//	echo "<div class=message>";
	//	echo "Succeeded creating database structure.<br/>";
	//	echo "Please configure this site under menu item 'Site configuration'.<br/>";
	//	echo "</div>";
	//}


	return true;
}

//================================================================
//  INTERNAL HELPER METHODS
//================================================================

//returns true if version number exists and is correct
//also display debug information
function checkVersion($v, $bSilent=false, $AIGAION2_DB_PREFIX = "") {
	$res = mysql_query("SELECT version FROM ".$AIGAION2_DB_PREFIX."aigaiongeneral");
	if ($res) {
		$row = mysql_fetch_array($res);
		if ($row["version"]==$v) { //if version == latest version number, return true
			return true;
		}
	}
	if (!$bSilent)
	{
		echo "<div class=message>";
		echo ("Checking version ".$v."... update needed.<br>");
		echo "</div>";
	}
	return false;
}

//set the version of the database to the given version; show some debug information.
function setVersion($v, $bSilent=false, $AIGAION2_DB_PREFIX = "") {

	$res = mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."aigaiongeneral SET version='$v'");
	if (!$res) {
		dbError(mysql_error());
		return false;
	}
	if (!$bSilent)
	{
		echo "<div class=message>";
		echo ("Update version ".$v." Succeeded<br>"); //for debug
		echo "</div>";
	}
	return true;
}

//set the version of the release to the given version; show some debug information.
function setReleaseVersion($v, $bSilent=false, $AIGAION2_DB_PREFIX = "") {

	$res = mysql_query("UPDATE ".$AIGAION2_DB_PREFIX."aigaiongeneral SET releaseversion='$v'");
	if (!$res) {
		dbError(mysql_error());
		return false;
	}
	if (!$bSilent)
	{
		echo "<div class=message>";
		echo ("Update to release version ".$v." Succeeded<br>"); //for debug
		echo "</div>";
	}
	return true;
}

//error function
function dbError($mysqlerror)
{
	echo "<div class=message>";
	echo "<br/>".$mysqlerror."<br/>UPDATE WAS NOT SUCCESSFULL<br/>";
	echo "Some database operations require mysql root privileges. Please ensure that the mysql<br/>";
	echo "user in your config.php file has sufficient rights.<br/>";
	echo "</div>";
}


?>
