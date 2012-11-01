<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
$this->load->helper('form');
$userlogin = getUserLogin();

    echo "<div class='editform'>";
    echo form_open_multipart('publications/commitcoverimage','',array('pub_id'=>$publication->pub_id));
    //formname is used to check whether the POST data is coming from the right form.
    //not as security mechanism, but just to avoid painful bugs where data was submitted 
    //to the wrong commit and the database is corrupted
    echo form_hidden('formname','coverimage');
    echo "<p class='header2'>".sprintf(__('Upload new cover image from this computer for publication "%s"'), $publication->title)."</p>";
    echo "
        <table>
            <tr><td><label for='upload'>".__('Select a file...').' '.__('(jpeg only)')."</label></td>
                <td>
         ";
    echo form_upload(array('name'=>'upload','size'=>'30'));
    echo "
                </td>
            </tr>
            <tr><td>";
    echo form_submit('submit',__('Upload cover image'));
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