<?php
defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WC_Serial_Numbers_Pro_Admin_Generators_List_Table extends WP_List_Table {

	public function __construct() {
		parent::__construct( [
			'singular' => __( 'Generator', 'wc-serial-numbers-pro' ),
			'plural'   => __( 'Generators', 'wc-serial-numbers-pro' ),
			'ajax'     => false
		] );
	}

	public function get_columns() {

		$columns = array(
			'cb'               => '<input type="checkbox" />',
			'pattern'          => __( 'Pattern', 'wc-serial-numbers-pro' ),
			'product'          => __( 'Product', 'wc-serial-numbers-pro' ),
			'type'             => __( 'Type', 'wc-serial-numbers-pro' ),
			'activation_limit' => __( 'Activation Limit', 'wc-serial-numbers-pro' ),
			'validity'         => __( 'Validity', 'wc-serial-numbers-pro' ),
			'generate'         => __( 'Generate', 'wc-serial-numbers-pro' ),
		);

		return $columns;
	}

	/**
	 * Sortable columns
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_sortable_columns() {

		$shortable = array();

		return $shortable;
	}

	/**
	 * Default columns
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @since 1.0.0
	 *
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'product':
				$product_id = get_post_meta( $item->ID, 'product_id', true );
				$line       = ! empty( $product_id ) ? get_the_title( $product_id ) : '&#45;';
				echo ! empty( $product_id ) ? '<a href="' . get_edit_post_link( $product_id ) . '">' . $line . '</a>' : $line;
				break;
			case 'type':
				$type = get_post_meta( $item->ID, 'type', true );
				echo empty( $type ) ? '&#45;' : ucfirst( $type );
				break;
			case 'activation_limit':
				$activation_limit = get_post_meta( $item->ID, 'activation_limit', true );
				echo empty( $activation_limit ) ? __( 'Unlimited', 'wc-serial-numbers-pro' ) : $activation_limit;
				break;
			case 'validity':
				$validity = get_post_meta( $item->ID, 'validity', true );
				echo empty( $validity ) ? __( 'Lifetime', 'wc-serial-numbers-pro' ) : $validity;
				break;
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	protected function column_cb( $item ) {
		return "<input type='checkbox' name='ids[]' id='id_{$item->ID}' value='{$item->ID}' />";
	}

	/**
	 * column pattern
	 *
	 * @param $item
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	function column_pattern( $item ) {
		$row_actions           = array();
		$edit_url              = add_query_arg( [ 'action' => 'edit', 'id' => $item->ID ], admin_url( 'admin.php?page=wc-serial-numbers-generators' ) );
		$delete_url            = add_query_arg( [ 'action' => 'delete', 'id' => $item->ID ], admin_url( 'admin.php?page=wc-serial-numbers-generators' ) );
		$row_actions['edit']   = sprintf( '<a href="%1$s">%2$s</a>', $edit_url, __( 'Edit', 'wc-serial-numbers-pro' ) );
		$row_actions['delete'] = sprintf( '<a href="%1$s">%2$s</a>', $delete_url, __( 'Delete', 'wc-serial-numbers-pro' ) );
		$pattern               = get_post_meta( $item->ID, 'pattern', true );

		return sprintf( '<code>%1$s</code> %2$s', $pattern, $this->row_actions( $row_actions ) );
	}

	/**
	 * column generate
	 *
	 * @param $item
	 *
	 * @since 1.0.0
	 *
	 */
	public function column_generate( $item ) {
		?>
		<input type="number" class="serial_count" maxlength="2" min="1" max="10000" style="width: 100px;"/>
		<?php submit_button( __( 'Generate', 'wc-serial-numbers-pro' ), 'secondary', '', false , array(
			'class'      => 'generate-serials',
			'data-id'    => $item->ID,
			'data-nonce' => wp_create_nonce( 'generate_serials' ),
		) ); ?>

		<div class="spinner" style="margin-top: -5px;float:none;"></div>
		<?php
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
			'delete' => __( 'Delete', 'wc-serial-numbers-pro' )
		);

		return $actions;
	}


	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */

	public function prepare_items() {

		$columns  = $this->get_columns();
		$sortable = $this->get_sortable_columns();
		$per_page = 20;

		$args = array(
			'post_type'      => 'wcsn_generator_rule',
			'posts_per_page' => $per_page,
			'paged'          => ! empty( $_REQUEST['paged'] ) ? intval( $_REQUEST['paged'] ) : 1,
		);

		if ( ! empty( $_REQUEST['s'] ) ) {
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => 'pattern',
					'value'   => sanitize_text_field( $_REQUEST['s'] ),
					'compare' => 'LIKE'
				)
			);
		}

		$query = new WP_Query( $args );

		$this->items = $query->posts;
		$this->set_pagination_args( array(
			'total_items' => $query->found_posts,
			'per_page'    => $per_page
		) );

		$this->_column_headers = array( $columns, array(), $sortable );
	}

}
