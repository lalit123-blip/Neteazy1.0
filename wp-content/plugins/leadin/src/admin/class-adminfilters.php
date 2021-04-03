<?php
namespace Leadin\admin;

use Leadin\LeadinOptions;

const LEADIN_AFFILIATE_CODE = 'leadin_affiliate_code';

/**
 * Class containing all the filters used for the admin side of the plugin.
 */
class AdminFilters {
	/**
	 * Class constructor, adds the necessary filters.
	 */
	public function __construct() {
		add_filter( LEADIN_AFFILIATE_CODE, array( $this, 'get_affiliate_code' ), 100 );
	}

	/**
	 * If no filter was defined, try to get the affiliate code from the options.
	 *
	 * @param String $affiliate Affiliate code returned by previous filter.
	 */
	public function get_affiliate_code( $affiliate ) {
		return empty( $affiliate ) ? LeadinOptions::get_affiliate_code() : $affiliate;
	}

	/**
	 * Apply leadin_affiliate_code filter.
	 */
	public static function apply_affiliate_code() {
		return apply_filters( LEADIN_AFFILIATE_CODE, null );
	}

	/**
	 * Apply leadin_view_plugin_menu_capability filter.
	 */
	public static function apply_view_plugin_menu_capability() {
		return apply_filters( 'leadin_view_plugin_menu_capability', 'edit_posts' );
	}

	/**
	 * Apply leadin_connect_plugin_capability filter.
	 */
	public static function apply_connect_plugin_capability() {
		return apply_filters( 'leadin_connect_plugin_capability', 'manage_options' );
	}
}
