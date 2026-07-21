<?php
/**
 * Create Post wizard template.
 *
 * @package Shrikant\SocialPostBuilder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Fetch categories, tags, and authors for filtering in Step 1.
$shrikant_spb_categories = get_categories( [ 'hide_empty' => false ] );
$shrikant_spb_tags       = get_tags( [ 'hide_empty' => false ] );
$shrikant_spb_authors    = get_users( [
	'who'     => 'authors',
	'orderby' => 'display_name',
] );
?>
<div class="shrikant-spb-wizard-container">
	<!-- Wizard Timeline Header -->
	<div class="shrikant-spb-wizard-steps">
		<div class="shrikant-spb-wizard-step active" data-step="1">
			<div class="shrikant-spb-step-num">1</div>
			<div class="shrikant-spb-step-label"><?php esc_html_e( 'Select Posts', 'shrikant-social-post-builder' ); ?></div>
		</div>
		<div class="shrikant-spb-wizard-step" data-step="2">
			<div class="shrikant-spb-step-num">2</div>
			<div class="shrikant-spb-step-label"><?php esc_html_e( 'Arrange Order', 'shrikant-social-post-builder' ); ?></div>
		</div>
		<div class="shrikant-spb-wizard-step" data-step="3">
			<div class="shrikant-spb-step-num">3</div>
			<div class="shrikant-spb-step-label"><?php esc_html_e( 'Choose Template', 'shrikant-social-post-builder' ); ?></div>
		</div>
		<div class="shrikant-spb-wizard-step" data-step="4">
			<div class="shrikant-spb-step-num">4</div>
			<div class="shrikant-spb-step-label"><?php esc_html_e( 'Options', 'shrikant-social-post-builder' ); ?></div>
		</div>
		<div class="shrikant-spb-wizard-step" data-step="5">
			<div class="shrikant-spb-step-num">5</div>
			<div class="shrikant-spb-step-label"><?php esc_html_e( 'Generate', 'shrikant-social-post-builder' ); ?></div>
		</div>
	</div>

	<!-- STEP 1: Select Posts Panel -->
	<div id="step-1" class="shrikant-spb-wizard-panel active">
		<h3 style="margin-top:0;"><?php esc_html_e( 'Step 1: Select Posts to Share', 'shrikant-social-post-builder' ); ?></h3>
		
		<!-- Filters Grid -->
		<div class="shrikant-spb-filters-grid">
			<div class="shrikant-spb-field-group">
				<label for="spb-post-search"><?php esc_html_e( 'Search Keyword', 'shrikant-social-post-builder' ); ?></label>
				<input type="text" id="spb-post-search" class="shrikant-spb-input" placeholder="<?php esc_attr_e( 'Search by title...', 'shrikant-social-post-builder' ); ?>">
			</div>
			
			<div class="shrikant-spb-field-group">
				<label for="spb-post-cat"><?php esc_html_e( 'Category', 'shrikant-social-post-builder' ); ?></label>
				<select id="spb-post-cat" class="shrikant-spb-select">
					<option value=""><?php esc_html_e( 'All Categories', 'shrikant-social-post-builder' ); ?></option>
					<?php foreach ( $shrikant_spb_categories as $cat ) : ?>
						<option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="shrikant-spb-field-group">
				<label for="spb-post-tag"><?php esc_html_e( 'Tag', 'shrikant-social-post-builder' ); ?></label>
				<select id="spb-post-tag" class="shrikant-spb-select">
					<option value=""><?php esc_html_e( 'All Tags', 'shrikant-social-post-builder' ); ?></option>
					<?php foreach ( $shrikant_spb_tags as $tag ) : ?>
						<option value="<?php echo esc_attr( $tag->term_id ); ?>"><?php echo esc_html( $tag->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="shrikant-spb-field-group">
				<label for="spb-post-author"><?php esc_html_e( 'Author', 'shrikant-social-post-builder' ); ?></label>
				<select id="spb-post-author" class="shrikant-spb-select">
					<option value=""><?php esc_html_e( 'All Authors', 'shrikant-social-post-builder' ); ?></option>
					<?php foreach ( $shrikant_spb_authors as $shrikant_spb_author ) : ?>
						<option value="<?php echo esc_attr( $shrikant_spb_author->ID ); ?>"><?php echo esc_html( $shrikant_spb_author->display_name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="shrikant-spb-field-group">
				<label for="spb-post-date"><?php esc_html_e( 'Date Published', 'shrikant-social-post-builder' ); ?></label>
				<select id="spb-post-date" class="shrikant-spb-select">
					<option value=""><?php esc_html_e( 'Anytime', 'shrikant-social-post-builder' ); ?></option>
					<option value="today"><?php esc_html_e( 'Today', 'shrikant-social-post-builder' ); ?></option>
					<option value="yesterday"><?php esc_html_e( 'Yesterday', 'shrikant-social-post-builder' ); ?></option>
					<option value="custom"><?php esc_html_e( 'Custom Range...', 'shrikant-social-post-builder' ); ?></option>
				</select>
			</div>
		</div>

		<!-- Custom Date Range -->
		<div class="shrikant-spb-filters-grid custom-date-range" style="display:none; margin-top:-10px;">
			<div class="shrikant-spb-field-group">
				<label for="spb-post-start-date"><?php esc_html_e( 'Start Date', 'shrikant-social-post-builder' ); ?></label>
				<input type="date" id="spb-post-start-date" class="shrikant-spb-input">
			</div>
			<div class="shrikant-spb-field-group">
				<label for="spb-post-end-date"><?php esc_html_e( 'End Date (Optional)', 'shrikant-social-post-builder' ); ?></label>
				<input type="date" id="spb-post-end-date" class="shrikant-spb-input">
			</div>
		</div>

		<!-- Post Selection Grid/Table -->
		<div class="shrikant-spb-posts-list-wrap">
			<table class="shrikant-spb-posts-table">
				<thead>
					<tr>
						<th style="width: 40px;"><input type="checkbox" id="spb-select-all-posts"></th>
						<th><?php esc_html_e( 'Post Title', 'shrikant-social-post-builder' ); ?></th>
						<th><?php esc_html_e( 'Category', 'shrikant-social-post-builder' ); ?></th>
						<th><?php esc_html_e( 'Published Date', 'shrikant-social-post-builder' ); ?></th>
					</tr>
				</thead>
				<tbody id="spb-posts-table-body">
					<!-- Loaded dynamically via AJAX -->
				</tbody>
			</table>
		</div>
	</div>

	<!-- STEP 2: Arrange Order Panel -->
	<div id="step-2" class="shrikant-spb-wizard-panel">
		<h3 style="margin-top:0;"><?php esc_html_e( 'Step 2: Arrange Post Order', 'shrikant-social-post-builder' ); ?></h3>
		<p style="color:var(--shrikant-spb-text-muted); margin-bottom:15px; font-size:13px;">
			<?php esc_html_e( 'Drag and drop items to arrange the order they will appear in your generated message. Click the trash icon to exclude posts.', 'shrikant-social-post-builder' ); ?>
		</p>
		<div class="shrikant-spb-sortable-list" id="spb-sortable-list">
			<!-- Populated dynamically by JS -->
		</div>
	</div>

	<!-- STEP 3: Choose Template Panel -->
	<div id="step-3" class="shrikant-spb-wizard-panel">
		<h3 style="margin-top:0;"><?php esc_html_e( 'Step 3: Select Sharing Platform Template', 'shrikant-social-post-builder' ); ?></h3>
		<div class="shrikant-spb-templates-grid" id="spb-templates-step3-grid">
			<!-- Loaded dynamically by JS -->
		</div>
	</div>

	<!-- STEP 4: Options Panel -->
	<div id="step-4" class="shrikant-spb-wizard-panel">
		<h3 style="margin-top:0;"><?php esc_html_e( 'Step 4: Layout and Formatting Options', 'shrikant-social-post-builder' ); ?></h3>
		<div class="shrikant-spb-options-grid">
			<label class="shrikant-spb-checkbox-label">
				<input type="checkbox" id="chk-number-posts" checked>
				<?php esc_html_e( 'Number posts list (1, 2, 3...)', 'shrikant-social-post-builder' ); ?>
			</label>
			<label class="shrikant-spb-checkbox-label">
				<input type="checkbox" id="chk-show-emojis" checked>
				<?php esc_html_e( 'Show emojis', 'shrikant-social-post-builder' ); ?>
			</label>
			<label class="shrikant-spb-checkbox-label">
				<input type="checkbox" id="chk-add-footer" checked>
				<?php esc_html_e( 'Add footer message text', 'shrikant-social-post-builder' ); ?>
			</label>
			<label class="shrikant-spb-checkbox-label">
				<input type="checkbox" id="chk-add-website" checked>
				<?php esc_html_e( 'Add website URL text', 'shrikant-social-post-builder' ); ?>
			</label>
			<label class="shrikant-spb-checkbox-label">
				<input type="checkbox" id="chk-add-hashtags" checked>
				<?php esc_html_e( 'Add hashtags group', 'shrikant-social-post-builder' ); ?>
			</label>
			<label class="shrikant-spb-checkbox-label">
				<input type="checkbox" id="chk-remove-duplicates" checked>
				<?php esc_html_e( 'Remove duplicate posts', 'shrikant-social-post-builder' ); ?>
			</label>
		</div>
	</div>

	<!-- STEP 5: Generate & Actions Panel -->
	<div id="step-5" class="shrikant-spb-wizard-panel">
		<h3 style="margin-top:0;"><?php esc_html_e( 'Step 5: Generate & Copy Message', 'shrikant-social-post-builder' ); ?></h3>
		
		<textarea id="spb-generated-output" class="shrikant-spb-output-box" placeholder="<?php esc_attr_e( 'Your compiled post will appear here...', 'shrikant-social-post-builder' ); ?>"></textarea>
		
		<div class="shrikant-spb-actions-row">
			<button class="shrikant-spb-btn shrikant-spb-btn-primary" id="btn-generate-message">
				<span class="dashicons dashicons-update"></span> <?php esc_html_e( 'Generate Again', 'shrikant-social-post-builder' ); ?>
			</button>
			<button class="shrikant-spb-btn shrikant-spb-btn-primary" id="btn-copy-message" style="background:var(--shrikant-spb-success);">
				<span class="dashicons dashicons-clipboard"></span> <?php esc_html_e( 'Copy to Clipboard', 'shrikant-social-post-builder' ); ?>
			</button>
			<button class="shrikant-spb-btn shrikant-spb-btn-secondary" id="btn-save-draft">
				<span class="dashicons dashicons-category"></span> <?php esc_html_e( 'Save Draft', 'shrikant-social-post-builder' ); ?>
			</button>
			<button class="shrikant-spb-btn shrikant-spb-btn-secondary" id="btn-save-history">
				<span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Save to History', 'shrikant-social-post-builder' ); ?>
			</button>
			<button class="shrikant-spb-btn shrikant-spb-btn-danger" id="btn-clear-wizard">
				<span class="dashicons dashicons-no-alt"></span> <?php esc_html_e( 'Clear All', 'shrikant-social-post-builder' ); ?>
			</button>
		</div>
	</div>

	<!-- Footer Wizard Control buttons -->
	<div class="shrikant-spb-wizard-nav">
		<button class="shrikant-spb-btn shrikant-spb-btn-secondary btn-prev-step" disabled>
			<span class="dashicons dashicons-arrow-left-alt2"></span> <?php esc_html_e( 'Back', 'shrikant-social-post-builder' ); ?>
		</button>
		<button class="shrikant-spb-btn shrikant-spb-btn-primary btn-next-step">
			<?php esc_html_e( 'Next', 'shrikant-social-post-builder' ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>
		</button>
	</div>
</div>
