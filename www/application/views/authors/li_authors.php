<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<ul>
<?php
  foreach ($authors as $author)
  {
    echo "  <li>".$author->getName()."</li>\n";
  }
?>
</ul>