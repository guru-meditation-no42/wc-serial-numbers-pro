<?php
defined( 'ABSPATH' ) || exit();


/**
 * generate serial number
 *
 * @param string $pattern
 * @param int $quantity
 * @param string $type
 * @param int $last
 *
 * @return array
 * @since 1.0.0
 *
 */
function wc_serial_numbers_pro_generate_serial_keys( $pattern = 'SERIAL-#####################', $quantity = 5, $type = 'random', $last = 0 ) {
	$pattern_length       = strlen( $pattern );
	$pattern_mask_length  = substr_count( $pattern, '#' );
	$required_mask_length = strlen( $quantity + $last );
	if ( $pattern_mask_length < $required_mask_length ) {
		$static              = $pattern_length - $pattern_mask_length;
		$pad_length          = $static + $required_mask_length;
		$pattern_mask_length = $required_mask_length;
		$pattern             = str_pad( $pattern, $pad_length, '#' );
	}

	$serial_numbers = array();
	for ( $i = 1; $i <= $quantity; $i ++ ) {
		$serial_number = $pattern;
		if ( $type == 'random' ) {
			$new_serial_number = strtolower( wp_generate_password( $pattern_mask_length, false ) );
		} else {
			$new_serial_number = str_pad( $last + $i, $pattern_mask_length, '0', STR_PAD_LEFT );

		}

		$new_serial_number_parts = str_split( $new_serial_number );
		for ( $j = 0; $j <= count( $new_serial_number_parts ) - 1; $j ++ ) {
			if ( strpos( $serial_number, '#' ) !== false ) {
				$occurrence    = strpos( $serial_number, '#' );
				$serial_number = substr_replace( $serial_number, $new_serial_number_parts[ $j ], $occurrence, 1 );
			}
		}

		$serial_numbers[] = wc_serial_numbers_pro_format_serial_number( $serial_number );
	}

	return $serial_numbers;
}

/**
 * Replace date tag with dates
 *
 * @param $matches
 *
 * @return false|string
 * @since 1.0.0
 *
 */
function wc_serial_numbers_pro_preg_match_date( $matches ) {
	return date( substr( $matches[0], 1, strlen( $matches[0] ) - 2 ) );
}

/**
 * Find dates
 *
 * @param $serial_number
 *
 * @return null|string|string[]
 * @since 1.0.0
 *
 */
function wc_serial_numbers_pro_format_serial_number( $serial_number ) {
	return preg_replace_callback( '/({[a-zA-Z0-9]+})/', 'wc_serial_numbers_pro_preg_match_date', $serial_number );
}

/**
 * Get Serial Source.
 *
 * @param $product_id
 *
 * @return bool|mixed
 * @since 1.2.0
 */
function wc_serial_numbers_pro_get_serial_source( $product_id ) {
	$source = get_post_meta( $product_id, '_serial_key_source', true );

	if ( 'generator_rule' == $source ) {
		$generator_id = (int) get_post_meta( $product_id, '_generator_id', true );
		if ( empty( $generator_id ) ) {
			return false;
		}

		if ( empty( get_post( $generator_id ) ) ) {
			return false;
		}
	}

	return $source;
}

/**
 * Generate serial numbers from generator rule.
 *
 * @param $generator_id
 * @param int $quantity
 * @param bool $user_initiated
 *
 * @return int
 * @since 1.2.0
 */
function wc_serial_numbers_pro_generate_generator_serials( $generator_id, $quantity = 1, $user_initiated = false ) {
	$pattern          = get_post_meta( $generator_id, 'pattern', true );
	$type             = get_post_meta( $generator_id, 'type', true );
	$product_id       = get_post_meta( $generator_id, 'product_id', true );
	$activation_limit = get_post_meta( $generator_id, 'activation_limit', true );
	$validity         = get_post_meta( $generator_id, 'validity', true );
	$expire_date      = get_post_meta( $generator_id, 'expire_date', true );
	$total_generated  = intval( get_post_meta( $generator_id, 'total_generated', true ) );
	$generated        = 0;
	$serial_keys      = wc_serial_numbers_pro_generate_serial_keys( $pattern, $quantity, $type, $total_generated );

	foreach ( $serial_keys as $serial_key ) {
		$data = array(
			'serial_key'       => $serial_key,
			'product_id'       => $product_id,
			'activation_limit' => $activation_limit,
			'status'           => 'available',
			'validity'         => $validity,
			'expire_date'      => $expire_date,
			'order_date'       => '',
			'source'           => $user_initiated ? 'custom_source' : 'generator_rule',
		);

		if ( is_numeric( wc_serial_numbers_insert_serial_number( $data ) ) ) {
			$generated += 1;
		}
	}
	if ( $type == 'sequential' ) {
		$pointer = $total_generated + $generated;
		update_post_meta( $generator_id, 'total_generated', $pointer );
	}

	return $generated;
}

/**
 * Generate automatically serial numbers.
 *
 * @param $product_id
 * @param int $quantity
 *
 * @return int
 * @since 1.2.0
 */
function wc_serial_numbers_pro_generate_automatic_serials( $product_id, $quantity = 1 ) {
	$prefix           = get_post_meta( $product_id, '_serial_number_key_prefix', true );
	$activation_limit = intval( get_post_meta( $product_id, '_activation_limit', true ) );
	$validity         = intval( get_post_meta( $product_id, '_validity', true ) );
	$pattern          = str_pad( $prefix, apply_filters( 'wc_serial_numbers_automatic_key_length', 32 ), '#' );
	$serial_keys      = wc_serial_numbers_pro_generate_serial_keys( $pattern, $quantity, 'random', 0 );

	$generated = 0;
	foreach ( $serial_keys as $serial_key ) {
		$data = array(
			'serial_key'       => $serial_key,
			'product_id'       => $product_id,
			'activation_limit' => $activation_limit,
			'status'           => 'available',
			'validity'         => $validity,
			'order_date'       => '',
			'source'           => 'auto_generated',
		);
		if ( wc_serial_numbers_insert_serial_number( $data ) ) {
			$generated += 1;
		}
	}

	return $generated;
}

function wc_serial_numbers_pro_autocomplete_statuses( $statuses ) {
	$pending    = get_option( 'wc_serial_numbers_autocomplete_status_pending' );
	$on_hold    = get_option( 'wc_serial_numbers_autocomplete_status_on_hold' );
	if ( $pending == 'yes' ) {
		$statuses[] = 'pending';
	}
	if ( $on_hold == 'yes' ) {
		$statuses[] = 'on-hold';
	}

	return $statuses;
}

add_filter( 'wc_serial_numbers_autocomplete_statuses', 'wc_serial_numbers_pro_autocomplete_statuses' );
