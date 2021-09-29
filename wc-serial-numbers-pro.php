<?php
/**
 * Plugin Name: WooCommerce Serial Numbers Pro
 * Plugin URI:  https://www.pluginever.com
 * Description: The best WordPress Plugin to sell license keys, redeem cards and other secret numbers!
 * Version:     1.1.4
 * Author:      pluginever
 * Author URI:  https://www.pluginever.com
 * Donate link: https://www.pluginever.com
 * License:     GPLv2+
 * Text Domain: wc-serial-numbers-pro
 * Domain Path: /i18n/languages/
 * Tested up to: 5.5.1
 * WC requires at least: 3.0.0
 * WC tested up to: 4.4.1
 */

/**
 * Copyright (c) 2019 pluginever (email : support@pluginever.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Main WC_Serial_Numbers_Pro Class.
 *
 * @class WC_Serial_Numbers_Pro
 */
final class WC_Serial_Numbers_Pro {
	/**
	 * WC_Serial_Numbers_Pro version.
	 *
	 * @var string
	 */
	public $version = '1.1.4';

	/**
	 * The single instance of the class.
	 *
	 * @var WC_Serial_Numbers_Pro
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * Main WC_Serial_Numbers_Pro Instance
	 *
	 * Insures that only one instance of WC_Serial_Numbers_Pro exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @return WC_Serial_Numbers_Pro The one true WC_Serial_Numbers_Pro
	 * @since 1.0.0
	 * @static var array $instance
	 */
	public static function init() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WC_Serial_Numbers_Pro ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Return plugin version.
	 *
	 * @return string
	 * @since 1.1.0
	 * @access public
	 **/
	public function get_version() {
		return $this->version;
	}

	/**
	 * Plugin URL getter.
	 *
	 * @return string
	 * @since 1.1.0
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Plugin path getter.
	 *
	 * @return string
	 * @since 1.1.0
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Plugin base path name getter.
	 *
	 * @return string
	 * @since 1.1.0
	 */
	public function plugin_basename() {
		return plugin_basename( __FILE__ );
	}

