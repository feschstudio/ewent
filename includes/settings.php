<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=ewent_event', 'ewent. Einstellungen', 'Einstellungen', 'manage_options', 'ewent-settings', 'ewent_settings_page' );
} );

add_action( 'admin_init', function () {
	register_setting( 'ewent_settings', 'ewent_notify_email', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_email',
		'default'           => '',
	) );
} );

function ewent_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$seeded = isset( $_GET['seeded'] ) ? (int) $_GET['seeded'] : null;
	?>
	<div class="wrap">
		<h1>ewent. Einstellungen</h1>
		<?php if ( isset( $_GET['matched'] ) ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( (int) $_GET['matched'] . ' Beitragsbilder gesetzt.' ); ?></p></div>
		<?php endif; ?>
		<?php if ( null !== $seeded ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $seeded . ' Events angelegt, ' . ( isset( $_GET['skipped'] ) ? (int) $_GET['skipped'] : 0 ) . ' waren schon vorhanden.' ); ?></p></div>
		<?php endif; ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'ewent_settings' ); ?>
			<table class="form-table">
				<tr>
					<th><label for="ewent_notify_email">Benachrichtigungen an</label></th>
					<td><input type="email" id="ewent_notify_email" name="ewent_notify_email" class="regular-text" value="<?php echo esc_attr( get_option( 'ewent_notify_email', 'info@ewent.at' ) ); ?>">
					<p class="description">Neue Events und Übernahme-Anfragen werden hierher gemeldet.</p></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
		<hr>
		<h2>Grundbefüllung</h2>
		<p>Legt die 18 manuell recherchierten Events (Adventzeit 2026) an. Bereits vorhandene Titel werden übersprungen.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="ewent_seed">
			<?php wp_nonce_field( 'ewent_seed' ); ?>
			<?php submit_button( 'Grundbefüllung importieren', 'secondary' ); ?>
		</form>
		<hr>
		<h2>Bilder zuordnen</h2>
		<p>Setzt als Beitragsbild das Bild aus der Mediathek, dessen Titel genau dem Event-Titel entspricht. Events mit vorhandenem Beitragsbild bleiben unverändert.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="ewent_match_images">
			<?php wp_nonce_field( 'ewent_match_images' ); ?>
			<?php submit_button( 'Bilder zuordnen', 'secondary' ); ?>
		</form>
	</div>
	<?php
}
