<?php
/**
 * Settings configuration class.
 *
 * @package Shrikant\SocialPostBuilder
 */

namespace Shrikant\SocialPostBuilder;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shrikant_Settings
 * Manages plugin settings and configuration defaults.
 */
class Shrikant_Settings {

	/**
	 * Singleton instance.
	 *
	 * @var Shrikant_Settings|null
	 */
	private static ?Shrikant_Settings $instance = null;

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private string $option_name = 'shrikant_spb_settings';

	/**
	 * Get the singleton instance.
	 *
	 * @return Shrikant_Settings
	 */
	public static function get_instance(): Shrikant_Settings {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor to enforce singleton.
	 */
	private function __construct() {
		// Can initialize settings registration hooks here if needed.
	}

	/**
	 * Get all settings with defaults populated.
	 *
	 * @return array
	 */
	public function get_settings(): array {
		$saved_settings = get_option( $this->option_name, [] );
		if ( ! is_array( $saved_settings ) ) {
			$saved_settings = [];
		}

		$defaults = $this->get_defaults();

		return wp_parse_args( $saved_settings, $defaults );
	}

	/**
	 * Get individual setting.
	 *
	 * @param string $key Setting key.
	 * @param mixed  $default Fallback default.
	 * @return mixed
	 */
	public function get( string $key, $default = null ) {
		$settings = $this->get_settings();
		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}

	/**
	 * Update settings.
	 *
	 * @param array $new_settings Settings to merge or replace.
	 * @return bool True if value updated, false otherwise.
	 */
	public function update_settings( array $new_settings ): bool {
		$current = $this->get_settings();
		$updated = wp_parse_args( $new_settings, $current );

		// Sanitize inputs.
		$sanitized = [];
		$sanitized['website_url']     = esc_url_raw( $updated['website_url'] );
		$sanitized['footer_text']     = sanitize_textarea_field( $updated['footer_text'] );
		$sanitized['default_template']= sanitize_text_field( $updated['default_template'] );
		$sanitized['default_emoji']   = sanitize_text_field( $updated['default_emoji'] );
		$sanitized['default_hashtags']= sanitize_text_field( $updated['default_hashtags'] );
		$sanitized['auto_delete']     = absint( $updated['auto_delete'] );
		$sanitized['max_records']     = absint( $updated['max_records'] );
		$sanitized['auto_copy']       = ! empty( $updated['auto_copy'] ) ? 1 : 0;

		return update_option( $this->option_name, $sanitized );
	}

	/**
	 * Get default settings values.
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return [
			'website_url'      => get_home_url(),
			'footer_text'      => __( 'Shared via Shrikant Social Post Builder', 'shrikant-social-post-builder' ),
			'default_template' => 'WhatsApp',
			'default_emoji'    => '🔥',
			'default_hashtags' => '#News #Updates',
			'auto_delete'      => 30, // 30 Days
			'max_records'      => 500, // 500 Max items
			'auto_copy'        => 0, // Disable auto copy by default
		];
	}
}
