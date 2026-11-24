<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Veranstalter-Konto ohne Passwort: Login-Link per Mail, Sitzung per signiertem Cookie.
 * Seite: /konto/
 */

const EWENT_COOKIE = 'ewent_org';

add_action( 'init', function () {
	add_rewrite_rule( '^konto/?$', 'index.php?ewent_konto=1', 'top' );
	add_filter( 'query_vars', function ( $v ) {
		$v[] = 'ewent_konto';
		return $v;
	} );
	if ( get_option( 'ewent_rewrite_ver' ) !== EWENT_VERSION ) {
		flush_rewrite_rules();
		update_option( 'ewent_rewrite_ver', EWENT_VERSION );
	}
} );

function ewent_account_url() {
	return home_url( '/konto/' );
}

/* ---------- Sitzung ---------- */

function ewent_session_sign( $mail, $exp ) {
	return hash_hmac( 'sha256', $mail . '|' . $exp, wp_salt( 'auth' ) );
}

function ewent_session_start( $mail ) {
	$exp = time() + 30 * DAY_IN_SECONDS;
	$val = base64_encode( $mail ) . '.' . $exp . '.' . ewent_session_sign( $mail, $exp );
	setcookie( EWENT_COOKIE, $val, array(
		'expires'  => $exp,
		'path'     => '/',
		'secure'   => is_ssl(),
		'httponly' => true,
		'samesite' => 'Lax',
	) );
	$_COOKIE[ EWENT_COOKIE ] = $val;
}

function ewent_session_mail() {
	if ( empty( $_COOKIE[ EWENT_COOKIE ] ) ) {
		return '';
	}
	$p = explode( '.', (string) $_COOKIE[ EWENT_COOKIE ] );
	if ( 3 !== count( $p ) ) {
		return '';
	}
	$mail = base64_decode( $p[0], true );
	$exp  = (int) $p[1];
	if ( ! $mail || $exp < time() || ! hash_equals( ewent_session_sign( $mail, $exp ), $p[2] ) ) {
		return '';
	}
	return is_email( $mail ) ? $mail : '';
}

function ewent_session_end() {
	setcookie( EWENT_COOKIE, '', array( 'expires' => time() - DAY_IN_SECONDS, 'path' => '/' ) );
	unset( $_COOKIE[ EWENT_COOKIE ] );
}

/* ---------- Events des Veranstalters ---------- */

function ewent_owner_events( $mail ) {
	return get_posts( array(
		'post_type'      => 'ewent_event',
		'post_status'    => array( 'publish', 'pending', 'draft' ),
		'posts_per_page' => 100,
		'no_found_rows'  => true,
		'meta_query'     => array( array( 'key' => '_ew_owner_email', 'value' => $mail ) ),
	) );
}

function ewent_owns( $post_id, $mail ) {
	$p = get_post( $post_id );
	return $p && 'ewent_event' === $p->post_type && $mail && strtolower( (string) get_post_meta( $post_id, '_ew_owner_email', true ) ) === strtolower( $mail );
}

/* ---------- Login-Link anfordern (REST) ---------- */

add_action( 'rest_api_init', function () {
	register_rest_route( 'ewent/v1', '/login', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => function ( WP_REST_Request $r ) {
			$p = $r->get_json_params();
			if ( ! is_array( $p ) ) {
				$p = $r->get_body_params();
			}
			if ( ! empty( $p['website'] ) ) {
				return rest_ensure_response( array( 'ok' => true ) );
			}
			if ( ewent_rate_limited( 'login', 6 ) ) {
				return new WP_Error( 'ewent_rate', 'Zu viele Anfragen. Bitte versuch es später nochmal.', array( 'status' => 429 ) );
			}
			$mail = sanitize_email( $p['mail'] ?? '' );
			if ( ! is_email( $mail ) ) {
				return new WP_Error( 'ewent_invalid', 'Bitte eine gültige E-Mail eingeben.', array( 'status' => 400 ) );
			}
			// Immer dieselbe Antwort, damit niemand Adressen abfragen kann.
			if ( ewent_owner_events( $mail ) ) {
				$token = wp_generate_password( 40, false, false );
				set_transient( 'ewent_tok_' . hash( 'sha256', $token ), $mail, 15 * MINUTE_IN_SECONDS );
				$url = add_query_arg( 'ewent_login', $token, ewent_account_url() );
				wp_mail( $mail, 'Dein Login bei ewent.', "Hallo,\n\nmit diesem Link meldest du dich bei ewent. an (gilt 15 Minuten, nur einmal verwendbar):\n\n{$url}\n\nWenn du das nicht angefordert hast, kannst du diese Mail ignorieren.\n\newent. by fesch." );
			}
			return rest_ensure_response( array( 'ok' => true ) );
		},
	) );
} );

/* ---------- Link einlösen ---------- */

add_action( 'template_redirect', function () {
	if ( ! get_query_var( 'ewent_konto' ) ) {
		return;
	}
	if ( ! empty( $_GET['ewent_login'] ) ) {
		$key  = 'ewent_tok_' . hash( 'sha256', sanitize_text_field( wp_unslash( $_GET['ewent_login'] ) ) );
		$mail = get_transient( $key );
		if ( $mail ) {
			delete_transient( $key );
			ewent_session_start( $mail );
			wp_safe_redirect( ewent_account_url() );
		} else {
			wp_safe_redirect( add_query_arg( 'msg', 'expired', ewent_account_url() ) );
		}
		exit;
	}
} );

