<?php defined( 'ABSPATH' ) or die( 'This script cannot be accessed directly.' );

?>
<div class="us-wizard step-success">
	<div class="us-wizard-body">
		<div class="us-wizard-step active">
			<i class="fas fa-check"></i>
			<h2 class="us-wizard-step-title"><?= __( 'Installation completed', 'us' ) ?></h2>
			<a class="button button-primary" href="<?= home_url() ?>" target="_blank"><?= us_translate( 'Visit Site' ) ?></a>
		</div>
	</div>
</div>