	/**
	 * Initialize plugin for localization
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'wc-serial-numbers-pro', false, plugin_basename( dirname( __FILE__ ) ) . '/i18n/languages' );
	}

	/**
	 * Free plugin dependency notice
	 * @since 1.1.0
	 */
	public function free_missing_notice() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( is_plugin_active( 'wc-serial-numbers/wc-serial-numbers.php' ) !== true ) {
			$message = sprintf( __( '<strong>WooCommerce Serial Numbers Pro</strong> requires <strong>WooCommerce Serial Numbers</strong> installed and activated. Please Install %s WooCommerce Serial Numbers. %s', 'wc-serial-numbers-pro' ),
				'<a href="https://wordpress.org/plugins/wc-serial-numbers/" target="_blank">', '</a>' );
			echo sprintf( '<div class="notice notice-error"><p>%s</p></div>', $message );
		}
	}

	/**
	 * Define constant if not already defined
	 *
	 * @param string $name
	 * @param string|bool $value
	 *
	 * @return void
	 * @since 1.1.0
	 *
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @access protected
	 * @return void
	 */

	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wc-serial-numbers-pro' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @access protected
	 * @return void
	 */

	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wc-serial-numbers-pro' ), '1.0.0' );
	}

	/**
	 * WC_Serial_Numbers constructor.
	 */
	private function __construct() {
		$this->define_constants();
		register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate_plugin' ) );

		add_action( 'wc_serial_numbers__loaded', array( $this, 'init_plugin' ) );
		add_action( 'admin_notices', array( $this, 'free_missing_notice' ) );
	}

	/**
	 * Define all constants
	 * @return void
	 * @since 1.1.0
	 */
	public function define_constants() {
		$this->define( 'WC_SERIAL_NUMBER_PRO_PLUGIN_VERSION', $this->version );
		$this->define( 'WC_SERIAL_NUMBER_PRO_PLUGIN_FILE', __FILE__ );
		$this->define( 'WC_SERIAL_NUMBER_PRO_PLUGIN_DIR', dirname( __FILE__ ) );
		$this->define( 'WC_SERIAL_NUMBER_PRO_PLUGIN_INC_DIR', dirname( __FILE__ ) . '/includes' );
	}

	/**
	 * Activate plugin.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public function activate_plugin() {

	}

	/**
	 * Deactivate plugin.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public function deactivate_plugin() {

	}

	/**
	 * Load the plugin when WooCommerce loaded.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public function init_plugin() {
		require_once( dirname( __FILE__ ) . '/includes/edd-client/EDD_Client_Init.php' );
		$plugin = new EDD_Client_Init( __FILE__, 'https://pluginever.com' );
		if ( ! $plugin->is_premium() ) {
			return;
		}
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 * @since 1.1.0
	 */
	public function includes() {
		require_once dirname( __FILE__ ) . '/includes/wc-serial-numbers-pro-functions.php';
		if ( is_admin() ) {
			require_once dirname( __FILE__ ) . '/includes/admin/class-wc-serial-numbers-pro-admin.php';
		}
	}


	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'localization_setup' ) );
		add_filter( 'wc_serial_numbers_product_types', array( __CLASS__, 'add_product_type_support' ) );
		add_filter( 'wc_serial_numbers_key_sources', array( __CLASS__, 'include_extra_key_sources' ) );
		add_filter( 'wc_serial_numbers_per_product_delivery_qty', array( __CLASS__, 'enable_multiple_delivery_qty' ), 10, 2 );
		add_filter( 'wc_serial_numbers_product_serial_source', array( __CLASS__, 'define_serials_source' ), 10, 2 );
		add_filter( 'wc_serial_numbers_pre_order_item_connect_serial_numbers', array( __CLASS__, 'generate_serials' ), 10, 3 );
		add_filter( 'wc_serial_numbers_low_stock_message', array( __CLASS__, 'control_low_stock_message' ) );
		add_filter( 'wc_serial_numbers_order_table_columns', array( __CLASS__, 'custom_order_table_columns' ) );
		add_filter( 'wc_serial_numbers_order_table_heading', array( __CLASS__, 'order_table_heading' ) );
		add_filter( 'wc_serial_numbers_allow_duplicate_serial_number', array( __CLASS__, 'control_duplicate_serial_numbers' ) );
		add_filter( 'wc_serial_numbers_maybe_manual_delivery', array( __CLASS__, 'control_serial_numbers_delivery' ) );
		add_filter( 'wc_serial_numbers_pending_notice', array( __CLASS__, 'control_pending_notice' ) );
		add_filter( 'wc_serial_numbers_allow_backorder', array( __CLASS__, 'control_backorder' ), 10, 2 );
	}

	/**
	 * @param $types
	 *
	 * @return mixed
	 * @since 1.1.0
	 */
	public static function add_product_type_support( $types ) {
		$types[] = 'product_variation';

		return $types;
	}

	/**
	 * Premium pre sources.
	 *
	 * @param $sources
	 *
	 * @return array
	 * @since 1.1.0
	 */
	public static function include_extra_key_sources( $sources ) {
		return array_merge( $sources, array(
			'generator_rule' => __( 'Generator Rule', 'wc-serial-numbers-pro' ),
			'auto_generated' => __( 'Auto Generated Serial Number', 'wc-serial-numbers-pro' ),
		) );
	}

	/**
	 * Enable multiple item delivery per product.
	 *
	 * @param $quantity
	 * @param $product_id
	 *
	 * @return int
	 * @since 1.2.0
	 */
	public static function enable_multiple_delivery_qty( $quantity, $product_id ) {
		$per_item = get_post_meta( $product_id, '_delivery_quantity', true );
		if ( ! empty( $per_item ) ) {
			$quantity = intval( $per_item );
		}

		return $quantity;
	}

	/**
	 * Define serial number source.
	 *
	 * @param $source
	 * @param $product_id
	 *
	 * @return bool|mixed|string
	 * @since 1.2.0
	 */
	public static function define_serials_source( $source, $product_id ) {
		if ( empty( $source = wc_serial_numbers_pro_get_serial_source( $product_id ) ) ) {
			return 'custom_source';
		}

		return $source;
	}


	/**
	 * Generate serial numbers.
	 *
	 * @param $product_id
	 * @param $total_delivery_qty
	 * @param $source
	 *
	 * @return bool|int
	 * @since 1.2.0
	 */
	public static function generate_serials( $product_id, $total_delivery_qty, $source ) {
		if ( empty( $product_id ) || empty( $total_delivery_qty ) || empty( $source ) || $source == 'custom_source' ) {
			return false;
		}
		$count           = WC_Serial_Numbers_Query::init()->from( 'serial_numbers' )->where( 'product_id', intval( $product_id ) )->where( 'status', 'available' )->where( 'source', sanitize_text_field( $source ) )->count();
		$needed_quantity = ceil( $total_delivery_qty - $count );
		if ( $needed_quantity < 1 ) {
			return false;
		}

		if ( $source != $source = wc_serial_numbers_pro_get_serial_source( $product_id ) ) {
			return false;
		}
		$total_generator = 0;

		if ( $source == 'generator_rule' ) {
			$generator_id = (int) get_post_meta( $product_id, '_generator_id', true );

			return wc_serial_numbers_pro_generate_generator_serials( $generator_id, $needed_quantity );
		}

		if ( $source == 'auto_generated' ) {
			return wc_serial_numbers_pro_generate_automatic_serials( $product_id, $needed_quantity );
		}


		return $total_generator;
	}

	/**
	 * custom low stock message.
	 *
	 * @param $message
	 *
	 * @return mixed
	 * @since 1.1.0
	 */
	public static function control_low_stock_message( $message ) {
		$custom_message = get_option( 'wc_serial_numbers_low_stock_message' );
		if ( ! empty( $custom_message ) ) {
			return $custom_message;
		}

		return $message;
	}

	/**
	 * @param $columns
	 *
	 * @return mixed
	 * @since 1.1.0
	 */
	public static function custom_order_table_columns( $columns ) {
		$columns_map = [
			'wc_serial_numbers_order_table_col_product' => 'wc_serial_numbers_order_table_col_product_label',
			'wc_serial_numbers_order_table_col_key'     => 'wc_serial_numbers_order_table_col_key_label',
			'wc_serial_numbers_order_table_col_email'   => 'wc_serial_numbers_order_table_col_email_label',
			'wc_serial_numbers_order_table_col_limit'   => 'wc_serial_numbers_order_table_col_limit_label',
			'wc_serial_numbers_order_table_col_expires' => 'wc_serial_numbers_order_table_col_expires_label',
		];

		$columns_keys = [
			'wc_serial_numbers_order_table_col_product' => 'product',
			'wc_serial_numbers_order_table_col_key'     => 'serial_key',
			'wc_serial_numbers_order_table_col_email'   => 'activation_email',
			'wc_serial_numbers_order_table_col_limit'   => 'activation_limit',
			'wc_serial_numbers_order_table_col_expires' => 'expire_date',
		];

		foreach ( $columns_map as $key => $label_key ) {
			if ( 'yes' == get_option( $key ) ) {
				$columns[ $columns_keys[ $key ] ] = get_option( $label_key );
			} else {
				unset( $columns[ $columns_keys[ $key ] ] );
			}
		}

		return $columns;
	}

	public static function order_table_heading( $heading ) {
		$heading = get_option( 'wc_serial_numbers_order_table_heading' );

		return $heading;
	}

	public static function control_duplicate_serial_numbers( $control ) {
		$control = wc_serial_numbers_validate_boolean( get_option( 'wc_serial_numbers_enable_duplicate' ) );

		return $control;
	}

	public static function control_serial_numbers_delivery( $control ) {
		$control = wc_serial_numbers_validate_boolean( get_option( 'wc_serial_numbers_manual_delivery' ) );

		return $control;
	}

	public static function control_pending_notice( $message ) {
		$custom_message = get_option( 'wc_serial_numbers_pending_notice' );

		return empty( $custom_message ) ? $message : $custom_message;
	}

	public static function control_backorder( $control, $product_id ) {
		return wc_serial_numbers_validate_boolean(get_option('wc_serial_numbers_enable_backorder'));
	}

}

function wc_serial_numbers_pro() {
	return WC_Serial_Numbers_Pro::init();
}

//fire off the plugin
wc_serial_numbers_pro();
