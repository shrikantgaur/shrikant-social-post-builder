<?php
/**
 * History tracker class.
 *
 * @package Shrikant\SocialPostBuilder
 */

namespace Shrikant\SocialPostBuilder;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter

/**
 * Class Shrikant_History
 * Manages database logging of generated messages, views, copying events, duplication, and cleanup.
 */
class Shrikant_History {

	/**
	 * Save a generated message log.
	 *
	 * @param array $data {
	 *     @type int    $user_id        ID of the user.
	 *     @type string $template       Template name or identifier.
	 *     @type string $generated_text Compiled message.
	 *     @type string $status         Status (draft, generated, copied, shared).
	 *     @type array  $posts          Array of arrays containing 'post_id', 'post_title', 'post_url'.
	 * }
	 * @return int|bool The history ID or false on failure.
	 */
	public static function save( array $data ) {
		global $wpdb;

		$table_history = $wpdb->prefix . 'shrikant_spb_history';
		$table_posts   = $wpdb->prefix . 'shrikant_spb_history_posts';

		// Insert history parent record.
		$history_data = [
			'user_id'        => absint( $data['user_id'] ),
			'template'       => sanitize_text_field( $data['template'] ),
			'generated_text' => wp_kses_post( $data['generated_text'] ),
			'status'         => sanitize_text_field( $data['status'] ),
			'created_at'     => current_time( 'mysql' ),
			'copied_at'      => ( 'copied' === $data['status'] ) ? current_time( 'mysql' ) : null,
		];

		$inserted = $wpdb->insert( $table_history, $history_data );
		if ( ! $inserted ) {
			return false;
		}

		$history_id = $wpdb->insert_id;

		// Insert posts links.
		if ( ! empty( $data['posts'] ) && is_array( $data['posts'] ) ) {
			foreach ( $data['posts'] as $post_item ) {
				$wpdb->insert(
					$table_posts,
					[
						'history_id' => $history_id,
						'post_id'    => absint( $post_item['post_id'] ),
						'post_title' => sanitize_text_field( $post_item['post_title'] ),
						'post_url'   => esc_url_raw( $post_item['post_url'] ),
					]
				);
			}
		}

		// Perform automatic cleanup after saving new log.
		self::cleanup_logs();

		return $history_id;
	}

	/**
	 * Mark a history log as copied.
	 *
	 * @param int $history_id History record ID.
	 * @return bool True on success, false on failure.
	 */
	public static function copy( int $history_id ): bool {
		global $wpdb;
		$table_history = $wpdb->prefix . 'shrikant_spb_history';

		$updated = $wpdb->update(
			$table_history,
			[
				'status'    => 'copied',
				'copied_at' => current_time( 'mysql' ),
			],
			[ 'id' => $history_id ],
			[ '%s', '%s' ],
			[ '%d' ]
		);

		return ( false !== $updated );
	}

