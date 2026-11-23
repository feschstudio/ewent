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
