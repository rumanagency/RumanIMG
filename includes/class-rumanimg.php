<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rumanimg {

	protected string $plugin_name = 'rumanimg';
	protected string $version;

	public function __construct() {
		$this->version = RUMANIMG_VERSION;
		$this->load_dependencies();
		$this->set_locale();
		$this->register_blocks();
		$this->register_ajax();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->register_duplicate();
	}

	private function load_dependencies(): void {
		require_once RUMANIMG_PATH . 'admin/class-rumanimg-admin.php';
		require_once RUMANIMG_PATH . 'public/class-rumanimg-public.php';
		require_once RUMANIMG_PATH . 'includes/class-rumanimg-duplicate.php';
	}

	private function register_blocks(): void {
		add_action( 'init', function () {
			$block = register_block_type( RUMANIMG_PATH . 'build/blocks/image-parser' );

			if ( $block && ! empty( $block->editor_script_handles ) ) {
				$handle = $block->editor_script_handles[0];

				// JS translations.
				wp_set_script_translations( $handle, 'rumanimg', RUMANIMG_PATH . 'languages' );

				// Pass AJAX data to the block editor script.
				wp_localize_script( $handle, 'rumanimg_block', [
					'ajax_url'      => admin_url( 'admin-ajax.php' ),
					'resolve_nonce' => wp_create_nonce( 'rumanimg_resolve_url' ),
				] );
			}
		} );
	}

	private function register_ajax(): void {
		// Resolve an external image page URL to the original full-resolution image URL.
		// Used for hosts (e.g. imgbb) where the embed src is a display/medium version.
		add_action( 'wp_ajax_rumanimg_resolve_url', [ $this, 'ajax_resolve_url' ] );
	}

	public function ajax_resolve_url(): void {
		check_ajax_referer( 'rumanimg_resolve_url', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
		}

		$page_url = esc_url_raw( sanitize_text_field( wp_unslash( $_POST['pageUrl'] ?? '' ) ) );

		if ( ! $page_url ) {
			wp_send_json_error( [ 'message' => 'No URL provided' ] );
		}

		$response = wp_remote_get( $page_url, [
			'timeout' => 8,
			'headers' => [
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
				'Accept'     => 'text/html,application/xhtml+xml',
			],
		] );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [ 'message' => 'Could not fetch page' ] );
		}

		$body = wp_remote_retrieve_body( $response );

		// ── imgbb / Chevereto ──────────────────────────────────────────────
		// Chevereto embeds JSON image data in the page. The original file URL
		// ("url" key) always appears before the display/thumbnail URLs, so the
		// first i.ibb.co match in the page source is the original image.
		if ( preg_match( '#i\.ibb\.co/[A-Za-z0-9]+/[A-Za-z0-9._-]+\.[a-z]{2,5}#i', $body, $m ) ) {
			$original = 'https://' . rtrim( str_replace( '\/', '/', $m[0] ), "\"', \t\n\r" );
			wp_send_json_success( [ 'url' => $original ] );
			return;
		}

		// ── imgbox ─────────────────────────────────────────────────────────
		// The viewer page embeds the original image at images2.imgbox.com with _o suffix.
		if ( preg_match( '#images2\.imgbox\.com/[A-Za-z0-9/]+_o\.[a-z]{2,5}#i', $body, $m ) ) {
			wp_send_json_success( [ 'url' => 'https://' . $m[0] ] );
			return;
		}

		// Nothing found — JS caller falls back to the display URL.
		wp_send_json_error( [ 'message' => 'Original URL not found in page' ] );
	}

	private function set_locale(): void {
		// Allow the plugin language to be overridden independently of the site locale.
		add_filter( 'plugin_locale', function ( $locale, $domain ) {
			if ( 'rumanimg' === $domain ) {
				$saved = get_option( 'rumanimg_language', '' );
				if ( $saved ) return $saved;
			}
			return $locale;
		}, 10, 2 );

		add_action( 'plugins_loaded', function () {
			load_plugin_textdomain(
				'rumanimg',
				false,
				dirname( RUMANIMG_BASENAME ) . '/languages/'
			);
		} );
	}

	private function define_admin_hooks(): void {
		$admin = new Rumanimg_Admin( $this->plugin_name, $this->version );

		add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_scripts' ) );
		add_action( 'admin_menu',            array( $admin, 'add_menu_pages' ) );
		add_action( 'admin_init',            array( $admin, 'register_settings' ) );
	}

	private function define_public_hooks(): void {
		$public = new Rumanimg_Public( $this->plugin_name, $this->version );

		add_action( 'wp_enqueue_scripts', array( $public, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $public, 'enqueue_scripts' ) );
	}

	private function register_duplicate(): void {
		if ( is_admin() ) {
			new Rumanimg_Duplicate();
		}
	}

	public function run(): void {}
}
