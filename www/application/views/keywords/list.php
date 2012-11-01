<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class='keyword_list'>
  <div class='header'><?php echo $header ?></div>
<?php 
if (isset($searchbox)&&$searchbox) {
    echo "    ".form_open('keywords/searchlist')."\n";
    echo "    ".form_input(array('name' => 'keyword_search', 'id' => 'keyword_search', 'size' => '50', 'autocomplete' => 'off'));
    echo "    ".form_submit('submit', __('Show'))."\n";
    echo "    <script type='text/javascript'>".$this->ajax->observe_field('keyword_search', $options = array('url' => base_url().'index.php/keywords/searchlist/\' + value +\'', 'update' => 'autocomplete_results', 'function' => '', 'frequency' => '0.2', 'on' => 'focus'))."</script>\n";
    echo "    ".form_close()."\n";
}
?>
  <div id='autocomplete_results'>
<?php 
$this->load->view('keywords/list_items', array('keywordList' => $keywordList, 'useHeaders' => true, 'isCloud' => false));
?>
  </div>
</div>
