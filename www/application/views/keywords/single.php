<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
$userlogin = getUserLogin();
?>
<div class='keyword'>
<?php
  if ($userlogin->hasRights('publication_edit'))
{
?>
  <div class='optionbox'><?php echo "[".anchor('keywords/delete/'.$keyword->keyword_id, __('delete'), array('title' => __('Delete this keyword')))."]&nbsp[".anchor('keywords/edit/'.$keyword->keyword_id, __('edit'), array('title' => __('Edit this keyword')))."]"; ?>
  </div>
<?php
}  
?>
  <div class='header'><?php echo $keyword->keyword ?></div>
<table width='100%'>
<tr>
    <td>
<?php 
echo "<div style='border:1px solid black;padding-right:0.2em;margin:0.2em;'>
<ul>
";
    if ($userlogin->hasRights('bookmarklist')) {
      echo '<li><nobr>['.anchor('bookmarklist/addkeyword/'.$keyword->keyword_id,__('BookmarkAll')).']</nobr></li>
<li><nobr>['.anchor('bookmarklist/removekeyword/'.$keyword->keyword_id,__('UnBookmarkAll')).']</nobr></li>';
    }
echo  "
</ul>
</div>";

if ($userlogin->hasRights("publication_edit")) {
    $CI = &get_instance();
    
    $similar = $CI->keyword_db->getSimilarKeywordsByID($keyword->keyword_id);
    if (count($similar)>0) {
        echo "<div class='message'>".__('Found keywords with very similar names. You can choose to merge the following keywords with this keyword by clicking on the merge link.')."<br/>\n";
        foreach ($similar as $simkeyword) {
            echo anchor('keywords/single/'.$simkeyword->keyword_id, $simkeyword->keyword, array('title' => __('Click to show details')))."\n";
		    echo '('.anchor('keywords/merge/'.$keyword->keyword_id.'/'.$simkeyword->keyword_id, 'merge', array('title' => __('Click to merge'))).")<br/>\n";
		}
		echo "</div>\n";
    }
}

echo "<div id='tagcloud'>\n";
if (isset($relatedKeywords))
{
  echo "<p class=header2>".__('Related keywords').":</p>\n";
  $keywordContent['keywordList'] = $relatedKeywords;
  $keywordContent['isCloud'] = true;
  echo $this->load->view('keywords/list_items', $keywordContent, true);
}
echo "</div>\n"; //tagcloud
?>
    </td>
</tr>
</table>
  <br/>
</div>