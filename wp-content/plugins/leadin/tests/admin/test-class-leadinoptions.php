<?php
/**
 * Class LeadinUtilsTest
 *
 * @package Leadin
 */

use Leadin\LeadinOptions;

/**
 * Test leadin-utils.php
 */
class LeadinFunctionsTest extends WP_UnitTestCase {

	/**
	 * Test function leadin_get_affiliate_code
	 */
	public function test_get_affiliate_code() {
		add_option( 'hubspot_affiliate_code', 'foo' );
		$this->assertEquals( LeadinOptions::get_affiliate_code(), 'foo' );
		update_option( 'hubspot_affiliate_code', 'hubs.to/bar' );
		$this->assertEquals( LeadinOptions::get_affiliate_code(), 'bar' );
		update_option( 'hubspot_affiliate_code', 'https://xhubs.to/123' );
		$this->assertEquals( LeadinOptions::get_affiliate_code(), '123' );
		update_option( 'hubspot_affiliate_code', 'https://mbsy.co/xyz' );
		$this->assertEquals( LeadinOptions::get_affiliate_code(), 'xyz' );
		update_option( 'hubspot_affiliate_code', 'https://abc.xyz/1234' );
		$this->assertEquals( LeadinOptions::get_affiliate_code(), 'https://abc.xyz/1234' );
		delete_option( 'hubspot_affiliate_code' );
	}
}
