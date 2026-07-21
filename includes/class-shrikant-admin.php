<?php
/**
 * Plugin Admin Screen Logic.
 *
 * @package Shrikant\SocialPostBuilder
 */

namespace Shrikant\SocialPostBuilder;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Shrikant_Admin
 * Handles admin menu creation, page rendering, asset enqueuing, and localization data.
 */
class Shrikant_Admin {

	/**
	 * Singleton instance.
	 *
	 * @var Shrikant_Admin|null
	 */
	private static ?Shrikant_Admin $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Shrikant_Admin
	 */
	public static function get_instance(): Shrikant_Admin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Add plugin admin menu and submenus.
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		// Main Menu Page.
		add_menu_page(
			__( 'Shrikant Social Post Builder', 'shrikant-social-post-builder' ),
			__( 'Social Post Builder', 'shrikant-social-post-builder' ),
			'manage_options',
			'shrikant-spb-dashboard',
			[ $this, 'render_admin_page' ],
			'dashicons-share',
			30
		);

		// Re-register Dashboard as submenu for consistency.
		add_submenu_page(
			'shrikant-spb-dashboard',
			__( 'Dashboard', 'shrikant-social-post-builder' ),
			__( 'Dashboard', 'shrikant-social-post-builder' ),
			'manage_options',
			'shrikant-spb-dashboard',
			[ $this, 'render_admin_page' ]
		);

		// Add submenus that point to separate tab views in our SPA template.
		$submenus = [
			'create-post' => __( 'Create Post', 'shrikant-social-post-builder' ),
			'history'     => __( 'History', 'shrikant-social-post-builder' ),
			'templates'   => __( 'Templates', 'shrikant-social-post-builder' ),
			'settings'    => __( 'Settings', 'shrikant-social-post-builder' ),
		];