/* ---------- Speichern / Logout ---------- */

add_action( 'admin_post_nopriv_ewent_logout', 'ewent_do_logout' );
add_action( 'admin_post_ewent_logout', 'ewent_do_logout' );
function ewent_do_logout() {
	ewent_session_end();
	wp_safe_redirect( ewent_account_url() );
	exit;
}

add_action( 'admin_post_nopriv_ewent_account_save', 'ewent_account_save' );
add_action( 'admin_post_ewent_account_save', 'ewent_account_save' );
function ewent_account_save() {
	$mail = ewent_session_mail();
	$id   = (int) ( $_POST['event'] ?? 0 );
	if ( ! $mail || ! ewent_owns( $id, $mail ) || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ewent_acc_' . $id ) ) {
		wp_safe_redirect( add_query_arg( 'msg', 'denied', ewent_account_url() ) );
		exit;
	}
	if ( '1' === get_post_meta( $id, '_ew_manual', true ) ) {
		wp_safe_redirect( add_query_arg( 'msg', 'denied', ewent_account_url() ) );
		exit;
	}
	$g     = function ( $k ) {
		return isset( $_POST[ $k ] ) ? wp_unslash( $_POST[ $k ] ) : '';
	};
	$old   = get_the_title( $id );
	$title = sanitize_text_field( $g( 'title' ) );
	$upd   = array( 'ID' => $id, 'post_content' => sanitize_textarea_field( $g( 'desc' ) ) );
	if ( '' !== $title ) {
		$upd['post_title'] = $title;
	}
	wp_update_post( $upd );
	$cat = sanitize_text_field( $g( 'cat' ) );
	update_post_meta( $id, '_ew_cat', in_array( $cat, EWENT_CATS, true ) ? $cat : get_post_meta( $id, '_ew_cat', true ) );
	$town = sanitize_text_field( $g( 'town' ) );
	update_post_meta( $id, '_ew_town', in_array( $town, EWENT_TOWNS, true ) ? $town : get_post_meta( $id, '_ew_town', true ) );
	update_post_meta( $id, '_ew_place', sanitize_text_field( $g( 'place' ) ) );
	update_post_meta( $id, '_ew_price', sanitize_text_field( $g( 'price' ) ) ?: 'Gratis' );
	update_post_meta( $id, '_ew_org', sanitize_text_field( $g( 'org' ) ) );
	update_post_meta( $id, '_ew_credit', sanitize_text_field( $g( 'credit' ) ) );
	$tier = (string) ( get_post_meta( $id, '_ew_tier', true ) ?: 'free' );
	if ( 'free' !== $tier ) {
		update_post_meta( $id, '_ew_link', esc_url_raw( $g( 'link' ) ) );
	}
	$dates = ewent_parse_dates_text( $g( 'dates' ) );
	if ( $dates ) {
		ewent_set_dates( $id, $dates );
	}

	$msg = 'saved';
	if ( ! empty( $_FILES['photo']['name'] ) ) {
		if ( empty( $_POST['rights'] ) ) {
			$msg = 'norights';
		} else {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$f = $_FILES['photo'];
			$ok = $f['size'] <= 8 * MB_IN_BYTES && in_array( wp_check_filetype( $f['name'] )['type'], array( 'image/jpeg', 'image/png', 'image/webp' ), true );
			if ( ! $ok ) {
				$msg = 'badfile';
			} else {
				$att = media_handle_upload( 'photo', $id );
				if ( is_wp_error( $att ) ) {
					$msg = 'badfile';
				} else {
					set_post_thumbnail( $id, $att );
					update_post_meta( $id, '_ew_photo_rights', $mail . ' · ' . current_time( 'mysql' ) );
				}
			}
		}
	}
	if ( $title && $title !== $old ) {
		wp_mail( ewent_notify_email(), 'Titel geändert: ' . $title, "Der Veranstalter {$mail} hat den Titel geändert.\n\nAlt: {$old}\nNeu: {$title}\n\n" . admin_url( 'post.php?post=' . $id . '&action=edit' ) );
	}
	wp_safe_redirect( add_query_arg( array( 'msg' => $msg, 'e' => $id ), ewent_account_url() ) );
	exit;
}

/* ---------- Seite /konto/ ---------- */

add_filter( 'template_include', function ( $t ) {
	if ( get_query_var( 'ewent_konto' ) ) {
		status_header( 200 );
		return EWENT_DIR . 'templates/konto.php';
	}
	return $t;
}, 99 );

add_filter( 'pre_get_document_title', function ( $t ) {
	return get_query_var( 'ewent_konto' ) ? 'Mein Konto – ewent.' : $t;
} );
add_filter( 'wp_robots', function ( $r ) {
	if ( get_query_var( 'ewent_konto' ) ) {
		$r['noindex'] = true;
	}
	return $r;
} );

add_action( 'wp_enqueue_scripts', function () {
	if ( get_query_var( 'ewent_konto' ) ) {
		wp_enqueue_script( 'ewent-konto', EWENT_URL . 'assets/konto.js', array(), EWENT_VERSION, true );
		wp_localize_script( 'ewent-konto', 'EWENT_KONTO', array( 'rest' => esc_url_raw( rest_url( 'ewent/v1/' ) ) ) );
	}
} );
