<?php

namespace Leadin\admin;

use Leadin\AssetsManager;
use Leadin\wp\User;
use Leadin\admin\Connection;
use Leadin\admin\AdminFilters;
use Leadin\admin\MenuConstants;
use Leadin\admin\Gutenberg;
use Leadin\admin\NoticeManager;
use Leadin\admin\PluginActionsManager;
use Leadin\admin\DeactivationForm;
use Leadin\auth\OAuth;
use Leadin\admin\api\RegistrationApi;
use Leadin\admin\api\DisconnectApi;
use Leadin\admin\api\SearchHubSpotFormsApi;
use Leadin\admin\utils\Background;
use Leadin\utils\QueryParameters;
use Leadin\utils\Versions;
use Leadin\includes\utils as utils;

/**
 * Class responsible for initializing the admin side of the plugin.
 */
class LeadinAdmin {
	const REDIRECT_TRANSIENT = 'leadin_redirect_after_activation';

	/**
	 * Class constructor, adds all the hooks and instantiate the APIs.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_languages' ), 14 );
		add_action( 'admin_init', array( $this, 'redirect_after_activation' ) );
		add_action( 'admin_init', array( $this, 'authorize' ) );
		add_action( 'admin_menu', array( $this, 'build_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		register_activation_hook( LEADIN_BASE_PATH, array( $this, 'do_activate_action' ) );

		/**
		 * The following hooks are public APIs.
		 */
		add_action( 'leadin_redirect', array( $this, 'set_redirect_transient' ) );
		add_action( 'leadin_activate', array( $this, 'do_redirect_action' ), 100 );

