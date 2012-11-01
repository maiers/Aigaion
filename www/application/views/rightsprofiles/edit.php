<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/rightsprofiles/edit

Shows a form for editing or adding rightsprofiles.

Parameters:
    $rightsprofile=>the Rightsprofile object to be edited
    
If $rightsprofile is null, the edit form will be restyled as an 'add new rightsprofile' form
if $rightsprofile is not null, but $action == 'add', the edit form will be restyled as a
pre filled 'add new rightsprofile' form

we assume that this view is not loaded if you don't have the appropriate read and edit rights

*/
$this->load->helper('form');

//note: the validation library must be loaded in the controller!


echo "<div class='editform'>";
echo form_open('rightsprofiles/commit');
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','rightsprofile');
$isAddForm = False;
if (!isset($rightsprofile)||($rightsprofile==null)||(isset($action)&&$action=='add')) {
    $isAddForm = True;
    echo form_hidden('action','add');
    if (!isset($action)||$action!='add')
        $rightsprofile = new Rightsprofile;
} else {
    echo form_hidden('action','edit');
    echo form_hidden('rightsprofile_id',$rightsprofile->rightsprofile_id);
}

if ($isAddForm) {
    echo "<p class='header2'>".__('Create a new rightsprofile')."</p>";
} else {
    echo "<p class='header2'>".sprintf(__('Edit rightsprofile "%s"'), $rightsprofile->name)."</p>";
}

//validation feedback
echo $this->form_validation->error_string();

echo "
    <table width='100%'>
        <tr>
        <td>".__('Name')."</td>
        <td>"
        .form_input(array('name'=>'name',
                          'size'=>'10',
                          'value'=>$rightsprofile->name))."
        </td>
        </tr>

        <tr><td colspan='2'>
        <hr><b>".__('User rights in this profile').":</b><hr>
        </td></tr>
        ";
        
    //list all userrights as checkboxes
    foreach (getAvailableRights() as $right=>$description) {
        $checked = FALSE;
        if (in_array($right,$rightsprofile->rights)) $checked=TRUE;
        echo "<tr><td>".form_checkbox($right, $right, $checked).$right."</td><td>".$description."</td></tr>";
    }

echo "
        <tr><td>";
if ($isAddForm) {
    echo form_submit('submit',__('Add'));
} else {
    echo form_submit('submit',__('Change'));
}
echo "
        </td>
        </tr>
    </table>
     ";
echo form_close();
echo form_open('users/manage');
echo form_submit('cancel',__('Cancel'));
echo form_close();
echo "</div>";

?>

