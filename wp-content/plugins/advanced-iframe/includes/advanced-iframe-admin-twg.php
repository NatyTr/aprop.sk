<?php
defined('_VALID_AI') or die('Direct Access to this location is not allowed.');

aiPostboxOpen("id-help-twg", "TinyWebGallery", $closedArray, "100%", " show-always");
?>
  <p>
    <?php _e('This plugin is the extract for the iframe wrapper which was written for the <a href="//www.tinywebgallery.com" target="_blank">TinyWebGallery</a>. I needed an iframe wrapper that could do more than simply include a page. It needed to pass parameters to the iframe and modify the template on the fly to get more space for TWG. If you want to integrate TWG please use the "TinyWebGallery wrapper". It offers specific features only needed for the gallery. I hope this standalone wrapper is useful for other Wordpress users as well.', 'advanced-iframe'); ?>
  </p>
  <p>
    <?php _e('Please go <a href="//www.tinywebgallery.com" target="_blank">www.tinywebgallery.com</a> for details.', 'advanced-iframe'); ?>
  </p>
<?php
aiPostboxClose();
?>