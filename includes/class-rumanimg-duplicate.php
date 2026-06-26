<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rumanimg_Duplicate {

	public function __construct() {
		add_filter( 'post_row_actions',    [ $this, 'add_row_action' ], 10, 2 );
		add_filter( 'page_row_actions',    [ $this, 'add_row_action' ], 10, 2 );
		add_action( 'post_submitbox_misc_actions', [ $this, 'add_edit_screen_button' ] );
		add_action( 'admin_action_rumanimg_duplicate', [ $this, 'duplicate_post' ] );
	}

	/**
	 * Add "Duplicate" link to the post/page list table row actions.
	 */
	public function add_row_action( array $actions, WP_Post $post ): array {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			add_query_arg(
				[
					'action'  => 'rumanimg_duplicate',
					'post_id' => $post->ID,
				],
				admin_url( 'admin.php' )
			),
			'rumanimg_duplicate_' . $post->ID
		);

		$actions['rumanimg_duplicate'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $url ),
			esc_html__( 'Duplicate', 'rumanimg' )
		);

		return $actions;
	}

	/**
	 * Add a "Duplicate" button inside the publish meta box on the edit screen.
	 */
	public function add_edit_screen_button(): void {
		global $post;

		if ( ! $post || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$url = wp_nonce_url(
			add_query_arg(
				[
					'action'  => 'rumanimg_duplicate',
					'post_id' => $post->ID,
				],
				admin_url( 'admin.php' )
			),
			'rumanimg_duplicate_' . $post->ID
		);

		echo '<div class="misc-pub-section">';
		printf(
			'<a href="%s" class="button">%s</a>',
			esc_url( $url ),
			esc_html__( 'Duplicate this post', 'rumanimg' )
		);
		echo '</div>';
	}

	/**
	 * Execute the duplication: copy post, meta, and terms; redirect to the new draft.
	 */
	public function duplicate_post(): void {
		$post_id = isset( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;

		if ( ! $post_id ) {
			wp_die( esc_html__( 'No post ID provided.', 'rumanimg' ) );
		}

		check_admin_referer( 'rumanimg_duplicate_' . $post_id );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to duplicate posts.', 'rumanimg' ) );
		}

		$original = get_post( $post_id );

		if ( ! $original ) {
			wp_die( esc_html__( 'Post not found.', 'rumanimg' ) );
		}

		// Create the duplicate post as a draft.
		$new_id = wp_insert_post(
			[
				'post_title'     => $original->post_title,
				'post_content'   => $original->post_content,
				'post_excerpt'   => $original->post_excerpt,
				'post_status'    => 'draft',
				'post_type'      => $original->post_type,
				'post_author'    => get_current_user_id(),
				'post_parent'    => $original->post_parent,
				'menu_order'     => $original->menu_order,
				'comment_status' => $original->comment_status,
				'ping_status'    => $original->ping_status,
			],
			true
		);

		if ( is_wp_error( $new_id ) ) {
			wp_die( esc_html( $new_id->get_error_message() ) );
		}

		// Copy all post meta.
		foreach ( get_post_meta( $post_id ) as $key => $values ) {
			foreach ( $values as $value ) {
				add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
			}
		}

		// Copy all taxonomy terms.
		$taxonomies = get_object_taxonomies( $original->post_type );
		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'ids' ] );
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				wp_set_object_terms( $new_id, $terms, $taxonomy );
			}
		}

		// Redirect to the new draft for editing.
		wp_safe_redirect(
			add_query_arg(
				[
					'action' => 'edit',
					'post'   => $new_id,
				],
				admin_url( 'post.php' )
			)
		);
		exit;
	}
}
