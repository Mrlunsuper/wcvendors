<?php
namespace WCVendors\Admin\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Display Vendor menu in WP Admin side
 *
 * @author      WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     3.0.0
 */

class Menus{

    /**
     * Init hook
     *
     * @return void
     */
    public function init_hook() {

		add_filter( 'set-screen-option', array( $this, 'set_table_option' ), 10, 3 );
        add_action( 'admin_menu', array( $this, 'vendor_dashboard_pages' ) );

		// Hook into init for form processing
		add_action( 'admin_init', array( SettingsPage::class, 'save_shop_settings' ) );
		add_action( 'admin_head', array( $this, 'admin_enqueue_order_style' ) );

    }

    function vendor_dashboard_pages() {
		add_menu_page(
			__( 'Marketplace', 'wc-vendors' ),
			__( 'Marketplace', 'wc-vendors' ),
			'manage_product',
			'wcv-vendor-marketplace',
			'',
			'dashicons-cart'
		);
		add_submenu_page(
			'wcv-vendor-marketplace',
			__( 'Product', 'wc-vendors' ),
			__( 'Products', 'wc-vendors' ),
			'manage_product',
			'edit.php?post_type=product'
		);
		$order_page = add_submenu_page(
			'wcv-vendor-marketplace',
			__( 'Orders', 'wc-vendors' ),
			__( 'Orders', 'wc-vendors' ),
			'manage_product',
			'wcv-vendor-orders',
			array(
				$this,
				'orders_page',
			)
		);
		add_submenu_page(
			'wcv-vendor-marketplace',
			__( 'Media', 'wc-vendors' ),
			__( 'Media', 'wc-vendors' ),
			'manage_product',
			'upload.php'
		);
		add_submenu_page(
			'wcv-vendor-marketplace',
			__( 'Shop Settings', 'wc-vendors' ),
			__( 'Shop Settings', 'wc-vendors' ),
			'manage_product',
			'wcv-vendor-shopsettings',
			array(
				$this,
				'settings_page',
			)
		);
		add_action( "load-$order_page", array( $this, 'orders_screen_options' ) );
		remove_submenu_page( 'wcv-vendor-marketplace', 'wcv-vendor-marketplace' );
		remove_menu_page( 'upload.php' );
		remove_menu_page( 'edit.php?post_type=product' );
	}

	function settings_page() {

		SettingsPage::output();
	}

	function admin_enqueue_order_style() {

		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		$screen_id = $screen->id;

		if ( 'wcv-vendor-orders' === $screen_id ) {

			add_thickbox();
			wp_enqueue_style( 'admin_order_styles', WCV_PLUGIN_PATH . '/assets/css/admin-orders.css' );
		}
	}

	/**
	 *
	 *
	 * @param unknown $status
	 * @param unknown $option
	 * @param unknown $value
	 *
	 * @return unknown
	 */
	public static function set_table_option( $status, $option, $value ) {

		if ( $option == 'orders_per_page' ) {
			return $value;
		}
	}


	/**
	 *Add screen option to any page
	 */
	public function orders_screen_options() {

		$args = array(
			'label'   => 'Rows',
			'default' => 10,
			'option'  => 'orders_per_page',
		);
		add_screen_option( 'per_page', $args );
		new OrdersTable();

	}

	/**
	 * HTML setup for the Orders Page
	 */
	public function orders_page() {

		Orders::output();
	}

}



