<?php
/**
 * Core helper functions
 *
 * @package WCVendors/Functions
 */

/**
 * Get the permalink structure
 *
 * @return string The permalink structure.
 */
function wcv_get_permalink_structure() {
	$permalinks = wp_parse_args(
		(array) get_option( 'wcvendors_permalinks', array() ),
		array(
			'vendor_shop_base' => '',
		)
	);

	// Ensure that the permalinks are set.
	$permalinks['vendor_shop_base'] = untrailingslashit( empty( $permalinks['vendor_shop_base'] ) ? __( 'vendors', 'wc-vendors' ) : $permalinks['vendor_shop_base'] );

	return $permalinks;
}

/**
 * Formats the order status for localization
 *
 * @param string $order_status The order status to format.
 *
 * @since 1.0.0
 */
function wcv_format_order_status( $order_status = '' ) {
	switch ( $order_status ) {
		case 'pending':
			$order_status = __( 'Pending', 'wc-vendors' );
			break;
		case 'processing':
			$order_status = __( 'Processing', 'wc-vendors' );
			break;
		case 'on-hold':
			$order_status = __( 'On-hold', 'wc-vendors' );
			break;
		case 'completed':
			$order_status = __( 'Completed', 'wc-vendors' );
			break;
		case 'cancelled':
			$order_status = __( 'Cancelled', 'wc-vendors' );
			break;
		case 'refunded':
			$order_status = __( 'Refunded', 'wc-vendors' );
			break;
		case 'failed':
			$order_status = __( 'Failed', 'wc-vendors' );
			break;
		case 'pre-ordered':
			$order_status = __( 'Pre-ordered', 'wc-vendors' );
			break;
		case 'trash':
			$order_status = __( 'Trash', 'wc-vendors' );
			break;
		default:
			$order_status = __( 'Unknown', 'wc-vendors' );
			break;
	}

	return $order_status;
}

/**
 * Converts a GMT date into the correct format for the blog.
 *
 * Requires and returns a date in the Y-m-d H:i:s format. If there is a
 * timezone_string available, the returned date is in that timezone, otherwise
 * it simply adds the value of gmt_offset. Return format can be overridden
 * using the $format parameter
 *
 * @param string $string The date to be converted.
 * @param string $format The format string for the returned date (default is Y-m-d H:i:s).
 * @param string $timezone_string The timezone string.
 *
 * @return  string Formatted date relative to the timezone / GMT offset.
 * @version 1.0.0
 * @since   1.0.0
 */
function wcv_get_date_from_gmt( $string, $format = 'Y-m-d H:i:s', $timezone_string ) {
	$tz = $timezone_string;

	if ( empty( $timezone_string ) ) {
		$tz = get_option( 'timezone_string' );
	}

	if ( $tz && ( ! preg_match( '/UTC-/', $tz ) && ! preg_match( '/UTC+/', $tz ) ) ) {
		$datetime = date_create( $string, new DateTimeZone( 'UTC' ) );

		if ( ! $datetime ) {
			return gmdate( $format, 0 );
		}

		$datetime->setTimezone( new DateTimeZone( $tz ) );
		$string_localtime = $datetime->format( $format );
	} else {
		if ( ! preg_match( '#([0-9]{1,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})#', $string, $matches ) ) {
			return gmdate( $format, 0 );
		}

		$string_time      = gmmktime( $matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1] );
		$string_localtime = gmdate( $format, $string_time + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
	}

	return $string_localtime;
}

/**
 * Formats the order and payout dates to be consistent
 *
 * @param string $sql_date The sql date to format.
 * @param string $timezone The timezone to format to.
 *
 * @return  string $date
 * @since   1.0.0
 * @version 1.0.0
 */
function wcv_format_date( $sql_date, $timezone = '' ) {
	$date = '0000-00-00 00:00:00';

	if ( '0000-00-00 00:00:00' !== $sql_date ) {
		$date = wcv_get_date_from_gmt( $sql_date, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timezone );
	}

	return apply_filters( 'wcvendors_date_format', $date, $sql_date );
}


if ( ! function_exists( 'wcv_get_products_for_order' ) ) {
	/**
	 *
	 *
	 * @param unknown $order_id
	 *
	 * @return unknown
	 */


	function wcv_get_products_for_order( $order_id ) {
		global $wpdb;

		$vendor_products = array();

		$results = $wpdb->get_results(
			"
			SELECT product_id
			FROM {$wpdb->prefix}pv_commission
			WHERE order_id = {$order_id}
			AND     status != 'reversed'
			AND     vendor_id = " . get_current_user_id() . '
			GROUP BY product_id'
		);

		$results = apply_filters( 'wcvendors_get_vendor_products', $results );

		foreach ( $results as $value ) {
			$ids[] = $value->product_id;
		}

		return $ids;
	}
}

if ( ! function_exists( 'wcv_sum_for_orders' ) ) {
		/**
		 * Sum of orders for a specific order
		 *
		 * @param array $order_ids
		 * @param array $args (optional)
		 *
		 * @return object
		 */
	function wcv_sum_for_orders( array $order_ids, array $args = array(), $date_range = true ) {
		global $wpdb;

		$dates = ( $date_range ) ? wcv_orders_within_range() : array();

		$defaults = array(
			'status' => apply_filters( 'wcvendors_completed_statuses', array( 'completed', 'processing' ) ),
		);

		$args = wp_parse_args( $args, $defaults );

		$sql = "
			SELECT COUNT(order_id) as total_orders,
			       SUM(total_due + total_shipping + tax) as line_total,
			       SUM(qty) as qty,
			       product_id

			FROM {$wpdb->prefix}pv_commission

			WHERE   order_id IN ('" . implode( "','", $order_ids ) . "')
			AND     status != 'reversed'
		";

		if ( ! empty( $dates ) ) {
			$sql .= "
				AND     time >= '" . $dates['after'] . "'
				AND     time <= '" . $dates['before'] . "'
			";
		}

		if ( ! empty( $args['vendor_id'] ) ) {
			$sql .= "
				AND vendor_id = {$args['vendor_id']}
			";
		}

		$sql .= '
			GROUP BY order_id
			ORDER BY time DESC;
		';

		$orders = $wpdb->get_results( $sql );

		return $orders;
	}
}

if ( ! function_exists( 'wcv_orders_within_range' ) ) {

	/**
	 * Orders for range filter function
	 *
	 * @return array
	 */
	function wcv_orders_within_range() {
		global $start_date, $end_date;

		if ( ! empty( $_POST['start_date'] ) ) {
			WC()->session->set( 'wcv_order_start_date', strtotime( sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) ) );
		}

		if ( ! empty( $_POST['end_date'] ) ) {
			WC()->session->set( 'wcv_order_end_date', strtotime( sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) ) );
		}

		$start_date = WC()->session->get( 'wcv_order_start_date', strtotime( current_time( 'Y-M' ) . '-01' ) );
		$end_date   = WC()->session->get( 'wcv_order_end_date', strtotime( current_time( 'mysql' ) ) );

		$after  = gmdate( 'Y-m-d', $start_date );
		$before = gmdate( 'Y-m-d', strtotime( '+1 day', $end_date ) );

		return apply_filters(
			'wcvendors_orders_date_range',
			array(
				'after'  => $after,
				'before' => $before,
			)
		);
	}
}