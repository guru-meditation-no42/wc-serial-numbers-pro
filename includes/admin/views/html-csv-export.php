<?php defined( 'ABSPATH' ) || exit(); ?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo __( 'Export Serial Number', 'wc-serial-numbers-pro' ) ?></h1>


    <form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wc-serial-numbers-export' ) ); ?>">
        <table class="form-table">
            <tbody>
            <!-- Product -->
            <tr scope="row">
                <th scope="row">
                    <label for="product_id">
						<?php esc_html_e( 'Product', 'wc-serial-numbers-pro' ); ?>
                    </label>
                </th>
                <td>
                    <select name="product_id" id="product_id"
                            class="regular-text wc-serial-numbers-select-product"></select>
                    <p class="description"><?php esc_html_e( 'Select product to narrow your export.', 'wc-serial-numbers-pro' ); ?></p>
                </td>
            </tr>

            <!-- status -->
            <tr scope="row">
                <th scope="row">
                    <label for="status">
						<?php esc_html_e( 'Status', 'wc-serial-numbers-pro' ); ?>
                    </label>
                </th>
                <td>
                    <select id="status" name="status" class="regular-text">
						<?php echo sprintf( '<option value="">%s</option>', __( 'All', 'wc-serial-numbers-pro' ) ); ?>
						<?php foreach ( wc_serial_numbers_get_serial_number_statuses() as $key => $option ): ?>
							<?php echo sprintf( '<option value="%s">%s</option>', $key, $option ); ?>
						<?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e( 'Limit export to specific serial number status.', 'wc-serial-numbers-pro' ); ?></p>
                </td>
            </tr>

            <!-- order -->
            <tr scope="row">
                <th scope="row">
                    <label for="filter_order_id">
						<?php esc_html_e( 'Order ID', 'wc-serial-numbers-pro' ); ?>
                    </label>
                </th>
                <td>
                    <input name="order_id" id="filter_order_id" class="regular-text" type="text" autocomplete="off">
                    <p class="description"><?php esc_html_e( 'Limit export to specific order. Use comma for multiple orders.', 'wc-serial-numbers-pro' ); ?></p>
                </td>
            </tr>


            <!-- Fields -->
            <tr scope="row">
                <th scope="row">
                    <label for="fields">
						<?php esc_html_e( 'Fields', 'wc-serial-numbers-pro' ); ?>
                    </label>
                </th>
                <td>
                    <fieldset>

                        <label for="serial_key">
                            <input type="checkbox" name="fields[serial_key]" value="1" id="serial_key" checked="checked"
                                   disabled="disabled"><?php _e( 'Serial Key', 'wc-serial-numbers-pro' ); ?>
                        </label>
                        <br>
                        <label for="product_id_checkbox">
                            <input type="checkbox" name="fields[product_id]" value="1"
                                   checked="checked" id="product_id_checkbox"><?php _e( 'Product ID', 'wc-serial-numbers-pro' ); ?>
                        </label>
                        <br>
                        <label for="activation_limit_checkbox">
                            <input type="checkbox" name="fields[activation_limit]" value="1"
                                   checked="checked" id="activation_limit_checkbox"><?php _e( 'Activation Limit', 'wc-serial-numbers-pro' ); ?>
                        </label>
                        <br>
                        <label for="order_id_checkbox">
                            <input type="checkbox" name="fields[order_id]" value="1"
                                   checked="checked" id="order_id_checkbox"><?php _e( 'Order ID', 'wc-serial-numbers-pro' ); ?>
                        </label>
						<br>
                        <label for="status_checkbox">
                            <input type="checkbox" name="fields[status]" value="1"
                                   checked="checked" id="status_checkbox"><?php _e( 'Status', 'wc-serial-numbers-pro' ); ?>
                        </label>
                        <br>
                        <label for="validity_checkbox">
                            <input type="checkbox" name="fields[validity]" value="1"
                                   checked="checked" id="validity_checkbox"><?php _e( 'Validity', 'wc-serial-numbers-pro' ); ?>
                        </label>
                        <br>
                        <label for="expire_date_checkbox">
                            <input type="checkbox" name="fields[expire_date]" value="1"
                                   checked="checked" id="expire_date_checkbox"><?php _e( 'Expire Date', 'wc-serial-numbers-pro' ); ?>
                        </label>
                        <br>
                        <label for="order_date_checkbox">
                            <input type="checkbox" name="fields[order_date]" value="1"
                                   checked="checked" id="order_date_checkbox"><?php _e( 'Order Date', 'wc-serial-numbers-pro' ); ?>
                        </label>
                        <br>
						<label for="customer_name_checkbox">
							<input type="checkbox" name="fields[customer_name]" value="1" id="customer_name_checkbox" checked="checked"><?php _e( 'Customer Name', 'wc-serial-numbers-pro' ); ?>
						</label>
						<br>

                    </fieldset>
                </td>
            </tr>

            <tr>
                <td></td>
                <td>
                    <p class="submit">
                        <input type="hidden" name="action" value="wc_serial_numbers_export">
						<?php wp_nonce_field( 'wc_serial_numbers_export' ); ?>
						<?php echo sprintf( '<input name="submit" id="submit" class="button button-primary" value="%s"  type="submit">', __( 'Export', 'wc-serial-numbers-pro' ) ); ?>
                    </p>
                </td>
            </tr>

            </tbody>
        </table>

    </form>

</div>
