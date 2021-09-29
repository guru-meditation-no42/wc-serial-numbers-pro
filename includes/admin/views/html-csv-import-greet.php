<div>
	<p><?php _e( 'Hi there! Upload a CSV file containing Serial Number to import the serial numbers into your shop.', 'wc-serial-numbers-pro' ); ?></p>
	<p><?php _e( 'Choose a CSV (.csv) file to upload, then click Upload file and import.', 'wc-serial-numbers-pro' ); ?></p>
	<p><?php printf( __( 'Visit our <a target="_blank" href="%s">documentation</a> to learn about the importer.', 'wc-serial-numbers-pro' ), esc_url( 'https://www.pluginever.com/docs/wocommerce-serial-numbers/import-and-export-format' ) ); ?></p>

	<?php wp_import_upload_form( 'admin.php?import=woocommerce_serial_numbers_csv&amp;step=1' ); ?>
</div>
