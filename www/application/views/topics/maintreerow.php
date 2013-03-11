<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php        
/*
views/topics/maintreerow

Displays a row if information about one topic in the 'normal' main tree. 
Some configuration can be passed through the view parameters.

Parameters:
    $topic: the topic for which the row is to be displayed
    $useCollapseCallback: if True, collapse and expand actions will be passed to the topics/collapse callback
*/
    if (!isset($useCollapseCallback))$useCollapseCallback = False;
    $collapseCallback = '';
    $expandCallback = '';
    if ($useCollapseCallback) {
        $collapseCallback = site_url('topics/collapse/'.$topic->topic_id.'/1');
        $expandCallback   = site_url('topics/collapse/'.$topic->topic_id.'/0');
    }
    #make hide scripts to show and hide proper parts depending on some collapse state
    $hide1="";
    $hide2="Element.hide('plus_topic_".$topic->topic_id."');";
    if (array_key_exists('flagCollapsed',$topic->configuration) && ($topic->flags['userIsCollapsed']==True)) {
        $hide1="Element.hide('min_topic_".$topic->topic_id."');";
        $hide2="";
    }
    #
    if (sizeof($topic->getChildren())>0) {
        echo "<img id      = 'min_topic_".$topic->topic_id."' 
                   onclick = 'collapse(\"".$topic->topic_id."\",\"".$collapseCallback."\");' 
                   class   = 'icon'
                   src     = '".getIconUrl('tree_min.gif')."'
                   alt     = '".__('collapse')."'/>\n";
        echo "<img id      = 'plus_topic_".$topic->topic_id."' 
                   onclick = 'expand(\"".$topic->topic_id."\",\"".$expandCallback."\");' 
                   class   = 'icon'
                   src     = '".getIconUrl('tree_plus.gif')."'
                   alt     = '".__('expand')."'/>\n";
        echo "<script type='text/javascript'>".$hide1.$hide2."</script>"; 
    } else {
        echo "<img  class   = 'icon'
                    src     = '".getIconUrl('tree_blank.gif')."'
                    alt     = 'blank'/>\n";
    }
   	$publicationCount     = $this->topic_db->getVisiblePublicationCountForTopic($topic->topic_id);
	$publicationReadCount = $this->topic_db->getReadPublicationCountForTopic($topic->topic_id);

    echo anchor('topics/single/'.$topic->topic_id,$topic->name)." <span title='".sprintf(__('read: %s of %s publications'), $publicationReadCount, $publicationCount)."'><i> ".$publicationReadCount.'/'.$publicationCount."</i></span>\n";
?>