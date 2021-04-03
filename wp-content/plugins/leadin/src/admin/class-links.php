<?php

namespace Leadin\admin;

use Leadin\LeadinFilters;
use Leadin\LeadinOptions;
use Leadin\admin\AdminFilters;
use Leadin\admin\MenuConstants;
use Leadin\admin\utils\Background;
use Leadin\wp\User;
use Leadin\utils\QueryParameters;
use Leadin\utils\Versions;
use Leadin\auth\OAuth;
use Leadin\admin\Connection;
use Leadin\admin\AdminConstants;
use Leadin\includes\utils as utils;

/**
 * Class containing all the functions to generate links to HubSpot.
 */
class Links {
	/**
	 * Deprecated for OAuth2 routes
	 *
	 * Get a map of <admin_page, url>
	 * Where
	 * - admin_page is a string
	 * - url is either a string or another map <route, string_url>, both strings
	 */
	public static function get_routes_mapping() {
		$portal_id      = Connection::get_portal_id();
		$reporting_page = "/wordpress-plugin-ui/$portal_id/reporting";
		$user_guide     = "/wordpress-plugin-ui/$portal_id/onboarding/start";

		return array(
			MenuConstants::ROOT       => $user_guide,
			MenuConstants::USER_GUIDE => $user_guide,
			MenuConstants::REPORTING  => $reporting_page,
			MenuConstants::CHATFLOWS  => array(
				''         => "/chatflows/$portal_id",
				'settings' => "/live-messages-settings/$portal_id",
			),
			MenuConstants::CONTACTS   => "/contacts/$portal_id",
			MenuConstants::LISTS      => "/contacts/$portal_id/lists",
			MenuConstants::FORMS      => "/forms/$portal_id",
			MenuConstants::EMAIL      => array(
				''    => "/email/$portal_id",
				'cms' => "/content/$portal_id/create/email",
			),
			MenuConstants::SETTINGS   => array(
				''      => "/wordpress-plugin-ui/$portal_id/settings",
				'forms' => "/settings/$portal_id/marketing/form",
			),
			MenuConstants::PRICING    => "/pricing/$portal_id/marketing",
		);
	}

	/**
	 * Get page name from the current page id.
	 * E.g. "hubspot_page_leadin_forms" => "forms"
	 */
	private static function get_page_id() {
		$screen_id = get_current_screen()->id;
		return preg_replace( '/^(hubspot_page_|toplevel_page_)/', '', $screen_id );
	}

