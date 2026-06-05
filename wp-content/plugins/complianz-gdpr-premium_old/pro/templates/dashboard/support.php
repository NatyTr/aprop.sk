<?php
$placeholder = __("When you send this form we will attach the following information: license key, your settings, your domain and a list of active plugins", "complianz-gdpr");
//closing form in footer
?>
<form action="" method="POST">
		<?php wp_nonce_field('cmplz-support-request', 'cmplz-nonce') ?>
		<textarea name="cmplz_support_request" required placeholder="<?php echo $placeholder?>"></textarea>
