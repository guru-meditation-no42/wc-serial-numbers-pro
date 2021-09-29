<?php
defined('ABSPATH') || exit();

class WC_Serial_Numbers_Pro_Admin_MetaBoxes
{
	public static function init()
	{
		add_filter('woocommerce_process_product_meta', array(__CLASS__, 'save_simple_product_meta'));
		add_action('woocommerce_save_product_variation', array(__CLASS__, 'save_variation_meta'), 10, 2);

		add_filter('wc_serial_numbers_delivery_quantity_field_args', array(
			__CLASS__,
			'unlock_per_item_quantity_input'
		));
		add_action('wc_serial_numbers_source_settings_generator_rule', array(
			__CLASS__,
			'product_key_source_generator_rule_settings'
		));
		add_action('wc_serial_numbers_source_settings_auto_generated', array(
			__CLASS__,
			'product_key_source_auto_generated_settings'
		));
		add_action('woocommerce_product_after_variable_attributes', array(
			__CLASS__,
			'add_variation_fields'
		), 10, 3);

		add_action('woocommerce_variation_options', array(__CLASS__, 'add_variation_enable_checkbox'), 10, 3);

		add_filter('wc_serial_numbers_variation_source_settings_generator_rule', array(
			__CLASS__,
			'variation_source_settings_generator_rule'
		), 10, 2);
		add_filter('wc_serial_numbers_variation_source_settings_auto_generated', array(
			__CLASS__,
			'variation_source_settings_auto_generated'
		), 10, 2);
	}


	/**
	 * Save simple product meta.
	 *
	 * @since 1.2.0
	 */
	public static function save_simple_product_meta()
	{
		global $post;
		update_post_meta($post->ID, '_delivery_quantity', empty($_POST['_delivery_quantity']) ? 1 : intval($_POST['_delivery_quantity']));
		update_post_meta($post->ID, '_generator_id', empty($_POST['_generator_id']) ? 1 : intval($_POST['_generator_id']));
		update_post_meta($post->ID, '_serial_key_source', empty($_POST['_serial_key_source']) ? 'custom_source' : sanitize_text_field($_POST['_serial_key_source']));
		update_post_meta($post->ID, '_serial_number_key_prefix', empty($_POST['_serial_number_key_prefix']) ? 'serial-' : sanitize_text_field($_POST['_serial_number_key_prefix']));
		update_post_meta($post->ID, '_activation_limit', empty($_POST['_activation_limit']) ? 0 : sanitize_text_field($_POST['_activation_limit']));
		update_post_meta($post->ID, '_validity', empty($_POST['_validity']) ? 0 : sanitize_text_field($_POST['_validity']));
	}

	/**
	 * Save variation meta.
	 *
	 * @param $variation_id
	 * @param $loop
	 *
	 * @since 1.2.0
	 */
	public static function save_variation_meta($variation_id, $loop)
	{
		if (!empty($_REQUEST['variable_is_serial'][$loop]) && $_REQUEST['variable_is_serial'][$loop] == 'on') {
			update_post_meta($variation_id, '_is_serial_number', 'yes');
		} else {
			update_post_meta($variation_id, '_is_serial_number', 'no');
		}
		update_post_meta($variation_id, '_serial_key_source', empty($_REQUEST['_serial_key_source'][$loop]) ? 'custom_source' : sanitize_key($_REQUEST['_serial_key_source'][$loop]));
		update_post_meta($variation_id, '_serial_number_key_prefix', empty($_REQUEST['_serial_number_key_prefix'][$loop]) ? '' : sanitize_key($_REQUEST['_serial_number_key_prefix'][$loop]));
		update_post_meta($variation_id, '_delivery_quantity', empty($_REQUEST['_delivery_quantity'][$loop]) ? '1' : intval($_REQUEST['_delivery_quantity'][$loop]));
		update_post_meta($variation_id, '_activation_limit', empty($_REQUEST['_activation_limit'][$loop]) ? '0' : intval($_REQUEST['_activation_limit'][$loop]));
		update_post_meta($variation_id, '_validity', empty($_REQUEST['_validity'][$loop]) ? '0' : intval($_REQUEST['_validity'][$loop]));
		update_post_meta($variation_id, '_generator_id', empty($_REQUEST['_generator_id'][$loop]) ? '0' : intval($_REQUEST['_generator_id'][$loop]));
		update_post_meta($variation_id, '_software_version', empty($_REQUEST['_software_version'][$loop]) ? '' : sanitize_text_field($_REQUEST['_software_version'][$loop]));
	}

