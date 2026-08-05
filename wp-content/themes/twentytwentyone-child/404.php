<?php
/**
 * 404 template.
 *
 * @package Twenty_Twenty_One_Child
 */

get_header();

$home_url    = home_url( '/' );
$drones_url  = home_url( '/drony/' );
$contact_url = home_url( '/kontakt/' );
?>

<main class="aprop-404" id="main">
	<div class="aprop-404__panel">
		<p class="aprop-404__code" aria-hidden="true">404</p>
		<p class="aprop-404__eyebrow"><?php esc_html_e( 'Stránka neexistuje', 'aprop' ); ?></p>
		<h1 class="aprop-404__title"><?php esc_html_e( 'Túto stránku sme nenašli', 'aprop' ); ?></h1>
		<p class="aprop-404__text">
			<?php esc_html_e( 'Odkaz môže byť neplatný alebo stránka už nie je dostupná. Skúste sa vrátiť na úvod, pozrieť drony, alebo niečo vyhľadať.', 'aprop' ); ?>
		</p>

		<form class="aprop-404__form" role="search" method="get" action="<?php echo esc_url( $home_url ); ?>">
			<label class="screen-reader-text" for="aprop-404-search"><?php esc_html_e( 'Hľadať', 'aprop' ); ?></label>
			<input
				id="aprop-404-search"
				class="aprop-404__input"
				type="search"
				name="s"
				placeholder="<?php esc_attr_e( 'Hľadať produkty a články…', 'aprop' ); ?>"
			/>
			<button class="aprop-404__submit" type="submit" aria-label="<?php esc_attr_e( 'Hľadať', 'aprop' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
					<circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"></circle>
					<path d="M20 20L16.5 16.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
				</svg>
			</button>
		</form>

		<div class="aprop-404__actions">
			<a class="btn-primary btn-black" href="<?php echo esc_url( $home_url ); ?>">
				<?php esc_html_e( 'Späť na úvod', 'aprop' ); ?>
			</a>
			<a class="btn-primary" href="<?php echo esc_url( $drones_url ); ?>">
				<?php esc_html_e( 'Pozrieť drony', 'aprop' ); ?>
			</a>
			<a class="btn-primary" href="<?php echo esc_url( $contact_url ); ?>">
				<?php esc_html_e( 'Kontakt', 'aprop' ); ?>
			</a>
		</div>
	</div>
</main>

<?php
get_footer();
