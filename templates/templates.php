<?php
/**
 * Templates manager screen template.
 *
 * @package Shrikant\SocialPostBuilder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="shrikant-spb-card shrikant-spb-header-card" style="margin-bottom: 24px; padding: 20px;">
	<div>
		<h3 style="margin-top:0; margin-bottom: 6px;"><?php esc_html_e( 'Message Templates Manager', 'shrikant-social-post-builder' ); ?></h3>
		<p style="margin:0; color:var(--shrikant-spb-text-muted); font-size:13px;">
			<?php esc_html_e( 'Add or update templates that control how compiled social posts are structured.', 'shrikant-social-post-builder' ); ?>
		</p>
	</div>
	<button class="shrikant-spb-btn shrikant-spb-btn-primary btn-add-template">
		<span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e( 'Create Template', 'shrikant-social-post-builder' ); ?>
	</button>
</div>

<!-- Templates Data Table -->
<div class="shrikant-spb-table-wrap">
	<table class="shrikant-spb-table">
		<thead>
			<tr>
				<th style="width: 200px;"><?php esc_html_e( 'Template Name', 'shrikant-social-post-builder' ); ?></th>
				<th><?php esc_html_e( 'Text Content Template', 'shrikant-social-post-builder' ); ?></th>
				<th style="width: 100px;"><?php esc_html_e( 'Status', 'shrikant-social-post-builder' ); ?></th>
				<th style="width: 150px;"><?php esc_html_e( 'Actions', 'shrikant-social-post-builder' ); ?></th>
			</tr>
		</thead>
		<tbody id="spb-templates-table-body">
			<!-- Loaded dynamically via AJAX -->
		</tbody>
	</table>
</div>

<!-- Add/Edit Template Modal -->
<div class="shrikant-spb-modal" id="template-editor-modal">
	<div class="shrikant-spb-modal-content" style="max-width: 650px;">
		<div class="shrikant-spb-modal-header">
			<h3 id="modal-template-editor-title"><?php esc_html_e( 'Create Custom Template', 'shrikant-social-post-builder' ); ?></h3>
			<span class="shrikant-spb-modal-close">&times;</span>
		</div>
		<div class="shrikant-spb-modal-body">
			<input type="hidden" id="template-editor-id" value="0">
			
			<div class="shrikant-spb-field-group" style="margin-bottom:15px;">
				<label for="template-editor-name"><?php esc_html_e( 'Template Name', 'shrikant-social-post-builder' ); ?></label>
				<input type="text" id="template-editor-name" class="shrikant-spb-input" placeholder="<?php esc_attr_e( 'e.g. WhatsApp Broadcast, Viber Group', 'shrikant-social-post-builder' ); ?>">
			</div>

			<div class="shrikant-spb-field-group" style="margin-bottom:5px;">
				<label for="template-editor-content"><?php esc_html_e( 'Template Content Markup', 'shrikant-social-post-builder' ); ?></label>
				<textarea id="template-editor-content" class="shrikant-spb-template-textarea" placeholder="<?php esc_attr_e( "🔥 Today's Updates\n\n{{posts}}\n\n🌐 Website:\n{{website}}", 'shrikant-social-post-builder' ); ?>"></textarea>
			</div>

			<!-- Placeholders Insertion Helpers -->
			<div class="shrikant-spb-placeholders-hint">
				<span style="font-weight:600; color:var(--shrikant-spb-text-main); font-size:12px;"><?php esc_html_e( 'Click to insert placeholders:', 'shrikant-social-post-builder' ); ?></span>
				<span class="shrikant-spb-placeholder-badge" title="<?php esc_attr_e( 'List of selected post titles and URLs', 'shrikant-social-post-builder' ); ?>">{{posts}}</span>
				<span class="shrikant-spb-placeholder-badge" title="<?php esc_attr_e( 'Your configured website URL', 'shrikant-social-post-builder' ); ?>">{{website}}</span>
				<span class="shrikant-spb-placeholder-badge" title="<?php esc_attr_e( 'Your configured footer text', 'shrikant-social-post-builder' ); ?>">{{footer}}</span>
				<span class="shrikant-spb-placeholder-badge" title="<?php esc_attr_e( 'Your configured default hashtags', 'shrikant-social-post-builder' ); ?>">{{hashtags}}</span>
				<span class="shrikant-spb-placeholder-badge" title="<?php esc_attr_e( 'Current date formatted', 'shrikant-social-post-builder' ); ?>">{{date}}</span>
				<span class="shrikant-spb-placeholder-badge" title="<?php esc_attr_e( 'Current time formatted', 'shrikant-social-post-builder' ); ?>">{{time}}</span>
			</div>
		</div>
		<div class="shrikant-spb-modal-footer">
			<button class="shrikant-spb-btn shrikant-spb-btn-secondary btn-modal-close"><?php esc_html_e( 'Cancel', 'shrikant-social-post-builder' ); ?></button>
			<button class="shrikant-spb-btn shrikant-spb-btn-primary" id="btn-save-template"><?php esc_html_e( 'Save Template', 'shrikant-social-post-builder' ); ?></button>
		</div>
	</div>
</div>
