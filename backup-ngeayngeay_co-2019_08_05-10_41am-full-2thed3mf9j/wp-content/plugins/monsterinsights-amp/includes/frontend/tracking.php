<?php
function monsterinsights_amp_rest_add_userid( $amp_template ) {
	?>
	<meta name="amp-google-client-id-api" content="googleanalytics">
	<meta name="monsterinsights-version" content="<?php echo esc_attr( MONSTERINSIGHTS_VERSION ); ?>">
	<meta name="monsterinsights-amp-version" content="<?php echo esc_attr( MONSTERINSIGHTS_AMP_VERSION );?>">
	<?php
	$track = function_exists( 'monsterinsights_track_user' ) ? monsterinsights_track_user() : ! monsterinsights_disabled_user_group();
	if ( $track ) { ?>
		<meta name="monsterinsights-tracking-user" content="true">
	<?php } else { ?>
		<meta name="monsterinsights-tracking-user" content="false">
	<?php }
}
add_action( 'amp_post_template_head', 'monsterinsights_amp_rest_add_userid', 12 );


function monsterinsights_amp_rest_add_userid_native() {
    if ( ! function_exists( 'amp_is_canonical' ) || ! amp_is_canonical() ) {
        return;
    }
	?>
	<meta name="amp-google-client-id-api" content="googleanalytics">
	<meta name="monsterinsights-version" content="<?php echo esc_attr( MONSTERINSIGHTS_VERSION ); ?>">
	<meta name="monsterinsights-amp-version" content="<?php echo esc_attr( MONSTERINSIGHTS_AMP_VERSION );?>">
	<?php
	$track = function_exists( 'monsterinsights_track_user' ) ? monsterinsights_track_user() : ! monsterinsights_disabled_user_group();
	if ( $track ) { ?>
		<meta name="monsterinsights-tracking-user" content="true">
	<?php } else { ?>
		<meta name="monsterinsights-tracking-user" content="false">
	<?php }
}
add_action( 'wp_head', 'monsterinsights_amp_rest_add_userid_native' );


function monsterinsights_not_tracking_amp() {
	$track = function_exists( 'monsterinsights_track_user' ) ? monsterinsights_track_user() : ! monsterinsights_disabled_user_group();
	if ( ! $track ) {
		echo '<!-- Note: MonsterInsights is not tracking this page as you are either a logged in administrator or a disabled user group. -->';
	}
}
add_filter( 'amp_post_template_footer', 'monsterinsights_not_tracking_amp' );

function monsterinsights_not_tracking_amp_native() {
    if ( ! function_exists( 'amp_is_canonical' ) || ! amp_is_canonical() ) {
        return;
    }
	$track = function_exists( 'monsterinsights_track_user' ) ? monsterinsights_track_user() : ! monsterinsights_disabled_user_group();
	if ( ! $track ) {
		echo '<!-- Note: MonsterInsights is not tracking this page as you are either a logged in administrator or a disabled user group. -->';
	}
}
add_action( 'wp_footer', 'monsterinsights_not_tracking_amp_native', 9 );

