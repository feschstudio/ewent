<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Liefert alle kommenden Termine veröffentlichter Events, ein Eintrag pro Tag.
 * Sortiert nach Datum/Uhrzeit. Mehrtägige Events erscheinen an jedem Tag.
 */
function ewent_occurrences( $args = array() ) {
	$args  = wp_parse_args( $args, array(
		'town'  => '',
		'cat'   => '',
		'free'  => false,
		'from'  => wp_date( 'Y-m-d' ),
		'limit' => 500,
	) );
	$posts = get_posts( array(
		'post_type'      => 'ewent_event',
		'post_status'    => 'publish',
		'posts_per_page' => 500,
		'no_found_rows'  => true,
	) );
	$out   = array();
	foreach ( $posts as $p ) {
		$town = (string) get_post_meta( $p->ID, '_ew_town', true );
		$cat  = (string) get_post_meta( $p->ID, '_ew_cat', true );
		$pr   = (string) get_post_meta( $p->ID, '_ew_price', true );
		if ( $args['town'] && $town !== $args['town'] ) {
			continue;
		}
		if ( $args['cat'] && $cat !== $args['cat'] ) {
			continue;
		}
		if ( $args['free'] && 'Gratis' !== $pr ) {
			continue;
		}
		$dates = ewent_get_dates( $p->ID );
		if ( ! $dates ) {
			continue;
		}
		$last = $dates[ count( $dates ) - 1 ][0];
		foreach ( $dates as $d ) {
			if ( $d[0] < $args['from'] ) {
				continue;
			}
			$out[] = array(
				'id'      => $p->ID,
				'title'   => get_the_title( $p ),
				'url'     => get_permalink( $p ),
				'date'    => $d[0],
				'time'    => $d[1],
				'last'    => $last,
				'multi'   => count( $dates ) > 1,
				'dates'   => $dates,
				'link'    => (string) get_post_meta( $p->ID, '_ew_link', true ),
				'place'   => (string) get_post_meta( $p->ID, '_ew_place', true ),
				'town'    => $town,
				'cat'     => $cat,
				'price'   => $pr,
				'tier'    => (string) ( get_post_meta( $p->ID, '_ew_tier', true ) ?: 'free' ),
				'org'     => (string) get_post_meta( $p->ID, '_ew_org', true ),
				'manual'  => '1' === get_post_meta( $p->ID, '_ew_manual', true ),
				'excerpt' => wp_strip_all_tags( $p->post_content ),
				'image'   => get_the_post_thumbnail_url( $p, 'large' ) ?: '',
			);
		}
	}
	usort( $out, function ( $a, $b ) {
		return strcmp( $a['date'] . $a['time'], $b['date'] . $b['time'] );
	} );
	return array_slice( $out, 0, (int) $args['limit'] );
}
