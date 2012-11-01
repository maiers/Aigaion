<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
$authorfields = array(
	'firstname'	=>	__('First name(s)'),
	'von'		=>	__('von-part'),
	'surname'	=>	__('Last name(s)'),
	'jr'			=>	__('jr-part'),
	'email'		=>	__('Email'),
	'institute'		=>	__('Institute'),
	'url'			=>	__('URL')
);
$customfieldkeys    = $this->customfields_db->getCustomFieldKeys('author');
$formAttributes = array('ID' => 'author_'.$author->author_id.'_edit');
?>
<div class='author'>
  <div class='header'><?php
    switch ($edit_type) {
      case 'new':
        echo __('New Author');
        break;
      case 'edit':
      default:
        echo __('Edit Author');
        break;
    }
    ?>
    </div>
<?php
  //open the edit form
  echo form_open('authors/commit', $formAttributes)."\n";
  echo form_hidden('edit_type',   $edit_type)."\n";
  echo form_hidden('author_id',   $author->author_id)."\n";
  echo form_hidden('formname','author');
  if (isset($review))
    echo form_hidden('submit_type', 'review');
  else
    echo form_hidden('submit_type', 'submit')."\n";
?>
  <table class='author_edit_form' width='100%'>
<?php
    if (isset($review)):
?>
    <tr>
      <td colspan = 2>
        <div class='errormessage'><?php echo $review['author']; ?></div>
      </td>
    </tr>
<?php
    endif;
    foreach ($authorfields as $field=>$display):
?>
    <tr>
      <td valign='top'><?php echo $display; ?>:</td>
      <td valign='top'><?php echo form_input(array('name' => $field, 'id' => $field, 'size' => '45', 'alt' => $field), $author->$field);?></td>
    </tr>
<?php
    endforeach;

    //do the custom fields
    $customFields = $author->getCustomFields();
    foreach ($customfieldkeys as $field_id => $field_name) {
      if (array_key_exists($field_id, $customFields)) {
        $value = $customFields[$field_id]['value'];
      }
      else {
        $value = '';
      }
      ?>
    <tr>
      <td><?php echo $field_name; ?>:</td>
      <td><?php echo form_input(array('name' => 'CUSTOM_FIELD_'.$field_id, 'id' => 'CUSTOM_FIELD_'.$field_id, 'size' => '45'), $value); ?></td>
    </tr>
      <?php
    }


if (getConfigurationSetting('USE_AUTHOR_SYNONYMS') != 'TRUE')
{    
?>
      <tr>
        <td colspan='2' valign='top'>
          <?php 
            echo form_hidden('synonym_of',$author->synonym_of);
          ?>
        </td>
      </tr>
<?php
}
else
{
    //and add the primary_author input stuff... (if this author is not already a primary itself)
    if ($edit_type!='new' && $author->hasSynonyms())
    {
?>
      <tr>
        <td colspan='2' valign='top'>
          <?php 
            echo __('This author is a primary author with one or more synonyms').'.'; 
            echo form_hidden('synonym_of',0);
          ?>
        </td>
      </tr>
<?php
    }
    else
    {    
?>
      <tr>
        <td valign='top'><?php echo __('This author is a synonym of'); ?>:</td>
        <td valign='top'>
          <?php 
            $primaries = array();
            $primaries[0] = '';
            $prims = $this->author_db->getAllAuthors(false);
            foreach ($prims as $prim)
            {
              $primaries[$prim->author_id] = $prim->getName();
            }
            echo form_dropdown('synonym_of',$primaries,$author->synonym_of);
          ?>
        </td>
      </tr>
<?php
    }
}    
?>
  </table>
<?php
if ($edit_type=='edit') {
  echo form_submit('publication_submit', __('Change'))."\n";
} else {
  echo form_submit('publication_submit', __('Add'))."\n";
}
  echo form_close()."\n";
if ($edit_type=='edit') {
  echo form_open('authors/show/'.$author->author_id);
} else {
  echo form_open('');
}
  echo form_submit('Cancel', __('Cancel'));
  echo form_close()."\n";
?>
</div>