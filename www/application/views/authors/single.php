<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
$userlogin = getUserLogin();
?>
<div class='author'>
<?php
if ($userlogin->hasRights('publication_edit'))
{
?>
  <?php 
  echo "<div class='optionbox'>";
  if ( (getConfigurationSetting('USE_AUTHOR_SYNONYMS') == 'TRUE') && $author->synonym_of == '0')
  {
    echo "[".anchor('authors/addsynonym/'.$author->author_id, __('add synonym'), array('title' => __('Add synonym for this author')))."]&nbsp";
  }
  echo "[".anchor('authors/delete/'.$author->author_id, __('delete'), array('title' => __('Delete this author')))."]&nbsp[".anchor('authors/edit/'.$author->author_id, __('edit'), array('title' => __('Edit this author')))."]</div>"; 
  ?>
<?php
}   
?>
  <div class='header'><?php echo $author->getName() ?></div>
<table width='100%'>
<tr>
    <td  width='100%'>
      <table class='author_details'>
<?php
      $authorfields = array('firstname'=>__('First name(s)'), 'von'=>__('von-part'), 'surname'=>__('Last name(s)'), 'jr'=>__('jr-part'), 'email'=>__('Email'), 'institute'=>__('Institute'));
      foreach ($authorfields as $field=>$display)
      {
        if (trim($author->$field) != '')
        {
?>
          <tr>
            <td valign='top'><?php echo $display; ?>:</td>
            <td valign='top'><?php echo $author->$field; ?></td>
          </tr>
<?php
        }
      }
      if ($author->url != '') {
        $this->load->helper('utf8');
        $urlname = prep_url($author->url);
        if (utf8_strlen($urlname)>21) {
            $urlname = utf8_substr($urlname,0,30)."...";
        }
        echo "<tr><td>".__('URL').":</td><td><a title='".prep_url($author->url)."' href='".prep_url($author->url)."' class='open_extern'>".$urlname."</a></td></tr>\n";
      }
    $customfields = $author->getCustomFields();
    if (is_array($customfields))
    {
      foreach ($customfields as $customfield)
      {
          ?>
        <tr>
          <td valign='top'><?php echo $customfield['fieldname']; ?>: </td>
          <td valign='top'><?php echo $customfield['value']; ?> </td>
        </tr>
            <?php
      }
    }
?>
      </table>
<?php
    if (getConfigurationSetting('USE_AUTHOR_SYNONYMS') == 'TRUE')
    {
      //check if this is a synonym of another author
      if ($author->synonym_of != '0') {
        echo "<div class='message'>".__('This author entry is a synonym of another author entry.').' '.sprintf(__('Click %s to go to the main entry to see any additional publications of this author'), anchor('authors/show/'.$author->synonym_of, "here", array('title' => __('Click to show details'))))."</div>\n";
      }
      else {
        $synonyms = $author->getSynonyms();
        if (count($synonyms) > 0)
        {
          echo "<div class='message'>".__('This author is also known under the following synonyms').":<br/>\n<ul>";
          foreach ($synonyms as $synonym)
          {
            echo "<li>".anchor('authors/show/'.$synonym->author_id, $synonym->getName(), array('title' => __('Click to show details')));
            if ($synonym->institute != '')
              echo ", ".$synonym->institute;
            echo ' ['.anchor('authors/merge/'.$author->author_id.'/'.$synonym->author_id, 'merge', array('title' => __('Click to merge')))."]".'&nbsp;['.anchor('authors/setprimary/'.$synonym->author_id, 'set as primary', array('title' => __('Click to set this one as primary')))."]</li>\n";
          }
          echo "</ul>\n";
          echo ' ('.__('All publications of this author under all names are included in the publication list below').')';
          echo "</div>\n";
        }       
      }
    }
?>
    </td>
    <td>
<?php 
    if ($userlogin->hasRights('bookmarklist')) {
      echo '<div style="border:1px solid black;padding-right:0.2em;margin:0.2em;">';
      echo "<ul>";
      echo  '<li><nobr>['
           .anchor('bookmarklist/addauthor/'.$author->author_id,__('BookmarkAll'))
           .']</nobr></li><li><nobr>['
           .anchor('bookmarklist/removeauthor/'.$author->author_id,__('UnBookmarkAll')).']</nobr></li>';
      echo  "</ul>";
    }
//echo  "<li><nobr>["
//      .anchor('export/author/'.$author->author_id,__('Export'))."]</nobr></li>

echo '</div>';
?>
    </td>
</tr>
</table>
<?php
if ($userlogin->hasRights("publication_edit")) {
    
    $similar = $author->getSimilarAuthors();
    if (count($similar)>0) {
        echo "<div class='message'>".__('Found authors with very similar names. You can choose to merge the following authors with this author by clicking on the merge link.')."<br/>\n";
        foreach ($similar as $simauth) {
            echo anchor('authors/show/'.$simauth->author_id, $simauth->getName(), array('title' => __('Click to show details')))."\n";
		    echo '('.anchor('authors/merge/'.$author->author_id.'/'.$simauth->author_id, 'merge', array('title' => __('Click to merge'))).")<br/>\n";
		}
		echo "</div>\n";
    }
}
?>

  <br/>
</div>
<?php
echo "<div id='tagcloud'>\n";
$keywords = $author->getKeywords();
if (sizeof($keywords) > 0)
{
  echo "<p class=header2>".__('Keywords').":</p>\n";
  $keywordContent['keywordList'] = $keywords;
  $keywordContent['isCloud'] = true;
  echo $this->load->view('keywords/list_items', $keywordContent, true);
}
echo "</div>\n"; //tagcloud
?>