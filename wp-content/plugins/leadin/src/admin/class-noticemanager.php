<?php

namespace Leadin\admin;

use Leadin\wp\User;
use Leadin\admin\Connection;

/**
 * Class responsible for rendering the admin notices.
 */
class NoticeManager {
	/**
	 * Class constructor, adds the necessary hooks.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'leadin_action_required_notice' ) );
	}

	/**
	 * Render the disconnected banner.
	 */
	private function leadin_render_disconnected_banner() {
		?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<img src="<?php echo esc_attr( LEADIN_PATH . '/assets/images/sprocket.svg' ); ?>" height="16" style="margin-bottom: -3px" />
					&nbsp;
					<?php
						echo sprintf(
							esc_html( __( 'The HubSpot plugin isn’t connected right now. To use HubSpot tools on your WordPress site, %1$sconnect the plugin now%2$s.', 'leadin' ) ),
							'<a href="admin.php?page=leadin&bannerClick=true">',
							'</a>'
						); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</p>
			</div>
		<?php
	}

	/**
	 * Find what notice (if any) needs to be rendered
	 */
	public function leadin_action_required_notice() {
		$current_screen = get_current_screen();

		if ( 'leadin' !== $current_screen->parent_base ) {
			if ( ! Connection::is_connected() && User::is_admin() ) {
				$this->leadin_render_disconnected_banner();
			}
		}
	}
}
