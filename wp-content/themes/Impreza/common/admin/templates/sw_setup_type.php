<?php defined( 'ABSPATH' ) or die( 'This script cannot be accessed directly.' );

?>
<div class="us-wizard-step setup_type active">
	<div class="us-wizard-step-title"><?= __( 'Select setup type', 'us' ) ?></div>
	<div class="us-wizard-setup-type">
		<label>
			<input type="radio" name="setup_type" value="prebuilt" checked>
			<div class="us-wizard-setup-type-item" for="prebuilt">
				<div class="us-wizard-setup-type-item-title"><?= __( 'Pre-built website', 'us' ) ?></div>
				<span><?= __( 'Use one of 50+ smart pre-built websites.', 'us' ) ?></span>
			</div>
		</label>
		<label>
			<input type="radio" name="setup_type" value="from_scratch">
			<div class="us-wizard-setup-type-item" for="from_scratch">
				<div class="us-wizard-setup-type-item-title"><?= __( 'Site from scratch', 'us' ) ?></div>
				<span><?= __( 'Create a website with selected layout and design.', 'us' ) ?></span>
			</div>
		</label>
	</div>
	<div class="us-wizard-step-footer">
		<button type="button" class="button button-primary action-select-type">
			<span><?= __( 'Next Step', 'us' ) ?></span>
		</button>
	</div>
</div>
