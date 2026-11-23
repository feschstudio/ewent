<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const EWENT_CATS  = array( 'Wein', 'Kulinarik', 'Musik', 'Markt', 'Kultur', 'Familie', 'Sport' );
const EWENT_TOWNS = array( 'Krems', 'Stein', 'Dürnstein', 'Spitz', 'Weißenkirchen', 'Furth bei Göttweig', 'Langenlois', 'Gobelsburg', 'Traismauer', 'Herzogenburg' );
const EWENT_TIERS = array( 'free' => 'Gratis', 'plus' => 'Plus', 'top' => 'Top', 'premium' => 'Premium' );

function ewent_register_types() {
	register_post_type( 'ewent_event', array(
		'labels'          => array(
			'name'          => 'Events',
			'singular_name' => 'Event',
			'add_new_item'  => 'Event hinzufügen',
			'edit_item'     => 'Event bearbeiten',
			'menu_name'     => 'ewent.',
		),
		'public'          => true,
		'show_in_rest'    => false,
		'menu_icon'       => 'dashicons-calendar-alt',
		'menu_position'   => 25,
		'supports'        => array( 'title', 'editor', 'thumbnail' ),
		'has_archive'     => 'events',
		'rewrite'         => array( 'slug' => 'event', 'with_front' => false ),
		'capability_type' => 'post',
	) );

	register_post_type( 'ewent_claim', array(
		'labels'          => array(
			'name'          => 'Übernahme-Anfragen',
			'singular_name' => 'Übernahme-Anfrage',
			'menu_name'     => 'Übernahme-Anfragen',
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=ewent_event',
		'supports'        => array( 'title' ),
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
	) );
}
add_action( 'init', 'ewent_register_types' );

/* Spalten in der Event-Liste */
add_filter( 'manage_ewent_event_posts_columns', function ( $c ) {
	return array(
		'cb'        => $c['cb'],
		'title'     => 'Titel',
		'ew_next'   => 'Nächster Termin',
		'ew_town'   => 'Ort',
		'ew_cat'    => 'Kategorie',
		'ew_tier'   => 'Paket',
		'ew_owner'  => 'Veranstalter',
		'date'      => 'Status',
	);
} );
add_action( 'manage_ewent_event_posts_custom_column', function ( $col, $id ) {
	switch ( $col ) {
		case 'ew_next':
			$d = ewent_get_dates( $id );
			echo $d ? esc_html( wp_date( 'D j. M', strtotime( $d[0][0] ) ) . ' ' . $d[0][1] ) . ( count( $d ) > 1 ? ' (+' . ( count( $d ) - 1 ) . ')' : '' ) : '–';
			break;
		case 'ew_town':
			echo esc_html( get_post_meta( $id, '_ew_town', true ) );
			break;
		case 'ew_cat':
			echo esc_html( get_post_meta( $id, '_ew_cat', true ) );
			break;
		case 'ew_tier':
			$t = get_post_meta( $id, '_ew_tier', true ) ?: 'free';
			echo esc_html( EWENT_TIERS[ $t ] ?? $t );
			break;
		case 'ew_owner':
			$o = get_post_meta( $id, '_ew_owner_email', true );
			echo $o ? esc_html( $o ) : '<span style="color:#999">nicht übernommen</span>';
			break;
	}
}, 10, 2 );
