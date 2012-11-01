<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
?>
<rss version="2.0">
   <channel>
<title><?php 
        if (getConfigurationSetting('WINDOW_TITLE')!='')
            echo getConfigurationSetting('WINDOW_TITLE').' - '; 
        ?> Public RSS - Aigaion 2.0
</title>
<description><?php 
        if (getConfigurationSetting('WINDOW_TITLE')!='')
            echo getConfigurationSetting('WINDOW_TITLE').' - '; 
        ?> Public RSS - Aigaion 2.0
</description>
<link><?php  echo site_url(''); ?></link>
<copyright>Aigaion.nl</copyright>
<?php
$this->load->helper('url');
foreach ($publications as $publication)
{
    $title = htmlspecialchars($publication->title,ENT_NOQUOTES,'UTF-8');
    $summary = "";
    $current_author = 1;
    $num_authors = count($publication->authors);
    foreach ($publication->authors as $author)
    {
      if (($current_author == $num_authors) & ($num_authors > 1)) 
      {
        $summary .= " ".__('and')." ";
      }
      else if ($current_author>1)
      {
        $summary .= ", ";
      }
    
      $summary .=  htmlspecialchars($author->getName(),ENT_NOQUOTES,'UTF-8');
      $current_author++;
    }
    $summary .= ": \"".$title."\"";
    echo "<item>
            <title>".$title."</title>
            <description>".$summary."</description>
            <link>".site_url('publications/show/'.$publication->pub_id)."</link>
          </item>
    ";
}
?>
</channel>
</rss>