<?php
/**
 * WCV_Admin_Reports class.
 *
 * Shows reports related to software in the woocommerce backend
 *
 * @author Matt Gates <http://mgates.me>
 * @package
 */


class WCV_Admin_Reports {


	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 *
	 * @param bool $debug (optional) (default: false)
	 */
	public function __construct( $debug = false ) {

		add_filter( 'woocommerce_admin_reports', array( $this, 'reports_tab' ) );
	}

	/**
	 * reports_tab function.
	 *
	 * @access public
	 *
	 * @param unknown $reports
	 *
	 * @return void
	 */
	public function reports_tab( $reports ) {

		$reports['vendors'] = array(
			'title'  => __( 'WC Vendors', 'wc-vendors' ),
			'charts' => array(
				array(
					'title'       => __( 'Overview', 'wc-vendors' ),
					'description' => '',
					'hide_title'  => true,
					'function'    => array( $this, 'sales' ),
				),
				array(
					'title'       => sprintf( __( 'Commission By %s', 'wc-vendors' ), wcv_get_vendor_name( true, true ) ),
					'description' => '',
					'hide_title'  => true,
					'function'    => array( $this, 'commission' ),
				),
				array(
					'title'       => __( 'Commission By Product', 'wc-vendors' ),
					'description' => '',
					'hide_title'  => true,
					'function'    => array( $this, 'commission' ),
				),
				array(
					'title'       => __( 'Commission Totals', 'wc-vendors' ),
					'description' => __( 'Commission totals for all vendors includes shipping and taxes. By default no date range is used and all due commissions are returned. Use the date range to filter.', 'wc-vendors' ),
					'hide_title'  => true,
					'function'    => array( $this, 'commission_totals' ),
				),
			),
		);

		return apply_filters( 'wcvendors_admin_reports_tab', $reports );
	}


