<?php

namespace WCVendors\Admin\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use WC_Product;

/**
 * WCV Vendor Order Page
 *
 * @author  Jamie Madden <http://wcvendors.com / https://github.com/digitalchild>
 * @package WCVendors
 * @extends WP_List_Table
 */
class OrdersTable extends \WP_List_Table {

	public $index;

	/**
	 * Current product from orders
	 *
	 * @var array
	 */
	private $current_products = array();

	/**
	 * can_view_comments
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string $can_view_comments permission check for view comments
	 */
	public $can_view_comments;

	/**
	 * can_add_comments
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string $can_add_comments permission check for add comments
	 */
	public $can_add_comments;

	/**
	 * __construct function.
	 *
	 * @access public
	 */
	function __construct() {

		global $status, $page;

		$this->index = 0;

		// Set parent defaults
		parent::__construct(
			array(
				'singular' => __( 'order', 'wc-vendors' ),
				'plural'   => __( 'orders', 'wc-vendors' ),
				'ajax'     => false,
			)
		);

		$this->can_view_comments = wc_string_to_bool( get_option( 'wcvendors_capability_order_read_notes', 'no' ) );
		$this->can_add_comments  = wc_string_to_bool( get_option( 'wcvendors_capability_order_update_notes', 'no' ) );
	}

