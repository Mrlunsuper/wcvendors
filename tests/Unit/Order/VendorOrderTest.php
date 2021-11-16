<?php

namespace WCVendors\Tests\Unit;

use Mockery;
use WCVendors\Tests\Unit\WCVendors_TestCase;
use WooCommerce;

class VendorOrderTest extends WCVendors_TestCase {

	public function setUp(): void {
		parent::setUp();
		Mockery::mock( 'WC_Order' );
		$this->data = array(
			'vendor_id'       => '1',
			'commission'      => '5',
			'order_item_ids'  => array(
				'1',
				'2',
				'3',
			),
			'parent_id'       => '1',
			'total_tax'       => '3',
			'shipping_total'  => '2',
			'shipping_tax'    => '0.6',
			'discount_total'  => '0.3',
			'discount_tax'    => '0.1',
			'commission_paid' => true,

		);
	}

	/**
	 * Test the vendor order class
	 */
	public function test_vendor_order_class() {

		$this->assertTrue( class_exists( 'WCVendors\VendorOrder' ) );
		$this->assertTrue( class_exists( 'WCVendors\DataStores\VendorOrder' ) );
	}

	/**
	 * Test the vendor order class has the correct getters and setters
	 */
	public function test_getter_setter() {
		$vendor_order = Mockery::mock( 'WCVendors\VendorOrder' );
		foreach ( $this->data as $key => $value ) {
			$exist_setter = method_exists( $vendor_order, 'set_' . $key );
			$exist_getter = method_exists( $vendor_order, 'get_' . $key );
			$this->assertTrue( $exist_setter );
			$this->assertTrue( $exist_getter );
		}
	}
}
