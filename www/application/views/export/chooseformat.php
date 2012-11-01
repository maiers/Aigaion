<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
this view shows a form that asks you the format in which you want to export the data
It needs several view parameters:

header              Default: "Export all publications"
exportCommand       Default: "export/all/"; will be suffixed with type. May also be, e.g., "export/topic/12/"
*/
$this->load->helper('form');
$userlogin  = getUserLogin();

if (!isset($header))$header=__('Export all publications');
if (!isset($exportCommand))$exportCommand="export/all/";
?>
<p class='header'><?php echo $header; ?></p>
<p>
  <?php echo __('Please select the format in which you want to export the publications:')?><br/>
</p>
<?php
echo form_open($exportCommand.'bibtex');
echo "<div>".form_submit(array('name'=>'BibTeX','title'=>__('Export to BibTeX')),__('BibTeX'));
echo "</div>\n";
echo form_close();
echo '<br/>';
echo form_open($exportCommand.'ris');
echo "<div>".form_submit(array('name'=>'RIS','title'=>__('Export to RIS')),__('RIS'));
echo "</div>\n";
echo form_close();
echo '<br/>';
if ($userlogin->hasRights('export_email')) {
  echo form_open($exportCommand.'email');
  echo "<div>".form_submit(array('name'=>'E-mail','title'=>__('Export by E-mail')),__('E-mail'));
  echo "</div>\n";
  echo form_close();
}

echo "<br/><hr/>";

$this->load->helper('osbib');
echo form_open($exportCommand.'formatted');
echo "<div>".__('Format').": ";
echo form_dropdown('format',array('html'=>'HTML','rtf'=>'RTF','plain'=>'TXT'),'html');//,'sxw'=>__('Open Office')
echo " ".__('Style').": ";
$style_options = array();
$styles = LOADSTYLE::loadDir(APPPATH."include/OSBib/styles/bibliography");
foreach ($styles as $style=>$longname) {
    $style_options[$style] = $style;
}
echo form_dropdown('style',$style_options);
echo form_hidden('sort','nothing');
echo '&nbsp;'.form_submit(array('name'=>__('Formatted'),'title'=>__('Export formatted entries')),__('Export'));
echo "</div>";
echo form_close();


?>