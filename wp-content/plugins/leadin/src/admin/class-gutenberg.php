<?php

namespace Leadin\admin;

use Leadin\AssetsManager;

/**
 * Contains all the methods used to initialize Gutenberg blocks.
 */
class Gutenberg {
	/**
	 * Class constructor, register Gutenberg blocks.
	 */
	public function __construct() {
		if ( ! function_exists( 'register_block_type' ) ) {
			// Gutenberg is not active.
			return;
		}

		add_action( 'init', array( $this, 'register_gutenberg_block' ) );
		add_filter( 'block_categories', array( $this, 'add_hubspot_category' ) );
	}

	/**
	 * Add HubSpot category to Gutenberg blocks.
	 *
	 * @param Array $categories Array of block categories.
	 */
	public function add_hubspot_category( $categories ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'leadin-blocks',
					'title' => __( 'HubSpot', 'leadin' ),
				),
			)
		);
	}
	/**
	 * Register HubSpot Form Gutenberg block.
	 */
	public function register_gutenberg_block() {
		AssetsManager::localize_gutenberg();
		register_block_type(
			'leadin/leadin-forms',
			array(
				'editor_script' => AssetsManager::GUTENBERG,
			)
		);
	}
}
