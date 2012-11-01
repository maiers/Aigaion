<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="help-holder">
  <p class='header1'>Themes</p>
  <p>Aigaion supports the use of themes, you can select it on your 'profile' page. If you don't like the standard themes, feel free to create one yourself. Here is how to do it:
    <ul>
      <li>Create a directory where the theme-files will be stored, below the '/aigaionengine/themes/' directory.</li>
      <li>Copy the <code>themes/default/css/</code>, <code>themes/default/img/</code> and <code>themes/default/icons/</code> directories (but NOT the files in them) to that theme directory. You end up with the following directory structure:
<pre>
 [new-theme-directory]/css/
                        [all style files]
 [new-theme-directory]/icons/
                        [all icons]
 [new-theme-directory]/img/
                        [all other images]
</pre>
      </li>
      <li>You can then start modifying the icons and the styles. Styles go into the file "css/styling.css" and "css/positioning.css"; new icons into the icons directory. You only need to override those style elements and icons that must be changed: the styles and icons in the "default" theme will be used as fallback. </li>
    </ul>
  Note that the personal configuration forms will automatically pick up the new theme as an option.</p>
  <p>If you do not have access to the web server yourself, create the theme and kindly ask your system administrator (<a href="mailto:<?php echo getConfigurationSetting("CFG_ADMINMAIL"); ?>"><?php echo getConfigurationSetting("CFG_ADMIN") ?></a>) to place it in the directory mentioned above.</p>
</div>
