<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Pro_Admin_Products_Screen {
	public static function output() {
		require_once dirname( __DIR__ ) . '/tables/class-wc-serial-numbers-pro-products-list-table.php';
		$table    = new WC_Serial_Numbers_Pro_Admin_Products_List_Table();
		$table->process_bulk_action();
		$table->prepare_items();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<?php _e( 'Serial Number Products', 'wc-serial-numbers-pro' ); ?>
			</h1>

			<hr class="wp-header-end">

			<form id="wc-serial-numbers-list" method="get">
				<?php
				$table->search_box( __( 'Search', 'wc-serial-numbers-pro' ), 'search' );
				$table->display();
				?>
				<input type="hidden" name="page" value="wc-serial-numbers-products">
			</form>
		</div>
		<?php
	}
}
