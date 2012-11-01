<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class='author_list'>
  <div class='header'><?php echo $header ?></div>
<?php 
if (isset($searchbox)&&$searchbox) {
    echo "    ".form_open('authors/searchlist')."\n";
    echo "    ".form_input(array('name' => 'author_search', 'id' => 'author_search', 'size' => '50', 'autocomplete' => 'off'));
    echo "    ".form_submit('submit', __('Show'))."\n";
    echo "    <script type='text/javascript'>".$this->ajax->observe_field('author_search', $options = array('url' => 'authors/searchlist/\' + value +\'', 'update' => 'autocomplete_results', 'function' => '', 'frequency' => '0.2', 'on' => 'focus'))."</script>\n";
    echo "    ".form_close()."\n";
}
?>
  <div id='autocomplete_results'>
<?php 
$this->load->view('authors/list_items', $authorlist);
?>
  </div>
</div>
