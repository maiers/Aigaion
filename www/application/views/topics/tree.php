<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<!-- topic browse displays -->
<?php        
/*

    'topics'       => $topics, //array of topics to be shown
    'showroot'      => False,    //if False, don't show the root(s) of the passed (sub)trees
    'depth'         => -1,       //max depth for which to render the trees
    'collapseAll' =>False
    The following var is passed around a lot, and not modified along the way, so it can be loaded using
    $this->load->vars(array( 
    
    'subviews' => array('topics/maintreerow'=>array('collapseCallback'=>$collapseCallback)) 
                             
        (subviews is array of 'viewname' => array(arguments,to,be,passed). $topic is always added to the arguments.
  
    Maybe optional: pass css classnames for node, leaf, subtree, etc. Just so we can make different trees even have different styling.
    Typically loaded with $this->load->vars(
  
                             */
?>
<?php
    if (!isset($depth))$depth = -1;
    if (!isset($showroot))$showroot = False;
    if (!isset($collapseAll))$collapseAll = False;
    if (!isset($subviews))$this->load->vars(array('subviews' => array()));
    
    $todo = array();
    if (isset($topics)) {
        if (is_array($topics)) {
            $todo = $topics;
        } else {
            $todo = array($topics);
        }
    }
    $currentdepth=0;
    $first = True;
    /* This is an experiment in left traversal of the tree that does not need nested views. (loading nested views seems to be extremely inefficient) */
    while (sizeof($todo)>0){
        //get next topic to be displayed
        $next = $todo[0];
        //remove from todo list
        unset($todo[0]);
        if (!is_a($next,'Topic') && ($next=="end")) {
            //if next is an end marker:
            echo "</ul>\n</div>\n";
            //should we collapse?
            echo $todo[1];
            echo "</li>\n";
            //remove collapse status from todo list
            unset($todo[1]);
            $todo = array_values($todo); //reindex
            $currentdepth--;
        } else {
            //if next is a node: 
            $children = $next->getChildren();
            if (!$first || $showroot) {
                if (sizeof($children)==0) {
                    $li_class = 'topictree-leaf';
                } else {
                    $li_class = 'topictree-node';
                }
                echo "<li class='".$li_class."'>";
                foreach ($subviews as $subview => $args) {
                    $args['topic'] = $next;
                    echo $this->load->view($subview,
                                          $args,
                                          True);
                }
                echo "</li>\n";
            }
            if ((sizeof($children)>0) && (($depth<0) || ($currentdepth<$depth))) {
                $currentdepth++;
                echo "<li class='topictree-children'>\n<div id='topic_children_".$next->topic_id."' class='topictree-children'>\n<ul class='topictree-list'>\n";
                //has children: open node and add all children + end marker in front of todo list; print this node
                
                //here we would store the collapse command in the todolist as well: hide this element if we had decided that this node is collapsed 
                //(calling Element.hide() directly from a piece of javascript)
                $collapse='';
                if ($collapseAll||array_key_exists('flagCollapsed',$next->configuration)&&$next->flags['userIsCollapsed']) {
                    $collapse = "<script type='text/javascript'>Element.hide('topic_children_".$next->topic_id."')</script>";
                }
                $todo = array_merge($children,array('end',$collapse),array(),$todo); //merge and reindex
            } else {
                $todo = array_values($todo); //reindex
            }
            $first = False;
        }
         //reindex
    }    
    
?>

<!-- End of topic browse displays -->
