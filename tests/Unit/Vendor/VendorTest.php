<?php
/**
 * Ready to use test case which set up Brain Monkey.
 *
 * @package WCVendors
 */

namespace WCVendors\Tests\Integration;

use Brain\Monkey;
use Mockery; 
use WCVendors\Vendor\Vendor;
use Brain\Monkey\Functions;
use WCVendors\Tests\Unit\WCVendors_TestCase;


/**
 * Base test case for WC Vendors Component.
 */
class Vendor_Test extends WCVendors_TestCase {

	/**
	 * Setup the test
	 */
	public function setUp(): void {
		parent::setUp();
		$this->wp_user =  Mockery::mock( 'WP_USER' );
	}

	/**
	 * Test Setters and getters 
	 */
	public function test_setters_getters() {
		
		Functions\expect( 'get_user_by' )->with( 'id', 1 )->andReturn( true );
        
        $store_defaults = array(
            'store_name'    => '',
            'info'          => '',
            'description'   => '',
            'company_url'   => '',
            'slug'          => '',
            'phone'         => '',
            'email'         => '',
            'address'       => array(),
            'address_other' => array(),
            'seo'           => array(),
            'social'        => array(),
            'location'      => array(
                'long' => 0,
                'lat'  => 0,
            ),
            'branding'      => array(
                'banner_id' => 0,
                'icon_id'   => 0,
            ),
            'payout'        => array(
                'paypal' => array( 'email' => '' ),
                'bank'   => array(
                    'account_name'   => '',
                    'account_number' => '',
                    'bank_name'      => '',
                    'routing_number' => '',
                    'iban'           => '',
                    'bic_swift'      => '',
                ),
            ),
            'give_tax'      => 'no',
            'give_shipping' => 'no',
            'commission'    => array(
                'type'   => 'percent',
                'amount' => 0,
                'fee'    => 0,
            ),
        );

		$setters = [
			'store_name'    => 'Vendor One Store',
			'info'          => 'Info',
			'description'   => 'Description',
			'company_url'   => 'https://wcvendors.com',
			'slug'          => 'vendor_one',
			'phone'         => '12345678',
			'email'         => 'vendor1@wcvendors.com',
			'address'       => [
                'address_1' => '123 Main Street', 
                'address_2' => '', 
                'city' => 'Frisco', 
                'state' => 'CO', 
                'postcode' => '80443', 
                'country' => 'US', 
            ],
			'address_other' => [
                'address_1' => '400 Second Street', 
                'address_2' => '', 
                'city' => 'Silverthorne', 
                'state' => 'CO', 
                'postcode' => '80497', 
                'country' => 'US', 
            ],
			'seo'        => [
				'title' => 'Vendor Store One', 
				'meta_description' => 'Description', 
				'meta_keywords' => 'one,two,thre'
			], 
			'social'        => [ 'twitter' => 'wcvendors' ],
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

        Functions\expect( 'wcv_vendor_store_info_defaults' )->once()->andReturn( $store_defaults );

		$vendor = new Vendor( $this->wp_user );
        

		// Check the action has fired 
		$this->assertSame( 1, did_action( 'wcvendors_vendor_loaded' ) );
        // Set all values
		foreach ( $setters as $method => $value ) {
			$vendor->{"set_{$method}"}( $value );
		}

        Functions\expect( 'update_user_meta' )->once()->andReturn( true );
        // Save the object
        $vendor->save();
        $this->assertSame( 1, did_action( 'wcvendors_vendor_before_vendor_save' ) );
        $this->assertSame( 1, did_action( 'wcvendors_vendor_after_vendor_save' ) );
        // Test the getters 
		$getters = array();
		foreach ( $setters as $method => $value ) {
			$getters[ $method ] = $vendor->{"get_{$method}"}();
		}

		$this->assertEquals( $setters, $getters );
	

	}




}