	/**
	 * Unlock input field
	 *
	 * @param $args
	 *
	 * @return mixed
	 * @since 1.2.0
	 */
	public static function unlock_per_item_quantity_input($args)
	{
		unset($args['custom_attributes']['disabled']);

		return $args;
	}

	public static function product_key_source_generator_rule_settings($product_id)
	{
		include dirname(__FILE__) . '/views/html-simple-generator-rule-settings.php';
	}

	public static function product_key_source_auto_generated_settings($product_id)
	{
		include dirname(__FILE__) . '/views/html-simple-auto-generated-settings.php';
	}

	/**
	 * Show Enable serial number checkbox for variable product
	 *
	 * @param $loop
	 * @param $variation_data
	 * @param $variation
	 *
	 * @since 1.2.0
	 */
	public static function add_variation_enable_checkbox($loop, $variation_data, $variation)
	{
		$saved = get_post_meta($variation->ID, '_is_serial_number', true);
?>
		<label class="tips" data-tip="<?php esc_html_e('Enable this option if this is a serial key enabled.', 'wc-serial-numbers-pro'); ?>">
			<?php esc_html_e('Serial Number', 'wc-serial-numbers-pro'); ?>:
			<input type="checkbox" class="checkbox variable_is_serial" name="variable_is_serial[<?php echo esc_attr($loop); ?>]" <?php checked($saved, 'yes'); ?> />
		</label>
	<?php
	}

	/**
	 * Variation metabox.
	 *
	 * @param $loop
	 * @param $variation_data
	 * @param $variation
	 *
	 * @since 1.2.0
	 */
	public static function add_variation_fields($loop, $variation_data, $variation)
	{
		$serial_meta = get_post_meta($variation->ID, '_is_serial_number', true);
	?>
		<div class="wc-serial-numbers-variation-settings" style="display: <?php echo $serial_meta == 'yes' ? 'block' : 'none'; ?>">
			<?php
			echo sprintf('<p class="wc-serial-numbers-settings-title">%s</p>', __('Serial Numbers settings', 'wc-serial-numbers-pro'));

			$delivery_qty = get_post_meta($variation->ID, '_delivery_quantity', true);
			woocommerce_wp_text_input(array(
				'id'          => "_delivery_quantity{$loop}",
				'name'        => "_delivery_quantity[{$loop}]",
				'label'       => __('Delivery quantity', 'wc-serial-numbers-pro'),
				'description' => __('The number of serial key will be delivered per item.', 'wc-serial-numbers-pro'),
				'value'       => empty($delivery_qty) ? 1 : $delivery_qty,
				'type'        => 'number',
				'desc_tip'    => true,
			));

			$source  = get_post_meta($variation->ID, '_serial_key_source', true);
			$sources = wc_serial_numbers_get_key_sources();
			woocommerce_wp_radio(array(
				'id'      => "_serial_key_source{$loop}",
				'name'    => "_serial_key_source[{$loop}]",
				'class'   => "serial_key_source",
				'label'   => __('Serial Key Source', 'wc-serial-numbers-pro'),
				'value'   => empty($source) ? 'custom_source' : $source,
				'options' => $sources,
			));

			foreach (array_keys($sources) as $source) {
				do_action('wc_serial_numbers_variation_source_settings_' . $source, $variation->ID, $loop);
				do_action('wc_serial_numbers_variation_source_settings', $source, $variation->ID, $loop);
			}
			if (!wc_serial_numbers_software_support_disabled()) {
				woocommerce_wp_text_input(
					array(
						'id'          => "_software_version{$loop}",
						'name'        => "_software_version[{$loop}]",
						'label'       => __('Software Version', 'wc-serial-numbers-pro'),
						'description' => __('Version number for the software. If its not a software product ignore this.', 'wc-serial-numbers-pro'),
						'placeholder' => __('e.g. 1.0', 'wc-serial-numbers-pro'),
						'desc_tip'    => true,
					)
				);
			}

			if ('custom_source' == $source) {
				echo sprintf(
					'<p class="form-field options_group"><label>%s</label><span class="description"><code>%d</code> %s</span></p>',
					__('Available', 'wc-serial-numbers-pro'),
					WC_Serial_Numbers_Query::init()->table('serial_numbers')->where([
						'product_id' => $variation->ID,
						'status'     => 'available'
					])->count(),
					__('Serial Number available for sale', 'wc-serial-numbers-pro')
				);
			}
			?>
		</div>
	<?php
	}

