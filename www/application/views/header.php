<?php header("Content-Type: text/html; charset=UTF-8"); ?>
<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php 
        echo $title; 
        if (getConfigurationSetting('WINDOW_TITLE')!='')
            echo ' - '.getConfigurationSetting('WINDOW_TITLE'); 
    ?> - Aigaion 2.0</title>
    <link href="<?php echo AIGAION_WEBCONTENT_URL; ?>themes/default/css/positioning.css" rel="stylesheet" type="text/css" media="screen,projection,tv" />
    <link href="<?php echo AIGAION_WEBCONTENT_URL; ?>themes/default/css/styling.css"     rel="stylesheet" type="text/css" media="screen,projection,tv" />
    <link href="<?php echo getCssUrl("positioning.css"); ?>" rel="stylesheet" type="text/css" media="screen,projection,tv" />
    <link href="<?php echo getCssUrl("styling.css"); ?>"     rel="stylesheet" type="text/css" media="screen,projection,tv" />
  </head>
  <body>
<?php
    //view parameter to be passed to menu: a prefix for the sort options. See views/menu.php for more info
    if (!isset($sortPrefix))
      $sortPrefix = '';
    //view parameter to be passed to menu: a command relevant for the menu export option. See views/menu.php for more info
    if (!isset($exportCommand))
      $exportCommand = '';
    if (!isset($exportName))
      $exportName = __('Aigaion export list');
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

    <div id="main_holder">
      <!-- Aigaion header: Logo, simple search form -->
      <div id="header_holder">
        <div id='quicksearch'>
          <?php
          echo form_open('search/quicksearch')."\n";
          echo "<div>\n";
          echo form_hidden('formname','simplesearch');
          echo form_input(array('name' => 'searchstring', 'size' => '25'));
          echo form_submit('submit_search', __('Search'));
          echo "</div>\n";
          echo form_close();
          ?>
        </div>  
        <?php
        if (getConfigurationSetting('USE_UPLOADED_LOGO')=='TRUE') {
            //echo '<img border=0 style="height:100%;" src="'.AIGAION_ROOT_URL.'/custom_logo.jpg">';
        }
        ?>
        &nbsp;<?php
            //echo anchor('','Aigaion 2.0','id="page_title"');
            echo anchor('',"<img border=0 src='".AIGAION_WEBCONTENT_URL."themes/".getThemeName()."/img/aigaion2.png'/>",'id="page_title"');
        ?>
        
      </div>
      <!-- End of header -->

      <?php
        //load menu
        $this->load->view('menu', array('sortPrefix'=>$sortPrefix,'exportCommand'=>$exportCommand,'exportName'=>$exportName));
      ?>

      <!-- Aigaion main content -->
      <div id="content_holder">
      
      
      <!-- I think that here we want to have the (error) messages: -->
      <?php
            $err = getErrorMessage();
            $msg = getMessage();
            if ($err != "") {
                echo "<div class='errormessage'>".$err."</div>";
                clearErrorMessage();
            }
            if ($msg != "") {
                echo "<div class='message'>".$msg."</div>";
                clearMessage();
            }      

        ?>
        <!---->
