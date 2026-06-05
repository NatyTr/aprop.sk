<?php defined( 'ABSPATH' ) or die( 'This script cannot be accessed directly.' );

global $us_template_directory_uri;

$header_templates = us_config( 'header-templates', array() );
$footer_templates = us_config( 'footer-templates', array() );
$color_schemes = us_config( 'color-schemes', array() );
$typography_templates = us_config( 'typography-templates', array() );

?>
<div class="us-wizard-step from_scratch_with_iframe">
	<div class="us-wizard-step-title for_install"><?= __( 'Install website', 'us' ) ?></div>
	<div class="us-wizard-step-row">
		<div class="us-wizard-preview-wrap">
			<div class="us-wizard-step-title for_header"><?= _x( 'Select website header', 'site top area', 'us' ) ?></div>
			<div class="us-wizard-step-title for_footer"><?= __( 'Select website footer', 'us' ) ?></div>
			<div class="us-wizard-step-title for_colors"><?= __( 'Select desired colors', 'us' ) ?></div>
			<div class="us-wizard-step-title for_fonts"><?= __( 'Select desired fonts', 'us' ) ?></div>
			<div class="us-wizard-preview">
				<iframe src="<?= trailingslashit( home_url(), PHP_URL_HOST ) ?>?us_setup_wizard_preview=1" frameborder="0"></iframe>
			</div>
			<button class="button button-primary action-next-step from_scratch_layout from_scratch_design">
				<?= __( 'Next Step', 'us' ) ?>
			</button>
		</div>
		<div class="us-wizard-templates-list for_header">
			<?php foreach ( $header_templates as $name => $header_template ) { ?>
				<div class="us-wizard-templates-item<?= ( array_key_first( $header_templates ) === $name ? ' active' : '' ) ?>" data-type="header_id" data-id="<?= esc_attr( $name ) ?>">
					<img src="<?= $us_template_directory_uri ?>/common/admin/img/header-templates/<?= $name ?>.png" alt="">
				</div>
			<?php } ?>
		</div>
		<div class="us-wizard-templates-list for_footer">
			<?php foreach ( $footer_templates as $name => $footer_template ) { ?>
				<div class="us-wizard-templates-item<?= ( array_key_first( $footer_templates ) === $name ? ' active' : '' ) ?>" data-type="footer_id" data-id="<?= esc_attr( $name ) ?>">
					<img src="<?= $us_template_directory_uri ?>/common/admin/img/footer-templates/<?= $name ?>.jpg" alt="">
				</div>
			<?php } ?>
		</div>
		<div class="us-wizard-templates-list for_colors">
			<?php foreach ( $color_schemes as $key => &$scheme ) { ?>
				<div class="us-wizard-templates-item<?= ( array_key_first( $color_schemes ) === $key ? ' active' : '' ) ?>" data-type="scheme_id" data-id="<?= $key ?>">
					<?= us_color_scheme_preview( $scheme ) ?>
				</div>
			<?php } ?>
		</div>
		<div class="us-wizard-templates-list for_fonts">
			<?php foreach ( $typography_templates as $name => $typography_template ) { ?>
				<div class="us-wizard-templates-item<?= ( array_key_first( $typography_templates ) === $name ? ' active' : '' ) ?>" data-type="font_id" data-id="<?= $name ?>">
					<img src="<?= $us_template_directory_uri ?>/common/admin/img/typography-templates/<?= $name ?>.png" alt="">
				</div>
			<?php } ?>
		</div>
	</div>
</div>
<div class="us-wizard-step from_scratch_plugins">
	<div class="us-wizard-step-title"><?= __( 'Select needed plugins', 'us' ) ?></div>
	<?php us_setup_wizard_load_template( 'common/admin/templates/sw_select_plugins' ); ?>
	<div class="us-wizard-step-footer">
		<button class="button button-primary action-select-addons">
			<?= __( 'Next Step', 'us' ) ?>
		</button>
	</div>
</div>