function monsterinsights_amp_add_analytics( $analytics ) {
	// if Yoast is outputting analytics
	if ( isset( $analytics['yst-googleanalytics'] ) ) {
		return $analytics;
	}

	$track = function_exists( 'monsterinsights_track_user' ) ? monsterinsights_track_user() : ! monsterinsights_disabled_user_group();
	if ( ! $track ) {
		return $analytics;
	}

	// if there's no UA code set
	$ua = monsterinsights_get_ua_to_output( array( 'amp' => true ) );
	if ( empty( $ua ) ) {
		return $analytics;
	}
	$site_url = str_replace( array( 'http:', 'https:'), '',  site_url() );
	$analytics['monsterinsights-googleanalytics'] = array(
		'type' => 'googleanalytics',
		'attributes'  => array(),
		'config_data' => array(
			'vars' => array(
				'account' => $ua,
			),
			'triggers' => array(
				'trackPageview' => array(
					'on'      => 'visible',
					'request' => 'pageview',
				),
			),
		),
	);

	// Dimensions Addon Integration
	// First, let's get dimensions by pulling them out of the normal frontend output
	if ( class_exists( 'MonsterInsights_Frontend_Custom_Dimensions' ) ) {
		$options = new MonsterInsights_Frontend_Custom_Dimensions();
		$options = $options->output_custom_dimensions( array () );
		$has_dim = false;
		if ( ! empty( $options ) && is_array( $options ) ) {
			foreach ( $options as $optionname => $optionvalue ) {
				if ( monsterinsights_string_starts_with( $optionname, 'dimension' ) ) {
					$has_dim       = true;
					$num           = str_replace( 'dimension', '',  $optionname );
					$dimensionname = str_replace( 'dimension', 'cd',  $optionname );
					$optionvalue   = str_replace("'set', 'dimension" . absint( $num ) . "', '", '', $optionvalue );
					$optionvalue   = rtrim( $optionvalue,"'" );
					$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['trackPageview']['vars'][$dimensionname] = $optionvalue;
					if ( isset( $analytics['monsterinsights-googleanalytics']['config_data']['requests'] ) ) {
						$analytics['monsterinsights-googleanalytics']['config_data']['requests']['pageviewWithCDs'] = $analytics['monsterinsights-googleanalytics']['config_data']['requests']['pageviewWithCDs'] . '&cd' . $num . '=${cd' . $num . '}';
					} else {
						$analytics['monsterinsights-googleanalytics']['config_data']['requests']['pageviewWithCDs'] = '${pageview}' . '&cd' . $num . '=${cd' . $num . '}';
					}
				}
			}
		}

		if ( $has_dim ){
			$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['trackPageview']['request'] = 'pageviewWithCDs';
		}
	}



	// Todo: track all below as a single item
	// Track Downloads
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['downloadLinks']['on'] = 'click';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['downloadLinks']['selector'] = 'a, .monsterinsights-download';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['downloadLinks']['request'] = 'event';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['downloadLinks']['vars'] = array(
		'eventCategory' => '${category}',
		'eventAction'   => '${action}',
		'eventLabel'    => '${label}',
	);

	// Track Internal as Outbound
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['internalAsOutboundLinks']['on'] = 'click';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['internalAsOutboundLinks']['selector'] = 'a, .monsterinsights-internal-as-outbound';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['internalAsOutboundLinks']['request'] = 'event';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['internalAsOutboundLinks']['vars'] = array(
		'eventCategory' => '${category}',
		'eventAction'   => '${action}',
		'eventLabel'    => '${label}',
	);

	// Track External
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['outboundLinks']['on'] = 'click';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['outboundLinks']['selector'] = 'a, .monsterinsights-outbound-link';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['outboundLinks']['request'] = 'event';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['outboundLinks']['vars'] = array(
		'eventCategory' => '${category}',
		'eventAction'   => '${action}',
		'eventLabel'    => '${label}',
	);

	// Track Tel
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['telLinks']['on'] = 'click';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['telLinks']['selector'] = 'a, .monsterinsights-tel';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['telLinks']['request'] = 'event';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['telLinks']['vars'] = array(
		'eventCategory' => '${category}',
		'eventAction'   => '${action}',
		'eventLabel'    => '${label}',
	);

	// Track Mailto
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['mailtoLinks']['on'] = 'click';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['mailtoLinks']['selector'] = 'a, .monsterinsights-mailto';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['mailtoLinks']['request'] = 'event';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['mailtoLinks']['vars'] = array(
		'eventCategory' => '${category}',
		'eventAction'   => '${action}',
		'eventLabel'    => '${label}',
	);

	// Track Custom Links
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['customLinks']['on'] = 'click';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['customLinks']['selector'] = 'a, .monsterinsights-link';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['customLinks']['request'] = 'event';
	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['customLinks']['vars'] = array(
		'eventCategory' => '${category}',
		'eventAction'   => '${action}',
		'eventLabel'    => '${label}',
	);

	$samplerate = monsterinsights_get_option( 'samplerate', 100 );
	// If performance addon turned on sample our event
	if ( (int ) $samplerate > 0 && (int) $samplerate < 100 ) {
		// Set ours to sample
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['trackPageview']['sampleSpec']['sampleOn'] = '${clientId}';
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['trackPageview']['sampleSpec']['threshold'] = (int) $samplerate;
	}

	$samplerate = monsterinsights_get_option( 'speedsamplerate', 1 );
	// If performance addon turned on sample Google's pagespeed event
	if ( (int) $samplerate > 0 && (int) $samplerate < 100 && (int) $samplerate !== 1 ) {
		// Set Google's to sample
		$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['performanceTiming']['sampleSpec']['threshold'] = (int) $samplerate;
	}

	// Disabled Form Tracking
	// if ( defined( 'MONSTERINSIGHTS_FORMS_VERSION' ) ) {
	// 	// Track Form Impressions
	// 	// Note: Unfortunately AMP doesn't support non-interaction devents, so the impressions are going to affect this
	// 	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['formImpressions']['on'] = 'visible';
	// 	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['formImpressions']['visibilitySpec']['selector'] = 'amp-state, .monsterinsights-form';
	// 	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['formImpressions']['request'] = 'event';
	// 	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['formImpressions']['vars'] = array(
	// 		'eventCategory' => '${category}',
	// 		'eventAction'   => '${action}',
	// 		'eventLabel'    => '${label}',
	// 	);

	// 	// Track Form Conversions
	// 	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['formConversions']['on'] = 'amp-form-submit-success';
	// 	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['formConversions']['selector'] = 'amp-state, .monsterinsights-track-form-conversions';
	// 	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['formConversions']['request'] = 'event';
	// 	$analytics['monsterinsights-googleanalytics']['config_data']['triggers']['formConversions']['vars'] = array(
	// 	 	'eventCategory' => 'form',
	// 	 	'eventAction'   => 'conversion',
	// 	 	'eventLabel'    => '${formId}',
	// 	);
	// }

	// Todo: Future: Optin: https://github.com/ampproject/amphtml/blob/master/extensions/amp-user-notification/amp-user-notification.md or
	// https://www.ampproject.org/docs/reference/components/amp-analytics 'data-consent-notification-id'
	// Todo: Clarification on tracking of internal links
	$analytics = apply_filters( 'monsterinsights_amp_add_analytics', $analytics );
	return $analytics;
}
add_filter( 'amp_post_template_analytics', 'monsterinsights_amp_add_analytics' );
add_filter( 'amp_analytics_entries', 'monsterinsights_amp_add_analytics' );

