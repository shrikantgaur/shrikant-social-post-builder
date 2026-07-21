<?php
/**
 * Dashboard screen template.
 *
 * @package Shrikant\SocialPostBuilder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="shrikant-spb-dashboard-grid">
	<!-- Today's Posts Card -->
	<div class="shrikant-spb-card card-metric card-indigo">
		<div class="shrikant-spb-card-title"><?php esc_html_e( "Today's Published Posts", "shrikant-social-post-builder" ); ?></div>
		<div class="shrikant-spb-card-body-row">
			<div class="shrikant-spb-card-val" id="stat-today-posts">0</div>
			<div class="shrikant-spb-card-icon-box">
				<span class="dashicons dashicons-admin-post"></span>
			</div>
		</div>
		<div class="shrikant-spb-card-desc"><?php esc_html_e( "Standard post types published today.", "shrikant-social-post-builder" ); ?></div>
	</div>

	<!-- Today's Generated Messages Card -->
	<div class="shrikant-spb-card card-metric card-success">
		<div class="shrikant-spb-card-title"><?php esc_html_e( "Messages Generated Today", "shrikant-social-post-builder" ); ?></div>
		<div class="shrikant-spb-card-body-row">
			<div class="shrikant-spb-card-val" id="stat-today-generated">0</div>
			<div class="shrikant-spb-card-icon-box">
				<span class="dashicons dashicons-share"></span>
			</div>
		</div>
		<div class="shrikant-spb-card-desc"><?php esc_html_e( "Total messages generated in the last 24h.", "shrikant-social-post-builder" ); ?></div>
	</div>

	<!-- Total History Card -->
	<div class="shrikant-spb-card card-metric card-info">
		<div class="shrikant-spb-card-title"><?php esc_html_e( "Total History Records", "shrikant-social-post-builder" ); ?></div>
		<div class="shrikant-spb-card-body-row">
			<div class="shrikant-spb-card-val" id="stat-total-history">0</div>
			<div class="shrikant-spb-card-icon-box">
				<span class="dashicons dashicons-backup"></span>
			</div>
		</div>
		<div class="shrikant-spb-card-desc"><?php esc_html_e( "Total logs tracked in database.", "shrikant-social-post-builder" ); ?></div>
	</div>

	<!-- Most Used Template -->
	<div class="shrikant-spb-card card-metric card-warning">
		<div class="shrikant-spb-card-title"><?php esc_html_e( "Most Used Template", "shrikant-social-post-builder" ); ?></div>
		<div class="shrikant-spb-card-body-row">
			<div class="shrikant-spb-card-val" style="font-size:20px; font-weight:700;" id="stat-most-used-template">None</div>
			<div class="shrikant-spb-card-icon-box">
				<span class="dashicons dashicons-format-aside"></span>
			</div>
		</div>
		<div class="shrikant-spb-card-desc"><?php esc_html_e( "Based on history logging stats.", "shrikant-social-post-builder" ); ?></div>
	</div>

	<!-- Last Generated Time -->
	<div class="shrikant-spb-card card-metric card-danger">
		<div class="shrikant-spb-card-title"><?php esc_html_e( "Last Generated Time", "shrikant-social-post-builder" ); ?></div>
		<div class="shrikant-spb-card-body-row">
			<div class="shrikant-spb-card-val" style="font-size:14px; font-weight:700; word-break:break-word; line-height:1.4; padding-right:8px;" id="stat-last-time">Never</div>
			<div class="shrikant-spb-card-icon-box">
				<span class="dashicons dashicons-clock"></span>
			</div>
		</div>
		<div class="shrikant-spb-card-desc"><?php esc_html_e( "Time of your last compiled message.", "shrikant-social-post-builder" ); ?></div>
	</div>
</div>

<!-- Quick Actions Section -->
<div class="shrikant-spb-card" style="background:#fff; padding:28px;">
	<h3 style="margin-top:0;margin-bottom:12px;font-size:16px;font-weight:600;color:var(--shrikant-spb-primary);"><?php esc_html_e( "Quick Actions", "shrikant-social-post-builder" ); ?></h3>
	<p style="color:var(--shrikant-spb-text-muted);margin-bottom:20px;"><?php esc_html_e( "Streamline your publishing workflow. Generate shareable messages or view your previous creations in seconds.", "shrikant-social-post-builder" ); ?></p>
	<div class="shrikant-spb-actions-row">
		<button class="shrikant-spb-btn shrikant-spb-btn-primary action-create-new">
			<span class="dashicons dashicons-edit"></span> <?php esc_html_e( "Create New Message", "shrikant-social-post-builder" ); ?>
		</button>
		<button class="shrikant-spb-btn shrikant-spb-btn-secondary action-open-history">
			<span class="dashicons dashicons-backup"></span> <?php esc_html_e( "Open History Logs", "shrikant-social-post-builder" ); ?>
		</button>
		<button class="shrikant-spb-btn shrikant-spb-btn-secondary action-open-settings">
			<span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( "Configure Settings", "shrikant-social-post-builder" ); ?>
		</button>
	</div>
</div>

<!-- User Guidelines Card -->
<div class="shrikant-spb-card" style="background:#fff; padding:28px; margin-top:24px;">
	<h3 style="margin-top:0;margin-bottom:20px;font-size:16px;font-weight:600;color:var(--shrikant-spb-primary);display:flex;align-items:center;gap:8px;border-bottom:1px solid var(--shrikant-spb-border);padding-bottom:12px;">
		<span class="dashicons dashicons-editor-help" style="color:var(--shrikant-spb-primary);"></span> 
		<?php esc_html_e( "Shrikant Social Post Builder - User Guide", "shrikant-social-post-builder" ); ?>
	</h3>
	
	<div class="shrikant-spb-guide-layout">
		
		<!-- Left Column: Visual Steps Workflow -->
		<div>
			<h4 style="margin:0 0 16px 0; font-size:14px; font-weight:600; color:var(--shrikant-spb-text-main); display:flex; align-items:center; gap:6px;">
				<span class="dashicons dashicons-image-rotate"></span>
				<?php esc_html_e( "How It Works (5-Step Workflow)", "shrikant-social-post-builder" ); ?>
			</h4>
			
			<div style="display:flex; flex-direction:column; gap:16px;">
				
				<div style="display:flex; gap:12px;">
					<div style="width:28px; height:28px; border-radius:50%; background:var(--shrikant-spb-primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; flex-shrink:0;">1</div>
					<div>
						<h5 style="margin:0 0 4px 0; font-size:13px; font-weight:600; color:var(--shrikant-spb-text-main);">
							📝 <?php esc_html_e( "Select Today's Posts", "shrikant-social-post-builder" ); ?>
						</h5>
						<p style="margin:0; font-size:12px; color:var(--shrikant-spb-text-muted); line-height:1.4;">
							<?php esc_html_e( "Go to the 'Create Post' tab. Search posts or filter them by Category, Tag, Author, or Custom Date. Check the posts you want to compile.", "shrikant-social-post-builder" ); ?>
						</p>
					</div>
				</div>

				<div style="display:flex; gap:12px;">
					<div style="width:28px; height:28px; border-radius:50%; background:var(--shrikant-spb-primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; flex-shrink:0;">2</div>
					<div>
						<h5 style="margin:0 0 4px 0; font-size:13px; font-weight:600; color:var(--shrikant-spb-text-main);">
							↕️ <?php esc_html_e( "Arrange Order (Drag & Drop)", "shrikant-social-post-builder" ); ?>
						</h5>
						<p style="margin:0; font-size:12px; color:var(--shrikant-spb-text-muted); line-height:1.4;">
							<?php esc_html_e( "In Step 2, arrange the importance of your posts. Simply click and drag items to swap their ranking order.", "shrikant-social-post-builder" ); ?>
						</p>
					</div>
				</div>

				<div style="display:flex; gap:12px;">
					<div style="width:28px; height:28px; border-radius:50%; background:var(--shrikant-spb-primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; flex-shrink:0;">3</div>
					<div>
						<h5 style="margin:0 0 4px 0; font-size:13px; font-weight:600; color:var(--shrikant-spb-text-main);">
							🎨 <?php esc_html_e( "Choose Platform Template", "shrikant-social-post-builder" ); ?>
						</h5>
						<p style="margin:0; font-size:12px; color:var(--shrikant-spb-text-muted); line-height:1.4;">
							<?php esc_html_e( "In Step 3, choose where you want to share. Select WhatsApp, Telegram, Facebook, LinkedIn, X, or any custom template.", "shrikant-social-post-builder" ); ?>
						</p>
					</div>
				</div>

				<div style="display:flex; gap:12px;">
					<div style="width:28px; height:28px; border-radius:50%; background:var(--shrikant-spb-primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; flex-shrink:0;">4</div>
					<div>
						<h5 style="margin:0 0 4px 0; font-size:13px; font-weight:600; color:var(--shrikant-spb-text-main);">
							⚙️ <?php esc_html_e( "Toggle Formatting Options", "shrikant-social-post-builder" ); ?>
						</h5>
						<p style="margin:0; font-size:12px; color:var(--shrikant-spb-text-muted); line-height:1.4;">
							<?php esc_html_e( "Customize the look: enable numbering lists, emojis, custom footers, site URLs, hashtags, or filter duplicate posts.", "shrikant-social-post-builder" ); ?>
						</p>
					</div>
				</div>

				<div style="display:flex; gap:12px;">
					<div style="width:28px; height:28px; border-radius:50%; background:var(--shrikant-spb-success); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; flex-shrink:0;">5</div>
					<div>
						<h5 style="margin:0 0 4px 0; font-size:13px; font-weight:600; color:var(--shrikant-spb-text-main);">
							🔥 <?php esc_html_e( "Generate & Share Anywhere", "shrikant-social-post-builder" ); ?>
						</h5>
						<p style="margin:0; font-size:12px; color:var(--shrikant-spb-text-muted); line-height:1.4;">
							<?php esc_html_e( "Click 'Generate' in Step 5. Click the green 'Copy to Clipboard' button, and paste the compiled message directly into WhatsApp, Telegram, or other chat platforms.", "shrikant-social-post-builder" ); ?>
						</p>
					</div>
				</div>

			</div>
		</div>

		<!-- Right Column: Placeholder Cheat Sheet -->
		<div style="background:var(--shrikant-spb-bg); border:1px solid var(--shrikant-spb-border); padding:20px; border-radius:var(--shrikant-spb-radius);">
			<h4 style="margin:0 0 14px 0; font-size:14px; font-weight:600; color:var(--shrikant-spb-primary); display:flex; align-items:center; gap:6px;">
				<span class="dashicons dashicons-code-standards"></span>
				<?php esc_html_e( "Template Placeholders", "shrikant-social-post-builder" ); ?>
			</h4>
			<p style="margin:0 0 16px 0; font-size:11px; color:var(--shrikant-spb-text-muted); line-height:1.4;">
				<?php esc_html_e( "Use these tags in your custom templates to merge dynamic content into social messages:", "shrikant-social-post-builder" ); ?>
			</p>
			
			<div style="display:flex; flex-direction:column; gap:12px;">
				<div>
					<code style="font-size:11px; font-weight:700; color:#c7254e; background:#f9f2f4; padding:2px 4px; border-radius:4px;">{{posts}}</code>
					<div style="font-size:11px; color:var(--shrikant-spb-text-muted); margin-top:2px;"><?php esc_html_e( "Formats & lists selected post titles with URLs.", "shrikant-social-post-builder" ); ?></div>
				</div>
				<div>
					<code style="font-size:11px; font-weight:700; color:#c7254e; background:#f9f2f4; padding:2px 4px; border-radius:4px;">{{website}}</code>
					<div style="font-size:11px; color:var(--shrikant-spb-text-muted); margin-top:2px;"><?php esc_html_e( "Inserts Website URL configured in Settings.", "shrikant-social-post-builder" ); ?></div>
				</div>
				<div>
					<code style="font-size:11px; font-weight:700; color:#c7254e; background:#f9f2f4; padding:2px 4px; border-radius:4px;">{{footer}}</code>
					<div style="font-size:11px; color:var(--shrikant-spb-text-muted); margin-top:2px;"><?php esc_html_e( "Appends configured default footer text.", "shrikant-social-post-builder" ); ?></div>
				</div>
				<div>
					<code style="font-size:11px; font-weight:700; color:#c7254e; background:#f9f2f4; padding:2px 4px; border-radius:4px;">{{hashtags}}</code>
					<div style="font-size:11px; color:var(--shrikant-spb-text-muted); margin-top:2px;"><?php esc_html_e( "Appends configured default hashtags.", "shrikant-social-post-builder" ); ?></div>
				</div>
				<div>
					<code style="font-size:11px; font-weight:700; color:#c7254e; background:#f9f2f4; padding:2px 4px; border-radius:4px;">{{date}} / {{time}}</code>
					<div style="font-size:11px; color:var(--shrikant-spb-text-muted); margin-top:2px;"><?php esc_html_e( "Inserts current localized date and time stamp.", "shrikant-social-post-builder" ); ?></div>
				</div>
			</div>
		</div>

	</div>
</div>

