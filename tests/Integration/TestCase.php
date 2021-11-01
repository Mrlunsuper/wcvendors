<?php

namespace WCVendors\Tests\Unit;

use Brain\Monkey;
use WPSEO_Options;
use Yoast\WPTestUtils\BrainMonkey\YoastTestCase;

/**
 * TestCase base class.
 */
abstract class TestCase extends YoastTestCase {

	/**
	 * Options being mocked.
	 *
	 * @var array
	 */
	protected $mocked_options = [ 'wcvendors_store_data' ];

	/**
	 * Sets up the test fixtures.
	 */
	protected function set_up() {
		parent::set_up();

		Monkey\Functions\stubs(
			[
				// Null makes it so the function returns its first argument.
				'is_admin'             => false,
			]
		);

		Monkey\Functions\expect( 'get_option' )
			->zeroOrMoreTimes()
			->with( \call_user_func_array( 'Mockery::anyOf', $this->mocked_options ) )
			->andReturn( [] );

		Monkey\Functions\expect( 'get_site_option' )
			->zeroOrMoreTimes()
			->with( \call_user_func_array( 'Mockery::anyOf', $this->mocked_options ) )
			->andReturn( [] );
	}
}
