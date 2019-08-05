<?php
/**
 * Plugin Name: MonsterInsights - Page Insights Addon
 * Plugin URI:  https://www.monsterinsights.com
 * Description: Adds individual page insights directly in the WordPress admin.
 * Author:      MonsterInsights Team
 * Author URI:  https://www.monsterinsights.com
 * Version:     1.3.0
 * Text Domain: monsterinsights-page-insights
 * Domain Path: languages
 *
 * @package monsterinsights-page-insights
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MonsterInsights_Posts
 */
class MonsterInsights_Page_Insights {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var MonsterInsights_Page_Insights
	 */
	public static $instance;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.3.0';

	/**
	 * The name of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'MonsterInsights Page Insights';

	/**
	 * Unique plugin slug identifier.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_slug = 'monsterinsights-page-insights';

	/**
	 * Plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file;

	/**
	 * MonsterInsights_Posts constructor.
	 */
	private function __construct() {
		$this->file = __FILE__;

		// Load the plugin textdomain.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		// Load the updater.
		add_action( 'monsterinsights_updater', array( $this, 'updater' ) );

		// Load the plugin.
		add_action( 'monsterinsights_load_plugins', array( $this, 'init' ), 99 );

		$this->install_hooks();
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return MonsterInsights_Page_Insights The MonsterInsights_Posts object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof MonsterInsights_Page_Insights ) ) {
			self::$instance = new MonsterInsights_Page_Insights();
		}

		return self::$instance;
	}

	/**
	 * Loads the plugin textdomain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( $this->file ) ) . '/languages/' );
	}

	/**
	 * Loads the plugin into WordPress.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		if ( ! defined( 'MONSTERINSIGHTS_PRO_VERSION' ) ) {
			// admin notice, MI not installed.
			add_action( 'admin_notices', array( self::$instance, 'requires_monsterinsights' ) );

			return;
		}

		if ( version_compare( MONSTERINSIGHTS_VERSION, '7.3.0', '<' ) ) {
			// MonsterInsights version not supported.
			add_action( 'admin_notices', array( self::$instance, 'requires_monsterinsights_version' ) );

			return;
		}

		if ( ! defined( 'MONSTERINSIGHTS_PAGE_INSIGHTS_ADDON_PLUGIN_URL' ) ) {
			define( 'MONSTERINSIGHTS_PAGE_INSIGHTS_ADDON_PLUGIN_URL', plugin_dir_url( $this->file ) );
		}

		// Load admin only components.
		if ( is_admin() ) {
			$this->require_admin();
		}

		$this->require_frontend();
	}

	/**
	 * Loads all admin related files into scope.
	 *
	 * @since 1.0.0
	 */
	public function require_admin() {
		// The caching class.
		require_once plugin_dir_path( $this->file ) . 'includes/class-monsterinsights-page-insights-cache.php';
		// Load the background data fetcher.
		require_once plugin_dir_path( $this->file ) . 'includes/class-monsterinsights-page-insights-background.php';

		// Load the admin interface.
		require_once plugin_dir_path( $this->file ) . 'includes/admin/class-monsterinsights-page-insights-admin.php';
		new MonsterInsights_Page_Insights_Admin();

		// Load the report.
		require_once plugin_dir_path( $this->file ) . 'includes/admin/reports/class-monsterinsights-report-page-insights.php';

	}

	/**
	 * Non-admin includes.
	 */
	public function require_frontend() {

		require_once plugin_dir_path( $this->file ) . 'includes/class-monsterinsights-page-insights-ajax.php';
		new MonsterInsights_Page_Insights_Ajax();
    }

	/**
	 * Initializes the addon updater.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The user license key.
	 */
	public function updater( $key ) {
		$args = array(
			'plugin_name' => $this->plugin_name,
			'plugin_slug' => $this->plugin_slug,
			'plugin_path' => plugin_basename( __FILE__ ),
			'plugin_url'  => trailingslashit( WP_PLUGIN_URL ) . $this->plugin_slug,
			'remote_url'  => 'https://www.monsterinsights.com/',
			'version'     => $this->version,
			'key'         => $key,
		);

		new MonsterInsights_Updater( $args );
	}

	/**
	 * Add the install hooks.
	 */
	public function install_hooks() {

		require_once plugin_dir_path( $this->file ) . 'includes/class-monsterinsights-page-insights-install.php';

		register_activation_hook( $this->file, array(
			'MonsterInsights_Page_Insights_Install',
			'handle_install',
		) );

		register_uninstall_hook( $this->file, array(
			'MonsterInsights_Page_Insights_Install',
			'handle_uninstall',
		) );

		// Add tables to new blogs.
		add_action( 'wpmu_new_blog', array( 'MonsterInsights_Page_Insights_Install', 'install_new_blog' ), 10, 6 );
	}

	/**
	 * Output a nag notice if the user does not have MI installed
	 */
	public function requires_monsterinsights() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please install MonsterInsights Pro to use the MonsterInsights Page Insights addon', 'monsterinsights-page-insights' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Output a nag notice if the user does not have MI version installed
	 */
	public function requires_monsterinsights_version() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'Please install or update MonsterInsights Pro with version 7.4.0 or newer to use the MonsterInsights Page Insights addon', 'monsterinsights-page-insights' ); ?></p>
		</div>
		<?php
	}
}

$monsterinsights_page_insights = MonsterInsights_Page_Insights::get_instance();
