<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!-- topic browse displays -->
<?php        
/*

    'topics'       => $topics, //array of topics to be shown
    'showroot'      => False,    //if False, don't show the root(s) of the passed (sub)trees
    'depth'         => -1,       //max depth for which to render the tree
    'selected'      => 1;        //which topic is selected?
    'header'        => 'Select new parent...'
    'dropdownname' => 'parent_id'; //the name of the dropdown element
                             */
?>
<?php
    if (!isset($depth))$depth = -1;
    if (!isset($showroot))$showroot = False;
    if (!isset($selected))$selected = 1;
    if (!isset($dropdownname))$dropdownname = 'parent_id';
    if (!isset($header))$header = '';
    
    $todo = array();
    if (isset($topics)) {
        if (is_array($topics)) {
            $todo = $topics;
        } else {
            $todo = array($topics);
        }
    }
    
    $first = True;
    $level = 0;
    if ($header != "") {
        $options = array('header'=>$header);
    } else {
        $options = array();
    }        
    /* This is an experiment in left traversal of the tree that does not need nested views. (loading nested views seems to be extremely inefficient) */
    while (sizeof($todo)>0){
        //get next topic to be displayed
        $next = $todo[0];
        //remove from todo list
        unset($todo[0]);
        if (!is_a($next,'Topic') && ($next=="end")) {
            //if next is an end marker:
            $level--;
            $todo = array_values($todo); //reindex
        } else {
            //if next is a node: 
            $children = $next->getChildren();
            if (!$first || $showroot) {
                $text = "";
                for($i = 0; $i < $level; $i++)
                {
                    $text .= "&nbsp;&nbsp;";
                }
                $text .= $next->name;
                $options[$next->topic_id]=$text;
            }
            if (sizeof($children)>0) {
                //has children: open node and add all children + end marker in front of todo list; print this node
                $todo = array_merge($children,array('end'),$todo); //merge and reindex
                $level++;
            } else {
                $todo = array_values($todo); //reindex
            }
            $first = False;
        }
         //reindex
    }    
    echo form_dropdown($dropdownname,$options,$selected);
    
?>
<!-- End of topic  browse displays -->
