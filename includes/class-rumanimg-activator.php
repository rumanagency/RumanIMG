<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rumanimg_Activator {

	public static function activate(): void {
		// Create default options on first activation.
		if ( ! get_option( 'rumanimg_settings' ) ) {
			add_option( 'rumanimg_settings', array(
				'version' => RUMANIMG_VERSION,
			) );
		}

		flush_rewrite_rules();
	}
}
