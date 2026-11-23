<?php
/**
 * Plugin Name: ewent core
 * Description: Veranstaltungskalender Region Krems (ewent. by fesch.): Events, Freigabe, Übernahme-Anfragen, Einreichen, Grundbefüllung.
 * Version: 0.6.1
 * Author: fesch.
 * Text Domain: ewent-core
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EWENT_VERSION', '0.6.1' );
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
require_once EWENT_DIR . 'includes/shop.php';
require_once EWENT_DIR . 'includes/seed.php';
require_once EWENT_DIR . 'includes/settings.php';
require_once EWENT_DIR . 'includes/shortcode.php';

// Updates aus dem GitHub-Repo: erscheinen im WordPress unter Dashboard > Aktualisierungen.
require_once EWENT_DIR . 'lib/plugin-update-checker/load-v5p6.php';
$ewent_updater = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker( 'https://github.com/feschstudio/ewent/', __FILE__, 'ewent-core' );
$ewent_updater->setBranch( 'main' );
// Nur nötig, wenn das Repo privat ist: in wp-config.php  define( 'EWENT_GITHUB_TOKEN', '...' );
if ( defined( 'EWENT_GITHUB_TOKEN' ) && EWENT_GITHUB_TOKEN ) {
	$ewent_updater->setAuthentication( EWENT_GITHUB_TOKEN );
}

register_activation_hook( __FILE__, function () {
	ewent_register_types();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );
