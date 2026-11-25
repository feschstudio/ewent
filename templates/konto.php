<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$mail = ewent_session_mail();
$msgs = array(
	'saved'    => 'Gespeichert.',
	'norights' => 'Das Foto wurde nicht hochgeladen: Bitte bestätige, dass du die Rechte daran hast. Der Rest ist gespeichert.',
	'badfile'  => 'Das Foto wurde nicht hochgeladen (nur JPG, PNG oder WebP bis 8 MB). Der Rest ist gespeichert.',
	'denied'   => 'Das hat nicht geklappt. Bitte melde dich neu an.',
	'expired'  => 'Der Link ist abgelaufen oder wurde schon benutzt. Fordere unten einen neuen an.',
);
$msg  = isset( $_GET['msg'] ) ? sanitize_key( $_GET['msg'] ) : '';
$tiers = array( 'free' => 'Gratis', 'plus' => 'Plus', 'top' => 'Top', 'premium' => 'Premium' );
?>
<main class="wrap pagewrap konto">
<?php if ( $msg && isset( $msgs[ $msg ] ) ) : ?><p class="note" role="status" style="padding:12px 16px;background:var(--accent-soft);border-radius:var(--r-sm);margin-bottom:18px"><?php echo esc_html( $msgs[ $msg ] ); ?></p><?php endif; ?>

<?php if ( ! $mail ) : ?>
	<span class="label">Für Veranstalter</span>
	<h1 style="margin:8px 0 12px">Anmelden</h1>
	<p class="note" style="margin-bottom:16px">Gib deine E-Mail ein. Wir schicken dir einen Link, ein Passwort brauchst du nicht. Das funktioniert mit der Adresse, die bei deinem Event hinterlegt ist.</p>
	<form id="kform" novalidate style="max-width:420px">
		<div class="field"><label for="k_mail">E-Mail</label><input class="in" id="k_mail" type="email" autocomplete="email" inputmode="email"></div>
		<p class="note" id="k_msg" role="status" style="margin:8px 0"></p>
		<button class="btn accent block" type="submit">Link senden</button>
	</form>
