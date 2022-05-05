<?php
/**
 * Vendor List Template
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/front/vendors-list.php
 *
 * @author        Jamie Madden, WC Vendors
 * @package       WCVendors/Templates/Emails/HTML
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
<div class="vendor_list">
	<div class="vendor_list_avatar">
		<?php echo $avatar; ?>
	</div>
	<div class="vendor_list_info">
		<h3 class="vendor_list--shop-name"><?php echo esc_html( $shop_name ); ?></h3>
		<small class="vendors_list--shop-phone"><span class="dashicons dashicons-smartphone"></span><span><?php echo esc_html( $phone ); ?></span></small> <br/>
		<small class="vendors_list--shop-address"><span class="dashicons dashicons-location"></span><span><?php echo esc_html( $address ); ?></span></small><br/>
		<a class="button vendors_list--shop-link" href="<?php echo esc_url( $shop_link ); ?>"><?php esc_html_e( 'Visit Store', 'wc-vendors' ); ?></a>
	</div>

</div>
