<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php

$importTypes = $this->import_lib->getAvailableImportTypes();


if (!isset($content)||($content==null)) 
{
   $content = '';
}
?>
<div class='publication'>
  <div class='header'><?php echo __('Import publications'); ?></div>
  <p>
    <?php echo sprintf(__('Paste the entries (%s) to import in the text area below and then press "%s"'), implode(', ',$importTypes), __('Import'));?>. </p>
<?php
  //open the edit form
  $formAttributes     = array('ID' => 'import_form');
  echo form_open_multipart('import/submit', $formAttributes)."\n";
  echo form_hidden('submit_type', 'submit')."\n";
  echo form_hidden('formname','import');
?>

  <table class='publication_edit_form' width='100%'>
    <tr>
      <td>
<?php
        echo form_textarea(array('name' => 'import_data', 'id' => 'import_data', 'rows' => '20', 'cols' => '60', 'value'=>$content));
?>
      </td>      
    </tr>
    <tr>
      <td>
<?php    
    echo __('When you upload a file for import (below), any data in the import form above will be discarded.');
    echo '<br/>';
    echo form_upload(array('name'=>'import_file', 'id'=>'import_file'));
?>
      </td>      
    </tr>
  </table>
<?php
  $importTypes["auto"] = "auto";
  echo '<span title="'.__('Select the format of the data entered in the form above, or auto to let Aigaion automatically detect the format.').'">'.__('Format:')
       .'&nbsp;'.form_dropdown('format',$importTypes,'auto')."</span>\n"
       .'<br/>'.form_checkbox('markasread','markasread',False).'&nbsp;'.__('Mark imported entries as read.')
       ."<br/>".form_checkbox('noreview','noreview',False).'&nbsp;'.__('Do not review entries.')
       ."<br/>".form_submit('publication_submit', __('Import'));
       
  echo form_close()."\n";
?>
</div>