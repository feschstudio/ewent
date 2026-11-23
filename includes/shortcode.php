<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * [ewent_list town="Krems" cat="Markt" free="1" limit="30"]
 * Einfache Übergangsliste, bis das ewent-Theme die Startseite übernimmt.
 */
add_shortcode( 'ewent_list', function ( $atts ) {
	$a    = shortcode_atts( array( 'town' => '', 'cat' => '', 'free' => '', 'limit' => 60 ), $atts );
	$rows = ewent_occurrences( array(
		'town'  => $a['town'],
		'cat'   => $a['cat'],
		'free'  => (bool) $a['free'],
		'limit' => (int) $a['limit'],
	) );
	if ( ! $rows ) {
		return '<p>Aktuell sind keine Events eingetragen.</p>';
	}
	$out = '<div class="ewent-list">';
	$day = '';
	foreach ( $rows as $r ) {
		if ( $r['date'] !== $day ) {
			$day  = $r['date'];
			$out .= '<h3 class="ewent-day">' . esc_html( wp_date( 'l, j. F', strtotime( $day ) ) ) . '</h3>';
		}
		$out .= '<p class="ewent-item"><a href="' . esc_url( $r['url'] ) . '"><strong>' . esc_html( $r['title'] ) . '</strong></a><br>'
			. esc_html( $r['time'] . ' Uhr · ' . $r['place'] . ', ' . $r['town'] . ' · ' . $r['cat'] . ' · ' . $r['price'] )
			. ( $r['multi'] ? ' · bis ' . esc_html( wp_date( 'j. F', strtotime( $r['last'] ) ) ) : '' )
			. '</p>';
	}
	return $out . '</div>';
} );
