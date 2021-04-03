<?php

namespace Leadin\admin;

/**
 * Class for building iframe routes
 */
class IframeRoutes {

	/**
	 * Get the iframe route based on plugin state
	 */
	public static function get_oauth_path() {
		if ( OAuthRouting::is_expired() ) {
			return self::get_expired_path();
		}

		$page_id = self::get_page_id();
		$routes  = self::get_iframe_routes_map();

		if ( isset( $routes[ $page_id ] ) ) {
			return $routes[ $page_id ];
		}

		return '';
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
	 * Return a mapping from PHP routes to iframe routes.
	 *
	 * @return array Associate array mapping leadin routes to iframe routes.
	 */
	private static function get_iframe_routes_map() {
		return array(
			MenuConstants::ROOT     => self::get_root_path(),
			MenuConstants::SETTINGS => self::get_settings_path(),
		);
	}

	/**
	 * Get the iframe route for the root menu option in the sidebar.
	 *
	 * @return string The Iframe route for the root menu of the plugin.
	 */
	private static function get_root_path() {
		return Connection::is_connected() ?
			self::get_base_connected_path() . '/user-guide' :
			'/onboarding';
	}

	/**
	 * Get the base path for the iframe when plugin is connected
	 */
	private static function get_base_connected_path() {
		$portal_id = Connection::get_portal_id();
		return "/$portal_id/oauth";
	}

	/**
	 * Get the iframe route for the plugin settings page.
	 */
	private static function get_settings_path() {
		return self::get_base_connected_path() . '/settings';
	}

	/**
	 * Get the iframe route for showing the session expired screen.
	 */
	private static function get_expired_path() {
		return self::get_base_connected_path() . '/expired';
	}
}
