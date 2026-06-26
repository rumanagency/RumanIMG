<?php
/**
 * Plugin Name:       Ruman IMG
 * Plugin URI:        https://ruman.sa
 * Description:       Image management and optimization plugin by Ruman.
 * Version:           1.0.1
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Saleh — Ruman
 * Author URI:        https://ruman.sa
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rumanimg
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RUMANIMG_VERSION', '1.0.1' );
define( 'RUMANIMG_PATH',    plugin_dir_path( __FILE__ ) );
define( 'RUMANIMG_URL',     plugin_dir_url( __FILE__ ) );
define( 'RUMANIMG_BASENAME', plugin_basename( __FILE__ ) );

if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p>' .
			esc_html__( 'Ruman IMG requires PHP 7.4 or higher.', 'rumanimg' ) .
			'</p></div>';
	} );
	return;
}

require_once RUMANIMG_PATH . 'includes/class-rumanimg-activator.php';
require_once RUMANIMG_PATH . 'includes/class-rumanimg-deactivator.php';

register_activation_hook( __FILE__,   array( 'Rumanimg_Activator',   'activate' ) );
register_deactivation_hook( __FILE__, array( 'Rumanimg_Deactivator', 'deactivate' ) );

require_once RUMANIMG_PATH . 'includes/class-rumanimg.php';

function rumanimg_run() {
	$plugin = new Rumanimg();
	$plugin->run();
}
rumanimg_run();
