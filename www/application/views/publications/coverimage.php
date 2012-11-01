<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php        
    $userlogin = getUserLogin();
    if ($publication->coverimage != '' && $publication->coverimage != null)
    {
      if (!file_exists(AIGAION_ATTACHMENT_DIR."/".$publication->coverimage)) {
          appendErrorMessage (sprintf(__('Cover image file could not be found: "%s/%s"'), AIGAION_ATTACHMENT_DIR, $publication->coverimage)."<br/>");
      } else {
          $this->load->helper('download');
          $this->output->set_header("Content-type: image/jpeg");
//          if ($userlogin->getPreference('newwindowforatt') == 'TRUE') {
//              $this->output->set_header('Content-Disposition: inline; filename="'.$attachment->name.'"');
//              $this->output->set_header('Title: "'.$attachment->name.'"');
//          } else {
              $this->output->set_header('Content-Disposition: attachment; filename="cover.jpg"');
//          }
          $this->output->set_header("Cache-Control: cache, must-revalidate");
          $this->output->set_header("Pragma: public");
          echo file_get_contents(AIGAION_ATTACHMENT_DIR."/".$publication->coverimage); // Read the file's contents
      }
    }
?>