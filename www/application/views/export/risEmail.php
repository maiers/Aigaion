<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/**
views/export/ris

displays ris for given publications

input parameters:
nonxref: map of [id=>publication] for non-crossreffed-publications
xref: map of [id=>publication] for crossreffed-publications
header: not used here.

*/
if (!isset($header)||($header==null))$header='';

//no header
$result = '';
$this->load->helper('export');
foreach ($nonxrefs as $pub_id=>$publication) {
    $result .= getRISForPublication($publication);
}
foreach ($xrefs as $pub_id=>$publication) {
    $result .= getRISForPublication($publication);
}

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

?>