<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Einfache Rate-Limit-Prüfung pro IP (Transient). */
function ewent_rate_limited( $bucket, $max, $window = HOUR_IN_SECONDS ) {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'x';
	$key = 'ewent_rl_' . $bucket . '_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= $max ) {
		return true;
	}
	set_transient( $key, $n + 1, $window );
	return false;
}

function ewent_notify_email() {
	$m = get_option( 'ewent_notify_email' );
	return is_email( $m ) ? $m : get_option( 'admin_email' );
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'ewent/v1', '/events', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => function ( WP_REST_Request $r ) {
			return rest_ensure_response( ewent_occurrences( array(
				'town' => sanitize_text_field( (string) $r->get_param( 'town' ) ),
				'cat'  => sanitize_text_field( (string) $r->get_param( 'cat' ) ),
				'free' => (bool) $r->get_param( 'free' ),
			) ) );
		},
	) );

	register_rest_route( 'ewent/v1', '/submit', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'ewent_rest_submit',
	) );

	register_rest_route( 'ewent/v1', '/newsletter', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'ewent_rest_newsletter',
	) );

	register_rest_route( 'ewent/v1', '/claim', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'ewent_rest_claim',
	) );
} );

function ewent_rest_submit( WP_REST_Request $r ) {
	$p = $r->get_json_params();
	if ( ! is_array( $p ) ) {
		$p = $r->get_body_params();
	}
	// Honeypot: Bots füllen "website" aus.
	if ( ! empty( $p['website'] ) ) {
		return rest_ensure_response( array( 'ok' => true ) );
	}
	if ( ewent_rate_limited( 'submit', 5 ) ) {
		return new WP_Error( 'ewent_rate', 'Zu viele Einreichungen. Bitte versuch es später nochmal.', array( 'status' => 429 ) );
	}
	$title = sanitize_text_field( $p['title'] ?? '' );
	$date  = sanitize_text_field( $p['date'] ?? '' );
	$time  = sanitize_text_field( $p['time'] ?? '' );
	$until = sanitize_text_field( $p['until'] ?? '' );
	$mail  = sanitize_email( $p['mail'] ?? '' );
	if ( '' === $title || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) || ! is_email( $mail ) ) {
		return new WP_Error( 'ewent_invalid', 'Bitte Titel, Datum und eine gültige E-Mail angeben.', array( 'status' => 400 ) );
	}
	$time  = preg_match( '/^\d{2}:\d{2}$/', $time ) ? $time : '00:00';
	$dates = array( array( $date, $time ) );
	if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $until ) && $until > $date ) {
		$d = new DateTime( $date );
		$e = new DateTime( $until );
		$n = 0;
		while ( $d < $e && $n < 60 ) {
			$d->modify( '+1 day' );
			$dates[] = array( $d->format( 'Y-m-d' ), $time );
			$n++;
		}
	}
	$id = wp_insert_post( array(
		'post_type'    => 'ewent_event',
		'post_status'  => 'pending',
		'post_title'   => $title,
		'post_content' => sanitize_textarea_field( $p['desc'] ?? '' ),
	), true );
	if ( is_wp_error( $id ) ) {
		return new WP_Error( 'ewent_fail', 'Das hat leider nicht geklappt.', array( 'status' => 500 ) );
	}
	ewent_set_dates( $id, $dates );
	$cat = sanitize_text_field( $p['cat'] ?? '' );
	update_post_meta( $id, '_ew_cat', in_array( $cat, EWENT_CATS, true ) ? $cat : 'Kultur' );
	update_post_meta( $id, '_ew_place', sanitize_text_field( $p['place'] ?? '' ) );
	update_post_meta( $id, '_ew_town', sanitize_text_field( $p['town'] ?? '' ) );
	update_post_meta( $id, '_ew_price', sanitize_text_field( $p['price'] ?? 'Gratis' ) ?: 'Gratis' );
	update_post_meta( $id, '_ew_org', sanitize_text_field( $p['org'] ?? '' ) );
	update_post_meta( $id, '_ew_credit', sanitize_text_field( $p['credit'] ?? '' ) );
	update_post_meta( $id, '_ew_tier', 'free' );
	update_post_meta( $id, '_ew_link', esc_url_raw( $p['link'] ?? '' ) );
	update_post_meta( $id, '_ew_owner_tel', sanitize_text_field( $p['tel'] ?? '' ) );
	$pack = sanitize_text_field( $p['pack'] ?? 'free' );
	update_post_meta( $id, '_ew_pack_wish', in_array( $pack, array( 'free', 'plus', 'top', 'premium' ), true ) ? $pack : 'free' );
	update_post_meta( $id, '_ew_owner_email', $mail );
	update_post_meta( $id, '_ew_manual', '0' );
	ewent_attach_submitted_image( $id, $p['img'] ?? '', $title );

	wp_mail(
		ewent_notify_email(),
		'Neues Event zur Freigabe: ' . $title,
		"Es wurde ein neues Event eingereicht.\n\nTitel: {$title}\nVeranstalter: {$mail}\n\nFreigeben: " . admin_url( 'post.php?post=' . $id . '&action=edit' )
	);
	return rest_ensure_response( array( 'ok' => true ) );
}

