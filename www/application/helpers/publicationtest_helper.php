<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
This helper provides functions for selecting publicationtype dependent fields.
*/


function getPublicationFieldArray($type)
{
	$type = ucfirst(strtolower(trim($type)));
	switch ($type) {
		case "Article":
		return array( 'namekey'         => '',  
		              'journal'         => 'required',  
		              'year'            => 'required', 
		              'month'           => '', 
		              'volume'          => '', 
		              'number'          => '', 
		              'pages'           => '', 
		              'issn'            => '', 
		              'doi'             => '',
								  'note'            => '', 
		              'keywords'        => '', 
		              'abstract'        => ''
								);
		break;
		case "Book":
		return array( 'namekey'         => '', 
  								'booktitle'       => '', 
  								'series'          => '', 
  								'year'            => 'required', 
  								'month'           => '', 
  								'volume'          => '', 
  								'number'          => '', 
  				        'edition'         => '', 
  								'publisher'       => 'required', 
  								'address'         => '', 
  								'isbn'            => '', 
  								'note'            => '', 
  								'doi'             => '',
  								'abstract'        => ''
								);
		break;
		case "Booklet":
		return array( 'namekey'         => '', 
  								'year'            => '', 
  								'month'           => '', 
  								'address'         => '', 
  								'howpublished'    => '', 
  								'note'            => '', 
  								'doi'             => '',
  								'keywords'        => '', 
  								'abstract'        => ''
								);
		break;
		case "Inbook":
		return array( 'namekey'         => '', 
  								'series'          => '',  
  								'year'            => 'required', 
  								'month'           => '', 
  								'volume'          => '', 
  								'number'          => '', 	
  								'edition'         => '',  
  								'pages'           => 'conditional', 
  				        'chapter'         => 'conditional', 
  								'publisher'       => 'required', 
  								'address'         => '',
  								'type'            => '',  
  								'isbn'            => '', 
  								'note'            => '', 
  								'doi'             => '',
  								'keywords'        => '', 
  								'abstract'        => ''
								);
		break;
		case "Incollection":
		return array( 'namekey'         => '', 
  								'booktitle'       => 'required',  
  								'series'          => '',  
  								'year'            => 'required', 
  								'month'           => '', 
  								'volume'          => '', 
  								'number'          => '',  
  								'pages'           => '', 
  				        'chapter'         => '',  
  								'publisher'       => 'required',  
  								'organization'    => '', 
  								'address'         => '',  
  								'type'            => '',   
  								'isbn'            => '', 
  								'note'            => '', 
  								'doi'             => '',
								  'keywords'        => '',  
								  'abstract'        => ''
								);
		break;
		case "Inproceedings":
		return array( 'namekey'         => '', 
  								'booktitle'       => 'required',  
  								'series'          => '',  
  								'year'            => 'required', 
  								'month'           => '', 
  								'volume'          => '', 
  								'number'          => '',  
  								'pages'           => '', 
  								'organization'    => '', 
				          'publisher'       => '', 
  								'location'        => '',
  								'address'         => '',  
  								'note'            => '', 
								  'doi'             => '',
								  'keywords'        => '',  
  								'abstract'        => ''
								);
		break;
		case "Manual":
		return array( 'namekey'         => '', 
  								'year'            => '', 
  								'month'           => '', 
  								'edition'         => '',  
  								'organization'    => '', 
  								'address'         => '', 
  								'note'            => '', 
  								'doi'             => '',
								  'keywords'        => '',  
  								'abstract'        => ''
								);
		break;
		case "Mastersthesis":
		return array( 'namekey'         => '', 
  								'year'            => 'required', 
  								'month'           => '', 
  								'school'          => 'required', 
  								'address'         => '',  
  								'type'            => '',   
  								'note'            => '', 
  								'doi'             => '',
								  'keywords'        => '',  
  								'abstract'        => ''
								);
		break;
		case "Misc":
  	return array( 'namekey'         => '', 
  								'year'            => '', 
  								'month'           => '', 
  								'howpublished'    => '', 
  								'note'            => '', 
  								'url'             => '', 
  								'doi'             => '',
  								'keywords'        => '',  
  								'abstract'        => ''
								);
		break;
		case "Phdthesis":
		return array( 'namekey'         => '', 
  								'year'            => 'required', 
  								'month'           => '', 
  								'school'          => 'required', 
  								'address'         => '',  
  								'type'            => '',   
  								'note'            => '', 
								  'doi'             => '',
  								'keywords'        => '',  
								  'abstract'        => ''
								);
		break;
		case "Proceedings":
		return array( 'namekey'         => '', 
  								'booktitle'       => '',  
  								'series'          => '',  
  								'year'            => 'required', 
  								'month'           => '', 
  								'volume'          => '', 
  								'number'          => '',  
  								'publisher'       => '', 
          				'organization'    => '', 
  								'address'         => '',  
  								'isbn'            => '', 
  								'issn'            => '', 
  								'note'            => '', 
  								'doi'             => '',
  								'keywords'        => '', 
  								'abstract'        => ''
								);
		break;
		case "Techreport":
		return array( 'namekey'         => '', 
  								'year'            => 'required', 
  								'month'           => '', 
  								'number'          => '',  
  								'institution'     => 'required', 
  								'address'         => '',  
  								'type'            => '',   
  								'note'            => '', 
  								'doi'             => '',
								  'keywords'        => '',  
  								'abstract'        => ''
								);
		break;
		case "Unpublished":
		return array( 'namekey'         => '', 
  								'year'            => '', 
  								'month'           => '', 
  								'note'            => 'required', 
								  'doi'             => '',
  								'keywords'        => '',  
								  'abstract'        => ''
								);
		break;
		default:
		return array();
		break;
	}
}

