<?php defined( 'ABSPATH' ) || exit(); ?>
<div class="wc-serial-numbers-key-source-settings options_group" data-source="auto_generated" style="display: none;">
	<?php
	woocommerce_wp_text_input(
		array(
			'id'          => "_serial_number_key_prefix",
			'name'        => "_serial_number_key_prefix",
			'label'       => __( 'Serial number prefix', 'wc-serial-numbers-pro' ),
			'description' => __( 'Optional prefix for generated Serial numbers.', 'wc-serial-numbers-pro' ),
			'placeholder' => __( 'N/A', 'wc-serial-numbers-pro' ),
			'value'       => get_post_meta( $product_id, '_serial_number_key_prefix', true ),
			'desc_tip'    => true,
		)
	);
	woocommerce_wp_text_input(
		array(
			'id'          => "_activation_limit",
			'name'        => "_activation_limit",
			'label'       => __( 'Activation limit', 'wc-serial-numbers-pro' ),
			'description' => __( 'Amount of activations possible per Serial number. 0 means unlimited. If its not a software product ignore this.', 'wc-serial-numbers-pro' ),
			'placeholder' => __( '0', 'wc-serial-numbers-pro' ),
			'value'       => get_post_meta( $product_id, '_activation_limit', true ),
			'desc_tip'    => true,
		)
	);
	woocommerce_wp_text_input(
		array(
			'id'          => "_validity",
			'name'        => "_validity",
			'label'       => __( 'Validity', 'wc-serial-numbers-pro' ),
			'description' => __( 'The number validity in days.', 'wc-serial-numbers-pro' ),
			'placeholder' => __( '0', 'wc-serial-numbers-pro' ),
			'value'       => get_post_meta( $product_id, '_validity', true ),
			'desc_tip'    => true,
		)
	);

	?>
</div>
