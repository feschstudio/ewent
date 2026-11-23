<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Spalten für Übernahme-Anfragen */
add_filter( 'manage_ewent_claim_posts_columns', function () {
	return array(
		'cb'       => '<input type="checkbox" />',
		'title'    => 'Anfrage',
		'ew_event' => 'Event',
		'ew_mail'  => 'E-Mail',
		'ew_state' => 'Status',
		'ew_act'   => 'Aktion',
	);
} );

add_action( 'manage_ewent_claim_posts_custom_column', function ( $col, $id ) {
	$eid   = (int) get_post_meta( $id, '_ew_claim_event', true );
	$state = get_post_meta( $id, '_ew_claim_state', true ) ?: 'offen';
	switch ( $col ) {
		case 'ew_event':
			echo $eid ? '<a href="' . esc_url( get_edit_post_link( $eid ) ) . '">' . esc_html( get_the_title( $eid ) ) . '</a>' : '–';
			break;
		case 'ew_mail':
			echo esc_html( get_post_meta( $id, '_ew_claim_mail', true ) );
			break;
		case 'ew_state':
			echo esc_html( $state );
			break;
		case 'ew_act':
			if ( 'offen' === $state ) {
				$ok = wp_nonce_url( admin_url( 'admin-post.php?action=ewent_claim_decide&claim=' . $id . '&do=approve' ), 'ewent_claim_' . $id );
				$no = wp_nonce_url( admin_url( 'admin-post.php?action=ewent_claim_decide&claim=' . $id . '&do=reject' ), 'ewent_claim_' . $id );
				echo '<a class="button button-primary" href="' . esc_url( $ok ) . '">Bestätigen</a> <a class="button" href="' . esc_url( $no ) . '">Ablehnen</a>';
			}
			break;
	}
}, 10, 2 );

add_action( 'admin_post_ewent_claim_decide', function () {
	$id = isset( $_GET['claim'] ) ? (int) $_GET['claim'] : 0;
	if ( ! $id || ! current_user_can( 'edit_post', $id ) || ! check_admin_referer( 'ewent_claim_' . $id ) ) {
		wp_die( 'Nicht erlaubt.' );
	}
	$do    = isset( $_GET['do'] ) ? sanitize_key( $_GET['do'] ) : '';
	$eid   = (int) get_post_meta( $id, '_ew_claim_event', true );
	$mail  = get_post_meta( $id, '_ew_claim_mail', true );
	$title = $eid ? get_the_title( $eid ) : '';
	if ( 'approve' === $do && $eid && is_email( $mail ) ) {
		update_post_meta( $eid, '_ew_owner_email', $mail );
		update_post_meta( $eid, '_ew_manual', '0' );
		update_post_meta( $id, '_ew_claim_state', 'bestätigt' );
		wp_mail( $mail, 'Dein Event auf ewent.: ' . $title, "Hallo,\n\ndu bist jetzt als Veranstalter von „{$title}“ eingetragen. Zum Bearbeiten meldest du dich hier mit deiner E-Mail an (du bekommst einen Link, ein Passwort brauchst du nicht):\n\n" . home_url( '/konto/' ) . "\n\newent. by fesch." );
	} elseif ( 'reject' === $do ) {
		update_post_meta( $id, '_ew_claim_state', 'abgelehnt' );
	}
	wp_safe_redirect( admin_url( 'edit.php?post_type=ewent_claim' ) );
	exit;
} );
