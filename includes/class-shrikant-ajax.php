<?php
/**
 * AJAX endpoints handler.
 *
 * @package Shrikant\SocialPostBuilder
 */

namespace Shrikant\SocialPostBuilder;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

/**
 * Class Shrikant_Ajax
 * Registers and executes all asynchronous requests from the admin UI.
 */
class Shrikant_Ajax {

	/**
	 * Singleton instance.
	 *
	 * @var Shrikant_Ajax|null
	 */
	private static ?Shrikant_Ajax $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Shrikant_Ajax
	 */
	public static function get_instance(): Shrikant_Ajax {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor.
	 */
	private function __construct() {
		// List of AJAX actions.
		$actions = [
			'spb_fetch_posts',
			'spb_fetch_posts_by_ids',
			'spb_generate_message',
			'spb_save_history',
			'spb_load_dashboard_stats',
			'spb_fetch_history',
			'spb_get_history_item',
			'spb_copy_history',
			'spb_duplicate_history',
			'spb_delete_history',
			'spb_fetch_templates',
			'spb_get_template',
			'spb_save_template',
			'spb_delete_template',
			'spb_save_settings',
		];

		foreach ( $actions as $action ) {
			add_action( 'wp_ajax_' . $action, [ $this, $action ] );
		}
	}

	/**
	 * Validate nonce and capability.
	 *
	 * @return void
	 */
	private function check_security() {
		check_ajax_referer( 'shrikant_spb_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'shrikant-social-post-builder' ), 403 );
		}
	}

	/**
	 * Fetch posts matching selection filters.
	 *
	 * @return void
	 */
	public function spb_fetch_posts() {
		$this->check_security();

		$search      = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
		$category    = isset( $_POST['category'] ) ? absint( wp_unslash( $_POST['category'] ) ) : 0;
		$tag         = isset( $_POST['tag'] ) ? absint( wp_unslash( $_POST['tag'] ) ) : 0;
		$author      = isset( $_POST['author'] ) ? absint( wp_unslash( $_POST['author'] ) ) : 0;
		$date_filter = isset( $_POST['date_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['date_filter'] ) ) : '';
		$start_date  = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$end_date    = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';

		$args = [
			'search'      => $search,
			'category'    => $category,
			'tag'         => $tag,
			'author'      => $author,
			'date_filter' => $date_filter,
			'start_date'  => $start_date,
			'end_date'    => $end_date,
			'number'      => 50, // Display 50 recent posts
		];

		$posts = shrikant_spb_get_posts( $args );
		$data  = [];

		foreach ( $posts as $post ) {
			$cats = get_the_category( $post->ID );
			$cat_name = ! empty( $cats ) ? $cats[0]->name : __( 'Uncategorized', 'shrikant-social-post-builder' );
			$data[] = [
				'id'       => $post->ID,
				'title'    => get_the_title( $post ),
				'url'      => get_permalink( $post ),
				'category' => $cat_name,
				'date'     => get_the_date( '', $post ),
			];
		}

		wp_send_json_success( $data );
	}

	/**
	 * Fetch posts matching specific IDs (used in duplication).
	 *
	 * @return void
	 */
	public function spb_fetch_posts_by_ids() {
		$this->check_security();

		$post_ids = isset( $_POST['post_ids'] ) ? array_map( 'absint', wp_unslash( $_POST['post_ids'] ) ) : [];
		if ( empty( $post_ids ) ) {
			wp_send_json_success( [] );
		}

		$data = [];
		foreach ( $post_ids as $id ) {
			$post = get_post( $id );
			if ( $post ) {
				$cats = get_the_category( $post->ID );
				$cat_name = ! empty( $cats ) ? $cats[0]->name : __( 'Uncategorized', 'shrikant-social-post-builder' );
				$data[] = [
					'id'       => $post->ID,
					'title'    => get_the_title( $post ),
					'url'      => get_permalink( $post ),
					'category' => $cat_name,
					'date'     => get_the_date( '', $post ),
				];
			}
		}

		wp_send_json_success( $data );
	}

	/**
	 * Compile message from selected posts and formatting configurations.
	 *
	 * @return void
	 */
	public function spb_generate_message() {
		global $wpdb;
		$this->check_security();

		$post_ids    = isset( $_POST['post_ids'] ) ? array_map( 'absint', wp_unslash( $_POST['post_ids'] ) ) : [];
		$template_id = isset( $_POST['template_id'] ) ? absint( wp_unslash( $_POST['template_id'] ) ) : 0;
		
		// Retrieve template content.
		$template_content = $wpdb->get_var(
			$wpdb->prepare( "SELECT content FROM {$wpdb->prefix}shrikant_spb_templates WHERE id = %d", $template_id )
		);

		if ( ! $template_content ) {
			// Fallback.
			$template_content = "🔥 Today's Top Updates\n\n{{posts}}\n\n🌐 Website: {{website}}\n\n{{footer}}\n\n{{hashtags}}";
		}

		// Retrieve options.
		$options = [];
		if ( isset( $_POST['options'] ) && is_array( $_POST['options'] ) ) {
			$raw_options = wp_unslash( $_POST['options'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			foreach ( $raw_options as $key => $val ) {
				$options[ sanitize_key( $key ) ] = ( 'true' === $val || 1 === $val || true === $val );
			}
		}

		$message = shrikant_spb_generate( $post_ids, $template_content, $options );

		wp_send_json_success( [ 'message' => $message ] );
	}

	/**
	 * Log generated message to history.
	 *
	 * @return void
	 */
	public function spb_save_history() {
		$this->check_security();

		$template       = isset( $_POST['template'] ) ? sanitize_text_field( wp_unslash( $_POST['template'] ) ) : 'Custom';
		$generated_text = isset( $_POST['generated_text'] ) ? wp_kses_post( wp_unslash( $_POST['generated_text'] ) ) : '';
		$status         = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'generated';
		
		$posts = [];
		if ( isset( $_POST['posts'] ) && is_array( $_POST['posts'] ) ) {
			$raw_posts = wp_unslash( $_POST['posts'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			foreach ( $raw_posts as $post ) {
				$posts[] = [
					'post_id'    => absint( $post['post_id'] ),
					'post_title' => sanitize_text_field( $post['post_title'] ),
					'post_url'   => esc_url_raw( $post['post_url'] ),
				];
			}
		}

		$history_id = shrikant_spb_save_history( [
			'user_id'        => get_current_user_id(),
			'template'       => $template,
			'generated_text' => $generated_text,
			'status'         => $status,
			'posts'          => $posts,
		] );

		if ( $history_id ) {
			wp_send_json_success( [ 'history_id' => $history_id ] );
		} else {
			wp_send_json_error( __( 'Failed to save log.', 'shrikant-social-post-builder' ) );
		}
	}

	/**
	 * Load Dashboard statistics parameters.
	 *
	 * @return void
	 */
	public function spb_load_dashboard_stats() {
		global $wpdb;
		$this->check_security();

		// 1. Count standard posts published today.
		$local_today = current_time( 'Y-m-d' );
		$today_posts = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' AND DATE(post_date) = %s",
				$local_today
			)
		);

		// 2. Count compiled logs generated today (last 24 hours).
		$cutoff_time = wp_date( 'Y-m-d H:i:s', strtotime( '-24 hours', current_time( 'timestamp' ) ) );
		$today_generated = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}shrikant_spb_history WHERE created_at >= %s", $cutoff_time )
		);

		// 3. Count total history logs.
		$total_history = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}shrikant_spb_history" );

		// 4. Find most used template in history.
		$most_used_template = $wpdb->get_var(
			"SELECT template FROM {$wpdb->prefix}shrikant_spb_history GROUP BY template ORDER BY COUNT(*) DESC LIMIT 1"
		);

		// 5. Find last generated time.
		$last_time = $wpdb->get_var( "SELECT created_at FROM {$wpdb->prefix}shrikant_spb_history ORDER BY created_at DESC LIMIT 1" );
		if ( $last_time ) {
			$last_time = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_time );
		}

		wp_send_json_success( [
			'today_posts'        => $today_posts,
			'today_generated'    => $today_generated,
			'total_history'      => $total_history,
			'most_used_template' => $most_used_template,
			'last_time'          => $last_time,
		] );
	}

	/**
	 * Retrieve list of history items for data tables.
	 *
	 * @return void
	 */
	public function spb_fetch_history() {
		$this->check_security();

		$page  = isset( $_POST['page'] ) ? absint( wp_unslash( $_POST['page'] ) ) : 1;
		$limit = isset( $_POST['limit'] ) ? absint( wp_unslash( $_POST['limit'] ) ) : 10;
		$offset = ( $page - 1 ) * $limit;

		$args = [
			'limit'       => $limit,
			'offset'      => $offset,
			'search'      => isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '',
			'date_filter' => isset( $_POST['date_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['date_filter'] ) ) : '',
			'start_date'  => isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '',
			'end_date'    => isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '',
			'template'    => isset( $_POST['template'] ) ? sanitize_text_field( wp_unslash( $_POST['template'] ) ) : '',
		];

		$history_data = Shrikant_History::get_all( $args );

		// Format output dates.
		foreach ( $history_data['items'] as $item ) {
			$item->created_at = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $item->created_at );
		}

		wp_send_json_success( $history_data );
	}

	/**
	 * Retrieve a single history item details.
	 *
	 * @return void
	 */
	public function spb_get_history_item() {
		$this->check_security();

		$history_id = isset( $_POST['history_id'] ) ? absint( wp_unslash( $_POST['history_id'] ) ) : 0;
		$item = Shrikant_History::get( $history_id );

		if ( $item ) {
			$item->created_at = mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $item->created_at );
			wp_send_json_success( $item );
		} else {
			wp_send_json_error( __( 'Log record not found.', 'shrikant-social-post-builder' ) );
		}
	}

	/**
	 * Mark a history item as copied.
	 *
	 * @return void
	 */
	public function spb_copy_history() {
		$this->check_security();

		$history_id = isset( $_POST['history_id'] ) ? absint( wp_unslash( $_POST['history_id'] ) ) : 0;
		$success = shrikant_spb_copy( $history_id );

		if ( $success ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( __( 'Failed to log copy event.', 'shrikant-social-post-builder' ) );
		}
	}

	/**
	 * Duplicate history configuration.
	 *
	 * @return void
	 */
	public function spb_duplicate_history() {
		$this->check_security();

		$history_id = isset( $_POST['history_id'] ) ? absint( wp_unslash( $_POST['history_id'] ) ) : 0;
		$duplicate_data = shrikant_spb_duplicate( $history_id );

		if ( $duplicate_data ) {
			wp_send_json_success( $duplicate_data );
		} else {
			wp_send_json_error( __( 'Failed to duplicate log details.', 'shrikant-social-post-builder' ) );
		}
	}

	/**
	 * Delete a history log.
	 *
	 * @return void
	 */
	public function spb_delete_history() {
		$this->check_security();

		$history_id = isset( $_POST['history_id'] ) ? absint( wp_unslash( $_POST['history_id'] ) ) : 0;
		$deleted = Shrikant_History::delete( $history_id );

		if ( $deleted ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( __( 'Failed to delete record.', 'shrikant-social-post-builder' ) );
		}
	}

	/**
	 * Fetch template rows.
	 *
	 * @return void
	 */
	public function spb_fetch_templates() {
		global $wpdb;
		$this->check_security();

		$templates = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}shrikant_spb_templates ORDER BY id ASC" );

		wp_send_json_success( $templates );
	}

