<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
$this->load->helper('form');
$userlogin = getUserLogin();

    echo "<div class='editform'>";
    echo form_open_multipart('publications/commitdeletecoverimage','',array('pub_id'=>$publication->pub_id));
    //formname is used to check whether the POST data is coming from the right form.
    //not as security mechanism, but just to avoid painful bugs where data was submitted 
    //to the wrong commit and the database is corrupted
    echo form_hidden('formname','coverimage');
    echo "<p class='header2'>".sprintf(__('Are you sure you want to delete the cover image for publication "%s"'), $publication->title)."?</p>";
    echo "
            <tr><td>";
    echo form_submit('submit',__('Delete cover image'));
    echo "
            </td>
            </tr>
        </table>
         ";
    echo form_close();
    echo form_open('publications/show/'.$publication->pub_id);
    echo form_submit('cancel',__('Cancel'));
    echo form_close();
    echo "</div>";


?>