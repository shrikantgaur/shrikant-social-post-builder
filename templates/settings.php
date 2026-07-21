<?php
/**
 * Settings screen template.
 *
 * @package Shrikant\SocialPostBuilder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shrikant_spb_settings_instance = Shrikant\SocialPostBuilder\Shrikant_Settings::get_instance();
$shrikant_spb_settings          = $shrikant_spb_settings_instance->get_settings();

global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$shrikant_spb_template_names  = $wpdb->get_col( "SELECT name FROM {$wpdb->prefix}shrikant_spb_templates ORDER BY name ASC" );
?>
<form id="shrikant-spb-settings-form" class="shrikant-spb-settings-form">
	<!-- General Settings Section -->
	<div class="shrikant-spb-settings-section">
		<h3><?php esc_html_e( 'General Settings', 'shrikant-social-post-builder' ); ?></h3>
		
		<div class="shrikant-spb-field-group" style="margin-bottom:18px;">
			<label for="settings-website-url"><?php esc_html_e( 'Website URL', 'shrikant-social-post-builder' ); ?></label>
			<input type="url" id="settings-website-url" class="shrikant-spb-input" style="max-width:400px;" value="<?php echo esc_attr( $shrikant_spb_settings['website_url'] ); ?>" required>
			<p class="description"><?php esc_html_e( 'Substitutes the {{website}} placeholder in templates.', 'shrikant-social-post-builder' ); ?></p>
		</div>

		<div class="shrikant-spb-field-group" style="margin-bottom:18px;">
			<label for="settings-footer-text"><?php esc_html_e( 'Default Footer Text', 'shrikant-social-post-builder' ); ?></label>
			<textarea id="settings-footer-text" class="shrikant-spb-input" style="max-width:400px; height:80px;"><?php echo esc_textarea( $shrikant_spb_settings['footer_text'] ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Substitutes the {{footer}} placeholder in templates.', 'shrikant-social-post-builder' ); ?></p>
		</div>

		<div class="shrikant-spb-field-group" style="margin-bottom:18px;">
			<label for="settings-default-template"><?php esc_html_e( 'Default Template', 'shrikant-social-post-builder' ); ?></label>
			<select id="settings-default-template" class="shrikant-spb-select" style="max-width:250px;">
				<?php foreach ( $shrikant_spb_template_names as $shrikant_spb_tpl_name ) : ?>
					<option value="<?php echo esc_attr( $shrikant_spb_tpl_name ); ?>" <?php selected( $shrikant_spb_settings['default_template'], $shrikant_spb_tpl_name ); ?>><?php echo esc_html( $shrikant_spb_tpl_name ); ?></option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Which template is selected by default in the Create Post wizard.', 'shrikant-social-post-builder' ); ?></p>
		</div>

		<div class="shrikant-spb-field-group" style="margin-bottom:18px;">
			<label for="settings-default-emoji"><?php esc_html_e( 'Default Emoji', 'shrikant-social-post-builder' ); ?></label>
			<input type="text" id="settings-default-emoji" class="shrikant-spb-input" style="max-width:100px;" value="<?php echo esc_attr( $shrikant_spb_settings['default_emoji'] ); ?>">
			<p class="description"><?php esc_html_e( 'Prepends to posts when "Show Emojis" option is enabled.', 'shrikant-social-post-builder' ); ?></p>
		</div>

		<div class="shrikant-spb-field-group" style="margin-bottom:18px;">
			<label for="settings-default-hashtags"><?php esc_html_e( 'Default Hashtags', 'shrikant-social-post-builder' ); ?></label>
			<input type="text" id="settings-default-hashtags" class="shrikant-spb-input" style="max-width:400px;" value="<?php echo esc_attr( $shrikant_spb_settings['default_hashtags'] ); ?>">
			<p class="description"><?php esc_html_e( 'Substitutes the {{hashtags}} placeholder. Separate tags with spaces (e.g. #News #Updates).', 'shrikant-social-post-builder' ); ?></p>
		</div>
	</div>

	<!-- History Logging Settings -->
	<div class="shrikant-spb-settings-section">
		<h3><?php esc_html_e( 'History Retention Settings', 'shrikant-social-post-builder' ); ?></h3>

		<div class="shrikant-spb-field-group" style="margin-bottom:18px;">
			<label for="settings-auto-delete"><?php esc_html_e( 'Auto Delete Logs (Days)', 'shrikant-social-post-builder' ); ?></label>
			<input type="number" id="settings-auto-delete" class="shrikant-spb-input" style="max-width:120px;" min="0" value="<?php echo esc_attr( $shrikant_spb_settings['auto_delete'] ); ?>" required>
			<p class="description"><?php esc_html_e( 'Automatically clear history records older than X days. Set to 0 to disable.', 'shrikant-social-post-builder' ); ?></p>
		</div>

		<div class="shrikant-spb-field-group" style="margin-bottom:18px;">
			<label for="settings-max-records"><?php esc_html_e( 'Maximum History Records Limit', 'shrikant-social-post-builder' ); ?></label>
			<input type="number" id="settings-max-records" class="shrikant-spb-input" style="max-width:120px;" min="0" value="<?php echo esc_attr( $shrikant_spb_settings['max_records'] ); ?>" required>
			<p class="description"><?php esc_html_e( 'Automatically delete oldest records if total database limit is exceeded. Set to 0 to disable.', 'shrikant-social-post-builder' ); ?></p>
		</div>
	</div>

	<!-- Copy Actions Options -->
	<div class="shrikant-spb-settings-section">
		<h3><?php esc_html_e( 'Copy Action Settings', 'shrikant-social-post-builder' ); ?></h3>

		<div class="shrikant-spb-field-group" style="margin-bottom:18px;">
			<label class="shrikant-spb-checkbox-label">
				<input type="checkbox" id="settings-auto-copy" <?php checked( $shrikant_spb_settings['auto_copy'], 1 ); ?>>
				<?php esc_html_e( 'Auto Copy generated messages to clipboard immediately after generation', 'shrikant-social-post-builder' ); ?>
			</label>
			<p class="description"><?php esc_html_e( 'Saves you one click by copying the text automatically when you hit Generate.', 'shrikant-social-post-builder' ); ?></p>
		</div>
	</div>

	<!-- Save Settings Actions Row -->
	<div>
		<button type="submit" class="shrikant-spb-btn shrikant-spb-btn-primary" style="padding:12px 28px;">
			<span class="dashicons dashicons-saved"></span> <?php esc_html_e( 'Save Settings Options', 'shrikant-social-post-builder' ); ?>
		</button>
	</div>
</form>
