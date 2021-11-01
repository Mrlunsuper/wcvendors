<?php
/**
 * WC Vendors Admin Functions
 *
 * @package WCVendors/Functions
 */

/**
 * Get all WC Vendors screen ids.
 *
 * @return array
 */
function wcv_get_screen_ids() {

	$wc_screen_id = sanitize_title( __( 'WC Vendors', 'wc-vendors' ) );
	$screen_ids   = array(
		'toplevel_page_' . $wc_screen_id,
		$wc_screen_id . '_page_wcv-commissions',
		$wc_screen_id . '_page_wcv-vendors',
		$wc_screen_id . '_page_wcv-settings',
		$wc_screen_id . '_page_wcv-addons',
	);

	return apply_filters( 'wcvendors_screen_ids', $screen_ids );
}

/**
 * Output a single select page drop down
 *
 * @param int    $id the css ID.
 * @param string $value the value for the dropdown.
 * @param string $class the CSS class.
 * @param string $css Extra CSS styles.
 */
function wcv_single_select_page( $id, $value, $class = '', $css = '' ) {

	$dropdown_args = array(
		'name'             => $id,
		'id'               => $id,
		'sort_column'      => 'menu_order',
		'sort_order'       => 'ASC',
		'show_option_none' => ' ',
		'class'            => $class,
		'echo'             => false,
		'selected'         => $value,
	);

	echo esc_html( str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'wc-vendors' ) . "' style='" . $css . "' class='" . $class . "' id=", wp_dropdown_pages( $dropdown_args ) ) );
}
