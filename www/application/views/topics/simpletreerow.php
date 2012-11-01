<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php        
/*
views/topics/simpletreerow

Displays a row of information about one topic in the 'simple' main tree. 
No collapse icons, only linked names.

Parameters:
    $topic: the topic for which the row is to be displayed
*/
    echo anchor('topics/single/'.$topic->topic_id,$topic->name)."\n";

?>