<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php

$this->load->helper('form');
$this->load->helper('translation');
echo "<div class='editform'>";
echo form_open_multipart('site/commitconfigform');
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','siteconfig');

echo "<p class='header'>".utf8_strtoupper(__('Aigaion site configuration form'))."</p>";

?>
    <table width='100%'>