<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
if (isset($isCloud) && $isCloud)
{
  echo "<ul class='tagcloud'>";
  
  //find highest keyword count
  $maxKeywordCount = 0;
  $minKeywordCount = 100;
  foreach ($keywordList as $keyword)
  {
    if (isset($keyword->count))
    {
      if ($keyword->count > $maxKeywordCount)
        $maxKeywordCount = $keyword->count;
      if ($keyword->count < $minKeywordCount)
        $minKeywordCount = $keyword->count;
    }
  }
  
  if ($minKeywordCount != $maxKeywordCount)
  {
    //set thresholds for three tag classes
    $threshold1 = $minKeywordCount + ceil(($maxKeywordCount - $minKeywordCount) / 3);
    $threshold2 = $minKeywordCount + ceil((($maxKeywordCount - $minKeywordCount) * 2) / 3);
  }
  else
    $threshold1 = $threshold2 = $maxKeywordCount + 1;
  
  //clouds use no headers.
  $useHeaders = false;
}
else
{
  $isCloud = false;
  echo "<ul class='nosymbol'>";
}

  $initial = '';
  $liClass = '';
  foreach ($keywordList as $keyword)
  {
    if ($useHeaders && ($keyword->cleankeyword != '') && (strtoupper($keyword->cleankeyword[0])!=$initial)) {
        $initial = strtoupper($keyword->cleankeyword[0]);
        echo "<li><b>".$initial."</b></li>\n";
    }
    //get li class
    if ($isCloud)
    {
      if ($keyword->count < $threshold2) {
        if ($keyword->count < $threshold1) {
          $liClass='class=t1';
        }
        else {
          $liClass='class=t2';
        }
      }
      else {
        $liClass='class=t3';
      }  
    }
    
    
    echo "  <li ".$liClass.">".anchor('keywords/single/'.$keyword->keyword_id, $keyword->keyword, array('title' => sprintf(__('All information on %s'), $keyword->cleankeyword)));
    echo "</li>\n";
  }
  
  echo "</ul>";
?>