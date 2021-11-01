<?php
/**
 * Ready to use test case which set up Brain Monkey.
 *
 * @package WCVendors
 */

namespace WCVendors\Tests\Integration;

use Brain\Monkey;
use Mockery; 
use WP_User;
use WCVendors\Vendor\Vendor;
use Brain\Monkey\Functions;
use WCVendors\Tests\Integration\TestCase;


/**
 * Base test case for WC Vendors Component.
 */
class Vendor_Test extends TestCase {

	/**
	 * Setup the test
	 */
	public function setUp(): void {
		parent::setUp();
		$this->wp_user =  Mockery::mock( WP_User::class );
		$this->wp_user->ID = 1; 
	}

	/**
	 * Test Setters and getters 
	 */
	public function setters_getters() {

		$vendor_id = $this->factory->user->create(
			[ 
				'user_login'   => 'vendor1',
				'display_name' => 'Vendor One',
				'role' => 'vendor' 
			]
		);	
		
		Functions\expect( 'get_user_by' )->once()->with( 'id', 1 )->andReturn( true );

		$setters = [
			'store_name'    => 'Vendor One Store',
			'info'          => 'Info',
			'description'   => 'Description',
			'company_url'   => 'https://wcvendors.com',
			'slug'          => 'vendor_one',
			'phone'         => '12345678',
			'email'         => 'vendor1@wcvendors.com',
			'address'       => [ ],
			'address_other' => [],
			'seo'        => [
				'title' => 'Vendor Store One', 
				'meta_description' => 'Description', 
				'meta_keywords' => 'one,two,thre'
			], 
			'social'           => [ 'twitter' => 'wcvendors' ],
			'location'      => [ 'long' => 123, 'lat'  => 456 ], 
			'banner_id' => 12,
			'icon_id'   => 15,
			'payout'        => [
				'paypal' => [ 'email' => 'vendor1@wcvendors.com' ],
				'bank'   =>[
					'account_name'   => 'Vendor One',
					'account_number' => '12345678',
					'bank_name'      => 'WC Vendors Bank of Testing',
					'routing_number' => '123222',
					'iban'           => '',
					'bic_swift'      => '',
				],
			],
			'give_tax'      => true,
			'give_shipping' => true,
			'commission'    => [
				'type'   => 'percent',
				'amount' => 50,
				'fee'    => 0,
			],
		]; 

		$vendor = new Vendor( $this->wp_user );

		// Check the action has fired 
		$this->assertSame( 1, did_action( 'wcvendors_vendor_loaded' ) );

		error_log( print_r( $vendor, true )); 

		foreach ( $setters as $method => $value ) {
			$vendor->{"set_{$method}"}( $value );
		}

		$getters = array();

		foreach ( $setters as $method => $value ) {
			$getters[ $method ] = $vendor->{"get_{$method}"}();
		}

		$this->assertEquals( $setters, $getters );
	

	}




}