function ewent_rest_claim( WP_REST_Request $r ) {
	if ( ! apply_filters( 'ewent_allow_claims', false ) ) {
		return new WP_Error( 'ewent_claims_off', 'Bitte schick Änderungen per Mail an info@ewent.at.', array( 'status' => 403 ) );
	}
	$p = $r->get_json_params();
	if ( ! is_array( $p ) ) {
		$p = $r->get_body_params();
	}
	if ( ! empty( $p['website'] ) ) {
		return rest_ensure_response( array( 'ok' => true ) );
	}
	if ( ewent_rate_limited( 'claim', 5 ) ) {
		return new WP_Error( 'ewent_rate', 'Zu viele Anfragen. Bitte versuch es später nochmal.', array( 'status' => 429 ) );
	}
	$event_id = (int) ( $p['event'] ?? 0 );
	$mail     = sanitize_email( $p['mail'] ?? '' );
	$event    = $event_id ? get_post( $event_id ) : null;
	if ( ! $event || 'ewent_event' !== $event->post_type || 'publish' !== $event->post_status || ! is_email( $mail ) ) {
		return new WP_Error( 'ewent_invalid', 'Bitte eine gültige E-Mail angeben.', array( 'status' => 400 ) );
	}
	$cid = wp_insert_post( array(
		'post_type'   => 'ewent_claim',
		'post_status' => 'pending',
		'post_title'  => $mail . ' → ' . $event->post_title,
	) );
	if ( $cid && ! is_wp_error( $cid ) ) {
		update_post_meta( $cid, '_ew_claim_event', $event_id );
		update_post_meta( $cid, '_ew_claim_mail', $mail );
		wp_mail(
			ewent_notify_email(),
			'Übernahme-Anfrage: ' . $event->post_title,
			"{$mail} möchte das Event „{$event->post_title}“ übernehmen.\n\nPrüfen: " . admin_url( 'edit.php?post_type=ewent_claim' )
		);
	}
	return rest_ensure_response( array( 'ok' => true ) );
}

/**
 * Newsletter-Anmeldung. Vorerst: Adresse wird als "unbestätigt" gespeichert
 * (Double-Opt-In-Versand folgt in einer späteren Version).
 */
function ewent_rest_newsletter( WP_REST_Request $r ) {
	$p = $r->get_json_params();
	if ( ! is_array( $p ) ) {
		$p = $r->get_body_params();
	}
	if ( ! empty( $p['website'] ) ) {
		return rest_ensure_response( array( 'ok' => true ) );
	}
	if ( ewent_rate_limited( 'newsletter', 5 ) ) {
		return new WP_Error( 'ewent_rate', 'Zu viele Anfragen. Bitte versuch es später nochmal.', array( 'status' => 429 ) );
	}
	$mail = sanitize_email( $p['mail'] ?? '' );
	if ( ! is_email( $mail ) ) {
		return new WP_Error( 'ewent_invalid', 'Bitte eine gültige E-Mail angeben.', array( 'status' => 400 ) );
	}
	$list = get_option( 'ewent_newsletter', array() );
	if ( ! is_array( $list ) ) {
		$list = array();
	}
	if ( ! isset( $list[ $mail ] ) ) {
		$list[ $mail ] = array( 'at' => current_time( 'mysql' ), 'confirmed' => 0 );
		update_option( 'ewent_newsletter', $list, false );
		wp_mail( ewent_notify_email(), 'Newsletter-Anmeldung', "Neue Anmeldung: {$mail}" );
	}
	return rest_ensure_response( array( 'ok' => true ) );
}

/**
 * Titelbild aus der Einreichung (Data-URL, im Browser auf max. 1600 px verkleinert) speichern.
 * Fehler hier brechen die Einreichung nicht ab.
 */
function ewent_attach_submitted_image( $post_id, $data, $title ) {
	if ( ! is_string( $data ) || ! preg_match( '#^data:image/(jpeg|png|webp);base64,#', $data, $m ) ) {
		return;
	}
	$bin = base64_decode( substr( $data, strpos( $data, ',' ) + 1 ), true );
	if ( false === $bin || strlen( $bin ) > 4 * 1024 * 1024 ) {
		return;
	}
	$info = @getimagesizefromstring( $bin );
	if ( ! $info || ! in_array( $info[2], array( IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP ), true ) || $info[0] > 4000 || $info[1] > 4000 ) {
		return;
	}
	$ext = IMAGETYPE_PNG === $info[2] ? 'png' : ( IMAGETYPE_WEBP === $info[2] ? 'webp' : 'jpg' );
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$up = wp_upload_bits( sanitize_file_name( sanitize_title( $title ) . '-' . $post_id . '.' . $ext ), null, $bin );
	if ( ! empty( $up['error'] ) ) {
		return;
	}
	$att = wp_insert_attachment( array(
		'post_mime_type' => image_type_to_mime_type( $info[2] ),
		'post_title'     => sanitize_text_field( $title ),
		'post_status'    => 'inherit',
	), $up['file'], $post_id );
	if ( is_wp_error( $att ) || ! $att ) {
		return;
	}
	wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $up['file'] ) );
	set_post_thumbnail( $post_id, $att );
}
