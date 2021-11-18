<?php
/**
 * WC Vendors Vendor Functions
 *
 * Functions for customers.
 *
 * @package WCVendors/Functions
 * @version 3.0.0
 */

 use WCVendors\Vendor\Vendor;
/**
 * The vendor store info defaults plugins can hook into this to add more data to a vendor store.
 *
 * @return array $vendor_store_defaults The vendor store data defaults.
 */
function wcv_vendor_store_data_defaults() {

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

	return apply_filters( 'wcvendors_vendor_store_data_defaults', $store_defaults );
}

/**
 * Format the wp user meta removing any empty values
 *
 * @param int $vendor_id Vendor ID to look up.
 *
 * @since 3.0.0
 *
 * @return array $wp_user_meta cleand array
 */
function wcv_format_user_data( $vendor_id ) {

	// User array_filter to remove empty values.
	$wp_user_meta = array_filter(
		array_map(
			function( $a ) {
				return $a[0];
			},
			get_user_meta( $vendor_id )
		)
	);

	return apply_filters( 'wcv_formatted_user_data', $wp_user_meta, $vendor_id );
}


/**
 * Check to see if the user_id is a vendor. Based on capability and role.
 *
 * @param Vendor $vendor the user id to check.
 */
function wcv_is_vendor( $vendor ) {

	if ( is_numeric( $vendor ) ) {
		$vendor = get_userdata( $vendor );
	}

	if ( is_object( $vendor ) ) {
		if ( is_array( $vendor->roles ) ) {
			return in_array( 'vendor', $vendor->roles, true );
		}
	}

	return false;
}

/**
 * Check to see if the user_id is a vendor pending. Based on capability and role.
 *
 * @param int $user_id the user id to check.
 */
function wcv_is_vendor_pending( $user_id ) {
	$current_user = get_userdata( $user_id );

	if ( is_object( $current_user ) ) {

		if ( is_array( $current_user->roles ) ) {
			return in_array( 'vendor_pending', $current_user->roles, true );
		}
	}

	return false;
}

/**
 * Check to see if the user_id is a vendor denied. Based on capability and role.
 *
 * @param int $user_id The user id to check.
 */
function wcv_is_vendor_denied( $user_id ) {
	$current_user = get_userdata( $user_id );

	if ( is_object( $current_user ) ) {

		if ( is_array( $current_user->roles ) ) {
			return in_array( 'vendor_denied', $current_user->roles, true );
		}
	}

	return false;
}

/**
 * This function gets the vendor term used throughout the interface on the front and backend
 *
 * @param bool $singluar is it a singular.
 * @param bool $upper_case Uppercase the first letter of the label.
 */
function wcv_get_vendor_name( $singluar = true, $upper_case = true ) {

	$vendor_singular = get_option( 'wcvendors_vendor_singular', __( 'Vendor', 'wc-vendors' ) );
	$vendor_plural   = get_option( 'wcvendors_vendor_plural', __( 'Vendors', 'wc-vendors' ) );

	$vendor_label = $singluar ? $vendor_singular : $vendor_plural;
	$vendor_label = $upper_case ? ucfirst( $vendor_label ) : lcfirst( $vendor_label );

	return apply_filters( 'wcvendors_vendor_name', $vendor_label, $vendor_singular, $vendor_plural, $singluar, $upper_case );

}

/**
 * Get all vendors
 *
 * @param array $args list of arguments to pass to get_users().
 */
function wcv_get_vendors( $args = array() ) {

	$args = wp_parse_args(
		$args,
		array(
			'role__in' => array( 'vendor', 'administrator' ),
			'fields'   => array( 'ID', 'display_name', 'username' ),
		)
	);

	$vendors = get_users( $args );

	return $vendors;
}

/**
 * Get the vendor display name
 *
 * @param int $vendor_id the vendor_id to get th display name for.
 */
function wcv_get_vendor_display_name( $vendor_id ) {

	$vendor_display_name_option = apply_filters( 'wcvendors_vendor_display_name_option', 'user_login' );
	$vendor                     = get_userdata( $vendor_id );
	$display_name               = __( 'vendor', 'wc-vendors' );

	switch ( $vendor_display_name_option ) {

		case 'display_name':
			$display_name = $vendor->display_name;
			break;
		case 'user_email':
			$display_name = $vendor->user_email;
			break;
		case 'user_login':
			$display_name = $vendor->user_login;
			break;
		default:
			$display_name = $vendor->user_login;
			break;
	}

	return apply_filters( 'wcvendors_get_display_name', $display_name, $vendor_id, $vendor );

}



