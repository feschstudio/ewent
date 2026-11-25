<?php
/**
 * Datenaufbewahrung: 12 Monate nach dem letzten Termin werden Kontaktdaten
 * des Veranstalters gelöscht, das Event bleibt ohne personenbezogene Daten als Archiv.
 * Erledigte Übernahme-Anfragen werden nach 12 Monaten entfernt.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const EWENT_RETENTION_MONTHS = 12;

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'ewent_retention_daily' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'ewent_retention_daily' );
	}
} );

add_action( 'ewent_retention_daily', function () {
	$limit = wp_date( 'Y-m-d', strtotime( '-' . EWENT_RETENTION_MONTHS . ' months' ) );
	$ids   = get_posts( array(
		'post_type'      => 'ewent_event',
		'post_status'    => 'any',
		'posts_per_page' => 200,
		'fields'         => 'ids',
		'meta_query'     => array(
			'relation' => 'OR',
			array( 'key' => '_ew_owner_email', 'value' => '', 'compare' => '!=' ),
			array( 'key' => '_ew_owner_tel', 'value' => '', 'compare' => '!=' ),
		),
	) );
	foreach ( $ids as $id ) {
		$dates = function_exists( 'ewent_get_dates' ) ? ewent_get_dates( $id ) : array();
		if ( ! $dates ) {
			continue;
		}
		$last = end( $dates );
		if ( $last[0] < $limit ) {
			delete_post_meta( $id, '_ew_owner_email' );
			delete_post_meta( $id, '_ew_owner_tel' );
			delete_post_meta( $id, '_ew_photo_rights' );
		}
	}
	$claims = get_posts( array(
		'post_type'      => 'ewent_claim',
		'post_status'    => 'any',
		'posts_per_page' => 200,
		'fields'         => 'ids',
		'date_query'     => array( array( 'before' => '-' . EWENT_RETENTION_MONTHS . ' months' ) ),
	) );
	foreach ( $claims as $cid ) {
		wp_delete_post( $cid, true );
	}
} );
