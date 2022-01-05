<?php

/**
 * Admin orders class 
 * 
 * All WooCommerce Order related functions for WC Vendors.
 * 
 * @since 2.4.0 
 * @version 2.4.0
 * @package WCVendors\Admin
 */

class WCVendors_Admin_Orders { 


    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialise all actions and filters here.
     *
     */
    public function init_hooks(){ 
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'add_vendor_shipped_details'), 10, 2 );
		add_action( 'woocommerce_admin_order_actions', array( $this, 'append_mark_shipped' )   , 10, 2 );
		add_action( 'wp_ajax_wcvendors_mark_order_shipped', array( __CLASS__, 'mark_order_shipped' ) );
    }

    /**
     * Add the vendor shipped information to the order edit screen. 
     *
     * @param WC_Order $order the order we are viewing.
     */
    public function add_vendor_shipped_details( $order ) {
		echo $this->get_vendors_shipped_text($order);
	}

	
	/**
	 * Append the mark shipped action to the actions column on the orders screen
	 *
	 * @param array $actions The order actions column
	 * @param WC_Order $order the order row we are currently on.
	 */
	public function append_mark_shipped( $actions, $order ) { 

		if ( $order->has_status( array( 'processing', 'completed' ) ) ) {
			$actions['wcvendors_mark_shipped'] = array(
				'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=wcvendors_mark_order_shipped&order_id=' . $order->get_id() ), 'wcvendors-mark-order-shipped' ),
				'name'   => __( 'Mark Shipped', 'wc-vendors' ). $this->get_vendors_shipped_text( $order ),
				'action' => 'wcvendors_mark_shipped',
			);
		}

		return $actions; 
	}

    /**
     * Get the formatted shipped text to output in various places. 
     *
     * @param WC_Order $order The WooCommerce order being referenced.
     * @param boolean $order_edit Is this the order edit screen.
     * @return void
     */
	public function get_vendors_shipped_text( $order, $order_edit = false ){ 

		$vendors = WCV_Vendors::get_vendors_from_order( $order );
		$vendors = $vendors ? array_keys( $vendors ) : array();
		
		if ( empty( $vendors ) ) {
			return false;
		}

		$shipped = (array) get_post_meta( $order->get_id(), 'wc_pv_shipped', true );
		$string  = '<h4>' . __( 'Vendors shipped', 'wc-vendors' ) . '</h4>';

		foreach ( $vendors as $vendor_id ) {
			$string .= in_array( $vendor_id, $shipped ) ? '&#10004; ' : '&#10005; ';
			$string .= WCV_Vendors::get_vendor_shop_name( $vendor_id );
			$string .= '<br />';
		}

		return $string;
	}

	/**
	 * Mark an order shipped for all vendors.
	 */
	public static function mark_order_shipped() {
		if ( current_user_can( 'edit_shop_orders' ) && check_admin_referer( 'wcvendors-mark-order-shipped' ) && $_GET['order_id'] )  {
            WC_Vendors::log( 'Marking shipped...' );

            $order  = wc_get_order( absint( wp_unslash( $_GET['order_id'] ) ) );
            $vendors    = WCV_Vendors::get_vendors_from_order( $order );
            $vendor_ids = array_keys( $vendors );

            WC_Vendors::log( $vendor_ids );
            
            foreach ( $vendor_ids as $vendor_id ) {
                wcv_mark_order_shipped( $order, $vendor_id );
            }

		}

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=shop_order' ) );
		exit;
	}



}


