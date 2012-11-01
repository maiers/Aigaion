<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/** This class regulates the database access for Keywords. */
class Keyword_db {


  function Keyword_db()
  {
  }

  function getByID($keyword_id)
  {
        $CI = &get_instance();
    //retrieve one keyword row
    $Q = $CI->db->get_where('keywords',array('keyword_id'=>$keyword_id));

    if ($Q->num_rows() > 0)
    {
      //load the keyword
      $R = $Q->row();
      return $this->getFromRow($R);
    }
    else
      return null;
  }
  
  function getFromRow($R)
  {
    foreach ($R as $key => $value)
    {
      $kw->$key = $value;
    }
    return $kw;
  }  
  
  function getFromPost()
  {
    $CI = &get_instance();
    $CI->load->library('bibtex2utf8');
    $CI->load->helper('utf8_to_ascii');
    
    //create the array with variables to retrieve
    $fields = array('keyword_id',
                    //'specialchars', no! specialchars var is not set in edit form.
                    'cleankeyword',
                    'keyword'
                   );
    
    //retrieve all fields
    foreach ($fields as $key)
    {
      $keyword->$key = trim($CI->input->post($key));
    }
    
    //check for specialchars
    $specialfields = array('keyword');
    if (getConfigurationSetting('CONVERT_BIBTEX_TO_UTF8')!='FALSE') {
      foreach ($specialfields as $field)
      {
        //remove bibchars
        $keyword->$field = $CI->bibtex2utf8->bibCharsToUtf8FromString($keyword->$field);
      }
    }
    $keyword->cleankeyword = utf8_to_ascii($keyword->keyword);
    return $keyword;
  }
  
  function getByKeyword($keyword)
  {
    $CI = &get_instance();
    $Q = $CI->db->get_where('keywords',array('keyword'=>$keyword));

    if ($Q->num_rows() > 0)
    {
      //load the publication
      $R = $Q->row();
    
      return $this->getFromRow($R);
    }
    else
    {
      $CI->load->helper('utf8_to_ascii');
      $Q = $CI->db->get_where('keywords', array('cleankeyword'=>utf8_to_ascii($keyword)));
      if ($Q->num_rows() > 0)
      {
        //load the publication
        $R = $Q->row();
        return $this->getFromRow($R);
      }
      else
        return null;
    }
  }
  
  function getKeywordsLike($keyword)
  {
    $CI = &get_instance();
    //select all keywords from the database that start with the characters as in $keyword
    $CI->db->order_by('keyword');
    $CI->db->like('keyword',$keyword);
    $Q = $CI->db->get('keywords');
    
    //retrieve results or fail
    $result = array();
    foreach ($Q->result() as $R)
    {
      $result[] = $this->getFromRow($R);
    }
    return $result;
  }
  
  //getSimilarKeywords takes the keyword as argument *not the keyword object*
  function getSimilarKeywordsByWord($keyword) {
    $result = array();
    $CI = &get_instance();
    $CI->load->helper('utf8_to_ascii');
    
    //get database author array
    $CI->db->select('keyword_id, cleankeyword');
    $CI->db->order_by('cleankeyword');
    $Q = $CI->db->get('keywords');
    
    $db_cleankeywords= array();
    //retrieve results or fail                       
    foreach ($Q->result() as $R)
    {
      $db_cleankeywords[$R->keyword_id] = strtolower($R->cleankeyword); //why strtolower? because we want to check case insensitive.
    }
    
    $db_distances = array();
    //allow max 1 character different if keyword smaller than four characters
    $cleankeyword = strtolower(utf8_to_ascii($keyword));
    $existing_keyword = $this->getByKeyword($keyword);
    if ($existing_keyword != null)
      $existing_id = $existing_keyword->keyword_id;
    else
      $existing_id = -1;
      
    if (sizeof($cleankeyword) < 4)
      $dist_threshold = 2;
    else if (sizeof($cleankeyword) < 8)
      $dist_threshold = 3;
    else
      $dist_threshold = 4;
      
    foreach ($db_cleankeywords as $keyword_id => $db_keyword)
    {
      $distance = levenshtein($db_keyword , $cleankeyword);
      if (($distance < $dist_threshold) && ($keyword_id != $existing_id))
        $db_distances[$keyword_id] = $distance;
    }
    
    //sort while keeping key relationship
    asort($db_distances, SORT_NUMERIC);
    
    foreach($db_distances as $key => $value)
    {
      $result[$key]= $this->getByID($key);
    }
    return $result;
  }
  