function monsterinsights_amp_rename_config_data( $analytics ) {
	$analytics['monsterinsights-googleanalytics']['config'] = $analytics['monsterinsights-googleanalytics']['config_data'];
	unset( $analytics['monsterinsights-googleanalytics']['config_data'] );
	$analytics['monsterinsights-googleanalytics']['config'] = wp_json_encode($analytics['monsterinsights-googleanalytics']['config'] );
	return $analytics;
}
add_filter( 'monsterinsights_amp_add_analytics', 'monsterinsights_amp_rename_config_data' );

/**
 * Add our own sanitizer to the array of sanitizers
 *
 * @param array $sanitizers
 *
 * @return array
 */
function monsterinsights_amp_add_sanitizer( $sanitizers ) {
	require_once 'link-parser.php';
	$sanitizers['MonsterInsights_AMP_Link_Parser'] = array();

	// if ( defined( 'MONSTERINSIGHTS_FORMS_VERSION' ) ) {
	// 	require_once 'form-parser.php';
	// 	$sanitizers['MonsterInsights_AMP_Form_Parser'] = array();
	// }

	return $sanitizers;
}
add_filter( 'amp_content_sanitizers', 'monsterinsights_amp_add_sanitizer' );

// If Yoast SEO Glue is active, turn off our integration hosted in their plugin and use
// the more advanced one from this addon.
remove_class_filter( 'amp_post_template_analytics', 'YoastSEO_AMP_Frontend', 'analytics' );

// Remove the submenu page so users do not get confused as much.
function monsterinsights_amp_remove_analytics_submenu() {
	if ( class_exists( 'AMP_Options_Manager' ) ) {
		remove_submenu_page( AMP_Options_Manager::OPTION_NAME, 'amp-analytics-options' );
	}
}
add_action( 'admin_menu', 'monsterinsights_amp_remove_analytics_submenu', 999 );

function monsterinsights_amp_remove_analytics_code() {
    if ( ! function_exists( 'is_amp_endpoint' ) || ! is_amp_endpoint() ) {
        return;
    }

    // Core
	remove_action( 'wp_head', 'monsterinsights_tracking_script', 6 );
	remove_action( 'template_redirect', 'monsterinsights_events_tracking', 6 );

	// Ads
	remove_action( 'monsterinsights_tracking_after_analytics', 'monsterinsights_ads_output_after_script_old' );

	// Dimensions
	remove_class_filter( 'monsterinsights_frontend_tracking_options_analytics_before_pageview', 'MonsterInsights_Frontend_Custom_Dimensions', 'output_custom_dimensions' );

	// Forms
	// @todo: track form impressions
	remove_action( 'wp_head', 'monsterinsights_forms_output_after_script', 15 );

	// Admin bar scripts.
	remove_action( 'wp_enqueue_scripts', 'monsterinsights_frontend_admin_bar_scripts' );

	// Scroll tracking.
	remove_action( 'wp_footer', 'monsterinsights_scroll_tracking_output_after_script', 11 );

	// eCommerce
	remove_class_action( 'monsterinsights_load_plugins', 'MonsterInsights_eCommerce', 'init', 99 );
}
add_action( 'template_redirect', 'monsterinsights_amp_remove_analytics_code', 2 );