if ( ! function_exists( 'wcv_set_primary_vendor_role' ) ) {

	/**
	 * Set the primary role of the specified user to vendor while retaining all other roles after
	 *
	 * @param WP_User $user The User to set the primary role for.
	 *
	 * @since   3.0.0
	 * @version 3.0.0
	 */
	function wcv_set_primary_vendor_role( $user ) {
		// Get existing roles.
		$existing_roles = $user->roles;
		// Remove all existing roles.
		foreach ( $existing_roles as $role ) {
			$user->remove_role( $role );
		}
		// Add vendor first.
		$user->add_role( 'vendor' );
		// Re-add all other roles.
		foreach ( $existing_roles as $role ) {
			$user->add_role( $role );
		}
	}
}

/**
 * Retrieve the shop name for a specific vendor
 *
 * @param Vendor $vendor The vendor to get the shopname for.
 *
 * @version 3.0.0
 * @since   3.0.0
 * @return  string
 */
function wcv_get_vendor_shop_name( $vendor ) {

	if ( is_numeric( $vendor ) ) {
		if ( ! wcv_is_vendor( $vendor ) ) {
			return;
		}
		$vendor = new Vendor( $vendor );
	}

	$shop_name = $vendor->get_store_name() ? $vendor->get_store_name() : $vendor->get_wp_user()->get_user_login();
	return apply_filters( 'wcvendors_get_vendor_shop_name', $shop_name, $vendor );
}

/**
 * Get vendors from an order including all user meta and vendor items filtered and grouped
 *
 * @param WC_Order $order The order to check.
 * @param array    $items Order items to check (optional).
 *
 * @return  array $vendors
 * @version 3.0.0
 * @since   3.0.0
 */
function wcv_get_vendors_from_order( $order, $items = false ) {

	$vendors      = array();
	$vendor_items = array();

	if ( $order instanceof WC_Order ) {

		// Only loop through order items if there isn't an error.
		if ( is_array( $order->get_items() ) || is_object( $order->get_items() ) ) {

			foreach ( $order->get_items() as $item_id => $order_item ) {

				if ( 'line_item' === $order_item->get_type() ) {

					$product_id = ( $order_item->get_variation_id() ) ? $order_item->get_variation_id() : $order_item->get_product_id();
					$vendor_id  = wcv_get_vendor_from_product( $product_id );

					if ( ! wcv_is_vendor( $vendor_id ) ) {
						continue;
					}

					if ( array_key_exists( $vendor_id, $vendors ) ) {
						$vendors[ $vendor_id ]['line_items'][ $order_item->get_id() ] = $order_item;
					} else {
						$vendor_details        = array(
							'vendor'     => get_userdata( $vendor_id ),
							'line_items' => array( $order_item->get_id() => $order_item ),
						);
						$vendors[ $vendor_id ] = $vendor_details;
					}
				}
			}
		} else {
			$vendors = array();
		}
	}

	// legacy filter left in place.
	$vendors = apply_filters( 'pv_vendors_from_order', $vendors, $order );

	return apply_filters( 'wcvendors_get_vendors_from_order', $vendors, $order );
}

/**
 * Get a vendor from a product.
 *
 * @param int $product_id The product id.
 *
 * @version 3.0.0
 * @since   3.0.0
 * @return  mixed
 */
function wcv_get_vendor_from_product( $product_id ) {

	// Make sure we are returning an author for products or product variations only.
	if ( 'product' === get_post_type( $product_id ) || 'product_variation' === get_post_type( $product_id ) ) {
		$parent = get_post_ancestors( $product_id );
		if ( $parent ) {
			$product_id = $parent[0];
		}

		$post   = get_post( $product_id );
		$author = $post ? $post->post_author : 1;
		$author = apply_filters( 'wcvendors_product_author', $author, $product_id );
	} else {
		$author = -1;
	}

	return $author;
}

/**
 * Wrapper for get_avatar_url() to be able to filter it specifically for wcvendors
 *
 * @param int $vendor_id The the user_id to check.
 *
 * @return string Avatar URL The avatar URL.
 */
function wcv_get_avatar_url( $vendor_id ) {
	$avatar_url = get_avatar_url( $vendor_id );
	return apply_filters( 'wcvendors_get_avatar_url', $avatar_url, $vendor_id );
}

