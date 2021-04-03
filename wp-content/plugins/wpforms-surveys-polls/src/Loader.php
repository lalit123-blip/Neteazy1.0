<?php

namespace WPFormsSurveys;

/**
 * WPForms Surveys and Polls loader class.
 *
 * @package    WPFormsSurveys
 * @author     WPForms
 * @since      1.0.0
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2018, WPForms LLC
 */
final class Loader {

	/**
	 * Have the only available instance of the class.
	 *
	 * @var Loader
	 *
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * URL to a plugin directory. Used for assets.
	 *
	 * @var string
	 *
	 * @since 1.0.0
	 */
	public $url = '';

	/**
	 * Initiate main plugin instance.
	 *
	 * @since 1.0.0
	 *
	 * @return Loader
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Loader ) ) {
			self::$instance = new Loader();
		}

		return self::$instance;
	}

	/**
	 * Loader constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->url = plugin_dir_url( __DIR__ );

		add_action( 'wpforms_loaded', array( $this, 'init' ) );
	}

	/**
	 * All the actual plugin loading is done here.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// WPForms Pro is required.
		if ( ! class_exists( 'WPForms_Pro', false ) ) {
			return;
		}

		// Load translated strings.
		load_plugin_textdomain( 'wpforms-surveys-polls', false, dirname( plugin_basename( WPFORMS_SURVEYS_POLLS_FILE ) ) . '/languages/' );

		new Reporting\Ajax();
		new Fields\LikertScale();
		new Fields\NetPromoterScore();
		new Polls();

		add_action( 'admin_init', function() {
			new Admin();
			new Reporting\Admin();
			new Templates\Poll();
			new Templates\Survey();
			new Templates\NPSSurveySimple();
			new Templates\NPSSurveyEnhanced();
		} );

		// Register the updater of this plugin.
		$this->updater();
	}

	/**
	 * Load the plugin updater.
	 *
	 * @since 1.0.0
	 */
	private function updater() {

		$url = $this->url;

		add_action( 'wpforms_updater', function( $key ) use ( $url ) {

			new \WPForms_Updater(
				array(
					'plugin_name' => 'WPForms Surveys and Polls',
					'plugin_slug' => 'wpforms-surveys-polls',
					'plugin_path' => plugin_basename( WPFORMS_SURVEYS_POLLS_FILE ),
					'plugin_url'  => trailingslashit( $url ),
					'remote_url'  => WPFORMS_UPDATER_API,
					'version'     => WPFORMS_SURVEYS_POLLS_VERSION,
					'key'         => $key,
				)
			);
		} );
	}
}
