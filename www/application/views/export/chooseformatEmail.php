<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
this view shows a form that asks you the format in which you want to export the data
It needs several view parameters:

header              Default: "Export all publications"
exportCommand       Default: "export/all/"; will be suffixed with type. May also be, e.g., "export/topic/12/"
*/
$this->load->helper('form');

if (!isset($header))$header=__('Export all publications');
if (!isset($exportCommand))$exportCommand="export/all/";
?>
<p class='header'><?php echo $header; ?></p>
<p>
  <?php echo __('Please select the format(s) in which you want to export the publications and enter the email address(es) you want to send it to:'); ?><br/>
</p>
<?php
$this->load->helper('osbib');
echo " Style: ";
$style_options = array();
$styles = LOADSTYLE::loadDir(APPPATH."include/OSBib/styles/bibliography");
foreach ($styles as $style=>$longname) {
    $style_options[$style] = $style;
}
$email_input = array(
	'name'        => 'email_address',
	'value'       => __('Input email addresses here separated by ,'),
	'size'        => '100%',
);
if(isset($recipientaddress) && $recipientaddress != -1)
{
	$email_input = array(
		'name'        => 'email_address',
		'value'       => $recipientaddress,
		'size'        => '135',
	);
}
$email_subject = array(
	'name'        => 'email_subject',
	'value'       => __('Export from Aigaion'),
	'size'        => '100%',
	'cols'        => '99',
);
$email_body = array(
	'name'        => 'email_body',
	'value'       => __('Export from Aigaion'),
	'size'        => '100%',
	'cols'        => '99',
);


echo form_open($controller);

if(MAXIMUM_ATTACHMENT_SIZE > $attachmentsize)
{
	echo '<table><tr><td>PDF </td><td>'.form_checkbox('email_pdf', 'pdf', FALSE).'</td><td>'.sprintf(__('Attachment size: %s KB'), $attachmentsize).'</td></tr>';
}
else
{
	echo '<table><tr><td>PDF </td><td>'.sprintf(__('Maximum attachment size: %s KB'), MAXIMUM_ATTACHMENT_SIZE).'</td><td>'.sprintf(__('Current attachment size: %s KB'), $attachmentsize).'</td></tr>';
}

echo '<tr><td>BibTeX </td><td>'.form_checkbox('email_bibtex', 'bibtex', FALSE).'</td></tr>';
echo '<tr><td>RIS </td><td>'.form_checkbox('email_ris', 'ris', FALSE).'</td></tr>';
echo '<tr><td>'.__('Formatted').' </td><td>'.form_checkbox('email_formatted', 'html', FALSE).'</td><td>'.form_dropdown('style',$style_options).'</td></tr>';
echo '<tr><td> Recipients &nbsp;&nbsp;&nbsp; </td><td colspan="2">'.form_input($email_input).'</td></tr>';
echo '<tr><td> Subject </td><td colspan="2">'.form_input($email_subject).'</td></tr>';
echo '<tr><td>Body </td><td colspan="2">'.form_textarea($email_body).'</td></tr>';
echo form_hidden('sort','nothing');
echo '<tr><td>'.form_submit(array('name'=>__('Formatted'),'title'=>__('Export formatted entries')),__('Export')).'</td></tr>';
echo '</table>';
echo form_close();

?>