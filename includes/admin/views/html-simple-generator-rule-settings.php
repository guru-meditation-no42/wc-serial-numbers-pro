<?php defined( 'ABSPATH' ) || exit(); ?>
<div class="wc-serial-numbers-key-source-settings options_group" data-source="generator_rule" style="display: none;">
	<?php
	$generators = get_posts( array(
		'post_type'  => 'wcsn_generator_rule',
		'meta_key'   => 'product_id',
		'meta_value' => $product_id,
	) );
	$options    = [];
	foreach ( $generators as $generator ) {
		$options[ $generator->ID ] = get_post_meta( $generator->ID, 'pattern', true );
	}
	woocommerce_wp_select(
		array(
			'id'          => '_generator_id',
			'label'       => __( 'Generator ID', 'wc-serial-numbers-pro' ),
			'description' => __( 'Select generator source that will be used to generate serial numbers for the product.', 'wc-serial-numbers-pro' ),
			'options'     => $options,
			'desc_tip'    => true,
		)
	);
	?>
</div>
