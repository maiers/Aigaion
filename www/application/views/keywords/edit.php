<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
  $keywordfields = array('keyword' => __('Keyword'));
  $formAttributes = array('ID' => 'keyword_'.$keyword->keyword_id.'_edit');
?>
<div class='keyword'>
  <div class='header'><?php 
    switch ($edit_type) {
      case 'new':
        echo __('New Keyword');
        break;
      case 'edit':
      default:
        echo __('Edit Keyword');
        break;
    }
    ?>
    </div>
<?php
  //open the edit form
  echo form_open('keywords/commit', $formAttributes)."\n";
  echo form_hidden('edit_type',   $edit_type)."\n";
  echo form_hidden('keyword_id',   $keyword->keyword_id)."\n";
  echo form_hidden('formname','keyword');
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
        <div class='errormessage'><?php echo $review; ?></div>
      </td>
    </tr>
<?php
    endif;
    foreach ($keywordfields as $field=>$display):
?>
    <tr>
      <td valign='top'><?php echo $display; ?>:</td>
      <td valign='top'><?php echo form_input(array('name' => $field, 'id' => $field, 'size' => '45', 'alt' => $field), $keyword->$field);?></td>
    </tr>
<?php
    endforeach;
?>
  </table>
<?php
if ($edit_type=='edit') {
  echo form_submit('keyword_submit', __('Change'))."\n";
} else {
  echo form_submit('keyword_submit', __('Add'))."\n";
}
  echo form_close()."\n";
if ($edit_type=='edit') {
  echo form_open('keywords/single/'.$keyword->keyword_id);
} else {
  echo form_open('');
}
  echo form_submit('Cancel', __('Cancel'));
  echo form_close()."\n";
?>
</div>