	/**
	 *
	 */
	public function sales() {

		global $start_date, $end_date, $woocommerce, $wpdb;

		$commission_status_labels = WCV_Commission::commission_status();

		$start_date = ! empty( $_POST['start_date'] ) ? $_POST['start_date'] : strtotime( gmdate( 'Ymd', strtotime( gmdate( 'Ym', current_time( 'timestamp' ) ) . '01' ) ) );
		$end_date   = ! empty( $_POST['end_date'] ) ? $_POST['end_date'] : strtotime( gmdate( 'Ymd', current_time( 'timestamp' ) ) );

		if ( ! empty( $_POST['start_date'] ) ) {
			$start_date = strtotime( $_POST['start_date'] );
		}

		if ( ! empty( $_POST['end_date'] ) ) {
			$end_date = strtotime( $_POST['end_date'] );
		}

		$after  = gmdate( 'Y-m-d', $start_date );
		$before = gmdate( 'Y-m-d', strtotime( '+1 day', $end_date ) );

		$commission_due = $wpdb->get_var(
			"
			SELECT SUM(total_due + total_shipping + tax) FROM {$wpdb->prefix}pv_commission WHERE status = 'due'
			AND     time >= '" . $after . "'
			AND     time <= '" . $before . "'
		"
		);

		$reversed = $wpdb->get_var(
			"
			SELECT SUM(total_due + total_shipping + tax) FROM {$wpdb->prefix}pv_commission WHERE status = 'reversed'
			AND     time >= '" . $after . "'
			AND     time <= '" . $before . "'
		"
		);

		$paid = $wpdb->get_var(
			"
			SELECT SUM(total_due + total_shipping + tax) FROM {$wpdb->prefix}pv_commission WHERE status = 'paid'
			AND     time >= '" . $after . "'
			AND     time <= '" . $before . "'
		"
		);

		?>

		<form method="post" action="">
			<p><label for="from"><?php _e( 'From:', 'wc-vendors' ); ?></label>
				<input type="text" size="9" placeholder="yyyy-mm-dd"
					   value="<?php echo esc_attr( gmdate( 'Y-m-d', $start_date ) ); ?>" name="start_date"
					   class="range_datepicker from" id="from"/>
				<label for="to"><?php _e( 'To:', 'wc-vendors' ); ?></label>
				<input type="text" size="9" placeholder="yyyy-mm-dd"
					   value="<?php echo esc_attr( gmdate( 'Y-m-d', $end_date ) ); ?>" name="end_date"
					   class="range_datepicker to" id="to"/>
				<input type="submit" class="button" value="<?php _e( 'Show', 'wc-vendors' ); ?>"/></p>
		</form>

		<div id="poststuff" class="woocommerce-reports-wrap">
			<div class="woocommerce-reports-sidebar">
				<div class="postbox">
					<h3><span><?php _e( 'Total Paid In Range', 'wc-vendors' ); ?></span></h3>

					<div class="inside">
						<p class="stat">
						<?php
						if ( $paid > 0 ) {
							echo wc_price( $paid );
						} else {
							_e( 'n/a', 'wc-vendors' );
						}
						?>
						</p>
					</div>
				</div>
				<div class="postbox">
					<h3><span><?php _e( 'Total Due In Range', 'wc-vendors' ); ?></span></h3>

					<div class="inside">
						<p class="stat">
							<?php
							if ( $commission_due > 0 ) {
								echo wc_price( $commission_due );
							} else {
								_e( 'n/a', 'wc-vendors' );
							}
							?>
						</p>
					</div>
				</div>
				<div class="postbox">
					<h3><span><?php _e( 'Total Reversed In Range', 'wc-vendors' ); ?></span></h3>

					<div class="inside">
						<p class="stat">
							<?php
							if ( $reversed > 0 ) {
								echo wc_price( $reversed );
							} else {
								_e( 'n/a', 'wc-vendors' );
							}
							?>
						</p>
					</div>
				</div>
			</div>

			<div class="woocommerce-reports-main">
				<div class="postbox">
					<h3><span><?php _e( 'Recent Commission', 'wc-vendors' ); ?></span></h3>

					<div>
							<?php
							$commission = $wpdb->get_results(
								"
								SELECT * FROM {$wpdb->prefix}pv_commission
								WHERE     time >= '" . $after . "'
								AND     time <= '" . $before . "'
								ORDER BY time DESC
							"
							);

							if ( sizeof( $commission ) > 0 ) {

								?>
							<div class="woocommerce_order_items_wrapper">
								<table id="commission-table" class="woocommerce_order_items" cellspacing="0">
									<thead>
									<tr>
										<th><?php _e( 'Order', 'wc-vendors' ); ?></th>
										<th><?php _e( 'Product', 'wc-vendors' ); ?></th>
										<th><?php printf( __( '%s', 'wc-vendors' ), wcv_get_vendor_name() ); ?></th>
										<th><?php _e( 'Total', 'wc-vendors' ); ?></th>
										<th><?php _e( 'Date &amp; Time', 'wc-vendors' ); ?></th>
										<th><?php _e( 'Status', 'wc-vendors' ); ?></th>
									</tr>
									</thead>
									<tbody>
								<?php
								$i = 1;
								foreach ( $commission as $row ) :
									$i ++
									?>
										<tr
										<?php
										if ( $i % 2 == 1 ) {
											echo ' class="alternate"';
										}
										?>
										>
											<td>
											<?php
											if ( $row->order_id ) :
												?>
													<a
															href="<?php echo admin_url( 'post.php?post=' . $row->order_id . '&action=edit' ); ?>"><?php echo $row->order_id; ?></a>
													<?php
												else :
													_e( 'N/A', 'wc-vendors' );
												endif;
												?>
											</td>
											<td><?php echo get_the_title( $row->product_id ); ?></td>
											<td><?php echo WCV_Vendors::get_vendor_shop_name( $row->vendor_id ); ?></td>
											<td><?php echo wc_price( $row->total_due + $row->total_shipping + $row->tax ); ?></td>
											<td><?php echo date_i18n( __( 'D j M Y \a\t h:ia', 'wc-vendors' ), strtotime( $row->time ) ); ?></td>
											<td><?php echo $commission_status_labels[ $row->status ]; ?></td>
										</tr>
									<?php endforeach; ?>
									</tbody>
								</table>
							</div>
									<?php
							} else {
								?>
							<p><?php _e( 'No commission yet', 'wc-vendors' ); ?></p>
								<?php
							}
							?>
					</div>
				</div>
			</div>
		</div>
			<?php

	}


