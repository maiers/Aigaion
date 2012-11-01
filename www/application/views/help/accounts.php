<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="help-holder">
  
  <p class='header1'>Groups, user accounts and rights profiles</p>
  <p>Access to the Aigaion database is possible through logging in with a user account, using the Aigaion login forms or some external module using e.g. LDAP or some CMS for authentication. Individual users can have certain access rights assigned to them. Individual users can also be assigned to groups. Groups facilitate group-defined topic subscriptions, quick assignment of default right profiles and restriction of read and write access for certain notes, publications, attachments or topics to a subset of users.</p>
  
  <p class='header1'>Group topic subscriptions</p>
  <p>Users with sufficient rights ('user_edit_all') can subscribe a group to certain topics from the 'manage accounts' page. In that case all users that belong to that group will be counted 'subscribed' to that topic, no matter whether they were individually subscribed or not.</p>
  
  <p class='header1'>Default rights profiles for groups</p>
  <p>Each group can be associated with one or more <i>rights profiles</i>, collections of user rights. This association has no influence at all on the user rights considered to be assigned to the users currently belonging to the group. However, whenever you newly assign a user to a group, that user will immediately also receive all user rights from the rights profiles associated to the group. This helps in quickly establishing default rights for users in certain groups.</p>
  
  <p class='header1'>Access levels</p>
  <p>See <?php echo anchor('help/viewhelp/accessrights','here'); ?> for more information about access levels.</p>
  
  <p class='header1'>External login modules</p>
  <p>Still to be documented. See also explanation on the site configuration page. Allows login to be controlled through an external system such as LDAP, .htpasswd files or some CMS login state.</p>
</div>
