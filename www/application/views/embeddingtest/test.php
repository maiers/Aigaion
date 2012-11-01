<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
//this view is meant as an example of how you can embed an per-author-publication-listing in another page.
//See also the controller authors/embed

$userlogin = getUserLogin();

?>
<div class='author'>
  <div class='header'><?php echo $author->getName() ?></div>
<table width='100%'>
  <tr> <!-- author info in tr -->
    <td  width='100%'>
      <table class='author_details'>
<?php
      $authorfields = array('firstname'=>__('First name(s)'), 'von'=>__('von-part'), 'surname'=>__('Last name(s)'), 'jr'=>__('jr-part'), 'email'=>__('Email'), 'institute'=>__('Institute'));
      
      foreach ($authorfields as $field=>$display)
      {
        if (trim($author->$field) != '')
        {
?>
          <tr>
            <td valign='top'><?php echo $display; ?>:</td>
            <td valign='top'><?php echo $author->$field; ?></td>
          </tr>
<?php
        }
      }
?>
      </table>
    </td>
  </tr> <!-- end of author info in tr -->
</table>
</div>
<!-- and now we should enter the summary of the publications, with some login-dependent aspects such as download links, to show how well the cross-subdomain embedding works :) -->