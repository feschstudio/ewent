<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EWENT_THEME_VERSION', '0.6.2' );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'style', 'script' ) );
} );

add_action( 'wp_enqueue_scripts', function () {
	$u = get_template_directory_uri() . '/assets/';
	wp_enqueue_style( 'ewent', $u . 'ewent.css', array(), EWENT_THEME_VERSION );
	wp_enqueue_script( 'ewent', $u . 'ewent.js', array(), EWENT_THEME_VERSION, true );
	wp_add_inline_script( 'ewent', 'window.EWENT=' . wp_json_encode( ewent_theme_data() ) . ';', 'before' );
	if ( is_singular( 'ewent_event' ) ) {
		wp_enqueue_script( 'ewent-single', $u . 'single.js', array(), EWENT_THEME_VERSION, true );
	}
} );

// Die Startseite braucht kein ewent.js-Listing auf anderen Seiten.
add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	return $tag;
}, 10, 2 );

function ewent_theme_data() {
	$events = function_exists( 'ewent_occurrences' ) ? ewent_occurrences( array( 'limit' => 500 ) ) : array();
	return array(
		'rest'   => esc_url_raw( rest_url( 'ewent/v1/' ) ),
		'home'   => home_url( '/' ),
		'config' => array(
			'btxt' => (string) get_option( 'ewent_banner_text', '' ),
			'blink' => (string) get_option( 'ewent_banner_link', '' ),
			'wk'   => ewent_picks(),
		),
		'events' => ewent_is_home() ? $events : array(),
	);
}

add_action( 'wp_head', function () {
	echo '<meta name="theme-color" content="#F6F3E8">' . "\n";
} );

/** Pfad zu einer rechtlichen Seite (oder #), falls vorhanden. */
function ewent_page_url( $slug ) {
	$p = get_page_by_path( $slug );
	return $p ? get_permalink( $p ) : home_url( '/' . $slug . '/' );
}

/** Beim Aktivieren: Impressum, Datenschutz, AGB als Seiten anlegen (Entwurf-Texte, Platzhalter in [eckigen Klammern]). */
add_action( 'after_switch_theme', function () {
	$file = get_template_directory() . '/inc/legal.json';
	if ( ! file_exists( $file ) ) {
		return;
	}
	$pages = json_decode( file_get_contents( $file ), true );
	foreach ( (array) $pages as $slug => $pg ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $pg['title'],
			'post_content' => $pg['html'] . '<p><em>Entwurf. Angaben in [eckigen Klammern] müssen noch ergänzt und die Texte rechtlich geprüft werden.</em></p>',
		) );
	}
	flush_rewrite_rules();
} );

/** Wochenempfehlung: eine Zeile pro Tipp, "Event-Titel | kurzer Satz". */
function ewent_picks() {
	$out = array();
	foreach ( preg_split( '/\r?\n/', (string) get_option( 'ewent_picks', '' ) ) as $line ) {
		$p = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( '' !== $p[0] ) {
			$out[] = array( $p[0], $p[1] ?? '' );
		}
	}
	return $out;
}

/** Einstellungsseite "Startseite" unter dem ewent.-Menü. */
add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=ewent_event', 'Startseite', 'Startseite', 'manage_options', 'ewent-home', function () {
		if ( isset( $_POST['ewent_home_nonce'] ) && wp_verify_nonce( $_POST['ewent_home_nonce'], 'ewent_home' ) && current_user_can( 'manage_options' ) ) {
			update_option( 'ewent_banner_text', sanitize_text_field( wp_unslash( $_POST['banner_text'] ?? '' ) ) );
			update_option( 'ewent_banner_link', sanitize_text_field( wp_unslash( $_POST['banner_link'] ?? '' ) ) );
			update_option( 'ewent_picks', sanitize_textarea_field( wp_unslash( $_POST['picks'] ?? '' ) ) );
			echo '<div class="notice notice-success"><p>Gespeichert.</p></div>';
		}
		?>
		<div class="wrap"><h1>Startseite</h1>
		<form method="post"><?php wp_nonce_field( 'ewent_home', 'ewent_home_nonce' ); ?>
		<h2>Banner oben</h2>
		<p>Leer lassen = kein Banner.</p>
		<p><label>Text<br><input type="text" class="large-text" name="banner_text" value="<?php echo esc_attr( get_option( 'ewent_banner_text', '' ) ); ?>"></label></p>
		<p><label>Suchbegriff beim Antippen (z. B. Advent)<br><input type="text" class="regular-text" name="banner_link" value="<?php echo esc_attr( get_option( 'ewent_banner_link', '' ) ); ?>"></label></p>
		<h2>Wochenempfehlung</h2>
		<p>Eine Zeile pro Tipp: <code>Event-Titel | kurzer Satz</code>. Der Titel muss genau so heißen wie das Event.</p>
		<p><textarea name="picks" rows="6" class="large-text code"><?php echo esc_textarea( get_option( 'ewent_picks', '' ) ); ?></textarea></p>
		<?php submit_button( 'Speichern' ); ?></form></div>
		<?php
	} );
} );

/** Startseite, Events-Archiv oder eine Seite mit dem Slug "events" zeigen die volle Event-Ansicht. */
function ewent_is_home() {
	if ( get_query_var( 'ewent_konto' ) ) {
		return false;
	}
	return is_front_page() || is_post_type_archive( 'ewent_event' ) || is_page( 'events' );
}

add_filter( 'template_include', function ( $t ) {
	if ( is_page( 'events' ) && ! is_front_page() ) {
		return get_template_directory() . '/front-page.php';
	}
	return $t;
} );

/** Rechtstexte bei neuer Theme-Version aus inc/legal.json in die Seiten übernehmen. */
add_action( 'init', function () {
	if ( get_option( 'ewent_legal_ver' ) === EWENT_THEME_VERSION ) {
		return;
	}
	$file = get_template_directory() . '/inc/legal.json';
	if ( file_exists( $file ) ) {
		foreach ( (array) json_decode( file_get_contents( $file ), true ) as $slug => $pg ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				wp_update_post( array( 'ID' => $page->ID, 'post_title' => $pg['title'], 'post_content' => $pg['html'] . ( false !== strpos( $pg['html'], '[' ) ? '<p><em>Entwurf. Angaben in [eckigen Klammern] müssen noch ergänzt und die Texte rechtlich geprüft werden.</em></p>' : '' ) ) );
			}
		}
	}
	update_option( 'ewent_legal_ver', EWENT_THEME_VERSION );
} );
