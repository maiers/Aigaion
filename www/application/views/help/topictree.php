<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="help-holder">
  <p class='header1'>The topic tree</p>
  <p>Click on a topic to read its description and find all <a href="?page=help&help=publicationlist">papers</a> that are attached to it. A paper can be attached to multiple topics! If you want to find for example the publications that are part of two topics, you can use the search interface from the menu.</p>
  <div class='message'>
    <img class='icon' src="<?php echo getIconUrl("tree_min.gif"); ?>">&nbsp;<a href="" title="Go to topic First Topic">First Topic</a> <br/>
    &nbsp;&nbsp;&nbsp;<img class='icon' src="<?php echo getIconUrl("tree_blank.gif"); ?>">&nbsp;<a href="" title="Go to topic Child of First Topic">Child of First Topic</a><br/>
    <img class='icon islink' src="<?php echo getIconUrl("tree_blank.gif"); ?>">&nbsp;<a href="" title="Go to topic Second Topic">Second Topic</a><br/>
    <br/><br/><i><u>An example topic tree fragment</u></i>
  </div>
  <p class='header2'>Icons in the topic tree and their functions.</p>
    <ul>
      <li><img class='icon islink' src="<?php echo getIconUrl("tree_plus.gif"); ?>"> Expand the topic in the tree view.</li>
      <li><img class='icon islink' src="<?php echo getIconUrl("tree_min.gif"); ?>"> Collapse the topic in the tree view.</li>
    </ul>
  <p class='header1'>Subscribing for topics.</p>
  <p>It may be that not every user of a certain Aigaion database is interested in exactly the same topics. Therefore Aigaion contains a mechanism that allows you to subscribe to - or unsubscribe from - any topic, meaning that you can determine which topics you will see while browsing the system.
  By default, a new user will not be subscribed to any topic. To review which topics already exist in this copy of Aigaion, go to the <?php echo anchor('users/topicreview','topic review page'); ?> and subscribe to any topic that you are interested in. Those topics will then appear in your topic tree.</p>
</div>
