<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<ul>
<?php
  foreach ($keywords as $keyword_id => $keyword)
  {
    echo "  <li>".$keyword->keyword."</li>\n";
  }
?>
</ul>