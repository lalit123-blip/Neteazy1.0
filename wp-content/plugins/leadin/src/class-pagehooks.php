<?php

namespace Leadin;

use Leadin\LeadinFilters;
use Leadin\AssetsManager;
use Leadin\wp\User;
use Leadin\auth\OAuth;
use Leadin\admin\Connection;
/**
 * Class responsible of adding the script loader to the website, as well as rendering forms, live chat, etc.
 */
class PageHooks {
	/**
	 * Class constructor, adds the necessary hooks.
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'add_page_analytics' ) );
		add_action( 'wp_head', array( $this, 'add_form_management_script' ) );
		if ( Connection::is_connected() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_scripts' ) );
		}
		add_filter( 'script_loader_tag', array( $this, 'add_id_to_tracking_code' ), 10, 2 );
		add_shortcode( 'hubspot', array( $this, 'leadin_add_hubspot_shortcode' ) );
	}

	/**
	 * Generates 10 characters long string with random values
	 */
	private function get_random_number_string() {
		$result = '';
		for ( $i = 0; $i < 10; $i++ ) {
			$result .= rand( 0, 9 );
		}
		return $result;
	}

	/**
	 * Generates a unique uuid
	 */
	private function generate_div_uuid() {
		return time() * 1000 . '-' . $this->get_random_number_string();
	}

	/**
	 * Adds the script loader to the page.
	 */
	public function add_frontend_scripts() {
		if ( is_single() ) {
			$page_type = 'post';
		} elseif ( is_front_page() ) {
			$page_type = 'home';
		} elseif ( is_archive() ) {
			$page_type = 'archive';
		} elseif ( is_page() ) {
			$page_type = 'page';
		} else {
			$page_type = 'other';
		}

		$leadin_wordpress_info = array(
			'userRole'            => User::get_role(),
			'pageType'            => $page_type,
			'leadinPluginVersion' => LEADIN_PLUGIN_VERSION,
		);

		AssetsManager::enqueue_script_loader( $leadin_wordpress_info );
	}

	/**
	 * Adds the script containing the information needed by the script loader.
	 */
	public function add_page_analytics() {
		$portal_id = Connection::get_portal_id();
		if ( empty( $portal_id ) ) {
			echo '<!-- HubSpot WordPress Plugin v' . esc_html( LEADIN_PLUGIN_VERSION ) . ': embed JS disabled as a portalId has not yet been configured -->';
		} else {
			?>
			<!-- DO NOT COPY THIS SNIPPET! Start of Page Analytics Tracking for HubSpot WordPress plugin v<?php echo esc_html( LEADIN_PLUGIN_VERSION ); ?>-->
			<script type="text/javascript">
				var _hsq = _hsq || [];
				<?php
				// Pass along the correct content-type.
				if ( is_single() ) {
					echo '_hsq.push(["setContentType", "blog-post"]);' . "\n";
				} elseif ( is_archive() || is_search() ) {
					echo '_hsq.push(["setContentType", "listing-page"]);' . "\n";
				} else {
					echo '_hsq.push(["setContentType", "standard-page"]);' . "\n";
				}
				?>
			</script>
			<!-- DO NOT COPY THIS SNIPPET! End of Page Analytics Tracking for HubSpot WordPress plugin -->
			<?php
		}
	}

	/**
	 * Add the required id to the script loader <script>
	 *
	 * @param String $tag tag name.
	 * @param String $handle handle.
	 */
	public function add_id_to_tracking_code( $tag, $handle ) {
		if ( AssetsManager::TRACKING_CODE === $handle ) {
			$tag = str_replace( "id='" . $handle . "-js'", "async defer id='hs-script-loader'", $tag );
		}
		return $tag;
	}

	/**
	 * Parse leadin shortcodes
	 *
	 * @param array $attributes Shortcode attributes.
	 */
	public function leadin_add_hubspot_shortcode( $attributes ) {
		$parsed_attributes = shortcode_atts(
			array(
				'type'   => null,
				'portal' => null,
				'id'     => null,
			),
			$attributes
		);

		if (
			! isset( $parsed_attributes['type'] ) ||
			! isset( $parsed_attributes['portal'] ) ||
			! isset( $parsed_attributes['id'] )
		) {
			return;
		}

		$portal_id = $parsed_attributes['portal'];
		$id        = $parsed_attributes['id'];

		switch ( $parsed_attributes['type'] ) {
			case 'form':
				$form_div_uuid = $this->generate_div_uuid();
				AssetsManager::enqueue_forms_script();
				return '
					<script>
						hbspt.enqueueForm({
							portalId: ' . $portal_id . ',
							formId: "' . $id . '",
							target: "#hbspt-form-' . $form_div_uuid . '",
							shortcode: "wp",
							' . LeadinFilters::get_leadin_forms_payload() . '
						});
					</script>
					<div class="hbspt-form" id="hbspt-form-' . $form_div_uuid . '"></div>';
			case 'cta':
				return '
					<!--HubSpot Call-to-Action Code -->
					<span class="hs-cta-wrapper" id="hs-cta-wrapper-' . $id . '">
							<span class="hs-cta-node hs-cta-' . $id . '" id="' . $id . '">
									<!--[if lte IE 8]>
									<div id="hs-cta-ie-element"></div>
									<![endif]-->
									<a href="https://cta-redirect.hubspot.com/cta/redirect/' . $portal_id . '/' . $id . '" >
											<img class="hs-cta-img" id="hs-cta-img-' . $id . '" style="border-width:0px;" src="https://no-cache.hubspot.com/cta/default/' . $portal_id . '/' . $id . '.png"  alt="New call-to-action"/>
									</a>
							</span>
							<' . 'script charset="utf-8" src="//js.hubspot.com/cta/current.js"></script>
							<script type="text/javascript">
									hbspt.cta.load(' . $portal_id . ', \'' . $id . '\', {});
							</script>
					</span>
					<!-- end HubSpot Call-to-Action Code -->
				';
		}
	}

	/**
	 * Adds script to manage HubSpot forms on the page
	 */
	public function add_form_management_script() {
		?>
			<script>
				(function() {
					var hbspt = window.hbspt = window.hbspt || {};
					hbspt.forms = hbspt.forms || {};
					hbspt._wpFormsQueue = [];
					hbspt.enqueueForm = function(formDef) {
						if (hbspt.forms && hbspt.forms.create) {
							hbspt.forms.create(formDef);
						} else {
							hbspt._wpFormsQueue.push(formDef);
						}
					}
					if (!window.hbspt.forms.create) {
						Object.defineProperty(window.hbspt.forms, 'create', {
							configurable: true,
							get: function() {
								return hbspt._wpCreateForm;
							},
							set: function(value) {
								hbspt._wpCreateForm = value;
								while (hbspt._wpFormsQueue.length) {
									var formDef = hbspt._wpFormsQueue.shift();
									if (!document.currentScript) {
										var formScriptId = '<?php echo esc_html( AssetsManager::FORMS_SCRIPT ); ?>-js';
										hubspot.utils.currentScript = document.getElementById(formScriptId);
									}
									hbspt._wpCreateForm.call(hbspt.forms, formDef);
								}
							},
						});
					}
				})();
			</script>
		<?php
	}
}
