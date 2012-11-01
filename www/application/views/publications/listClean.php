<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

//first we get all necessary values required for displaying
  $userlogin  = getUserLogin();

  //retrieve the publication summary and list stype preferences (author first or title first etc)
  $summarystyle = $userlogin->getPreference('summarystyle');
  $liststyle    = $userlogin->getPreference('liststyle');


  //here the output starts
  echo "<div class='publication_list'>\n";

  $b_even = true;
  $subheader = '';
  $subsubheader = '';
  $subheaderyear = '';
  $pubno = 0;
  foreach ($publications as $publication)
  {
    $pubno++;
    if (isset($multipage) && ($multipage == True)) {
        if (($currentpage*$liststyle > $pubno) || (($currentpage+1)*$liststyle < $pubno))
            continue;
    }
    if ($publication!=null) {
      $b_even = !$b_even;
    if ($b_even)
      $even = 'even';
    else
      $even = 'odd';

    //check whether we should display a new header/subheader, depending on the $order parameter
    switch ($order) {
      case 'year':
        $newsubheader = $publication->actualyear;
        if ($newsubheader!=$subheader) {
          $subheader = $newsubheader;
          echo '<div><br/></div><div class="header"><b>'.$subheader.'</b></div><div></div>';
        }
        break;
      case 'title':
        $newsubheader = "";
        if (strlen($publication->cleantitle)>0)
            $newsubheader = $publication->cleantitle[0];
        if ($newsubheader!=$subheader) {
          $subheader = $newsubheader;
          echo '<div><br/></div><div class="header">'.strtoupper($subheader).'</div><div><br/></div>';
        }
        break;
      case 'author':
        $newsubheader = "";
        if (strlen($publication->cleanauthor)>0)
            $newsubheader = $publication->cleanauthor[0];
        if ($newsubheader!=$subheader) {
          $subheader = $newsubheader;
          echo '<div><br/></div><div class="header">'.strtoupper($subheader).'</div><div><br/></div>';
        }
        break;
      case 'type':
        $newsubheader = $publication->pub_type;
        if ($newsubheader!=$subheader) {
          $subheader = $newsubheader;
          if ($publication->pub_type!='Article')
            echo '<div><br/></div><div class="header">'.sprintf(__('Publications of type %s'),$subheader).'</div><div><br/></div>';
        }
        if ($publication->pub_type=='Article') {
            $newsubsubheader = $publication->cleanjournal;
            if ($newsubsubheader!=$subsubheader) {
              $subsubheader = $newsubsubheader;
              echo '<div><br/></div><div class="header">'.$publication->journal.'</div><div><br/></div>';
            }
        } else {
            $newsubsubheader = $publication->actualyear;
            if ($newsubsubheader!=$subsubheader) {
              $subsubheader = $newsubsubheader;
              echo '<div><br/></div><div class="header">'.$subsubheader.'</div><div><br/></div>';
            }
        }
        break;
      case 'msc':
        $newsubheader = $publication->pub_type;
        if ($newsubheader!=$subheader) {
          $subheader = $newsubheader;
          if ($publication->pub_type == 'Mastersthesis')
            echo '<div><br/></div><div class="header">'.__('Master theses').'</div>';
        }
		$newsubheaderyear = $publication->actualyear;
        if ($newsubheaderyear!=$subheaderyear) {
          $subheaderyear = $newsubheaderyear;
          echo '<div><br/></div><div class="header">'.$subheaderyear.'</div><div></div>';
        }
        break;
      case 'recent':
        break;
      default:
        break;
    }

    $summaryfields = getPublicationSummaryFieldArray($publication->pub_type);

echo "
<div class='publication_summary ".$even."' id='publicationsummary".$publication->pub_id."'>";
?>
<table width='100%'>
  <tr>
    <td style="padding:0.2cm 0cm 0.0cm 0.2cm;">
<?php
$displayTitle = $publication->title;
$displayJournal = $publication->journal;

//remove braces in list display
if (strpos($displayTitle,'$')===false) {
  $displayTitle = str_replace(array('{','}'),'',$displayTitle);
}

$num_authors    = count($publication->authors);

if ($summarystyle == 'title') {
    echo "<span class='title'>".anchor('publications/show/'.$publication->pub_id, $displayTitle, array('title' => __('View publication details')))."</span>";
}

$current_author = 1;

foreach ($publication->authors as $author)
{
  if (($current_author == $num_authors) & ($num_authors > 1)) {
    echo " and ";
  }
  else if ($current_author>1 || ($summarystyle == 'title')) {
    echo ", ";
  }
  echo  "<span class='author'>".$author->getName()."</span>";
  $current_author++;
}

if ($summarystyle == 'author') {
    if ($num_authors > 0) {
        echo ', ';
    }
    echo "<span class='title'>".$displayTitle."</span>";
}


foreach ($summaryfields as $key => $prefix) {
  if ($key == 'pages') {
    $pages = "";
    if (($publication->firstpage != "0") || ($publication->lastpage != "0")) {
      if ($publication->firstpage != "0") {
        $pages = $publication->firstpage;
      }
      if (($publication->firstpage != $publication->lastpage)&& ($publication->lastpage != "0") && ($publication->lastpage != "")) {
        if ($pages != "") {
            $pages .= "-";
        }
        $pages .= $publication->lastpage;
      }
    }
    $val = $pages;
  } else {

    $val = str_replace('{', '', str_replace('}','',utf8_trim($publication->getFieldValue($key))));

  }
  $postfix='';
  if (is_array($prefix)) {
    $postfix = $prefix[1];
    $prefix = $prefix[0];
  }
  if ($val) {
    echo $prefix.$val.$postfix;
  }
}
?>
</td><td style="padding:0.2cm 0cm 0cm 0.5cm; text-align:left; width: 85px">

<?php
echo " <span>".anchor('export/publication/'.$publication->pub_id.'/bibtex', 'bibtex', array('title' => 'bibtex'))."</span> ";
echo " <span>".anchor('export/publication/'.$publication->pub_id.'/ris', 'ris', array('title' => 'ris'))."</span> ";

$attachments = $publication->getAttachments();
if (count($attachments) != 0)
{
    if ($attachments[0]->isremote) {
        echo "<a href='".prep_url($attachments[0]->location)."' target='_blank'><img class='large_icon' title='".sprintf(__('Download %s'),htmlentities($attachments[0]->name,ENT_QUOTES, 'utf-8'))."' src='".getIconUrl("attachment_html.gif")."'/></a>\n";
    } else {
        $iconUrl = getIconUrl("attachment.gif");
        //might give problems if location is something containing UFT8 higher characters! (stringfunctions)
        //however, internal file names were created using transliteration, so this is not a problem
        $extension=strtolower(substr(strrchr($attachments[0]->location,"."),1));
        if (iconExists("attachment_".$extension.".gif")) {
            $iconUrl = getIconUrl("attachment_".$extension.".gif");
        }
        $params = array('title'=> sprintf(__('Download %s'), $attachments[0]->name));
        if ($userlogin->getPreference('newwindowforatt')=='TRUE')
            $params['target'] = '_blank';
        echo anchor('attachments/single/'.$attachments[0]->att_id,"<img class='large_icon' style='border: none' src='".$iconUrl."'/>" ,$params)."\n";
    }
}
echo"</td></tr>";
echo "</table></div>"; //end of publication_summary div
    }
  }
?>
</div>