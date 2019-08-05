<?php
/**
 * Load the individual Posts reports in the admin.
 *
 * @package monsterinsights-page-insights
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MonsterInsights_Page_Insights_Admin
 */
final class MonsterInsights_Page_Insights_Admin {

	/**
	 * The post types for which the column is loaded.
	 *
	 * @var array
	 */
	public $post_types;

	/**
	 * MonsterInsights_Page_Insights_Admin constructor.
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'add_admin_column_for_public_post_types' ) );

		add_action( 'manage_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );

		add_action( 'manage_pages_custom_column', array( $this, 'custom_column_content' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );

		add_action( 'admin_footer', array( $this, 'add_reports_markup' ) );

		// Clear the cache when the profile is updated.
		add_action( 'update_option_monsterinsights_site_profile', array( $this, 'clear_cache' ) );
		add_action( 'update_site_option_monsterinsights_network_profile', array( $this, 'clear_network_cache' ) );
	}

	/**
	 * Grab only the public post types and add the insights column to their manage screen.
	 */
	public function add_admin_column_for_public_post_types() {

		$post_types = $this->get_post_types();

		foreach ( $post_types as $post_type ) {
			add_filter( 'manage_' . $post_type . '_posts_columns', array( $this, 'add_posts_table_column' ), 150 );
		}
	}


	/**
	 * Grab the post types for which we will add the column.
	 *
	 * @return array
	 */
	public function get_post_types() {

		if ( ! isset( $this->post_types ) ) {
			$post_types_args = array(
				'public' => true,
			);
			$post_types      = get_post_types( $post_types_args );

			// Allow plugins to exclude post types from having the column added.
			$this->post_types = apply_filters( 'monsterinsights_posts_post_types_admin_column', $post_types );
		}

		return $this->post_types;

	}

	/**
	 * Add custom column to manage posts/pages table.
	 *
	 * @param array $columns The current columns.
	 *
	 * @return array
	 */
	public function add_posts_table_column( $columns ) {

		if ( ! isset( $columns['monsterinsights_reports'] ) ) {
			if ( current_user_can( 'monsterinsights_view_dashboard' ) || apply_filters( 'monsterinsights_pageinsights_author_can_view', false ) ) {
				$columns['monsterinsights_reports'] = esc_html__( 'Insights', 'monsterinsights-pageinsights' );
			}
		}

		return $columns;

	}

	/**
	 * Load the reports icon in the custom column.
	 *
	 * @param string $column The column key.
	 * @param int    $post_id The current post id.
	 */
	public function custom_column_content( $column, $post_id ) {

		// Only use output for the MonsterInsights column.
		if ( 'monsterinsights_reports' === $column ) {
			$author_can_view = current_user_can( 'edit_post', $post_id ) && apply_filters( 'monsterinsights_pageinsights_author_can_view', false );
			// Don't show the insights button if the current user can't access the data.
			if ( current_user_can( 'monsterinsights_view_dashboard' ) || $author_can_view ) {
				$post_title = get_the_title( $post_id );
				echo '<button class="monsterinsights-reports-loader" type="button" data-post_id="' . esc_attr( $post_id ) . '" data-title="' . esc_attr( $post_title ) . '">';
				// Translators: %s is the post/page title.
				echo '<span class="screen-reader-text">' . sprintf( esc_html__( 'View Reports for “%s“', 'monsterinsights-pageinsights' ), esc_html( $post_title ) ) . '</span>';
				echo '</button>';
			}
		}
	}

	/**
	 * Check if we should load scripts and templates in current screen.
	 *
	 * @return bool
	 */
	public function should_load_in_current_screen() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();

		// Check if we need to load scripts on current page.
		if ( ! isset( $screen->post_type ) || ! isset( $screen->base ) || ! in_array( $screen->post_type, $this->get_post_types(), true ) || 'edit' !== $screen->base ) {
			return false;
		}

