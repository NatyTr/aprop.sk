<?php
// Pridanie admin menu stránky
add_action('admin_menu', function () {
	add_menu_page(
		'Options',
		'Options',
		'manage_options',
		'aprop_options',
		'aprop_render_options_page'
	);
});

// Spracovanie formulára
add_action('admin_init', function () {
	if (
		isset($_POST['aprop_footer_nonce']) &&
		wp_verify_nonce($_POST['aprop_footer_nonce'], 'aprop_footer_save')
	) {
		update_option('footer_email', sanitize_email($_POST['footer_email']));
		update_option('footer_ig', esc_url_raw($_POST['footer_ig']));
		update_option('footer_fb', esc_url_raw($_POST['footer_fb']));
		update_option('footer_li', esc_url_raw($_POST['footer_li']));
		update_option('footer_yt', esc_url_raw($_POST['footer_yt']));
		update_option('banner_label', sanitize_text_field($_POST['banner_label']));
		update_option('banner_title', sanitize_text_field($_POST['banner_title']));
		update_option('banner_text', sanitize_textarea_field($_POST['banner_text']));

		if (!empty($_FILES['footer_image_upload']['tmp_name'])) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$upload_id = media_handle_upload('footer_image_upload', 0);
			if (!is_wp_error($upload_id)) {
				$image_url = wp_get_attachment_url($upload_id);
				update_option('footer_image', esc_url_raw($image_url));
			}
		}

		if (!empty($_FILES['banner_image_upload']['tmp_name'])) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$upload_id = media_handle_upload('banner_image_upload', 0);
			if (!is_wp_error($upload_id)) {
				$image_url = wp_get_attachment_url($upload_id);
				update_option('banner_image', esc_url_raw($image_url));
			}
		}


		add_action('admin_notices', function () {
			echo '<div class="notice notice-success is-dismissible"><p>Nastavenia boli uložené.</p></div>';
		});
	}
});

// Render formulára
function aprop_render_options_page() {
	$email      = get_option('footer_email');
	$ig         = get_option('footer_ig');
	$fb         = get_option('footer_fb');
	$li         = get_option('footer_li');
	$yt         = get_option('footer_yt');
	$footer_img = get_option('footer_image');
	$banner_label = get_option('banner_label');
	$banner_title = get_option('banner_title');
	$banner_text  = get_option('banner_text');
	$banner_img = get_option('banner_image');
	?>
	<div class="wrap">
		<h1>Footer Options</h1>
		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field('aprop_footer_save', 'aprop_footer_nonce'); ?>
			<table class="form-table">
				<tr>
					<th scope="row">Kontaktný e-mail</th>
					<td><input type="email" name="footer_email" value="<?php echo esc_attr($email); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row">Instagram</th>
					<td><input type="url" name="footer_ig" value="<?php echo esc_attr($ig); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row">Facebook</th>
					<td><input type="url" name="footer_fb" value="<?php echo esc_attr($fb); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row">LinkedIn</th>
					<td><input type="url" name="footer_li" value="<?php echo esc_attr($li); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row">YouTube</th>
					<td><input type="url" name="footer_yt" value="<?php echo esc_attr($yt); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row">Footer obrázok</th>
					<td>
						<input type="file" name="footer_image_upload" />
						<?php if ($footer_img): ?>
							<div style="margin-top:10px;">
								<img src="<?php echo esc_url($footer_img); ?>" alt="Footer Image" style="max-width: 200px;" />
							</div>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th colspan="2"><h2>Kontajner: Nenašli ste, čo ste hľadali?</h2></th>
				</tr>
				<tr>
					<th scope="row">Banner - Label</th>
					<td><input type="text" name="banner_label" value="<?php echo esc_attr($banner_label); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row">Banner - Title</th>
					<td><input type="text" name="banner_title" value="<?php echo esc_attr($banner_title); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row">Banner - Text</th>
					<td><textarea name="banner_text" rows="5" class="large-text"><?php echo esc_textarea($banner_text); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row">Banner – Obrázok</th>
					<td>
						<input type="file" name="banner_image_upload" />
						<?php if ($banner_img): ?>
							<div style="margin-top:10px;">
								<img src="<?php echo esc_url($banner_img); ?>" alt="Banner Image" style="max-width: 200px;" />
							</div>
						<?php endif; ?>
					</td>
				</tr>

			</table>
			<?php submit_button('Uložiť nastavenia'); ?>
		</form>
	</div>
	<?php
}
