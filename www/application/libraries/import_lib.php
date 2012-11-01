<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php

/** This library is developed as access point to the import libraries in Aigaion.
 *  In the future, it will provide access to: parsers from text, parsers from URLs, etc,
 *  through a plugin mechanism.
 */
class Import_lib
{
  /** Returns a list of the types of data that can be import from a string
   * (e.g. entered in the textarea in the import/viewform controller.
   * In the future, this should be determined by looking at the registered import plugins...
   */
  function getAvailableImportTypes()
  {
    return array('BibTeX'=>'BibTeX','ris'=>'ris','refer'=>'refer');
  }
  
  /** Attempts to automatically determine the type of import data in a string
   * (e.g. entered in the textarea in the import/viewform controller 
   * In the future, this *might* be determined by asking the registered import plugins...
   */
  function determineImportType(&$import_data)
  {
    $type = 'unknown';
    //determine type of input
    if (preg_match("/(@[A-Za-z]{4,}\s*[\r\n\t]*{)/", $import_data) == 1)
    {
      $type = "BibTeX";
    } 
    else if (preg_match("/(TY\s{1,2}-\s)/", $import_data) == 1)
    {
      $type = "ris";
    }
    else if (preg_match("/\%0/", $import_data) == 1)
    {
      $type = "refer";
    }    
    return $type;
  }

  /** Try to guess importt type from file name. We can try to add more extensions here, but let's be
  conservative: not every XML extension is endnote data, and '.ref' is not very imformative either... */
  function determineImportTypeFromFilename($fileName)
  {
    $type = 'unknown';
    //determine type of input
    if (strlen($fileName)<4) return $type;
    if (strtolower(substr($fileName,-4))=='.bib')
    {
      $type = "BibTeX";
    } 
    if (strtolower(substr($fileName,-4))=='.ris')
    {
      $type = "ris";
    }
    return $type;
  }
}

?>