  function getSimilarKeywordsByID($keyword_id)
  {
    $keyword = $this->getByID($keyword_id);
    return $this->getSimilarKeywordsByWord($keyword->keyword);
  }
  
  //getRelatedKeywords($keyword)
  //returns an array with keywords that co-occur with given keywords
  function getRelatedKeywords($keyword)
  {
    
    $CI = &get_instance();
    $Q = $CI->db->query("SELECT DISTINCT ".AIGAION_DB_PREFIX."keywords.*, COUNT(".AIGAION_DB_PREFIX."keywords.keyword_id) AS sum 
                          FROM ".AIGAION_DB_PREFIX."keywords, ".AIGAION_DB_PREFIX."publicationkeywordlink, ".AIGAION_DB_PREFIX."publicationkeywordlink as linkcopy
                          WHERE ".AIGAION_DB_PREFIX."publicationkeywordlink.keyword_id = ".$CI->db->escape($keyword->keyword_id)."
                          AND ".AIGAION_DB_PREFIX."publicationkeywordlink.pub_id = linkcopy.pub_id
                          AND linkcopy.keyword_id = ".AIGAION_DB_PREFIX."keywords.keyword_id
                          GROUP BY ".AIGAION_DB_PREFIX."keywords.keyword_id ORDER BY ".AIGAION_DB_PREFIX."keywords.cleankeyword");

    $result = array();
    foreach ($Q->result() as $R) {
      if ($R->keyword_id != $keyword->keyword_id)
      {
        $kw = $CI->keyword_db->getByID($R->keyword_id);
        $kw->count = $R->sum;
        $result[] = $kw;
      }
    }
    return $result;
  }
  
  function getAllKeywords()
  {
    $CI = &get_instance();
    $result = array();
    
    //get all keywords from the database, order by cleankeyword
    $CI->db->order_by('cleankeyword');
    $Q = $CI->db->get('keywords');
    
    //retrieve results or fail
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }
    return $result;
  }
  
