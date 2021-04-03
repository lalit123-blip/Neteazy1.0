<?php

namespace Leadin\admin;

use Leadin\utils\QueryParameters;

/**
 * Class for helping route around the plugin in OAuth mode.
 */
class OAuthRouting {

	const EXPIRED        = 'leadin_expired';
	const JUST_CONNECTED = 'leadin_just_connected';
	const REDIRECT_NONCE = 'leadin_redirect';

	/**
	 * Redirect to the root of the leadin plugin with optional query parameters.
	 * Verified with a redirect nonce.
	 *
	 * @param array $extra_params Associative array of parameters to add to the redirected URL.
	 */
	public static function root_redirect( $extra_params = array() ) {
		$redirect_params = array_merge(
			array( 'page' => 'leadin' ),
			array( self::REDIRECT_NONCE => wp_create_nonce( self::REDIRECT_NONCE ) ),
			$extra_params
		);

		$redirect_url = add_query_arg(
			urlencode_deep( $redirect_params ),
			admin_url( 'admin.php' )
		);

		nocache_headers();
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Return a boolean if the plugin has just been connected.
	 * Signified by query parameter flag `leadin_just_connected`.
	 *
	 * @return bool True if the plugin has just connected.
	 */
	public static function has_just_connected() {
		$just_connected_param = QueryParameters::get_param(
			self::JUST_CONNECTED,
			self::REDIRECT_NONCE,
			self::REDIRECT_NONCE
		);

		return null !== $just_connected_param;
	}

	/**
	 * Reads query parameter from the frontend to report that the OAuth access token
	 * is expired/malformed. Used to determine if we need to ask the user to re-authorise.
	 *
	 * @return bool True if the `leadin_expired` query parameter is set from the frontend
	 */
	public static function is_expired() {
		$is_expired = QueryParameters::get_param(
			self::EXPIRED,
			self::REDIRECT_NONCE,
			self::EXPIRED
		);

		return null !== $is_expired;
	}
}
