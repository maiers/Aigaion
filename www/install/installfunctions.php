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

/*
This file contains some helper functions for the install scripts

siteIsConfigured
    returns true if the site has already been configured successfully (e.g. a config.php exists)
*/

function siteIsConfigured() {
    // check existence of config script (index.php). 
    // TAKE CARE of the size of the default index.php script
    if (file_exists("../index.php") && filesize("../index.php") > 200) return true;
    return false;
}

function _query($q) {
    $res = mysql_query($q);
    if (mysql_error())
    {
      $errormessage  = "An error occured while executing a query on the database, please report this error and the query to your administrator or the aigaion development team.<br/>\n";
      $errormessage .= "MySQL Error: ".mysql_error()."<br/>\n";
      $errormessage .= "Query: ".$q;
      die($errormessage);
    }
    return $res;
}
?>