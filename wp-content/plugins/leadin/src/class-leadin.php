<?php

namespace Leadin;

use \Leadin\AssetsManager;
use \Leadin\PageHooks;
use \Leadin\admin\LeadinAdmin;
use Leadin\rest\LeadinRestApi;

/**
 * Main class of the plugin.
 */
class Leadin {
	/**
	 * Plugin's constructor. Everything starts here.
	 */
	public function __construct() {
		new PageHooks();
		new LeadinRestApi();
		if ( is_admin() ) {
			new LeadinAdmin();
		}
	}
}