		foreach ( $submenus as $tab => $title ) {
			add_submenu_page(
				'shrikant-spb-dashboard',
				$title,
				$title,
				'manage_options',
				'shrikant-spb-' . $tab,
				[ $this, 'render_admin_page' ]
			);
		}
	}

	/**
	 * Enqueue assets (CSS, JS) for admin panels.
	 *
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( string $hook ) {
		// Only enqueue on our plugin pages.
		if ( strpos( $hook, 'shrikant-spb' ) === false ) {
			return;
		}

		// Enqueue WordPress dashicons and jquery.
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_script( 'jquery' );

		// Enqueue our styles.
		wp_enqueue_style(
			'shrikant-spb-admin-style',
			SHRIKANT_SPB_PLUGIN_URL . 'assets/css/admin.css',
			[],
			SHRIKANT_SPB_VERSION
		);

		// Enqueue our scripts.
		wp_enqueue_script(
			'shrikant-spb-admin-script',
			SHRIKANT_SPB_PLUGIN_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			SHRIKANT_SPB_VERSION,
			true
		);

		// Fetch current options for client side.
		$settings = Shrikant_Settings::get_instance()->get_settings();

		// Localize parameters and i18n translation texts.
		$localized = wp_localize_script( 'shrikant-spb-admin-script', 'shrikant_spb_admin_opts', [
			'settings' => $settings,
			'i18n'     => [
				'select_posts_err'         => __( 'Please select at least one post to proceed.', 'shrikant-social-post-builder' ),
				'select_template_err'      => __( 'Please choose a template.', 'shrikant-social-post-builder' ),
				'confirm_delete'           => __( 'Are you sure you want to delete this history record?', 'shrikant-social-post-builder' ),
				'confirm_delete_template'  => __( 'Are you sure you want to delete this template?', 'shrikant-social-post-builder' ),
				'copied_toast'             => __( 'Copied to clipboard!', 'shrikant-social-post-builder' ),
				'draft_saved'              => __( 'Draft saved successfully.', 'shrikant-social-post-builder' ),
				'history_saved'            => __( 'Compilation saved to history.', 'shrikant-social-post-builder' ),
				'duplicated_toast'         => __( 'Duplicated post list to draft wizard!', 'shrikant-social-post-builder' ),
				'template_req_err'         => __( 'Both Name and Content fields are required.', 'shrikant-social-post-builder' ),
				'template_saved'           => __( 'Template saved successfully.', 'shrikant-social-post-builder' ),
				'settings_saved'           => __( 'Settings saved successfully.', 'shrikant-social-post-builder' ),
				'loading'                  => __( 'Loading...', 'shrikant-social-post-builder' ),
				'no_posts_found'           => __( 'No posts found matching the criteria.', 'shrikant-social-post-builder' ),
				'no_posts_selected_step2'  => __( 'No posts selected. Go back to Step 1 and check posts.', 'shrikant-social-post-builder' ),
				'no_history'               => __( 'No history records found.', 'shrikant-social-post-builder' ),
				'error_occurred'           => __( 'An error occurred. Please try again.', 'shrikant-social-post-builder' ),
				'compiling'                => __( 'Generating compiled message...', 'shrikant-social-post-builder' ),
				'generation_success'       => __( 'Message generated successfully!', 'shrikant-social-post-builder' ),
				'deleted_toast'            => __( 'Deleted successfully.', 'shrikant-social-post-builder' ),
				'add_template_title'       => __( 'Add Custom Template', 'shrikant-social-post-builder' ),
				'edit_template_title'      => __( 'Edit Custom Template', 'shrikant-social-post-builder' ),
			],
		] );
	}

	/**
	 * Render the main plugin interface shell.
	 *
	 * @return void
	 */
	public function render_admin_page() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'shrikant-spb-dashboard';
		$active_tab   = 'dashboard';

		if ( 'shrikant-spb-create-post' === $current_page ) {
			$active_tab = 'create-post';
		} elseif ( 'shrikant-spb-history' === $current_page ) {
			$active_tab = 'history';
		} elseif ( 'shrikant-spb-templates' === $current_page ) {
			$active_tab = 'templates';
		} elseif ( 'shrikant-spb-settings' === $current_page ) {
			$active_tab = 'settings';
		}

		// Security nonce.
		$nonce = wp_create_nonce( 'shrikant_spb_admin_nonce' );

		// Enforce wrapper div.
		echo '<div class="shrikant-spb-wrap">';
		
		// Render Header.
		echo '<header class="shrikant-spb-header">';
		echo '<h1>' . esc_html__( 'Shrikant Social Post Builder', 'shrikant-social-post-builder' ) . ' <span>v1.0.0</span></h1>';
		echo '<input type="hidden" id="shrikant_spb_nonce" value="' . esc_attr( $nonce ) . '">';
		echo '</header>';

		// Render Tabs Navigation.
		echo '<nav class="shrikant-spb-nav">';
		printf(
			'<a class="shrikant-spb-nav-item %s" href="#dashboard">%s</a>',
			( 'dashboard' === $active_tab ) ? 'active' : '',
			esc_html__( 'Dashboard', 'shrikant-social-post-builder' )
		);
		printf(
			'<a class="shrikant-spb-nav-item %s" href="#create-post">%s</a>',
			( 'create-post' === $active_tab ) ? 'active' : '',
			esc_html__( 'Create Post', 'shrikant-social-post-builder' )
		);
		printf(
			'<a class="shrikant-spb-nav-item %s" href="#history">%s</a>',
			( 'history' === $active_tab ) ? 'active' : '',
			esc_html__( 'History', 'shrikant-social-post-builder' )
		);
		printf(
			'<a class="shrikant-spb-nav-item %s" href="#templates">%s</a>',
			( 'templates' === $active_tab ) ? 'active' : '',
			esc_html__( 'Templates', 'shrikant-social-post-builder' )
		);
		printf(
			'<a class="shrikant-spb-nav-item %s" href="#settings">%s</a>',
			( 'settings' === $active_tab ) ? 'active' : '',
			esc_html__( 'Settings', 'shrikant-social-post-builder' )
		);
		echo '</nav>';

		// Toast container.
		echo '<div class="shrikant-spb-toast-container"></div>';

		// Render Screen Content Panels.
		$screens = [ 'dashboard', 'create-post', 'history', 'templates', 'settings' ];
		foreach ( $screens as $screen ) {
			$style_display = ( $screen === $active_tab ) ? 'block' : 'none';
			echo '<div id="' . esc_attr( $screen ) . '" class="shrikant-spb-tab-content" style="display:' . esc_attr( $style_display ) . ';">';
			$template_file = SHRIKANT_SPB_PLUGIN_PATH . 'templates/' . $screen . '.php';
			if ( file_exists( $template_file ) ) {
				include_once $template_file;
			}
			echo '</div>';
		}

		echo '</div>'; // End wrap
	}
}