	/**
	 * Retrieve a single history item with its posts.
	 *
	 * @param int $history_id History ID.
	 * @return object|null History item object with a 'posts' property, or null.
	 */
	public static function get( int $history_id ) {
		global $wpdb;
		$table_history = $wpdb->prefix . 'shrikant_spb_history';
		$table_posts   = $wpdb->prefix . 'shrikant_spb_history_posts';

		$history = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}shrikant_spb_history WHERE id = %d", $history_id )
		);

		if ( ! $history ) {
			return null;
		}

		$history->posts = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}shrikant_spb_history_posts WHERE history_id = %d", $history_id )
		);

		return $history;
	}

	/**
	 * Retrieve history records based on search and filters.
	 *
	 * @param array $args Filter options (limit, offset, search, date_filter, template, platform, user).
	 * @return array List of history items and total count.
	 */
	public static function get_all( array $args = [] ): array {
		global $wpdb;
		$table_history = $wpdb->prefix . 'shrikant_spb_history';
		$table_posts   = $wpdb->prefix . 'shrikant_spb_history_posts';

		$args = wp_parse_args( $args, [
			'limit'       => 10,
			'offset'      => 0,
			'search'      => '',
			'date_filter' => '', // 'today', 'yesterday', 'week', 'month', 'custom'
			'start_date'  => '',
			'end_date'    => '',
			'template'    => '',
			'user_id'     => 0,
		] );

		$where = [ '1=1' ];
		$params = [];

		// Text Search (searches generated text, or post titles).
		if ( ! empty( $args['search'] ) ) {
			$search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[] = "(h.generated_text LIKE %s OR EXISTS (
				SELECT 1 FROM $table_posts p WHERE p.history_id = h.id AND p.post_title LIKE %s
			))";
			$params[] = $search_term;
			$params[] = $search_term;
		}

		// Template Filter.
		if ( ! empty( $args['template'] ) ) {
			$where[] = 'h.template = %s';
			$params[] = sanitize_text_field( $args['template'] );
		}

		// User Filter.
		if ( ! empty( $args['user_id'] ) ) {
			$where[] = 'h.user_id = %d';
			$params[] = absint( $args['user_id'] );
		}

		// Date Filter.
		$today     = current_time( 'Y-m-d' );
		$yesterday = wp_date( 'Y-m-d', strtotime( '-1 day', current_time( 'timestamp' ) ) );

		if ( 'today' === $args['date_filter'] ) {
			$where[] = 'DATE(h.created_at) = %s';
			$params[] = $today;
		} elseif ( 'yesterday' === $args['date_filter'] ) {
			$where[] = 'DATE(h.created_at) = %s';
			$params[] = $yesterday;
		} elseif ( 'week' === $args['date_filter'] ) {
			$where[] = 'h.created_at >= %s';
			$params[] = wp_date( 'Y-m-d H:i:s', strtotime( '-7 days', current_time( 'timestamp' ) ) );
		} elseif ( 'month' === $args['date_filter'] ) {
			$where[] = 'h.created_at >= %s';
			$params[] = wp_date( 'Y-m-d H:i:s', strtotime( '-30 days', current_time( 'timestamp' ) ) );
		} elseif ( 'custom' === $args['date_filter'] && ! empty( $args['start_date'] ) ) {
			$where[] = 'DATE(h.created_at) >= %s';
			$params[] = sanitize_text_field( $args['start_date'] );
			if ( ! empty( $args['end_date'] ) ) {
				$where[] = 'DATE(h.created_at) <= %s';
				$params[] = sanitize_text_field( $args['end_date'] );
			}
		}

		$where_str = implode( ' AND ', $where );

		// Count total records.
		$count_query = "SELECT COUNT(*) FROM {$wpdb->prefix}shrikant_spb_history h WHERE $where_str";
		if ( ! empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$total = (int) $wpdb->get_var( $wpdb->prepare( $count_query, $params ) );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$total = (int) $wpdb->get_var( $count_query );
		}

		// Fetch items.
		$query = "SELECT h.*, u.display_name as user_name 
			FROM {$wpdb->prefix}shrikant_spb_history h 
			LEFT JOIN {$wpdb->users} u ON h.user_id = u.ID
			WHERE $where_str 
			ORDER BY h.created_at DESC";

		// Pagination.
		$query .= ' LIMIT %d OFFSET %d';
		$params[] = absint( $args['limit'] );
		$params[] = absint( $args['offset'] );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$items = $wpdb->get_results( $wpdb->prepare( $query, $params ) );

		// Populate posts count.
		foreach ( $items as $item ) {
			$item->posts_count = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}shrikant_spb_history_posts WHERE history_id = %d", $item->id )
			);
		}

		return [
			'items' => $items,
			'total' => $total,
		];
	}

	/**
	 * Delete a history log.
	 *
	 * @param int $history_id History record ID.
	 * @return bool True on success, false on failure.
	 */
	public static function delete( int $history_id ): bool {
		global $wpdb;
		$table_history = $wpdb->prefix . 'shrikant_spb_history';
		$table_posts   = $wpdb->prefix . 'shrikant_spb_history_posts';

		$wpdb->delete( $table_posts, [ 'history_id' => $history_id ], [ '%d' ] );
		$deleted = $wpdb->delete( $table_history, [ 'id' => $history_id ], [ '%d' ] );

		return ( false !== $deleted );
	}

	/**
	 * Automatically rotate and clean history logs.
	 *
	 * @return void
	 */
	public static function cleanup_logs() {
		global $wpdb;
		$table_history = $wpdb->prefix . 'shrikant_spb_history';
		$table_posts   = $wpdb->prefix . 'shrikant_spb_history_posts';

		$settings = Shrikant_Settings::get_instance()->get_settings();
		$auto_delete_days = absint( $settings['auto_delete'] );
		$max_records      = absint( $settings['max_records'] );

		// 1. Delete logs older than X days.
		if ( $auto_delete_days > 0 ) {
			$cutoff_date = wp_date( 'Y-m-d H:i:s', strtotime( "-$auto_delete_days days", current_time( 'timestamp' ) ) );
			// Find history IDs to delete
			$old_ids = $wpdb->get_col(
				$wpdb->prepare( "SELECT id FROM {$wpdb->prefix}shrikant_spb_history WHERE created_at < %s", $cutoff_date )
			);

			if ( ! empty( $old_ids ) ) {
				$ids_str = implode( ',', array_map( 'absint', $old_ids ) );
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}shrikant_spb_history_posts WHERE history_id IN ($ids_str)" ) );
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}shrikant_spb_history WHERE id IN ($ids_str)" ) );
			}
		}

		// 2. Enforce max records.
		if ( $max_records > 0 ) {
			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}shrikant_spb_history" );
			if ( $total > $max_records ) {
				$excess = $total - $max_records;
				// Find oldest history IDs to delete
				$excess_ids = $wpdb->get_col(
					$wpdb->prepare( "SELECT id FROM {$wpdb->prefix}shrikant_spb_history ORDER BY created_at ASC LIMIT %d", $excess )
				);

				if ( ! empty( $excess_ids ) ) {
					$ids_str = implode( ',', array_map( 'absint', $excess_ids ) );
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}shrikant_spb_history_posts WHERE history_id IN ($ids_str)" ) );
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}shrikant_spb_history WHERE id IN ($ids_str)" ) );
				}
			}
		}
	}
}
// phpcs:enable
