<?php
defined('ABSPATH') || exit();

class WC_Serial_Numbers_Pro_Admin_Generators_Screen
{
	/**
	 * Init actions.
	 * @since 1.1.0
	 */
	public static function init()
	{
		add_action('admin_post_wc_serial_numbers_edit_generator', array(__CLASS__, 'action_edit_generator'));
	}

	/**
	 * @since 1.1.0
	 */
	public static function action_edit_generator()
	{
		if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'wc_serial_numbers_edit_generator')) {
			wp_die('No, Cheating!');
		}

		$id     = !empty($_POST['id']) ? intval($_POST['id']) : '';
		$posted = array(
			'id'               => !empty($_POST['id']) ? intval($_POST['id']) : '',
			'product_id'       => !empty($_POST['product_id']) ? intval($_POST['product_id']) : '',
			'pattern'          => !empty($_POST['pattern']) ? sanitize_textarea_field($_POST['pattern']) : '',
			'activation_limit' => !empty($_POST['activation_limit']) ? intval($_POST['activation_limit']) : '',
			'validity'         => !empty($_POST['validity']) ? intval($_POST['validity']) : '',
			'expire_date'      => !empty($_POST['expire_date']) ? sanitize_text_field($_POST['expire_date']) : '',
			'type'             => !empty($_POST['type']) ? sanitize_text_field($_POST['type']) : 'random',
			'total_generated'  => !empty($_POST['total_generated']) ? intval($_POST['total_generated']) : '',
		);

		$redirect_args = array(
			'page'   => 'wc-serial-numbers-generators',
			'action' => empty($id) ? 'add' : 'edit',
		);
		if (!empty($id)) {
			$redirect_args['id'] = $id;
		}

		if (empty($posted['product_id'])) {
			WC_Serial_Numbers_Admin_Notice::add_notice(__('You must select a product to add generator.', 'wc-serial-numbers-pro'), ['type' => 'error']);
			wp_safe_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
			exit();
		}

		if (empty($posted['pattern'])) {
			WC_Serial_Numbers_Admin_Notice::add_notice(__('You must input a serial number pattern to add generator rule.', 'wc-serial-numbers-pro'), ['type' => 'error']);
			wp_safe_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
			exit();
		}

		if (!empty($id)) {
			wp_update_post(array(
				'ID'         => $id,
				'meta_input' => $posted
			));

			if ('sequential' == $posted['type']) {
				update_post_meta($id, 'total_generated', $posted['total_generated']);
			}

			WC_Serial_Numbers_Admin_Notice::add_notice(__('Generator updated successfully', 'wc-serial-numbers-pro'), ['type' => 'success']);
			unset($redirect_args['action']);
			unset($redirect_args['id']);
			wp_safe_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
			exit();
		}

		$id = wp_insert_post(array(
			'post_type'   => 'wcsn_generator_rule',
			'post_title'  => 'Generator rule',
			'post_status' => 'publish',
			'meta_input'  => $posted
		));
		if (is_wp_error($id)) {
			WC_Serial_Numbers_Admin_Notice::add_notice($id->get_error_message(), ['type' => 'error']);
		} else {
			WC_Serial_Numbers_Admin_Notice::add_notice(__('Generator created successfully.', 'wc-serial-numbers-pro'), ['type' => 'success']);
			unset($redirect_args['action']);
			unset($redirect_args['id']);
		}

		wp_safe_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
		exit();
	}

	/**
	 * Conditionally render view.
	 *
	 * @since 1.1.0
	 */
	public static function output()
	{
		$action = isset($_GET['action']) && !empty($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
		if (in_array($action, ['add', 'edit'])) {
			self::render_add($action);
		} else {
			self::render_table();
		}
	}

	/**
	 * Render list table.
	 * @since 1.1.0
	 */
	public static function render_table()
	{
		require_once dirname(__DIR__) . '/tables/class-wc-serial-numbers-pro-generators-list-table.php';
		$table    = new WC_Serial_Numbers_Pro_Admin_Generators_List_Table();
		$doaction = $table->current_action();
		self::handle_bulk_actions($doaction);
		$table->prepare_items();
?>
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<?php _e('Generators', 'wc-serial-numbers-pro'); ?>
			</h1>
			<a href="<?php echo admin_url('admin.php?page=wc-serial-numbers-generators&action=add') ?>" class="add-serial-title page-title-action">
				<?php _e('Add Generator Rule', 'wc-serial-numbers-pro') ?>
			</a>
			<hr class="wp-header-end">

			<form id="wc-serial-numbers-list" method="get">
				<?php
				$table->search_box(__('Search', 'wc-serial-numbers-pro'), 'search');
				$table->display();
				?>
				<input type="hidden" name="page" value="wc-serial-numbers-generators">
			</form>
		</div>
	<?php
	}

	/**
	 * Render add or edit view.
	 *
	 * @param $action string
	 *
	 * @since 1.1.0
	 */
	public static function render_add($action)
	{
		$id = isset($_GET['id']) ? absint($_GET['id']) : 0;
		if (empty($id) && 'edit' == $action) {
			wp_redirect(add_query_arg(['action' => 'add'], remove_query_arg(array(
				'_wp_http_referer',
				'_wpnonce',
				'id'
			), wp_unslash($_SERVER['REQUEST_URI']))));
			exit;
		}
		$update = false;
		$item   = array(
			'product_id'       => '',
			'pattern'          => '',
			'type'             => 'random',
			'activation_limit' => '',
			'validity'         => '',
			'expire_date'      => '',
		);

		if (!empty($id) && $serial = WC_Serial_Numbers_Query::init()->from('posts')->select('ID')->where('post_type', 'wcsn_generator_rule')->find($id, 'ID')) {
			$item['product_id']       = get_post_meta($id, 'product_id', true);
			$item['pattern']          = get_post_meta($id, 'pattern', true);
			$item['type']             = get_post_meta($id, 'type', true);
			$item['activation_limit'] = get_post_meta($id, 'activation_limit', true);
			$item['validity']         = get_post_meta($id, 'validity', true);
			$item['expire_date']      = get_post_meta($id, 'expire_date', true);
			$item['total_generated']  = get_post_meta($id, 'total_generated', true);
			$update                   = true;
		}

	?>
		<div class="wrap">

			<h1 class="wp-heading-inline">
				<?php if ($update) : ?>
					<?php _e('Update Generator', 'wc-serial-numbers-pro') ?>
				<?php else : ?>
					<?php _e('Add Generator', 'wc-serial-numbers-pro') ?>
				<?php endif ?>
			</h1>
			<a href="<?php echo esc_url(remove_query_arg(array('action', 'id'))); ?>" class="page-title-action">
				<?php _e('Back', 'wc-serial-numbers-pro'); ?>
			</a>

			<hr class="wp-header-end">

			<form method="post" action="<?php echo esc_html(admin_url('admin-post.php')); ?>">
				<table class="form-table">
					<tbody>
						<tr>
							<th>
								<label for="product_id">
									<?php esc_html_e('Product', 'wc-serial-numbers-pro'); ?>
								</label>
							</th>

							<td>
								<select name="product_id" id="product_id" class="regular-text wc-serial-numbers-select-product" required="required">
									<?php echo sprintf('<option value="%d" selected="selected">%s</option>', $item['product_id'], wc_serial_numbers_get_product_title($item['product_id'])); ?>
								</select>
								<p class="description"><?php esc_html_e('The product to which the serial number will be assigned.', 'wc-serial-numbers-pro'); ?></p>
							</td>
						</tr>

						<tr>
							<th>
								<label for="pattern">
									<?php esc_html_e('Pattern', 'wc-serial-numbers-pro'); ?>
								</label>
							</th>

							<td>
								<?php echo sprintf('<textarea name="pattern" id="pattern" class="regular-text" required="required" placeholder="serial-############################">%s</textarea>', $item['pattern']); ?>
								<p class="description"><?php esc_html_e('#\'s will be replaced by random/sequential characters and Y, m and d within {} will be replaced with the year, month and date.', 'wc-serial-numbers-pro'); ?></p>
							</td>
						</tr>

						<tr>
							<th>
								<label for="type">
									<?php esc_html_e('Type', 'wc-serial-numbers-pro'); ?>
								</label>
							</th>

							<td>
								<select name="type" id="type" class="regular-text">
									<?php
									$types = array(
										'random'     => __('Random', 'wc-serial-numbers-pro'),
										'sequential' => __('Sequential', 'wc-serial-numbers-pro'),
									);
									foreach ($types as $key => $option) {
										echo sprintf('<option value="%s" %s>%s</option>', $key, selected($key, $item['type']), $option);
									}
									?>
								</select>
								<p class="description"><?php esc_html_e('Select how serial numbers will be generated.', 'wc-serial-numbers-pro'); ?></p>
							</td>
						</tr>

						<?php if ('sequential' == $item['type']) : ?>
							<tr>
								<th>
									<label for="order_pointer">
										<?php esc_html_e('Sequential Pointer', 'wc-serial-numbers-pro'); ?>
									</label>
								</th>
								<td>
									<?php echo sprintf('<input name="total_generated" id="total_generated" class="regular-text" type="number" value="%s" autocomplete="off">', absint($item['total_generated'])); ?>
									<p class="description"><?php esc_html_e('Current location of the sequential order pointer, Do not change unless you know what you are doing.', 'wc-serial-numbers-pro'); ?></p>
								</td>
							</tr>

						<?php endif; ?>

						<?php if (!wc_serial_numbers_software_support_disabled()) : ?>
							<tr>
								<th>
									<label for="activation_limit">
										<?php esc_html_e('Activation Limit', 'wc-serial-numbers-pro'); ?>
									</label>
								</th>
								<td>
									<?php echo sprintf('<input name="activation_limit" id="activation_limit" class="regular-text" type="number" value="%s" autocomplete="off">', $item['activation_limit']); ?>
									<p class="description"><?php esc_html_e('Maximum number of times the key can be used to activate the software. If the product is not software keep blank.', 'wc-serial-numbers-pro'); ?></p>
								</td>
							</tr>

							<tr>
								<th>
									<label for="validity">
										<?php esc_html_e('Validity (days)', 'wc-serial-numbers-pro'); ?>
									</label>
								</th>
								<td>
									<?php echo sprintf('<input name="validity" id="validity" class="regular-text" type="number" value="%s">', $item['validity']); ?>
									<p class="description"><?php esc_html_e('The number of days the key will be valid for after the purchase date.', 'wc-serial-numbers-pro'); ?></p>
								</td>
							</tr>
						<?php endif; ?>

						<tr>
							<th>
								<label for="expire_date"><?php esc_html_e('Expires at', 'wc-serial-numbers-pro'); ?></label>
							</th>
							<td>
								<?php echo sprintf('<input name="expire_date" id="expire_date" class="regular-text wc-serial-numbers-select-date" type="text" autocomplete="off" value="%s">', $item['expire_date']); ?>
								<p class="description"><?php esc_html_e('After this date the key will not be assigned with any order. Leave blank for no expire date.', 'wc-serial-numbers-pro'); ?></p>
							</td>
						</tr>

						<tr>
							<td></td>
							<td>
								<p class="submit">
									<input type="hidden" name="action" value="wc_serial_numbers_edit_generator">
									<?php wp_nonce_field('wc_serial_numbers_edit_generator'); ?>
									<?php if ($update) : ?>
										<?php echo sprintf('<input type="hidden" name="id" value="%d">', $id); ?>
										<?php submit_button(__('Update Generator', 'wc-serial-numbers-pro')); ?>
									<?php else : ?>
										<?php submit_button(__('Add Generator', 'wc-serial-numbers-pro')); ?>
									<?php endif ?>
								</p>
							</td>
						</tr>

					</tbody>
				</table>

			</form>

		</div>
<?php
	}


	/**
	 * handle bulk actions
	 *
	 * @param $doaction
	 *
	 * @since 1.1.0
	 */
	public static function handle_bulk_actions($doaction)
	{
		if ($doaction) {
			if (isset($_REQUEST['id'])) {
				$ids      = [intval($_REQUEST['id'])];
				$doaction = (-1 != $_REQUEST['action']) ? $_REQUEST['action'] : $_REQUEST['action2'];
			} elseif (isset($_REQUEST['ids'])) {
				$ids = array_map('absint', $_REQUEST['ids']);
			} elseif (wp_get_referer()) {
				wp_safe_redirect(wp_get_referer());
				exit;
			}

			foreach ($ids as $id) {
				switch ($doaction) {
					case 'delete':
						wp_delete_post($id, true);
						break;
				}
			}

			wp_safe_redirect(wp_get_referer());
			exit;
		} elseif (!empty($_GET['_wp_http_referer'])) {
			wp_redirect(remove_query_arg(array(
				'_wp_http_referer',
				'_wpnonce'
			), wp_unslash($_SERVER['REQUEST_URI'])));
			exit;
		}
	}
}

WC_Serial_Numbers_Pro_Admin_Generators_Screen::init();
