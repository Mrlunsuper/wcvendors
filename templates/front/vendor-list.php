<?php
/**
 * Vendor List Template
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/front/vendors-list.php
 *
 * @author        Jamie Madden, WC Vendors
 * @package       WCVendors/Templates/Front
 * @version       2.0.0
 * @version       2.4.2 - More responsive
 *
 *  Template Variables available
 *  $shop_name : pv_shop_name
 *  $shop_description : pv_shop_description (completely sanitized)
 *  $shop_link : the vendor shop link
 *  $vendor_id  : current vendor id for customization
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
	/**
	 * wcvendors_before_vendor_list_loop hook.
	 * @hooked wcvendors_before_vendor_list_loop - 10
	 */
	do_action( 'wcvendors_vendor_list_filter', $display_mode, $search_term, $vendors_count );
?>
<?php
	/**
	 * wcvendors_before_vendor_list hook.
	 * @hooked wcvendors_before_vendor_list - 10
	 */
	do_action( 'wcvendors_before_vendor_list', $display_mode );
?>
	<?php
		/**
		 * wcvendors_before_vendor_list_loop hook.
		 * @hooked wcvendors_before_vendor_list_loop - 10
		 */
		do_action( 'wcvendors_vendor_list_loop', $vendors );
	?>
<?php
	/**
	 * wcvendors_after_vendor_list hook.
	 * @hooked wcvendors_after_vendor_list - 10
	 */
	do_action( 'wcvendors_after_vendor_list' );
