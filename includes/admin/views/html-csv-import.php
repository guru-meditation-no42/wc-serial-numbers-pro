<?php defined( 'ABSPATH' ) || exit(); ?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo __( 'Import Serial Number', 'wc-serial-numbers-pro' ) ?></h1>

    <h3 class="title"><?php _e( 'Import Serial Numbers From CSV File', 'wc-serial-numbers-pro' ); ?></h3>
	<?php echo sprintf( __( 'Upload a CSV file containing Serial Number to import the serial numbers. Download %s sample file %s to learn how to format your CSV file', 'wc-serial-numbers-pro' ), '<a target="_blank" href="' . wc_serial_numbers_pro()->plugin_url() . '/data/sample.csv' . '">', '</a>' ); ?>

    <hr/>
    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
        <table class="form-table">

            <tbody>
            <!-- File -->
            <tr scope="row">
                <th scope="row">
                    <label for="csv_file">
						<?php esc_html_e( 'CSV File', 'wc-serial-numbers-pro' ); ?>
                    </label>
                </th>
                <td>
                    <input class="input-field" name="file" type="file" accept=".csv" required="required">
                    <p class="description"><?php esc_html_e( 'CSV file containing serial numbers.', 'wc-serial-numbers-pro' ); ?></p>
                </td>
            </tr>

            <tr>
                <td></td>
                <td>
                    <p class="submit">
                        <input type="hidden" name="action" value="wc_serial_numbers_csv_import">
						<?php wp_nonce_field( 'wc_serial_numbers_csv_import' ); ?>
						<?php echo sprintf( '<input name="submit" id="submit" class="button button-primary" value="%s"  type="submit">', __( 'Import', 'wc-serial-numbers-pro' ) ); ?>
                    </p>
                </td>
            </tr>


            </tbody>

        </table>
    </form>


    <h3 class="title"><?php _e( 'Import Serial Numbers From .txt File', 'wc-serial-numbers-pro' ); ?></h3>
	<?php echo sprintf( __( 'Upload a TXT file containing Serial Numbers & select product from below to import. Download %s sample file %s to learn how to format your TXT file', 'wc-serial-numbers-pro' ), '<a target="_blank" href="' . wc_serial_numbers_pro()->plugin_url() . '/data/sample.txt' . '">', '</a>' ); ?>
    <hr/>
    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>"  enctype="multipart/form-data">
        <table class="form-table">

            <tbody>
            <!-- File -->
            <tr scope="row">
                <th scope="row">
                    <label for="text_file">
						<?php esc_html_e( 'Text File', 'wc-serial-numbers-pro' ); ?>
                    </label>
                </th>
                <td>
                    <input class="input-field" name="file" type="file" accept=".txt">
                    <p class="description"><?php esc_html_e( 'Text file containing serial numbers.', 'wc-serial-numbers-pro' ); ?></p>
                </td>
            </tr>

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
                    <p class="description"><?php esc_html_e( 'The product to which the serial number will be assigned.', 'wc-serial-numbers-pro' ); ?></p>
                </td>
            </tr>

            <tr scope="row">
                <th scope="row">
                    <label for="activation_limit">
						<?php esc_html_e( 'Activation Limit', 'wc-serial-numbers-pro' ); ?>
                    </label>
                </th>
                <td>
                    <input name="activation_limit" id="activation_limit" class="regular-text" type="number" value="1"
                           autocomplete="off">
                    <p class="description"><?php esc_html_e( 'Maximum number of times the key can be used to activate the software. If the product is not software keep blank.', 'wc-serial-numbers-pro' ); ?></p>
                </td>
            </tr>

            <!-- Valid for -->
            <tr scope="row">
                <th scope="row">
                    <label for="validity">
						<?php esc_html_e( 'Validity (days)', 'wc-serial-numbers-pro' ); ?>
                    </label>
                </th>
                <td>
                    <input name="validity" id="validity" class="regular-text" type="number">
                    <p class="description"><?php esc_html_e( 'The number of days the key will be valid for after the purchase date.', 'wc-serial-numbers-pro' ); ?></p>
                </td>
            </tr>

            <!-- Expire Date -->
            <tr scope="row">
                <th scope="row">
                    <label for="expire_date"><?php esc_html_e( 'Expires at', 'wc-serial-numbers-pro' ); ?></label>
                </th>
                <td>
                    <input name="expire_date" id="expire_date" class="regular-text wc-serial-numbers-select-date" type="text"
                           autocomplete="off">
                    <p class="description"><?php esc_html_e( 'After this date the key will not be assigned with any order. Leave blank for no expire date.', 'wc-serial-numbers-pro' ); ?></p>
                </td>
            </tr>

            <tr>
                <td></td>
                <td>
                    <p class="submit">
                        <input type="hidden" name="action" value="wc_serial_numbers_txt_import">
						<?php wp_nonce_field( 'wc_serial_numbers_txt_import' ); ?>
						<?php echo sprintf( '<input name="submit" id="submit" class="button button-primary" value="%s"  type="submit">', __( 'Import', 'wc-serial-numbers-pro' ) ); ?>
                    </p>
                </td>
            </tr>

            </tbody>

        </table>
    </form>
</div>


