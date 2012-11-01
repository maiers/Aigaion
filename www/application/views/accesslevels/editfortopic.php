<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php 
/*

    always from root.
    parameter: topic_id - the topic to be highlighted
*/
$userlogin=getUserLogin();
$user_id = $userlogin->userId();
$this->load->helper('form');
?>
<div class='header'><?php echo __('Edit topic access levels');?></div>
<?php echo __('You have to manually edit all access levels - there is no automatic propagation of changes up or down the tree.');?>

<br/>
<table>
    <tr >
        <td colspan='2'>
            <img src='<?php echo getIconUrl('info.gif')."' title='".__('Effective access levels (after combining all relevant access levels)')."'";?> />
            <br/><?php echo __('Effective');?>
        </td>
        <td>
            <img src='<?php echo getIconUrl('info.gif')."' title='".__('Topic')."'"; ?>/>
            <br/><?php echo __('Topic');?>
        </td>
        <td>
            <img src='<?php echo getIconUrl('info.gif')."' title='".__('Owner of topic (only owner can change objects with private edit levels...)')."'";?> />
            <br/><?php echo __('Owner');?>
        </td>
        <td colspan='2'>
            <img src='<?php echo getIconUrl('info.gif')."' title='".__('Per-object access levels')."'";?> />
            <br/><?php echo __('Individual per-object access levels');?>
        </td>
        
    </tr>
<?php
$userlogin = getUserLogin();
$config = array('onlyIfUserSubscribed'=>True,
                 'user'=>$this->user_db->getByID($userlogin->userId()),
                 'includeGroupSubscriptions'=>True);
$root = $this->topic_db->getByID(1,$config);

    $todo = array($root);
    
    $first = True;
    $level = 0;
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
            if (!$first) {
                //MAKE TABLE ROW
                $topic = $next;
                ?>
                <tr <?php
                    if ($topic_id==$topic->topic_id)echo 'style="background:#dfdfff;" ';
                    ?>>
                    <td>
                        r:<img class='rights_icon' src='<?php echo getIconurl('al_'.$topic->derived_read_access_level.'_grey.gif'); ?>'/>
                    </td>
                    <td>
                        e:<img class='rights_icon' src='<?php echo getIconurl('al_'.$topic->derived_edit_access_level.'_grey.gif'); ?>'/>
                    </td>
                    <td style='padding-left:0.5em;' class='header2'>
                        <?php 
                        for ($space = 0; $space<$level; $space++)echo "&nbsp;&nbsp;&nbsp;&nbsp;";
                        echo anchor('topics/single/'.$topic->topic_id,$topic->name); 
                        ?>
                    </td>
                    <td style='padding-left:0.5em;'>
                        <?php 
                        if ($topic->user_id==$user_id) {
                            echo '<span class="owner_self">';
                        } else {
                            echo '<span class="owner_other">';
                        }
                        echo '['.getAbbrevForUser($topic->user_id).']</span>'; 
                        ?>
                    </td>
                    <td>
                        <?php
                        echo $this->accesslevels_lib->getAccessLevelEditPanel($topic,'topic',$topic->topic_id);
                        ?>
                    </td>
                </tr>
                <?php
                //END MAKE TABLE ROW
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
?>
</table>    
<br/><br/>
<div style='border:1px solid black;'>
    <div style='border:1px solid black;'>
        <b><?php echo __('Legenda'); ?></b>
    </div>
    <?php
        echo "
    r:<img class='rights_icon' src='".getIconurl('rights_public.gif')."'/> ".__('read public')."<br/> 
    r:<img class='rights_icon' src='".getIconurl('rights_intern.gif')."'/> ".__('read intern')."<br/> 
    r:<img class='rights_icon' src='".getIconurl('rights_private.gif')."'/> ".__('read private')."<br/> 
    e:<img class='rights_icon' src='".getIconurl('rights_public.gif')."'/> ".__('edit public')."<br/> 
    e:<img class='rights_icon' src='".getIconurl('rights_intern.gif')."'/> ".__('edit intern')."<br/> 
    e:<img class='rights_icon' src='".getIconurl('rights_private.gif')."'/> ".__('edit private')."<br/> 
    - ".__("If nothing is shown, access level is 'intern'")."<br/>
    ";
    ?>
</div>
