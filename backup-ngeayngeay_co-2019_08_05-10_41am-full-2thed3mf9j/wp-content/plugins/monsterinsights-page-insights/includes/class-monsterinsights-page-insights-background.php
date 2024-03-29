<?php
/**
 * Handle background data-grabbing from the relay for all pages.
 *
 * @package monsterinsights-page-insights
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MonsterInsights_Page_Insights_Background
 */
class MonsterInsights_Page_Insights_Background {

	/**
	 * MonsterInsights_Page_Insights_Background constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_monsterinsights_pageinsights_fetch_data', array( $this, 'fetch_all_data' ) );

	}

	/**
	 * Fetch all the data from Relay and store it in the local cache.
	 */
	public function fetch_all_data() {

		// Don't lock up other requests while processing this because it's meant to run async.
		session_write_close();

		check_ajax_referer( 'monsterinsights_pageinsights_fetch', 'nonce' );

		if ( ! MonsterInsights()->license->license_can( 'plus' ) ) {
			wp_die( 'license' );
		}

		$site_auth = MonsterInsights()->auth->get_viewname();
		$ms_auth   = is_multisite() && MonsterInsights()->auth->get_network_viewname();

		if ( empty( $site_auth ) && empty( $ms_auth ) ) {
			wp_die();
		}

		$api_options = array();
		if ( ! $site_auth && $ms_auth ) {
			$api_options['network'] = true;
		}

		$api = new MonsterInsights_API_Request( 'analytics/reports/pageinsights', $api_options, 'GET' );

		$report_data = $api->request();

		if ( is_wp_error( $report_data ) ) {
			// Failed pulling report data.
			wp_die();
		} else {
			if ( ! isset( $report_data['data']['pageinsights'] ) ) {
				// Prevent updating the next fetch date if the report reply is not correct.
				wp_die();
			}

			foreach ( $report_data['data']['pageinsights'] as $page_data ) {
				MonsterInsights_Page_Insights_Cache::get_instance()->set( $page_data['page_path'], $page_data );
			}

			// Save the time when the next bulk fetch should be triggered.
			$expiration = strtotime( ' Tomorrow 1am ' ) - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
			update_option( 'monsterinsights_pageinsights_next_fetch', $expiration );
		}

		wp_die(); // No need to output anything as it's meant for a non-blocking request.
	}

	/**
	 * Initialize a fetch in a non-blocking request.
	 *
	 * @return bool
	 */
	public static function start_fetch() {

		if ( ! self::should_fetch() ) {
			return false;
		}

		MonsterInsights_Page_Insights_Cache::get_instance()->clear_cache();

		wp_remote_post( admin_url( 'admin-ajax.php' ), array(
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			'blocking'  => false,
			'timeout'   => 0.1,
			'body'      => array(
				'action' => 'monsterinsights_pageinsights_fetch_data',
				'nonce'  => wp_create_nonce( 'monsterinsights_pageinsights_fetch' ),
			),
			'cookies'   => $_COOKIE,
		) );

		return true;
	}

	/**
	 * If we should fetch all the data again.
	 *
	 * @return bool
	 */
	public static function should_fetch() {

		$next_fetch = get_option( 'monsterinsights_pageinsights_next_fetch' );

		return $next_fetch < time();

	}

}

new MonsterInsights_Page_Insights_Background();
