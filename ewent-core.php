<?php
/**
 * Plugin Name: ewent core
 * Description: Veranstaltungskalender Region Krems (ewent. by fesch.): Events, Freigabe, Übernahme-Anfragen, Einreichen, Grundbefüllung.
 * Version: 0.4.0
 * GitHub Plugin URI: feschstudio/ewent
 * Primary Branch: main
 * Author: fesch.
 * Text Domain: ewent-core
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EWENT_VERSION', '0.4.0' );
define( 'EWENT_DIR', plugin_dir_path( __FILE__ ) );
define( 'EWENT_URL', plugin_dir_url( __FILE__ ) );

// Das Theme "ewent." liegt im Plugin und wird mit ihm aktualisiert.
register_theme_directory( EWENT_DIR . 'themes' );

require_once EWENT_DIR . 'includes/cpt.php';
require_once EWENT_DIR . 'includes/meta.php';
require_once EWENT_DIR . 'includes/query.php';
require_once EWENT_DIR . 'includes/rest.php';
require_once EWENT_DIR . 'includes/claims.php';
require_once EWENT_DIR . 'includes/account.php';
require_once EWENT_DIR . 'includes/seed.php';
require_once EWENT_DIR . 'includes/settings.php';
require_once EWENT_DIR . 'includes/shortcode.php';

register_activation_hook( __FILE__, function () {
	ewent_register_types();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );
