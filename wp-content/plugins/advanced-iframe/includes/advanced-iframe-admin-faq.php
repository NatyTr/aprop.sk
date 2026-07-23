<?php
defined('_VALID_AI') or die('Direct Access to this location is not allowed.');

aiPostboxOpen("id-help-faq", "FAQ", $closedArray, "100%", " show-always");
echo "<p>";
_e('The FAQ is not included in the plugin directly as it is updated frequently on the website.', 'advanced-iframe');
echo "</p>";
?>
  <p>
    <a href="//www.advanced-iframe.com/advanced-iframe/advanced-iframe-faq" target="_blank" id="faq"
       class="button-primary"><?php _e('Go to the FAQ', 'advanced-iframe'); ?></a>
  </p>
<?php
aiPostboxClose();
?>