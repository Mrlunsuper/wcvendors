<?php

namespace WCVendors\Admin\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class SettingsPage {

	public static $dashboard_error_msg;

	public static function output() {
		$user_id          = get_current_user_id();
		$paypal_address   = true;
		$shop_description = true;
		$description      = get_user_meta( $user_id, 'pv_shop_description', true );
		$seller_info      = get_user_meta( $user_id, 'pv_seller_info', true );
		$has_html         = get_user_meta( $user_id, 'pv_shop_html_enabled', true );
		$shop_page        = wcv_get_vendor_shop_page( wp_get_current_user()->user_login );
		$global_html      = wc_string_to_bool( get_option( 'wcvendors_display_shop_description_html', 'no' ) );
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

			if ( isset( $_POST['pv_paypal'] ) && '' !== $_POST['pv_paypal'] ) {
				if ( ! is_email( $_POST['pv_paypal'] ) ) {
					$error_msg .= __( 'Your PayPal address is not a valid email address.', 'wc-vendors' );
					$error      = true;
				} else {
					update_user_meta( $user_id, 'pv_paypal', $_POST['pv_paypal'] );
				}
			} else {
				update_user_meta( $user_id, 'pv_paypal', $_POST['pv_paypal'] );
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
					update_user_meta( $user_id, 'pv_shop_name', $_POST['pv_shop_name'] );
					update_user_meta( $user_id, 'pv_shop_slug', sanitize_title( $_POST['pv_shop_name'] ) );
				}
			}

			if ( isset( $_POST['pv_shop_description'] ) ) {
				update_user_meta( $user_id, 'pv_shop_description', $_POST['pv_shop_description'] );
			}

			if ( isset( $_POST['pv_seller_info'] ) ) {
				update_user_meta( $user_id, 'pv_seller_info', $_POST['pv_seller_info'] );
			}

			// Bank details
			if ( isset( $_POST['wcv_bank_account_name'] ) ) {
				update_user_meta( $user_id, 'wcv_bank_account_name', $_POST['wcv_bank_account_name'] );
			}
			if ( isset( $_POST['wcv_bank_account_number'] ) ) {
				update_user_meta( $user_id, 'wcv_bank_account_number', $_POST['wcv_bank_account_number'] );
			}
			if ( isset( $_POST['wcv_bank_name'] ) ) {
				update_user_meta( $user_id, 'wcv_bank_name', $_POST['wcv_bank_name'] );
			}
			if ( isset( $_POST['wcv_bank_routing_number'] ) ) {
				update_user_meta( $user_id, 'wcv_bank_routing_number', $_POST['wcv_bank_routing_number'] );
			}
			if ( isset( $_POST['wcv_bank_iban'] ) ) {
				update_user_meta( $user_id, 'wcv_bank_iban', $_POST['wcv_bank_iban'] );
			}
			if ( isset( $_POST['wcv_bank_bic_swift'] ) ) {
				update_user_meta( $user_id, 'wcv_bank_bic_swift', $_POST['wcv_bank_bic_swift'] );
			}

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
