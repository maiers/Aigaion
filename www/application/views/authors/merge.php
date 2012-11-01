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
  <div class='header'><?php echo __('Merge authors'); ?></div>
  <?php echo __('Merges the source author with the target author. The source author will be deleted, all publications will be transferred to the target author.');

    //open the edit form
    echo form_open('authors/mergecommit', $formAttributes)."\n";
    echo form_hidden('author_id',   $author->author_id)."\n";
    echo form_hidden('simauthor_id',   $simauthor->author_id)."\n";
    echo form_hidden('formname','author');
?>
  <table>
    <tr><td>
    <table class='author_edit_form' width='100%'>
        <tr>
        <td colspan=2><p class='header2'><?php echo __('Target author');?></p></td>
        <td><p class='header2'></p></td>
        <td colspan=2><p class='header2'><?php echo __('Source author');?></p></td>
        </tr>
<?php
        foreach ($authorfields as $field=>$display):
?>
        <tr>
        <td valign='top'><?php echo $display; ?>:</td>
        <td valign='top'><?php echo form_input(array('name' => $field, 'id' => $field, 'size' => '30', 'alt' => $field), $author->$field);?></td>
        <td valign='top'><?php echo $this->ajax->button_to_function('<<', "$('".$field."').value=$('sim".$field."').value;");?></td>
        <td valign='top'><?php echo $display; ?>:</td>
        <td valign='top'><?php echo form_input(array('name' => 'sim'.$field, 'id' => 'sim'.$field, 'size' => '30', 'alt' => $field), $simauthor->$field);?></td>
        </tr>
<?php
        endforeach;
    
        //do the custom fields
        $customFields = $author->getCustomFields();
        $simCustomFields = $simauthor->getCustomFields();
        foreach ($customfieldkeys as $field_id => $field_name) {
          if (array_key_exists($field_id, $customFields)) {
            $value = $customFields[$field_id]['value'];
          }
          else {
            $value = '';
          }
          if (array_key_exists($field_id, $simCustomFields)) {
            $simValue = $simCustomFields[$field_id]['value'];
          }
          else {
            $simValue = '';
          }
?>
          <tr>
            <td valign='top'><?php echo $field_name; ?>:</td>
            <td valign='top'><?php echo form_input(array('name' => 'CUSTOM_FIELD_'.$field_id, 'id' => 'CUSTOM_FIELD_'.$field_id, 'size' => '30', 'alt' => $field_name), $value);?></td>
            <td valign='top'><?php echo $this->ajax->button_to_function('<<', "$('".'CUSTOM_FIELD_'.$field_id."').value=$('sim".'CUSTOM_FIELD_'.$field_id."').value;");?></td>
            <td valign='top'><?php echo $field_name; ?>:</td>
            <td valign='top'><?php echo form_input(array('name' => 'sim'.'CUSTOM_FIELD_'.$field_id, 'id' => 'sim'.'CUSTOM_FIELD_'.$field_id, 'size' => '30', 'alt' => $field_name), $simValue);?></td>
          </tr>
<?php
        }
        //end custom fields
        
        echo form_hidden('synonym_of',$author->synonym_of);        
?>
    </table>
    </td></tr>

    <tr><td colspan='2'>
      <?php echo __("Note that you can also choose to set the source author as a synonym of the target author, instead of fully merging them").". ".__("To do this, go to the edit page of the source author").". \n"; ?>
    </td></tr>

    <tr><td colspan='2'>
      <?php echo form_submit('merge_submit', __('Merge'))."\n"; ?>
    </td></tr>

  </table>

<?php

  echo form_close()."\n";
echo form_open('authors/show/'.$author->author_id);
echo form_submit('cancel',__('Cancel'));
echo form_close();

?>
</div>