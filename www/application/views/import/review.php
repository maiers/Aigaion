<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
$publicationFields  = getFullFieldArray();
$importCount        = count($publications);
$formAttributes     = array('ID' => 'import_review');
echo form_open('import/commit',   $formAttributes)."\n";
echo form_hidden('import_count',  $importCount)."\n";
echo form_hidden('formname','import');
$mark = '';
if ($markasread) $mark = 'markasread'; //commit controller expects not a boolean, but the value 'markasread'
echo form_hidden('markasread',  $mark)."\n";
$b_even = true;

echo "<div class='publication'>\n";
echo "  <div class='header'>".__('Review publications')."</div>\n";

for ($i = 0; $i < $importCount; $i++)
{

  $b_even = !$b_even;
  if ($b_even)
  $even = 'even';
  else
  $even = 'odd';

    echo "<div class='publication_summary ".$even."' id='publicationsummary".$i."'>\n";
    echo "<table width='100%'>\n";
    //open the edit form
    echo form_hidden('pub_type_'.$i,    $publications[$i]->pub_type)."\n";
    //bibtex_id

    ?>
    <tr>
      <td colspan = 2><?php
        echo form_checkbox(array('name' => 'do_import_'.$i, 'id' => 'import_'.$i, 'value'=>'CHECKED', 'checked' => TRUE));
        echo __('Import:')." <b>".$publications[$i]->title."</b>\n"; 
        
        if ($reviews[$i]['title'] != null)
          echo "<div class='errormessage'>".$reviews[$i]['title']."</div>\n";
        ?>
        </td>
    </tr>
    <?php
    if ($reviews[$i]['bibtex_id'] != null)
    {
    ?>
    <tr>
      <td colspan = 2><div class='errormessage'><?php echo $reviews[$i]['bibtex_id'] ?></div></td>
    </tr>
    <?php
    }
    ?>
    <tr>
      <td style='width:2em;'></td>
      <td><?php echo __('Citation')." ".form_input(array('name' => 'bibtex_id_'.$i, 'id' => 'bibtex_id_'.$i, 'size' => '45'), $publications[$i]->bibtex_id); ?></td>
    </tr>
    <?php

    echo form_hidden('authorcount_'.$i,count($publications[$i]->authors));
    
    /** 
    the review form contains the following data,
    for a to-be-reviewed author j [0..nrAuthors] 
    for publication i [0..import_count]:
      author_i_j_input: the original bibtex parsed version of the input text for this author as hidden field
      author_i_j_alternative: a value normally determined by a radio button selection. 
           A value of -1 means: create new author from input text. (if no alternatives at all, -1 is used)
           If this radio button has another value it determines the existing author that should be used.
    */
    if ($reviews[$i]['authors'] != null) //each item consists of an array A with A[0] a review message, and A[1] an array of the similar author ID
    {

      ?>
      <tr>
        <td></td><td valign='top'><br/><b><?php echo __('Choose alternative authors:'); ?></b></td>
      </tr>
      <tr>
        <td></td><td>
          <?php
          $j = 0;
          foreach ($publications[$i]->authors as $author)
          {
            echo form_hidden('author_'.$i.'_'.$j.'_inputfirst',$author->firstname);
            echo form_hidden('author_'.$i.'_'.$j.'_inputvon',$author->von);
            echo form_hidden('author_'.$i.'_'.$j.'_inputlast',$author->surname);
            echo form_hidden('author_'.$i.'_'.$j.'_inputjr',$author->jr);
            $similar_authors = $reviews[$i]['authors'][1][$j];
            if (count($similar_authors)!=0 ) {
                echo '<br/>'.sprintf(__('Options for import-author "%s":'), $author->getName('lvf')).'<br/>';
                $exactMatch = false;
                $alternatives = '';
                $radiocheck = false;
                foreach ($similar_authors as $sa_id) {
                  $sa = $this->author_db->getByID($sa_id);
                  $feedback = '['.__('from database').']';
                  if ($sa->getName('lvf') == $author->getName('lvf')) { //exact match!
                    $exactMatch = True;
                    $feedback = '['.__('keep').']';
                    $radiocheck = true;
                  }
                  $alternatives .= form_radio(array('name'        => 'author_'.$i.'_'.$j.'_alternative',
                                        'id'          => 'author_'.$i.'_'.$j.'_alternative',
                                        'title'       => __('select to use similar author found in database'),
                                        'value'       => $sa_id,
                                        'checked'     => $radiocheck
                                       )).$sa->getName('lvf').' '.$feedback.'<br/>';
                }
                if (!$exactMatch)
                  echo form_radio(array('name'        => 'author_'.$i.'_'.$j.'_alternative',
                                        'id'          => 'author_'.$i.'_'.$j.'_alternative',
                                        'title'       => __('select to use author as found in BibTeX'),
                                        'value'       => '-1',
                                        'checked'     => TRUE
                                       )).$author->getName('lvf').' [add new]<br/>';
                echo $alternatives;
            } else {
                //no similar authors. Either we have ONE exact match, OR we have NO macth at all
                $exactMatchingAuthor = $this->author_db->getByExactName($author->firstname, $author->von, $author->surname, $author->jr);
                if ($exactMatchingAuthor == null) {
                    echo form_hidden('author_'.$i.'_'.$j.'_alternative',-1);
                } else {
                    echo form_hidden('author_'.$i.'_'.$j.'_alternative',$exactMatchingAuthor->author_id);
                }
            }
            $j++;
          }

          ?>
        </td>
      </tr>
      <?php
    }
    else
    {
      //authors 
      //no review message, i.e. either exact matches or new authors. proceed accordingly to build up hidden fields.
          $j = 0;
          foreach ($publications[$i]->authors as $author)
          {
            echo form_hidden('author_'.$i.'_'.$j.'_inputfirst',$author->firstname);
            echo form_hidden('author_'.$i.'_'.$j.'_inputvon',$author->von);
            echo form_hidden('author_'.$i.'_'.$j.'_inputlast',$author->surname);
            echo form_hidden('author_'.$i.'_'.$j.'_inputjr',$author->jr);
            //no similar authors. Either we have ONE exact match, OR we have NO macth at all
            $exactMatchingAuthor = $this->author_db->getByExactName($author->firstname, $author->von, $author->surname, $author->jr);
            if ($exactMatchingAuthor == null) {
                echo form_hidden('author_'.$i.'_'.$j.'_alternative',-1);
            } else {
                echo form_hidden('author_'.$i.'_'.$j.'_alternative',$exactMatchingAuthor->author_id);
            }
            $j++;
          }
    }

    echo form_hidden('editorcount_'.$i,count($publications[$i]->editors));
    
    /** 
    the review form contains the following data,
    for a to-be-reviewed editor j [0..nrEditors] 
    for publication i [0..import_count]:
      editor_i_j_input: the original bibtex parsed version of the input text for this editor as hidden field
      editor_i_j_alternative: a value normally determined by a radio button selection. 
           A value of -1 means: create new editor from input text. (if no alternatives at all, -1 is used)
           If this radio button has another value it determines the existing editor that should be used.
    */
    if ($reviews[$i]['editors'] != null) //each item consists of an array A with A[0] a review message, and A[1] an array of the similar author ID
    {

      ?>
      <tr>
        <td></td><td valign='top'><br/><b><?php echo __('Choose alternative editors:')?></b></td>
      </tr>
      <tr>
        <td></td><td>
          <?php
          $j = 0;
          foreach ($publications[$i]->editors as $editor)
          {
            echo form_hidden('editor_'.$i.'_'.$j.'_inputfirst',$editor->firstname);
            echo form_hidden('editor_'.$i.'_'.$j.'_inputvon',$editor->von);
            echo form_hidden('editor_'.$i.'_'.$j.'_inputlast',$editor->surname);
            echo form_hidden('editor_'.$i.'_'.$j.'_inputjr',$editor->jr);
            $similar_editors = $reviews[$i]['editors'][1][$j];
            if (count($similar_editors)!=0 ) {
                echo '<br/>Options for import-editor '.$editor->getName('lvf').':<br/>';
                $exactMatch = false;
                $alternatives = '';
                $radiocheck = false;
                foreach ($similar_editors as $sa_id) {
                  $sa = $this->author_db->getByID($sa_id);
                  $feedback = '['.__('from database').']';
                  if ($sa->getName('lvf') == $editor->getName('lvf')) { //exact match!
                    $exactMatch = True;
                    $feedback = '['.__('keep').']';
                    $radiocheck = true;
                  }
                  $alternatives .= form_radio(array('name'        => 'editor_'.$i.'_'.$j.'_alternative',
                                        'id'          => 'editor_'.$i.'_'.$j.'_alternative',
                                        'title'       => __('select to use similar editor found in database'),
                                        'value'       => $sa_id,
                                        'checked'     => $radiocheck
                                       )).$sa->getName('lvf').' '.$feedback.'<br/>';
                }
                if (!$exactMatch)
                  echo form_radio(array('name'        => 'editor_'.$i.'_'.$j.'_alternative',
                                        'id'          => 'editor_'.$i.'_'.$j.'_alternative',
                                        'title'       => __('select to use editor as found in BibTeX'),
                                        'value'       => '-1',
                                        'checked'     => TRUE
                                       )).$editor->getName('lvf').' [add new]<br/>';
                echo $alternatives;
            } else {
                //no similar editors. Either we have ONE exact match, OR we have NO macth at all
                $exactMatchingEditor = $this->author_db->getByExactName($editor->firstname, $editor->von, $editor->surname, $editor->jr);
                if ($exactMatchingEditor == null) {
                    echo form_hidden('editor_'.$i.'_'.$j.'_alternative',-1);
                } else {
                    echo form_hidden('editor_'.$i.'_'.$j.'_alternative',$exactMatchingEditor->author_id);
                }
            }
            $j++;
          }

          ?>
        </td>
      </tr>
      <?php
    }
    else
    {
      //editor 
      //no review message, i.e. either exact matches or new editors. proceed accordingly to build up hidden fields.
          $j = 0;
          foreach ($publications[$i]->editors as $editor)
          {
            echo form_hidden('editor_'.$i.'_'.$j.'_inputfirst',$editor->firstname);
            echo form_hidden('editor_'.$i.'_'.$j.'_inputvon',$editor->von);
            echo form_hidden('editor_'.$i.'_'.$j.'_inputlast',$editor->surname);
            echo form_hidden('editor_'.$i.'_'.$j.'_inputjr',$editor->jr);
            //no similar editors. Either we have ONE exact match, OR we have NO macth at all
            $exactMatchingEditor = $this->author_db->getByExactName($editor->firstname, $editor->von, $editor->surname, $editor->jr);
            if ($exactMatchingEditor == null) {
                echo form_hidden('editor_'.$i.'_'.$j.'_alternative',-1);
            } else {
                echo form_hidden('editor_'.$i.'_'.$j.'_alternative',$exactMatchingEditor->author_id);
            }
            $j++;
          }
    }



    if ($reviews[$i]['keywords'] != null)
    {
      $keywords = $publications[$i]->keywords;
      $keyword_string = "";
      if (is_array($keywords))
      foreach ($keywords as $keyword)
      {
        $keyword_string .= $keyword->keyword.", ";
      }
      $keywords = $keyword_string;

      ?>
      <tr>
        <td colspan = 2><div class='errormessage'><?php echo $reviews[$i]['keywords'] ?></div></td>
      </tr>
      <tr>
        <td valign='top'><?php __('Keywords')?>:</td>
        <td valign='top'>
          <?php
          echo form_input(array('name' => 'keywords_'.$i, 'id' => 'keywords_'.$i, 'size' => '45', 'alt' => 'keywords', 'autocomplete' => 'off'), $keywords);
          echo "<div name='keyword_autocomplete_".$i."' id='keyword_autocomplete_".$i."' class='autocomplete'></div>\n";
          echo $this->ajax->auto_complete_field('keywords_'.$i, $options = array('url' => base_url().'index.php/keywords/li_keywords/keywords_'.$i, 'update' => 'keyword_autocomplete_'.$i, 'tokens'=> ',', 'frequency' => '0.01'))."\n";
          ?>
        </td>
      </tr>
    <?php
    }
    foreach ($publicationFields as $field)
    {
      if ($field =="month")
      {
        echo form_hidden($field."_".$i,     formatMonthBibtexForEdit($publications[$i]->$field))."\n";
      } else if ($field != "keywords") {
        echo form_hidden($field."_".$i,     $publications[$i]->$field)."\n";
      }
      else if ($reviews[$i]['keywords'] == null)
      {
        if (is_array($publications[$i]->keywords))
        {
          $keywords = $publications[$i]->keywords;
          $keyword_string = "";
          foreach ($keywords as $keyword)
          {
            $keyword_string .= $keyword->keyword.", ";
          }
          $keywords = substr($keyword_string, 0, -2);
          echo form_hidden('keywords_'.$i, $keywords)."\n";
        }
        else
        echo form_hidden('keywords_'.$i, $publications[$i]->keywords)."\n";
      }
    }
    echo form_hidden("actualyear_".$i,     $publications[$i]->actualyear)."\n"; //don't forget to remember this one... as during import, actualyear is determined in parser_import.php
    echo form_hidden("old_bibtex_id_".$i,     $publications[$i]->bibtex_id)."\n"; //don't forget to remember this one... when the bibtexID is changed in the edit box, we need to know whether we should change any crossrefs (later on in controller import.php#commit() )

    ?>
  </table>
  </div>
  <?php

} //end for each publication

echo form_submit('publication_submit', __('Import'))."\n";
echo form_close()."\n";
?>
</div>
