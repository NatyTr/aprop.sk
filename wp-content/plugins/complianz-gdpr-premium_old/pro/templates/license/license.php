<div class="field-group">
	<div class="cmplz-field">
		<input id="cmplz_license_key" placeholder="<?php _e("Enter your license key", "complianz-gdpr")?>" name="cmplz_license_key" type="password" class="regular-text" value="<?php esc_attr_e(COMPLIANZ::$license->license_key() ); ?>"/>
		<?php echo COMPLIANZ::$license->get_license_label() ?>
	</div>
</div>


