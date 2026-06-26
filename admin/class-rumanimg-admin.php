<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rumanimg_Admin {

	private string $plugin_name;
	private string $version;

	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function enqueue_styles( string $hook ): void {
		// Load only on this plugin's pages.
		if ( strpos( $hook, $this->plugin_name ) === false ) {
			return;
		}

		wp_enqueue_style(
			'rumanimg-admin',
			RUMANIMG_URL . 'admin/css/rumanimg-admin.css',
			array(),
			$this->version
		);
	}

	public function enqueue_scripts( string $hook ): void {
		if ( strpos( $hook, $this->plugin_name ) === false ) {
			return;
		}

		wp_enqueue_script(
			'rumanimg-admin',
			RUMANIMG_URL . 'admin/js/rumanimg-admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_localize_script( 'rumanimg-admin', 'rumanimg_admin', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'rumanimg_admin_nonce' ),
		) );
	}

	public function add_menu_pages(): void {
		add_menu_page(
			__( 'Ruman IMG', 'rumanimg' ),
			__( 'Ruman IMG', 'rumanimg' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'render_main_page' ),
			RUMANIMG_URL . 'assets/images/icon.svg',
			25
		);

		add_submenu_page(
			$this->plugin_name,
			__( 'Settings', 'rumanimg' ),
			__( 'Settings', 'rumanimg' ),
			'manage_options',
			$this->plugin_name . '-settings',
			array( $this, 'render_settings_page' )
		);
	}

	public function get_stats(): array {
		global $wpdb;

		$total_posts = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'"
		);

		$block_posts = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts}
				WHERE post_status IN ('publish','draft','pending','future')
				AND post_type = 'post'
				AND post_content LIKE %s",
				'%<!-- wp:rumanimg/image-parser%'
			)
		);

		return [
			'total_posts' => $total_posts,
			'block_posts' => $block_posts,
		];
	}

	public function render_main_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		include RUMANIMG_PATH . 'admin/views/main.php';
	}

	public function register_settings(): void {
		register_setting( 'rumanimg_settings_group', 'rumanimg_language', [
			'sanitize_callback' => function ( $val ) {
				return in_array( $val, [ '', 'en_US', 'ar' ], true ) ? $val : '';
			},
			'default' => '',
		] );

		add_settings_section( 'rumanimg_general', '', '__return_false', 'rumanimg-settings' );

		add_settings_field(
			'rumanimg_language',
			__( 'Plugin Language', 'rumanimg' ),
			[ $this, 'field_language' ],
			'rumanimg-settings',
			'rumanimg_general'
		);
	}

	public function field_language(): void {
		$current = get_option( 'rumanimg_language', '' );
		$options = [
			''      => __( 'Auto (follow site language)', 'rumanimg' ),
			'en_US' => 'English',
			'ar'    => 'العربية',
		];
		echo '<select name="rumanimg_language" id="rumanimg_language" class="regular-text">';
		foreach ( $options as $val => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $val ),
				selected( $current, $val, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
		echo '<p class="description">' . esc_html__( 'Choose the language for the plugin UI, independent of the site locale.', 'rumanimg' ) . '</p>';
	}

	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		include RUMANIMG_PATH . 'admin/views/settings.php';
	}
}
