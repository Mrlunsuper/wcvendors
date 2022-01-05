<?php 
/**
 * Vendor related functions.
 * 
 * @since 2.4.0
 * @version 2.4.0
 */


/**
 * Mark an order shipped for a particular vendor
 *
 * @param $user WP_User
 *
 * @since 2.4.0
 * @version 2.4.0
 */

if ( ! function_exists( 'wcv_mark_order_shipped' ) ){
	function wcv_mark_order_shipped( $order, $vendor_id ){

        $shippers = (array) get_post_meta( $order->get_id(), 'wc_pv_shipped', true );

        WC_Vendors::log( $shippers );

        // If not in the shippers array mark as shipped otherwise do nothing.
        if ( ! in_array( $vendor_id, $shippers ) ) {

            $shippers[] = $vendor_id;

            if ( ! empty( $mails ) ) {
                WC()->mailer()->emails['WC_Email_Notify_Shipped']->trigger( $order_id, $vendor_id );
            }

            do_action( 'wcvendors_vendor_ship', $order->get_id(), $vendor_id, $order );

            $shop_name = WCV_Vendors::get_vendor_shop_name( $vendor_id );
            $order->add_order_note( apply_filters( 'wcvendors_vendor_shipped_note', sprintf( __( '%s has marked as shipped. ', 'wc-vendors' ), $shop_name ), $vendor_id, $shop_name ) );

        }

        WC_Vendors::log( $shippers );

        update_post_meta( $order->get_id(), 'wc_pv_shipped', $shippers );

	}
}