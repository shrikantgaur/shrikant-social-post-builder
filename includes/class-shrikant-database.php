<?php
/**
 * Database management class.
 *
 * @package Shrikant\SocialPostBuilder
 */

namespace Shrikant\SocialPostBuilder;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange

/**
 * Class Shrikant_Database
 * Handles database operations including schema creation, migrations, and template seeding.
 */
class Shrikant_Database {

	/**
	 * Activate and install/update database tables.
	 *
	 * @return void
	 */
	public static function activate() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Table: wp_shrikant_spb_history
		$table_history = $wpdb->prefix . 'shrikant_spb_history';
		// Table: wp_shrikant_spb_history_posts
		$table_history_posts = $wpdb->prefix . 'shrikant_spb_history_posts';
		// Table: wp_shrikant_spb_templates
		$table_templates = $wpdb->prefix . 'shrikant_spb_templates';

		$sql = "CREATE TABLE $table_history (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			template varchar(100) NOT NULL,
			generated_text longtext NOT NULL,
			status varchar(50) NOT NULL DEFAULT 'generated',
			created_at datetime NOT NULL,
			copied_at datetime DEFAULT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;

		CREATE TABLE $table_history_posts (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			history_id bigint(20) unsigned NOT NULL,
			post_id bigint(20) unsigned NOT NULL,
			post_title text NOT NULL,
			post_url text NOT NULL,
			PRIMARY KEY  (id),
			KEY history_id (history_id)
		) $charset_collate;

		CREATE TABLE $table_templates (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(100) NOT NULL,
			content text NOT NULL,
			active tinyint(1) NOT NULL DEFAULT 1,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Seed templates if the templates table is empty.
		self::seed_templates();
	}

	/**
	 * Seed the default templates.
	 *
	 * @return void
	 */
	public static function seed_templates() {
		global $wpdb;
		$table_templates = $wpdb->prefix . 'shrikant_spb_templates';

		// Check if templates already exist.
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}shrikant_spb_templates" );
		if ( $count > 0 ) {
			return;
		}

		$default_templates = [
			[
				'name'    => 'WhatsApp',
				'content' => "🔥 *Today's Top Updates*\n\n{{posts}}\n\n🌐 *Website*\n{{website}}\n\n{{footer}}\n\n{{hashtags}}",
				'active'  => 1,
			],
			[
				'name'    => 'Telegram',
				'content' => "🔥 **Today's Top Updates**\n\n{{posts}}\n\n🌐 **Website**\n{{website}}\n\n{{footer}}\n\n{{hashtags}}",
				'active'  => 1,
			],
			[
				'name'    => 'Facebook',
				'content' => "🔥 Today's Top Updates\n\n{{posts}}\n\n🌐 Website\n{{website}}\n\n{{footer}}\n\n{{hashtags}}",
				'active'  => 1,
			],
			[
				'name'    => 'LinkedIn',
				'content' => "🔥 Today's Top Updates\n\n{{posts}}\n\n🌐 Website\n{{website}}\n\n{{footer}}\n\n{{hashtags}}",
				'active'  => 1,
			],
			[
				'name'    => 'X (Twitter)',
				'content' => "🔥 Today's Top Updates\n\n{{posts}}\n\n🌐 Website\n{{website}}\n\n{{footer}}\n\n{{hashtags}}",
				'active'  => 1,
			],
		];

		foreach ( $default_templates as $template ) {
			$wpdb->insert( $table_templates, $template );
		}
	}
}
// phpcs:enable
