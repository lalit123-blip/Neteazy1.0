<?php

namespace Leadin;

use Leadin\LeadinFilters;
use Leadin\admin\AdminConstants;
use Leadin\admin\Connection;

/**
 * Class responsible of managing all the plugin assets.
 */
class AssetsManager {
	const ADMIN_CSS     = 'leadin-css';
	const BRIDGE_CSS    = 'leadin-bridge-css';
	const ADMIN_JS      = 'leadin-js';
	const FEEDBACK_CSS  = 'leadin-feedback-css';
	const FEEDBACK_JS   = 'leadin-feedback';
	const TRACKING_CODE = 'leadin-script-loader-js';
	const GUTENBERG     = 'leadin-gutenberg';
	const FORMS_SCRIPT  = 'leadin-forms-v2';
	const LEADIN_CONFIG = 'leadinConfig';
	const LEADIN_I18N   = 'leadinI18n';

	/**
	 * Register and localize all assets.
	 */
	public static function register_assets() {
		wp_register_style( self::ADMIN_CSS, LEADIN_PATH . '/assets/style/leadin.css', array(), LEADIN_PLUGIN_VERSION );
		wp_register_script( self::ADMIN_JS, LEADIN_JS_BASE_PATH . '/leadin.js', array( 'jquery' ), LEADIN_PLUGIN_VERSION, true );
		wp_localize_script( self::ADMIN_JS, self::LEADIN_CONFIG, AdminConstants::get_leadin_config() );
		wp_localize_script( self::ADMIN_JS, self::LEADIN_I18N, AdminConstants::get_leadin_i18n() );
		wp_register_script( self::FEEDBACK_JS, LEADIN_JS_BASE_PATH . '/feedback.js', array( 'jquery', 'thickbox' ), LEADIN_PLUGIN_VERSION, true );
		wp_localize_script( self::FEEDBACK_JS, self::LEADIN_CONFIG, AdminConstants::get_background_leadin_config() );
		wp_register_style( self::FEEDBACK_CSS, LEADIN_PATH . '/assets/style/leadin-feedback.css', array(), LEADIN_PLUGIN_VERSION );
		wp_register_style( self::BRIDGE_CSS, LEADIN_PATH . '/assets/style/leadin-bridge.css?', array(), LEADIN_PLUGIN_VERSION );
	}

	/**
	 * Enqueue the assets needed in the admin section.
	 */
	public static function enqueue_admin_assets() {
		wp_enqueue_style( self::ADMIN_CSS );
	}

	/**
	 * Enqueue the assets needed to render the deactivation feedback form.
	 */
	public static function enqueue_feedback_assets() {
		wp_enqueue_style( self::FEEDBACK_CSS );
		wp_enqueue_script( self::FEEDBACK_JS );
	}

	/**
	 * Enqueue the assets needed to correctly render the plugin's iframe.
	 */
	public static function enqueue_bridge_assets() {
		wp_enqueue_style( self::BRIDGE_CSS );
		wp_enqueue_script( self::ADMIN_JS );
	}

	/**
	 * Register and enqueue the HubSpot's script loader (aka tracking code), used to collect data from your visitors.
	 * https://knowledge.hubspot.com/account/how-does-hubspot-track-visitors
	 *
	 * @param Object $leadin_wordpress_info Object used to pass to the script loader.
	 */
	public static function enqueue_script_loader( $leadin_wordpress_info ) {
		$embed_domain = LeadinFilters::get_leadin_script_loader_domain();
		$portal_id    = Connection::get_portal_id();
		$embed_url    = "https://$embed_domain/$portal_id.js?integration=WordPress";
		wp_register_script( self::TRACKING_CODE, $embed_url, array( 'jquery' ), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_localize_script( self::TRACKING_CODE, 'leadin_wordpress', $leadin_wordpress_info );
		wp_enqueue_script( self::TRACKING_CODE );
	}

	/**
	 * Register and enqueue forms script
	 */
	public static function enqueue_forms_script() {
		wp_enqueue_script( // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			self::FORMS_SCRIPT,
			LeadinFilters::get_leadin_forms_script_url(),
			array(),
			null,
			true
		);
	}

	/**
	 * Register and localize the Gutenberg scripts.
	 */
	public static function localize_gutenberg() {
		wp_register_script( self::GUTENBERG, LEADIN_JS_BASE_PATH . '/gutenberg.js', array( 'wp-blocks', 'wp-element' ), LEADIN_PLUGIN_VERSION, true );
		wp_localize_script( self::GUTENBERG, self::LEADIN_CONFIG, AdminConstants::get_background_leadin_config() );
		wp_localize_script( self::GUTENBERG, self::LEADIN_I18N, AdminConstants::get_leadin_i18n() );
	}
}