//REMINDER: TECHREPORT CASE -> 'type' FIELD IS TO BE RENAMED
function getPublicationSummaryFieldArray($type)
{
	$type = ucfirst(strtolower($type));
	switch ($type) {
		case "Article":
			return array( 'journal'       => ', in: ',
	                  'number'        => ', number ',
	                  'pages'         => ', pages ',
	                  'volume'        => ', volume ', 
	                  'actualyear'    => ', '
	                );
		break;
		case "Book":
			return array( 'publisher'     => ', ',
	                  'series'        => ', ',
	                  'volume'        => ', volume ', 
	                  'actualyear'    => ', '
                  );
		break;
		case "Booklet":
			return array( 'howpublished'  => ', ',
			              'actualyear'    => ', ',
			            );
		break;
		case "Inbook":
			return array( 'chapter'       => ', chapter ', 
			              'pages'         => ', pages ', 
			              'publisher'     => ', ',
			              'series'        => ', ',
			              'volume'        => ', volume ', 
			              'actualyear'    => ', '
			            );
		break;
		case "Incollection":
			return array( 'booktitle'     => ', in: ', 
			              'organization'  => ', ', 
	                  'pages'         => ', pages ', 
	                  'publisher'     => ', ',
	                  'actualyear'    => ', '
	                );
		break;
		case "Inproceedings":
			return array( 'booktitle'     => ', in: ', 
	                  'organization'  => ', ', 
 	                  'location'      => ', ',
	                  'pages'         => ', pages ', 
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
	                  'number'        => ', number ', 
	                  'type'          => ', ',         //REMINDER: THIS FIELD IS TO BE RENAMED!!
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
  return array("Article"        => 'Article',
          		 "Book"           => 'Book',
          		 "Booklet"        => 'Booklet',
          		 "Inbook"         => 'Inbook',
          		 "Incollection"   => 'Incollection',
          		 "Inproceedings"  => 'Inproceedings',
          		 "Manual"         => 'Manual',
          		 "Mastersthesis"  => 'Mastersthesis',
          		 "Misc"           => 'Misc',
          		 "Phdthesis"      => 'Phdthesis',
          		 "Proceedings"    => 'Proceedings',
          		 "Techreport"     => 'Techreport',
          		 "Unpublished"    => 'Unpublished');
}


?>