<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id='singletopic-content-holder'>
<!-- Topic: HEADER AND DESCRIPTION -->
<?php
    if ($topic->name=="") {
        $name = __('Topic')." #".$topic->topic_id;
    } else {
        $name = $topic->name;
    }
    if ($topic->description != null) {
        $description = $topic->description;
    } else {
        $description = "- ".__('No description')." -";
    }
?>
<div class='header'>
<?php 
    echo __('Topic').": ";
    echo $name;
?>
</div>
<table class='fullwidth'>
<tr>
    <td class='fullwidth'>
<?php

    if ($topic->url != '') {
        $this->load->helper('utf8');
        $urlname = prep_url($topic->url);
        if (utf8_strlen($urlname)>21) {
            $urlname = utf8_substr($urlname,0,30)."...";
        }
        echo "URL: <a  title='".prep_url($topic->url)."' href='".prep_url($topic->url)."' class='open_extern'>".$urlname."</a><br/><br/>\n";
    }
    if ($description)
        echo "<p>".$description."</p>\n";
        
?>
    </td>
</tr>
</table>
</div> 