if ( ! function_exists( 'wcv_get_storeurl' ) ) {

	/**
	 * Retrieve the shop page for a specific vendor
	 *
	 * @param unknown $vendor_id
	 *
	 * @return string
	 */
	function wcv_get_storeurl( $vendor_id ) {

		$vendor_id = wcv_get_vendor_id( $vendor_id );
		if ( ! $vendor_id ) {
			return;
		}

		$vendor      = new Vendor( $vendor_id );
		$slug        = $vendor->get_slug();
		$vendor_slug = ! $slug ? $vendor->get_wp_user()->get_user_login() : $slug;

		if ( get_option( 'permalink_structure' ) ) {
			$permalink = trailingslashit( get_option( 'wcvendors_vendor_shop_permalink' ) );

			return trailingslashit( home_url( sprintf( '/%s%s', $permalink, $vendor_slug ) ) );
		} else {
			return esc_url( add_query_arg( array( 'vendor_shop' => $vendor_slug ), get_post_type_archive_link( 'product' ) ) );
		}
	}
}

if ( ! function_exists( 'wcv_get_vendor_id' ) ) {
	/**
	 * Grabs the vendor ID whether a username or an int is provided
	 * and returns the vendor_id if it's actually a vendor
	 *
	 * @param unknown $input
	 *
	 * @return unknown
	 */
	function wcv_get_vendor_id( $input ) {

		if ( empty( $input ) ) {
			return false;
		}

		$users = get_users(
			array(
				'meta_key'   => 'pv_shop_slug',
				'meta_value' => sanitize_title( $input ),
			)
		);

		if ( ! empty( $users ) && 1 == count( $users ) ) {
			$vendor = $users[0];
		} else {
			$int_vendor = is_numeric( $input );
			$vendor     = ! empty( $int_vendor ) ? get_userdata( $input ) : get_user_by( 'login', $input );
		}

		if ( $vendor ) {
			$vendor_id = $vendor->ID;
			if ( wcv_is_vendor( $vendor_id ) ) {
				return $vendor_id;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'wcv_get_prop_map') ) {

	/**
	 * Get mapped vendor props with old meta keys
	 *
	 * @return array
	 */
   function wcv_get_prop_map() {
	$prop_to_meta_map = array(
		'store_name'    => 'pv_shop_name',
		'info'          => 'pv_seller_info',
		'description'   => 'pv_shop_description',
		'company_url'   => '_wcv_company_url',
		'slug'          => 'pv_shop_slug',
		'phone'         => '_wcv_store_phone',
		'email'         => 'billing_email',
		'address'       => array(
			'address_1' => '_wcv_store_address1',
			'address_2' => '_wcv_store_address2',
			'city'      => '_wcv_store_city',
			'state'     => '_wcv_store_state',
			'postcode'  => '_wcv_store_postcode',
			'country'   => '_wcv_store_country',
		),
		'address_other' => array(),
		'seo'           => array(
			'title'            => 'wcv_seo_title',
			'meta_description' => 'wcv_seo_meta_description',
			'meta_keywords'    => 'wcv_seo_meta_keywords',
		),
		'social'        => array(
			'twitter'  => array(
				'title'       => 'wcv_seo_twitter_title',
				'description' => 'wcv_seo_twitter_description',
				'image_id'    => 'wcv_seo_twitter_image_id',
			),
			'facebook' => array(
				'title'       => 'wcv_seo_fb_title',
				'description' => 'wcv_seo_fb_description',
				'image_id'    => 'wcv_seo_fb_image_id',
			),
		),
		'location'      => array(
			'long' => 'wcv_address_latitude',
			'lat'  => 'wcv_address_longitude',
		),
		'branding'      => array(
			'banner_id' => '_wcv_store_banner_id',
			'icon_id'   => '_wcv_store_icon_id',
		),
		'payout'        => array(
			'paypal' => array( 'email' => 'pv_paypal' ),
			'bank'   => array(
				'account_name'   => 'wcv_bank_account_name',
				'account_number' => 'wcv_bank_account_number',
				'bank_name'      => 'wcv_bank_name',
				'routing_number' => 'wcv_bank_routing_number',
				'iban'           => 'wcv_bank_iban',
				'bic_swift'      => 'wcv_bank_bic_swift',
			),
		),
		'give_tax'      => 'wcv_give_vendor_tax',
		'give_shipping' => 'wcv_give_vendor_shipping',
		'commission'    => array(
			'type'   => '_wcv_commission_type',
			'amount' => '_wcv_commission_percent',
			'fee'    => '_wcv_commission_fee',
		),
	);

	return apply_filters( 'wcvendors_prop_map', $prop_to_meta_map );
   }
}
