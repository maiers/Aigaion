<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for whether the current version of Aigaion is up-to-date
| -------------------------------------------------------------------
|
|   Provides information whether the version of Aigaion is up-to-date by checking
|   version information on http://www.aigaion.nl
|   Used by the login module: once every 2 days, when a dbadmin logs in, a check is done whether 
|   the current version is up-to-date. A very short time-out is used, to make it least intrusive.
|   If an update is available, a warning is returned and the up-to-date-check is not performed for 
|   48 hours for this dbadmin user. If the new version is a security update, a red warning ('error message')
|   is returned.
|
|	Usage:
|       $this->load->helper('checkupdates'); //load this helper
|       checkUpdate(); //is Aigaion up to date? if not, an message is generated using appendMessage or appendErrorMessage
|       
*/
    /** Returns a piece of HTML giving the result of the check for updates, or empty string if no update available. */
    function checkUpdates() {
        $CI = &get_instance();
        $CI->load->helper('readremote');
        //return '<div class="message">no info on updates available</div>';
        #try with short timeout, to get version info from www.aigaion.nl
        $remoterelease = getRemoteFile ('http://demo2.aigaion.nl/index.php/version');
        if ($remoterelease == '') {
            return "<div class='message'>".__("Couldn't connect to demo2.aigaion.nl to check for updates")."</div>";
        }
        if ($remoterelease == '') {
            return "<div class='message'>".__("Couldn't obtain release version info from demo2.aigaion.nl")."</div>";
        }
        #compare info to current version
        $CI->db->order_by('version','desc');
        $CI->db->limit(1);
        $Q = $CI->db->get('changehistory');
        foreach ($Q->result() as $R) {
            $thisrelease = $R->version;
        }
        #if same: report 'up to date'
        if ($remoterelease == $thisrelease) {
            return '';
        }
        #if current installed version higher: SVN?
        if ($remoterelease < $thisrelease) {
            appendMessage(__('Your database version is higher than the official release. Probably you are using the SVN version?').'<br/>');
            return '';
        }
        $result = '<p>'.__('There is a new version available').': <b>'.$remoterelease.'</b> ('.__('Current version').': '.$thisrelease.')<br/>';
        #if deviation: get detailed info for change history from aigaion.nl
        $remotechangehistory = getRemoteFile("http://demo2.aigaion.nl/index.php/version/details/".$thisrelease);
        #parse detailed info
        $class='message';
        if ($remotechangehistory=='') {
            $result .= __("Couldn't obtain detailed update info from demo2.aigaion.nl")."<br/>";
        } else { 
            #note: we use quite ugly parsing here - assuming that version/details outputs exactly what we expect and assuming that description contains NO XML
            $p = xml_parser_create();
            xml_parse_into_struct($p, $remotechangehistory, $vals, $index);
            xml_parser_free($p);
            $i = 1;
            $history = array();
            $alltypes = '';
            while (($i+3) < count($vals)) { //the last one is the close for the changehistory
                $release = $vals[$i+1]['value'];
                $type    = $vals[$i+2]['value'];
                $alltypes.=','.$type;
                $description = $vals[$i+3]['value'];
                $history[] = array($release,$type,$description);
                $i+=5;
            }
            if (strpos($alltypes,'security')>0) { //if this is a security update
                $class='errormessage';
                //also extend message with extra warning
                $result .= __('Note: the updated version contains security fixes. You are strongly recommended to get the latest version of Aigaion.').'<br/>';
            }
            $result .= '<span class=header2>'.__('Detailed info for available updates').': </span><br/>';
            //print out the new versions into $result
            $result .= "
              <table class=tablewithborder>
                <tr>
                    <td class='tablewithborder'></td>
                    <td class='tablewithborder' colspan=4>".__("Types of update")."</td>
                    <td class='tablewithborder'></td>
                </tr>
                <tr>
                    <td class='tablewithborder'>".__("Version")."</td>
                    <td class='tablewithborder'>".__("Bugfix")."</td>
                    <td class='tablewithborder'>".__("Features")."</td>
                    <td class='tablewithborder'>".__("Layout")."</td>
                    <td class='tablewithborder'>".__("Security")."</td>
                    <td class='tablewithborder'>".__("Description")."</td>
                </tr>
                ";
            foreach ($history as $version) {
                $result .= '<tr>';
                $result .= '<td class="tablewithborder">'.$version[0].'</td>';
                $result .= '<td class="tablewithborder">';
                if (!(strpos($version[1],'bugfix')===false)) {
                    $result .= '<img class="icon" title="'.__('Some bugs were fixed this release').'" src="'.getIconUrl('check.gif').'"/>';
                }
                $result .= '</td><td class="tablewithborder">';
                if (!(strpos($version[1],'features')===false)) {
                    $result .= '<img class="icon" title="'.__('Some features were added this release').'" src="'.getIconUrl('check.gif').'"/>';
                }
                $result .= '</td><td class="tablewithborder">';
                if (!(strpos($version[1],'layout')===false)) {
                    $result .= '<img class="icon" title="'.__('Some layout elements were changed this release').'" src="'.getIconUrl('check.gif').'"/>';
                }
                $result .= '</td><td class="tablewithborder">';
                if (!(strpos($version[1],'security')===false)) {
                    $result .= '<img class="icon" title="'.__('This release contains security fixes!').'" src="'.getIconUrl('check.gif').'"/>';
                }
                $result .= '</td>';
                $result .= '<td class="tablewithborder">'.str_replace("\n","<br/>",$version[2]).'</td>';
                $result .= '</tr>';            
            }
            $result .= '</table>';
        }
        #give message depending on type of update (normal, minor, security, etc; max type that was missed since current version of installation); 
        $result .= "<p>".sprintf(__("You can download the new version %s here %s."),"<a href='http://www.aigaion.nl'>","</a>");
        #update status of 'last check for this user'
        //return message or errormessage
        return '<div class="'.$class.'">'.$result.'</div>';
    }

?>