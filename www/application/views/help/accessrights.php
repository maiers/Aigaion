<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="help-holder">
  <p class='header1'>Access rights on different levels</p>
  <p>Aigaion users can be anonymous or not, can be assigned to different groups, and can be assigned different user rights. This makes it possible to restrict access to certain objects to a limited set of users. This restriction can apply to topics, publications, notes and attachments, and can be set separately for <i>reading</i> and for <i>editing</i> them.</p>
  
  <p class='header1'>What access levels are there?</p>
  <p>For each of the above mentioned objects one can set a <code>read_access_level</code> and a <code>edit_access_level</code>. These levels can be:
    <ul>
      <li>'public' (object can be read or edited by everyone including anonymous users), 
      <li>'intern' (object can be read or edited by all non-anonymous users), 
      <!--<li>'group'  (object can be read or edited by the users from one specific group), -->
      <li>'private' (object can be read or edited by the owner only) 
    </ul>
  </p>
  
  <p class='header1'>What user rights are involved?</p>
  <p>There are two types of user rights that also influence reading and editing access. The first are the normal read and edit rights. For example, a user who has no 'topic_edit' rights cannot edit a topic, even if all access levels of that topic are set to 'public'. The second are the 'override rights'. A user who has for example the right 'topic_read_all' can read every topic, even if he is not the owner and the access levels are set to 'private'.</p>
  
  <p class='header1'>Who can change the access level of an object?</p>
  <p>The access levels can normally be changed only by the owner. However, users who have the appropriate override rights for editing (see above) can also change the access levels of an object.</p>
</div>
