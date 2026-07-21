<?php
/**
 * History tracking screen template.
 *
 * @package Shrikant\SocialPostBuilder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$shrikant_spb_template_names  = $wpdb->get_col( "SELECT DISTINCT name FROM {$wpdb->prefix}shrikant_spb_templates ORDER BY name ASC" );
?>
<div class="shrikant-spb-card" style="margin-bottom: 24px; padding: 20px;">
	<h3 style="margin-top:0; margin-bottom: 12px;"><?php esc_html_e( 'Search & Filter History', 'shrikant-social-post-builder' ); ?></h3>
	
	<!-- Search & Filters grid -->
	<div class="shrikant-spb-filters-grid">
		<div class="shrikant-spb-field-group">
			<label for="spb-history-search"><?php esc_html_e( 'Keyword Search', 'shrikant-social-post-builder' ); ?></label>
			<input type="text" id="spb-history-search" class="shrikant-spb-input" placeholder="<?php esc_attr_e( 'Search content or titles...', 'shrikant-social-post-builder' ); ?>">
		</div>

		<div class="shrikant-spb-field-group">
			<label for="spb-history-template"><?php esc_html_e( 'Template', 'shrikant-social-post-builder' ); ?></label>
			<select id="spb-history-template" class="shrikant-spb-select">
				<option value=""><?php esc_html_e( 'All Templates', 'shrikant-social-post-builder' ); ?></option>
				<?php foreach ( $shrikant_spb_template_names as $shrikant_spb_tpl_name ) : ?>
					<option value="<?php echo esc_attr( $shrikant_spb_tpl_name ); ?>"><?php echo esc_html( $shrikant_spb_tpl_name ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="shrikant-spb-field-group">
			<label for="spb-history-date-filter"><?php esc_html_e( 'Date Range', 'shrikant-social-post-builder' ); ?></label>
			<select id="spb-history-date-filter" class="shrikant-spb-select">
				<option value=""><?php esc_html_e( 'All Time', 'shrikant-social-post-builder' ); ?></option>
				<option value="today"><?php esc_html_e( 'Today', 'shrikant-social-post-builder' ); ?></option>
				<option value="yesterday"><?php esc_html_e( 'Yesterday', 'shrikant-social-post-builder' ); ?></option>
				<option value="week"><?php esc_html_e( 'Last 7 Days', 'shrikant-social-post-builder' ); ?></option>
				<option value="month"><?php esc_html_e( 'This Month', 'shrikant-social-post-builder' ); ?></option>
				<option value="custom"><?php esc_html_e( 'Custom Range...', 'shrikant-social-post-builder' ); ?></option>
			</select>
		</div>
	</div>

	<!-- Custom Date Range -->
	<div class="shrikant-spb-filters-grid history-custom-date" style="display:none; margin-top:-10px;">
		<div class="shrikant-spb-field-group">
			<label for="spb-history-start"><?php esc_html_e( 'Start Date', 'shrikant-social-post-builder' ); ?></label>
			<input type="date" id="spb-history-start" class="shrikant-spb-input">
		</div>
		<div class="shrikant-spb-field-group">
			<label for="spb-history-end"><?php esc_html_e( 'End Date', 'shrikant-social-post-builder' ); ?></label>
			<input type="date" id="spb-history-end" class="shrikant-spb-input">
		</div>
	</div>
</div>

<!-- History Records Table -->
<div class="shrikant-spb-table-wrap">
	<table class="shrikant-spb-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Date Generated', 'shrikant-social-post-builder' ); ?></th>
				<th><?php esc_html_e( 'Template', 'shrikant-social-post-builder' ); ?></th>
				<th><?php esc_html_e( 'Posts Included', 'shrikant-social-post-builder' ); ?></th>
				<th><?php esc_html_e( 'Status', 'shrikant-social-post-builder' ); ?></th>
				<th><?php esc_html_e( 'User', 'shrikant-social-post-builder' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'shrikant-social-post-builder' ); ?></th>
			</tr>
		</thead>
		<tbody id="spb-history-table-body">
			<!-- Loaded dynamically via AJAX -->
		</tbody>
	</table>

	<!-- Pagination Footer -->
	<div class="shrikant-spb-pagination">
		<span id="history-page-info" style="align-self:center; font-size:12px; font-weight:600; color:var(--shrikant-spb-text-muted); margin-right:10px;">Page 1 of 1</span>
		<button class="shrikant-spb-pagination-btn" id="history-prev" disabled><?php esc_html_e( 'Previous', 'shrikant-social-post-builder' ); ?></button>
		<button class="shrikant-spb-pagination-btn" id="history-next" disabled><?php esc_html_e( 'Next', 'shrikant-social-post-builder' ); ?></button>
	</div>
</div>

<!-- View History Log Modal -->
<div class="shrikant-spb-modal" id="history-view-modal">
	<div class="shrikant-spb-modal-content">
		<div class="shrikant-spb-modal-header">
			<h3><?php esc_html_e( 'View Generated Message', 'shrikant-social-post-builder' ); ?></h3>
			<span class="shrikant-spb-modal-close">&times;</span>
		</div>
		<div class="shrikant-spb-modal-body">
			<div style="display:flex; justify-content:space-between; margin-bottom:15px; font-size:12px; color:var(--shrikant-spb-text-muted);">
				<div><?php esc_html_e( 'Template:', 'shrikant-social-post-builder' ); ?> <strong id="modal-template-name">WhatsApp</strong></div>
				<div><?php esc_html_e( 'Date:', 'shrikant-social-post-builder' ); ?> <strong id="modal-creation-time">Never</strong></div>
			</div>
			
			<div style="margin-bottom:15px;">
				<h4 style="margin-top:0; margin-bottom:6px; font-size:13px; font-weight:600; color:var(--shrikant-spb-text-main);"><?php esc_html_e( 'Posts included in compilation:', 'shrikant-social-post-builder' ); ?></h4>
				<ul id="modal-posts-list" style="margin:0; padding-left:20px; font-size:12px; color:var(--shrikant-spb-text-muted);">
					<!-- Loaded dynamically -->
				</ul>
			</div>

			<div>
				<h4 style="margin-top:0; margin-bottom:6px; font-size:13px; font-weight:600; color:var(--shrikant-spb-text-main);"><?php esc_html_e( 'Generated Message Text:', 'shrikant-social-post-builder' ); ?></h4>
				<textarea id="modal-text-content" class="shrikant-spb-output-box" style="height:180px; margin-bottom:0;" readonly></textarea>
			</div>
		</div>
		<div class="shrikant-spb-modal-footer">
			<button class="shrikant-spb-btn shrikant-spb-btn-secondary btn-modal-close"><?php esc_html_e( 'Close', 'shrikant-social-post-builder' ); ?></button>
			<button class="shrikant-spb-btn shrikant-spb-btn-primary" id="btn-modal-copy">
				<span class="dashicons dashicons-clipboard"></span> <?php esc_html_e( 'Copy Again', 'shrikant-social-post-builder' ); ?>
			</button>
		</div>
	</div>
</div>
