<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php 
echo form_hidden('configformname','userdefaults');
?>
<!-- USER PREFERENCE DEFAULTS -->
	    <tr>
	        <td colspan='2'><hr><p class='header2'><?php echo __('Defaults for user preferences:');?></p></td>
	    </tr>
        <tr><td align=left colspan=2><img class='icon' border=0 src='<?php echo getIconUrl("small_arrow.gif"); ?>'>
        <?php echo __('Several user preferences can be given a default value here, to be overridden as wished by users who can change their profile.');?></td>
        </tr>
<?php
$theme_array = array();
$availableThemes = getThemes();
foreach ($availableThemes as $theme)
{
  $theme_array[$theme] = $theme;
}
$lang_array = array();
global $AIGAION_SUPPORTED_LANGUAGES;
foreach ($AIGAION_SUPPORTED_LANGUAGES as $lang)
{
  $lang_array[$lang] = $this->userlanguage->getLanguageName($lang);
}
echo "
        <tr>
        <td>".__('Default theme')."</td>
        <td>
        ".form_dropdown('DEFAULTPREF_THEME',
                        $theme_array,
                        $siteconfig->getConfigSetting("DEFAULTPREF_THEME"))."
        </td>
        </tr>

        <td>".__('Default language')."</td>
        <td>
        ".form_dropdown('DEFAULTPREF_LANGUAGE',
                        $lang_array,
                        $siteconfig->getConfigSetting("DEFAULTPREF_LANGUAGE"))."
        </td>
        </tr>

        <tr>
        <td>".__('Default publication summary style')."</td>
        <td>
        ".form_dropdown('DEFAULTPREF_SUMMARYSTYLE',
                        array('author'=>__('Author first'),'title'=>__('Title first')),
                        $siteconfig->getConfigSetting("DEFAULTPREF_SUMMARYSTYLE"))."
        </td>
        </tr>
        <tr>
        <td>".__('Default author display style')."</td>
        <td>
        ".form_dropdown('DEFAULTPREF_AUTHORDISPLAYSTYLE',
                        array('fvl'=>__('First [von] Last'),'vlf'=>__('[von] Last, First'),'vl'=>__('[von] Last')),
                        $siteconfig->getConfigSetting("DEFAULTPREF_AUTHORDISPLAYSTYLE"))."
        </td>
        </tr>
        <tr>
        <td>".__('Default number of publications per page')."</td>
        <td>
        ".form_dropdown('DEFAULTPREF_LISTSTYLE',
                        array('0'=>__("All"), "10"=>"10", '15'=>"15", '20'=>"20", '25'=>"25", '50'=>"50", '100'=>"100"),
                        $siteconfig->getConfigSetting("DEFAULTPREF_LISTSTYLE"))."
        </td>
        </tr>
        <tr>
        <td>".__("'Similar author' check")."</td>
        <td>
        ".form_dropdown('DEFAULTPREF_SIMILAR_AUTHOR_TEST',
                        array('il'=>__("Last names, then initials"), "c"=>__("Full name")),
                        $siteconfig->getConfigSetting("DEFAULTPREF_SIMILAR_AUTHOR_TEST"))."
        </td>
        </tr>
        <tr>
	        <td align='left' colspan='2'><img class='icon' src='".getIconUrl("small_arrow.gif")."'>
	        ".__("Select the method for checking whether two author names are counted as 'similar'.")."
	        </td>
	      </tr>
        ";
?>
