<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rumanimg_Public {

	private string $plugin_name;
	private string $version;

	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function enqueue_styles(): void {
		wp_enqueue_style(
			'rumanimg-public',
			RUMANIMG_URL . 'public/css/rumanimg-public.css',
			array(),
			$this->version
		);
	}

	public function enqueue_scripts(): void {
		wp_enqueue_script(
			'rumanimg-public',
			RUMANIMG_URL . 'public/js/rumanimg-public.js',
			array( 'jquery' ),
			$this->version,
			true
		);
	}
}
