<?php
/** See formrepost_helper, login filter and login controller... */

$userlogin = getUserLogin();
$this->load->helper('form');
echo "<div class='editform'>";
echo form_open($this->latesession->get('FORMREPOST_uri'));
echo sprintf(__("The system detected that you were logged out while submitting a form named '%s'. The data in that form has <b>not</b> yet been submitted successfully to the database. Press the button below to re-submit the form data."), $this->latesession->get('FORMREPOST_formname'))."<br/><br/>";
if ($userlogin->isAnonymous()) {
    echo "<div class='errormessage'>";
    echo "<b>".__('NOTE')."</b>: ".__("You are now logged in as a guest user. If you submitted the form from a registered account, you should first login with your registered account before resubmitting the form, because from this guest account you might then not have enough rights to submit the form.")."<br/><br/>";
    echo "</div>";
}

echo form_submit('repost_form', __('Repost'));
foreach($this->latesession->get('FORMREPOST_post') as $field=>$val) {
    echo form_hidden($field,$val);
}
echo form_hidden('form_reposted','form_reposted');
echo form_close();
echo form_open('');
echo form_submit('cancel', __('Cancel'));
echo form_hidden('form_reposted','form_reposted');
echo form_close();
echo "</div>";
//note: if the form was a search form, we might as well immediately force a submit through javascript; searches don't need confirmation

//reset the session vars related to this formrepost, but not here because a refresh would then destroy this form
?>