	/**
	 * Get the parsed `leadin_route` from the query string.
	 */
	private static function get_iframe_route() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$iframe_route = isset( $_GET['leadin_route'] ) ? wp_unslash( $_GET['leadin_route'] ) : array();
		return is_array( $iframe_route ) ? $iframe_route : array();
	}

	/**
	 * Get the parsed `leadin_search` from the query string.
	 */
	private static function get_iframe_search_string() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET['leadin_search'] ) ? esc_url_raw( wp_unslash( '&' . $_GET['leadin_search'] ) ) : '';
	}

	/**
	 * Return query string from object
	 *
	 * @param array $arr query parameters to stringify.
	 */
	private static function http_build_query( $arr ) {
		if ( ! is_array( $arr ) ) {
			return '';
		}
		return http_build_query( $arr, null, ini_get( 'arg_separator.output' ), PHP_QUERY_RFC3986 );
	}

	/**
	 * Validate static version.
	 *
	 * @param String $version version of the static bundle.
	 */
	private static function is_static_version_valid( $version ) {
		preg_match( '/static-\d+\.\d+/', $version, $match );
		return ! empty( $match );
	}

	/**
	 * Return utm_campaign to add to the signup link.
	 */
	private static function get_utm_campaign() {
		$wpe_template = LeadinOptions::get_wpe_template();
		if ( 'hubspot' === $wpe_template ) {
			return 'wp-engine-site-template';
		}
	}

	/**
	 * Return a string query parameters to add to the iframe src.
	 */
	public static function get_query_params() {
		$config_array = AdminConstants::get_hubspot_config();

		return self::http_build_query( $config_array );
	}

	/**
	 * Return an array with the user's pre-fill info for signup
	 */
	public static function get_signup_prefill_params_array() {
		$wp_user   = wp_get_current_user();
		$user_info = array(
			'firstName' => $wp_user->user_firstname,
			'lastName'  => $wp_user->user_lastname,
			'email'     => $wp_user->user_email,
			'company'   => get_bloginfo( 'name' ),
			'domain'    => parse_url( get_site_url(), PHP_URL_HOST ),
			'show_nav'  => 'true',
			'wp_user'   => $wp_user->first_name ? $wp_user->first_name : $wp_user->user_nicename,
		);
		if ( function_exists( 'get_avatar_url' ) ) {
			$user_info['wp_gravatar'] = get_avatar_url( $wp_user->ID );
		}

		return $user_info;
	}

	/**
	 * Return an array with the utm parameters for signup
	 */
	public static function get_utm_query_params_array() {
		$utm_params = array(
			'utm_source' => 'wordpress-plugin',
			'utm_medium' => 'marketplaces',
		);

		$utm_campaign = self::get_utm_campaign();
		if ( ! empty( $utm_campaign ) ) {
			$utm_params['utm_campaign'] = $utm_campaign;
		}
		return $utm_params;
	}

	/**
	 * Return an array of properties to be included in the signup search string
	 */
	public static function get_signup_query_param_array() {
		// Get attribution string.
		$acquisition_option = LeadinOptions::get_acquisition_attribution();
		parse_str( $acquisition_option, $signup_params );
		$signup_params['enableCollectedForms'] = 'true';

		if ( ! OAuth::is_enabled() ) {
			$redirect_page                    = Connection::get_portal_id() ? 'leadin_settings' : 'leadin';
			$signup_params['wp_redirect_url'] = admin_url( "admin.php?page=$redirect_page" );
		} else {
			$signup_params['oauthEnabled'] = true;
		}

		$user_prefill_params = self::get_signup_prefill_params_array();
		$leadin_params       = AdminConstants::get_hubspot_config();
		$signup_params       = array_merge( $signup_params, $leadin_params, $user_prefill_params );

		return $signup_params;
	}

	/**
	 * Return the signup url based on the site options.
	 */
	public static function get_signup_url() {
		$affiliate_code = AdminFilters::apply_affiliate_code();
		$signup_url     = LeadinFilters::get_leadin_signup_base_url() . '/signup/wordpress?';

		$query_param_array = self::get_signup_query_param_array();

		if ( $affiliate_code ) {
			$signup_url     .= self::http_build_query( $query_param_array );
			$destination_url = rawurlencode( $signup_url );
			return "https://mbsy.co/$affiliate_code?url=$destination_url";
		}

		$utm_params    = self::get_utm_query_params_array();
		$signup_params = array_merge( $query_param_array, $utm_params );

		return $signup_url . self::http_build_query( $signup_params );
	}

	/**
	 * Get background iframe src.
	 */
	public static function get_background_iframe_src() {
		$portal_id     = Connection::get_portal_id();
		$portal_id_url = '';

		if ( Connection::is_connected() ) {
			$portal_id_url = "/$portal_id";
		}

		$query = '';

		return LeadinFilters::get_leadin_base_url() . "/wordpress-plugin-ui$portal_id_url/background?$query" . self::get_query_params();
	}

	/**
	 * Return login link to redirect to when the user isn't authenticated in HubSpot
	 */
	public static function get_login_url() {
		$portal_id = Connection::get_portal_id();
		return LeadinFilters::get_leadin_base_url() . "/wordpress-plugin-ui/$portal_id/login?" . self::get_query_params();
	}

	/**
	 * Returns the url for the connection page
	 */
	private static function get_connection_src() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$portal_id = filter_var( wp_unslash( $_GET['leadin_connect'] ), FILTER_VALIDATE_INT );
		return LeadinFilters::get_leadin_base_url() . "/wordpress-plugin-ui/onboarding/connect?portalId=$portal_id&" . self::get_query_params();
	}

	/**
	 * Deprecated for OAuth2 flows
	 *
	 * Returns the right iframe src.
	 *
	 * The `page` query param is used as a key to get the url from the get_routes_mapping
	 * The `leadin_route[]` query params are added to the url
	 *
	 * e.g.:
	 * ?page=leadin_forms&leadin_route[]=foo&leadin_route[]=bar will redirect to /forms/$portal_id/foo/bar
	 *
	 * If the value of get_routes_mapping is an array, the first value of `leadin_route` will be used as key.
	 * If the key isn't found, it will fall back to ''
	 *
	 * e.g.:
	 * ?page=leadin_settings&leadin=route[]=forms&leadin_route[]=bar will redirect to /settings/$portal_id/forms/bar
	 * ?page=leadin_settings&leadin=route[]=foo&leadin_route[]=bar will redirect to /wordpress_plugin_ui/$portal_id/settings/foo/bar
	 */
	public static function deprecated_get_iframe_src() {
		$leadin_onboarding     = 'leadin_onboarding';
		$leadin_new_portal     = 'leadin_new_portal';
		$browser_search_string = '';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['leadin_connect'] ) ) {
			$extra = '';
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['is_new_portal'] ) ) {
				$extra = '&isNewPortal=true';
				set_transient( $leadin_new_portal, 'true' );
			}
			return self::get_connection_src() . $extra;
		}

		if ( get_transient( $leadin_onboarding ) ) {
			delete_transient( $leadin_onboarding );
			$browser_search_string = '&justConnected=true';
			if ( get_transient( $leadin_new_portal ) ) {
				delete_transient( $leadin_new_portal );
				$browser_search_string = $browser_search_string . '&isNewPortal=true';
			}
		}

		$sub_routes_array      = self::get_iframe_route();
		$inframe_search_string = self::get_iframe_search_string();
		$browser_search_string = $browser_search_string . $inframe_search_string;

		if ( empty( Connection::get_portal_id() ) ) {
			$wp_user    = wp_get_current_user();
			$wp_user_id = $wp_user->ID;
			set_transient( $leadin_onboarding, 'true' );
			$route = '/wordpress-plugin-ui/onboarding';
		} else {
			$page_id = self::get_page_id();
			$routes  = self::get_routes_mapping();

			if ( isset( $routes[ $page_id ] ) ) {
				$route = $routes[ $page_id ];

				if ( \is_array( $route ) && isset( $sub_routes_array[0] ) ) {
					$first_sub_route = $sub_routes_array[0];

					if ( isset( $route[ $first_sub_route ] ) ) {
						$route = $route[ $first_sub_route ];
						array_shift( $sub_routes_array );
					}
				}

				if ( \is_array( $route ) ) {
					$route = $route[''];
				}
			} else {
				$route = '';
			}
		}

		$sub_routes = join( '/', $sub_routes_array );
		$sub_routes = empty( $sub_routes ) ? $sub_routes : "/$sub_routes";
		// Query string separator "?" may have been added to the URL already.
		$add_separator = strpos( $sub_routes, '?' ) ? '&' : '?';

		return LeadinFilters::get_leadin_base_url() . "$route$sub_routes" . $add_separator . self::get_query_params() . $browser_search_string;
	}

	/**
	 * Get wordpress-plugin-ui iframe src URL
	 */
	public static function get_iframe_src() {
		if ( OAuth::is_enabled() ) {
			$path          = IframeRoutes::get_oauth_path();
			$config_params = AdminConstants::get_hubspot_config();

			$url            = LeadinFilters::get_leadin_base_url() . '/wordpress-plugin-ui' . "$path";
			$encoded_params = urlencode_deep( $config_params );
			$url            = add_query_arg( $encoded_params, $url );

			return $url;
		} else {
			return self::deprecated_get_iframe_src();
		}
	}
}
