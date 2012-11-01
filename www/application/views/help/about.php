<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="help-holder">
  <p class='header1'>About</p>
  <p>
<?php
        $Q = $this->db->get('aigaiongeneral');
        if ($Q->num_rows()>0) {
          $R = $Q->row();
            $version = $R->version;
        } else {
            $version = '0.0';
        }
        echo "Administrator of this installation: <a href='mailto: \"".getConfigurationSetting("CFG_ADMIN")."\" ".'<'.getConfigurationSetting("CFG_ADMINMAIL").'>'."?subject=About ".getConfigurationSetting("WINDOW_TITLE")." Aigaion database'>".getConfigurationSetting("CFG_ADMIN")."</a><br/>";
        echo "URL of this installation: ".AIGAION_ROOT_URL."<br/>";
        echo "PHP version: ".phpversion()."<br/>";
        echo "Aigaion Database Version: ".$version."<br/>";
        echo "Based on the <a href='http://codeigniter.com/' target=_blank>CodeIgniter</a> framework<br/>";
        
?>
  </p>
  <span class='header2'>Version history</span> 
  <?php 
  $userlogin=getUserLogin();
  if ($userlogin->hasRights('database_manage')) 
    echo '['.anchor('site/maintenance/checkupdates','Check for updates').']';
  ?>
  <br/>
  <table class='tablewithborder'>
    <tr>
        <td class='tablewithborder'></td>
        <td class='tablewithborder' colspan=4>Types of update</td>
        <td class='tablewithborder'></td>
    </tr>
    <tr>
        <td class='tablewithborder'>Version</td>
        <td class='tablewithborder'>bugfix</td>
        <td class='tablewithborder'>features</td>
        <td class='tablewithborder'>layout</td>
        <td class='tablewithborder'>security</td>
        <td class='tablewithborder'>Description</td>
    </tr>
<?php
    $this->db->order_by('version','desc');
    $Q = $this->db->get('changehistory');
    foreach ($Q->result() as $R) {
        echo '<tr>';
        echo '<td class="tablewithborder">'.$R->version.'</td>';
        echo '<td class="tablewithborder">';
        if (!(strpos($R->type,'bugfix')===false)) {
            echo '<img class="icon" title="Some bugs were fixed this release" src="'.getIconUrl('check.gif').'"/>';
        }
        echo '</td><td class="tablewithborder">';
        if (!(strpos($R->type,'features')===false)) {
            echo '<img class="icon" title="Some features were added this release" src="'.getIconUrl('check.gif').'"/>';
        }
        echo '</td><td class="tablewithborder">';
        if (!(strpos($R->type,'layout')===false)) {
            echo '<img class="icon" title="Some layout elements were changed this release" src="'.getIconUrl('check.gif').'"/>';
        }
        echo '</td><td class="tablewithborder">';
        if (!(strpos($R->type,'security')===false)) {
            echo '<img class="icon" title="This release contains security fixes!" src="'.getIconUrl('check.gif').'"/>';
        }
        echo '</td>';
        echo '<td class="tablewithborder">'.str_replace("\n","<br/>",$R->description).'</td>';
        echo '</tr>';
    }
?>
  </table>
</div>
