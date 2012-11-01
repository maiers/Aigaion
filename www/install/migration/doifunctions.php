<?php	## if this script is not called from within one of the base pages, redirect to frontpage:

/*
Web based document management system
Copyright (C) 2003,2004 Hendri Hondorp, Dennis Reidsma, Arthur van Bunningen

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

/** Stripping a doi means removing the http://xx.yy.zz/ prefix. In the end, we are supposed to
end up with a doi that can be added to http://dx.doi.org/ and lead us to the right place in cyberspace. */
function stripDoi($doi)
{
    $doi = trim(strtolower($doi));
    $matches = array();
    preg_match("/(http:\/\/[\w]+\.([\w]+\.)?[\w]+\/)?(.*)/",$doi,$matches);
    $result = $matches[3];
    return $result;
}
?>