	/**
	 * Output the report.
	 */
	public function commission() {
		global $start_date, $end_date;

		$show_start_date = isset( $_POST['show_start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['show_start_date'] ) ) : $start_date;
		$show_end_date   = isset( $_POST['show_end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['show_end_date'] ) ) : $end_date;

		$vendors         = get_users( array( 'role' => 'vendor' ) );
		$vendors         = apply_filters( 'pv_commission_vendors_list', $vendors );
		$selected_vendor = ! empty( $_POST['show_vendor'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['show_vendor'] ) ) : false;
		$products        = ! empty( $_POST['product_ids'] ) ? $_POST['product_ids'] : array();
		$report_type     = isset( $_GET['report'] ) ? sanitize_text_field( wp_unslash( $_GET['report'] ) ) : 1;

		$products = array_map( 'esc_attr', $products );
		?>

		<form method="post" action="" class="report_filters">

			<label for="show_start_date"><?php esc_html_e( 'From:', 'wc-vendors' ); ?></label>
			<input type="text" name="show_start_date" id="show_start_date" value="<?php echo esc_attr( $show_start_date ); ?>" class="range_datepicker from hasDatePicker" placeholder="<?php esc_attr_e( 'yyy-mm-dd', 'wc-vendors' ); ?>" />
			<label for="show_end_date"><?php esc_html_e( 'To:', 'wc-vendors' ); ?></label>
			<input type="text" name="show_end_date" id="show_end_date" value="<?php echo esc_attr( $show_end_date ); ?>" class="range_datepicker to hasDatePicker" placeholder="<?php esc_attr_e( 'yyyy-mm-dd', 'wc-vendors' ); ?>" />
		<?php
		if ( 2 === absint( $report_type ) ) {
			?>
			<select id="product_ids" name="product_ids[]" class="wc-product-search ajax_chosen_select_products"
					multiple="multiple"
					data-placeholder="<?php esc_attr_e( 'Type in a product name to start searching...', 'wc-vendors' ); ?>"
					style="width: 400px;">
				<?php foreach ( $products as $product_id ) { ?>
					<?php
					$product = wc_get_product( $product_id );
					if ( is_object( $product ) ) :
						?>
						<option value="<?php echo esc_attr( $product_id ); ?>" selected="selected"><?php echo esc_html( $product->get_formatted_name() ); ?></option>
						<?php
					endif;
				}
				?>
			</select>
			<?php
		} else {
			?>
				<select class="chosen_select" id="show_vendor" name="show_vendor" style="width: 300px;"
						data-placeholder="<?php echo sprintf( esc_attr( 'Select a %s&hellip;', 'wc-vendors' ), wcv_get_vendor_name() ); ?>">
					<option></option>
					<?php
					foreach ( $vendors as $key => $vendor ) {
						printf( '<option value="%s" %s>%s</option>', $vendor->ID, selected( $selected_vendor, $vendor->ID, false ), $vendor->display_name );
					}
					?>
				</select>
			<?php } ?>
			<input type="submit" class="button" value="<?php esc_attr_e( 'Show', 'wc-vendors' ); ?>"/>
		</form>

			<?php
			if ( 1 === absint( $report_type ) ) {
				$this->show_commission_by_vendor( $selected_vendor, $show_start_date, $show_end_date );
			} else {
				$this->show_commission_by_products( $products, $show_start_date, $show_end_date );
			}
	}


	/**
	 *  Commission Totals for vendors reports
	 *
	 * @since    1.8.4
	 */
	public function commission_totals() {

		global $total_start_date, $total_end_date, $wpdb;

		$total_start_date  = ! empty( $_POST['total_start_date'] ) ? $_POST['total_start_date'] : '';
		$total_end_date    = ! empty( $_POST['total_end_date'] ) ? $_POST['total_end_date'] : '';
		$commission_status = ! empty( $_POST['commission_status'] ) ? $_POST['commission_status'] : 'due';
		$date_sql          = ( ! empty( $_POST['total_start_date'] ) && ! empty( $_POST['total_end_date'] ) ) ? " time BETWEEN '$total_start_date 00:00:00' AND '$total_end_date 23:59:59' AND" : '';

		$status_sql = " status='$commission_status'";

		$sql = "SELECT vendor_id, total_due, total_shipping, tax, status FROM {$wpdb->prefix}pv_commission WHERE";

		$commissions = $wpdb->get_results( $sql . $date_sql . $status_sql );

		if ( ! empty( $_POST['total_start_date'] ) ) {
			$total_start_date = strtotime( $_POST['total_start_date'] );
		}

		if ( ! empty( $_POST['total_end_date'] ) ) {
			$total_end_date = strtotime( $_POST['total_end_date'] );
		}

		$totals = $this->calculate_totals( $commissions );

		?>
		<form method="post" action="">
			<p><label for="from"><?php _e( 'From:', 'wc-vendors' ); ?></label>
				<input type="text" size="9" placeholder="yyyy-mm-dd"
					   value="<?php echo esc_attr( wp_date( 'Y-m-d', $total_start_date ) ); ?>" name="total_start_date"
					   class="range_datepicker from" id="from"/>
				<label for="to"><?php _e( 'To:', 'wc-vendors' ); ?></label>
				<input type="text" size="9" placeholder="yyyy-mm-dd"
					   value="<?php echo esc_attr( wp_date( 'Y-m-d', $total_end_date ) ); ?>" name="total_end_date"
					   class="range_datepicker to" id="to"/>

				<select name="commission_status">
					<option value="due"><?php _e( 'Due', 'wc-vendors' ); ?></option>
					<option value="paid"><?php _e( 'Paid', 'wc-vendors' ); ?></option>
					<option value="reversed"><?php _e( 'Reversed', 'wc-vendors' ); ?></option>
				</select>

				<input type="submit" class="button" value="<?php _e( 'Show', 'wc-vendors' ); ?>"/>

			<?php do_action( 'wcvendors_after_commission_reports', $commissions ); ?>
			</p>
		</form>

		<div class="woocommerce-reports-main">
		<table class="widefat">
			<thead>
			<tr>
				<th class="total_row"><?php printf( __( '%s', 'wc-vendors' ), wcv_get_vendor_name() ); ?></th>
				<th class="total_row"><?php _e( 'Tax Total', 'wc-vendors' ); ?></th>
				<th class="total_row"><?php _e( 'Shipping Total', 'wc-vendors' ); ?></th>
				<th class="total_row"><?php _e( 'Status', 'wc-vendors' ); ?></th>
				<th class="total_row"><?php _e( 'Commission Total', 'wc-vendors' ); ?></th>
			</tr>
			</thead>
			<tbody>
		<?php

		if ( ! empty( $commissions ) ) {

			foreach ( $totals as $totals ) {

				echo '<tr>';
				echo '<td>' . $totals['user_login'] . '</td>';
				echo '<td>' . wc_price( $totals['tax'] ) . '</td>';
				echo '<td>' . wc_price( $totals['total_shipping'] ) . '</td>';
				echo '<td>' . $totals['status'] . '</td>';
				echo '<td>' . wc_price( $totals['total_due'] ) . '</td>';
				echo '</tr>';

			}
		} else {
			echo '<tr>';
			echo '<td colspan="5">' . __( 'No commissions found.', 'wc-vendors' ) . '</td>';
			echo '</tr>';

		}
		?>
			</tbody>
		</table>

		<?php

	} // commission_totals()

	/**
	 *   Calculate the totals of the commissions return an array with vendor id as the key with the totals
	 *
	 * @param    array $commissions total commissions array
	 *
	 * @return   array $totals    calculated totals
	 */
	public function calculate_totals( $commissions ) {

		$totals = array();

		$vendors      = get_users(
			array(
				'role'   => 'vendor',
				'fields' => array( 'ID', 'user_login' ),
			)
		);
		$vendor_names = wp_list_pluck( $vendors, 'user_login', 'ID' );

		foreach ( $commissions as $commission ) {

			if ( array_key_exists( $commission->vendor_id, $totals ) ) {

				$totals[ $commission->vendor_id ]['total_due']      += $commission->total_due + $commission->tax + $commission->total_shipping;
				$totals[ $commission->vendor_id ]['tax']            += $commission->tax;
				$totals[ $commission->vendor_id ]['total_shipping'] += $commission->total_shipping;

			} else {

				if ( array_key_exists( $commission->vendor_id, $vendor_names ) ) {

					$temp_array = array(
						'user_login'     => $vendor_names[ $commission->vendor_id ],
						'total_due'      => $commission->total_due + $commission->tax + $commission->total_shipping,
						'tax'            => $commission->tax,
						'total_shipping' => $commission->total_shipping,
						'status'         => $commission->status,
					);

					$totals[ $commission->vendor_id ] = $temp_array;

				}
			}
		}

		usort(
			$totals,
			function ( $a, $b ) {

				return strcmp( strtolower( $a['user_login'] ), strtolower( $b['user_login'] ) );
			}
		);

		return $totals;

	} // calculate_totals()

	/**
	 * Show report for a specific vendor
	 *
	 * @param int    $vendor_id Vendor ID.
	 * @param string $show_start_date Start date.
	 * @param string $show_end_date End date.
	 * @since 2.3.4
	 */
	public function show_commission_by_vendor( $vendor_id, $show_start_date, $show_end_date ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

		$sql_query = "SELECT total_shipping, tax, total_due, DATE( time ) as date, status  FROM {$table_name} WHERE vendor_id = {$vendor_id}";

		if ( empty( $vendor_id ) ) {
			return;
		}

		if ( ! empty( $show_start_date ) && ! empty( $show_end_date ) ) {
			$sql_query .= $wpdb->prepare( ' AND ( DATE_FORMAT(time, "%%Y-%%m-%%d") BETWEEN %s AND %s )', $show_start_date, $show_end_date );
		} elseif ( ! empty( $show_start_date ) ) {
			$sql_query .= $wpdb->prepare( ' AND DATE_FORMAT(time, "%%Y-%%m-%%d")  = %s', $show_start_date );
		}

		$sql_query .= ' ORDER BY date DESC';

		$commissions          = $wpdb->get_results( $sql_query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$formatted_commission = $this->get_commission_by_month( $commissions );

		if ( ! empty( $formatted_commission ) ) {
			?>
			<div class="woocommerce-reports-main">
				<table class="widefat">
					<thead>
					<tr>
						<th class="total_row"><?php esc_html_e( 'Date / Month', 'wc-vendors' ); ?></th>
						<th class="total_row"><?php esc_html_e( 'Commission Total', 'wc-vendors' ); ?></th>
						<th class="total_row"><?php esc_html_e( 'Tax Total', 'wc-vendors' ); ?></th>
						<th class="total_row"><?php esc_html_e( 'Shipping Total', 'wc-vendors' ); ?></th>
						<th class="total_row"><?php esc_html_e( 'Total Due', 'wc-vendors' ); ?></th>
						<th class="total_row"><?php esc_html_e( 'Total Paid', 'wc-vendors' ); ?></th>
						<th class="total_row"><?php esc_html_e( 'Total Reversed', 'wc-vendors' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					$all_total_commission = 0;
					$all_total_due        = 0;
					$all_tax              = 0;
					$all_shipping         = 0;
					$all_paid             = 0;
					$all_reversed         = 0;
					// @codingStandardsIgnoreStart
					foreach ( $formatted_commission as $month => $date ) {
						$month_total_commission = 0;
						$month_total_due        = 0;
						$month_total_paid       = 0;
						$month_total_rev        = 0;
						$month_total_shipping   = 0;
						$month_total_tax        = 0;
						foreach ( $date as $d => $commission ) {
							$month_total_commission += $commission['commission'];
							$month_total_due        += $commission['due'];
							$month_total_paid       += $commission['paid'];
							$month_total_rev        += $commission['reversed'];
							$month_total_shipping   += $commission['shipping'];
							$month_total_tax        += $commission['tax'];

							echo '<tr>';
							echo '<td>' . $d . '</td>';
							echo '<td>' . wc_price( $commission['commission'] ) . '</td>';
							echo '<td>' . wc_price( $commission['tax'] ) . '</td>';
							echo '<td>' . wc_price( $commission['shipping'] ) . '</td>';
							echo '<td>' . wc_price( $commission['due'] ) . '</td>';
							echo '<td>' . wc_price( $commission['paid'] ) . '</td>';
							echo '<td>' . wc_price( $commission['reversed'] ) . '</td>';

							echo '</tr>';
						}
						echo '<tr class="total_row">';
						echo '<td><strong>' . date( 'M', strtotime( $month . '-01' ) ) . '</strong></td>';
						echo '<td><strong>' . wc_price( $month_total_commission ) . '</strong></td>';
						echo '<td><strong>' . wc_price( $month_total_tax ) . '</strong></td>';
						echo '<td><strong>' . wc_price( $month_total_shipping ) . '</strong></td>';
						echo '<td><strong>' . wc_price( $month_total_due ) . '</strong></td>';
						echo '<td><strong>' . wc_price( $month_total_paid ) . '</strong></td>';
						echo '<td><strong>' . wc_price( $month_total_rev ) . '</strong></td>';
						echo '</tr>';
						$all_total_commission += $month_total_commission;
						$all_paid             += $month_total_paid;
						$all_reversed         += $month_total_rev;
						$all_total_due        += $month_total_due;
						$all_tax              += $month_total_tax;
						$all_shipping         += $month_total_shipping;

					}
					echo '<tfoot>';
					echo '<tr>';
					echo '<td>' . esc_html__( 'Total', 'wc-vendors' ) . '</td>';
					echo '<td>' . wc_price( $all_total_commission ) . '</td>';
					echo '<td>' . wc_price( $all_tax ) . '</td>';
					echo '<td>' . wc_price( $all_shipping ) . '</td>';
					echo '<td>' . wc_price( $all_total_due ) . '</td>';
					echo '<td>' . wc_price( $all_paid ) . '</td>';
					echo '<td>' . wc_price( $all_reversed ) . '</td>';
					echo '</tr>';
					echo '</tfoot>';
					// @codingStandardsIgnoreEnd
					?>
					</tbody>
				</table>
			</div>
			<?php
		} else {
			echo '<p>' . esc_html__( 'No commissions found', 'wc-vendors' ) . '</p>';
		}

	}

	/**
	 * Show report for products
	 *
	 * @since 2.3.4
	 */
	public function show_commission_by_products( $product_ids, $show_start_date, $show_end_date ) {

		global $wpdb;

		if ( empty( $product_ids ) ) {
			return;
		}
		$joined_product_ids = implode( ',', $product_ids );
		$table_name         = $wpdb->prefix . 'pv_commission';

		$sql_query = "SELECT total_shipping, tax, total_due, product_id, DATE(time) as date, status FROM {$table_name} WHERE product_id IN (  {$joined_product_ids} )";

		if ( ! empty( $show_start_date ) && ! empty( $show_end_date ) ) {
			$sql_query .= $wpdb->prepare( ' AND ( time BETWEEN %s AND %s )', $show_start_date, $show_end_date );

		} elseif ( ! empty( $show_start_date ) ) {
			$sql_query .= $wpdb->prepare( ' AND DATE( time ) = DATE(  %s )', $show_start_date );
		}

		$sql_query .= ' ORDER BY date DESC ';

		$commissions = $wpdb->get_results( $sql_query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$formatted_commission = $this->get_commission_by_product( $commissions );

		if ( ! empty( $formatted_commission ) ) {
			?>
			<div class="woocommerce-reports-main">
				<table class="widefat">
					<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'wc-vendors' ); ?></th>
						<th><?php esc_html_e( 'Product', 'wc-vendors' ); ?></th>
						<th><?php esc_html_e( 'Total Commission', 'wc-vendors' ); ?></th>
						<th><?php esc_html_e( 'Total Tax', 'wc-vendors' ); ?></th>
						<th><?php esc_html_e( 'Total Shipping', 'wc-vendors' ); ?></th>
						<th><?php esc_html_e( 'Total Due', 'wc-vendors' ); ?></th>
						<th><?php esc_html_e( 'Total Paid', 'wc-vendors' ); ?></th>
						<th><?php esc_html_e( 'Total Reversed', 'wc-vendors' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					$all_total_commission = 0;
					$all_paid             = 0;
					$all_reversed         = 0;
					$all_total_due        = 0;
					$all_tax              = 0;
					$all_shipping         = 0;
					// @codingStandardsIgnoreStart
					foreach ( $formatted_commission as $product_id => $product_commission ) {

						$product_name             = get_the_title( $product_id );
						$product_total_commission = 0;
						$product_total_paid       = 0;
						$product_total_rev        = 0;
						$product_total_due        = 0;
						$product_total_tax        = 0;
						$product_total_shipping   = 0;
						foreach ( $product_commission as $date => $commission ) {
							$product_total_commission += $commission['commission'];
							$product_total_paid       += $commission['paid'];
							$product_total_rev        += $commission['reversed'];
							$product_total_due        += $commission['due'];
							$product_total_tax        += $commission['tax'];
							$product_total_shipping   += $commission['shipping'];

							echo '<tr>';
							echo '<td>' . esc_html( $date ) . '</td>';
							echo '<td>' . esc_html( $product_name ) . '</td>';
							echo '<td>' . wc_price( $commission['commission'] ) . '</td>';
							echo '<td>' . wc_price( $commission['tax'] ) . '</td>';
							echo '<td>' . wc_price( $commission['shipping'] ) . '</td>';
							echo '<td>' . wc_price( $commission['due'] ) . '</td>';
							echo '<td>' . wc_price( $commission['paid'] ) . '</td>';
							echo '<td>' . wc_price( $commission['reversed'] ) . '</td>';
							echo '</tr>';

						}
						$all_total_commission += $product_total_commission;
						$all_paid             += $product_total_paid;
						$all_reversed         += $product_total_rev;
						$all_total_due        += $product_total_due;
						$all_tax              += $product_total_tax;
						$all_shipping         += $product_total_shipping;
						echo '<tr>';
						echo '<td><strong>' . $product_name . '</strong></td>';
						echo '<td><strong>-</strong></td>';
						echo '<td><strong>' . wc_price( $product_total_commission ) . '</strong></td>';
						echo '<td><strong>' . wc_price( $product_total_tax ) . '</strong></td>';
						echo '<td><strong>' . wc_price( $product_total_shipping ) . '</strong></td>';
						echo '<td><strong>' . wc_price( $product_total_due ) . '</strong></td>';
						echo '<td><strong>' . wc_price( $product_total_paid ) . '</strong></td>';
						echo '<td><strong>' . wc_price( $product_total_rev ) . '</strong></td>';
						echo '</tr>';

					}
					echo '<tfoot>';
					echo '<tr class="total_row">';
					echo '<td>' . esc_html__( 'Total', 'wc-vendors' ) . '</td>';
					echo '<td></td>';
					echo '<td>' . wc_price( $all_total_commission ) . '</td>';
					echo '<td>' . wc_price( $all_tax ) . '</td>';
					echo '<td>' . wc_price( $all_shipping ) . '</td>';
					echo '<td>' . wc_price( $all_total_due ) . '</td>';
					echo '<td>' . wc_price( $all_paid ) . '</td>';
					echo '<td>' . wc_price( $all_reversed ) . '</td>';
					echo '</tr>';
					echo '</tfoot>';
					// @codingStandardsIgnoreEnd
					?>
					</tbody>
				</table>
			</div>
			<?php

		} else {
			echo '<p>' . esc_html__( 'No commission data found', 'wc-vendors' ) . '</p>';
		}

	}


	/**
	 * Get commission by month
	 *
	 * @param  array $commissions Array of commissions.
	 * @return array
	 */
	private function get_commission_by_month( $commissions ) {
		$month_array       = array();
		$count_commissions = count( $commissions );
		for ( $i = 0; $i < $count_commissions; $i++ ) {

			$date  = $commissions[ $i ]['date'];
			$month = gmdate( 'Y-m', strtotime( $date ) );

			if ( ! isset( $month_array[ $month ] ) ) {
				$month_array[ $month ] = array();
			}
			if ( ! isset( $month_array[ $month ][ $date ] ) ) {
				$month_array[ $month ][ $date ] = array(
					'tax'        => 0,
					'shipping'   => 0,
					'due'        => 0,
					'paid'       => 0,
					'reversed'   => 0,
					'commission' => 0,
				);
			}
			$month_array[ $month ][ $date ]['tax']      += $commissions[ $i ]['tax'];
			$month_array[ $month ][ $date ]['shipping'] += $commissions[ $i ]['total_shipping'];

			if ( $commissions[ $i ]['status'] === 'due' ) {
				$month_array[ $month ][ $date ]['due'] += $commissions[ $i ]['total_due'];
			} elseif ( $commissions[ $i ]['status'] === 'paid' ) {
				$month_array[ $month ][ $date ]['paid'] += $commissions[ $i ]['total_due'];
			} elseif ( $commissions[ $i ]['status'] === 'reversed' ) {
				$month_array[ $month ][ $date ]['reversed'] += $commissions[ $i ]['total_due'];
			}
			$month_array[ $month ][ $date ]['commission'] += $commissions[ $i ]['total_due'] + $commissions[ $i ]['total_shipping'] + $commissions[ $i ]['tax'];
		}
		return $month_array;
	}

	/**
	 * Get commission by product
	 *
	 * @param  array $commissions Array of commissions.
	 * @return array
	 */
	private function get_commission_by_product( $commissions ) {
		$product_array     = array();
		$count_commissions = count( $commissions );
		for ( $i = 0; $i < $count_commissions; $i++ ) {
			$product_id = $commissions[ $i ]['product_id'];
			$date       = $commissions[ $i ]['date'];
			if ( ! isset( $product_array[ $product_id ] ) ) {
				$product_array[ $product_id ] = array();
			}
			if ( ! isset( $product_array[ $product_id ][ $date ] ) ) {
				$product_array[ $product_id ][ $date ] = array(
					'tax'        => 0,
					'shipping'   => 0,
					'due'        => 0,
					'paid'       => 0,
					'reversed'   => 0,
					'commission' => 0,
				);
			}
			$product_array[ $product_id ][ $date ]['tax']      += $commissions[ $i ]['tax'];
			$product_array[ $product_id ][ $date ]['shipping'] += $commissions[ $i ]['total_shipping'];

			if ( $commissions[ $i ]['status'] === 'due' ) {
				$product_array[ $product_id ][ $date ]['due'] += $commissions[ $i ]['total_due'];
			} elseif ( $commissions[ $i ]['status'] === 'paid' ) {
				$product_array[ $product_id ][ $date ]['paid'] += $commissions[ $i ]['total_due'];
			} elseif ( $commissions[ $i ]['status'] === 'reversed' ) {
				$product_array[ $product_id ][ $date ]['reversed'] += $commissions[ $i ]['total_due'];
			}

			$product_array[ $product_id ][ $date ]['commission'] += $commissions[ $i ]['total_due'] + $commissions[ $i ]['total_shipping'] + $commissions[ $i ]['tax'];

		}
		return $product_array;
	}
}