		return true;
	}

	/**
	 * Load the posts reports scripts.
	 *
	 * @param string $hook The current admin page hook.
	 *
	 * @return bool
	 */
	public function load_scripts( $hook ) {

		if ( ! $this->should_load_in_current_screen() ) {
			return false;
		}

		wp_enqueue_style( 'monsterinsights_page_insights_styles', MONSTERINSIGHTS_PAGE_INSIGHTS_ADDON_PLUGIN_URL . 'assets/css/page-insights-reports.css', array(), monsterinsights_get_asset_version() );

		wp_enqueue_script( 'monsterinsights_page_insights_script', MONSTERINSIGHTS_PAGE_INSIGHTS_ADDON_PLUGIN_URL . 'assets/js/page-insights-reports.js', array( 'jquery' ), monsterinsights_get_asset_version(), true );

		wp_localize_script( 'monsterinsights_page_insights_script', 'monsterinsights_page_insights_admin', array(
			'admin_nonce'   => wp_create_nonce( 'mi-admin-nonce' ),
			'isnetwork'     => is_network_admin(),
			'timezone'      => date( 'e' ),
			'error_text'    => esc_html__( 'Error', 'monsterinsights-page-insights' ),
			'error_default' => esc_html__( 'There was an issue loading the report data. Please try again.', 'monsterinsights-page-insights' ),
		) );

	}

	/**
	 * Add the reports overlay markup to the admin footer.
	 *
	 * @return bool
	 */
	public function add_reports_markup() {

		if ( ! $this->should_load_in_current_screen() ) {
			return false;
		}

		?>
		<div class="monsterinsights-reports-overlay">
			<div class="monsterinsights-reports-overlay-inner">
				<div class="monsterinsights-reports-overlay-header">
					<div class="monsterinsights-reports-overlay-logo">
						<img src="<?php echo esc_url( MONSTERINSIGHTS_PAGE_INSIGHTS_ADDON_PLUGIN_URL ); ?>/assets/img/MonsterInsights-Logo.png" srcset="<?php echo esc_url( MONSTERINSIGHTS_PAGE_INSIGHTS_ADDON_PLUGIN_URL ); ?>/assets/img/MonsterInsights-Logo@2x.png 2x" alt="MonsterInsights"/>
					</div>
					<h2 class="monsterinsights-reports-overlay-title"><?php esc_html_e( 'Page Insights for:', 'monsterinsights-page-insights' ); ?>
						<span class="monsterinsights-reports-overlay-title-text"></span></h2>
					<button type="button" class="monsterinsights-close-overlay">
						<span class="dashicons dashicons-no-alt"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Close reports overlay', 'monsterinsights-page-insights' ); ?></span>
					</button>
				</div>
				<div class="monsterinsights-reports-overlay-controls">
					<select id="monsterinsights-report-interval">
						<option value="30days"><?php esc_html_e( 'Last 30 Days', 'monsterinsights-page-insights' ); ?></option>
						<option value="yesterday"><?php esc_html_e( 'Yesterday', 'monsterinsights-page-insights' ); ?></option>
					</select>
				</div>
				<div class="monsterinsights-reports-overlay-content">
					<div class="monsterinsights-reports-overlay-loading"></div>
				</div>
			</div>
		</div>
		<script id="monsterinsights-pageinsights-error-template" type="text/html">
			<div class="monsterinsights-pageinsights-error">
				<div class="mi-pageinsights-icon mi-pageinsights-error mi-pageinsights-animate-error-icon" style="display: flex;">
					<span class="mi-pageinsights-x-mark"><span class="mi-pageinsights-x-mark-line-left"></span><span class="mi-pageinsights-x-mark-line-right"></span></span>
				</div>
				<h2 class="monsterinsights-pageinsights-error-title"></h2>
				<div class="monsterinsights-pageinsights-error-content"></div>
				<div class="monsterinsights-pageinsights-error-footer"></div>
			</div>
		</script>
		<?php

	}

	/**
	 * When this is called, clears the cache of all the sites in the network.
	 */
	public function clear_network_cache() {

		if ( function_exists( 'get_sites' ) && class_exists( 'WP_Site_Query' ) ) {

			$sites = get_sites();

			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				MonsterInsights_Page_Insights_Cache::get_instance()->clear_cache();
				MonsterInsights_Page_Insights_Cache::destroy(); // This is needed to use the right wpdb instance.
				restore_current_blog();
			}
		} else {
			$sites = wp_get_sites( array( 'limit' => 0 ) );

			foreach ( $sites as $site ) {

				switch_to_blog( $site['blog_id'] );
				MonsterInsights_Page_Insights_Cache::get_instance()->clear_cache();
				MonsterInsights_Page_Insights_Cache::destroy(); // This is needed to use the right wpdb instance.
				restore_current_blog();
			}
		}

	}

	/**
	 * Clear the cache for the current site.
	 */
	public function clear_cache() {
		MonsterInsights_Page_Insights_Cache::get_instance()->clear_cache();
	}

}