	/**
	 * Fetch a single template by ID.
	 *
	 * @return void
	 */
	public function spb_get_template() {
		global $wpdb;
		$this->check_security();

		$template_id = isset( $_POST['template_id'] ) ? absint( wp_unslash( $_POST['template_id'] ) ) : 0;

		$template = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}shrikant_spb_templates WHERE id = %d", $template_id )
		);

		if ( $template ) {
			wp_send_json_success( $template );
		} else {
			wp_send_json_error( __( 'Template not found.', 'shrikant-social-post-builder' ) );
		}
	}

	/**
	 * Save a template (Custom create or System edit).
	 *
	 * @return void
	 */
	public function spb_save_template() {
		global $wpdb;
		$this->check_security();

		$template_id = isset( $_POST['template_id'] ) ? absint( wp_unslash( $_POST['template_id'] ) ) : 0;
		$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$content     = isset( $_POST['content'] ) ? sanitize_textarea_field( wp_unslash( $_POST['content'] ) ) : '';

		if ( empty( $name ) || empty( $content ) ) {
			wp_send_json_error( __( 'Template name and content are required.', 'shrikant-social-post-builder' ) );
		}

		$table_templates = $wpdb->prefix . 'shrikant_spb_templates';

		if ( $template_id > 0 ) {
			// Edit existing.
			// Prevent changing name for system default templates.
			$existing_name = $wpdb->get_var(
				$wpdb->prepare( "SELECT name FROM {$wpdb->prefix}shrikant_spb_templates WHERE id = %d", $template_id )
			);
			$system_tpls = [ 'WhatsApp', 'Telegram', 'Facebook', 'LinkedIn', 'X (Twitter)' ];
			
			$data = [ 'content' => $content ];
			if ( ! in_array( $existing_name, $system_tpls, true ) ) {
				$data['name'] = $name;
			}

			$updated = $wpdb->update(
				$table_templates,
				$data,
				[ 'id' => $template_id ],
				[ '%s' ],
				[ '%d' ]
			);

			if ( false !== $updated ) {
				wp_send_json_success();
			} else {
				wp_send_json_error( __( 'Failed to update template.', 'shrikant-social-post-builder' ) );
			}
		} else {
			// Create custom.
			// Prevent duplicate names.
			$exists = $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}shrikant_spb_templates WHERE name = %s", $name )
			);
			if ( $exists > 0 ) {
				wp_send_json_error( __( 'A template with this name already exists.', 'shrikant-social-post-builder' ) );
			}

			$inserted = $wpdb->insert(
				$table_templates,
				[
					'name'    => $name,
					'content' => $content,
					'active'  => 1,
				]
			);

			if ( $inserted ) {
				wp_send_json_success();
			} else {
				wp_send_json_error( __( 'Failed to save template.', 'shrikant-social-post-builder' ) );
			}
		}
	}

	/**
	 * Delete a custom template.
	 *
	 * @return void
	 */
	public function spb_delete_template() {
		global $wpdb;
		$this->check_security();

		$template_id = isset( $_POST['template_id'] ) ? absint( wp_unslash( $_POST['template_id'] ) ) : 0;

		// Verify this is not a system template.
		$tpl_name = $wpdb->get_var(
			$wpdb->prepare( "SELECT name FROM {$wpdb->prefix}shrikant_spb_templates WHERE id = %d", $template_id )
		);
		$system_tpls = [ 'WhatsApp', 'Telegram', 'Facebook', 'LinkedIn', 'X (Twitter)' ];
		if ( in_array( $tpl_name, $system_tpls, true ) ) {
			wp_send_json_error( __( 'System templates cannot be deleted.', 'shrikant-social-post-builder' ) );
		}

		$deleted = $wpdb->delete( "{$wpdb->prefix}shrikant_spb_templates", [ 'id' => $template_id ], [ '%d' ] );

		if ( $deleted ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( __( 'Failed to delete template.', 'shrikant-social-post-builder' ) );
		}
	}

	/**
	 * Save plugin settings options.
	 *
	 * @return void
	 */
	public function spb_save_settings() {
		$this->check_security();

		$new_settings = isset( $_POST['settings'] ) ? (array) wp_unslash( $_POST['settings'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$updated = Shrikant_Settings::get_instance()->update_settings( $new_settings );

		if ( $updated ) {
			wp_send_json_success();
		} else {
			// Even if nothing changed, update_option might return false.
			wp_send_json_success();
		}
	}
}
// phpcs:enable
