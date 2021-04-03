<?php

namespace Leadin\admin;

use Leadin\LeadinOptions;
use Leadin\wp\User;
use Leadin\utils\QueryParameters;
use Leadin\auth\OAuth;
use Leadin\admin\Connection;

/**
 * Handles portal connection to the plugin.
 */
class Connection {

	const CONNECT_KEYS = array(
		'portal_id',
		'portal_domain',
		'access_token',
		'refresh_token',
		'expires_in',
	);

	const CONNECT_NONCE_ARG    = 'leadin_connect';
	const DISCONNECT_NONCE_ARG = 'leadin_disconnect';

	/**
	 * Returns true if a portal has been connected to the plugin
	 */
	public static function is_connected() {
		return ! empty( self::get_portal_id() );
	}

	/**
	 * Returns the connected portal id from the options table.
	 */
	public static function get_portal_id() {
		if ( OAuth::is_enabled() ) {
			return LeadinOptions::get( 'portal_id' );
		}

		return get_option( 'leadin_portalId' );
	}

	/**
	 * Returns true if the current request is for the plugin to connect to a portal
	 */
	public static function is_connection_requested() {
		$maybe_leadin_connect = QueryParameters::get_param( self::CONNECT_NONCE_ARG, 'hubspot-nonce', self::CONNECT_NONCE_ARG );
		return isset( $maybe_leadin_connect );
	}

	/**
	 * Returns true if the current request is to disconnect the plugin from the portal
	 */
	public static function is_disconnection_requested() {
		$maybe_leadin_disconnect = QueryParameters::get_param( self::DISCONNECT_NONCE_ARG, 'hubspot-nonce', self::DISCONNECT_NONCE_ARG );
		return isset( $maybe_leadin_disconnect );
	}
	/**
	 * Retrieves user ID and create new metadata
	 *
	 * @param Array $user_meta array of pairs metadata - value.
	 */
	private static function add_metadata( $user_meta ) {
		$wp_user    = wp_get_current_user();
		$wp_user_id = $wp_user->ID;
		foreach ( $user_meta as $key => $value ) {
			add_user_meta( $wp_user_id, $key, $value );
		}
	}

	/**
	 * Retrieves user ID and deletes a piece of the users meta data.
	 *
	 * @param String $meta_key is the key of the data you want to delete.
	 */
	private static function delete_metadata( $meta_key ) {
		$wp_user    = wp_get_current_user();
		$wp_user_id = $wp_user->ID;
		delete_user_meta( $wp_user_id, $meta_key );
	}

	/**
	 * Connect portal id, domain, name to WordPress options and HubSpot email to user meta data.
	 *
	 * @param Number $portal_id     HubSpot account id.
	 * @param String $portal_name   HubSpot account name.
	 * @param String $portal_domain HubSpot account domain.
	 * @param String $hs_user_email HubSpot user email.
	 */
	public static function connect( $portal_id, $portal_name, $portal_domain, $hs_user_email ) {
		self::disconnect();

		add_option( 'leadin_portalId', $portal_id );
		add_option( 'leadin_portal_domain', $portal_domain );
		add_option( 'leadin_account_name', $portal_name );

		self::add_metadata( array( 'leadin_email' => $hs_user_email ) );
	}

	/**
	 * Connect the plugin with OAuthorization. Storing OAuth tokens and metadata for the connected portal.
	 */
	public static function oauth_connect() {
		$connect_params = QueryParameters::get_parameters( self::CONNECT_KEYS, 'hubspot-nonce', self::CONNECT_NONCE_ARG );

		self::disconnect();
		self::oauth_disconnect();

		self::store_portal_info( $connect_params['portal_id'], $connect_params['portal_domain'] );
		OAuth::authorize( $connect_params['access_token'], $connect_params['refresh_token'], $connect_params['expires_in'] );
	}

	/**
	 * Removes portal id and domain from the WordPress options.
	 */
	public static function disconnect() {
		delete_option( 'leadin_portalId' );
		delete_option( 'leadin_account_name' );
		delete_option( 'leadin_portal_domain' );
		$users = get_users( array( 'fields' => array( 'ID' ) ) );
		foreach ( $users as $user ) {
			delete_user_meta( $user->ID, 'leadin_email' );
		}

		add_option( 'leadin_did_disconnect', true );
	}

	/**
	 * Cleanup database to disconnect portal from plugin
	 */
	public static function oauth_disconnect() {
		OAuth::deauthorize();
		self::delete_portal_info();
	}

	/**
	 * Store the portal metadata for connecting the plugin in the options table
	 *
	 * @param String $portal_id ID for connecting portal.
	 * @param String $portal_domain Domain for the connecting portal.
	 */
	private static function store_portal_info( $portal_id, $portal_domain ) {
		LeadinOptions::add( 'portal_id', $portal_id );
		LeadinOptions::add( 'portal_domain', $portal_domain );
	}

	/**
	 * Delete stored portal metadata for disconnecting the plugin from the options table
	 */
	private static function delete_portal_info() {
		LeadinOptions::delete( 'portal_id' );
		LeadinOptions::delete( 'portal_domain' );
	}
}
