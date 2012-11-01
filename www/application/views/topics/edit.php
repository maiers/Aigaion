<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php
/**
views/topics/edit

Shows a form for editing topics.

Parameters:
    $topic=>the Topic object to be edited
    
If $topic is null, the edit for will be restyled as an 'add new topic' form
if $topic is not null, but $action == 'add', the edit form will be restyled as a
pre filled 'add new topic' form
*/

$this->load->helper('form');
echo "<div class='editform'>";
echo form_open('topics/commit');
//formname is used to check whether the POST data is coming from the right form.
//not as security mechanism, but just to avoid painful bugs where data was submitted 
//to the wrong commit and the database is corrupted
echo form_hidden('formname','topic');
$customfieldkeys    = $this->customfields_db->getCustomFieldKeys('topic');
$isAddForm = False;
$userlogin  = getUserLogin();
$user       = $this->user_db->getByID($userlogin->userID());

if (!isset($topic)||($topic==null)||(isset($action)&&$action=='add')) {
    $isAddForm = True;
    echo form_hidden('action','add');
    if (!isset($action)||$action!='add')
        $topic = new Topic;
    echo form_hidden('user_id',$userlogin->userId());
} else {
    echo form_hidden('action','edit');
    echo form_hidden('topic_id',$topic->topic_id);
    echo form_hidden('user_id',$topic->user_id);
}


if ($isAddForm) {
    echo "<p class='header2'>".__('Add a topic')."</p>";
} else {
    echo "<p class='header2'>".sprintf(__('Change topic "%s"'), $topic->name)."</p>";
}
//validation feedback
echo $this->form_validation->error_string();
?>
    <table>
        <tr><td><label for='name'><?php echo __('Name');?></label></td>
            <td>
<?php echo form_input(array('name'=>'name','size'=>'45','value'=>$topic->name)); ?>
            </td>
        </tr>
        <tr><td><label for='url'><?php echo __('URL');?></label></td>
            <td>
<?php
echo form_input(array('name'=>'url','size'=>'45','value'=>$topic->url));
?>
            </td>
        </tr>    
<?php
    //do the custom fields
    $customFields = $topic->getCustomFields();
    foreach ($customfieldkeys as $field_id => $field_name) {
      if (array_key_exists($field_id, $customFields)) {
        $value = $customFields[$field_id]['value'];
      }
      else {
        $value = '';
      }
      ?>
    <tr>
      <td><?php echo $field_name; ?>:</td>
      <td><?php echo form_input(array('name' => 'CUSTOM_FIELD_'.$field_id, 'id' => 'CUSTOM_FIELD_'.$field_id, 'size' => '45'), $value); ?></td>
    </tr>
      <?php
    }
    ?>

        <tr><td><label for='parent_id'><?php echo __('Parent');?></label></td>
            <td>
<?php     

    $config = array('onlyIfUserSubscribed'=>True,
                    'includeGroupSubscriptions'=>True,
                    'user'=>$user);
if (isset($parent)) {
    $parent_id = $parent->topic_id;
} else {
    $parent_id = $topic->parent_id;
}
echo $this->load->view('topics/optiontree',
                       array('topics'   => $this->topic_db->getByID(1,$config),
                            'showroot'  => True,
                            'depth'     => -1,
                            'selected'  => $parent_id
                            ),  
                       true)."\n";
?>
            </td>
        </tr>
        <tr><td><label for='description'><?php echo __('Description');?></label></td>
            <td>
<?php
    echo form_textarea(array('name'=>'description','cols'=>'70','rows'=>'7','value'=>$topic->description));
?>
            </td>
        </tr>                
        <tr><td>
<?php
if ($isAddForm) {
    echo form_submit('submit',__('Add'));
} else {
    echo form_submit('submit',__('Change'));
}
?>
        </td>
        </tr>
    </table>
<?php
echo form_close();
echo form_open('');
echo form_submit('cancel',__('Cancel'));
echo form_close();
?>
</div>

