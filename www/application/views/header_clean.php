<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php $title; ?></title>
    <link href="<?php echo getCssUrl("styling.css"); ?>"     rel="stylesheet" type="text/css" media="screen,projection,tv" />
    <link href="<?php echo getCssUrl("positioning.css"); ?>"     rel="stylesheet" type="text/css" media="screen,projection,tv" />
<?php
    //view parameter: the javascripts that should be linked
    if (!isset($javascripts))
      $javascripts = array();
    elseif (!is_array($javascripts))
      $javascripts = array($javascripts);
    foreach ($javascripts as $jsName):
?>
    <script type="text/javascript" src="<?php echo AIGAION_WEBCONTENT_URL."javascript/".$jsName; ?>"></script>
<?php
    endforeach;
?>
    <script type="text/javascript">
      //<![CDATA[
      base_url = '<?php echo base_url();?>index.php/';
      //]]>
    </script>

  </head>
    