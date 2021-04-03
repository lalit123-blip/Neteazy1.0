<?php

namespace Leadin\rest;

use Leadin\auth\OAuth;
use Leadin\LeadinFilters;
use Leadin\admin\AdminFilters;
use Leadin\rest\HubSpotApiClient;

/**
 * Basic rest endpoint to proxy json requests to and from the HubSpot API's
 */
class LeadinRestApi {
	/**
	 * Class constructor, registering rest endpoints.
	 */
	public function __construct() {
		add_action(
			'rest_api_init',
			array( $this, 'register_routes' )
		);
	}

	/**
	 * Register all the routes for the leadin REST Api service
	 */
	public function register_routes() {
		self::register_leadin_route(
			'/proxy(?P<path>.*)',
			\WP_REST_Server::ALLMETHODS,
			array( $this, 'proxy_request' )
		);

		self::register_leadin_route(
			'/healthcheck',
			\WP_REST_Server::READABLE,
			array( $this, 'healthcheck_request' )
		);
	}

	/**
	 * Register a route with given parameters
	 *
	 * @param string $path The path for the route to register the service on. Route gets namespaced with leadin/v1.
	 * @param string $methods Comma seperated list of methods allowed for this route.
	 * @param array  $callback Method to execute when this endpoint is requested.
	 */
	public function register_leadin_route( $path, $methods, $callback ) {
		register_rest_route(
			'leadin/v1',
			$path,
			array(
				'methods'             => $methods,
				'callback'            => $callback,
				'permission_callback' => array( $this, 'verify_permissions' ),
			)
		);
	}

	/**
	 * Proxy the request from the frontend to the HubSpot api's User is authenticated via nonce
	 * and permissions are checked in the proxy_permissions callback.
	 *
	 * @param array $request Request to proxy forward.
	 *
	 * @return \WP_REST_Response Response object to return from this endpoint.
	 */
	public function proxy_request( $request ) {
		$api_path = $request->get_params()['path'];

		try {
			$proxy_request = HubSpotApiClient::authenticated_request( $api_path, $request->get_method(), $request->get_body() );
		} catch ( \Exception $e ) {
			return new \WP_REST_Response( json_decode( $e->getMessage() ), $e->getCode() );
		}

		$response_code = wp_remote_retrieve_response_code( $proxy_request );
		$response_body = wp_remote_retrieve_body( $proxy_request );

		return new \WP_REST_Response( json_decode( $response_body ), $response_code );
	}

	/**
	 * Callback for healtcheck endpoint.
	 *
	 * @return string OK response message.
	 */
	public function healthcheck_request() {
		return 'OK';
	}

	/**
	 * Permissions required by user to execute the request. User permissions are already
	 * verified by nonce 'wp_rest' automatically.
	 *
	 * @return bool true if the user has adequate permissions for this proxy endpoint.
	 */
	public function verify_permissions() {
		return current_user_can( AdminFilters::apply_view_plugin_menu_capability() );
	}

}
