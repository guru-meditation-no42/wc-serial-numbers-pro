<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Pro_Admin_Actions {
	public static function init() {
		add_filter( 'bulk_actions-edit-shop_order', array( __CLASS__, 'order_bulk_actions' ), 20, 1 );
		add_filter( 'handle_bulk_actions-edit-shop_order', array( __CLASS__, 'handle_order_bulk_actions' ), 10, 3 );
		add_action( 'admin_post_wc_serial_numbers_txt_import', array( __CLASS__, 'import_txt_serial_numbers' ) );
		add_action( 'admin_post_wc_serial_numbers_csv_import', array( __CLASS__, 'import_csv_serial_numbers' ) );
		add_action( 'admin_init', array( __CLASS__, 'export_serial_numbers' ) );
	}

	public static function order_bulk_actions( $actions ) {
		$actions['order_bulk_add_serial_numbers']    = __( 'Connect serial numbers', 'wc-serial-numbers-pro' );
		$actions['order_bulk_remove_serial_numbers'] = __( 'Disconnect Serial numbers', 'wc-serial-numbers-pro' );

		return $actions;
	}

	public static function handle_order_bulk_actions( $redirect_to, $action, $post_ids ) {
		if ( ! in_array( $action, [ 'order_bulk_add_serial_numbers', 'order_bulk_remove_serial_numbers' ] ) ) {
			return $redirect_to;
		}

		if ( $action == 'order_bulk_add_serial_numbers' ) {
			$connected = 0;
			foreach ( $post_ids as $post_id ) {
				$connected += wc_serial_numbers_order_connect_serial_numbers( $post_id );
			}

			WC_Serial_Numbers_Admin_Notice::add_notice( sprintf( __( 'Total %d serial numbers added for %d orders.', 'wc-serial-numbers-pro' ), $connected, count( $post_ids ) ), [ 'type' => 'error' ] );
		} elseif ( $action == 'order_bulk_remove_serial_numbers' ) {
			$disconnected = 0;
			foreach ( $post_ids as $post_id ) {
				$disconnected += wc_serial_numbers_order_disconnect_serial_numbers( $post_id );
			}

			WC_Serial_Numbers_Admin_Notice::add_notice( sprintf( __( 'Total %d serial numbers disconnected for %d orders.', 'wc-serial-numbers-pro' ), $disconnected, count( $post_ids ) ), [ 'type' => 'error' ] );
		}

		return $redirect_to;
	}

	public static function export_serial_numbers() {
		if ( ! isset( $_REQUEST['action'] ) || $_REQUEST['action'] !== 'wc_serial_numbers_export' ) {
			return false;
		}

		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wc_serial_numbers_export' ) ) {
			wp_die( __( 'Error: Nonce verification failed', 'wc-serial-numbers-pro' ) );
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( __( 'Error: Sorry, you are not allowed to do this.', 'wc-serial-numbers-pro' ) );
		}
		$product_id = isset( $_REQUEST['product_id'] ) ? wp_parse_id_list( $_REQUEST['product_id'] ) : '';
		$order_id   = isset( $_REQUEST['order_id'] ) ? wp_parse_id_list( $_REQUEST['order_id'] ) : '';
		$status     = isset( $_REQUEST['status'] ) ? sanitize_text_field( $_REQUEST['status'] ) : '';
		$fields     = ! empty( $_REQUEST['fields'] ) ? array_keys( $_REQUEST['fields'] ) : '';
		$fields     = array_merge( [ 'serial_key' ], $fields );
		$fields     = array_unique( $fields );

		if ( empty( $fields ) ) {
			wp_safe_redirect( wp_get_referer() );
			exit;
		}
		$where = [];
		if ( ! empty( $order_id ) ) {
			$where['order_id'] = $order_id;
		}

		if ( ! empty( $product_id ) ) {
			$where['product_id'] = $product_id;
		}

		if ( ! empty( $status ) ) {
			$where['status'] = $status;
		}

		$serials = WC_Serial_Numbers_Query::init()->from( 'serial_numbers' )->where( $where )->get();

		$rows = array();
		foreach ( $serials as $serial ) {
			$order_id = $serial->order_id;
			if ( ! empty( $order_id ) ) {
				$order         = wc_get_order( $order_id );
				$customer_name = $order->get_formatted_billing_full_name();
			} else {
				$customer_name = '';
			}
			$serial->customer_name = $customer_name;

			$row    = array();
			$serial = get_object_vars( $serial );
			foreach ( $serial as $key => $value ) {
				if ( in_array( $key, $fields ) ) {
					$row[] = $key == 'serial_key' ? wc_serial_numbers_decrypt_key( $value ) : $value;
				}
			}

			$rows[] = $row;
		}

		$filename = date( 'YmdHis' ) . '_wc_serial_numbers.csv';

		// disable caching
		$now = gmdate( "D, d M Y H:i:s" );
		header( "Expires: Tue, 03 Jul 2001 06:00:00 GMT" );
		header( "Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate" );
		header( "Last-Modified: {$now} GMT" );

		// force download
		header( "Content-Type: application/force-download" );
		header( "Content-Type: application/octet-stream" );
		header( "Content-Type: application/download" );

		// disposition / encoding on response body
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( "Content-Disposition: attachment; filename={$filename}" );
		//header( "Content-Transfer-Encoding: binary" );

		$df = fopen( "php://output", 'w' );
		fputcsv( $df, $fields );
		foreach ( $rows as $row ) {
			fputcsv( $df, $row );
		}

		fclose( $df );
		exit();
	}


	public static function import_txt_serial_numbers() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wc_serial_numbers_txt_import' ) ) {
			wp_die( __( 'Error: Nonce verification failed', 'wc-serial-numbers-pro' ) );
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( __( 'Error: Sorry, you are not allowed to do this.', 'wc-serial-numbers-pro' ) );
		}

		$ext       = pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION );
		$mimes     = array( 'text/plain' );
		$fileName  = $_FILES['file']['tmp_name'];
		$upload    = wp_upload_dir();
		$file_path = $upload['path'] . '/serial_numbers.' . $ext;

		// Validate the file extension

		if ( ! in_array( $ext, array( 'txt' ) ) || ! in_array( $_FILES['file']['type'], $mimes ) ) {
			WC_Serial_Numbers_Admin_Notice::add_notice( __( 'Invalid file type, only TXT allowed.', 'wc-serial-numbers-pro' ), [ 'type' => 'error' ] );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		// File upload file, return with error.
		if ( ! move_uploaded_file( $fileName, $file_path ) ) {
			WC_Serial_Numbers_Admin_Notice::add_notice( __( 'Failed uploading file.', 'wc-serial-numbers-pro' ), [ 'type' => 'error' ] );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$product_id       = ! empty( $_REQUEST['product_id'] ) ? intval( $_REQUEST['product_id'] ) : '';
		$activation_limit = ! empty( $_REQUEST['activation_limit'] ) ? intval( $_REQUEST['activation_limit'] ) : '';
		$validity         = ! empty( $_REQUEST['validity'] ) ? intval( $_REQUEST['validity'] ) : '';
		$expire_date      = ! empty( $_REQUEST['expire_date'] ) ? sanitize_text_field( $_REQUEST['expire_date'] ) : '';

		if ( empty( $product_id ) ) {
			WC_Serial_Numbers_Admin_Notice::add_notice( __( 'Please select product & upload file.', 'wc-serial-numbers-pro' ), [ 'type' => 'error' ] );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		global $current_user;
		$item = [
			'serial_key'       => '',
			'product_id'       => $product_id,
			'activation_limit' => $activation_limit,
			'activation_count' => 0,
			'order_id'         => 0,
			'vendor_id'        => $current_user->ID,
			'status'           => 'available',
			'validity'         => $validity,
			'expire_date'      => $expire_date,
			'order_date'       => '',
			'source'           => 'custom_source',
			'created_date'     => current_time( 'mysql' ),
		];

		$total_imported = 0;
		$serial_keys    = file( $file_path, FILE_IGNORE_NEW_LINES );

		if ( empty( $serial_keys ) ) {
			WC_Serial_Numbers_Admin_Notice::add_notice( __( 'Could not find any item to import, please check file.', 'wc-serial-numbers-pro' ), [ 'type' => 'error' ] );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		foreach ( $serial_keys as $serial_key ) {
			if ( ! is_wp_error( wc_serial_numbers_insert_serial_number( wp_parse_args( [ 'serial_key' => $serial_key ], $item ) ) ) ) {
				$total_imported += 1;
			}
		}

		WC_Serial_Numbers_Admin_Notice::add_notice( sprintf( __( 'Total %d serial numbers imported.', 'wc-serial-numbers-pro' ), $total_imported ), [ 'type' => 'success' ] );
		wp_safe_redirect( wp_get_referer() );
		exit;
	}


	/**
	 * Import file.
	 * @since 1.1.0
	 */
	public static function import_csv_serial_numbers() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wc_serial_numbers_csv_import' ) ) {
			wp_die( __( 'Error: Nonce verification failed', 'wc-serial-numbers-pro' ) );
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( __( 'Error: Sorry, you are not allowed to do this.', 'wc-serial-numbers-pro' ) );
		}

		$ext       = pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION );
		$mimes     = array( 'application/vnd.ms-excel', 'text/csv', 'text/tsv' );
		$fileName  = $_FILES['file']['tmp_name'];
		$upload    = wp_upload_dir();
		$file_path = $upload['path'] . '/serial_numbers.' . $ext;

		// Validate the file extension

		if ( ! in_array( $_FILES['file']['type'], $mimes ) ) {
			WC_Serial_Numbers_Admin_Notice::add_notice( __( 'Invalid file type, only CSV allowed.', 'wc-serial-numbers-pro' ), [ 'type' => 'error' ] );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		// File upload file, return with error.
		if ( ! move_uploaded_file( $fileName, $file_path ) ) {
			WC_Serial_Numbers_Admin_Notice::add_notice( __( 'Failed uploading CSV file.', 'wc-serial-numbers-pro' ), [ 'type' => 'error' ] );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		global $current_user;

		$item = [
			'serial_key'       => '',
			'product_id'       => null,
			'activation_limit' => 0,
			'activation_count' => 0,
			'order_id'         => 0,
			'vendor_id'        => $current_user->ID,
			'status'           => 'available',
			'validity'         => 0,
			'expire_date'      => '0000-00-00 00:00:00',
			'order_date'       => '',
			'source'           => 'custom_source',
			'created_date'     => current_time( 'mysql' ),
		];

		$serial_numbers = array();
		$headers_found  = false;
		$headers        = array();
		$total_imported = 0;

		if ( ( $handle = fopen( $file_path, 'r' ) ) !== false ) {
			while ( ( $row = fgetcsv( $handle, 1000, ',' ) ) !== false ) {
				if ( $row && is_array( $row ) && count( $row ) > 0 ) {
					if ( $headers_found == false && in_array( 'serial_key', $row ) ) {
						$headers       = $row;
						$headers_found = true;
						continue;
					}

					if ( ! in_array( 'serial_key', $headers ) ) {
						WC_Serial_Numbers_Admin_Notice::add_notice( __( 'Could not find serial_key columns in the CSV.', 'wc-serial-numbers-pro' ), [ 'type' => 'error' ] );
						wp_safe_redirect( wp_get_referer() );
						exit;
					} else if ( ! in_array( 'product_id', $headers ) ) {
						WC_Serial_Numbers_Admin_Notice::add_notice( __( 'Could not find product_id columns in the CSV.', 'wc-serial-numbers-pro' ), [ 'type' => 'error' ] );
						wp_safe_redirect( wp_get_referer() );
						exit;
					}
					$row = array_combine( $headers, $row );

					foreach ( $row as $head => $value ) {
						if ( ! in_array( $head, array_keys( $item ) ) ) {
							unset( $row[ $head ] );
						}
					}

					$serial_numbers[] = wp_parse_args( $row, $item );
				}
			}
			fclose( $handle );
		}

		if ( empty( $serial_numbers ) ) {
			WC_Serial_Numbers_Admin_Notice::add_notice( __( 'Could not find any item to import, please check CSV file.', 'wc-serial-numbers-pro' ), [ 'type' => 'error' ] );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}
		foreach ( $serial_numbers as $serial_number ) {

			$serial_number['expire_date']  = ( $serial_number['expire_date'] != '' ) ? date( 'Y-m-d H:i:s', strtotime( $serial_number['expire_date'] ) ) : '0000-00-00 00:00:00';
			$serial_number['order_date']   = ( $serial_number['order_date'] != '0000-00-00 00:00:00' ) ? date( 'Y-m-d H:i:s', strtotime( $serial_number['order_date'] ) ) : $serial_number['order_date'];
			$serial_number['created_date'] = ( $serial_number['created_date'] != '0000-00-00 00:00:00' ) ? date( 'Y-m-d H:i:s', strtotime( $serial_number['created_date'] ) ) : $serial_number['created_date'];

			if ( ! is_wp_error( wc_serial_numbers_insert_serial_number( $serial_number ) ) ) {
				$total_imported += 1;
			}
		}

		WC_Serial_Numbers_Admin_Notice::add_notice( sprintf( __( 'Total %d serial numbers imported.', 'wc-serial-numbers-pro' ), $total_imported ), [ 'type' => 'success' ] );
		wp_safe_redirect( wp_get_referer() );
		exit;
	}
}

WC_Serial_Numbers_Pro_Admin_Actions::init();
