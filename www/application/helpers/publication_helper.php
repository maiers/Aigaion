<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
Aigaion - Web based document management system
Copyright (C) 2003-2007 (in alphabetical order):
Wietse Balkema, Arthur van Bunningen, Dennis Reidsma, Sebastian Schleußner

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
This helper provides functions for selecting publicationtype dependent fields.
*/

function getFullFieldArray() {
    return array(
                  'title'          ,
                  'type'	       ,
                  'journal'        ,
                  'booktitle'      ,
                  'edition'        ,
                  'series'         ,
                  'volume'         ,
                  'number'         ,
                  'chapter'        ,
                  'year'           ,
                  'month'          ,
                  'firstpage'      , //DEPRECATED
                  'lastpage'       , //DEPRECATED
                  'pages'		   ,
                  'publisher'      ,
                  'location'       ,
                  'institution'    ,
                  'organization'   ,
                  'school'         ,
                  'address'        ,
                  'howpublished'   ,
                  'note'           ,
                  'keywords'       ,
                  'issn'           ,
                  'isbn'           ,
                  'url'            ,
                  'doi'            ,
                  'crossref'       ,
                  'namekey'        ,
                  'abstract'       ,
                  'userfields'     
    );
}
function getCapitalFieldArray() {
    return array(
                  'issn'           ,
                  'isbn'           ,
                  'url'            ,
                  'doi'            
    );
}

