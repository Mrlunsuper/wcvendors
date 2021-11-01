<?php
/**
 * Ready to use test case which set up Brain Monkey.
 *
 * @package WCVendors
 */

namespace WCVendors\Tests\Unit;

use Brain\Monkey;
use Mockery; 
use WP_User;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use WCVendors\Tests\Unit\WCVendors_TestCase;


/**
 * Base test case for WC Vendors Component.
 */
class Vendor_Functions_Test extends WCVendors_TestCase {

    /**
     * Represents the vendor user
     *
     * @var Mockery\
     */
    protected $vendor; 

	/**
	 * Setup the test
	 */
	public function setUp(): void {
		parent::setUp();
        $this->vendor = Mockery::mock( WP_User::class ); 
        $this->vendor->ID = 1; 
        $this->vendor->roles = ['vendor']; 
	}

    /**
     * Test the wcv_vendor_store_data_defaults. 
     */
	public function test_wcv_vendor_store_data_defaults(){

        // Make a copy of the defaults to test them.
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

        // Test returning array.
        $defaults = wcv_vendor_store_data_defaults(); 
        $this->assertIsArray( $defaults );

        // Test all keys exist.
        foreach ( $store_defaults as $key => $value ) {
            $this->assertArrayHasKey( $key, $defaults );
        }

    }

    /**
     * Test wcv_is_vendor function 
     */
    public function test_wcv_is_vendor(){ 
        $is_vendor = wcv_is_vendor( $this->vendor ); 
        $this->assertTrue( $is_vendor ); 
    }


    /**
     * Test the vendor term function formally called
     * wcv_get_vendor_name()
     *
     * @return void
     */
    public function test_wcv_get_vendor_name(){ 
        
        $term = wcv_get_vendor_name(); 
        // Check the filter has fired.
        $this->assertTrue( Filters\applied('wcvendors_vendor_name') > 0 );

        // Check default params.
        $this->assertEquals( 'Vendor', $term );

        // Test Upper plural.
        $term = wcv_get_vendor_name( false ); 
        $this->assertEquals( 'Vendors', $term );

        // Test lowercase plural.
        $term = wcv_get_vendor_name( false, false ); 
        $this->assertEquals( 'vendors', $term );

    }

}

