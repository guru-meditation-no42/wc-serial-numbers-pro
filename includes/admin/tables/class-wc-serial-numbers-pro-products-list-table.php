<?php
defined( 'ABSPATH' ) || exit();
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WC_Serial_Numbers_Pro_Admin_Products_List_Table extends WP_List_Table {
	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $per_page = 20;

	/**
	 *
	 * Total number of items
	 * @var string
	 * @since 1.0.0
	 */
	public $total_count;

	/**
	 * Base URL
	 * @var string
	 */
	public $base_url;


	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'product', 'wc-serial-numbers-pro' ),
			'plural'   => __( 'products', 'wc-serial-numbers-pro' ),
			'ajax'     => false,
		) );
	}

	/**
	 * Setup the final data for the table
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function prepare_items() {
		$per_page              = $this->per_page;
		$columns               = $this->get_columns();
		$hidden                = [];
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$data = $this->get_results();

		$total_items = $this->total_count;

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	/**
	 * Show the search field
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @return void $views All the views sellable
	 * @since 1.0.0
	 */
	public function get_views() {

	}

	/**
	 * Get bulk actions
	 *
	 * since 1.0.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'source_automatic' => __( 'Set Source "Automatic"', 'wc-serial-numbers-pro' ),
			'source_custom'    => __( 'Set Source "Manual"', 'wc-serial-numbers-pro' ),
		);

		return $actions;
	}

	/**
	 * since 1.0.0
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'            => '<input type="checkbox" />',
			'thumb'         => '',
			'product'       => __( 'Product', 'wc-serial-numbers-pro' ),
			'product_price' => __( 'Price', 'wc-serial-numbers-pro' ),
			'stock'         => __( 'Stock', 'wc-serial-numbers-pro' ),
			'sold'          => __( 'Sold', 'wc-serial-numbers-pro' ),
			'source'        => __( 'Source', 'wc-serial-numbers-pro' ),
		);

		return apply_filters( 'wc_serial_numbers_products_table_columns', $columns );
	}

	/**
	 * since 1.0.0
	 * @return array
	 */
	function get_sortable_columns() {
		$sortable_columns = array();

		return apply_filters( 'wc_serial_numbers_products_table_sortable_columns', $sortable_columns );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @return string Name of the primary column.
	 * @since 1.0.0
	 * @access protected
	 *
	 */
	protected function get_primary_column_name() {
		return 'product';
	}

	/**
	 * since 1.0.0
	 *
	 * @param object $item
	 *
	 * @return string|void
	 */
	protected function column_cb( $item ) {
		return "<input type='checkbox' name='ids[]' id='id_{$item->ID}' value='{$item->ID}' />";
	}

	/**
	 * since 1.0.0
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string|void
	 */
	function column_default( $item, $column_name ) {
		$product = wc_get_product( $item->ID );
		switch ( $column_name ) {
			case 'thumb':
				$thumbnail = $product ? apply_filters( 'woocommerce_admin_order_item_thumbnail', $product->get_image( 'thumbnail', array( 'title' => '' ), false ), $product->get_id(), $item ) : '';
				$column    = '<div class="wc-order-item-thumbnail">' . wp_kses_post( $thumbnail ) . '</div>';
				break;
			case 'product':
				$column = empty( $item->ID ) || empty( $product ) ? '&mdash;' : sprintf( '<a href="%s">#%d - %s</a>', get_edit_post_link( $item->post_parent ? $item->post_parent : $product->get_id() ), $product->get_id(), $product->get_formatted_name() );
				break;
			case 'product_price':
				$column = empty( $item->ID ) || empty( $product ) ? '&mdash;' : $product->get_price_html();
				break;
			case 'stock':
				$stock  = wc_serial_numbers_get_stock_quantity( $product->get_id() );
				$column = empty( $item->ID ) || empty( $stock ) ? '&mdash;' : $stock;
				break;
			case 'sold':
				$stock  = WC_Serial_Numbers_Query::init()->from( 'serial_numbers' )->where( [
					'status'     => 'sold',
					'product_id' => $product->get_id()
				] )->count();
				$column = empty( $item->ID ) || empty( $stock ) ? '&mdash;' : $stock;
				break;
			case 'source':
				$source  = get_post_meta( $product->get_id(), '_serial_key_source', true );
				$sources = wc_serial_numbers_get_key_sources();
				if ( array_key_exists( $source, $sources ) ) {
					$source = $sources[ $source ];
				}
				$column = empty( $item->ID ) || empty( $source ) ? 'Manually Generated Serial Number' : $source;
				break;
			default:
				$column = isset( $item->$column_name ) ? $item->$column_name : '&mdash;';
				break;
		}


		return apply_filters( 'wcsn_serials_table_column_content', $column, $item, $column_name );
	}

	/**
	 * @since 1.1.0
	 */
	public function process_bulk_action() {
		$action = $this->current_action();
		if ( ! empty( $action ) ) {
			$ids = [];
			if ( isset( $_REQUEST['ids'] ) ) {
				$ids = array_map( 'absint', $_REQUEST['ids'] );
			}

			foreach ( $ids as $id ) { // Check the permissions on each.
				switch ( $action ) {
					case 'source_automatic':
						update_post_meta($id, '_serial_key_source', 'auto_generated');
						break;
					case 'source_custom':
						update_post_meta($id, '_serial_key_source', 'custom_source');
						break;
					default:
						break;
				}
			}


			wp_safe_redirect( wp_get_referer() );
			exit;
		}
	}


	/**
	 * Retrieve all the data for all the discount codes
	 *
	 * @return object $get_results  of all the data for the discount codes
	 * @since 1.0.0
	 */
	public function get_results() {
		$per_page   = $this->get_items_per_page( 'serials_per_page', $this->per_page );
		$orderby    = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'product_id';
		$page       = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
		$order      = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'desc';
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : '';


		$args = array(
			'per_page'      => $per_page,
			'page'          => isset( $_GET['paged'] ) ? $_GET['paged'] : 1,
			'orderby'       => $orderby,
			'order'         => $order,
			'product_id'    => $product_id,
			'search'        => $search,
			'serial_number' => true
		);

		if ( array_key_exists( $orderby, $this->get_sortable_columns() ) && 'order_date' != $orderby ) {
			$args['orderby'] = $orderby;
		}

		global $wpdb;
		$types = apply_filters( 'wc_serial_numbers_product_types', array( 'product' ) );
		$query = WC_Serial_Numbers_Query::init()->table( 'posts' )
										->where( 'post_status', 'publish' )
										->whereRaw( "ID IN (select post_id from $wpdb->postmeta WHERE meta_key='_is_serial_number' AND meta_value='yes')" )
										->whereRaw( 'post_type IN ("' . implode( '","', $types ) . '")' )
										->whereRaw( "ID NOT IN  (SELECT DISTINCT post_parent FROM {$wpdb->posts} WHERE post_type='product_variation') " )
										->search( sanitize_text_field( $search ), array( 'post_title' ) )
										->page( $page, $per_page );

		$this->total_count = $query->count();

		$results = $query->get();

		return $results;
	}
}
