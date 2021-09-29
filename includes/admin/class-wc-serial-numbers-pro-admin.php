<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Pro_Admin {

	/**
	 * WC_Serial_Numbers_Pro_Admin constructor.
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'includes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_ajax_wc_serial_numbers_generate_serials', array( __CLASS__, 'generate_serial_numbers' ) );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public static function includes() {
		require_once dirname( __FILE__ ) . '/class-wc-serial-numbers-pro-admin-settings.php';
		require_once dirname( __FILE__ ) . '/class-wc-serial-numbers-pro-admin-menus.php';
		require_once dirname( __FILE__ ) . '/class-wc-serial-numbers-pro-admin-metabox.php';
		require_once dirname( __FILE__ ) . '/class-wc-serial-numbers-pro-admin-actions.php';
		require_once dirname( __FILE__ ) . '/screen/class-wc-serial-numbers-pro-generators-screen.php';
		require_once dirname( __FILE__ ) . '/screen/class-wc-serial-numbers-pro-generators-screen.php';
		require_once dirname( __FILE__ ) . '/screen/class-wc-serial-numbers-pro-products-screen.php';
	}

	/**
	 * Enqueue admin related assets
	 *
	 * @param $hook
	 *
	 * @since 1.2.0
	 */
	public function admin_scripts( $hook ) {
		$css_url = wc_serial_numbers_pro()->plugin_url() . '/assets/css';
		$js_url  = wc_serial_numbers_pro()->plugin_url() . '/assets/js';
		$version = wc_serial_numbers_pro()->get_version();

		//wp_enqueue_style( 'wc-serial-numbers-pro-admin', $css_url . '/wc-serial-numbers-pro-admin.css', array(), $version );
		wp_enqueue_script( 'wc-serial-numbers-pro-admin', $js_url . '/wc-serial-numbers-pro-admin.js', [ 'jquery' ], $version, true );

		wp_localize_script( 'wc-serial-numbers-admin', 'wc_serial_numbers_pro_admin_i10n', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'i18n'    => array(
				'serial_number_activated'    => __( 'Serial Number Activated.', 'wc-serial-numbers-pro' ),
				'serial_number_deactivated'  => __( 'Serial Number Deactivated.', 'wc-serial-numbers-pro' ),
				'empty_serial_number_notice' => __( 'The Serial Number is empty. Please enter a serial number and try again.', 'wc-serial-numbers-pro' ),
				'generate_confirm'           => __( 'Are you sure to generate ', 'wc-serial-numbers-pro' ),
				'generate_success'           => __( ' Keys generated successfully.', 'wc-serial-numbers-pro' ),
				'enter_a_valid_number'       => __( 'Please, enter a valid number.', 'wc-serial-numbers-pro' ),
				'something_wrong'            => __( 'something, Happened wrong. Please try again.', 'wc-serial-numbers-pro' )
			)
		) );
	}


	public static function generate_serial_numbers() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'generate_serials' ) ) {
			wp_send_json_error( [ 'message' => __('No, cheating','wc-serial-numbers-pro') ] );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( [ 'message' => __('You are not allowed, to use this.','wc-serial-numbers-pro') ] );
		}

		$generator_id = intval( $_REQUEST['generator_id'] );
		$serial_count = intval( $_REQUEST['serial_count'] );
		if ( empty( $generator_id ) ) {
			wp_send_json_error( [ 'message' => __('No generator id found.','wc-serial-numbers-pro') ] );
		}

		if ( empty( $serial_count ) ) {
			wp_send_json_error( [ 'message' => __('Please specify an amount to generate.','wc-serial-numbers-pro') ] );
		}
		$product_id       = get_post_meta( $generator_id, 'product_id', true );
		$created =  wc_serial_numbers_pro_generate_generator_serials( $generator_id, $serial_count, true );
		do_action( 'wc_serial_numbers_generator_generated', $created, $product_id );
		wp_send_json_success( [ 'message' => sprintf( __( 'Generated %d serial number successfully', 'wc-serial-numbers-pro' ), $created ) ] );
	}
}

new WC_Serial_Numbers_Pro_Admin();
