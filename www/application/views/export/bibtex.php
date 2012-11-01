<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/**
views/export/bibtex

displays bibtex for given publications

input parameters:
nonxref: map of [id=>publication] for non-crossreffed-publications
xref: map of [id=>publication] for crossreffed-publications

*/
if (!isset($header)||($header==null))$header='';

$result = "
%".sprintf(__('Aigaion2 BibTeX export from %s'), getConfigurationSetting("WINDOW_TITLE"))."
%".date('l d F Y h:i:s A')."
".$header."
";


$this->load->helper('export');
foreach ($nonxrefs as $pub_id=>$publication) {
    $result .= getBibtexForPublication($publication)."\n";
}
if (count($xrefs)>0) $result .= "\n\n".__('crossreferenced publications').": \n";
foreach ($xrefs as $pub_id=>$publication) {
    $result .= getBibtexForPublication($publication)."\n";
}
$userlogin = getUserLogin();
if ($userlogin->getPreference('exportinbrowser')=='TRUE') {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  </head>        
  <body>
    <pre>
<?php
    echo $result;
?>
    </pre>
  </body>
</html>
<?php
} else { 
    // Load the download helper and send the file to your desktop
    $this->load->helper('download');
    //how to tell browser that encoding is utf8? it SEEMS the browser understands all by itself. If not, we should 
    //introduce a 3rd param for force_download, which takes care of the utf 8 charset somehow
    force_download(AIGAION_DB_NAME."_export_".date("Y_m_d").'.bib', $result);
}

?>