	/**
	 * column_default function.
	 *
	 * @access public
	 *
	 * @param unknown $item
	 * @param mixed   $column_name
	 *
	 * @return unknown
	 */
	function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'order_id':
				return $item->order_id;
			case 'customer':
				return $item->customer;
			case 'products':
				return $item->products;
			case 'total':
				return $item->total;
			case 'date':
				return $item->date;
			case 'status':
				return $item->status;
			default:
				return apply_filters( 'wcvendors_vendor_order_page_column_default', '', $item, $column_name );
		}
	}


	/**
	 * column_cb function.
	 *
	 * @access public
	 *
	 * @param mixed $item
	 *
	 * @return unknown
	 */
	function column_cb( $item ) {

		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/
			'order_id',
			/*$2%s*/
			$item->order_id
		);
	}


	/**
	 * get_columns function.
	 *
	 * @access public
	 * @return unknown
	 */
	function get_columns() {

		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'order_id' => __( 'Order ID', 'wc-vendors' ),
			'customer' => __( 'Customer', 'wc-vendors' ),
			'products' => __( 'Products', 'wc-vendors' ),
			'total'    => __( 'Total', 'wc-vendors' ),
			'date'     => __( 'Date', 'wc-vendors' ),
			'status'   => __( 'Shipped', 'wc-vendors' ),
		);

		if ( ! $this->can_view_comments ) {
			unset( $columns['comments'] );
		}

		return apply_filters( 'wcvendors_vendor_order_page_get_columns', $columns );

	}


	/**
	 * get_sortable_columns function.
	 *
	 * @access public
	 * @return unknown
	 */
	function get_sortable_columns() {

		$sortable_columns = array(
			'order_id' => array( 'order_id', false ),
			'total'    => array( 'total', false ),
			'status'   => array( 'status', false ),
		);

		return $sortable_columns;
	}


	/**
	 * Get bulk actions
	 *
	 * @return unknown
	 */
	function get_bulk_actions() {

		$actions = array(
			'mark_shipped' => apply_filters( 'wcvendors_mark_shipped_label', __( 'Mark shipped', 'wc-vendors' ) ),
		);

		return $actions;
	}


	/**
	 * Process bulk actions
	 *
	 * @return unknown
	 */
	function process_bulk_action() {

		if ( ! isset( $_GET['order_id'] ) ) {
			return;
		}

		if ( is_array( $_GET['order_id'] ) ) {

			$items = array_map( 'intval', $_GET['order_id'] );

			switch ( $this->current_action() ) {
				case 'mark_shipped':
					$result = $this->mark_shipped( $items );

					if ( $result ) {
						echo '<div class="updated"><p>' . __( 'Orders marked shipped.', 'wc-vendors' ) . '</p></div>';
					}
					break;

				default:
					// code...
					break;
			}
		} else {

			if ( ! isset( $_GET['action'] ) ) {
				return;
			}
		}

	}


	/**
	 *  Mark orders as shipped
	 *
	 * @param unknown $ids (optional)
	 *
	 * @version 2.0.0
	 * @return unknown
	 */
	public function mark_shipped( $ids = array() ) {

		$user_id = get_current_user_id();

		if ( ! empty( $ids ) ) {

			foreach ( $ids as $order_id ) {
				$order      = wc_get_order( $order_id );
				$vendors    = wcv_get_vendors_from_order( $order );
				$vendor_ids = array_keys( $vendors );

				if ( ! in_array( $user_id, $vendor_ids ) ) {
					return;
				}

				$shippers = (array) get_post_meta( $order_id, 'wc_pv_shipped', true );

				if ( ! in_array( $user_id, $shippers ) ) {

					$shippers[] = $user_id;

					if ( ! empty( $mails ) ) {
						WC()->mailer()->emails['WC_Email_Notify_Shipped']->trigger( $order_id, $user_id );
					}
					do_action( 'wcvendors_vendor_ship', $order_id, $user_id, $order );
				}

				update_post_meta( $order_id, 'wc_pv_shipped', $shippers );
			}

			return true;
		}

		return false;
	}

	/**
	 *  Get Orders to display in admin
	 *
	 * @return $orders
	 */
	function get_orders( $per_page, $page_num ) {

		$user_id  = get_current_user_id();
		$orders   = array();
		$products = array();

		$vendor_products = $this->get_vendor_products( $user_id );

		foreach ( $vendor_products as $_product ) {
			$products[] = $_product->ID;
		}

		$this->current_products = $products;
		$options                = array(
			'per_page' => $per_page,
			'page_num' => $page_num,
		);
		$_orders                = $this->get_orders_for_vendor_products( $products, $options );

		$model_id = 0;

		if ( ! empty( $_orders ) ) {

			foreach ( $_orders as $_order ) {

				// Check to see that the order hasn't been deleted or in the trash
				if ( ! get_post_status( $_order->order_id ) || 'trash' === get_post_status( $_order->order_id ) ) {
					continue;
				}

				$order       = wc_get_order( $_order->order_id );
				$valid_items = wcv_get_products_for_order( $_order->order_id );
				$valid       = array();
				$items       = $order->get_items();

				foreach ( $items as $order_item_id => $item ) {
					if ( in_array( $item['variation_id'], $valid_items ) || in_array( $item['product_id'], $valid_items ) ) {
						$valid[ $order_item_id ] = $item;
					}
				}

				$products = '';

				foreach ( $valid as $order_item_id => $item ) {

					$wc_product = new WC_Product( $item['product_id'] );
					$products  .= '<strong>' . $item['qty'] . ' x ' . $item['name'] . '</strong><br />';
					$_item      = $order->get_item( $order_item_id );
					$meta_data  = $_item->get_meta_data();

					if ( ! empty( $metadata ) ) {

						$products .= '<table cellspacing="0" class="wcv_display_meta">';

						foreach ( $metadata as $meta ) {

							// Skip hidden core fields
							if ( in_array(
								$meta['meta_key'],
								apply_filters(
									'woocommerce_hidden_order_itemmeta',
									array(
										'_qty',
										'_tax_class',
										'_product_id',
										'_variation_id',
										'_line_subtotal',
										'_line_subtotal_tax',
										'_line_total',
										'_line_tax',
										'_vendor_order_item_id',
										'_vendor_commission',
										__( get_option( 'wcvendors_label_sold_by' ), 'wc-vendors' ),
									)
								)
							) ) {
								continue;
							}

							// Skip serialised meta
							if ( is_serialized( $meta['meta_value'] ) ) {
								continue;
							}

							// Get attribute data
							if ( taxonomy_exists( wc_sanitize_taxonomy_name( $meta['meta_key'] ) ) ) {
								$term               = get_term_by( 'slug', $meta['meta_value'], wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
								$meta['meta_key']   = wc_attribute_label( wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
								$meta['meta_value'] = isset( $term->name ) ? $term->name : $meta['meta_value'];
							} else {
								$meta['meta_key'] = apply_filters( 'woocommerce_attribute_label', wc_attribute_label( $meta['meta_key'], $wc_product ), $meta['meta_key'] );
							}

							$products .= '<tr><th>' . wp_kses_post( rawurldecode( $meta['meta_key'] ) ) . ':</th><td>' . rawurldecode( $meta['meta_value'] ) . '</td></tr>';
						}
						$products .= '</table>';
					}
				}

				$order_id = $order->get_id();
				$shippers = (array) get_post_meta( $order_id, 'wc_pv_shipped', true );
				$shipped  = in_array( $user_id, $shippers ) ? __( 'Yes', 'wc-vendors' ) : __( 'No', 'wc-vendors' );

				$sum = wcv_sum_for_orders( array( $order_id ), array( 'vendor_id' => get_current_user_id() ), false );
				$sum = reset( $sum );

				$total = $sum->line_total;

				$show_billing_name     = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_name', 'no' ) );
				$show_shipping_name    = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_shipping_name', 'no' ) );
				$show_billing_address  = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_billing', 'no' ) );
				$show_shipping_address = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_shipping', 'no' ) );
				$order_date            = $order->get_date_created();

				$address = $order->get_address( 'billing' );
				if ( ! $show_billing_name ) {
					unset( $address['first_name'] );
					unset( $address['last_name'] );
				}

				if ( ! $show_billing_address ) {
					unset( $address['company'] );
					unset( $address['address_1'] );
					unset( $address['address_2'] );
					unset( $address['city'] );
					unset( $address['state'] );
					unset( $address['postcode'] );
					unset( $address['country'] );
				}

				if ( ( get_option( 'woocommerce_ship_to_billing_address_only' ) === 'no' ) && ( $order->get_formatted_shipping_address() ) ) {

					$address = $order->get_address( 'shipping' );
					if ( ! $show_shipping_name ) {
						unset( $address['first_name'] );
						unset( $address['last_name'] );
					}

					if ( ! $show_shipping_address ) {
						unset( $address['company'] );
						unset( $address['address_1'] );
						unset( $address['address_2'] );
						unset( $address['city'] );
						unset( $address['state'] );
						unset( $address['postcode'] );
						unset( $address['country'] );
					}
				}

				$customer = WC()->countries->get_formatted_address( $address );

				$order_items             = array();
				$order_items['order_id'] = $order_id;
				$order_items['customer'] = $customer;
				$order_items['products'] = $products;
				$order_items['total']    = wc_price( $total );
				$order_items['date']     = date_i18n( wc_date_format(), strtotime( $order_date ) );
				$order_items['status']   = $shipped;

				$orders[] = (object) $order_items;

				$model_id ++;
			}
		}

		return $orders;

	}


	/**
	 *  Get the vendor products sold
	 *
	 * @param $user_id - the user_id to get the products of
	 *
	 * @return unknown
	 */
	public function get_vendor_products( $user_id ) {

		global $wpdb;

		$vendor_products = array();
		$sql             = '';

		$sql    .= "SELECT product_id FROM {$wpdb->prefix}pv_commission WHERE vendor_id = {$user_id} AND status != 'reversed' GROUP BY product_id";
		$results = $wpdb->get_results( $sql );
		foreach ( $results as $value ) {
			$ids[] = $value->product_id;
		}

		if ( ! empty( $ids ) ) {
			$vendor_products = get_posts(
				array(
					'numberposts' => -1,
					'orderby'     => 'post_date',
					'post_type'   => array( 'product', 'product_variation' ),
					'order'       => 'DESC',
					'include'     => $ids,
				)
			);
		}
		$vendor_products = apply_filters( 'wcvendors_get_vendor_products', $vendor_products );
		return $vendor_products;
	}


	/**
	 * All orders for a specific product
	 *
	 * @param array $product_ids
	 * @param array $args (optional)
	 *
	 * @return object
	 */
	public function get_orders_for_vendor_products( array $product_ids, array $args = array() ) {

		global $wpdb;

		if ( empty( $product_ids ) ) {
			return false;
		}

		$defaults = array(
			'status' => apply_filters( 'wcvendors_completed_statuses', array( 'completed', 'processing' ) ),
		);

		$args = wp_parse_args( $args, $defaults );

		$sql = "SELECT order_id
			FROM {$wpdb->prefix}pv_commission as order_items
			WHERE product_id IN ('" . implode( "','", $product_ids ) . "')
			AND status != 'reversed'";

		if ( ! empty( $args['vendor_id'] ) ) {
			$sql .= " AND vendor_id = {$args['vendor_id']} ";
		}

		$sql .= ' GROUP BY order_id ORDER BY time DESC';

		$sql   .= ' LIMIT ' . $args['per_page'] . ' OFFSET ' . ( $args['page_num'] - 1 ) * $args['per_page'];
		$orders = $wpdb->get_results( $sql );

		return $orders;
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return int
	 */
	public function record_count() {
		global $wpdb;

		$user_id = get_current_user_id();
		$sql     = "SELECT c.order_id
		FROM {$wpdb->prefix}pv_commission c INNER JOIN {$wpdb->prefix}posts p ON c.order_id = p.ID AND p.post_type = 'shop_order' AND p.post_status != 'trash' AND c.status != 'reversed' AND c.product_id IN ('" . implode( "','", $this->current_products ) . "') AND c.vendor_id = $user_id ";
		$sql    .= ' GROUP BY c.order_id';

		return count( $wpdb->get_results( $sql ) );
	}

	/**
	 * prepare_items function.
	 *
	 * @access public
	 */
	public function prepare_items() {

		/**
		 * Init column headers
		 */
		$this->_column_headers = $this->get_column_info();

		/**
		 * Get current page number and per page
		 */
		$per_page = $this->get_items_per_page( 'orders_per_page', 10 );
		$page_num = $this->get_pagenum();

		/**
		 * Get items
		 */

		$this->items = $this->get_orders( $per_page, $page_num );

		/**
		 * Count total record
		 */
		$record_count = $this->record_count();

		/**
		 * Process bulk actions
		 */
		$this->process_bulk_action();

		/**
		 * Pagination
		 */
		$this->set_pagination_args(
			array(
				'total_items' => $record_count,
				'per_page'    => $per_page,
				'totals_page' => ceil( $record_count / $per_page ),
			)
		);
	}
}
