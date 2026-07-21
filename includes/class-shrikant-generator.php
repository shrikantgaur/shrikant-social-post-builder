<?php
/**
 * Message generator class.
 *
 * @package Shrikant\SocialPostBuilder
 */

namespace Shrikant\SocialPostBuilder;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shrikant_Generator
 * Generates formatted social media messages based on post selection, templates, and options.
 */
class Shrikant_Generator {

	/**
	 * Generate social media message from posts, template, and options.
	 *
	 * @param array  $post_ids         Array of WordPress post IDs.
	 * @param string $template_content The raw template string with placeholders.
	 * @param array  $options          Formatting options.
	 * @return string The generated message text.
	 */
	public static function generate( array $post_ids, string $template_content, array $options = [] ): string {
		$settings_instance = Shrikant_Settings::get_instance();
		$settings = $settings_instance->get_settings();

		// Default options.
		$options = wp_parse_args( $options, [
			'number_posts'      => true,
			'show_emojis'       => true,
			'add_footer'        => true,
			'add_website'       => true,
			'add_hashtags'      => true,
			'remove_duplicates' => true,
		] );

		// 1. Process posts placeholder.
		$posts_text = '';
		$seen_ids   = [];
		$counter    = 1;

		foreach ( $post_ids as $post_id ) {
			$post_id = absint( $post_id );
			if ( ! $post_id ) {
				continue;
			}

			// Deduplicate if selected.
			if ( $options['remove_duplicates'] && in_array( $post_id, $seen_ids, true ) ) {
				continue;
			}
			$seen_ids[] = $post_id;

			$post = get_post( $post_id );
			if ( ! $post || 'publish' !== $post->post_status ) {
				continue;
			}

			$title = get_the_title( $post );
			$url   = get_permalink( $post );

			$post_line = '';
			if ( $options['number_posts'] ) {
				$post_line .= $counter . '. ';
			}

			if ( $options['show_emojis'] && ! empty( $settings['default_emoji'] ) ) {
				$post_line .= $settings['default_emoji'] . ' ';
			}

			$post_line .= $title . "\n" . $url;
			$posts_text .= $post_line . "\n\n";

			$counter++;
		}

		$posts_text = trim( $posts_text );

		// 2. Prepare place holders values.
		$website_val  = $options['add_website'] ? $settings['website_url'] : '';
		$footer_val   = $options['add_footer'] ? $settings['footer_text'] : '';
		$hashtags_val = $options['add_hashtags'] ? $settings['default_hashtags'] : '';
		$date_val     = date_i18n( get_option( 'date_format' ) );
		$time_val     = date_i18n( get_option( 'time_format' ) );

		// Placeholders mapping.
		$placeholders = [
			'{{posts}}'    => $posts_text,
			'{{website}}'  => $website_val,
			'{{footer}}'   => $footer_val,
			'{{hashtags}}' => $hashtags_val,
			'{{date}}'     => $date_val,
			'{{time}}'     => $time_val,
		];

		// Apply replacements.
		$output = $template_content;
		foreach ( $placeholders as $placeholder => $value ) {
			$output = str_replace( $placeholder, $value, $output );
		}

		// Clean up empty lines where blocks were excluded (e.g. double newlines from removed footer/hashtags).
		$output = preg_replace( "/\n{3,}/", "\n\n", $output );

		return trim( $output );
	}
}
