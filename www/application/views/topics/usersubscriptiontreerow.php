<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php        
/*
views/topics/usersubscriptiontreerow

Displays a row if information about one topic in the 'user subscription' tree. 
Among other things, subscription status is visualised, and a (un)subscription link is provided for each
topic. Clicking the (un)subscription link results in an async (un)subscription call as well as
in the execution of a javascript that toggles the display style of affected topics (ancestors and children included)

Some configuration can be passed through the view parameters.

Parameters:
    $topic: the topic for which the row is to be displayed. This topic should have configuration['user'] set, and
        flags['userIsSubscribed'] should be used if relevant.
    
Information about which user this subscription tree is shown is found in the configuration and flags attributes
of the topic.
*/

/*=== COLLAPSE LINK ===*/
    #make hide scripts to show and hide proper parts 
    $hide1="";
    $hide2="Element.hide('plus_topic_".$topic->topic_id."');";
    #
    if (count($topic->getChildren())>0) {
        echo "\n<img id      = 'min_topic_".$topic->topic_id."' 
                   onclick = 'collapse(\"".$topic->topic_id."\",\"\");' 
                   class   = icon
                   src     = '".getIconUrl('tree_min.gif')."'/>
              <img id      = 'plus_topic_".$topic->topic_id."' 
                   onclick = 'expand(\"".$topic->topic_id."\",\"\");' 
                   class   = icon
                   src     = '".getIconUrl('tree_plus.gif')."'/>
              <script>".$hide1.$hide2."</script>\n"; 
    } else {
        echo "<img  class   = icon
                    src     = '".getIconUrl('tree_blank.gif')."'/>\n";
    }
    

if ($topic->flags['userIsSubscribed']) {
    $class = 'subscribedtopic';
} else {
    $class = 'unsubscribedtopic';
}
echo "\n<span id='subscription_".$topic->topic_id."' class='".$class."'>"
     .$this->ajax->link_to_function($topic->name, "toggleSubscription(".$topic->configuration['user']->user_id.",".$topic->topic_id.",'".site_url('users/')."')" )
     ."</span>";
     
if ($topic->flags['userIsGroupSubscribed']) {
    echo " (".__('group subscribed').")";
}
?>