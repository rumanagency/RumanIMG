<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rumanimg_Deactivator {

	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