<?php else : ?>
	<span class="label"><?php echo esc_html( $mail ); ?></span>
	<h1 style="margin:8px 0 18px">Meine Events</h1>
	<?php
	$events = ewent_owner_events( $mail );
	if ( ! $events ) {
		echo '<p class="note">Zu dieser Adresse gibt es noch keine Events.</p>';
	}
	$open = isset( $_GET['e'] ) ? (int) $_GET['e'] : 0;
	foreach ( $events as $ev ) :
		$id    = $ev->ID;
		$m     = function ( $k ) use ( $id ) { return (string) get_post_meta( $id, $k, true ); };
		$tier  = $m( '_ew_tier' ) ?: 'free';
		$lines = array();
		foreach ( ewent_get_dates( $id ) as $d ) {
			$lines[] = $d[0] . ' ' . $d[1];
		}
		$state = 'publish' === $ev->post_status ? 'Online' : 'In Prüfung';
		?>
		<details class="kev" style="border:1.5px solid var(--line);border-radius:var(--r-sm);margin-bottom:14px;background:var(--card)" <?php echo $open === $id ? 'open' : ''; ?>>
			<summary style="cursor:pointer;padding:16px 18px;font-weight:600;list-style:none"><?php echo esc_html( $ev->post_title ); ?> <span class="label" style="margin-left:8px"><?php echo esc_html( $state . ' · ' . $tiers[ $tier ] ); ?></span></summary>
			<?php if ( '1' === $m( '_ew_manual' ) ) : ?>
			<div class="fnote" style="margin:0 18px 18px"><b>Manuell angelegt.</b> Dieses Event haben wir manuell angelegt (alle Events im November und Dezember). Bei Änderungen schick uns bitte kurz eine Mail an <a href="mailto:info@ewent.at">info@ewent.at</a>.</div>
			<?php else : ?>
			<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="padding:0 18px 18px">
				<input type="hidden" name="action" value="ewent_account_save">
				<input type="hidden" name="event" value="<?php echo (int) $id; ?>">
				<?php wp_nonce_field( 'ewent_acc_' . $id ); ?>
				<div class="field"><label>Titel</label><input class="in" name="title" value="<?php echo esc_attr( $ev->post_title ); ?>"></div>
				<p class="note" style="margin:-6px 0 14px">Titeländerungen prüfen wir kurz nachträglich.</p>
				<div class="field"><label>Termine (pro Zeile: JJJJ-MM-TT HH:MM)</label><textarea class="in" name="dates" rows="<?php echo max( 3, min( 8, count( $lines ) + 1 ) ); ?>"><?php echo esc_textarea( implode( "\n", $lines ) ); ?></textarea></div>
				<div class="field"><label>Location</label><input class="in" name="place" value="<?php echo esc_attr( $m( '_ew_place' ) ); ?>"></div>
				<div class="two"><div class="field"><label>Ort</label><select class="in" name="town"><?php foreach ( EWENT_TOWNS as $t ) { echo '<option' . selected( $m( '_ew_town' ), $t, false ) . '>' . esc_html( $t ) . '</option>'; } ?></select></div>
				<div class="field"><label>Kategorie</label><select class="in" name="cat"><?php foreach ( EWENT_CATS as $c ) { echo '<option' . selected( $m( '_ew_cat' ), $c, false ) . '>' . esc_html( $c ) . '</option>'; } ?></select></div></div>
				<div class="two"><div class="field"><label>Preis (z. B. Gratis oder € 12)</label><input class="in" name="price" value="<?php echo esc_attr( $m( '_ew_price' ) ); ?>"></div>
				<div class="field"><label>Veranstalter</label><input class="in" name="org" value="<?php echo esc_attr( $m( '_ew_org' ) ); ?>"></div><div class="field"><label>Bildnachweis (Foto von)</label><input class="in" name="credit" value="<?php echo esc_attr( $m( '_ew_credit' ) ); ?>" placeholder="leer = Veranstalter"></div></div>
				<div class="field"><label>Beschreibung</label><textarea class="in" name="desc" rows="5"><?php echo esc_textarea( $ev->post_content ); ?></textarea></div>
				<?php if ( 'free' !== $tier || apply_filters( 'ewent_start_promo', true ) ) : ?>
					<div class="field"><label>Ticket-/Anmelde-Link</label><input class="in" name="link" inputmode="url" value="<?php echo esc_attr( $m( '_ew_link' ) ); ?>" placeholder="https://"></div>
				<?php else : ?>
					<p class="note" style="margin-bottom:14px">Den Ticket-Button gibt es ab dem Paket Plus.</p>
				<?php endif; ?>
				<div class="field"><label>Flyer / Plakat (erscheint auf der Event-Seite)</label>
					<?php $fl = (int) $m( '_ew_flyer' ); if ( $fl && wp_attachment_is_image( $fl ) ) { echo '<div style="max-width:220px;border-radius:12px;overflow:hidden;margin-bottom:8px">' . wp_get_attachment_image( $fl, 'medium' ) . '</div><label style="display:flex;gap:8px;font-weight:400;margin-bottom:8px"><input type="checkbox" name="remove_flyer" value="1"> Flyer entfernen</label>'; } ?>
					<input type="file" name="flyer" accept="image/jpeg,image/png,image/webp">
				</div>
				<div class="field"><label>Foto</label>
					<?php if ( has_post_thumbnail( $id ) ) { echo '<div style="max-width:220px;border-radius:12px;overflow:hidden;margin-bottom:8px">' . get_the_post_thumbnail( $id, 'medium' ) . '</div>'; } ?>
					<input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
					<label style="display:flex;gap:10px;align-items:flex-start;margin-top:10px;font-weight:400"><input type="checkbox" name="rights" value="1" style="margin-top:4px"> <span>Ich habe die Rechte an diesem Foto und erlaube ewent., es zur Bewerbung dieses Events zu zeigen.</span></label>
				</div>
				<div class="nav" style="display:flex;gap:10px;flex-wrap:wrap"><button class="btn accent" type="submit">Speichern</button><?php if ( 'publish' === $ev->post_status ) : ?><a class="btn ghost" href="<?php echo esc_url( get_permalink( $id ) ); ?>">Event ansehen</a><?php endif; ?></div>
				<?php if ( function_exists( 'ewent_shop_active' ) && ewent_shop_active() ) :
					$names = array( 'plus' => 'Plus', 'top' => 'Top', 'premium' => 'Premium' );
					$pr    = ewent_pack_prices();
					$offer = array();
					foreach ( $names as $k => $nm ) {
						if ( ewent_pack_rank( $k ) > ewent_pack_rank( $tier ) ) {
							$offer[ $k ] = $nm . ' · € ' . max( 0, $pr[ $k ] - $pr[ $tier ] );
						}
					}
					if ( $offer ) : ?>
					<div style="margin:22px 0 0;padding:16px;border:1.5px dashed var(--line);border-radius:var(--r-sm)"><b>Mehr Sichtbarkeit</b>
						<p class="note" style="margin:6px 0 12px">Paket pro Event, inkl. 20 % USt. Bei einem Upgrade zahlst du nur die Differenz.</p>
						<div style="display:flex;gap:8px;flex-wrap:wrap"><?php foreach ( $offer as $k => $label ) : ?><a class="btn ghost" href="<?php echo esc_url( add_query_arg( array( 'ewent_buy' => $k, 'event' => $id, '_wpnonce' => wp_create_nonce( 'ewent_buy_' . $id ) ), ewent_account_url() ) ); ?>"><?php echo esc_html( $label ); ?></a><?php endforeach; ?></div>
					</div>
				<?php endif; endif; ?>
			</form>
			<?php endif; ?>
		</details>
	<?php endforeach; ?>
	<p style="margin-top:24px"><a class="btn ghost" href="<?php echo esc_url( admin_url( 'admin-post.php?action=ewent_logout' ) ); ?>">Abmelden</a></p>
<?php endif; ?>
</main>
<?php get_footer();
