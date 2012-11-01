<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for some BibTeX related functions. 
| -------------------------------------------------------------------
|
|   This helper caches the bibtex id mappings, used for e.g. crossreferencing in notes.
|   NOTE: the bibtex id mappings also contain non-accessible (due to rights) 
|   publications!
|
|	Usage:
|       //load this helper:
|       $this->load->helper('bibtex'); 
|       //get an array containing info on the bibtex_ids in the database: pub_id, bibtex_id and a regexp for replacement
|       $bibtexidlinks = getBibtexIdLinks();
|       //get an array of all pub_ids for publications with an bibtex_id referenced in the given text
|       $bibtex_ids = getCrossrefIDsForText($text);
|
*/

    function getBibtexIdLinks() {
        $CI = &get_instance();
        $bibtexidlinks = $CI->latesession->get('BIBTEX_ID_LINKS');
        if (!isset($bibtexidlinks)||($bibtexidlinks==null)) {
            $bibtexidlinks = refreshBibtexIdLinks();
        }
        return $bibtexidlinks;
    }  

    function getCrossrefIDsForText($text)
    {
        $pub_ids = array();
        $bibtexidlinks = getBibtexIdLinks();
		foreach ($bibtexidlinks as $pub_id => $bibtex_id) {
			if ($bibtex_id != "") {
				if (preg_match($bibtex_id[1], $text))
					$pub_ids[] = $pub_id;
			}
		}
		return $pub_ids;
    }
    
    function refreshBibtexIdLinks() {
        $CI = &get_instance();
        $bibtexidlinks = array();
        $CI->db->select('pub_id, bibtex_id');
        $Q = $CI->db->get('publication');
        foreach ($Q->result() as $R)
        {
            if ($R->bibtex_id != "")
                $bibtexidlinks[$R->pub_id ] = array($R->bibtex_id, "/\b(?<!\.)(".preg_quote($R->bibtex_id, "/").")\b/");
            else
                $bibtexidlinks[$R->pub_id ] = "";
        }
        $CI->latesession->set('BIBTEX_ID_LINKS',$bibtexidlinks);
        return $bibtexidlinks;
    }
?>