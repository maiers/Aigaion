<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for parsing DOI, PDF, URL and related fields. 
| -------------------------------------------------------------------
|
|   When you import a publication, the import data often contains URLs or DOIs.
|   Such external links are often pointers to a file containing the contents of the 
|   publication, or pointers to a website where such a file can be downloaded.
|   This helper contains a few functions to help interpret such data in such a way
|   that the information can be stored in Aigaion as a 'remote attachment' or a DOI.
|
|   Normally, one would expect to use this method on the BibTeX fields:
|       pdf, url, doi, ee, 
|
|	Usage:
|       //load this helper:
|       $this->load->helper('attachments'); 
|       //given the contents of a field, try to interpret it:
|       list($parsed, $attUrl, $doi) = parseUrlField($name, $value)
|       //if $parsed is True, the contents of the field have been successfully interpreted 
|       and $attUrl or $doi contain the result. Information that has been interpreted as a 
|       DOI will not be returned as attUrl as well.
|
|   Example:
|       BibTeX: url={http://dx.doi.org/10.1007/11872320_3}
|       Output: (True, '', '10.1007/11872320_3')
|
*/

    /** This function will probably evolve and extend a lot over time, as
    we ever find new ways in which urls have been encoded in import data. 
    Note: the incoming data is UTF8. However, if its really a doi or url, it will 
    contain only ascii characters and we need not worry. */
    function parseUrlField($name, $value) {
        $parsed = False;
        $attUrl = '';
        $doi = '';
        /* attempt to extract doi. Normally this involved looking for prefixes such as 
        http://dx.doi.org/ , but when the field name was 'doi' and such a prefix was not
        found, the full field value is seen as DOI. */
        $test = trim(strtolower($value));
        if (   (strpos($test,'http://dx.doi.org/')===0)
            || (strpos($test,'http://doi.acm.org/')===0)
            || (strpos($test,'http://doi.wiley.org/')===0)
            || (strtolower($name)=='doi') 
            ) {
            $matches = array();
            preg_match("/(http:\/\/[\w]+\.([\w]+\.)?[\w]+\/)?(.*)/",$test,$matches);
            $doi = $matches[3];
            $parsed = True;
        }
        
        /* attempt to extract other url if no DOI was found. simply tests for a start of 'http://' */
        if (!$parsed) {
            if (   (strpos($test,'http://')===0) ) {
                $attUrl = $value;
                $parsed = True;
            }
        }
        
        /* attempt other ways to parse the field (are there any, actually? Maybe when we start allowing
        relative URLs in the import data.) */
        if (!$parsed) {
        }
        
        return array($parsed, $attUrl, $doi);
    }
    
?>