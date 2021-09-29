<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Pro_Admin_Settings {
	public static function init() {
		add_filter( 'wc_serial_numbers_general_settings_fields', array( __CLASS__, 'include_advance_setting_fields' ) );
		add_filter( 'wc_serial_numbers_get_settings_pages', array( __CLASS__, 'include_settings' ) );
	}

	public static function include_advance_setting_fields( $fields ) {
		$advance_settings = array(
			[
				'title' => __( 'Advance Settings.', 'wc-serial-numbers-pro' ),
				'type'  => 'title',
				'desc'  => __( 'Advance plugin settings.', 'wc-serial-numbers-pro' ),
				'id'    => 'section_advance_settings'
			],
			[
				'id'    => 'wc_serial_numbers_enable_duplicate',
				'title' => __( 'Enable duplicates', 'wc-serial-numbers-pro' ),
				'desc'  => __( 'Enable duplicate serial number, this will force to send billing email with API request [Not Recommended].', 'wc-serial-numbers-pro' ),
				'type'  => 'checkbox',
			],
			[
				'id'    => 'wc_serial_numbers_manual_delivery',
				'title' => __( 'Manual Delivery', 'wc-serial-numbers-pro' ),
				'desc'  => __( 'This will stop automatically assigning serial numbers with order & you have to assign manually.', 'wc-serial-numbers-pro' ),
				'type'  => 'checkbox',
			],
			[
				'id'    => 'wc_serial_numbers_enable_backorder',
				'title' => __( 'Enable Backorder', 'wc-serial-numbers-pro' ),
				'desc'  => __( 'This is let the customer to buy serial numbers even without stock and you can add later for the order.', 'wc-serial-numbers-pro' ),
				'type'  => 'checkbox',
			],
			[
				'title'             => __( 'Order Auto Complete statuses', 'wc-serial-numbers-pro' ),
				'desc'              => __( 'Processing', 'wc-serial-numbers-pro' ),
				'id'                => 'wc_serial_numbers_autocomplete_status_processing',
				'default'           => 'yes',
				'type'              => 'checkbox',
				'checkboxgroup'     => 'start',
				'show_if_checked'   => 'option',
				'custom_attributes' => array(
					'disabled' => 'disabled'
				)
			],
			[
				'desc'          => __( 'Pending', 'wc-serial-numbers-pro' ),
				'id'            => 'wc_serial_numbers_autocomplete_status_pending',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => '',

			],
			[
				'desc'          => __( 'On Hold [Not recommended]', 'wc-serial-numbers-pro' ),
				'id'            => 'wc_serial_numbers_autocomplete_status_on_hold',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'end',
			],
			[
				'type' => 'sectionend',
				'id'   => 'section_advance_settings'
			],
		);

		return array_merge( $fields, $advance_settings );
	}

	public static function include_settings( $settings ) {
		$settings[] = include( 'settings/wc-serial-numbers-settings-template.php' );

		return $settings;
	}
}

WC_Serial_Numbers_Pro_Admin_Settings::init();
