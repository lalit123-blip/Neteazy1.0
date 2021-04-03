<?php
namespace Leadin;

/**
 * Class containing all the custom filters defined to be used instead of constants.
 */
class LeadinFilters {
	/**
	 * Apply leadin_env filter.
	 */
	public static function get_leadin_env() {
		return apply_filters( 'leadin_env', 'prod' );
	}

	/**
	 * Apply leadin_base_url filter.
	 */
	public static function get_leadin_base_url() {
		return apply_filters( 'leadin_base_url', 'https://app.hubspot.com' );
	}

	/**
	 * Apply filter to get the base url for the HubSpot api.
	 */
	public static function get_leadin_base_api_url() {
		return apply_filters( 'leadin_base_api_url', 'https://api.hubspot.com' );
	}

	/**
	 * Apply leadin_signup_base_url filter.
	 */
	public static function get_leadin_signup_base_url() {
		$base_url = self::get_leadin_base_url();
		return apply_filters( 'leadin_signup_base_url', $base_url );
	}

	/**
	 * Apply leadin_forms_script_url filter.
	 */
	public static function get_leadin_forms_script_url() {
		return apply_filters( 'leadin_forms_script_url', 'https://js.hsforms.net/forms/v2.js' );
	}

	/**
	 * Apply leadin_script_loader_domain filter.
	 */
	public static function get_leadin_script_loader_domain() {
		return apply_filters( 'leadin_script_loader_domain', 'js.hs-scripts.com' );
	}

	/**
	 * Apply leadin_forms_payload filter.
	 */
	public static function get_leadin_forms_payload() {
		return apply_filters( 'leadin_forms_payload', '' );
	}
}
