<?php

  // restrict direct script access
  if (!defined('BASEPATH')) exit('No direct script access allowed');
  
  // set benchmark
  $var_benchmark = sprintf(__('processing time: %s seconds'), $this->benchmark->elapsed_time());
  
  // merge variables
  $this->tbswrapper->tbsLoadTemplate(APPPATH . 'templates/footer3.tpl.html');
  $this->tbswrapper->tbsMergeField('benchmark', $var_benchmark);
  
  // render
  echo $this->tbswrapper->tbsRender();

?>