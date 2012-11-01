<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div style='float:bottom;font-size:90%;'>
  <p class='header'><?php _e('All available languages'); ?></p>
<?php 
global $AIGAION_SUPPORTED_LANGUAGES;
foreach ($AIGAION_SUPPORTED_LANGUAGES as $lang)
{
  echo anchor('language/set/'.$lang.'/'.implode('/',$this->uri->segment_array()),$this->userlanguage->getLanguageName($lang)).'<br/>';
}
?>
</div>