<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Grundbefüllung: legt die mitgelieferten Events an, überspringt vorhandene (gleicher Titel). */
function ewent_run_seed() {
	$file = EWENT_DIR . 'data/seed-events.json';
	if ( ! file_exists( $file ) ) {
		return array( 0, 0 );
	}
	$rows = json_decode( (string) file_get_contents( $file ), true );
	if ( ! is_array( $rows ) ) {
		return array( 0, 0 );
	}
	$new = 0;
	$old = 0;
	foreach ( $rows as $r ) {
		$exists = get_posts( array(
			'post_type'      => 'ewent_event',
			'post_status'    => 'any',
			'title'          => $r['title'],
			'posts_per_page' => 1,
			'fields'         => 'ids',
		) );
		if ( $exists ) {
			$old++;
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'    => 'ewent_event',
			'post_status'  => 'publish',
			'post_title'   => $r['title'],
			'post_content' => $r['desc'],
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		ewent_set_dates( $id, $r['dates'] );
		update_post_meta( $id, '_ew_place', $r['place'] );
		update_post_meta( $id, '_ew_town', $r['town'] );
		update_post_meta( $id, '_ew_cat', $r['cat'] );
		update_post_meta( $id, '_ew_price', $r['price'] );
		update_post_meta( $id, '_ew_org', $r['org'] );
		update_post_meta( $id, '_ew_tier', 'free' );
		update_post_meta( $id, '_ew_manual', '1' );
		$new++;
	}
	return array( $new, $old );
}

add_action( 'admin_post_ewent_seed', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ewent_seed' ) ) {
		wp_die( 'Nicht erlaubt.' );
	}
	list( $new, $old ) = ewent_run_seed();
	wp_safe_redirect( add_query_arg( array( 'page' => 'ewent-settings', 'seeded' => $new, 'skipped' => $old ), admin_url( 'edit.php?post_type=ewent_event' ) ) );
	exit;
} );

/** Ordnet Mediathek-Bildern (Titel = Event-Titel) das Beitragsbild zu. */
add_action( 'admin_post_ewent_match_images', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ewent_match_images' ) ) {
		wp_die( 'Nicht erlaubt.' );
	}
	$set    = 0;
	$events = get_posts( array(
		'post_type'      => 'ewent_event',
		'post_status'    => 'any',
		'posts_per_page' => -1,
	) );
	foreach ( $events as $e ) {
		if ( has_post_thumbnail( $e->ID ) ) {
			continue;
		}
		$att = get_posts( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image',
			'title'          => $e->post_title,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		) );
		if ( $att && set_post_thumbnail( $e->ID, $att[0] ) ) {
			$set++;
		}
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'ewent-settings', 'matched' => $set ), admin_url( 'edit.php?post_type=ewent_event' ) ) );
	exit;
} );

/** Absender: statt wordpress@… die Adresse der eigenen Domain verwenden. */
add_filter( 'wp_mail_from', function ( $from ) {
	return 0 === strpos( (string) $from, 'wordpress@' ) ? 'info@ewent.at' : $from;
} );
add_filter( 'wp_mail_from_name', function ( $name ) {
	return 'WordPress' === $name ? 'ewent.' : $name;
} );

/** Test-Mail aus den Einstellungen senden und Ergebnis anzeigen. */
add_action( 'admin_post_ewent_test_mail', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Keine Berechtigung.' );
	}
	check_admin_referer( 'ewent_test_mail' );
	$to  = isset( $_POST['to'] ) ? sanitize_email( wp_unslash( $_POST['to'] ) ) : '';
	$err = '';
	$cb  = function ( $e ) use ( &$err ) {
		$err = $e->get_error_message();
	};
	add_action( 'wp_mail_failed', $cb );
	$ok = is_email( $to ) ? wp_mail( $to, 'ewent. Test-Mail', "Wenn du das liest, funktioniert der Mailversand.\n\nGesendet am " . wp_date( 'd.m.Y H:i' ) . ' Uhr von ' . home_url( '/' ) ) : false;
	remove_action( 'wp_mail_failed', $cb );
	set_transient( 'ewent_mail_test_' . get_current_user_id(), array(
		'to'   => $to,
		'ok'   => (bool) $ok,
		'err'  => $err,
		'from' => apply_filters( 'wp_mail_from', 'wordpress@' . wp_parse_url( home_url(), PHP_URL_HOST ) ),
	), 300 );
	wp_safe_redirect( add_query_arg( array( 'page' => 'ewent-settings', 'mailtest' => 1 ), admin_url( 'edit.php?post_type=ewent_event' ) ) );
	exit;
} );
