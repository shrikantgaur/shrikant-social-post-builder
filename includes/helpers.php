<?php
/**
 * Procedural helper functions.
 *
 * @package Shrikant\SocialPostBuilder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Shrikant\SocialPostBuilder\Shrikant_Generator;
use Shrikant\SocialPostBuilder\Shrikant_History;

/**
 * Generate social post message.
 *
 * @param array  $post_ids WordPress post IDs.
 * @param string $template_content The raw template string.
 * @param array  $options Form formatting options.
 * @return string Compiled message.
 */
function shrikant_spb_generate( array $post_ids, string $template_content, array $options = [] ): string {
	return Shrikant_Generator::generate( $post_ids, $template_content, $options );
}

/**
 * Fetch posts matching filters.
 *
 * @param array $args Query filter arguments.
 * @return WP_Post[] Array of post objects.
 */
function shrikant_spb_get_posts( array $args = [] ): array {
	$defaults = [
		'search'      => '',
		'category'    => 0,
		'tag'         => 0,
		'author'      => 0,
		'date_filter' => '', // 'today', 'yesterday', 'custom'
		'start_date'  => '',
		'end_date'    => '',
		'number'      => 50,
	];

	$params = wp_parse_args( $args, $defaults );

	$query_args = [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => absint( $params['number'] ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	];

	// Search filter.
	if ( ! empty( $params['search'] ) ) {
		$query_args['s'] = sanitize_text_field( $params['search'] );
	}

	// Category filter.
	if ( ! empty( $params['category'] ) ) {
		$query_args['cat'] = absint( $params['category'] );
	}

	// Tag filter.
	if ( ! empty( $params['tag'] ) ) {
		$query_args['tag_id'] = absint( $params['tag'] );
	}

	// Author filter.
	if ( ! empty( $params['author'] ) ) {
		$query_args['author'] = absint( $params['author'] );
	}

	// Date filter.
	$local_today = current_time( 'Y-m-d' );
	$date_query  = [];

	if ( 'today' === $params['date_filter'] ) {
		$date_query[] = [
			'after'     => $local_today . ' 00:00:00',
			'before'    => $local_today . ' 23:59:59',
			'inclusive' => true,
		];
	} elseif ( 'yesterday' === $params['date_filter'] ) {
		$local_yesterday = wp_date( 'Y-m-d', strtotime( '-1 day', current_time( 'timestamp' ) ) );
		$date_query[] = [
			'after'     => $local_yesterday . ' 00:00:00',
			'before'    => $local_yesterday . ' 23:59:59',
			'inclusive' => true,
		];
	} elseif ( 'custom' === $params['date_filter'] && ! empty( $params['start_date'] ) ) {
		$start = sanitize_text_field( $params['start_date'] );
		$end   = ! empty( $params['end_date'] ) ? sanitize_text_field( $params['end_date'] ) : $start;
		$date_query[] = [
			'after'     => $start . ' 00:00:00',
			'before'    => $end . ' 23:59:59',
			'inclusive' => true,
		];
	}

	if ( ! empty( $date_query ) ) {
		$query_args['date_query'] = $date_query;
	}

	$query = new WP_Query( $query_args );

	return $query->posts;
}

/**
 * Save compiled history to database.
 *
 * @param array $data History properties.
 * @return int|bool History ID or false.
 */
function shrikant_spb_save_history( array $data ) {
	return Shrikant_History::save( $data );
}

/**
 * Mark a history item as copied.
 *
 * @param int $history_id History ID.
 * @return bool True on success, false on failure.
 */
function shrikant_spb_copy( int $history_id ): bool {
	return Shrikant_History::copy( $history_id );
}

/**
 * Prepare an existing history item for duplication.
 *
 * @param int $history_id History ID.
 * @return array|bool Config array or false.
 */
function shrikant_spb_duplicate( int $history_id ) {
	$history = Shrikant_History::get( $history_id );
	if ( ! $history ) {
		return false;
	}

	$post_ids = [];
	foreach ( $history->posts as $post_row ) {
		$post_ids[] = (int) $post_row->post_id;
	}

	return [
		'template' => $history->template,
		'posts'    => $post_ids,
	];
}
