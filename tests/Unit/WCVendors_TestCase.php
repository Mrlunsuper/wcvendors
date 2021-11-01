<?php
/**
 * Ready to use test case which set up Brain Monkey.
 *
 * @package WCVendors
 */

namespace WCVendors\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;
use Brain\Monkey\MatchesSnapshots;
use Brain\Monkey\Functions;


/**
 * Base test case for WC Vendors Component.
 */
abstract class WCVendors_TestCase extends \PHPUnit\Framework\TestCase {
	

	/**
	 * Options being mocked.
	 *
	 * @var array
	 */
	protected $mocked_options = [ 'wcvendors_vendor_singular', 'wcvendors_vendor_plural' ];

	/**
	 * Set up test case.
	 */
	public function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Monkey\Functions\when( '__' )
			->returnArg( 1 );
		Monkey\Functions\when( '_e' )
			->returnArg( 1 );
		Monkey\Functions\when( '_n' )
			->returnArg( 1 );

		Functions\when('get_option')->returnArg(2);
	}

	/**
	 * Tear down test case.
	 */
	public function tearDown(): void {
		Monkey\tearDown();
		\Mockery::close(); 
		parent::tearDown();
	}
}
