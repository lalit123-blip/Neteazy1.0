<?php

namespace Leadin;

use \Datetime;

/**
 * Class that wraps the functions to access the Leadin's options on the db.
 */
class LeadinOptions {
	const LEADIN_NAMESPACE = 'leadin_';

	// Deprecated, lets keep this class solely responsible for being an interface to the options table.
	const ACQUISITION_ATTRIBUTION = 'hubspot_acquisition_attribution';
	const AFFILIATE_CODE          = 'hubspot_affiliate_code';
	const WPE_TEMPLATE            = 'wpe_template';

	/**
	 * Prefixes a given string with 'leadin_' to namespace any options we store in the WordPress options DB.
	 *
	 * @param String $key Option key to prefix.
	 */
	private static function get_namespaced_key( $key ) {
		return self::LEADIN_NAMESPACE . $key;
	}

	/**
	 * Wrapper of WordPress' get_option function. Namespaces option key before inserting.
	 *
	 * @param String $option_key Option name.
	 */
	public static function get( $option_key ) {
		$prefixed_key = self::get_namespaced_key( $option_key );
		return get_option( $prefixed_key );
	}

	/**
	 * Wrapper of WordPress' add_option function. Namespaces give option key before adding.
	 *
	 * @param String $option_key Name of the option to add to the DB.
	 * @param String $value Value to store in the option.
	 */
	public static function add( $option_key, $value ) {
		$prefixed_key = self::get_namespaced_key( $option_key );
		return add_option( $prefixed_key, $value );
	}

	/**
	 * Wrapper of WordPress update_option function. Namespaces given option keys before updating.
	 *
	 * @param String $option_key Name of the option to update in the DB.
	 * @param String $value Value to update the option with.
	 */
	public static function update( $option_key, $value ) {
		$prefixed_key = self::get_namespaced_key( $option_key );
		return update_option( $prefixed_key, $value );
	}

	/**
	 * Wrapper of WordPress' delete_option function. Namespaces given option key before deleting.
	 *
	 * @param String $option_key Name of the option to delete from the DB.
	 */
	public static function delete( $option_key ) {
		$prefixed_key = self::get_namespaced_key( $option_key );
		return delete_option( $prefixed_key );
	}

	/**
	 * Get acquisition attribution
	 */
	public static function get_acquisition_attribution() {
		return get_option( self::ACQUISITION_ATTRIBUTION );
	}

	/**
	 * Return affiliate code
	 */
	public static function get_affiliate_code() {
		$affiliate_code_option = trim( get_option( self::AFFILIATE_CODE ) );
		preg_match( '/(?:(?:hubs\.to)|(?:mbsy\.co))\/([a-zA-Z0-9]+)/', $affiliate_code_option, $matches );

		if ( count( $matches ) === 2 ) {
			$affiliate_link = $matches[1];
		} else {
			$affiliate_link = $affiliate_code_option;
		}

		return $affiliate_link;
	}

	/**
	 * Return WPEngine template
	 */
	public static function get_wpe_template() {
		return get_option( self::WPE_TEMPLATE );
	}
}