		new RegistrationApi();
		new DisconnectApi();
		new PluginActionsManager();
		new DeactivationForm();
		new NoticeManager();
		new AdminFilters();
		if ( Connection::is_connected() ) {
			new Gutenberg();
		}
	}

	/**
	 * Load the .mo language files.
	 */
	public function load_languages() {
		load_plugin_textdomain( 'leadin', false, '/leadin/languages' );
	}

	/**
	 * Handler called on plugin activation.
	 */
	public function do_activate_action() {
		\do_action( 'leadin_activate' );
	}

	/**
	 * Handler for the leadin_activate action.
	 */
	public function do_redirect_action() {
		\do_action( 'leadin_redirect' );
	}

	/**
	 * Set transient after activating the plugin.
	 */
	public function set_redirect_transient() {
		set_transient( self::REDIRECT_TRANSIENT, true, 60 );
	}

	/**
	 * Redirect to the dashboard after activation.
	 */
	public function redirect_after_activation() {
		if ( get_transient( self::REDIRECT_TRANSIENT ) ) {
			delete_transient( self::REDIRECT_TRANSIENT );
			wp_safe_redirect( admin_url( 'admin.php?page=leadin' ) );
			exit;
		}
	}

	/**
	 * Connect/disconnect the plugin
	 */
	public function authorize() {
		if ( OAuth::is_enabled() ) {
			if ( Connection::is_connection_requested() ) {
				Connection::oauth_connect();
				OAuthRouting::root_redirect( array( 'leadin_just_connected' => 1 ) );
			} elseif ( Connection::is_disconnection_requested() ) {
				Connection::oauth_disconnect();
				OAuthRouting::root_redirect();
			}
		}
	}


	/**
	 * Adds scripts for the admin section.
	 */
	public function enqueue_scripts() {
		AssetsManager::register_assets();
		AssetsManager::enqueue_admin_assets();
		if ( get_current_screen()->id === 'plugins' ) {
			AssetsManager::enqueue_feedback_assets();
		}
	}

	/**
	 * Adds Leadin menu to admin sidebar
	 */
	public function build_menu() {
		if ( Connection::is_connected() ) {
			if ( OAuth::is_enabled() ) {
				add_menu_page( __( 'HubSpot', 'leadin' ), __( 'HubSpot', 'leadin' ), AdminFilters::apply_view_plugin_menu_capability(), MenuConstants::ROOT, array( $this, 'build_app' ), 'dashicons-sprocket', '25.100713' );
				add_submenu_page( MenuConstants::ROOT, __( 'Settings', 'leadin' ), __( 'Settings', 'leadin' ), AdminFilters::apply_view_plugin_menu_capability(), MenuConstants::SETTINGS, array( $this, 'build_app' ) );
			} else {
				add_menu_page( __( 'HubSpot', 'leadin' ), __( 'HubSpot', 'leadin' ), AdminFilters::apply_view_plugin_menu_capability(), MenuConstants::ROOT, array( $this, 'build_app' ), 'dashicons-sprocket', '25.100713' );
				add_submenu_page( MenuConstants::ROOT, __( 'User Guide', 'leadin' ), __( 'User Guide', 'leadin' ), AdminFilters::apply_view_plugin_menu_capability(), MenuConstants::USER_GUIDE, array( $this, 'build_app' ) );
				add_submenu_page( MenuConstants::ROOT, __( 'Reporting', 'leadin' ), __( 'Reporting', 'leadin' ), AdminFilters::apply_view_plugin_menu_capability(), MenuConstants::REPORTING, array( $this, 'build_app' ) );
				add_submenu_page( MenuConstants::ROOT, __( 'Contacts', 'leadin' ), __( 'Contacts', 'leadin' ), AdminFilters::apply_view_plugin_menu_capability(), MenuConstants::CONTACTS, array( $this, 'build_app' ) );
				add_submenu_page( MenuConstants::ROOT, __( 'Lists', 'leadin' ), __( 'Lists', 'leadin' ), AdminFilters::apply_view_plugin_menu_capability(), MenuConstants::LISTS, array( $this, 'build_app' ) );
				add_submenu_page( MenuConstants::ROOT, __( 'Forms', 'leadin' ), __( 'Forms', 'leadin' ), AdminFilters::apply_view_plugin_menu_capability(), MenuConstants::FORMS, array( $this, 'build_app' ) );
				add_submenu_page( MenuConstants::ROOT, __( 'Live Chat', 'leadin' ), __( 'Live Chat', 'leadin' ), AdminFilters::apply_view_plugin_menu_capability(), MenuConstants::CHATFLOWS, array( $this, 'build_app' ) );
				add_submenu_page( MenuConstants::ROOT, __( 'Email', 'leadin' ), __( 'Email', 'leadin' ), AdminFilters::apply_view_plugin_menu_capability(), MenuConstants::EMAIL, array( $this, 'build_app' ) );
				add_submenu_page( MenuConstants::ROOT, __( 'Settings', 'leadin' ), __( 'Settings', 'leadin' ), AdminFilters::apply_view_plugin_menu_capability(), MenuConstants::SETTINGS, array( $this, 'build_app' ) );
				add_submenu_page( MenuConstants::ROOT, __( 'Upgrade', 'leadin' ), __( 'Upgrade', 'leadin' ), AdminFilters::apply_view_plugin_menu_capability(), MenuConstants::PRICING, array( $this, 'build_app' ) );
				remove_submenu_page( MenuConstants::ROOT, MenuConstants::ROOT );
			}
		} else {
			$notification_icon = ' <span class="update-plugins count-1"><span class="plugin-count">!</span></span>';
			add_menu_page( __( 'HubSpot', 'leadin' ), __( 'HubSpot', 'leadin' ) . $notification_icon, AdminFilters::apply_connect_plugin_capability(), MenuConstants::ROOT, array( $this, 'build_app' ), 'dashicons-sprocket', '25.100713' );
		}
	}

	/**
	 * Renders the leadin admin page.
	 */
	public function build_app() {
		AssetsManager::enqueue_bridge_assets();

		$error_message = '';

		if ( Versions::is_php_version_supported() ) {
			$error_message = sprintf(
				__( 'HubSpot All-In-One Marketing %1$s requires PHP %2$s or higher. Please upgrade WordPress first.', 'leadin' ),
				LEADIN_PLUGIN_VERSION,
				LEADIN_REQUIRED_PHP_VERSION
			);
		} elseif ( Versions::is_wp_version_supported() ) {
			$error_message = sprintf(
				__( 'HubSpot All-In-One Marketing %1$s requires PHP %2$s or higher. Please upgrade WordPress first.', 'leadin' ),
				LEADIN_PLUGIN_VERSION,
				LEADIN_REQUIRED_WP_VERSION
			);
		}

		if ( $error_message ) {
			?>
				<div class='notice notice-warning'>
					<p>
						<?php echo esc_html( $error_message ); ?>
					</p>
				</div>
			<?php
		} else {
			?>
				<div id="leadin-iframe-container"></div>
			<?php
		}
	}
}