function getPublicationFieldArray($type)
{
  if (file_exists(dirname(__FILE__).'/local_override_publication_helper.php'))
  {
    require_once(dirname(__FILE__).'/local_override_publication_helper.php');
    return getLocalPublicationFieldArray($type);
  }
	$type = ucfirst(strtolower(trim($type)));
	switch ($type) {
		case "Article":
		return array( 
		          'type'	          => 'hidden',
                  'journal'         => 'required',
                  'booktitle'       => 'hidden',
                  'edition'         => 'hidden',
                  'series'          => 'hidden',
                  'volume'          => 'optional',
                  'number'          => 'optional',
                  'chapter'         => 'hidden',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'optional',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'hidden',
                  'address'         => 'hidden',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'issn'            => 'optional',
                  'isbn'            => 'hidden',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'abstract'        => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Book":
		return array( 
		              'type'	          => 'hidden',
                  'journal'         => 'hidden',
                  'booktitle'       => 'optional',
                  'edition'         => 'optional',
                  'series'          => 'optional',
                  'volume'          => 'optional',
                  'number'          => 'optional',
                  'chapter'         => 'hidden',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'required',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'abstract'        => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Booklet":
		return array( 
				          'type'	          => 'hidden',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'hidden',
                  'series'          => 'hidden',
                  'volume'          => 'hidden',
                  'number'          => 'hidden',
                  'chapter'         => 'hidden',
                  'year'            => 'optional',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'optional',
                  'note'            => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'hidden',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'abstract'        => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Inbook":
		return array( 'type'	          => 'optional',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'optional',
                  'series'          => 'optional',
                  'volume'          => 'optional',
                  'number'          => 'optional',
                  'chapter'         => 'conditional',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'conditional',
                  'publisher'       => 'required',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'abstract'        => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Incollection":
		return array( 'type'	          => 'optional',
                  'journal'         => 'hidden',
                  'booktitle'       => 'required',
                  'edition'         => 'optional',
                  'series'          => 'optional',
                  'volume'          => 'optional',
                  'number'          => 'optional',
                  'chapter'         => 'optional',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'optional',
                  'publisher'       => 'required',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'optional',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'abstract'        => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Inproceedings":
		return array( 'type'	          => 'hidden',
                  'journal'         => 'hidden',
                  'booktitle'       => 'optional', //cannot be required, since it may have been stored in a crossref entry! (and then this field stays empty)
                  'edition'         => 'hidden',
                  'series'          => 'optional',
                  'volume'          => 'optional',
                  'number'          => 'optional',
                  'chapter'         => 'hidden',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'optional',
                  'publisher'       => 'optional',
                  'location'        => 'optional',
                  'institution'     => 'hidden',
                  'organization'    => 'optional',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'issn'            => 'optional',
                  'isbn'            => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'abstract'        => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Manual":
		return array( 'type'	          => 'hidden',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'optional',
                  'series'          => 'hidden',
                  'volume'          => 'hidden',
                  'number'          => 'hidden',
                  'chapter'         => 'hidden',
                  'year'            => 'optional',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'optional',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'hidden',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'abstract'        => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Mastersthesis":
		return array( 'type'	          => 'optional',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'hidden',
                  'series'          => 'hidden',
                  'volume'          => 'hidden',
                  'number'          => 'hidden',
                  'chapter'         => 'hidden',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'required',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'hidden',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'abstract'        => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Misc":
  	return array( 'type'	          => 'hidden',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'hidden',
                  'series'          => 'hidden',
                  'volume'          => 'hidden',
                  'number'          => 'hidden',
                  'chapter'         => 'hidden',
                  'year'            => 'optional',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'hidden',
                  'address'         => 'hidden',
                  'howpublished'    => 'optional',
                  'note'            => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'hidden',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'abstract'        => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Phdthesis":
		return array( 'type'	          => 'optional',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'hidden',
                  'series'          => 'hidden',
                  'volume'          => 'hidden',
                  'number'          => 'hidden',
                  'chapter'         => 'hidden',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'required',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'hidden',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'abstract'        => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Proceedings":
		return array( 'type'	          => 'hidden',
                  'journal'         => 'hidden',
                  'booktitle'       => 'optional',
                  'edition'         => 'hidden',
                  'series'          => 'optional',
                  'volume'          => 'optional',
                  'number'          => 'optional',
                  'chapter'         => 'hidden',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'optional',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'optional',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'issn'            => 'optional',
                  'isbn'            => 'optional',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'abstract'        => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Techreport":
		return array( 'type'	          => 'optional',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'hidden',
                  'series'          => 'hidden',
                  'volume'          => 'hidden',
                  'number'          => 'optional',
                  'chapter'         => 'hidden',
                  'year'            => 'required',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'required',
                  'organization'    => 'hidden',
                  'school'          => 'hidden',
                  'address'         => 'optional',
                  'howpublished'    => 'hidden',
                  'note'            => 'optional',
                  'issn'            => 'hidden',
                  'isbn'            => 'hidden',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'abstract'        => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		case "Unpublished":
		return array( 'type'	    => 'hidden',
                  'journal'         => 'hidden',
                  'booktitle'       => 'hidden',
                  'edition'         => 'hidden',
                  'series'          => 'hidden',
                  'volume'          => 'hidden',
                  'number'          => 'hidden',
                  'chapter'         => 'hidden',
                  'year'            => 'optional',
                  'month'           => 'optional',
                  'pages'		        => 'hidden',
                  'publisher'       => 'hidden',
                  'location'        => 'hidden',
                  'institution'     => 'hidden',
                  'organization'    => 'hidden',
                  'school'          => 'hidden',
                  'address'         => 'hidden',
                  'howpublished'    => 'hidden',
                  'note'            => 'required',
                  'issn'            => 'hidden',
                  'isbn'            => 'hidden',
                  'crossref'        => 'optional',
                  'namekey'         => 'optional',
                  'url'             => 'optional',
                  'doi'             => 'optional',
                  'abstract'        => 'optional',
                  'userfields'      => 'optional'
								);
		break;
		default:
		return array();
		break;
	}
}

//note: the prefix may be an array instead of a string, in that case its (prefix,postfix)
function getPublicationSummaryFieldArray($type)
{
  if (file_exists(dirname(__FILE__).'/local_override_publication_helper.php'))
  {
    require_once(dirname(__FILE__).'/local_override_publication_helper.php');
    return getLocalOverridePublicationSummaryFieldArray($type);
  }
	$type = ucfirst(strtolower($type));
	switch ($type) {
		case "Article":
			return array( 
	                  'actualyear'    => array(' (',')'),
			              'journal'       => ', '.__('in:').' ',
	                  'volume'        => ', ', 
	                  'number'        => ':',
	                  'pages'         => array('(',')')
	                );
		break;
		case "Book":
			return array( 'publisher'     => ', ',
	                  'series'        => ', ',
	                  'volume'        => ', '.__('volume').' ', 
	                  'actualyear'    => ', '
                  );
		break;
		case "Booklet":
			return array( 'howpublished'  => ', ',
			              'actualyear'    => ', ',
			            );
		break;
		case "Inbook":
			return array( 'chapter'       => ', '.__('chapter').' ', 
			              'pages'         => ', '.__('pages').' ', 
			              'publisher'     => ', ',
			              'series'        => ', ',
			              'volume'        => ', '.__('volume').' ', 
			              'actualyear'    => ', '
			            );
		break;
		case "Incollection":
			return array( 'booktitle'     => ', '.__('in:').' ', 
			              'organization'  => ', ', 
	                  'pages'         => ', '.__('pages').' ', 
	                  'publisher'     => ', ',
	                  'actualyear'    => ', '
	                );
		break;
		case "Inproceedings":
			return array( 'booktitle'     => ', '.__('in:').' ', 
	                  'organization'  => ', ', 
 	                  'location'      => ', ',
	                  'pages'         => ', '.__('pages').' ', 
	                  'publisher'     => ', ',
	                  'actualyear'    => ', '
                  );
		break;
		case "Manual":
			return array( 'edition'       => ', ',
	                  'organization'  => ', ',
	                  'actualyear'    => ', '
	                );
		break;
		case "Mastersthesis":
			return array( 'school'        => ', ' ,
	                  'year'          => ', '
                  );
		break;
		case "Misc":
			return array( 'howpublished'  => ', ',
	                  'actualyear'    => ', '
                  );
		break;
		case "Phdthesis":
			return array( 'school'        => ', ',
	                  'actualyear'    => ', '
                  );
		break;
		case "Proceedings":
			return array( 'organization'  => ', ',
			              'publisher'     => ', ',
			              'actualyear'    => ', '
			            );
		break;
		case "Techreport":
			return array( 'institution'   => ', ',
	                  'number'        => ', '.__('number').' ', 
	                  'type'          => ', ',
	                  'actualyear'    => ', '
                  );
		break;
		case "Unpublished":
			return array( 'actualyear'    => ', '
			            );
		break;
		default:
	    return array();
		break;
	}
}

function getPublicationTypes()
{
  return array("Article"        => __('Article'),
          		 "Book"           => __('Book'),
          		 "Booklet"        => __('Booklet'),
          		 "Inbook"         => __('Inbook'),
          		 "Incollection"   => __('Incollection'),
          		 "Inproceedings"  => __('Inproceedings'),
          		 "Manual"         => __('Manual'),
          		 "Mastersthesis"  => __('Mastersthesis'),
          		 "Misc"           => __('Misc'),
          		 "Phdthesis"      => __('Phdthesis'),
          		 "Proceedings"    => __('Proceedings'),
          		 "Techreport"     => __('Techreport'),
          		 "Unpublished"    => __('Unpublished'));
}


function getPublicationStatusTypes()
{
  return array(""               => "",
               "preparation"    => __('In preparation'),
               "submitted"      => __('Submitted'),
               "review"         => __('Under review'),
               "revision"       => __('Under revision'),
               "accepted"       => __('Accepted'),
               "rejected"       => __('Rejected'),
               "published"      => __('Published'));
}

?>