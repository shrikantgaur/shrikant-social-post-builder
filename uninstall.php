<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package Shrikant\SocialPostBuilder
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Delete options.
delete_option( 'shrikant_spb_settings' );
delete_option( 'shrikant_spb_version' );
delete_option( 'shrikant_spb_templates' );

// Drop tables.
// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}shrikant_spb_history" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}shrikant_spb_history_posts" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}shrikant_spb_templates" );
// phpcs:enable

