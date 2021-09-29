<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Pro_Admin_Menus {

	/**
	 * WC_Serial_Numbers_Pro_Admin_Menus constructor.
	 */
	public function __construct() {
		add_filter( 'custom_menu_order', array(__CLASS__, 'submenu_order'));
		add_action( 'admin_menu', array( $this, 'register_pages' ), 20 );
	}

	/**
	 * Control sub menu order
	 *
	 * @since 1.1.0
	 * @param $menu_order
	 *
	 * @return mixed
	 */
	public static function submenu_order( $menu_order ){
		global $submenu;
		$settings = $submenu['wc-serial-numbers'];
		foreach ( $settings as $key => $details ) {
			if ( $details[2] == 'wc-serial-numbers-settings' ) {
				unset($submenu['wc-serial-numbers'][$key]);
				$submenu['wc-serial-numbers'][99]= $details;
			}
			if ( $details[2] == 'wc-serial-numbers-activations' ) {
				unset($submenu['wc-serial-numbers'][$key]);
				$submenu['wc-serial-numbers'][70]= $details;
			}
			if ( $details[2] == 'wc-serial-numbers-import' ) {
				unset($submenu['wc-serial-numbers'][$key]);
				$submenu['wc-serial-numbers'][80]= $details;
			}
			if ( $details[2] == 'wc-serial-numbers-export' ) {
				unset($submenu['wc-serial-numbers'][$key]);
				$submenu['wc-serial-numbers'][81]= $details;
			}
		}

		ksort( $submenu['wc-serial-numbers'] );
		# Return the new submenu order
		return $menu_order;
	}

	/**
	 * Register pages.
	 * @since 1.1.0
	 */
	public function register_pages() {
		$role = wc_serial_numbers_get_user_role();
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Products', 'wc-serial-numbers-pro' ),
			__( 'Products', 'wc-serial-numbers-pro' ),
			$role,
			'wc-serial-numbers-products',
			array( 'WC_Serial_Numbers_Pro_Admin_Products_Screen', 'output' )
		);
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Generators', 'wc-serial-numbers-pro' ),
			__( 'Generators', 'wc-serial-numbers-pro' ),
			$role,
			'wc-serial-numbers-generators',
			array( 'WC_Serial_Numbers_Pro_Admin_Generators_Screen', 'output' )
		);
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Import', 'wc-serial-numbers-pro' ),
			__( 'Import', 'wc-serial-numbers-pro' ),
			$role,
			'wc-serial-numbers-import',
			array( __CLASS__, 'import_page' )
		);
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Export', 'wc-serial-numbers-pro' ),
			__( 'Export', 'wc-serial-numbers-pro' ),
			$role,
			'wc-serial-numbers-export',
			array( __CLASS__, 'export_page' )
		);
	}

	public static function import_page(){
		include dirname( __FILE__ ) .'/views/html-csv-import.php';
	}

	public static function export_page(){
		include dirname( __FILE__ ) .'/views/html-csv-export.php';
	}

}

new WC_Serial_Numbers_Pro_Admin_Menus();
