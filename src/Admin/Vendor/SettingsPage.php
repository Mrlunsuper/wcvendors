<?php

namespace WCVendors\Admin\Vendor;

use WCVendors\Vendor\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SettingsPage {

	public static $dashboard_error_msg;

	public static function output() {
		$user_id          = get_current_user_id();
		$vendor           = new Vendor( $user_id );
		$paypal_address   = true;
		$shop_description = true;
		$store_name       = $vendor->get_store_name();
		$paypal_email     = $vendor->get_paypal_email();
		$description      = $vendor->get_description();
		$seller_info      = $vendor->get_info();
		$has_html         = $vendor->get_store_prop( 'pv_shop_html_enabled' );
		$shop_page        = wcv_get_storeurl( $user_id );
		$global_html      = wc_string_to_bool( get_option( 'wcvendors_display_shop_description_html', 'no' ) );
		$bank_details    = $vendor->get_bank_details();

		include WCV_ABSPATH_ADMIN . 'views/html-vendor-settings-page.php';
	}
	/**
	 *    Save shop settings
	 */
	public static function save_shop_settings() {

		$user_id   = get_current_user_id();
		$error     = false;
		$error_msg = '';

		if ( isset( $_POST['wc-vendors-nonce'] ) ) {

			if ( ! wp_verify_nonce( $_POST['wc-vendors-nonce'], 'save-shop-settings-admin' ) ) {
				return false;
			}

			$vendor = new Vendor( $user_id );

			if ( isset( $_POST['pv_paypal'] ) && '' !== $_POST['pv_paypal'] ) {
				if ( ! is_email( $_POST['pv_paypal'] ) ) {
					$error_msg .= __( 'Your PayPal address is not a valid email address.', 'wc-vendors' );
					$error      = true;
				} else {
					$vendor->set_paypal_email( sanitize_email( $_POST['pv_paypal'] ) );
				}
			} else {
				$vendor->set_paypal_email( sanitize_email( $_POST['pv_paypal'] ) );
			}

			if ( ! empty( $_POST['pv_shop_name'] ) ) {
				$users = get_users(
					array(
						'meta_key'   => 'pv_shop_slug',
						'meta_value' => sanitize_title( $_POST['pv_shop_name'] ),
					)
				);
				if ( ! empty( $users ) && $users[0]->ID != $user_id ) {
					$error_msg .= __( 'That shop name is already taken. Your shop name must be unique.', 'wc-vendors' );
					$error      = true;
				} else {
					$vendor->set_store_name( sanitize_text_field( $_POST['pv_shop_name'] ) );
					$vendor->set_slug( sanitize_title( $_POST['pv_shop_name'] ) );
				}
			}

			if ( isset( $_POST['pv_shop_description'] ) ) {
				$vendor->set_description( sanitize_textarea_field( $_POST['pv_shop_description'] ) );
			}

			if ( isset( $_POST['pv_seller_info'] ) ) {
				$vendor->set_info( sanitize_textarea_field( $_POST['pv_seller_info'] ) );
			}

			// Bank details

			$bank_account_name   = isset( $_POST['wcv_bank_account_name'] ) ? sanitize_text_field( $_POST['wcv_bank_account_name'] ) : '';
			$bank_account_number = isset( $_POST['wcv_bank_account_number'] ) ? sanitize_text_field( $_POST['wcv_bank_account_number'] ) : '';
			$bank_name           = isset( $_POST['wcv_bank_name'] ) ? sanitize_text_field( $_POST['wcv_bank_name'] ) : '';
			$bank_routing_number = isset( $_POST['wcv_bank_routing_number'] ) ? sanitize_text_field( $_POST['wcv_bank_routing_number'] ) : '';
			$bank_iban           = isset( $_POST['wcv_bank_iban'] ) ? sanitize_text_field( $_POST['wcv_bank_iban'] ) : '';
			$bank_bic_swift      = isset( $_POST['wcv_bank_bic_swift'] ) ? sanitize_text_field( $_POST['wcv_bank_bic_swift'] ) : '';

			$bank_details = array(
				'account_name'   => $bank_account_name,
				'account_number' => $bank_account_number,
				'bank_name'      => $bank_name,
				'routing_number' => $bank_routing_number,
				'iban'           => $bank_iban,
				'bic_swift'      => $bank_bic_swift,
			);

			$vendor->set_bank_details( $bank_details );
            $vendor->save();

			do_action( 'wcvendors_shop_settings_admin_saved', $user_id );

			if ( ! $error ) {
				add_action( 'admin_notices', array( self::class, 'add_admin_notice_success' ) );
			} else {
				self::$dashboard_error_msg = $error_msg;
				add_action( 'admin_notices', array( self::class, 'add_admin_notice_error' ) );
			}
		}
	}
		/**
		 * Output a sucessful message after saving the shop settings
		 *
		 * @since  1.9.9
		 * @access public
		 */
	public static function add_admin_notice_success() {

		echo '<div class="updated"><p>';
		echo __( 'Settings saved.', 'wc-vendors' );
		echo '</p></div>';

	} // add_admin_notice_success()

	/**
	 * Output an error message
	 *
	 * @since  1.9.9
	 * @access public
	 */
	public static function add_admin_notice_error() {

		echo '<div class="error"><p>';
		echo self::$dashboard_error_msg;
		echo '</p></div>';

	} // add_admin_notice_error()

}
