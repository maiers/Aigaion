<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
$publicationfields  = getPublicationFieldArray($publication->pub_type);
$customfieldkeys    = $this->customfields_db->getCustomFieldKeys('publication');
$formAttributes     = array('ID' => 'publication_'.$publication->pub_id.'_review');
?>
<div class='publication'>
  <div class='header'>Review publication</div>
<?php
    //open the edit form
    echo form_open('publications/commit', $formAttributes)."\n";
    
    //first display form elements that are not in the publicationfields array
    echo form_hidden('edit_type',   $review['edit_type'])."\n";
    echo form_hidden('pub_id',      $publication->pub_id)."\n";
    echo form_hidden('user_id',     $publication->user_id)."\n";
    echo form_hidden('submit_type', 'review')."\n";
    echo form_hidden('pub_type',    $publication->pub_type)."\n";
    echo form_hidden('title',       $publication->title)."\n";
    
    //then display publicationfiels array elements
    foreach ($publicationfields as $key => $class):
    echo form_hidden($key,        $publication->$key)."\n";
    endforeach;
    
    //display hidden custom fields
    $customFields = $publication->getCustomFields();
    foreach ($customfieldkeys as $field_id => $field_name) {
      if (array_key_exists($field_id, $customFields)) {
        $value = $customFields[$field_id]['value'];
      }
      else {
        $value = '';
      }
      echo form_hidden('CUSTOM_FIELD_'.$field_id, $value)."\n"; 
    }
?>    
    <table class='publication_review_form' width='100%'>
<?php
    //cite id review
    if ($review['bibtex_id'] != null)
    {
      $key    = 'bibtex_id';
      $class  = 'required';
?>
      <tr>
        <td colspan = 2>
          <div class='errormessage'><?php echo $review['bibtex_id']; ?></div>
        </td>
      </tr>
      <tr>
        <td valign='top'><?php echo __('Citation');?>:</td>
        <td valign='top'><?php echo "<span title='".sprintf(__('%s field'), $class)."'>".form_input(array('name' => $key, 'id' => $key, 'size' => '45', 'alt' => $class, 'autocomplete' => 'off', 'class' => $class), $publication->bibtex_id);?></span></td>
      </tr>
<?php
    }
    else
      echo form_hidden('bibtex_id', $publication->bibtex_id)."\n";
    
    //keyword review
    if ($review['keywords'] != null)
    {
?>
      <tr>
        <td colspan = 2>
          <div class='errormessage'><?php echo $review['keywords']; ?></div>
        </td>
      </tr>
<?php
      $keywords = $publication->keywords;
      if (is_array($keywords))
        {
                $keyword_string = "";
        foreach ($keywords as $keyword)
        {
          $keyword_string .= $keyword->keyword.", ";
        }
        $keywords = substr($keyword_string, 0, -2);
      }
      else
      $keywords = "";

      $key    = 'keywords';
      $class  = 'optional';
?>
      <tr>
        <td valign='top'><?php echo __('Keywords');?>:</td>
        <td valign='top'><?php echo "<span title='".sprintf(__('%s field'), $class)."'>".form_input(array('name' => $key, 'id' => $key, 'size' => '45', 'alt' => $class, 'autocomplete' => 'off', 'class' => $class), $keywords);?></span></td>
      </tr>
<?php
    }
    else
    {
      if (is_array($publication->keywords))
      {
        $keyword_string = "";
        foreach ($publication->keywords as $keyword)
        {
          $keyword_string .= $keyword->keyword.", ";
        }
        $keywords = substr($keyword_string, 0, -2);
        echo form_hidden('keywords', $keywords)."\n";
      }
      else
      echo form_hidden('keywords', '')."\n";
    }

    //author review
    $authors = array();
    if (is_array($publication->authors))
    {
      foreach ($publication->authors as $author)
      {
        $authors[] = $author->author_id;
      }
    }

    echo form_hidden('pubform_authors', implode($authors, ","))."\n";

    //editor review
    $editors = array();
    if (is_array($publication->editors))
    {
      foreach ($publication->editors as $author)
      {
        $editors[] = $author->author_id;
      }
    }

    echo form_hidden('pubform_editors', implode($editors, ","))."\n";

?>
  </table>

<?php
  echo form_submit('publication_submit', 'Submit')."\n";
  echo form_close()."\n";
?>
</div>