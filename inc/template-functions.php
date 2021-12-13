<?php
/**
 * All function for template
 */

use WCVendors\Vendor\Vendor;

if ( ! function_exists( 'wcv_get_sold_by_link' ) ) {

	/**
	 * Get the vendor sold by URL
	 *
	 * @param int    $vendor_id - vendor's id
	 * @param string $css_class - optional css class
	 */
	function wcv_get_sold_by_link( $vendor_id, $css_class = '' ) {
		$class   = isset( $css_class ) ? 'class="' . $css_class . '"' : '';
        $vendor = new Vendor( $vendor_id );
		$sold_by = $vendor->is_vendor()
			? sprintf( '<a href="%s" %s>%s</a>', $vendor->get_store_url(), $class, wcv_get_vendor_shop_name( $vendor_id ) )
			: get_bloginfo( 'name' );

		$sold_by = apply_filters_deprecated( 'wcv_sold_by_link', array( $sold_by, $vendor_id ), '2.3.0', 'wcvendors_sold_by_link' );

		return apply_filters( 'wcvendors_sold_by_link', $sold_by, $vendor_id );

	}
}

if ( ! function_exists( 'wcv_get_vendor_sold_by' ) ) {

    /**
     * Get the vendor sold by name
     *
     * @param int $vendor_id - vendor's id
     */
	function wcv_get_vendor_sold_by( $vendor_id ) {

		$sold_by_label     = __( get_option( 'wcvendors_label_sold_by' ), 'wc-vendors' );
		$sold_by_separator = __( get_option( 'wcvendors_label_sold_by_separator' ), 'wc-vendors' );
		$sold_by           = wcv_get_sold_by_link( $vendor_id, 'wcvendors_cart_sold_by_meta' );

		$vendor_sold_by = sprintf(
			apply_filters( 'wcvendors_cart_sold_by_meta_template', '%1$s %2$s %3$s', get_the_ID(), $vendor_id ),
			apply_filters( 'wcvendors_cart_sold_by_meta', $sold_by_label, get_the_ID(), $vendor_id ),
			apply_filters( 'wcvendors_cart_sold_by_meta_separator', $sold_by_separator, get_the_ID(), $vendor_id ),
			$sold_by
		);

		return $vendor_sold_by;
	}
}