	/**
	 * Generator rule key source.
	 *
	 * @param $product_id
	 *
	 * @since 1.2.0
	 */
	public static function variation_source_settings_auto_generated($product_id, $loop)
	{
	?>
		<div class="wc-serial-numbers-key-source-settings options_group" data-source="auto_generated" style="display: none;">
			<?php
			woocommerce_wp_text_input(
				array(
					'id'          => "_serial_number_key_prefix{$loop}",
					'name'        => "_serial_number_key_prefix[{$loop}]",
					'label'       => __('Serial number prefix', 'wc-serial-numbers-pro'),
					'description' => __('Optional prefix for generated Serial numbers.', 'wc-serial-numbers-pro'),
					'placeholder' => __('N/A', 'wc-serial-numbers-pro'),
					'value'       => get_post_meta($product_id, '_serial_number_key_prefix', true),
					'desc_tip'    => true,
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => "_activation_limit{$loop}",
					'name'        => "_activation_limit[{$loop}]",
					'label'       => __('Activation limit', 'wc-serial-numbers-pro'),
					'description' => __('Amount of activations possible per Serial number. 0 means unlimited. If its not a software product ignore this.', 'wc-serial-numbers-pro'),
					'placeholder' => __('0', 'wc-serial-numbers-pro'),
					'value'       => get_post_meta($product_id, '_activation_limit', true),
					'desc_tip'    => true,
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => "_validity{$loop}",
					'name'        => "_validity[{$loop}]",
					'label'       => __('Validity', 'wc-serial-numbers-pro'),
					'description' => __('The number validity in days.', 'wc-serial-numbers-pro'),
					'placeholder' => __('0', 'wc-serial-numbers-pro'),
					'value'       => get_post_meta($product_id, '_validity', true),
					'desc_tip'    => true,
				)
			);
			?>
		</div>
	<?php
	}

	/**
	 * Generator rule key source.
	 *
	 * @param $product_id
	 *
	 * @since 1.2.0
	 */
	public static function variation_source_settings_generator_rule($product_id, $loop)
	{
		$generators = get_posts(array(
			'post_type'  => 'wcsn_generator_rule',
			'meta_key'   => 'product_id',
			'meta_value' => $product_id,
		));
		$options    = [];
		foreach ($generators as $generator) {
			$options[$generator->ID] = get_post_meta($generator->ID, 'pattern', true);
		}
	?>
		<div class="wc-serial-numbers-key-source-settings options_group" data-source="generator_rule" style="display: none;">
			<?php
			woocommerce_wp_select(
				array(
					'id'          => "_generator_id{$loop}",
					'name'        => "_generator_id[{$loop}]",
					'label'       => __('Generator ID', 'wc-serial-numbers-pro'),
					'description' => __('Select generator source that will be used to generate serial numbers for the product.', 'wc-serial-numbers-pro'),
					'options'     => $options,
					'desc_tip'    => true,
				)
			);
			?>
		</div>
<?php
	}
}

WC_Serial_Numbers_Pro_Admin_MetaBoxes::init();
