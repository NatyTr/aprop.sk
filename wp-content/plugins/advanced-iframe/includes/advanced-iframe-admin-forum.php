<?php
defined('_VALID_AI') or die('Direct Access to this location is not allowed.');

aiPostboxOpen("id-help-forum", "Forum", $closedArray, "100%", " show-always");
echo "<p>";
_e('Please use the forum for support. Make sure to enable the "Check shortcode" on the options tab first as this maybe already show you which setting is not valid!', 'advanced-iframe');
echo "</p>";
?>
<p>
  <a href="https://www.advanced-iframe.com/forum" target="_blank" id=""
     class="button-primary"><?php _e('Go to the forum', 'advanced-iframe'); ?></a>
</p>
<?php
aiPostboxClose();
?>
