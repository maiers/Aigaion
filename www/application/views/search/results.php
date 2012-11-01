<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
$userlogin = getUserLogin();
$this->load->helper('publication');

//$resulttabs will be 'title'=>'resultdisplay'.
//later on, display will take care of surrounding divs, and show-and-hide-scripts for the tabs
$resulttabs = array();
foreach ($searchresults as $type=>$resultList) {
    switch ($type) {
        case 'authors':
            $authordisplay = "<ul>";
            if (getConfigurationSetting('USE_AUTHOR_SYNONYMS') == 'TRUE') 
            {
              foreach ($resultList as $author) 
              {
                $primary = $author;
                if ($author->synonym_of != '0') $primary = $this->author_db->getByID($author->synonym_of);
                $syns = $primary->getSynonyms();
                 
                $authordisplay .= '<li>'.anchor('authors/show/'.$primary->author_id,$primary->getName());
                if (count($syns)>0)
                {
                  $authordisplay .= " <i>(";
                  $authordisplay .= anchor('authors/show/'.$syns[0]->author_id,$syns[0]->getName());
                  for ($i = 1; $i<count($syns); $i++)
                  {
                    $authordisplay .= ', '.anchor('authors/show/'.$syns[i]->author_id,$syns[i]->getName());
                  }
                  $authordisplay .= ")</i>";
                }
                $authordisplay .= '</li>';
              }
            }
            else 
            {
              foreach ($resultList as $author) 
              {
                  $authordisplay .= '<li>'.anchor('authors/show/'.$author->author_id,$author->getName()).'</li>';
              }
            }
            $authordisplay .= "</ul>";
            $resulttabs[sprintf(__('Authors: %s'),count($resultList))] = $authordisplay;
            break;
        case 'topics':
            $topicdisplay = "<ul>";
            foreach ($resultList as $topic) {
                $topicdisplay .= '<li>'.anchor('topics/single/'.$topic->topic_id,$topic->name).'</li>';
            }
            $topicdisplay .= "</ul>";
            $resulttabs[sprintf(__('Topics: %s'),count($resultList))] = $topicdisplay;
            break;
        case 'keywords':
            $keyworddisplay = "<ul>";
            foreach ($resultList as $kw) {
                $keyworddisplay .= '<li>'.anchor('keywords/single/'.$kw->keyword_id,$kw->keyword).'</li>';
            }
            $keyworddisplay .= "</ul>";
            $resulttabs[sprintf(__('Keywords: %s'),count($resultList))] = $keyworddisplay;
            break;
/*        case 'publications_titles':
            $pubdisplay = "<ul>";
            foreach ($resultList as $publication) {
                $pubdisplay .= '<li>';
                $pubdisplay .= anchor('publications/show/'.$publication->pub_id,$publication->title);
                $pubdisplay .= '</li>';
            }
            $pubdisplay .= "</ul>";
            $resulttabs[sprintf(__('Publications: %s'),count($resultList))] = $pubdisplay;
            //option below displays the publciations as list, but I don't want the headers and everything... maybe make an option in that view that 
            //determines whether headers are displayed?
            //$resulttabs[sprintf(__('Publications: %s'),count($resultList))] = $this->load->view('publications/list', array('publications'=>$resultList), true);
            break;
        case 'publications_bibtex':
            $pubdisplay = "<ul>";
            foreach ($resultList as $publication) {
                $pubdisplay .= '<li>';
                $pubdisplay .= anchor('publications/show/'.$publication->pub_id,$publication->bibtex_id.': '.$publication->title);
                $pubdisplay .= '</li>';
            }
            $pubdisplay .= "</ul>";
            $resulttabs[sprintf(__('Citation ID: %s'),count($resultList))] = $pubdisplay;
            break;
        case 'publications_notes':
            $pubdisplay = "<ul>";
            foreach ($resultList as $publication) {
                $pubdisplay .= '<li>';
                $pubdisplay .= anchor('publications/show/'.$publication->pub_id,$publication->title);
                $pubdisplay .= '</li>';
            }
            $pubdisplay .= "</ul>";
            $resulttabs[sprintf(__('Notes: %s'),count($resultList))] = $pubdisplay;
            break;
  */
        default:
            break;
    }
  
}


//show all relevant result tabs
foreach ($resulttabs as $title=>$tabdisplay) {
    echo '<div class="header">'.sprintf(__('%s matches'), $title).'</div>';
    echo $tabdisplay;
}

$types = array();
$resultHeaders = array();
$result_div_ids = array();
foreach ($searchresults as $title=>$content)
{
  if (substr($title, 0, strlen("publication")) == "publication")
  {
    $type = substr($title, strlen("publication") + 2);
    $types[] = $type;
    $resultHeaders[$type] = ucfirst($type)." (".count($content).")";
    $result_div_ids[$type] = "result_".$type;
    $result_views[$type] = $this->load->view('publications/list', array('publications' => $content, 'order' => 'year'), true);
  }
}

if (count($types) > 0)
{
  echo "<div class='header'>".__('Publication matches')."</div>\n";
  $cells = "";
  $divs  = "";
  $hideall = "";
  foreach ($types as $type)
  {
    $cells .= "<td><div class='header'><a onclick=\"";
    foreach ($types as $type2)
    {
      if ($type2 == $type)
        $cells .= $this->ajax->show($result_div_ids[$type2])."; ";
      else
        $cells .= $this->ajax->hide($result_div_ids[$type2])."; ";
    }
    
    $cells .= "\">".$resultHeaders[$type]."</a></div></td>\n";
    $divs .= "<div id='".$result_div_ids[$type]."'>\n".$result_views[$type]."\n</div>\n\n";
    $hideall .= $this->ajax->hide($result_div_ids[$type])."; ";
    
  }
  $showfirst = $this->ajax->show($result_div_ids[$types[0]])."; ";
?>  
  <table>
    <tr>
<?php
    echo $cells;
?>
    </tr>
  </table>
<?php
  echo $divs;
  echo "<script>".$hideall.$showfirst."</script>";
} else { //no publication results
    if (count($resulttabs)==0)
    {
        echo "<div class='message'>".sprintf(__('No search results found for query: %s'), "<b>".htmlentities($query,ENT_QUOTES, 'utf-8')."</b>")."</div>\n";
    }
    else
    {
        echo "<div class='message'>".sprintf(__('Search results for query: %s'), "<b>".htmlentities($query,ENT_QUOTES, 'utf-8')."</b>")."</div>\n";
    } 
}
/*
$content['publications']    = $this->publication_db->getForTopic('1',$order);
        $content['order'] = $order;
        
        $output = $this->load->view('header', $headerdata, true);
        $output .= $this->load->view('publications/list', $content, true);
        */
?>