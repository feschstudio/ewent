<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Termine: Array von [ 'YYYY-MM-DD', 'HH:MM' ], sortiert. */
function ewent_get_dates( $post_id ) {
	$raw = get_post_meta( $post_id, '_ew_dates', true );
	$arr = is_string( $raw ) ? json_decode( $raw, true ) : $raw;
	if ( ! is_array( $arr ) ) {
		return array();
	}
	$out = array();
	foreach ( $arr as $row ) {
		if ( is_array( $row ) && isset( $row[0] ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $row[0] ) ) {
			$t     = ( isset( $row[1] ) && preg_match( '/^\d{2}:\d{2}$/', $row[1] ) ) ? $row[1] : '00:00';
			$out[] = array( $row[0], $t );
		}
	}
	usort( $out, function ( $a, $b ) {
		return strcmp( $a[0] . $a[1], $b[0] . $b[1] );
	} );
	return $out;
}

function ewent_set_dates( $post_id, $dates ) {
	update_post_meta( $post_id, '_ew_dates', wp_json_encode( array_values( $dates ) ) );
}

/** Text "YYYY-MM-DD HH:MM" pro Zeile -> Array. */
function ewent_parse_dates_text( $text ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		if ( preg_match( '/^\s*(\d{4}-\d{2}-\d{2})(?:\s+(\d{1,2}):(\d{2}))?\s*$/', $line, $m ) ) {
			$t     = isset( $m[2] ) ? sprintf( '%02d:%s', (int) $m[2], $m[3] ) : '00:00';
			$out[] = array( $m[1], $t );
		}
	}
	return $out;
}

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'ewent_details', 'Event-Details', 'ewent_render_metabox', 'ewent_event', 'normal', 'high' );
} );

function ewent_render_metabox( $post ) {
	wp_nonce_field( 'ewent_save_' . $post->ID, 'ewent_nonce' );
	$g     = function ( $k, $d = '' ) use ( $post ) {
		$v = get_post_meta( $post->ID, $k, true );
		return '' === $v ? $d : $v;
	};
	$lines = array();
	foreach ( ewent_get_dates( $post->ID ) as $d ) {
		$lines[] = $d[0] . ' ' . $d[1];
	}
	$tier = $g( '_ew_tier', 'free' );
	?>
	<style>.ew-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px 20px}.ew-grid label{display:block;font-weight:600;margin-bottom:4px}.ew-grid input,.ew-grid select,.ew-grid textarea{width:100%}.ew-full{grid-column:1/-1}</style>
	<div class="ew-grid">
		<div class="ew-full">
			<label for="ew_dates">Termine (eine Zeile pro Tag: JJJJ-MM-TT HH:MM)</label>
			<textarea id="ew_dates" name="ew_dates" rows="5" placeholder="2026-12-05 10:00&#10;2026-12-06 10:00"><?php echo esc_textarea( implode( "\n", $lines ) ); ?></textarea>
			<p class="description">Mehrtägige Events: einfach jeden Tag in eine Zeile, auch mit unterschiedlichen Uhrzeiten.</p>
		</div>
		<div><label for="ew_place">Location</label><input id="ew_place" name="ew_place" value="<?php echo esc_attr( $g( '_ew_place' ) ); ?>"></div>
		<div><label for="ew_town">Ort</label>
			<input id="ew_town" name="ew_town" list="ew_towns" value="<?php echo esc_attr( $g( '_ew_town' ) ); ?>">
			<datalist id="ew_towns"><?php foreach ( EWENT_TOWNS as $t ) { echo '<option value="' . esc_attr( $t ) . '">'; } ?></datalist>
		</div>
		<div><label for="ew_cat">Kategorie</label>
			<select id="ew_cat" name="ew_cat"><?php foreach ( EWENT_CATS as $c ) { echo '<option' . selected( $g( '_ew_cat' ), $c, false ) . '>' . esc_html( $c ) . '</option>'; } ?></select>
		</div>
		<div><label for="ew_price">Eintritt (Text, z. B. Gratis, € 12, ab € 32,50)</label><input id="ew_price" name="ew_price" value="<?php echo esc_attr( $g( '_ew_price', 'Gratis' ) ); ?>"></div>
		<div><label for="ew_org">Veranstalter</label><input id="ew_org" name="ew_org" value="<?php echo esc_attr( $g( '_ew_org' ) ); ?>"></div>
		<div><label for="ew_credit">Bildnachweis (Foto von; leer = Veranstalter)</label><input id="ew_credit" name="ew_credit" value="<?php echo esc_attr( $g( '_ew_credit' ) ); ?>"></div>
		<div><label for="ew_link">Ticket-/Anmelde-Link</label><input id="ew_link" name="ew_link" type="url" value="<?php echo esc_attr( $g( '_ew_link' ) ); ?>"></div>
		<div><label for="ew_tier">Paket</label>
			<select id="ew_tier" name="ew_tier"><?php foreach ( EWENT_TIERS as $k => $v ) { echo '<option value="' . esc_attr( $k ) . '"' . selected( $tier, $k, false ) . '>' . esc_html( $v ) . '</option>'; } ?></select>
		</div>
		<div><label for="ew_owner_email">Veranstalter-E-Mail (nach Übernahme)</label><input id="ew_owner_email" name="ew_owner_email" type="email" value="<?php echo esc_attr( $g( '_ew_owner_email' ) ); ?>"></div>
		<div class="ew-full"><label><input type="checkbox" name="ew_manual" value="1" <?php checked( $g( '_ew_manual' ), '1' ); ?> style="width:auto"> Von uns eingetragen (zeigt „Ist das dein Event?“)</label></div>
	</div>
	<?php
}

add_action( 'save_post_ewent_event', function ( $post_id ) {
	if ( ! isset( $_POST['ewent_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ewent_nonce'] ) ), 'ewent_save_' . $post_id ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$p = function ( $k ) {
		return isset( $_POST[ $k ] ) ? wp_unslash( $_POST[ $k ] ) : '';
	};
	ewent_set_dates( $post_id, ewent_parse_dates_text( $p( 'ew_dates' ) ) );
	update_post_meta( $post_id, '_ew_place', sanitize_text_field( $p( 'ew_place' ) ) );
	update_post_meta( $post_id, '_ew_town', sanitize_text_field( $p( 'ew_town' ) ) );
	$cat = sanitize_text_field( $p( 'ew_cat' ) );
	update_post_meta( $post_id, '_ew_cat', in_array( $cat, EWENT_CATS, true ) ? $cat : 'Kultur' );
	update_post_meta( $post_id, '_ew_price', sanitize_text_field( $p( 'ew_price' ) ) );
	update_post_meta( $post_id, '_ew_org', sanitize_text_field( $p( 'ew_org' ) ) );
	update_post_meta( $post_id, '_ew_credit', sanitize_text_field( $p( 'ew_credit' ) ) );
	update_post_meta( $post_id, '_ew_link', esc_url_raw( $p( 'ew_link' ) ) );
	$tier = sanitize_key( $p( 'ew_tier' ) );
	update_post_meta( $post_id, '_ew_tier', isset( EWENT_TIERS[ $tier ] ) ? $tier : 'free' );
	update_post_meta( $post_id, '_ew_owner_email', sanitize_email( $p( 'ew_owner_email' ) ) );
	update_post_meta( $post_id, '_ew_manual', $p( 'ew_manual' ) ? '1' : '0' );
} );
