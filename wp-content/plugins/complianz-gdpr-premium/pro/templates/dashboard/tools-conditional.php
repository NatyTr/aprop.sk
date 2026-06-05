<div class="cmplz-tools-row">
	<div>
		<span><?php _e( "Create processing agreements", 'complianz-gdpr' ); ?></span>
	</div>
	<div>
		<a href="<?php echo add_query_arg(array('post_type' => 'cmplz-processing'), admin_url('edit.php'))?>">
			<?php _e( 'Create', 'complianz-gdpr' ) ?>
		</a>
	</div>
</div>
<div class="cmplz-tools-row">
	<div>
		<?php _e( "Create a Data Leak inventory", 'complianz-gdpr' ); ?>
	</div>
	<div>
		<a href="<?php echo add_query_arg(array('post_type' => 'cmplz-dataleak'), admin_url('edit.php'))?>">
			<?php _e( 'Create', 'complianz-gdpr' ) ?>
		</a>
	</div>
</div>
<?php if (cmplz_ab_testing_enabled()) {?>
<div class="cmplz-tools-row">
	<div>
		<?php _e( "Create an A/B test", 'complianz-gdpr' ); ?>
	</div>
	<div>
		<a href="<?php echo admin_url( 'admin.php?page=cmplz-cookiebanner' ) ?>">
			<?php _e( 'Create', 'complianz-gdpr' ) ?>
		</a>
	</div>
</div>
<?php } ?>

<?php if ( cmplz_dnsmpi_required() ) {?>
<div class="cmplz-tools-row">
	<div>
		<?php _e( "DNSMPI Requests", 'complianz-gdpr' ); ?>
	</div>
	<div>
		<a href="<?php echo add_query_arg(array('page' => 'cmplz_dnsmpd'), admin_url('admin.php'))?>">
			<?php _e( 'Visit', 'complianz-gdpr' ) ?>
		</a>
	</div>
</div>
<?php } ?>

<div class="cmplz-tools-row">
	<div>
		<?php _e( "Consent API", 'complianz-gdpr' ); ?>
	</div>
	<div>
		<a href="https://complianz.io">
			<?php _e( 'More information','complianz-gdpr' ) ?>
		</a>
	</div>
</div>
