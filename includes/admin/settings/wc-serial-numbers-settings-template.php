<?php
defined( 'ABSPATH' ) || exit();
include_once( WC()->plugin_path() . '/includes/admin/settings/class-wc-settings-page.php' );
if ( ! class_exists( 'WC_Serial_Numbers_Settings_Template' ) ) :
	/**
	 * WC_Serial_Numbers_Settings_Template
	 */
	class WC_Serial_Numbers_Settings_Template extends WC_Settings_Page {
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'template';
			$this->label = __( 'Template', 'wc-serial-numbers-pro' );

			add_filter( 'wc_serial_numbers_settings_tabs_array', array( $this, 'add_settings_page' ), 30 );
			add_action( 'wc_serial_numbers_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'wc_serial_numbers_settings_save_' . $this->id, array( $this, 'save' ) );
		}

		/**
		 * Get settings array
		 *
		 * @return array
		 */
		public function get_settings() {
			$settings = array(
				[
					'title' => __( 'Template Settings.', 'wc-serial-numbers-pro' ),
					'type'  => 'title',
					'desc'  => __( 'The following options affects how the serial numbers will show its data..', 'wc-serial-numbers-pro' ),
					'id'    => 'section_messages'
				],
				[
					'title'    => __( 'Low stock message', 'wc-serial-numbers-pro' ),
					'id'       => 'wc_serial_numbers_low_stock_message',
					'desc'     => __( 'When "Sell From Stock" enabled and there is not enough items in <br/>stock the message will appear on checkout page. Supported tags {product_title}, {stock_quantity}', 'wc-serial-numbers-pro' ),
					'desc_tip' => true,
					'type'     => 'textarea',
					'css'      => 'min-width: 50%; height: 75px;',
					'default'  => __( 'Sorry, There is not enough Serial Numbers available for {product_title}, Please remove this item or lower the quantity, For now we have {stock_quantity} Serial Number for this product.', 'wc-serial-numbers-pro' ),
				],
				[
					'title'    => __( 'Pending  notification', 'wc-serial-numbers-pro' ),
					'id'       => 'wc_serial_numbers_pending_notice',
					'desc'     => __( 'When Order is completed but no serial numbers assigned the message will appear.', 'wc-serial-numbers-pro' ),
					'desc_tip' => true,
					'type'     => 'textarea',
					'css'      => 'min-width: 50%; height: 75px;',
					'default'  => __( 'Order waiting for assigning serial numbers.', 'wc-serial-numbers-pro' ),
				],
				[
					'type' => 'sectionend',
					'id'   => 'section_messages'
				],
				[
					'title' => __( 'Order Table Settings.', 'wc-serial-numbers-pro' ),
					'type'  => 'title',
					'desc'  => __( 'The following options affects how the serial numbers will show on thank you page & in email.', 'wc-serial-numbers-pro' ),
					'id'    => 'section_order_table'
				],
				[
					'id'      => 'wc_serial_numbers_order_table_heading',
					'title'   => __( 'Heading', 'wc-serial-numbers-pro' ),
					'css'     => 'width: 400px;',
					'type'    => 'text',
					'desc'    => __( 'This will appear above the serial numbers table', 'wc-serial-numbers-pro' ),
					'default' => __( 'Serial Numbers', 'wc-serial-numbers-pro' ),
				],
				[
					'title'           => __( 'Order Table Columns', 'wc-serial-numbers-pro' ),
					'desc'            => __( 'Product', 'wc-serial-numbers-pro' ),
					'id'              => 'wc_serial_numbers_order_table_col_product',
					'default'         => 'yes',
					'type'            => 'checkbox',
					'checkboxgroup'   => 'start',
					'show_if_checked' => 'option',
				],
				[
					'desc'          => __( 'Serial Number', 'wc-serial-numbers-pro' ),
					'id'            => 'wc_serial_numbers_order_table_col_key',
					'default'       => 'yes',
					'type'          => 'checkbox',
					'checkboxgroup' => '',
				],
				[
					'desc'          => __( 'Activation Email', 'wc-serial-numbers-pro' ),
					'id'            => 'wc_serial_numbers_order_table_col_email',
					'default'       => 'no',
					'type'          => 'checkbox',
					'checkboxgroup' => '',
				],
				[
					'desc'          => __( 'Activation Limit', 'wc-serial-numbers-pro' ),
					'id'            => 'wc_serial_numbers_order_table_col_limit',
					'default'       => 'yes',
					'type'          => 'checkbox',
					'checkboxgroup' => '',
				],
				[
					'desc'          => __( 'Expire Date', 'wc-serial-numbers-pro' ),
					'id'            => 'wc_serial_numbers_order_table_col_expires',
					'default'       => 'yes',
					'type'          => 'checkbox',
					'checkboxgroup' => 'end',
				],
				[
					'id'      => 'wc_serial_numbers_order_table_col_product_label',
					'title'   => __( 'Product', 'wc-serial-numbers-pro' ),
					'css'     => 'width: 400px;',
					'type'    => 'text',
					'desc'    => __( 'Product name column heading', 'wc-serial-numbers-pro' ),
					'default' => __( 'Product', 'wc-serial-numbers-pro' ),
				],
				[
					'id'      => 'wc_serial_numbers_order_table_col_key_label',
					'title'   => __( 'Serial Number', 'wc-serial-numbers-pro' ),
					'css'     => 'width: 400px;',
					'type'    => 'text',
					'desc'    => __( 'Key column heading', 'wc-serial-numbers-pro' ),
					'default' => __( 'Serial Number', 'wc-serial-numbers-pro' ),
				],
				[
					'id'      => 'wc_serial_numbers_order_table_col_email_label',
					'title'   => __( 'Activation Email', 'wc-serial-numbers-pro' ),
					'css'     => 'width: 400px;',
					'type'    => 'text',
					'desc'    => __( 'Email column heading', 'wc-serial-numbers-pro' ),
					'default' => __( 'Activation Email', 'wc-serial-numbers-pro' ),
				],
				[
					'id'      => 'wc_serial_numbers_order_table_col_limit_label',
					'title'   => __( 'Activation Limit', 'wc-serial-numbers-pro' ),
					'css'     => 'width: 400px;',
					'type'    => 'text',
					'desc'    => __( ' Activation Limit column heading', 'wc-serial-numbers-pro' ),
					'default' => __( ' Activation Limit', 'wc-serial-numbers-pro' ),
				],
				[
					'id'      => 'wc_serial_numbers_order_table_col_expires_label',
					'title'   => __( 'Activation Date', 'wc-serial-numbers-pro' ),
					'css'     => 'width: 400px;',
					'type'    => 'text',
					'desc'    => __( 'Expire Date column heading', 'wc-serial-numbers-pro' ),
					'default' => __( 'Expire Date', 'wc-serial-numbers-pro' ),
				],
				[
					'type' => 'sectionend',
					'id'   => 'section_order_table'
				],
			);

			return apply_filters( 'wc_serial_numbers_template_settings_fields', $settings );
		}

		/**
		 * Save settings
		 */
		public function save() {
			$settings = $this->get_settings();
			WC_Serial_Numbers_Admin_Settings::save_fields( $settings );
		}
	}

endif;

return new WC_Serial_Numbers_Settings_Template();
