<?php

namespace WCVendors\Admin\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Display Vendor orders in WP Admin side
 *
 * @author      WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     3.0.0
 */
class Orders {

	public static function output() {
		$order_page = new OrdersTable();
		$order_page->prepare_items();
		?>
		<div class="wrap">

			<div id="icon-woocommerce" class="icon32 icon32-woocommerce-reports"><br/></div>
			<h2><?php _e( 'Orders', 'wc-vendors' ); ?></h2>

			<form id="posts-filter" method="get">

				<input type="hidden" name="page" value="wcv-vendor-orders"/>
				<?php $order_page->search_box( __( 'Search', 'wc-vendors' ), 'vendor_orders_search' ); ?>
				<?php $order_page->display(); ?>

			</form>
			<div id="ajax-response"></div>
			<br class="clear"/>
		</div>

		<?php
	}
}