  function getKeywordsForPublication($pub_id)
  {
    $CI = &get_instance();
    $Q = $CI->db->query("SELECT ".AIGAION_DB_PREFIX."keywords.* FROM ".AIGAION_DB_PREFIX."keywords, ".AIGAION_DB_PREFIX."publicationkeywordlink
                               WHERE ".AIGAION_DB_PREFIX."publicationkeywordlink.pub_id = ".$CI->db->escape($pub_id)." 
                                 AND ".AIGAION_DB_PREFIX."publicationkeywordlink.keyword_id = ".AIGAION_DB_PREFIX."keywords.keyword_id
                               ORDER BY ".AIGAION_DB_PREFIX."keywords.keyword");
    $result = array();

    if ($Q->num_rows() > 0)
    {
      foreach ($Q->result() as $R)
      {
        $result[] = $this->getFromRow($R);
      }
    }

    return $result;
  }
  
  function getPublicationCount($keyword_id) {
    $CI = &get_instance();
    $CI->db->select("keyword_id");
    $CI->db->distinct();
    $CI->db->where(array('keyword_id'=>$keyword_id));
    $CI->db->from("publicationkeywordlink");
    return $CI->db->count_all_results();
  } 
  
  function add($keyword)
  {
    $CI = &get_instance();
    $CI->load->helper('utf8_to_ascii');
    $cleankeyword = utf8_to_ascii($keyword->keyword);
    $data = array('keyword' => $keyword->keyword, 'cleankeyword'=>$cleankeyword);
    
    $CI->db->insert('keywords', $data);
    
    $keyword_id = $CI->db->insert_id();
    
    if ($keyword_id)
    {
      //load the keyword
      $kw->keyword_id = $keyword_id;
      $kw->keyword = $keyword;
      $kw->cleankeyword = $cleankeyword;
      return $kw;
    }
    else
      return null;
  }
  
  function update($keyword_id, $keyword)
  {
    $CI = &get_instance();
    $CI->load->helper('utf8_to_ascii');
    $cleankeyword = utf8_to_ascii($keyword);
    $updatefields =  array('keyword'=>$keyword, 'cleankeyword'=>$cleankeyword);
    $CI->db->update('keywords', $updatefields, array('keyword_id'=>$keyword_id));
    
    return $this->getByID($keyword_id);
  }  
  
  //ensureKewordsInDatabase($keywords) checks if all keywords in the array 
  //are already in the database. If not, it will add them.
  //returns a map of (keyword_id=>keyword)
  function ensureKeywordsInDatabase($keywords)
  {
    $CI = &get_instance();
    if (!is_array($keywords))
      return null;
    
    $result = array();
    foreach($keywords as $kw)
    {
      $current      = $this->getByKeyword($kw->keyword);
      if ($current == null)
        $current    = $this->add($kw);
      
      $result[] = $current;
    }
    
    return $result;
  }
  
  //replaceSourceTarget($source_id, $target_id) replaces all occurrences of source_id with target_id
  function replaceSourceTarget($source_id, $target_id)
  {
    $CI = &get_instance();
        
    $Q = $CI->db->get_where('publicationkeywordlink',array('keyword_id'=>$source_id));
    if ($Q->num_rows()>0) {
      //the keyword is used. replace publicationkeyword links
      foreach ($Q->result() as $row) {
        $CI->db->delete('publicationkeywordlink',array('pub_id'=>$row->pub_id, 'keyword_id'=>$source_id));
        $Q2 = $CI->db->get_where('publicationkeywordlink', array('keyword_id'=>$target_id, 'pub_id'=>$row->pub_id));
        if ($Q2->num_rows() == 0)
        {
          $CI->db->insert('publicationkeywordlink',array('pub_id'=>$row->pub_id, 'keyword_id'=>$target_id));
        }
      }
    }  
  }
  
  function delete($keyword_id)
  {
    $CI = &get_instance();
    $CI->db->delete('publicationkeywordlink',array('keyword_id'=>$keyword_id));
    $CI->db->delete('keywords',array('keyword_id'=>$keyword_id));
  }
  
  function review($keywords, $keyword_id=-1)
  {
    $CI = &get_instance();
    if (!is_array($keywords))
      return null;
    
    $result_message   = "";
    
    //get database keyword array
    $db_keywords = array();
    $CI->db->order_by('keyword');
    $Q = $CI->db->get('keywords');
    if ($Q->num_rows() > 0)
    {
      foreach ($Q->result() as $R)
      {
        $db_keywords[$R->keyword_id] = strtolower($R->keyword);
      }
    }
    
    //check availability of the keywords in the database
    foreach ($keywords as $keyword)
    {
      $keyword_low  = strtolower($keyword->keyword);
      $db_keyword_id   = array_search($keyword_low, $db_keywords);
      
      //is the keyword already in the db?
      if (!is_numeric($db_keyword_id))
      {
        //not found in the database, so check for similar keywords
        $similar_keywords = $this->getSimilarKeywordsByWord($keyword_low);
        
        $numResults = count($similar_keywords);
        if (array_key_exists($keyword_id, $similar_keywords))
        $numResults = $numResults - 1;
        if ($numResults > 0)
        {
          $result_message .= __("Found similar keywords for")." <b>&quot;".$keyword->keyword."&quot;</b>:<br/>\n";
          $result_message .= "<ul>\n";
          foreach($similar_keywords as $db_keyword)
          {
            if ($db_keyword->keyword_id != $keyword_id)
              $result_message .= "<li>".$db_keyword->keyword."</li>\n";
          }
          $result_message .= "</ul>\n";
        }
        //when no similar keywords are found, we add the unknown keyword to the database
        else
        {
          $this->add($keyword);
        }
      }
    }
    if ($result_message != "")
    {
      $result_message .= __("Please review the entered keywords.")."<br/>\n";
      return $result_message;
    }
    else
      return null;
  }
}
?>