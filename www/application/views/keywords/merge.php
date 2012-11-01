<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
  $keywordfields = array('keyword' => 'Keyword');
  $formAttributes = array('ID' => 'keyword_'.$keyword->keyword_id.'_edit');
?>
<div class='author'>
  <div class='header'><?php echo __('Merge keywords'); ?></div>
  <?php echo __('Merges the source keyword with the target keyword. The source keyword will be deleted, all publications using this keyword will use the target keyword instead.');

    //open the edit form
    echo form_open('keywords/mergecommit', $formAttributes)."\n";
    echo form_hidden('keyword_id',   $keyword->keyword_id)."\n";
    echo form_hidden('simkeyword_id',   $simkeyword->keyword_id)."\n";
    echo form_hidden('formname','keyword');
?>
  <table>
    <tr><td>
    <table class='keyword_edit_form' width='100%'>
        <tr>
        <td colspan=2><p class='header2'><?php echo __('Target keyword');?></p></td>
        <td><p class='header2'></p></td>
        <td colspan=2><p class='header2'><?php echo __('Source keyword');?></p></td>
        </tr>
<?php
        foreach ($keywordfields as $field=>$display):
?>
        <tr>
        <td valign='top'><?php echo $display; ?>:</td>
        <td valign='top'><?php echo form_input(array('name' => $field, 'id' => $field, 'size' => '30', 'alt' => $field), $keyword->$field);?></td>
        <td valign='top'><?php echo $this->ajax->button_to_function('<<', "$('".$field."').value=$('sim".$field."').value;");?></td>
        <td valign='top'><?php echo $display; ?>:</td>
        <td valign='top'><?php echo form_input(array('name' => 'sim'.$field, 'id' => 'sim'.$field, 'size' => '30', 'alt' => $field), $simkeyword->$field);?></td>
        </tr>
<?php
        endforeach;
?>
    </table>
    </td></tr>
    <tr><td colspan='2'>
      <?php echo form_submit('merge_submit', __('Merge'))."\n"; ?>
    </td></tr>
  </table>

<?php
    
echo form_close()."\n";
echo form_open('keywords/show/'.$keyword->keyword_id);
echo form_submit('cancel',__('Cancel'));
echo form_close();

?>
</div>